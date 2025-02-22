<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Template;
use NyroDev\NyroCmsBundle\Repository\TemplateRepositoryInterface;

class TemplateRepository extends EntityRepository implements TemplateRepositoryInterface
{
    public function getAvailableTemplatesFor(Composable $row): array
    {
        if ($row instanceof Template) {
            return [];
        }

        return $this->createQueryBuilder('t')
                        ->andWhere('t.state = :state')
                            ->setParameter('state', Template::STATE_ACTIVE)
                        ->addOrderBy('t.title', 'asc')
                        ->getQuery()
                        ->execute();
    }
}
