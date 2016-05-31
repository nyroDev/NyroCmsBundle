<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Form\Type\ContactType;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use NyroDev\NyroCmsBundle\Form\Type\ContactMessageFilterType;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Contact extends AbstractHandler {
	
	public function hasComposer() {
		return false;
	}
	
	public function hasValidDates() {
		return false;
	}
	
	public function hasFeatured() {
		return false;
	}
	
	public function hasStateInvisible() {
		return false;
	}
	
	public function isReversePositionOrder() {
		return false;
	}
	
	public function saveInDb() {
		return false;
	}
	
	public function getOtherAdminRoutes() {
		$ret = null;
		if ($this->saveInDb()) {
			$ret = array(
				'contactMessage'=>array(
					'route'=>'nyrocms_admin_data_contactMessage',
					'routePrm'=>array(
						'chid'=>$this->contentHandler->getId()
					),
					'name'=>$this->contentHandler->getName().' '.$this->trans('admin.contactMessage.viewTitle')
				)
			);
		}
		return $ret;
	}
	
	public function getAdminMessageListFields() {
		return array(
			'id',
			'dest',
			'firstname',
			'lastname',
			'email',
			'inserted'
		);
	}
	
	public function getAdminMessageFilterType() {
		return ContactMessageFilterType::class;
	}
	
	public function getAdminMessageExportFields() {
		return array(
			'id',
			'dest',
			'firstname',
			'lastname',
			'company',
			'phone',
			'email',
			'message',
			'inserted'
		);
	}
	
	protected $validatedEmails;
	
	protected function getFormFields($action) {
		$ret = array();
		if ($this->contentHandler->getHasAdmin()) {
			$ret['emails'] = array(
				'type'=>TextareaType::class,
				'translatable'=>false,
				'label'=>$this->trans('nyrocms.handler.contact.emails'),
				'required'=>true,
				'constraints'=>array(
					new Constraints\NotBlank(),
					new Constraints\Callback(array(
						'callback'=>function($data, ExecutionContextInterface $context) {
							$emails = array_filter(array_map('trim', preg_split('/[\ \n\,;]+/', $data)));
							$errors = array();
							foreach($emails as $email) {
								if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
									$errors[] = $email;
								}
							}
							
							if (count($errors)) {
								 $context->buildViolation('nyrocms.handler.contact.emailsError')
									->setParameter('%emails%', implode(', ', $errors))
									->setTranslationDomain('messages')
									->atPath('emails')
									->addViolation();
							} else {
								$this->validatedEmails = implode(', ', $emails);
							}
						}
					))
				),
				'position'=>array('before'=>'state'),
			);
		}
		return $ret;
	}
	
	public function formClb($action, ContentSpec $row, FormBuilder $form, array $langs = array(), array $translations = array()) {
		parent::formClb($action, $row, $form, $langs, $translations);
	}
	
	public function flushClb($action, ContentSpec $row, Form $form) {
		parent::flushClb($action, $row, $form);
		$content = $row->getContent();
		$content['emails'] = $this->validatedEmails;
		$row->setContent($content);
	}
	
	protected function getEmails(Content $content) {
		$ret = array();
		
		if ($this->contentHandler->getHasAdmin()) {
			foreach($this->getContentSpecs($content) as $spec) {
				$ret['spec_'.$spec->getId()] = array(
					'emails'=>array_map('trim', explode(',', $spec->getInContent('emails'))),
					'name'=>$spec->getTitle()
				);
			}
		}
		
		if (count($ret) == 0) {
			$ret = array(
				'contact'=>array(
					'emails'=>array($this->trans('nyrocms.handler.contact.defaultTo.email')),
					'name'=>$this->trans('nyrocms.handler.contact.defaultTo.name')
				),
			);
		}
		
		return $ret;
	}
	
	protected function getFormType(Content $content) {
		return ContactType::class;
	}
	
	protected function getFormOptions(Content $content) {
		return array();
	}
	
	protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null) {
		$contactEmails = $this->getEmails($content);
		
		$form = $this->get('form.factory')->create($this->getFormType($content), null, array_merge(array(
			'attr'=>array(
				'id'=>'contactForm',
				'class'=>'publicForm',
			),
			'contacts'=>$contactEmails,
		), $this->getFormOptions($content)));
		$this->get('nyrodev_form')->addDummyCaptcha($form);
		
		/* @var $form \Symfony\Component\Form\Form */
		$form->handleRequest($this->request);
		if ($form->isValid()) {
			$subject = $this->trans('nyrocms.handler.contact.subject');
			$message = array();
			$message[] = '<h1>'.$subject.'</h1>';
			$message[] = '<p>';
			
			$data = $form->getData();
			
			if (isset($data['dest']) && isset($contactEmails[$data['dest']])) {
				$to = $contactEmails[$data['dest']]['emails'];
			} else {
				$to = $contactEmails[key($contactEmails)]['emails'];
			}
			
			$view = $form->createView();
			
			$saveInDb = $this->saveInDb();
			if ($saveInDb) {
				$contactMessage = $this->get('nyrocms_db')->getNew('contact_message');
				$contactMessage->setContentHandler($this->contentHandler);
				$contactMessage->setDest($contactEmails[$data['dest']]['name']);
				$accessor = PropertyAccess::createPropertyAccessor();
			}
			
			foreach($view as $k=>$field) {
				/* @var $field \Symfony\Component\Form\FormView */
				$v = $field->vars['value'];
				if ($k == 'dest' && $v)
					$v = $contactEmails[$v]['name'];
				if ($k != '_token' && $v) {
					$message[] = '<strong>'.$field->vars['label'].'</strong> : '.nl2br($v).'<br />';
					if ($saveInDb)
						$accessor->setValue($contactMessage, $k, $v);
				}
			}
			$message[] = '</p>';
			
			$this->sendEmail($to, $subject, implode("\n", $message), $data['email'], null, $content);
			
			if ($saveInDb)
				$this->get('nyrocms_db')->flush();
			
			return new RedirectResponse($this->get('nyrocms')->getUrlFor($content, false, array('sent'=>1)));
		}
		
		$view = 'NyroDevNyroCmsBundle:Handler:contact';
		
		return array(
			'view'=>$view.'.html.php',
			'vars'=>array(
				'content'=>$content,
				'form'=>$form->createView(),
				'sent'=>$this->request->query->getBoolean('sent'),
				'isAdmin'=>$this->isAdmin
			),
		);
	}
	
	protected function sendEmail($to, $subject, $content, $from = null, $locale = null, Content $dbContent = null) {
		return $this->get('nyrocms')->sendEmail($to, $subject, $content, $from, $locale, $dbContent);
	}
	
}