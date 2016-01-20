<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;

class ContentRepository extends NestedTreeRepository implements ContentRepositoryInterface {

	public function children($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false) {
        $q = $this->childrenQuery($node, $direct, $sortByField, $direction, $includeNode);
		$q->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        return $q->getResult();
	}
	
	public function childrenForMenu($node = null, $direct = true) {
		$qb = $this->childrenQueryBuilder($node, $direct);
			$qb->andWhere('node.state = :state')->setParameter('state', \NyroDev\NyroCmsBundle\Model\Content::STATE_ACTIVE);
		$q = $qb->getQuery();
		$q->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        return $q->getResult();
	}
	
	public function getPathForBreacrumb($node, $excludeNode = true) {
		$qb = $this->getPathQueryBuilder($node);
			$qb->andWhere('node.level > 0');
		
		if ($excludeNode)
			$qb->andWhere('node.id <> :id')
				->setParameter('id', $node->getid());
		
		$q = $qb->getQuery();
		$q->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        return $q->getResult();
	}
	
	public function findByUrl($url, $rootId, array $states = array()) {
		$qb = $this->createQueryBuilder('c')
			->andWhere('c.root = :root')->setParameter('root', $rootId)
			->andWhere('c.url = :url')->setParameter('url', $url);
		
		if (count($states))
			$qb->andWhere('c.state IN (:states)')->setParameter('states', $states);
		
		return $qb
			->setMaxResults(1)
			->getQuery()
			->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
			->getOneOrNullResult();
	}
	
	public function findByLog($field, $value) {
		$search = 's:'.strlen($field).':"'.$field.'";s:'.strlen($value).':"'.$value.'";';
		$logValues = $this->getEntityManager()->getRepository(str_replace('\Entity\Content', '\Entity\Log\Content', $this->getClassName()).'Log')
					->createQueryBuilder('cl')
						->andWhere('cl.data LIKE :search')
							->setParameter('search', '%'.$search.'%')
						->addOrderBy('cl.id', 'DESC')
						->addGroupBy('cl.objectId')
					->getQuery()
					->getResult();
		
		$ret = array();
		foreach($logValues as $logValue) {
			$ret[] = $this->find($logValue->getObjectId());
			if ($logValue->getLocale()) {
				$ret->setTranslatableLocale($logValue->getLocale());
				$this->getEntityManager()->refresh($ret);
			}
		}
		return $ret;
	}
	
	public function search(array $searches, $rootId = null, $state = null) {
		$query = $parameters = array();
		foreach($searches as $k=>$v) {
			$query[] = 'c.contentText LIKE :text'.$k;
			$parameters['text'.$k] = '%'.$v.'%';
		}
			
		$qb = $this->createQueryBuilder('c')
				->andWhere('('.implode(' AND ', $query).')')->setParameters($parameters);
		
		if (!is_null($rootId))
			$qb->andWhere('c.root = :root')->setParameter('root', $rootId);
		
		if (!is_null($state))
			$qb->andWhere('c.state = :state')->setParameter('state', $state);
		
		return $qb->getQuery()
				->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
				->getResult();
	}
	
	public function findWithContentHandler($rootId = null, $state = null) {
		$qb = $this->createQueryBuilder('c')
				->andWhere('c.contentHandler IS NOT NULL');
		
		if (!is_null($rootId))
			$qb->andWhere('c.root = :root')->setParameter('root', $rootId);
		
		if (!is_null($state))
			$qb->andWhere('c.state = :state')->setParameter('state', $state);
		
		return $qb->getQuery()->getResult();
	}
	
	public function findOneByContentHandlerClass($class, \NyroDev\NyroCmsBundle\Model\Content $root = null) {
		$qb = $this->createQueryBuilder('c')
			->innerJoin('c.contentHandler', 'ct')
				->andWhere('ct.class = :class')
					->setParameter('class', $class);
		
		if ($root)
			$qb->andWhere('c.root = :root')->setParameter('root', $root->getId());
		
		$q = $qb->getQuery();
		$q->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        return $q->getOneOrNullResult();
	}
	
	protected function getQueryMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null) {
		$qb = $this->createQueryBuilder('c')
				->andWhere('c.menuOption LIKE :menuOption')
					->setParameter('menuOption', $menuOption);
		
		if ($root)
			$qb->andWhere('c.root = :root')->setParameter('root', $root->getId());
		
		$q = $qb->getQuery();
		$q->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
		return $q;
	}
	
	public function findOneByMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null) {
        return $this->getQueryMenuOption($menuOption, $root)->getOneOrNullResult();
	}
	
	public function findByMenuOption($menuOption, \NyroDev\NyroCmsBundle\Model\Content $root = null) {
        return $this->getQueryMenuOption($menuOption, $root)->getResult();
	}
	
	public function getFormQueryBuilder($root, $ignoreId = null) {
		$qb = $this->createQueryBuilder('c')
			->andWhere('c.root = :root')
				->setParameter('root', $root)
			->addOrderBy('c.lft', 'ASC');
		if ($ignoreId)
			$qb->andWhere('c.id <> :id')->setParameter('id', $ignoreId);
		return $qb;
	}
	
}