<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;

class MainService extends AbstractService {
	
	protected $handlers = array();
	public function getHandler(\NyroDev\NyroCmsBundle\Model\ContentHandler $contentHandler) {
		if (!isset($this->handlers[$contentHandler->getId()])) {
			$class = $contentHandler->getClass();
			if (!class_exists($class))
				throw new \RuntimeException($class.' not found when trying to create handler.');
			
			$this->handlers[$contentHandler->getId()] = new $class($contentHandler, $this->container);
		}
		return $this->handlers[$contentHandler->getId()];
	}
	
}