<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Gedmo\Mapping\Annotation as Gedmo;

abstract class UserLogin
{
    protected $id;

    protected ?User $user = null;

    protected ?string $ipAddress = null;

    protected ?string $place = null;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    public function getId()
    {
        return $this->id;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setInserted(DateTimeInterface $inserted): self
    {
        $this->inserted = $inserted;

        return $this;
    }

    public function getInserted(): ?DateTimeInterface
    {
        return $this->inserted;
    }
}
