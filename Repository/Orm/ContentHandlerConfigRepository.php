<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use NyroDev\NyroCmsBundle\Repository\ContentHandlerConfigRepositoryInterface;

class ContentHandlerConfigRepository extends EntityRepository implements ContentHandlerConfigRepositoryInterface
{
    public function getClassMetadata(): ClassMetadata
    {
        return parent::getClassMetadata();
    }
}
