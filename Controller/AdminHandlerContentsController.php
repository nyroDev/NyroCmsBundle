<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use NyroDev\NyroCmsBundle\Model\ContentSpec;

class AdminHandlerContentsController extends AbstractAdminController
{
    /**
     * @param type $chid
     *
     * @return \NyroDev\NyroCmsBundle\Model\ContentHandler
     *
     * @throws type
     */
    protected function getContentHandler($chid)
    {
        $contentHandler = $this->get('nyrocms_db')->getContentHandlerRepository()->find($chid);
        if (!$contentHandler) {
            throw $this->createNotFoundException();
        }

        $this->canAdminContentHandler($contentHandler);

        return $contentHandler;
    }

    public function indexAction(Request $request, $chid)
    {
        $ch = $this->getContentHandler($chid);
        $handler = $this->get('nyrocms')->getHandler($ch);

        $repo = $this->get('nyrocms_db')->getContentSpecRepository();
        $qb = $this->get('nyrodev_db')->getQueryBuilder($repo)
                ->addWhere('contentHandler', '=', $ch->getId());

        $route = 'nyrocms_admin_handler_contents';
        $routePrm = array(
            'chid' => $ch->getId(),
        );

        $orderField = $handler->orderField();

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
                array_merge(
                    array(
                        'title' => $ch->getName(),
                        'routePrmAdd' => $routePrm,
                        'routePrmEdit' => $routePrm,
                        'routePrmDelete' => $routePrm,
                        'name' => 'contentSpec',
                        'route' => $route,
                        'fields' => array(
                            'id',
                            'title',
                            $orderField,
                            'updated',
                        ),
                        'moreActions' => array_filter(array(
                            'up' => $handler->hasMoveActions() ? array(
                                'name' => '↑',
                                'route' => 'nyrocms_admin_handler_contents_up',
                                'routePrm' => $routePrm,
                            ) : false,
                            'down' => $handler->hasMoveActions() ? array(
                                'name' => '↓',
                                'route' => 'nyrocms_admin_handler_contents_down',
                                'routePrm' => $routePrm,
                            ) : false,
                            'composer' => $handler->hasComposer() ? array(
                                'name' => $this->get('nyrocms_admin')->getIcon('pencil'),
                                '_blank' => true,
                                'route' => 'nyrocms_admin_composer',
                                'routePrm' => array(
                                    'type' => 'ContentSpec',
                                ),
                            ) : false,
                        )),
                    ),
                    $this->createList($request, $repo, $route, $routePrm, $orderField, $handler->isReversePositionOrder() ? 'desc' : 'asc', null, $qb)
                ));
    }

    public function deleteAction(Request $request, $chid, $id)
    {
        $ch = $this->getContentHandler($chid);

        $repo = $this->get('nyrocms_db')->getContentSpecRepository();
        $row = $repo->find($id);
        if ($row) {
            $row->setService($this->get('nyrodev'));
            $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
            $handler->init($request, true);
            $handler->deleteClb($row);

            $afters = $repo->getAfters($row);

            //$this->get('nyrocms_db')->remove($row);

            $row->setDeleted(new \DateTime());

            foreach ($afters as $after) {
                $after->setPosition(max(0, $after->getPosition() - 1));
            }

            $this->get('nyrocms_db')->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_handler_contents', array('chid' => $ch->getId())));
    }

    public function addAction(Request $request, $chid)
    {
        $ch = $this->getContentHandler($chid);
        $row = $this->get('nyrocms_db')->getNew('content_spec', false);
        $row->setContentHandler($ch);

        return $this->form($request, self::ADD, $row);
    }

    public function editAction(Request $request, $chid, $id)
    {
        $this->getContentHandler($chid);

        $row = $this->get('nyrocms_db')->getContentSpecRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->form($request, self::EDIT, $row);
    }

    public function moveAction($chid, $id, $dir)
    {
        $ch = $this->getContentHandler($chid);

        $repo = $this->get('nyrocms_db')->getContentSpecRepository();
        $row = $repo->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        $handler = $this->get('nyrocms')->getHandler($ch);
        if (!$handler->isReversePositionOrder()) {
            $dir = $dir == 'up' ? 'down' : 'up';
        }

        $position = $row->getPosition();
        if ($dir == 'up') {
            ++$position;
        } elseif ($position > 0) {
            --$position;
        }
        $row->setPosition($position);
        $this->get('nyrocms_db')->flush();

        return $this->redirect($this->generateUrl('nyrocms_admin_handler_contents', array('chid' => $ch->getId())));
    }

    public function form(Request $request, $action, $row)
    {
        $row->setService($this->get('nyrodev'));
        $routePrm = array('chid' => $row->getContentHandler()->getId());
        $moreOptions = array(
            'state' => array(
                'type' => ChoiceType::class,
                'choices' => $this->get('nyrocms_admin')->getContentSpecStateChoices(),
            ),
            'validStart' => $this->get('nyrocms')->getDateFormOptions(),
            'validEnd' => $this->get('nyrocms')->getDateFormOptions(),
            'submit' => array(
                'attr' => array(
                    'data-cancelurl' => $this->container->get('nyrodev')->generateUrl('nyrocms_admin_handler_contents', $routePrm),
                ),
            ),
        );

        $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
        $handler->init($request, true);

        if (!$handler->hasStateInvisible()) {
            unset($moreOptions['state']['choices'][ContentSpec::STATE_INVISIBLE]);
        }

        if ($handler->isIntroRequired()) {
            $moreOptions['intro'] = array(
                'required' => true,
                'constraints' => array(
                    new Constraints\NotBlank(),
                ),
            );
            $this->translationFields['intro']['required'] = true;
        }

        if ($handler->useDateSpec()) {
            $moreOptions['dateSpec'] = $this->get('nyrocms')->getDateFormOptions();
            if ($action == self::ADD) {
                $row->setDateSpec(new \DateTime());
            }
        }

        $fields = array_filter(array(
            'title',
            $handler->hasIntro() ? 'intro' : null,
            $handler->hasFeatured() ? 'featured' : null,
            'state',
            $handler->useDateSpec() ? 'dateSpec' : null,
            $handler->hasValidDates() ? 'validStart' : null,
            $handler->hasValidDates() ? 'validEnd' : null,
        ));

        if (!$handler->hasIntro()) {
            unset($this->translationFields['intro']);
        }

        if ($handler->hasMetas()) {
            $fields[] = 'metaTitle';
            $fields[] = 'metaDescription';
            $fields[] = 'metaKeywords';
            $this->translationFields['metaTitle'] = array(
                'type' => TextType::class,
                'required' => false,
            );
            $this->translationFields['metaDescription'] = array(
                'type' => TextareaType::class,
                'required' => false,
            );
            $this->translationFields['metaKeywords'] = array(
                'type' => TextareaType::class,
                'required' => false,
            );
        }

        if ($handler->hasOgs()) {
            $fields[] = 'ogTitle';
            $fields[] = 'ogDescription';
            $fields[] = 'ogImage';
            $this->translationFields['ogTitle'] = array(
                'type' => TextType::class,
                'required' => false,
            );
            $this->translationFields['ogDescription'] = array(
                'type' => TextareaType::class,
                'required' => false,
            );
        }

        $adminForm = $this->createAdminForm($request, 'contentSpec', $action, $row, $fields, 'nyrocms_admin_handler_contents', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush');
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        $adminForm['title'] = $row->getContentHandler()->getName();

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
    }

    protected $translationFields = array(
        'title' => array(
            'type' => TextType::class,
            'required' => true,
        ),
        'intro' => array(
            'type' => TextareaType::class,
            'required' => false,
        ),
    );
    protected $translations;
    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $contentForm;
    protected function contentFormClb($action, \NyroDev\NyroCmsBundle\Model\ContentSpec $row, \Symfony\Component\Form\FormBuilder $form)
    {
        $langs = $this->get('nyrocms')->getLocaleNames($row);
        $defaultLocale = $this->get('nyrocms')->getDefaultLocale($row);
        unset($langs[$defaultLocale]);

        $this->translations = array();
        foreach ($row->getTranslations() as $tr) {
            if (!isset($this->translations[$tr->getLocale()])) {
                $this->translations[$tr->getLocale()] = array();
            }
            $this->translations[$tr->getLocale()][$tr->getField()] = $tr;
        }

        /* @var $form \Ivory\OrderedForm\Builder\OrderedFormBuilder */

        $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());

        if ($handler->needTranslations()) {
            $propertyAccess = PropertyAccess::createPropertyAccessor();
            foreach ($langs as $lg => $lang) {
                foreach ($this->translationFields as $field => $options) {
                    $type = $options['type'];
                    unset($options['type']);
                    $fieldName = 'lang_'.$lg.'_'.$field;

                    if (isset($options['required']) && $options['required']) {
                        $options['constraints'] = array(new Constraints\NotBlank());
                    }

                    $form->add($fieldName, $type, array_merge($options, array(
                        'label' => $this->trans('admin.contentSpec.'.$field).' '.strtoupper($lg),
                        'mapped' => false,
                        'data' => isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
                        'position' => array('after' => $field),
                    )));
                }
            }
        }

        $handler->formClb($action, $row, $form, $langs, $this->translations);
    }
    protected function contentFlush($action, $row, $form)
    {
        $this->contentForm = $form;
        $this->get('nyrocms')->getHandler($row->getContentHandler())->flushClb($action, $row, $form);
    }

    protected function contentAfterFlush($response, $action, $row)
    {
        $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
        $handler->afterFlushClb($response, $action, $row);

        if ($handler->needTranslations()) {
            $langs = $this->get('nyrocms')->getLocaleNames($row);
            $defaultLocale = $this->get('nyrocms')->getDefaultLocale($row);
            unset($langs[$defaultLocale]);

            $om = $this->get('nyrocms_db')->getObjectManager();
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
