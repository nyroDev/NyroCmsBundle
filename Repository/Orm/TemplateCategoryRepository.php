<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use NyroDev\NyroCmsBundle\Repository\TemplateCategoryRepositoryInterface;

class TemplateCategoryRepository extends EntityRepository implements TemplateCategoryRepositoryInterface
{
    public function getClassMetadata(): ClassMetadata
    {
        return parent::getClassMetadata();
    }
}
