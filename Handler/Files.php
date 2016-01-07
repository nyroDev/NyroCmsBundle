<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use NyroDev\UtilityBundle\Controller\AbstractAdminController;

class Files extends AbstractHandler {
	
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
			'date'=>array_merge($this->get('nyrocms')->getDateFormOptions(), array(
				'type'=>DateType::class,
				'translatable'=>false,
				'label'=>$this->trans('nyrocms.handler.files.date'),
				'required'=>true,
				'constraints'=>array(
					new Constraints\NotBlank()
				),
				'data'=>new \DateTime()
			)),
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
		
		$results = $sorted = $tmp = array();
		foreach($this->getContentSpecs($content) as $contentSpec) {
			$tmp[$contentSpec->getId()] = $contentSpec;
			$date = $contentSpec->getInContent('date');
			if ($date) {
				$date = new \DateTime($date['date']);
			} else {
				$date = $contentSpec->getInserted();
			}
			$key = $date->format('Y-m-d');
			if (!isset($sorted[$key]))
				$sorted[$key] = array();
			$sorted[$key][] = $contentSpec->getId();
		}
		
		arsort($sorted);
		foreach($sorted as $ids) {
			foreach($ids as $id) {
				$results[] = $tmp[$id];
			}
		}
		
		$vars['results'] = $results;
		$vars['uploadDir'] = $this->getUploadDir();
		
		return array(
			'view'=>$view.'.html.php',
			'vars'=>$vars,
		);
	}
	
}