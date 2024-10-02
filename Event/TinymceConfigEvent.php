<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class TinymceConfigEvent extends Event
{
    public const TINYMCE_CONFIG = 'nyrocms.events.tinymceConfig';

    public function __construct(
        protected readonly Composable $row,
        protected bool $simple,
        protected array $config,
    ) {
    }

    public function getRow(): Composable
    {
        return $this->row;
    }

    public function getSimple(): bool
    {
        return $this->simple;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
