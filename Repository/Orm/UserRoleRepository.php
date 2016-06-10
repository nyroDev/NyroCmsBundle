<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface;

class UserRoleRepository extends EntityRepository implements UserRoleRepositoryInterface
{
    public function getFormQueryBuilder()
    {
        return $this->createQueryBuilder('ur')
                    ->addOrderBy('ur.internal', 'DESC')
                    ->addOrderBy('ur.name', 'ASC');
    }
}
