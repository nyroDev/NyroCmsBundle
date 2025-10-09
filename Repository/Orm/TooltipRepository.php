<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use NyroDev\NyroCmsBundle\Repository\TooltipRepositoryInterface;

class TooltipRepository extends EntityRepository implements TooltipRepositoryInterface
{
    public function getClassMetadata(): ClassMetadata
    {
        return parent::getClassMetadata();
    }
}
