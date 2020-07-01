<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UrlGenerationEvent extends Event
{
    const LOCALES_URL = 'nyrocms.events.urlGeneration.locales';
    const OBJECT_URL = 'nyrocms.events.urlGeneration.object';

    protected $routeName;
    protected $routePrm;
    protected $absolute;
    protected $object;
    protected $parent;

    public function __construct($routeName, array $routePrm = [], $absolute = false, $object = null, $parent = null)
    {
        $this->routeName = $routeName;
        $this->routePrm = $routePrm;
        $this->absolute = $absolute;
        $this->object = $object;
        $this->parent = $parent;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function getRoutePrm()
    {
        return $this->routePrm;
    }

    public function setRoutePrm(array $routePrm)
    {
        $this->routePrm = $routePrm;
    }

    public function getAbsolute()
    {
        return $this->absolute;
    }

    public function setAbsolute($absolute)
    {
        $this->absolute = $absolute;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getParent()
    {
        return $this->parent;
    }
}
