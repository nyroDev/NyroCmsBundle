<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AdminAdministrableContentIds extends Event
{
    public const ADMIN_ADMINISTRABLE_CONTENT_IDS = 'nyrocms.events.adminAdministrableContentIds';

    public function __construct(
        public readonly mixed $user,
        public array $administrableContentIds,
    ) {
    }
}
