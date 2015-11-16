<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Gedmo\Sortable\Entity\Repository\SortableRepository;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface;

class ContentSpecRepository extends SortableRepository implements ContentSpecRepositoryInterface {
	
	public function getAdminListQueryBuilder(ContentHandler $contentHandler) {
		return $this->createQueryBuilder('cs')
				->andWhere('cs.contentHandler = :chid')
					->setParameter('chid', $contentHandler->getId());
	}
	
	/**
	 * 
	 * @param type $contentHandlerId
	 * @param type $state
	 * @param \NyroDev\NyroCmsBundle\Repository\Orm\Content $specificContent
	 * @param array $where
	 * @param array $order
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQbForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array()) {
		$qb = $this->createQueryBuilder('cs')
				->andWhere('cs.contentHandler = :chid')
					->setParameter('chid', $contentHandlerId);
		
		if ($specificContent && $specificContent->getId()) {
			$qb->andWhere('(:cid MEMBER OF cs.contents OR SIZE(cs.contents) = 0)')
				->setParameter('cid', $specificContent->getId());
		}
		
		$qb
			->andWhere('(cs.validStart IS NULL OR cs.validStart <= :now)')
			->andWhere('(cs.validEnd IS NULL OR cs.validEnd >= :now)')
				->setParameter('now', new \DateTime());
		
		if ($state) {
			$qb->andWhere('cs.state = :state')
				->setParameter('state', $state);
		}
		
		if (count($where)) {
			foreach($where as $k=>$v) {
				$operator = '=';
				if ($k[0] == '!') {
					$operator = '<>';
					$k = substr($k, 1);
				}
				$qb->andWhere('cs.'.$k.' '.$operator.' :'.$k.'_wh')
					->setParameter($k.'_wh', $v);
			}
		}
		
		if (count($order)) {
			foreach($order as $k=>$v) {
				$qb->addOrderBy('cs.'.$k, $v);
			}
		}
		
		return $qb;
	}
	
	public function countForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array()) {
		$qb = $this->getQbForHandler($contentHandlerId, $state, $specificContent, $where);
		
		return $this->createQueryBuilder('cpt')
				->select('COUNT(cpt.id)')
				->andWhere('cpt.id = ANY('.$qb->getDQL().')')
				->setParameters($qb->getParameters())
				->getQuery()->getSingleScalarResult();
	}
	public function getForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array(), $start = null, $limit = null) {
		$qb = $this->getQbForHandler($contentHandlerId, $state, $specificContent, $where, $order);
		
		if (!is_null($start))
			$qb->setFirstResult($start);
		if (!is_null($limit))
			$qb->setMaxResults($limit);
		
		return $qb
				->getQuery()
				->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
				->getResult();
	}
	
	public function getOneOrNullForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array()) {
		return $this
				->getQbForHandler($contentHandlerId, $state, $specificContent, $where, $order)
				->getQuery()
				->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
				->getOneOrNullResult();
	}

	public function getAfters(ContentSpec $contentSpec) {
		return $this->createQueryBuilder('cs')
					->andWhere('cs.contentHandler = :chid')
						->setParameter('chid', $contentSpec->getContentHandler()->getId())
					->andWhere('cs.position > :position')
						->setParameter('position', $contentSpec->getPosition())
					->addOrderBy('cs.position', 'ASC')
					->getQuery()
					->getResult();
	}

}