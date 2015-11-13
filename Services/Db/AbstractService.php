<?php

namespace NyroDev\NyroCmsBundle\Services\Db;

use NyroDev\UtilityBundle\Services\AbstractService as AbstractServiceSrc;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractService extends AbstractServiceSrc {
	
	protected $objectManager;
	
	public function __construct($container, ObjectManager $objectManager) {
		parent::__construct($container);
		$this->objectManager = $objectManager;
	}
	
	public function getNamespace() {
		return $this->getParameter('nyroDev_nyroCms.model.namespace');
	}
	
	public function getClass($name, $namespaced = true) {
		$tmp = explode('\\', $name);
		$converter = new \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter();
		$paramKey = $converter->normalize($tmp[count($tmp) - 1]);
		return ($namespaced ? $this->getNamespace().'\\' : '').$this->getParameter('nyroDev_nyroCms.model.classes.'.$paramKey);
	}
	
	/**
	 * 
	 * @return ObjectManager
	 */
	public function getObjectManager() {
		return $this->objectManager;
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getUserRepository() {
		return $this->getRepository('user');
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getContentRepository() {
		return $this->getRepository('content');
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getContentSpecRepository() {
		return $this->getRepository('content_spec');
	}
	
	/**
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getContentHandlerRepository() {
		return $this->getRepository('content_handler');
	}
	
	/**
	 * @param string $name class name
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getRepository($name) {
		return $this->getObjectManager()->getRepository($this->getClass($name));
	}
	
	public function persist($object) {
		$this->getObjectManager()->persist($object);
	}
	
	public function remove($object) {
		$this->getObjectManager()->remove($object);
	}
	
	public function refresh($object) {
		$this->getObjectManager()->refresh($object);
	}
	
	public function flush() {
		$this->getObjectManager()->flush();
	}
	
}