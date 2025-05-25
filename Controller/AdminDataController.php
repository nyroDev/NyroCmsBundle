<?php

namespace NyroDev\NyroCmsBundle\Controller;

use InvalidArgumentException;
use NyroDev\NyroCmsBundle\Event\AdminFormEvent;
use NyroDev\NyroCmsBundle\Form\Type\ContentHandlerFilterType;
use NyroDev\NyroCmsBundle\Form\Type\UserFilterType;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\Template;
use NyroDev\NyroCmsBundle\Model\TemplateCategory;
use NyroDev\NyroCmsBundle\Model\User;
use NyroDev\NyroCmsBundle\Model\UserRole;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface;
use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\ComposerService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\NyroCmsBundle\Services\UserService;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService as nyroDevDbService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;

class AdminDataController extends AbstractAdminController
{
    // /////////////////////////////////////////////////////////////
    // Contents

    public function contentAction(): Response
    {
        return $this->redirectToRoute('nyrocms_admin_data_content_tree');
    }

    public function switchRootContentAction(Request $request, string $id): Response
    {
        $request->getSession()->set(AdminService::SESSION_ROOT_NAME, $id);

        return $this->redirectToRoute('nyrocms_admin_data_content_tree', ['id' => $id]);
    }

    public function contentTreeAction(Request $request, ?string $id = null): Response
    {
        $repo = $this->get(DbAbstractService::class)->getContentRepository();

        $parent = $id ? $repo->find($id) : $repo->findOneBy(['level' => 0]);
        if (!$parent) {
            throw $this->createNotFoundException();
        }

        $this->get(AdminService::class)->setContentParentId($parent->getVeryParent()->getId());

        $canRootComposer = $this->get(AdminService::class)->canRootComposer($parent);
        $canAdminContent = $this->get(AdminService::class)->canAdminContent($parent);
        if ($canAdminContent && $request->isMethod('post')) {
            $tree = $request->request->all('tree');
            $treeLevel = $request->request->all('treeLevel');
            $treeChanged = $request->request->all('treeChanged');

            $contents = [];
            foreach ($repo->children($parent) as $c) {
                $contents[$c->getId()] = $c;
            }

            $lastChild = [
                0 => $parent,
            ];
            $lastLevel = null;
            if (count($tree)) {
                foreach ($tree as $t) {
                    if (!isset($contents[$t]) || !isset($treeLevel[$t]) || !isset($treeChanged[$t])) {
                        throw new InvalidArgumentException('Unknown content Id: '.$t);
                    }

                    $curLevel = $treeLevel[$t];
                    if ($curLevel > $lastLevel) {
                        // Going deeper
                        $contents[$t]->setParent($lastChild[$curLevel - 1]);
                        $contents[$t]->setLevel($lastChild[$curLevel - 1]->getLevel() + 1);
                        $repo->persistAsFirstChildOf($contents[$t], $lastChild[$curLevel - 1]);
                    } else {
                        // going narrower or Same level
                        $contents[$t]->setParent($lastChild[$curLevel]->getParent());
                        $contents[$t]->setLevel($lastChild[$curLevel]->getLevel());
                        $repo->persistAsNextSiblingOf($contents[$t], $lastChild[$curLevel]);
                    }
                    $lastChild[$curLevel] = $contents[$t];
                    $lastLevel = $curLevel;
                    $this->get(DbAbstractService::class)->flush();
                }
            }

            return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(['id' => $id]));
        }

