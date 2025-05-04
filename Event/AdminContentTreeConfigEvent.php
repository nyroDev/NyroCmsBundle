<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Content;
use Symfony\Contracts\EventDispatcher\Event;

class AdminContentTreeConfigEvent extends Event
{
    public const ADMIN_CONTENT_TREE_CONFIG = 'nyrocms.events.adminContentTreeConfig';

    public const CONFIG_CAN_ROOT_COMPOSER = 'canRootComposer';
    public const CONFIG_CONTENT_MAX_LEVEL = 'contentMaxLevel';

    public function __construct(
        public readonly Content $content,
        public readonly string $configName,
        public mixed $value,
    ) {
    }
}
