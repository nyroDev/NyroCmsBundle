<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use DateTime;
use Gedmo\Sortable\Entity\Repository\SortableRepository;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\Orm\Traits\TranslatableHintTrait;

class ContentSpecRepository extends SortableRepository implements ContentSpecRepositoryInterface
{
    use TranslatableHintTrait;

    /**
     * @param type                                          $contentHandlerId
     * @param type                                          $state
     * @param \NyroDev\NyroCmsBundle\Repository\Orm\Content $specificContent
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQbForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = [], array $order = [])
    {
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
                ->setParameter('now', new DateTime());

        if ($state) {
            $qb->andWhere('cs.state = :state')
                ->setParameter('state', $state);
        }

        if (count($where)) {
            foreach ($where as $k => $v) {
                $operator = '=';

                if (is_array($v)) {
                    $operator = $v['operator'];
                    $v = $v['value'];
                } elseif ('!' == $k[0]) {
                    $operator = '<>';
                    $k = substr($k, 1);
                }

                $qb->andWhere('cs.'.$k.' '.$operator.' :'.$k.'_wh')
                    ->setParameter($k.'_wh', $v);
            }
        }

        if (count($order)) {
            foreach ($order as $k => $v) {
                $qb->addOrderBy('cs.'.$k, $v);
            }
        }

        return $qb;
    }

    public function countForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = [])
    {
        $qb = $this->getQbForHandler($contentHandlerId, $state, $specificContent, $where);

        return $this->createQueryBuilder('cpt')
                ->select('COUNT(cpt.id)')
                ->andWhere('cpt.id = ANY('.$qb->getDQL().')')
                ->setParameters($qb->getParameters())
                ->getQuery()->getSingleScalarResult();
    }

    public function getForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = [], array $order = [], $start = null, $limit = null)
    {
        $qb = $this->getQbForHandler($contentHandlerId, $state, $specificContent, $where, $order);

        if (!is_null($start)) {
            $qb->setFirstResult($start);
        }
        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        $q = $qb->getQuery();
        $this->setHint($q);

        return $q->getResult();
    }

    public function getOneOrNullForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = [], array $order = [])
    {
        $q = $this
            ->getQbForHandler($contentHandlerId, $state, $specificContent, $where, $order)
            ->setMaxResults(1)
            ->getQuery();
        $this->setHint($q);

        return $q->getOneOrNullResult();
    }

    public function getAfters(ContentSpec $contentSpec)
    {
        return $this->createQueryBuilder('cs')
                    ->andWhere('cs.contentHandler = :chid')
                        ->setParameter('chid', $contentSpec->getContentHandler()->getId())
                    ->andWhere('cs.position > :position')
                        ->setParameter('position', $contentSpec->getPosition())
                    ->addOrderBy('cs.position', 'ASC')
                    ->getQuery()
                    ->getResult();
    }

    public function findForAction($id, $contentHandlerId, array $states = [])
    {
        $qb = $this->createQueryBuilder('cs')
                ->andWhere('cs.id = :id')
                    ->setParameter('id', $id)
                ->andWhere('cs.contentHandler = :chid')
                    ->setParameter('chid', $contentHandlerId);

        if (count($states)) {
            $qb->andWhere('cs.state IN (:states)')->setParameter('states', $states);
        }

        $q = $qb
            ->setMaxResults(1)
            ->getQuery();
        $this->setHint($q);

        return $q->getOneOrNullResult();
    }

    public function search(array $searches, array $contentHandlersIds = [], $state = null)
    {
        $query = $parameters = [];
        foreach ($searches as $k => $v) {
            $query[] = 'cs.contentText LIKE :text'.$k;
            $parameters['text'.$k] = '%'.$v.'%';
        }

        $qb = $this->createQueryBuilder('cs')
            ->andWhere('('.implode(' AND ', $query).')')->setParameters($parameters);

        if (count($contentHandlersIds)) {
            $qb->andWhere('cs.contentHandler IN (:ctids)')->setParameter('ctids', $contentHandlersIds);
        }

        if (!is_null($state)) {
            $qb->andWhere('cs.state = :state')->setParameter('state', $state);
        }

        $q = $qb->getQuery();

        $this->setHint($q);

        return $q->getResult();
    }
}
