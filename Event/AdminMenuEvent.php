<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\UtilityBundle\Utility\Menu\Menuable;
use Symfony\Contracts\EventDispatcher\Event;

class AdminMenuEvent extends Event
{
    public const ADMIN_MENU = 'nyrocms.events.adminMenu';

    public function __construct(
        public array $vars,
    ) {
    }

    public function getUriSplitted(): array
    {
        return $this->vars['uriSplitted'] ?? [];
    }

    public function getAdminPerRoot(): bool
    {
        return $this->vars['adminPerRoot'] ?? false;
    }

    public function getRootContents(): array
    {
        return $this->vars['rootContents'] ?? [];
    }

    public function getCurRootId(): string
    {
        return $this->vars['curRootId'] ?? '';
    }

    public function getMenu(): ?Menuable
    {
        return $this->vars['menu'] ?? null;
    }
}
