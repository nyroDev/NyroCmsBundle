<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use NyroDev\NyroCmsBundle\Repository\ContentHandlerConfigRepositoryInterface;

class ContentHandlerConfigRepository extends EntityRepository implements ContentHandlerConfigRepositoryInterface
{
}
