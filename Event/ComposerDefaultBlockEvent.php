<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerDefaultBlockEvent extends Event
{
    public const COMPOSER_DEFAULT_ADMIN_CONTENT = 'nyrocms.events.composerDefaultAdminContent';

    protected $row;

    protected $content;

    public function __construct(Composable $row, array $content)
    {
        $this->row = $row;
        $this->content = $content;
    }

    /**
     * @return Composable
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Get the value of content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content.
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
