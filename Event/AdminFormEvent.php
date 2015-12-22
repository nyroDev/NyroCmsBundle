<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AdminFormEvent extends Event {
	
	const UPDATE_CONTENT = 'nyrocms.events.admin.form.update.content';
	const UPDATE_USER = 'nyrocms.events.admin.form.update.user';
	
	protected $action, $row, $form;
	
	public function __construct($action, $row, \Symfony\Component\Form\FormBuilder $form) {
		$this->action = $action;
		$this->row = $row;
		$this->form = $form;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function getRow() {
		return $this->row;
	}
	
	/**
	 * 
	 * @return \Symfony\Component\Form\FormBuilder
	 */
	public function getForm() {
		return $this->form;
	}
	
}