<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

abstract class UserRole
{
    protected $id;

    #[Assert\NotBlank]
    protected ?string $name = null;

    protected ?string $roleName = null;

    protected ?bool $internal = false;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected Collection $contents;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setRoleName(?string $roleName): self
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setInternal(?bool $internal): self
    {
        $this->internal = $internal;

        return $this;
    }

    public function getInternal(): ?bool
    {
        return $this->internal;
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

    public function setUpdated(DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function addContent(Content $contents): self
    {
        $this->contents[] = $contents;

        return $this;
    }

    public function removeContent(Content $contents): self
    {
        $this->contents->removeElement($contents);

        return $this;
    }

    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getSecurityRoleName(): string
    {
        $name = $this->getRoleName() ? $this->getRoleName() : $this->getName();

        return 'ROLE_'.strtoupper(str_replace(' ', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
    }
}
