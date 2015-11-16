<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AdminHandlerContentsController extends AbstractAdminController {
	
	/**
	 * 
	 * @param type $chid
	 * @return \Sis\DbBundle\Entity\ContentHandler
	 * @throws type
	 */
	protected function getContentHandler($chid) {
		$contentHandler = $this->get('nyrocms_db')->getContentHandlerRepository()->find($chid);
		if (!$contentHandler)
			throw $this->createNotFoundException();
		
		$this->canAdminContentHandler($contentHandler);
		
		return $contentHandler;
	}
	
	public function indexAction($chid) {
		$ch = $this->getContentHandler($chid);
		$handler = $this->get('nyrocms')->getHandler($ch);
		
		$repo = $this->get('nyrocms_db')->getContentSpecRepository();
		$qb = $repo->getAdminListQueryBuilder($ch);
		
		$route = 'nyrocms_admin_handler_contents';
		$routePrm = array(
			'chid'=>$ch->getId(),
		);
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:list.html.php',
				array_merge(
					array(
						'title'=>$ch->getName(),
						'routePrmAdd'=>$routePrm,
						'routePrmEdit'=>$routePrm,
						'routePrmDelete'=>$routePrm,
						'name'=>'contentSpec',
						'route'=>$route,
						'fields'=>array(
							'id',
							'title',
							'position',
							'updated'
						),
						'moreActions'=>array_filter(array(
							'up'=>array(
								'name'=>'↑',
								'route'=>'nyrocms_admin_handler_contents_up',
								'routePrm'=>$routePrm
							),
							'down'=>array(
								'name'=>'↓',
								'route'=>'nyrocms_admin_handler_contents_down',
								'routePrm'=>$routePrm
							),
							'composer'=>$handler->hasComposer() ? array(
								'name'=>$this->get('nyrocms_admin')->getIcon('pencil'),
								'_blank'=>true,
								'route'=>'nyrocms_admin_composer',
								'routePrm'=>array(
									'type'=>'ContentSpec'
								)
							) : false
						))
					),
					$this->createList($repo, $route, $routePrm, 'position', $handler->isReversePositionOrder() ? 'desc' : 'asc', null, $qb)
				));
	}
	
	public function deleteAction(Request $request, $chid, $id) {
		$ch = $this->getContentHandler($chid);
		
		$repo = $this->get('nyrocms_db')->getContentSpecRepository();
		$row = $repo->find($id);
		if ($row) {
			$handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
			$handler->init($request, true);
			$handler->deleteClb($row);
			$afters = $repo->getAfters($row);
			$this->get('nyrocms_db')->remove($row);
			foreach($afters as $after)
				$after->setPosition($after->getPosition() - 1);
			$this->get('nyrocms_db')->flush();
		}
		return $this->redirect($this->generateUrl('nyrocms_admin_handler_contents', array('chid'=>$ch->getId())));
	}
	
	public function addAction(Request $request, $chid) {
		$ch = $this->getContentHandler($chid);
		$row = $this->get('nyrocms_db')->getNew('content_spec', false);
		$row->setContentHandler($ch);
		return $this->form($request, self::ADD, $row);
	}
	
	public function editAction(Request $request, $chid, $id) {
		$this->getContentHandler($chid);
		
		$row = $this->get('nyrocms_db')->getContentSpecRepository()->find($id);
		if (!$row)
			throw $this->createNotFoundException();
		return $this->form($request, self::EDIT, $row);
	}
	
	public function moveAction($chid, $id, $dir) {
		$ch = $this->getContentHandler($chid);
		
		$repo = $this->get('nyrocms_db')->getContentSpecRepository();
		$row = $repo->find($id);
		if (!$row)
			throw $this->createNotFoundException();
		
		$handler = $this->get('nyrocms')->getHandler($ch);
		if ($handler->isReversePositionOrder())
			$dir = $dir == 'up' ? 'down' : 'up';
		
		$position = $row->getPosition();
		if ($dir == 'up') {
			$position++;
		} else if ($position > 0) {
			$position--;
		}
		$row->setPosition($position);
		$this->get('nyrocms_db')->flush();
		
		return $this->redirect($this->generateUrl('nyrocms_admin_handler_contents', array('chid'=>$ch->getId())));
	}
	
	public function form(Request $request, $action, $row) {
		$routePrm = array('chid'=>$row->getContentHandler()->getId());
		$moreOptions = array(
			'state'=>array(
				'type'=>'choice',
				'choices'=>$this->get('nyrocms_admin')->getContentSpecStateChoices()
			),
			'validStart'=>$this->get('nyrocms')->getDateFormOptions(),
			'validEnd'=>$this->get('nyrocms')->getDateFormOptions(),
			'submit'=>array(
				'attr'=>array(
					'data-cancelurl'=>$this->container->get('nyrodev')->generateUrl('nyrocms_admin_handler_contents', $routePrm)
				)
			)
		);
		
		$handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
		$handler->init($request, true);
		
		$adminForm = $this->createAdminForm('contentSpec', $action, $row, array(
					'title',
					'intro',
					'featured',
					'state',
					'validStart',
					'validEnd',
				), 'nyrocms_admin_handler_contents', $routePrm, 'contentFormClb', 'contentFlush', null, $moreOptions, 'contentAfterFlush');
		if (!is_array($adminForm))
			return $adminForm;
		return $this->render('NyroDevNyroCmsBundle:AdminTpl:form.html.php', $adminForm);
	}
	protected $translationFields = array(
		'title'=>array(
			'type'=>'text',
			'required'=>true,
		),
		'intro'=>array(
			'type'=>'textarea',
			'required'=>false,
		),
	);
	protected $translations;
	/**
	 *
	 * @var \Symfony\Component\Form\Form
	 */
	protected $contentForm;
	protected function contentFormClb($action, \NyroDev\NyroCmsBundle\Model\ContentSpec $row, \Symfony\Component\Form\FormBuilder $form) {
		$langs = $this->getLangs();
		unset($langs[$this->getParameter('locale')]);
		
		$this->translations = array();
		foreach($row->getTranslations() as $tr) {
			if (!isset($this->translations[$tr->getLocale()]))
				$this->translations[$tr->getLocale()] = array();
			$this->translations[$tr->getLocale()][$tr->getField()] = $tr;
		}
		
		/* @var $form \Ivory\OrderedForm\Builder\OrderedFormBuilder */
		
		if (!$this->get('nyrocms')->getHandler($row->getContentHandler())->hasIntro()) {
			$form->remove('intro');
			unset($this->translationFields['intro']);
		}
		
		$propertyAccess = PropertyAccess::createPropertyAccessor();
		foreach($langs as $lg=>$lang) {
			foreach($this->translationFields as $field=>$options) {
				$type = $options['type'];
				unset($options['type']);
				$fieldName = 'lang_'.$lg.'_'.$field;
				
				if (isset($options['required']) && $options['required'])
					$options['constraints'] = array(new Constraints\NotBlank());
				
				$form->add($fieldName, $type, array_merge($options, array(
					'label'=>$this->trans('admin.contentSpec.'.$field).' '.strtoupper($lg),
					'mapped'=>false,
					'data'=>isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
					'position'=>array('after'=>$field)
				)));
			}
		}
		$this->get('nyrocms')->getHandler($row->getContentHandler())->formClb($action, $row, $form, $langs, $this->translations);
	}
	protected function contentFlush($action, $row, $form) {
		$this->contentForm = $form;
		$this->get('nyrocms')->getHandler($row->getContentHandler())->flushClb($action, $row, $form);
	}
	
	protected function contentAfterFlush($response, $action, $row) {
		$this->get('nyrocms')->getHandler($row->getContentHandler())->afterFlushClb($response, $action, $row);
		
		$langs = $this->getLangs();
		unset($langs[$this->getParameter('locale')]);
		
		$om = $this->get('nyrocms_db')->getObjectManager();
		$propertyAccess = PropertyAccess::createPropertyAccessor();
		
		foreach($langs as $lg=>$lang) {
			$row->setTranslatableLocale($lg);
			$om->refresh($row);
			
			foreach($this->translationFields as $field=>$options) {
				$fieldName = 'lang_'.$lg.'_'.$field;
				$propertyAccess->setValue($row, $field, $this->contentForm->get($fieldName)->getData());
			}
			
			$this->get('nyrocms')->getHandler($row->getContentHandler())->flushLangClb($action, $row, $this->contentForm, $lg);
			
			$om->flush();
		}
	}
	
}