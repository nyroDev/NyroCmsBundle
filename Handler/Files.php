<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use NyroDev\UtilityBundle\Controller\AbstractAdminController;

class Files extends AbstractHandler {
	
	public function useDateSpec() {
		return true;
	}
	
	public function orderField() {
		return 'dateSpec';
	}
	
	public function hasIntro() {
		return true;
	}
	
	public function isIntroRequired() {
		return true;
	}
	
	public function hasMoveActions() {
		return false;
	}
	
	public function hasComposer() {
		return false;
	}
	
	public function hasContentSpecUrl() {
		return false;
	}
	
	public function hasValidDates() {
		return false;
	}
	
	public function hasStateInvisible() {
		return false;
	}
	
	protected function getFormFields($action) {
		$isAdd = $action == AbstractAdminController::ADD;
		return array(
			'file'=>array(
				'type'=>FileType::class,
				'translatable'=>true,
				'label'=>$this->trans('nyrocms.handler.files.file'),
				'required'=>$isAdd,
				'constraints'=>array_filter(array(
					$isAdd ? new Constraints\NotBlank() : null,
					new Constraints\File(),
				)),
			),
		);
	}
	
	protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null) {
		$view = 'NyroDevNyroCmsBundle:Handler:files';
		$vars = array(
			'content'=>$content,
		);
		
		$vars['results'] = $this->getContentSpecs($content);
		$vars['uploadDir'] = $this->getUploadDir();
		
		return array(
			'view'=>$view.'.html.php',
			'vars'=>$vars,
		);
	}
	
}