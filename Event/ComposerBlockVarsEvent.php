<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Contracts\EventDispatcher\Event;

class ComposerBlockVarsEvent extends Event
{
    public const COMPOSER_BLOCK_VARS = 'nyrocms.events.composerBlockVars';

    protected $row;
    protected $template;
    protected $vars;
    protected $blockConfig;

    public function __construct(Composable $row, $template, array $vars, array $blockConfig)
    {
        $this->row = $row;
        $this->template = $template;
        $this->vars = $vars;
        $this->blockConfig = $blockConfig;
    }

    /**
     * @return Composable
     */
    public function getRow()
    {
        return $this->row;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function setInVars($key, $value)
    {
        $this->vars[$key] = $value;
    }

    public function getInVars($key)
    {
        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }

    public function getInBlockVars($key)
    {
        return isset($this->vars['block'][$key]) ? $this->vars['block'][$key] : null;
    }

    public function setInBlockVars($key, $value)
    {
        $blocks = $this->vars['block'];
        $blocks[$key] = $value;

        $this->vars['block'] = $blocks;
    }

    public function getInBlockContentVars($key)
    {
        $contents = $this->getInBlockVars('contents');
        if (!is_array($contents)) {
            return null;
        }

        return isset($contents[$key]) ? $contents[$key] : null;
    }

    public function setInBlockContentVars($key, $value)
    {
        $contents = $this->getInBlockVars('contents');
        if (!is_array($contents)) {
            $contents = [];
        }

        $contents[$key] = $value;

        $this->setInBlockVars('contents', $contents);
    }

    public function getBlockType()
    {
        return $this->getInBlockVars('type');
    }

    public function getBlockConfig()
    {
        return $this->blockConfig;
    }

    public function getInBlockConfig($key)
    {
        return isset($this->blockConfig[$key]) ? $this->blockConfig[$key] : null;
    }
}
