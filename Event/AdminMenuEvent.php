<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AdminMenuEvent extends Event
{
    public const ADMIN_MENU = 'nyrocms.events.adminMenu';

    protected $menu;
    protected $uriSplitted;
    protected $adminPerRoot;
    protected $rootContents;
    protected $curRootId;

    public function __construct($uriSplitted, $adminPerRoot, array $rootContents, $curRootId)
    {
        $this->uriSplitted = $uriSplitted;
        $this->adminPerRoot = $adminPerRoot;
        $this->rootContents = $rootContents;
        $this->curRootId = $curRootId;
    }

    public function getUriSplitted()
    {
        return $this->uriSplitted;
    }

    public function getAdminPerRoot()
    {
        return $this->adminPerRoot;
    }

    public function getRootContents()
    {
        return $this->rootContents;
    }

    public function getCurRootId()
    {
        return $this->curRootId;
    }

    public function setMenu(array $menu)
    {
        $this->menu = $menu;
    }

    public function getMenu()
    {
        return $this->menu;
    }
}
