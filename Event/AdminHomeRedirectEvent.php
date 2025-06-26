<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AdminHomeRedirectEvent extends Event
{
    public const ADMIN_HOME_REDIRECT = 'nyrocms.events.adminHomeRedirect';

    public function __construct(
        public string $url,
    ) {
    }
}
