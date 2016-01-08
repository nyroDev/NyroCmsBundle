<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use NyroDev\NyroCmsBundle\Form\Type\UserFilterType;
use NyroDev\NyroCmsBundle\Form\Type\ContentHandlerFilterType;
use NyroDev\NyroCmsBundle\Event\AdminFormEvent;

class AdminDataController extends AbstractAdminController {
	
	///////////////////////////////////////////////////////////////
	// Contents
	
	public function contentAction() {
		return $this->redirectToRoute('nyrocms_admin_data_content_tree');
	}
	
	public function contentTreeAction(Request $request, $id = null) {
		$repo = $this->get('nyrocms_db')->getContentRepository();
		
		$parent = $id ? $repo->find($id) : $repo->findOneBy(array('level'=>0));
		if (!$parent)
			throw $this->createNotFoundException();
		
		$this->get('nyrocms_admin')->setContentParentId($parent->getVeryParent()->getId());
		
		$canAdminContent = $this->get('nyrocms_admin')->canAdminContent($parent);
		if ($canAdminContent && $request->isMethod('post')) {
			$tree = $request->request->get('tree');
			$treeLevel = $request->request->get('treeLevel');
			$treeChanged = $request->request->get('treeChanged');
			
			$contents = array();
			foreach($repo->children($parent) as $c)
				$contents[$c->getId()] = $c;
			
			$lastChild = array(
				0=>$parent
			);
			$lastLevel = null;
			if (count($tree)) {
				foreach($tree as $t) {
					if (!isset($contents[$t]) || !isset($treeLevel[$t]) || !isset($treeChanged[$t]))
						throw new \InvalidArgumentException('Unknown content Id: '.$t);

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
					$this->get('nyrocms_db')->flush();
				}
			}
			
			return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(array('id'=>$id)));
		}
		
