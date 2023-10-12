<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerConfigEvent extends Event
{
    public const COMPOSER_CONFIG = 'nyrocms.events.composerConfig';

    protected $row;
    protected $config;

    public function __construct(Composable $row, $configName, $config)
    {
        $this->row = $row;
        $this->configName = $configName;
        $this->config = $config;
    }

    /**
     * @return Composable
     */
    public function getRow()
    {
        return $this->row;
    }

    public function getConfigName()
    {
        return $this->configName;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
