<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class WrapperCssThemeEvent extends Event
{
    public const WRAPPER_CSS_THEME = 'nyrocms.events.wrapperCssTheme';

    public const POSITION_NORMAL = 'normal';
    public const POSITION_ADMIN_HTML = 'admin.html';
    public const POSITION_ADMIN_BODY = 'admin.body';

    protected $row;
    protected $wrapperCssTheme = [];

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

    public function setWrapperCssTheme($wrapperCssTheme, $position = self::POSITION_NORMAL)
    {
        $this->wrapperCssTheme[$position] = $wrapperCssTheme;
    }

    public function getWrapperCssTheme($position = self::POSITION_NORMAL)
    {
        return isset($this->wrapperCssTheme[$position]) ? $this->wrapperCssTheme[$position] : null;
    }
}
