<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface;

class UserRoleRepository extends EntityRepository implements UserRoleRepositoryInterface {
	
	public function getAdminListQueryBuilder($isDev = false) {
		$qb = $this->createQueryBuilder('ur');
		if (!$isDev)
			$qb->andWhere('ur.internal <> 1');
		return $qb;
	}
	
	public function getFormQueryBuilder() {
		return $this->createQueryBuilder('ur')
					->addOrderBy('ur.internal', 'DESC')
					->addOrderBy('ur.name', 'ASC');
	}

}