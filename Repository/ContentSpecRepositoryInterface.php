<?php

namespace NyroDev\NyroCmsBundle\Repository;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;

interface ContentSpecRepositoryInterface
{
    public function countForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array());

    public function getForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array(), $start = null, $limit = null);

    public function getOneOrNullForHandler($contentHandlerId, $state = ContentSpec::STATE_ACTIVE, Content $specificContent = null, array $where = array(), array $order = array());

    public function getAfters(ContentSpec $contentSpec);

    public function findForAction($id, $contentHandlerId, array $states = array());

    public function search(array $searches, array $contentHandlersIds = array(), $state = null);
}
