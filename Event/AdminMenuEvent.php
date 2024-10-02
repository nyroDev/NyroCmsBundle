<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AdminMenuEvent extends Event
{
    public const ADMIN_MENU = 'nyrocms.events.adminMenu';

    protected ?array $menu = null;

    public function __construct(
        protected readonly array $uriSplitted,
        protected readonly bool $adminPerRoot,
        protected readonly array $rootContents,
        protected readonly string $curRootId,
    ) {
    }

    public function getUriSplitted(): array
    {
        return $this->uriSplitted;
    }

    public function getAdminPerRoot(): bool
    {
        return $this->adminPerRoot;
    }

    public function getRootContents(): array
    {
        return $this->rootContents;
    }

    public function getCurRootId(): string
    {
        return $this->curRootId;
    }

    public function setMenu(array $menu): void
    {
        $this->menu = $menu;
    }

    public function getMenu(): ?array
    {
        return $this->menu;
    }
}
