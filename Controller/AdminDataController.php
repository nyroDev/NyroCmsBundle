<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Event\AdminFormEvent;
use NyroDev\NyroCmsBundle\Form\Type\ContentHandlerFilterType;
use NyroDev\NyroCmsBundle\Form\Type\UserFilterType;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface;
use NyroDev\NyroCmsBundle\Services\Db\AbstractService;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Services\Db\AbstractService as nyroDevDbService;
use NyroDev\UtilityBundle\Services\MainService as nyroDevService;
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
        $repo = $this->get(AbstractService::class)->getContentRepository();

        $parent = $id ? $repo->find($id) : $repo->findOneBy(array('level' => 0));
        if (!$parent) {
            throw $this->createNotFoundException();
        }

        $this->get('nyrocms_admin')->setContentParentId($parent->getVeryParent()->getId());

        $canAdminContent = $this->get('nyrocms_admin')->canAdminContent($parent);
        if ($canAdminContent && $request->isMethod('post')) {
            $tree = $request->request->get('tree');
            $treeLevel = $request->request->get('treeLevel');
            $treeChanged = $request->request->get('treeChanged');

            $contents = array();
            foreach ($repo->children($parent) as $c) {
                $contents[$c->getId()] = $c;
            }

            $lastChild = array(
                0 => $parent,
            );
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
                    $this->get(AbstractService::class)->flush();
                }
            }

            return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(array('id' => $id)));
        }

        return $this->render('NyroDevNyroCmsBundle:AdminData:contentTree.html.php', array(
            'parent' => $parent,
            'candDirectAdd' => $canAdminContent && $this->get('nyrocms_admin')->canHaveSub($parent),
        ));
    }

    public function contentFixAction($id = null)
    {
        $repo = $this->get(AbstractService::class)->getContentRepository();

        $repo->verify();
        $repo->recover();
        $this->get(AbstractService::class)->flush();

        $parent = $id ? $repo->find($id) : $repo->findOneBy(array('level' => 0));
        if (!$parent) {
            throw $this->createNotFoundException();
        }

        foreach ($repo->children($parent) as $update) {
            $this->get('nyrocms_admin')->updateContentUrl($update, false, false);
        }

        $this->get(AbstractService::class)->flush();

        return $this->redirectToRoute('nyrocms_admin_data_content_tree', array_filter(array('id' => $id)));
    }

    public function contentTreeSubAction(\NyroDev\NyroCmsBundle\Model\Content $parent = null)
    {
        $route = 'nyrocms_admin_data_content';

        return $this->render('NyroDevNyroCmsBundle:AdminData:contentTreeSub.html.php', array(
            'route' => $route,
            'parent' => $parent,
            'canEditParent' => $this->get('nyrocms_admin')->canAdminContent($parent),
            'canHaveSub' => $this->get('nyrocms_admin')->canHaveSub($parent),
            'contents' => $this->get(AbstractService::class)->getContentRepository()->children($parent, true),
        ));
    }

    public function contentDeleteAction($id)
    {
        $row = $this->get(AbstractService::class)->getContentRepository()->find($id);
        if ($row && !$row->getHandler() && true === $this->get('nyrocms_admin')->canAdminContent($row)) {
            $this->get(AbstractService::class)->remove($row);
            $this->get(AbstractService::class)->flush();
        }

        return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(array('id' => $row->getRoot())));
    }

    public function contentAddAction(Request $request, $pid = null)
    {
        $row = $this->get(AbstractService::class)->getNew('content', false);

        if ($pid) {
            $parent = $this->get(AbstractService::class)->getContentRepository()->find($pid);
            if (!$parent || !$this->get('nyrocms_admin')->canAdminContent($parent)) {
                throw $this->createNotFoundException();
            }
            $row->setParent($parent);
            $this->get('nyrocms_admin')->setContentParentId($parent->getVeryParent()->getId());
        }

        return $this->contentForm($request, self::ADD, $row);
    }

    public function contentEditAction(Request $request, $id)
    {
        $row = $this->get(AbstractService::class)->getContentRepository()->find($id);
        if (!$row || true === !$this->get('nyrocms_admin')->canAdminContent($row)) {
            throw $this->createNotFoundException();
        }
        $this->get('nyrocms_admin')->setContentParentId($row->getVeryParent()->getId());

        return $this->contentForm($request, self::EDIT, $row);
    }

    public function contentForm($request, $action, $row)
    {
        $routePrm = array(
            'id' => $row->getVeryParent()->getId(),
        );

        $themes = $this->get('nyrocms_composer')->getThemes($row->getParent());
        $moreOptions = array(
            'theme' => array(
                'type' => ChoiceType::class,
                'choices' => $themes,
            ),
            'state' => array(
                'type' => ChoiceType::class,
                'choices' => $this->get('nyrocms_admin')->getContentStateChoices(),
            ),
            'relateds' => array(
                'choice_label' => function ($row) {
                    return $row.''.($row->getParent() ? ' ('.$row->getParent().')' : '');
                },
                'query_builder' => function (ContentRepositoryInterface $er) use ($row) {
                    return $er->getFormQueryBuilder($row->getParent()->getRoot(), $row->getId());
                },
                'attr' => array(
                    'class' => 'autocompleteSelMul',
                    'placeholder' => $this->trans('admin.content.relatedsPlaceholder'),
                ),
            ),
            'submit' => array(
                'attr' => array(
                    'data-cancelurl' => $this->container->get(nyroDevService::class)->generateUrl('nyrocms_admin_data_content_tree', $routePrm),
                ),
            ),
        );

        $fields = array_filter(array(
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
        ));

        if ($this->get('nyrocms_admin')->isDeveloper()) {
            $fields[] = 'contentHandler';
            $fields[] = 'menuOption';
            $repoContentHandler = $this->get(AbstractService::class)->getContentHandlerRepository();
            $moreOptions['contentHandler'] = array(
                'query_builder' => function ($er) use ($repoContentHandler) {
                    return $repoContentHandler->getFormQueryBuilder();
                },
            );
        }

        if ($row instanceof AbstractUploadable) {
            $row->setService($this->get(nyroDevService::class));
        }

        $adminForm = $this->createAdminForm($request, 'content', $action, $row, $fields, 'nyrocms_admin_data_content_tree', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush', $this->get(AbstractService::class)->getObjectManager());
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
    }

    protected $contentTranslationFields = array(
        'title' => array(
            'type' => TextType::class,
            'required' => true,
        ),
        'goUrl' => array(
            'type' => UrlType::class,
            'required' => false,
        ),
        'metaTitle' => array(
            'type' => TextType::class,
            'required' => false,
        ),
        'metaDescription' => array(
            'type' => TextareaType::class,
            'required' => false,
        ),
        'metaKeywords' => array(
            'type' => TextareaType::class,
            'required' => false,
        ),
        'ogTitle' => array(
            'type' => TextType::class,
            'required' => false,
        ),
        'ogDescription' => array(
            'type' => TextareaType::class,
            'required' => false,
        ),
    );

    protected $translations;
    protected $langs;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $contentForm;

    protected function contentFormClb($action, \NyroDev\NyroCmsBundle\Model\Content $row, \Symfony\Component\Form\FormBuilder $form)
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

        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($langs as $lg => $lang) {
            foreach ($this->contentTranslationFields as $field => $options) {
                if ($form->has($field)) {
                    $type = $options['type'];
                    unset($options['type']);
                    $fieldName = 'lang_'.$lg.'_'.$field;

                    if (isset($options['required']) && $options['required']) {
                        $options['constraints'] = array(new Constraints\NotBlank());
                    }

                    $form->add($fieldName, $type, array_merge($options, array(
                        'label' => $this->trans('admin.content.'.$field).' '.strtoupper($lg),
                        'mapped' => false,
                        'data' => isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
                        'position' => array('after' => $field),
                    )));
                }
            }
        }

        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch(AdminFormEvent::UPDATE_CONTENT, $adminFormEvent);
    }

    protected function contentFlush($action, $row, $form)
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch(AdminFormEvent::BEFOREFLUSH_CONTENT, $adminFormEvent);

        $this->contentForm = $form;
        $this->get('nyrocms_admin')->updateContentUrl($row, self::EDIT == $action);
    }

    protected function contentAfterFlush($response, $action, $row)
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $this->contentForm);
        $adminFormEvent->setTranslations($this->translations);
        $this->get('event_dispatcher')->dispatch(AdminFormEvent::AFTERFLUSH_CONTENT, $adminFormEvent);

        $langs = $this->get('nyrocms')->getLocaleNames($row);
        $defaultLocale = $this->get('nyrocms')->getDefaultLocale($row);
        unset($langs[$defaultLocale]);

        $om = $this->get(AbstractService::class)->getObjectManager();
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

            $this->get('nyrocms_admin')->updateContentUrl($row, self::EDIT == $action);
            $om->flush();
        }
    }

    ///////////////////////////////////////////////////////////////
    // Users Roles

    public function userRoleAction(Request $request)
    {
        $isDev = $this->get('nyrocms_admin')->isDeveloper();

        $repo = $this->get(AbstractService::class)->getUserRoleRepository();
        $qb = $this->get(nyroDevDbService::class)->getQueryBuilder($repo);
        if (!$isDev) {
            $qb->addWhere('internal', '<>', 1);
        }
        $route = 'nyrocms_admin_data_userRole';

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
                array_merge(
                    array(
                        'name' => 'userRole',
                        'route' => $route,
                        'fields' => array_filter(array(
                            'id',
                            'name',
                            $isDev ? 'roleName' : null,
                            $isDev ? 'internal' : null,
                            'updated',
                        )),
                    ),
                    $this->createList($request, $repo, $route, array(), 'id', 'desc', null, $qb)
                ));
    }

    public function userRoleDeleteAction($id)
    {
        $row = $this->get(AbstractService::class)->getUserRoleRepository()->find($id);
        if ($row) {
            $this->get(AbstractService::class)->remove($row);
            $this->get(AbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_userRole'));
    }

    public function userRoleAddAction(Request $request)
    {
        $row = $this->get(AbstractService::class)->getNew('user_role', false);

        return $this->userRoleForm($request, self::ADD, $row);
    }

    public function userRoleEditAction(Request $request, $id)
    {
        $row = $this->get(AbstractService::class)->getUserRoleRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->userRoleForm($request, self::EDIT, $row);
    }

    public function userRoleForm(Request $request, $action, $row)
    {
        $moreOptions = array(
            'contents' => $this->get('nyrocms_admin')->getContentsChoiceTypeOptions($this->getParameter('nyroCms.user_roles.maxlevel_content')),
            'submit' => array(
                'attr' => array(
                    'data-cancelurl' => $this->container->get(nyroDevService::class)->generateUrl('nyrocms_admin_data_userRole'),
                ),
            ),
        );

        $isDev = $this->get('nyrocms_admin')->isDeveloper();
        $adminForm = $this->createAdminForm($request, 'userRole', $action, $row, array_filter(array(
                    'name',
                    $isDev ? 'roleName' : null,
                    $isDev ? 'internal' : null,
                    'contents',
                )), 'nyrocms_admin_data_userRole', array(), null, null, null, $moreOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
    }

    ///////////////////////////////////////////////////////////////
    // contentHandlers

    public function contentHandlerAction(Request $request)
    {
        $repo = $this->get(AbstractService::class)->getContentHandlerRepository();

        $route = 'nyrocms_admin_data_contentHandler';

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
                array_merge(
                    array(
                        'name' => 'contentHandler',
                        'route' => $route,
                        'fields' => array(
                            'id',
                            'name',
                            'class',
                            'hasAdmin',
                            'updated',
                        ),
                    ),
                    $this->createList($request, $repo, $route, array(), 'name', 'asc', ContentHandlerFilterType::class)
                ));
    }

    public function contentHandlerDeleteAction($id)
    {
        $row = $this->get(AbstractService::class)->getContentHandlerRepository()->find($id);
        if ($row) {
            $this->get(AbstractService::class)->remove($row);
            $this->get(AbstractService::class)->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_contentHandler'));
    }

    public function contentHandlerAddAction(Request $request)
    {
        $row = $this->get(AbstractService::class)->getNew('content_handler', false);

        return $this->contentHandlerForm($request, self::ADD, $row);
    }

    public function contentHandlerEditAction(Request $request, $id)
    {
        $row = $this->get(AbstractService::class)->getContentHandlerRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->contentHandlerForm($request, self::EDIT, $row);
    }

    public function contentHandlerForm(Request $request, $action, $row)
    {
        $classes = $this->get('nyrocms')->getFoundHandlers();

        $moreOptions = array(
            'class' => array(
                'constraints' => array(
                    new Constraints\Callback(array(
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
                    )),
                ),
            ),
            'submit' => array(
                'attr' => array(
                    'data-cancelurl' => $this->container->get(nyroDevService::class)->generateUrl('nyrocms_admin_data_contentHandler'),
                ),
            ),
        );

        if (count($classes)) {
            $moreOptions['class']['type'] = ChoiceType::class;
            $moreOptions['class']['choices'] = array_combine($classes, $classes);
        }

        $adminForm = $this->createAdminForm($request, 'contentHandler', $action, $row, array(
                    'name',
                    'class',
                    'hasAdmin',
                ), 'nyrocms_admin_data_contentHandler', array(), null, null, null, $moreOptions);
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
    }

    ///////////////////////////////////////////////////////////////
    // Contact Messages

    public function contactMessageAction(Request $request, $chid)
    {
        $contentHandler = $this->get(AbstractService::class)->getContentHandlerRepository()->find($chid);
        if (!$contentHandler) {
            throw $this->createNotFoundException();
        }

        $this->canAdminContentHandler($contentHandler);

        $handler = $this->get('nyrocms')->getHandler($contentHandler);

        $repo = $this->get(AbstractService::class)->getRepository('contact_message');

        $qb = $this->get(nyroDevDbService::class)->getQueryBuilder($repo);
        $qb->addWhere('contentHandler', '=', $contentHandler->getId());

        $exportConfig = array(
            'title' => $this->trans('admin.contactMessage.viewTitle'),
            'prefix' => 'contactMessage',
            'fields' => $handler->getAdminMessageExportFields(),
        );

        $route = 'nyrocms_admin_data_contactMessage';
        $routePrm = array('chid' => $chid);

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
                array_merge(
                    array(
                        'name' => 'contactMessage',
                        'route' => $route,
                        'fields' => $handler->getAdminMessageListFields(),
                        'moreGlobalActions' => array(
                            'export' => array(
                                'route' => $route,
                                'routePrm' => array_merge($routePrm, array('export' => 1)),
                                'name' => $this->trans('admin.contactMessage.export'),
                                'attrs' => 'target="_blank"',
                            ),
                        ),
                        'noAdd' => true,
                        'noActions' => true,
                    ),
                    $this->createList($request, $repo, $route, $routePrm, 'id', 'desc', $handler->getAdminMessageFilterType(), $qb, $exportConfig)
                ));
    }

    ///////////////////////////////////////////////////////////////
    // Users

    public function userAction(Request $request)
    {
        $route = 'nyrocms_admin_data_user';

        $repo = $this->get(AbstractService::class)->getUserRepository();
        $filter = UserFilterType::class;

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
                array_merge(
                    array(
                        'name' => 'user',
                        'route' => $route,
                        'fields' => array(
                            'id',
                            'email',
                            'firstname',
                            'lastname',
                            'userType',
                            'valid',
                            'updated',
                        ),
                    ),
                    $this->createList($request, $repo, $route, array(), 'id', 'desc', $filter)
                ));
    }

    public function userDeleteAction($id)
    {
        $row = $this->get(AbstractService::class)->getUserRepository()->find($id);
        if ($row) {
            $this->getDoctrine()->getManager()->remove($row);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->generateUrl('nyrocms_admin_data_user'));
    }

    public function userAddAction(Request $request)
    {
        $row = $this->get(AbstractService::class)->getNew('user', false);

        return $this->userForm($request, self::ADD, $row);
    }

    public function userEditAction(Request $request, $id)
    {
        $row = $this->get(AbstractService::class)->getUserRepository()->find($id);
        if (!$row) {
            throw $this->createNotFoundException();
        }

        return $this->userForm($request, self::EDIT, $row);
    }

    public function userForm(Request $request, $action, $row)
    {
        $moreOptions = array(
            'userType' => array(
                'type' => ChoiceType::class,
                'placeholder' => '',
                'choices' => $this->get('nyrocms_admin')->getUserTypeChoices(),
            ),
            'validStart' => $this->get('nyrocms')->getDateFormOptions(),
            'validEnd' => $this->get('nyrocms')->getDateFormOptions(),
            'userRoles' => array(
                'expanded' => true,
                'query_builder' => function (UserRoleRepositoryInterface $er) {
                    return $er->getFormQueryBuilder();
                },
            ),
            'submit' => array(
                'attr' => array(
                    'data-cancelurl' => $this->container->get(nyroDevService::class)->generateUrl('nyrocms_admin_data_user'),
                ),
            ),
        );

        $adminForm = $this->createAdminForm($request, 'user', $action, $row, array_filter(array(
                    'email',
                    'firstname',
                    'lastname',
                    //'password',
                    'userType',
                    'valid',
                    'validStart',
                    'validEnd',
                    $this->get('nyrocms_admin')->isDeveloper() ? 'developper' : null,
                    'userRoles',
                )), 'nyrocms_admin_data_user', array(), 'userFormUpdate', 'userFormFlush', null, $moreOptions, 'userFormAfterFlush');
        if (!is_array($adminForm)) {
            return $adminForm;
        }

        return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
    }

    protected $origUserPassword;

    protected function userFormUpdate($action, $row, \Symfony\Component\Form\FormBuilder $form)
    {
        $this->origUserPassword = $row->getPassword();
        $form->get('valid')->setRequired(false);

        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $this->get('event_dispatcher')->dispatch(AdminFormEvent::UPDATE_USER, $adminFormEvent);
    }

    protected function userFormFlush($action, $row, \Symfony\Component\Form\Form $form)
    {
        $adminFormEvent = new AdminFormEvent($action, $row, $form);
        $this->get('event_dispatcher')->dispatch(AdminFormEvent::BEFOREFLUSH_USER, $adminFormEvent);
    }

    protected function userFormAfterFlush($response, $action, $row)
    {
        $adminFormEvent = new AdminFormEvent($action, $row);
        $this->get('event_dispatcher')->dispatch(AdminFormEvent::AFTERFLUSH_USER, $adminFormEvent);

        if (self::ADD == $action) {
            $this->get('nyrocms_user')->handleAddUser($row);
        }
    }
}
