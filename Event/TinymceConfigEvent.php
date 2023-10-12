<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class TinymceConfigEvent extends Event
{
    public const TINYMCE_CONFIG = 'nyrocms.events.tinymceConfig';

    protected $row;
    protected $simple;
    protected $config;

    public function __construct(Composable $row, $simple, array $config)
    {
        $this->row = $row;
        $this->simple = $simple;
        $this->config = $config;
    }

    /**
     * @return Composable
     */
    public function getRow()
    {
        return $this->row;
    }

    public function getSimple()
    {
        return $this->simple;
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
