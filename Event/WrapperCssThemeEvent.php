<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use NyroDev\NyroCmsBundle\Model\Composable;

class WrapperCssThemeEvent extends Event
{
    const WRAPPER_CSS_THEME = 'nyrocms.events.wrapperCssTheme';

    const POSITION_NORMAL = 'normal';
    const POSITION_ADMIN_HTML = 'admin.html';
    const POSITION_ADMIN_BODY = 'admin.body';

    protected $row,
                $wrapperCssTheme = array();

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
