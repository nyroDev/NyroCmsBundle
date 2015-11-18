<?php

namespace NyroDev\NyroCmsBundle\Repository;

use Gedmo\Tree\RepositoryInterface;

interface ContentRepositoryInterface extends RepositoryInterface {
	
	public function childrenForMenu($node = null, $direct = true);
	
	public function getPathForBreacrumb($node, $excludeNode = true);
	
	public function findByUrl($url, $rootId, array $states = array());
	
	public function findByLog($field, $value);
	
	public function search(array $searches, $rootId = null, $state = null);
	
	public function findWithContentHandler($rootId = null, $state = null);
	
	public function findOneByContentHandlerCode($code, \NyroDev\NyroCmsBundle\Model\Content $root = null);
	
	public function findOneByMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null);
	
	public function findByMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null);
	
	public function getFormQueryBuilder($root, $ignoreId = null);

}