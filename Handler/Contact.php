<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Form\Type\ContactType;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Contact extends AbstractHandler {
	
	protected function getEmails() {
		return array(
			'contact'=>array(
				'email'=>$this->trans('nyrocms.handler.contact.defaultTo.email'),
				'name'=>$this->trans('nyrocms.handler.contact.defaultTo.name')
			),
		);
	}
	
	protected function getFormType() {
		return ContactType::class;
	}
	
	protected function getFormOptions() {
		return array();
	}
	
	protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null) {
		$contactEmails = $this->getEmails();
		
		$form = $this->get('form.factory')->create($this->getFormType(), null, array_merge(array(
			'attr'=>array(
				'id'=>'contactForm',
				'class'=>'publicForm',
			),
			'contacts'=>$this->getEmails(),
		), $this->getFormOptions()));
		$this->get('nyrodev_form')->addDummyCaptcha($form);
		
		$form->handleRequest($this->request);
		if ($form->isValid()) {
			$subject = $this->trans('nyrocms.handler.contact.subject');
			$message = array();
			$message[] = '<h1>'.$subject.'</h1>';
			$message[] = '<p>';
			
			$data = $form->getData();
			if (isset($data['to']) && isset($contactEmails[$data['to']])) {
				$to = $contactEmails[$data['to']]['email'];
			} else {
				$to = $contactEmails[key($contactEmails)]['email'];
			}
			foreach($data as $k=>$v) {
				if ($k == 'to' && $v)
					$v = $contactEmails[$v]['name'];
				if ($v)
					$message[] = '<strong>'.$form->get($k)->getConfig()->getOption('label').'</strong> : '.nl2br($v).'<br />';
			}
			$message[] = '</p>';
			
			$this->get('nyrocms')->sendEmail($to, $subject, implode("\n", $message), $data['email']);
			
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
	
}