<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerDefaultBlockEvent extends Event
{
    public const COMPOSER_DEFAULT_ADMIN_CONTENT = 'nyrocms.events.composerDefaultAdminContent';

    public function __construct(
        private readonly Composable $row,
        private array $content,
    ) {
    }

    public function getRow(): Composable
    {
        return $this->row;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }
}
