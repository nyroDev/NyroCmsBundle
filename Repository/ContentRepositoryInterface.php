<?php

namespace NyroDev\NyroCmsBundle\Repository;

use Gedmo\Tree\RepositoryInterface;

interface ContentRepositoryInterface extends RepositoryInterface {
	
	public function childrenForMenu($node = null, $direct = true);
	
	public function getPathForBreacrumb($node, $excludeNode = true);
	
	public function findByLog($field, $value);
	
	public function findOneByContentHandlerCode($code, \NyroDev\NyroCmsBundle\Model\Content $root = null);
	
	public function findOneByMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null);
	
	public function findByMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null);
	
}