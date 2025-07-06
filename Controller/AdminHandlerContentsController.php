<?php

namespace NyroDev\NyroCmsBundle\Controller;

use DateTime;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService as nyroDevDbService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;

class AdminHandlerContentsController extends AbstractAdminController
{
    protected function getContentHandler(string $chid): ContentHandler
    {
        $contentHandler = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($chid);
        if (!$contentHandler) {
            throw $this->createNotFoundException();
        }

        $this->canAdminContentHandler($contentHandler);

        return $contentHandler;
    }

    public function indexAction(Request $request, string $chid): Response
    {
        $ch = $this->getContentHandler($chid);
        $handler = $this->get(NyroCmsService::class)->getHandler($ch);

        $repo = $this->get(DbAbstractService::class)->getContentSpecRepository();
        $qb = $this->get(nyroDevDbService::class)->getQueryBuilder($repo)
                ->addWhere('contentHandler', '=', $ch->getId());

        $route = 'nyrocms_admin_handler_contents';
        $routePrm = [
            'chid' => $ch->getId(),
        ];

        $orderField = $handler->orderField();

        return $this->render('@NyroDevNyroCms/AdminTpl/list.html.php',
            array_merge(
                [
                    'title' => $ch->getName(),
                    'routePrmAdd' => $routePrm,
                    'routePrmEdit' => $routePrm,
                    'routePrmDelete' => $routePrm,
                    'name' => 'contentSpec',
                    'route' => $route,
                    'fields' => array_unique(array_filter([
                        'id',
                        'title',
                        $orderField,
                        'updated',
                    ])),
                    'moreActions' => array_filter([
                        'up' => $handler->hasMoveActions() ? [
                            'name' => '↑',
                            'route' => 'nyrocms_admin_handler_contents_up',
                            'routePrm' => $routePrm,
                        ] : false,
                        'down' => $handler->hasMoveActions() ? [
                            'name' => '↓',
                            'route' => 'nyrocms_admin_handler_contents_down',
                            'routePrm' => $routePrm,
                        ] : false,
                        'composer' => $handler->hasComposer() ? [
                            'name' => $this->get(AdminService::class)->getIcon('composer'),
                            'attrs' => 'title="'.$this->trans('admin.content.actions.composer').'"',
                            '_blank' => true,
                            'route' => 'nyrocms_admin_composer',
                            'routePrm' => [
                                'type' => 'ContentSpec',
                            ],
                        ] : false,
                    ]),
                ],
                $this->createList($request, $repo, $route, $routePrm, $orderField, $handler->isReversePositionOrder() ? 'desc' : 'asc', null, $qb)
            ));
    }

