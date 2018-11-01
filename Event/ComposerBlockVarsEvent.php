<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use NyroDev\NyroCmsBundle\Model\Composable;

class ComposerBlockVarsEvent extends Event
{
    const COMPOSER_BLOCK_VARS = 'nyrocms.events.composerBlockVars';

    protected $row;
    protected $template;
    protected $vars;

    public function __construct(Composable $row, $template, array $vars)
    {
        $this->row = $row;
        $this->template = $template;
        $this->vars = $vars;
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

    public function getBlockType()
    {
        return $this->getInBlockVars('type');
    }
}