        return $this->render('@NyroDevNyroCms/AdminData/contentTree.html.php', [
            'parent' => $parent,
            'canRootComposer' => $canRootComposer,
            'candDirectAdd' => $canAdminContent && $this->get(AdminService::class)->canHaveSub($parent),
        ]);
    }

    public function contentFixAction(?string $id = null): Response
    {
        $repo = $this->get(DbAbstractService::class)->getContentRepository();

        $repo->verify();
        $repo->recover();
        $this->get(DbAbstractService::class)->flush();

        $parent = $id ? $repo->find($id) : $repo->findOneBy(['level' => 0]);
        if (!$parent) {
            throw $this->createNotFoundException();
        }

        foreach ($repo->children($parent) as $update) {
            $this->get(AdminService::class)->updateContentUrl($update, false, false);
        }

        $this->get(DbAbstractService::class)->flush();

        return $this->redirectToRoute('nyrocms_admin_data_content_tree', array_filter(['id' => $id]));
    }

    public function contentDeleteAction(string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getContentRepository()->find($id);
        if ($row && !$row->getHandler() && true === $this->get(AdminService::class)->canAdminContent($row)) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(['id' => $row->getRoot()]));
    }

    public function contentAddAction(Request $request, ?string $pid = null): Response
    {
        $row = $this->get(DbAbstractService::class)->getNew('content', false);

        if ($pid) {
            $parent = $this->get(DbAbstractService::class)->getContentRepository()->find($pid);
            if (!$parent || !$this->get(AdminService::class)->canAdminContent($parent)) {
                throw $this->createNotFoundException();
            }
            $row->setParent($parent);
            $this->get(AdminService::class)->setContentParentId($parent->getVeryParent()->getId());
        }

        return $this->contentForm($request, self::ADD, $row);
    }

    public function contentEditAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getContentRepository()->find($id);
        if (!$row || true === !$this->get(AdminService::class)->canAdminContent($row)) {
            throw $this->createNotFoundException();
        }
        $this->get(AdminService::class)->setContentParentId($row->getVeryParent()->getId());

        return $this->contentForm($request, self::EDIT, $row);
    }

    public function contentForm(Request $request, string $action, object $row): Response
    {
        $routePrm = [
            'id' => $row->getVeryParent()->getId(),
        ];

        $themes = $this->get(ComposerService::class)->getThemes($row->getParent());
        $moreFormOptions = [
            'formTabs' => true,
        ];
        $moreOptions = [
            'theme' => [
                'type' => ChoiceType::class,
                'choices' => array_flip($themes),
            ],
            'state' => [
                'type' => ChoiceType::class,
                'choices' => array_flip($this->get(AdminService::class)->getContentStateChoices()),
            ],
            'relateds' => [
                'wc' => true,
                'expanded' => true,
                'choice_label' => function ($row) {
                    return $row.''.($row->getParent() ? ' ('.$row->getParent().')' : '');
                },
                'query_builder' => function (ContentRepositoryInterface $er) use ($row) {
                    return $er->getFormQueryBuilder($row->getParent()->getRoot(), $row->getId());
                },
                'attr' => [
                    'placeholder' => $this->trans('admin.content.relatedsPlaceholder'),
                ],
            ],
            'ogImage' => [
                'showDelete' => 'ogImageDelete',
            ],
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_content_tree', $routePrm),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        $fields = array_filter([
            'title',
            count($themes) > 1 ? 'theme' : null,
            'state',
            'goUrl',
            'goBlank',
            'redirectToChildren',
            'relateds',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'ogTitle',
            'ogDescription',
            'ogImage',
        ]);

        $isInMeta = false;
        foreach ($fields as $field) {
            if (!isset($moreOptions[$field])) {
                $moreOptions[$field] = [];
            }
            $isInMeta = $isInMeta || 'metaTitle' === $field;
            $moreOptions[$field]['fieldset'] = $isInMeta ? [
                'name' => 'metadata',
                'label' => $this->get(AdminService::class)->getIcon('seo').$this->trans('admin.content.metadataFieldset'),
            ] : [
                'name' => 'content',
                'label' => $this->get(AdminService::class)->getIcon('tab').$this->trans('admin.content.contentFieldset'),
            ];
        }

        $moreOptions['submit']['fieldset'] = [
            'name' => 'actions',
            'attr' => [
                'slot' => 'footer',
            ],
        ];

        if ($this->get(AdminService::class)->isDeveloper()) {
            $fields[] = 'contentHandler';
            $fields[] = 'menuOption';
            $repoContentHandler = $this->get(DbAbstractService::class)->getContentHandlerRepository();
            $moreOptions['contentHandler'] = [
                'query_builder' => function ($er) use ($repoContentHandler) {
                    return $repoContentHandler->getFormQueryBuilder();
                },
            ];
        }

        if ($row instanceof AbstractUploadable) {
            $row->setService($this->get(NyrodevService::class));

            if ($request->request->get('ogImageDelete')) {
                $row->removeFile('ogImage');
            }
        }

        $adminForm = $this->createAdminForm($request, 'content', $action, $row, $fields, 'nyrocms_admin_data_content_tree', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush', $this->get(DbAbstractService::class)->getObjectManager(), $moreFormOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        $adminForm['title'] = $row->getVeryParent()->getTitle();

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    protected array $contentTranslationFields = [
        'title' => [
            'type' => TextType::class,
            'required' => true,
        ],
        'goUrl' => [
            'type' => UrlType::class,
            'required' => false,
        ],
        'metaTitle' => [
            'type' => TextType::class,
            'required' => false,
        ],
        'metaDescription' => [
            'type' => TextareaType::class,
            'required' => false,
        ],
        'metaKeywords' => [
            'type' => TextareaType::class,
            'required' => false,
        ],
        'ogTitle' => [
            'type' => TextType::class,
            'required' => false,
        ],
        'ogDescription' => [
            'type' => TextareaType::class,
            'required' => false,
        ],
    ];

    protected array $langs;

    protected ?Form $contentForm;

    protected function contentFormClb(string $action, Content $row, FormBuilder $form): void
    {
        $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
        $defaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
        unset($langs[$defaultLocale]);

        $this->translations = [];
        foreach ($row->getTranslations() as $tr) {
            if (!isset($this->translations[$tr->getLocale()])) {
                $this->translations[$tr->getLocale()] = [];
            }
            $this->translations[$tr->getLocale()][$tr->getField()] = $tr;
        }

        /* @var $form \Ivory\OrderedForm\Builder\OrderedFormBuilder */

        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($langs as $lg => $lang) {
            foreach ($this->contentTranslationFields as $field => $options) {
                if ($form->has($field)) {
                    $type = $options['type'];
                    unset($options['type']);
                    $fieldName = 'lang_'.$lg.'_'.$field;

                    if (isset($options['required']) && $options['required']) {
                        $options['constraints'] = [new Constraints\NotBlank()];
                    }

                    $form->add($fieldName, $type, array_merge($options, [
                        'label' => $this->trans('admin.content.'.$field).' '.strtoupper($lg),
                        'mapped' => false,
                        'data' => isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
                        'position' => ['after' => $field],
                    ]));
                }
            }
        }

        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::UPDATE_CONTENT);
    }

    protected function contentFlush(string $action, object $row, Form $form): void
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::BEFOREFLUSH_CONTENT);

        $this->contentForm = $form;
        $this->get(AdminService::class)->updateContentUrl($row, self::EDIT == $action);
    }

    protected function contentAfterFlush(Response $response, string $action, Content $row): void
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $this->contentForm);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::AFTERFLUSH_CONTENT);

        $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
        $defaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
        unset($langs[$defaultLocale]);

        $om = $this->get(DbAbstractService::class)->getObjectManager();
        $propertyAccess = PropertyAccess::createPropertyAccessor();

        foreach ($langs as $lg => $lang) {
            $row->setTranslatableLocale($lg);
            $om->refresh($row);

            foreach ($this->contentTranslationFields as $field => $options) {
                $fieldName = 'lang_'.$lg.'_'.$field;
                if ($this->contentForm->has($fieldName)) {
                    $propertyAccess->setValue($row, $field, $this->contentForm->get($fieldName)->getData());
                }
            }

            $this->get(AdminService::class)->updateContentUrl($row, self::EDIT == $action);
            $om->flush();
        }
    }

    // /////////////////////////////////////////////////////////////
    // Users Roles

    public function userRoleAction(Request $request): Response
    {
        $isDev = $this->get(AdminService::class)->isDeveloper();

        $repo = $this->get(DbAbstractService::class)->getUserRoleRepository();
        $qb = $this->get(nyroDevDbService::class)->getQueryBuilder($repo);
        if (!$isDev) {
            $qb->addWhere('internal', '<>', 1);
        }
        $route = 'nyrocms_admin_data_userRole';

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'name' => 'userRole',
                    'route' => $route,
                    'fields' => array_filter([
                        'name',
                        $isDev ? 'roleName' : null,
                        $isDev ? 'internal' : null,
                        'updated',
                    ]),
                ],
                $this->createList($request, $repo, $route, [], 'id', 'desc', null, $qb)
            ));
    }

    public function userRoleDeleteAction(string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getUserRoleRepository()->find($id);
        if ($row) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_userRole'));
    }

    public function userRoleAddAction(Request $request): Response
    {
        $row = $this->get(DbAbstractService::class)->getNew('user_role', false);

        return $this->userRoleForm($request, self::ADD, $row);
    }

    public function userRoleEditAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getUserRoleRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->userRoleForm($request, self::EDIT, $row);
    }

    public function userRoleForm(Request $request, string $action, UserRole $row): Response
    {
        $moreOptions = [
            'contents' => $this->get(AdminService::class)->getContentsChoiceTypeOptions($this->getParameter('nyrocms.user_roles.maxlevel_content')),
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_userRole'),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        $isDev = $this->get(AdminService::class)->isDeveloper();
        $adminForm = $this->createAdminForm($request, 'userRole', $action, $row, array_filter([
            'name',
            $isDev ? 'roleName' : null,
            $isDev ? 'internal' : null,
            'contents',
        ]), 'nyrocms_admin_data_userRole', [], null, null, null, $moreOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    // /////////////////////////////////////////////////////////////
    // contentHandlers

    public function contentHandlerAction(Request $request): Response
    {
        $repo = $this->get(DbAbstractService::class)->getContentHandlerRepository();

        $route = 'nyrocms_admin_data_contentHandler';

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'name' => 'contentHandler',
                    'route' => $route,
                    'fields' => [
                        'name',
                        'class',
                        'hasAdmin',
                        'updated',
                    ],
                ],
                $this->createList($request, $repo, $route, [], 'name', 'asc', ContentHandlerFilterType::class)
            ));
    }

    public function contentHandlerDeleteAction(string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($id);
        if ($row) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_contentHandler'));
    }

    public function contentHandlerAddAction(Request $request): Response
    {
        $row = $this->get(DbAbstractService::class)->getNew('content_handler', false);

        return $this->contentHandlerForm($request, self::ADD, $row);
    }

    public function contentHandlerEditAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->contentHandlerForm($request, self::EDIT, $row);
    }

    public function contentHandlerForm(Request $request, string $action, ContentHandler $row): Response
    {
        $classes = $this->get(NyroCmsService::class)->getFoundHandlers();

        $moreOptions = [
            'class' => [
                'constraints' => [
                    new Constraints\Callback([
                        'callback' => function ($data, $context) {
                            if ($data) {
                                if (!class_exists($data)) {
                                    /* @var $context \Symfony\Component\Validator\Context\ExecutionContext */
                                    $context->addViolation('Class doesn\'t exist.');
                                } elseif (!is_subclass_of($data, '\NyroDev\NyroCmsBundle\Handler\AbstractHandler')) {
                                    $context->addViolation('Class is not a valid AbstractHandler.');
                                }
                            }
                        },
                    ]),
                ],
            ],
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_contentHandler'),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        if (count($classes)) {
            $moreOptions['class']['type'] = ChoiceType::class;
            $moreOptions['class']['placeholder'] = '';
            $moreOptions['class']['choices'] = array_combine($classes, $classes);
        }

        $adminForm = $this->createAdminForm($request, 'contentHandler', $action, $row, [
            'name',
            'class',
            'hasAdmin',
        ], 'nyrocms_admin_data_contentHandler', [], null, null, null, $moreOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    // /////////////////////////////////////////////////////////////
    // template categories

    public function templateCategoryAction(Request $request): Response
    {
        $repo = $this->get(DbAbstractService::class)->getTemplateCategoryRepository();

        $route = 'nyrocms_admin_data_templateCategory';

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'name' => 'templateCategory',
                    'route' => $route,
                    'fields' => [
                        'title',
                    ],
                ],
                $this->createList($request, $repo, $route, [], 'title', 'asc')
            ));
    }

    public function templateCategoryDeleteAction(string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getTemplateCategoryRepository()->find($id);
        if ($row) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_templateCategory'));
    }

    public function templateCategoryAddAction(Request $request): Response
    {
        $row = $this->get(DbAbstractService::class)->getNew('template_category', false);

        return $this->templateCategoryForm($request, self::ADD, $row);
    }

    public function templateCategoryEditAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getTemplateCategoryRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->templateCategoryForm($request, self::EDIT, $row);
    }

    public function templateCategoryForm(Request $request, string $action, TemplateCategory $row): Response
    {
        $moreOptions = [
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_templateCategory'),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        $fields = array_filter([
            'title',
            $this->get(AdminService::class)->isDeveloper() ? 'icon' : false,
        ]);

        $adminForm = $this->createAdminForm($request, 'templateCategory', $action, $row, $fields, 'nyrocms_admin_data_templateCategory', [], null, null, null, $moreOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    // /////////////////////////////////////////////////////////////
    // templates

    public function templateAction(Request $request): Response
    {
        $repo = $this->get(DbAbstractService::class)->getTemplateRepository();

        $route = 'nyrocms_admin_data_template';

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'name' => 'template',
                    'route' => $route,
                    'fields' => [
                        'title',
                        'templateCategory',
                        'updated',
                    ],
                    'moreActions' => [
                        'composer' => [
                            'name' => $this->get(AdminService::class)->getIcon('composer'),
                            '_blank' => true,
                            'route' => 'nyrocms_admin_composer',
                            'routePrm' => [
                                'type' => 'Template',
                            ],
                        ],
                    ],
                ],
                $this->createList($request, $repo, $route, [], 'title', 'asc')
            ));
    }

    public function templateDeleteAction(string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getTemplateRepository()->find($id);
        if ($row) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_template'));
    }

    public function templateAddAction(Request $request): Response
    {
        $row = $this->get(DbAbstractService::class)->getNew('template', false);

        return $this->templateForm($request, self::ADD, $row);
    }

    public function templateEditAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getTemplateRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->templateForm($request, self::EDIT, $row);
    }

    public function templateForm(Request $request, string $action, Template $row): Response
    {
        $defaultForChoices = [];

        foreach ($this->get(NyroCmsService::class)->getFoundComposables() as $foundComposable) {
            $defaultForChoices[$this->trans('admin.template.defaultForComposables.'.$foundComposable)] = $foundComposable;
        }

        $repoTemplateCategory = $this->get(DbAbstractService::class)->getTemplateCategoryRepository();
        $themes = $this->get(ComposerService::class)->getDefaultThemes();
        $moreOptions = [
            'templateCategory' => [
                'query_builder' => function ($er) use ($repoTemplateCategory) {
                    return $repoTemplateCategory->createQueryBuilder('tc')
                        ->orderBy('tc.title', 'ASC');
                },
            ],
            'defaultFor' => [
                'type' => ChoiceType::class,
                'choices' => $defaultForChoices,
            ],
            'enabledFor' => [
                'type' => ChoiceType::class,
                'multiple' => true,
                'expanded' => true,
                'choices' => $defaultForChoices,
            ],
            'theme' => [
                'type' => ChoiceType::class,
                'choices' => array_flip($themes),
            ],
            'state' => [
                'type' => ChoiceType::class,
                'choices' => array_flip($this->get(AdminService::class)->getTemplateStateChoices()),
            ],
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_template'),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        $fields = array_filter([
            'templateCategory',
            'title',
            $this->get(AdminService::class)->isDeveloper() ? 'icon' : false,
            'enabledFor',
            'defaultFor',
            count($themes) > 1 ? 'theme' : null,
            'state',
        ]);

        $adminForm = $this->createAdminForm($request, 'template', $action, $row, $fields, 'nyrocms_admin_data_template', [], null, null, null, $moreOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    // /////////////////////////////////////////////////////////////
    // Contact Messages

    public function contactMessageAction(Request $request, string $chid): Response
    {
        $contentHandler = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($chid);
        if (!$contentHandler) {
            throw $this->createNotFoundException();
        }

        $this->canAdminContentHandler($contentHandler);

        $handler = $this->get(NyroCmsService::class)->getHandler($contentHandler);

        $repo = $this->get(DbAbstractService::class)->getRepository('contact_message');

        $qb = $this->get(nyroDevDbService::class)->getQueryBuilder($repo);
        $qb->addWhere('contentHandler', '=', $contentHandler->getId());

        $exportConfig = [
            'title' => $this->trans('admin.contactMessage.viewTitle'),
            'prefix' => 'contactMessage',
            'fields' => $handler->getAdminMessageExportFields(),
        ];

        $route = 'nyrocms_admin_data_contactMessage';
        $routePrm = ['chid' => $chid];

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'name' => 'contactMessage',
                    'route' => $route,
                    'fields' => $handler->getAdminMessageListFields(),
                    'moreGlobalActions' => [
                        'export' => [
                            'route' => $route,
                            'routePrm' => array_merge($routePrm, ['export' => 1]),
                            'name' => $this->trans('admin.contactMessage.export'),
                            'attrs' => 'target="_blank"',
                        ],
                    ],
                    'noAdd' => true,
                    'noActions' => true,
                ],
                $this->createList($request, $repo, $route, $routePrm, 'id', 'desc', $handler->getAdminMessageFilterType(), $qb, $exportConfig)
            ));
    }

    // /////////////////////////////////////////////////////////////
    // Users

    public function userAction(Request $request): Response
    {
        $route = 'nyrocms_admin_data_user';

        $repo = $this->get(DbAbstractService::class)->getUserRepository();
        $filter = UserFilterType::class;

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'name' => 'user',
                    'route' => $route,
                    'fields' => [
                        'email',
                        'firstname',
                        'lastname',
                        'userType',
                        'valid',
                        'updated',
                    ],
                    'moreActions' => [
                        'welcome' => [
                            'name' => $this->get(AdminService::class)->getIcon('misc'),
                            'route' => 'nyrocms_admin_data_user_welcome',
                            'attrs' => 'title="'.$this->trans('admin.user.resendWelcome').'"',
                        ],
                    ],
                ],
                $this->createList($request, $repo, $route, [], 'id', 'desc', $filter)
            ));
    }

    public function userDeleteAction(string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getUserRepository()->find($id);
        if ($row) {
            // Email should remain unique, so update it to something unique just before it's deletion.
            $row->setEmail('deleted_'.uniqid().'_'.$row->getEmail());
            $this->getDoctrine()->getManager()->flush();

            $this->getDoctrine()->getManager()->remove($row);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_user'));
    }

    public function userAddAction(Request $request): Response
    {
        $row = $this->get(DbAbstractService::class)->getNew('user', false);

        return $this->userForm($request, self::ADD, $row);
    }

    public function userEditAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getUserRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->userForm($request, self::EDIT, $row);
    }

    public function userWelcomeAction(Request $request, string $id): Response
    {
        $row = $this->get(DbAbstractService::class)->getUserRepository()->find($id);
        if ($row) {
            $this->get(UserService::class)->sendWelcomeEmail($row);
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_user'));
    }

    public function userForm(Request $request, string $action, User $row): Response
    {
        $moreOptions = [
            'email' => [
                'icon' => NyroCmsService::ICON_PATH.'#email',
            ],
            'userType' => [
                'type' => ChoiceType::class,
                'placeholder' => '',
                'choices' => array_flip($this->get(AdminService::class)->getUserTypeChoices()),
            ],
            'userRoles' => [
                'expanded' => true,
                'query_builder' => function (UserRoleRepositoryInterface $er) {
                    return $er->getFormQueryBuilder();
                },
            ],
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_user'),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        $adminForm = $this->createAdminForm($request, 'user', $action, $row, array_filter([
            'email',
            'firstname',
            'lastname',
            // 'password',
            'userType',
            'valid',
            'validStart',
            'validEnd',
            $this->get(AdminService::class)->isDeveloper() ? 'developper' : null,
            'userRoles',
        ]), 'nyrocms_admin_data_user', [], 'userFormUpdate', 'userFormFlush', null, $moreOptions, 'userFormAfterFlush');
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    protected ?string $origUserPassword;

    protected function userFormUpdate(string $action, User $row, FormBuilder $form): void
    {
        $this->origUserPassword = $row->getPassword();
        $form->get('valid')->setRequired(false);

        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::UPDATE_USER);
    }

    protected function userFormFlush(string $action, User $row, Form $form): void
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::BEFOREFLUSH_USER);
    }

    protected function userFormAfterFlush(Response $response, string $action, User $row): void
    {
        $adminFormEvent = new AdminFormEvent($action, $row);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::AFTERFLUSH_USER);

        if (self::ADD == $action) {
            $this->get(UserService::class)->handleAddUser($row);
        }
    }
}
