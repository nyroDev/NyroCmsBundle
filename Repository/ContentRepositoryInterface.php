<?php

namespace NyroDev\NyroCmsBundle\Repository;

use Gedmo\Tree\RepositoryInterface;
use NyroDev\NyroCmsBundle\Model\Content;

interface ContentRepositoryInterface extends RepositoryInterface
{
    public function childrenForMenu($node = null, $direct = true);

    public function getPathForBreacrumb($node, $excludeNode = true);

    public function findByUrl($url, $rootId, array $states = array());

    public function findByLog($field, $value);

    public function search(array $searches, $rootId = null, $state = null, $sortByField = null, $direction = 'ASC');

    public function findWithContentHandler($rootId = null, $state = null, $sortByField = null, $direction = 'ASC');

    public function findContentHandlerClass($class, Content $root = null);

    public function findOneByContentHandlerClass($class, Content $root = null, Content $parent = null);

    public function findOneByMenuOption($menuOption, Content $root = null, Content $parent = null);

    public function findByMenuOption($menuOption, Content $root = null, Content $parent = null, $sortByField = null, $direction = 'ASC');

    public function getFormQueryBuilder($root, $ignoreId = null);
}
