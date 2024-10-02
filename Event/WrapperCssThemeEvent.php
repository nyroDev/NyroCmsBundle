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

    protected array $wrapperCssTheme = [];

    public function __construct(
        protected readonly Composable $row,
    ) {
    }

    public function getRow(): Composable
    {
        return $this->row;
    }

    public function setWrapperCssTheme(string $wrapperCssTheme, string $position = self::POSITION_NORMAL): void
    {
        $this->wrapperCssTheme[$position] = $wrapperCssTheme;
    }

    public function getWrapperCssTheme(string $position = self::POSITION_NORMAL): ?string
    {
        return isset($this->wrapperCssTheme[$position]) ? $this->wrapperCssTheme[$position] : null;
    }
}
