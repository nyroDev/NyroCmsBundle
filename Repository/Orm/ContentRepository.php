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
	
	public function findByLog($field, $value) {
		$search = 's:'.strlen($field).':"'.$field.'";s:'.strlen($value).':"'.$value.'";';
		$logValues = $this->getEntityManager()->getRepository('NyroDevNyroCmsBundle:ContentLog')
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
	
	public function findOneByContentHandlerCode($code, \NyroDev\NyroCmsBundle\Model\Content $root = null) {
		$qb = $this->createQueryBuilder('c')
			->innerJoin('c.contentHandler', 'ct')
				->andWhere('ct.code = :code')
					->setParameter('code', $code);
		
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
	
}