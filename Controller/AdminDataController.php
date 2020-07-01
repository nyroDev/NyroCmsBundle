<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Event\AdminFormEvent;
use NyroDev\NyroCmsBundle\Form\Type\ContentHandlerFilterType;
use NyroDev\NyroCmsBundle\Form\Type\UserFilterType;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;

class AdminDataController extends AbstractAdminController
{
    ///////////////////////////////////////////////////////////////
    // Contents

    public function contentAction()
    {
        return $this->redirectToRoute('nyrocms_admin_data_content_tree');
    }

    public function contentTreeAction(Request $request, $id = null)
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
            $tree = $request->request->get('tree');
            $treeLevel = $request->request->get('treeLevel');
            $treeChanged = $request->request->get('treeChanged');

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
                        throw new \InvalidArgumentException('Unknown content Id: '.$t);
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

    public function contentFixAction($id = null)
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

    public function contentTreeSub(\NyroDev\NyroCmsBundle\Model\Content $parent = null)
    {
        $route = 'nyrocms_admin_data_content';

        return $this->render('@NyroDevNyroCms/AdminData/contentTreeSub.html.php', [
            'route' => $route,
            'parent' => $parent,
            'canEditParent' => $this->get(AdminService::class)->canAdminContent($parent),
            'canHaveSub' => $this->get(AdminService::class)->canHaveSub($parent),
            'contents' => $this->get(DbAbstractService::class)->getContentRepository()->children($parent, true),
        ]);
    }

