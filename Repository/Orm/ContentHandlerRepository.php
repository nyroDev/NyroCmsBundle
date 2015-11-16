<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use NyroDev\NyroCmsBundle\Repository\ContentHandlerRepositoryInterface;

class ContentHandlerRepository extends EntityRepository implements ContentHandlerRepositoryInterface {
	
	public function getFormQueryBuilder() {
		return $this->createQueryBuilder('ch')
					->addOrderBy('ch.name', 'ASC');
	}

}