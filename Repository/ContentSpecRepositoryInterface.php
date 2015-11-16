<?php

namespace NyroDev\NyroCmsBundle\Repository;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;

interface ContentSpecRepositoryInterface {
	
	public function getAdminListQueryBuilder(ContentHandler $contentHandler);
	
	public function countForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array());
	
	public function getForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array(), $start = null, $limit = null);
	
	public function getOneOrNullForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array());
	
	public function getAfters(ContentSpec $contentSpec);
	
}