    public function contentDeleteAction($id)
    {
        $row = $this->get(DbAbstractService::class)->getContentRepository()->find($id);
        if ($row && !$row->getHandler() && true === $this->get(AdminService::class)->canAdminContent($row)) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(['id' => $row->getRoot()]));
    }

    public function contentAddAction(Request $request, $pid = null)
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

    public function contentEditAction(Request $request, $id)
    {
        $row = $this->get(DbAbstractService::class)->getContentRepository()->find($id);
        if (!$row || true === !$this->get(AdminService::class)->canAdminContent($row)) {
            throw $this->createNotFoundException();
        }
        $this->get(AdminService::class)->setContentParentId($row->getVeryParent()->getId());

        return $this->contentForm($request, self::EDIT, $row);
    }

    public function contentForm($request, $action, $row)
    {
        $routePrm = [
            'id' => $row->getVeryParent()->getId(),
        ];

        $themes = $this->get(ComposerService::class)->getThemes($row->getParent());
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
                'choice_label' => function ($row) {
                    return $row.''.($row->getParent() ? ' ('.$row->getParent().')' : '');
                },
                'query_builder' => function (ContentRepositoryInterface $er) use ($row) {
                    return $er->getFormQueryBuilder($row->getParent()->getRoot(), $row->getId());
                },
                'attr' => [
                    'class' => 'autocompleteSelMul',
                    'placeholder' => $this->trans('admin.content.relatedsPlaceholder'),
                ],
            ],
            'submit' => [
                'attr' => [
                    'data-cancelurl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_content_tree', $routePrm),
                ],
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
        }

        $adminForm = $this->createAdminForm($request, 'content', $action, $row, $fields, 'nyrocms_admin_data_content_tree', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush', $this->get(DbAbstractService::class)->getObjectManager());
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    protected $contentTranslationFields = [
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

    protected $translations;
    protected $langs;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $contentForm;

    protected function contentFormClb($action, \NyroDev\NyroCmsBundle\Model\Content $row, \Symfony\Component\Form\FormBuilder $form)
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

    protected function contentFlush($action, $row, $form)
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::BEFOREFLUSH_CONTENT);

        $this->contentForm = $form;
        $this->get(AdminService::class)->updateContentUrl($row, self::EDIT == $action);
    }

    protected function contentAfterFlush($response, $action, $row)
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

    ///////////////////////////////////////////////////////////////
    // Users Roles

    public function userRoleAction(Request $request)
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
                            'id',
                            'name',
                            $isDev ? 'roleName' : null,
                            $isDev ? 'internal' : null,
                            'updated',
                        ]),
                    ],
                    $this->createList($request, $repo, $route, [], 'id', 'desc', null, $qb)
                ));
    }

    public function userRoleDeleteAction($id)
    {
        $row = $this->get(DbAbstractService::class)->getUserRoleRepository()->find($id);
        if ($row) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_userRole'));
    }

    public function userRoleAddAction(Request $request)
    {
        $row = $this->get(DbAbstractService::class)->getNew('user_role', false);

        return $this->userRoleForm($request, self::ADD, $row);
    }

    public function userRoleEditAction(Request $request, $id)
    {
        $row = $this->get(DbAbstractService::class)->getUserRoleRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->userRoleForm($request, self::EDIT, $row);
    }

    public function userRoleForm(Request $request, $action, $row)
    {
        $moreOptions = [
            'contents' => $this->get(AdminService::class)->getContentsChoiceTypeOptions($this->getParameter('nyrocms.user_roles.maxlevel_content')),
            'submit' => [
                'attr' => [
                    'more' => 'hello',
                    'data-cancelurl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_userRole'),
                ],
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

    ///////////////////////////////////////////////////////////////
    // contentHandlers

    public function contentHandlerAction(Request $request)
    {
        $repo = $this->get(DbAbstractService::class)->getContentHandlerRepository();

        $route = 'nyrocms_admin_data_contentHandler';

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
                array_merge(
                    [
                        'name' => 'contentHandler',
                        'route' => $route,
                        'fields' => [
                            'id',
                            'name',
                            'class',
                            'hasAdmin',
                            'updated',
                        ],
                    ],
                    $this->createList($request, $repo, $route, [], 'name', 'asc', ContentHandlerFilterType::class)
                ));
    }

    public function contentHandlerDeleteAction($id)
    {
        $row = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($id);
        if ($row) {
            $this->get(DbAbstractService::class)->remove($row);
            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_contentHandler'));
    }

    public function contentHandlerAddAction(Request $request)
    {
        $row = $this->get(DbAbstractService::class)->getNew('content_handler', false);

        return $this->contentHandlerForm($request, self::ADD, $row);
    }

    public function contentHandlerEditAction(Request $request, $id)
    {
        $row = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->contentHandlerForm($request, self::EDIT, $row);
    }

    public function contentHandlerForm(Request $request, $action, $row)
    {
        $classes = $this->get(NyroCmsService::class)->getFoundHandlers();

        $moreOptions = [
            'class' => [
                'placeholder' => '',
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
                'attr' => [
                    'data-cancelurl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_contentHandler'),
                ],
            ],
        ];

        if (count($classes)) {
            $moreOptions['class']['type'] = ChoiceType::class;
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

    ///////////////////////////////////////////////////////////////
    // Contact Messages

    public function contactMessageAction(Request $request, $chid)
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

    ///////////////////////////////////////////////////////////////
    // Users

    public function userAction(Request $request)
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
                            'id',
                            'email',
                            'firstname',
                            'lastname',
                            'userType',
                            'valid',
                            'updated',
                        ],
                    ],
                    $this->createList($request, $repo, $route, [], 'id', 'desc', $filter)
                ));
    }

    public function userDeleteAction($id)
    {
        $row = $this->get(DbAbstractService::class)->getUserRepository()->find($id);
        if ($row) {
            $this->getDoctrine()->getManager()->remove($row);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_user'));
    }

    public function userAddAction(Request $request)
    {
        $row = $this->get(DbAbstractService::class)->getNew('user', false);

        return $this->userForm($request, self::ADD, $row);
    }

    public function userEditAction(Request $request, $id)
    {
        $row = $this->get(DbAbstractService::class)->getUserRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->userForm($request, self::EDIT, $row);
    }

    public function userForm(Request $request, $action, $row)
    {
        $moreOptions = [
            'userType' => [
                'type' => ChoiceType::class,
                'placeholder' => '',
                'choices' => array_flip($this->get(AdminService::class)->getUserTypeChoices()),
            ],
            'validStart' => $this->get(NyroCmsService::class)->getDateFormOptions(),
            'validEnd' => $this->get(NyroCmsService::class)->getDateFormOptions(),
            'userRoles' => [
                'expanded' => true,
                'query_builder' => function (UserRoleRepositoryInterface $er) {
                    return $er->getFormQueryBuilder();
                },
            ],
            'submit' => [
                'attr' => [
                    'data-cancelurl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_data_user'),
                ],
            ],
        ];

        $adminForm = $this->createAdminForm($request, 'user', $action, $row, array_filter([
                    'email',
                    'firstname',
                    'lastname',
                    //'password',
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

    protected $origUserPassword;

    protected function userFormUpdate($action, $row, \Symfony\Component\Form\FormBuilder $form)
    {
        $this->origUserPassword = $row->getPassword();
        $form->get('valid')->setRequired(false);

        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::UPDATE_USER);
    }

    protected function userFormFlush($action, $row, \Symfony\Component\Form\Form $form)
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::BEFOREFLUSH_USER);
    }

    protected function userFormAfterFlush($response, $action, $row)
    {
        $adminFormEvent = new AdminFormEvent($action, $row);
        $this->get('event_dispatcher')->dispatch($adminFormEvent, AdminFormEvent::AFTERFLUSH_USER);

        if (self::ADD == $action) {
            $this->get(UserService::class)->handleAddUser($row);
        }
    }
}
