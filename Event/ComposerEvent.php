<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerEvent extends Event
{
    const COMPOSER_DEFAULT = 'nyrocms.events.composerDefault';
    const COMPOSER_LANG_SAME = 'nyrocms.events.composerLangSame';
    const COMPOSER_LANG = 'nyrocms.events.composerLang';

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
