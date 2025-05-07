<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CmsFoundClassesEvent extends Event
{
    public const HANDLER = 'nyrocms.events.cmdFoundClasses.handler';
    public const COMPOSABLE = 'nyrocms.events.nyrocms.events.cmdFoundClasses.composable';

    public function __construct(
        public array $foundClasses,
    ) {
    }
}
