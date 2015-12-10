<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use NyroDev\UtilityBundle\Controller\AbstractAdminController;

class Files extends AbstractHandler {
	
	public function hasIntro() {
		return true;
	}
	
	public function isIntroRequired() {
		return true;
	}
	
	public function hasComposer() {
		return false;
	}
	
	public function hasContentSpecUrl() {
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
		
		$routCfg = $this->get('nyrocms')->getRouteFor($content);
		$route = $routCfg['route'];
		$routePrm = $routCfg['prm'];
		
		$page = $this->request->query->get('page', 1);
		$nbPerPage = $this->getParameter('handler_files_perpage', 10);
		$total = $this->getTotalContentSpec($content);
		$nbPages = ceil($total / $nbPerPage);

		if ($page > $nbPages)
			$page = $nbPages;
		if ($page < 1)
			$page = 1;

		$pager = new \NyroDev\UtilityBundle\Utility\Pager($this->get('nyrodev'), $route, $routePrm, $total, $page, $nbPerPage);

		$results = $this->getContentSpecs($content, $pager->getStart(), $nbPerPage);

		$vars['results'] = $results;
		$vars['pager'] = $pager;
		$vars['uploadDir'] = $this->getUploadDir();
		
		return array(
			'view'=>$view.'.html.php',
			'vars'=>$vars,
		);
	}
	
}