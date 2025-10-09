<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use NyroDev\NyroCmsBundle\Repository\ContentHandlerRepositoryInterface;

class ContentHandlerRepository extends EntityRepository implements ContentHandlerRepositoryInterface
{
    public function getFormQueryBuilder()
    {
        return $this->createQueryBuilder('ch')
                    ->addOrderBy('ch.name', 'ASC');
    }

    public function getClassMetadata(): ClassMetadata
    {
        return parent::getClassMetadata();
    }
}
