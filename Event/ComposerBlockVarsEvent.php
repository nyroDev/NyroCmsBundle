<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerBlockVarsEvent extends Event
{
    public const COMPOSER_BLOCK_VARS = 'nyrocms.events.composerBlockVars';

    public function __construct(
        protected readonly Composable $row,
        protected string $template,
        protected array $vars,
        protected array $blockConfig,
    ) {
    }

    public function getRow(): Composable
    {
        return $this->row;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function setVars(array $vars): void
    {
        $this->vars = $vars;
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function setInVars(string $key, mixed $value): void
    {
        $this->vars[$key] = $value;
    }

    public function getInVars(string $key): mixed
    {
        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }

    public function getInBlockVars(string $key): mixed
    {
        return isset($this->vars['block'][$key]) ? $this->vars['block'][$key] : null;
    }

    public function setInBlockVars(string $key, mixed $value): void
    {
        $blocks = $this->vars['block'];
        $blocks[$key] = $value;

        $this->vars['block'] = $blocks;
    }

    public function getInBlockContentVars(string $key): mixed
    {
        $contents = $this->getInBlockVars('contents');
        if (!is_array($contents)) {
            return null;
        }

        return isset($contents[$key]) ? $contents[$key] : null;
    }

    public function setInBlockContentVars(string $key, mixed $value): void
    {
        $contents = $this->getInBlockVars('contents');
        if (!is_array($contents)) {
            $contents = [];
        }

        $contents[$key] = $value;

        $this->setInBlockVars('contents', $contents);
    }

    public function getBlockType(): mixed
    {
        return $this->getInBlockVars('type');
    }

    public function getBlockConfig(): array
    {
        return $this->blockConfig;
    }

    public function getInBlockConfig(string $key): mixed
    {
        return isset($this->blockConfig[$key]) ? $this->blockConfig[$key] : null;
    }
}
