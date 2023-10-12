<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTime;
use DateTimeInterface;

abstract class ContentHandlerConfigLog
{
    protected $id;

    protected ?string $action = null;

    protected ?DateTimeInterface $loggedAt = null;

    protected ?string $objectClass = null;

    protected ?string $objectId = null;

    protected ?int $version = null;

    protected ?array $data = null;

    protected ?string $username = null;

    protected ?string $locale = null;

    public function getId()
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(?string $objectClass): static
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(?string $objectId): static
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getLoggedAt(): ?DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(): static
    {
        $this->loggedAt = new DateTime();

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
