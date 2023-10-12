<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerEvent extends Event
{
    public const COMPOSER_DEFAULT = 'nyrocms.events.composerDefault';
    public const COMPOSER_LANG_SAME = 'nyrocms.events.composerLangSame';
    public const COMPOSER_LANG = 'nyrocms.events.composerLang';

    protected $row;

    public function __construct(Composable $row)
    {
        $this->row = $row;
    }

    /**
     * @return Composable
     */
    public function getRow()
    {
        return $this->row;
    }
}
