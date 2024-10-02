<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerConfigEvent extends Event
{
    public const COMPOSER_CONFIG = 'nyrocms.events.composerConfig';

    public function __construct(
        protected readonly Composable $row,
        protected readonly string $configName,
        protected mixed $config,
    ) {
    }

    public function getRow(): Composable
    {
        return $this->row;
    }

    public function getConfigName(): string
    {
        return $this->configName;
    }

    public function setConfig(mixed $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): mixed
    {
        return $this->config;
    }
}
