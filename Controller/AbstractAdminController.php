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
	
	protected function getTranslatesActions($route, $routePrm = array(), $langs = null) {
		if (is_null($langs)) {
			$tmp = $this->getLangs();
			unset($tmp[$this->container->getParameter('locale')]);
			$langs = array();
			foreach($tmp as $k=>$v) {
				$langs[$k] = strtoupper($k);
			}
		}
		$ret = array();
		foreach($langs as $lg=>$lang) {
			$ret[$lg] = array(
				'route'=>$route,
				'routePrm'=>array_merge($routePrm, array(
					'lang'=>$lg
				)),
				'name'=>$lang
			);
		}
		return $ret;
	}
	
	protected function getLangs() {
		return $this->container->getParameter('localesNames');
	}
}