    public function deleteAction(Request $request, string $chid, string $id): Response
    {
        $ch = $this->getContentHandler($chid);

        $repo = $this->get(DbAbstractService::class)->getContentSpecRepository();
        $row = $repo->find($id);
        if ($row) {
            $row->setService($this->get(NyrodevService::class));
            $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
            $handler->init($request, true);
            $handler->deleteClb($row);

            $afters = $repo->getAfters($row);

            // $this->get(DbAbstractService::class)->remove($row);

            $row->setDeleted(new DateTime());

            foreach ($afters as $after) {
                $after->setPosition(max(0, $after->getPosition() - 1));
            }

            $this->get(DbAbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_handler_contents', ['chid' => $ch->getId()]));
    }

    public function addAction(Request $request, string $chid): Response
    {
        $ch = $this->getContentHandler($chid);
        $row = $this->get(DbAbstractService::class)->getNew('content_spec', false);
        $row->setContentHandler($ch);

        return $this->form($request, self::ADD, $row);
    }

    public function editAction(Request $request, string $chid, string $id): Response
    {
        $this->getContentHandler($chid);

        $row = $this->get(DbAbstractService::class)->getContentSpecRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->form($request, self::EDIT, $row);
    }

    public function moveAction(string $chid, string $id, string $dir): Response
    {
        $ch = $this->getContentHandler($chid);

        $repo = $this->get(DbAbstractService::class)->getContentSpecRepository();
        $row = $repo->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        $handler = $this->get(NyroCmsService::class)->getHandler($ch);
        if (!$handler->isReversePositionOrder()) {
            $dir = 'up' == $dir ? 'down' : 'up';
        }

        $position = $row->getPosition();
        if ('up' == $dir) {
            ++$position;
        } elseif ($position > 0) {
            --$position;
        }
        $row->setPosition($position);
        $this->get(DbAbstractService::class)->flush();

        return $this->redirect($this->generateUrl('nyrocms_admin_handler_contents', ['chid' => $ch->getId()]));
    }

    public function form(Request $request, string $action, ContentSpec $row): Response
    {
        $row->setService($this->get(NyrodevService::class));
        $routePrm = ['chid' => $row->getContentHandler()->getId()];
        $moreOptions = [
            'state' => [
                'type' => ChoiceType::class,
                'choices' => array_flip($this->get(AdminService::class)->getContentSpecStateChoices()),
            ],
            'submit' => [
                'icon' => NyroCmsService::ICON_PATH.'#save',
                'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl('nyrocms_admin_handler_contents', $routePrm),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
            ],
        ];

        $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
        $handler->init($request, true);

        if (!$handler->hasStateInvisible()) {
            unset($moreOptions['state']['choices'][ContentSpec::STATE_INVISIBLE]);
        }

        if ($handler->isIntroRequired()) {
            $moreOptions['intro'] = [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ];
            $this->translationFields['intro']['required'] = true;
        }

        if ($handler->useDateSpec()) {
            if (self::ADD == $action) {
                $row->setDateSpec(new DateTime());
            }
        }

        $fields = array_filter([
            'title',
            $handler->hasIntro() ? 'intro' : null,
            $handler->hasFeatured() ? 'featured' : null,
            'state',
            $handler->useDateSpec() ? 'dateSpec' : null,
            $handler->hasValidDates() ? 'validStart' : null,
            $handler->hasValidDates() ? 'validEnd' : null,
        ]);

        if (!$handler->hasIntro()) {
            unset($this->translationFields['intro']);
        }

        if ($handler->hasMetas()) {
            $fields[] = 'metaTitle';
            $fields[] = 'metaDescription';
            $fields[] = 'metaKeywords';
            $this->translationFields['metaTitle'] = [
                'type' => TextType::class,
                'required' => false,
            ];
            $this->translationFields['metaDescription'] = [
                'type' => TextareaType::class,
                'required' => false,
            ];
            $this->translationFields['metaKeywords'] = [
                'type' => TextareaType::class,
                'required' => false,
            ];
        }

        if ($handler->hasOgs()) {
            $fields[] = 'ogTitle';
            $fields[] = 'ogDescription';
            $fields[] = 'ogImage';
            $this->translationFields['ogTitle'] = [
                'type' => TextType::class,
                'required' => false,
            ];
            $this->translationFields['ogDescription'] = [
                'type' => TextareaType::class,
                'required' => false,
            ];
        }

        $adminForm = $this->createAdminForm($request, 'contentSpec', $action, $row, $fields, 'nyrocms_admin_handler_contents', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush');
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        $adminForm['title'] = $row->getContentHandler()->getName();

        return $this->render('@NyroDevNyroCms/AdminTpl/form.html.php', $adminForm);
    }

    protected array $translationFields = [
        'title' => [
            'type' => TextType::class,
            'required' => true,
        ],
        'intro' => [
            'type' => TextareaType::class,
            'required' => false,
        ],
    ];

    protected ?Form $contentForm;

    protected function contentFormClb(string $action, ContentSpec $row, FormBuilder $form): void
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

        $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());

        if ($handler->needTranslations()) {
            $propertyAccess = PropertyAccess::createPropertyAccessor();
            foreach ($langs as $lg => $lang) {
                foreach ($this->translationFields as $field => $options) {
                    $type = $options['type'];
                    unset($options['type']);
                    $fieldName = 'lang_'.$lg.'_'.$field;

                    if (isset($options['required']) && $options['required']) {
                        $options['constraints'] = [new Constraints\NotBlank()];
                    }

                    $form->add($fieldName, $type, array_merge($options, [
                        'label' => $this->trans('admin.contentSpec.'.$field).' '.strtoupper($lg),
                        'mapped' => false,
                        'data' => isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
                        'position' => ['after' => $field],
                    ]));
                }
            }
        }

        $handler->formClb($action, $row, $form, $langs, $this->translations);
    }

    protected function contentFlush(string $action, ContentSpec $row, Form $form): void
    {
        $this->contentForm = $form;
        $this->get(NyroCmsService::class)->getHandler($row->getContentHandler())->flushClb($action, $row, $form);
    }

    protected function contentAfterFlush(Response $response, string $action, ContentSpec $row): void
    {
        $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
        $handler->afterFlushClb($response, $action, $row);

        if ($handler->needTranslations()) {
            $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
            $defaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
            unset($langs[$defaultLocale]);

            $om = $this->get(DbAbstractService::class)->getObjectManager();
            $propertyAccess = PropertyAccess::createPropertyAccessor();

            foreach ($langs as $lg => $lang) {
                $row->setTranslatableLocale($lg);
                $om->refresh($row);

                foreach ($this->translationFields as $field => $options) {
                    $fieldName = 'lang_'.$lg.'_'.$field;
                    $propertyAccess->setValue($row, $field, $this->contentForm->get($fieldName)->getData());
                }

                $handler->flushLangClb($action, $row, $this->contentForm, $lg);

                $om->flush();
            }
        }
    }
}
