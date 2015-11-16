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
		return $this->getParameter('nyroCms.model.namespace');
	}
	
	public function getClass($name, $namespaced = true) {
		$tmp = explode('\\', $name);
		$converter = new \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter();
		$paramKey = $converter->normalize($tmp[count($tmp) - 1]);
		return ($namespaced ? $this->getNamespace().'\\' : '').$this->getParameter('nyroCms.model.classes.'.$paramKey);
	}
	
	/**
	 * 
	 * @return ObjectManager
	 */
	public function getObjectManager() {
		return $this->objectManager;
	}
	
	/**
	 * @return \NyroDev\NyroCmsBundle\Repository\UserRepositoryInterface
	 */
	public function getUserRepository() {
		return $this->getRepository('user');
	}
	
	/**
	 * @return \NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface
	 */
	public function getUserRoleRepository() {
		return $this->getRepository('user_role');
	}
	
	/**
	 * @return \NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface
	 */
	public function getContentRepository() {
		return $this->getRepository('content');
	}
	
	/**
	 * @return \NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface
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
	
	public function getNew($name, $persist = true) {
		$repo = $this->getRepository($name);
		$classname = $repo->getClassName();
		$new = new $classname();
		
		if ($persist)
			$this->persist($new);
		
		return $new;
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