		return $this->render('NyroDevNyroCmsBundle:AdminData:contentTree.html.php', array(
			'parent'=>$parent,
			'candDirectAdd'=>$canAdminContent && $this->get('nyrocms_admin')->canHaveSub($parent)
		));
	}
	
	public function contentFixAction($id = null) {
		$repo = $this->get('nyrocms_db')->getContentRepository();
		
		$repo->verify();
		$repo->recover();
		$this->get('nyrocms_db')->flush();
		
		$parent = $id ? $repo->find($id) : $repo->findOneBy(array('level'=>0));
		if (!$parent)
			throw $this->createNotFoundException();
		
		foreach($repo->children($parent) as $update)
			$this->get('nyrocms_admin')->updateContentUrl($update, false, false);

		$this->get('nyrocms_db')->flush();
		
		return $this->redirectToRoute('nyrocms_admin_data_content_tree', array_filter(array('id'=>$id)));
	}
	
	public function contentTreeSubAction(\NyroDev\NyroCmsBundle\Model\Content $parent = null) {
		$route = 'nyrocms_admin_data_content';
		return $this->render('NyroDevNyroCmsBundle:AdminData:contentTreeSub.html.php', array(
			'route'=>$route,
			'parent'=>$parent,
			'canEditParent'=>$this->get('nyrocms_admin')->canAdminContent($parent),
			'canHaveSub'=>$this->get('nyrocms_admin')->canHaveSub($parent),
			'contents'=>$this->get('nyrocms_db')->getContentRepository()->children($parent, true)
		));
	}
	
	public function contentDeleteAction($id) {
		$row = $this->get('nyrocms_db')->getContentRepository()->find($id);
		if ($row && !$row->getHandler() && $this->get('nyrocms_admin')->canAdminContent($row) === true) {
			$this->get('nyrocms_db')->remove($row);
			$this->get('nyrocms_db')->flush();
		}
		return $this->redirectToRoute('nyrocms_admin_data_content_fix', array_filter(array('id'=>$row->getRoot())));
	}
	
	public function contentAddAction(Request $request, $pid = null) {
		$row = $this->get('nyrocms_db')->getNew('content', false);
		
		if ($pid) {
			$parent = $this->get('nyrocms_db')->getContentRepository()->find($pid);
			if (!$parent || !$this->get('nyrocms_admin')->canAdminContent($parent))
				throw $this->createNotFoundException();
			$row->setParent($parent);
			$this->get('nyrocms_admin')->setContentParentId($parent->getVeryParent()->getId());
		}
		
		return $this->contentForm($request, self::ADD, $row);
	}
	
	public function contentEditAction(Request $request, $id) {
		$row = $this->get('nyrocms_db')->getContentRepository()->find($id);
		if (!$row || !$this->get('nyrocms_admin')->canAdminContent($row) === true)
			throw $this->createNotFoundException();
		$this->get('nyrocms_admin')->setContentParentId($row->getVeryParent()->getId());
		return $this->contentForm($request, self::EDIT, $row);
	}
	
	public function contentForm($request, $action, $row) {
		
		$routePrm = array(
			'id'=>$row->getVeryParent()->getId()
		);
		
		$themes = $this->get('nyrocms_composer')->getThemes($row->getParent());
		$moreOptions = array(
			'theme'=>array(
				'type'=>ChoiceType::class,
				'choices'=>$themes
			),
			'state'=>array(
				'type'=>ChoiceType::class,
				'choices'=>$this->get('nyrocms_admin')->getContentStateChoices()
			),
			'relateds'=>array(
				'choice_label'=>function($row) {
					return $row.''.($row->getParent() ? ' ('.$row->getParent().')' : '');
				},
				'query_builder'=>function(ContentRepositoryInterface $er) use($row) {
					return $er->getFormQueryBuilder($row->getParent()->getRoot(), $row->getId());
				},
				'attr'=>array(
					'class'=>'autocompleteSelMul',
					'placeholder'=>$this->trans('admin.content.relatedsPlaceholder'),
				)
			),
			'submit'=>array(
				'attr'=>array(
					'data-cancelurl'=>$this->container->get('nyrodev')->generateUrl('nyrocms_admin_data_content_tree', $routePrm)
				)
			),
		);
		
		$fields = array_filter(array(
			'title',
			count($themes) > 1 ? 'theme' : null,
			'state',
			'goUrl',
			'goBlank',
			'relateds'
		));
		
		if ($this->get('nyrocms_admin')->isDeveloper()) {
			$fields[] = 'contentHandler';
			$fields[] = 'menuOption';
			$repoContentHandler = $this->get('nyrocms_db')->getContentHandlerRepository();
			$moreOptions['contentHandler'] = array(
				'query_builder'=>function($er) use ($repoContentHandler) {
					return $repoContentHandler->getFormQueryBuilder();
				}
			);
		}
		
		$adminForm = $this->createAdminForm($request, 'content', $action, $row, $fields, 'nyrocms_admin_data_content_tree', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush', $this->get('nyrocms_db')->getObjectManager());
		if (!is_array($adminForm))
			return $adminForm;
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
	}
	protected $contentTranslationFields = array(
		'title'=>array(
			'type'=>TextType::class,
			'required'=>true,
		),
	);
	protected $translations;
	/**
	 *
	 * @var \Symfony\Component\Form\Form
	 */
	protected $contentForm;
	protected function contentFormClb($action, \NyroDev\NyroCmsBundle\Model\Content $row, \Symfony\Component\Form\FormBuilder $form) {
		$langs = $this->get('nyrocms')->getLocaleNames($row);
		$defaultLocale = $this->get('nyrocms')->getDefaultLocale($row);
		unset($langs[$defaultLocale]);
		
		$this->translations = array();
		foreach($row->getTranslations() as $tr) {
			if (!isset($this->translations[$tr->getLocale()]))
				$this->translations[$tr->getLocale()] = array();
			$this->translations[$tr->getLocale()][$tr->getField()] = $tr;
		}
		
		/* @var $form \Ivory\OrderedForm\Builder\OrderedFormBuilder */
		
		$propertyAccess = PropertyAccess::createPropertyAccessor();
		foreach($langs as $lg=>$lang) {
			foreach($this->contentTranslationFields as $field=>$options) {
				$type = $options['type'];
				unset($options['type']);
				$fieldName = 'lang_'.$lg.'_'.$field;
				
				if (isset($options['required']) && $options['required'])
					$options['constraints'] = array(new Constraints\NotBlank());
				
				$form->add($fieldName, $type, array_merge($options, array(
					'label'=>$this->trans('admin.content.'.$field).' '.strtoupper($lg),
					'mapped'=>false,
					'data'=>isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
					'position'=>array('after'=>$field)
				)));
			}
		}
		
		$adminFormEvent = new AdminFormEvent($action, $row, $form);
		$this->get('event_dispatcher')->dispatch(AdminFormEvent::UPDATE_CONTENT, $adminFormEvent);
	}
	protected function contentFlush($action, $row, $form) {
		$this->contentForm = $form;
		$this->get('nyrocms_admin')->updateContentUrl($row, $action == self::EDIT);
	}
	
	protected function contentAfterFlush($response, $action, $row) {
		$langs = $this->get('nyrocms')->getLocaleNames($row);
		$defaultLocale = $this->get('nyrocms')->getDefaultLocale($row);
		unset($langs[$defaultLocale]);
		
		$om = $this->get('nyrocms_db')->getObjectManager();
		$propertyAccess = PropertyAccess::createPropertyAccessor();
		
		foreach($langs as $lg=>$lang) {
			$row->setTranslatableLocale($lg);
			$om->refresh($row);
			
			foreach($this->contentTranslationFields as $field=>$options) {
				$fieldName = 'lang_'.$lg.'_'.$field;
				$propertyAccess->setValue($row, $field, $this->contentForm->get($fieldName)->getData());
			}
			$this->get('nyrocms_admin')->updateContentUrl($row, $action == self::EDIT);
			$om->flush();
		}
	}
	
	
	///////////////////////////////////////////////////////////////
	// Users Roles
	
	public function userRoleAction(Request $request) {
		$isDev = $this->get('nyrocms_admin')->isDeveloper();
		
		$repo = $this->get('nyrocms_db')->getUserRoleRepository();
		$qb = $this->get('nyrodev_db')->getQueryBuilder($repo);
		if (!$isDev)
			$qb->addWhere('internal' ,'<>', 1);
		$route = 'nyrocms_admin_data_userRole';
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
				array_merge(
					array(
						'name'=>'userRole',
						'route'=>$route,
						'fields'=>array_filter(array(
							'id',
							'name',
							$isDev ? 'roleName' : null,
							$isDev ? 'internal' : null,
							'updated'
						))
					),
					$this->createList($request, $repo, $route, array(), 'id', 'desc', null, $qb)
				));
	}
	
	public function userRoleDeleteAction($id) {
		$row = $this->get('nyrocms_db')->getUserRoleRepository()->find($id);
		if ($row) {
			$this->get('nyrocms_db')->remove($row);
			$this->get('nyrocms_db')->flush();
		}
		return $this->redirect($this->generateUrl('nyrocms_admin_data_userRole'));
	}
	
	public function userRoleAddAction(Request $request) {
		$row = $this->get('nyrocms_db')->getNew('user_role', false);
		return $this->userRoleForm($request, self::ADD, $row);
	}
	
	public function userRoleEditAction(Request $request, $id) {
		$row = $this->get('nyrocms_db')->getUserRoleRepository()->find($id);
		if (!$row)
			throw $this->createNotFoundException();
		return $this->userRoleForm($request, self::EDIT, $row);
	}
	
	public function userRoleForm(Request $request, $action, $row) {
		$moreOptions = array(
			'contents'=>$this->get('nyrocms_admin')->getContentsChoiceTypeOptions($this->getParameter('nyroCms.user_roles.maxlevel_content')),
			'submit'=>array(
				'attr'=>array(
					'data-cancelurl'=>$this->container->get('nyrodev')->generateUrl('nyrocms_admin_data_userRole')
				)
			),
		);
		
		$isDev = $this->get('nyrocms_admin')->isDeveloper();
		$adminForm = $this->createAdminForm($request, 'userRole', $action, $row, array_filter(array(
					'name',
					$isDev ? 'roleName' : null,
					$isDev ? 'internal' : null,
					'contents',
				)), 'nyrocms_admin_data_userRole', array(), null, null, null, $moreOptions);
		if (!is_array($adminForm))
			return $adminForm;
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
	}
	
	
	///////////////////////////////////////////////////////////////
	// contentHandlers
	
	public function contentHandlerAction(Request $request) {
		$repo = $this->get('nyrocms_db')->getContentHandlerRepository();
		
		$route = 'nyrocms_admin_data_contentHandler';
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
				array_merge(
					array(
						'name'=>'contentHandler',
						'route'=>$route,
						'fields'=>array(
							'id',
							'name',
							'class',
							'hasAdmin',
							'updated'
						)
					),
					$this->createList($request, $repo, $route, array(), 'name', 'asc', ContentHandlerFilterType::class)
				));
	}
	
	public function contentHandlerDeleteAction($id) {
		$row = $this->get('nyrocms_db')->getContentHandlerRepository()->find($id);
		if ($row) {
			$this->get('nyrocms_db')->remove($row);
			$this->get('nyrocms_db')->flush();
		}
		return $this->redirect($this->generateUrl('nyrocms_admin_data_contentHandler'));
	}
	
	public function contentHandlerAddAction(Request $request) {
		$row = $this->get('nyrocms_db')->getNew('content_handler', false);
		return $this->contentHandlerForm($request, self::ADD, $row);
	}
	
	public function contentHandlerEditAction(Request $request, $id) {
		$row = $this->get('nyrocms_db')->getContentHandlerRepository()->find($id);
		if (!$row)
			throw $this->createNotFoundException();
		return $this->contentHandlerForm($request, self::EDIT, $row);
	}
	
	public function contentHandlerForm(Request $request, $action, $row) {
		
		$classes = $this->get('nyrocms')->getFoundHandlers();
		
		$moreOptions = array(
			'class'=>array(
				'constraints'=>array(
					new Constraints\Callback(array(
						'callback'=>function($data, $context) {
							if ($data) {
								if (!class_exists($data)) {
									/* @var $context \Symfony\Component\Validator\Context\ExecutionContext */
									$context->addViolation('Class doesn\'t exist.');
								} else if (!is_subclass_of($data, '\NyroDev\NyroCmsBundle\Handler\AbstractHandler')) {
									$context->addViolation('Class is not a valid AbstractHandler.');
								}
							}
						}
					))
				)
			),
			'submit'=>array(
				'attr'=>array(
					'data-cancelurl'=>$this->container->get('nyrodev')->generateUrl('nyrocms_admin_data_contentHandler')
				)
			)
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
		if (!is_array($adminForm))
			return $adminForm;
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
	}
	
	
	
	///////////////////////////////////////////////////////////////
	// Users
	
	public function userAction(Request $request) {
		$route = 'nyrocms_admin_data_user';
		
		$repo = $this->get('nyrocms_db')->getUserRepository();
		$filter = UserFilterType::class;
		
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
				array_merge(
					array(
						'name'=>'user',
						'route'=>$route,
						'fields'=>array(
							'id',
							'email',
							'firstname',
							'lastname',
							'userType',
							'valid',
							'updated'
						)
					),
					$this->createList($request, $repo, $route, array(), 'id', 'desc', $filter)
				));
	}
	
	public function userDeleteAction($id) {
		$row = $this->get('nyrocms_db')->getUserRepository()->find($id);
		if ($row) {
			$this->getDoctrine()->getManager()->remove($row);
			$this->getDoctrine()->getManager()->flush();
		}
		return $this->redirect($this->generateUrl('nyrocms_admin_data_user'));
	}
	
	public function userAddAction(Request $request) {
		$row = $this->get('nyrocms_db')->getNew('user', false);
		return $this->userForm($request, self::ADD, $row);
	}
	
	public function userEditAction(Request $request, $id) {
		$row = $this->get('nyrocms_db')->getUserRepository()->find($id);
		if (!$row)
			throw $this->createNotFoundException();
		return $this->userForm($request, self::EDIT, $row);
	}
	
	public function userForm(Request $request, $action, $row) {
		$moreOptions = array(
			'userType'=>array(
				'type'=>ChoiceType::class,
				'placeholder'=>'',
				'choices'=>$this->get('nyrocms_admin')->getUserTypeChoices()
			),
			'validStart'=>$this->get('nyrocms')->getDateFormOptions(),
			'validEnd'=>$this->get('nyrocms')->getDateFormOptions(),
			'userRoles'=>array(
				'expanded'=>true,
				'query_builder'=>function(UserRoleRepositoryInterface $er) {
					return $er->getFormQueryBuilder();
				}
			),
			'submit'=>array(
				'attr'=>array(
					'data-cancelurl'=>$this->container->get('nyrodev')->generateUrl('nyrocms_admin_data_user')
				)
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
		if (!is_array($adminForm))
			return $adminForm;
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
	}
	
	protected $origUserPassword;
	
	protected function userFormUpdate($action, $row, \Symfony\Component\Form\FormBuilder $form) {
		$this->origUserPassword = $row->getPassword();
		$form->get('valid')->setRequired(false);
		
		$adminFormEvent = new AdminFormEvent($action, $row, $form);
		$this->get('event_dispatcher')->dispatch(AdminFormEvent::UPDATE_USER, $adminFormEvent);
	}
	
	protected function userFormFlush($action, $row, \Symfony\Component\Form\Form $form) {
	}
	
	protected function userFormAfterFlush($response, $action, $row) {
		if ($action == self::ADD)
			$this->get('nyrocms_user')->handleAddUser($row);
	}

}