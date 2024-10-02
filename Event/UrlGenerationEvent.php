<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UrlGenerationEvent extends Event
{
    public const LOCALES_URL = 'nyrocms.events.urlGeneration.locales';
    public const OBJECT_URL = 'nyrocms.events.urlGeneration.object';

    public function __construct(
        protected string $routeName,
        protected array $routePrm = [],
        protected bool $absolute = false,
        protected readonly mixed $object = null,
        protected readonly mixed $parent = null,
    ) {
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getRoutePrm(): array
    {
        return $this->routePrm;
    }

    public function setRoutePrm(array $routePrm): void
    {
        $this->routePrm = $routePrm;
    }

    public function getAbsolute(): bool
    {
        return $this->absolute;
    }

    public function setAbsolute($absolute): void
    {
        $this->absolute = $absolute;
    }

    public function getObject(): mixed
    {
        return $this->object;
    }

    public function getParent(): mixed
    {
        return $this->parent;
    }
}
