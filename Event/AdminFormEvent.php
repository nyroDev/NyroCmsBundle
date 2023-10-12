<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AdminFormEvent extends Event
{
    public const UPDATE_CONTENT = 'nyrocms.events.admin.form.update.content';
    public const BEFOREFLUSH_CONTENT = 'nyrocms.events.admin.form.beforeFlush.content';
    public const AFTERFLUSH_CONTENT = 'nyrocms.events.admin.form.afterFlush.content';

    public const UPDATE_USER = 'nyrocms.events.admin.form.update.user';
    public const BEFOREFLUSH_USER = 'nyrocms.events.admin.form.beforeFlush.user';
    public const AFTERFLUSH_USER = 'nyrocms.events.admin.form.afterFlush.user';

    protected $action;
    protected $row;
    protected $form;
    protected $translations;

    public function __construct($action, $row, $form = null)
    {
        $this->action = $action;
        $this->row = $row;
        $this->form = $form;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}
