<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\UtilityBundle\Controller\AbstractAdminController as SrcAbstractAdminController;

class AbstractAdminController extends SrcAbstractAdminController {
	
	protected function canAdminContentHandler(\NyroDev\NyroCmsBundle\Model\ContentHandler $contentHandler) {
		$canAdmin = false;
		$nyrocmsAdmin = $this->get('nyrocms_admin');
		foreach($contentHandler->getContents() as $content)
			$canAdmin = $canAdmin || $nyrocmsAdmin->canAdminContent($content);
		
		if (!$canAdmin)
			throw $this->createAccessDeniedException();
	}

}