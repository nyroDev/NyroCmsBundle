<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;

class Sitemap extends AbstractHandler {
	
	protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null) {
		$root = $this->getContentById($content->getRoot());
		
		return array(
			'view'=>'NyroDevNyroCmsBundle:Handler:sitemap.html.php',
			'vars'=>array(
				'content'=>$content,
				'contents'=>$this->getHierarchy($root),
				'isRoot'=>true
			),
		);
	}
	
	protected function getHierarchy(Content $content) {
		$ret = array();
		
		foreach($this->getContentRepo()->childrenForMenu($content, true) as $sub) {
			$ret[] = array(
				'content'=>$sub,
				'contents'=>$this->getHierarchy($sub)
			);
		}
		
		return $ret;
	}
	
}