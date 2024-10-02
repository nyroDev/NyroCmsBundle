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

    protected ?array $translations;

    public function __construct(
        protected readonly string $action,
        protected readonly mixed $row,
        protected readonly mixed $form = null,
    ) {
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

    public function getTranslations(): ?array
    {
        return $this->translations;
    }
}
