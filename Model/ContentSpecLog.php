<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTime;
use DateTimeInterface;

abstract class ContentSpecLog
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

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(?string $objectClass): self
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(?string $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getLoggedAt(): ?DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(): self
    {
        $this->loggedAt = new DateTime();

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
