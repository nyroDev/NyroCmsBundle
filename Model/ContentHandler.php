<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ContentHandler
{
    protected $id;

    #[Assert\NotBlank]
    protected ?string $name = null;

    #[Assert\NotBlank]
    protected ?string $class = null;

    protected ?bool $hasAdmin = null;

    protected Collection $contents;

    protected Collection $contentHandlerConfigs;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
        $this->contentHandlerConfigs = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setClass(?string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setHasAdmin(?bool $hasAdmin): static
    {
        $this->hasAdmin = $hasAdmin;

        return $this;
    }

    public function getHasAdmin(): ?bool
    {
        return $this->hasAdmin;
    }

    public function addContent(Content $contents): static
    {
        $this->contents[] = $contents;

        return $this;
    }

    public function removeContent(Content $contents): static
    {
        $this->contents->removeElement($contents);

        return $this;
    }

    public function setContents(Collection $contents): static
    {
        $this->contents = $contents;

        return $this;
    }

    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContentHandlerConfig(ContentHandlerConfig $contentHandlerConfigs): static
    {
        $this->contentHandlerConfigs[] = $contentHandlerConfigs;

        return $this;
    }

    public function removeContentHandlerConfig(ContentHandlerConfig $contentHandlerConfigs): static
    {
        $this->contentHandlerConfigs->removeElement($contentHandlerConfigs);

        return $this;
    }

    public function setContentHandlerConfigs(Collection $contentHandlerConfigs): static
    {
        $this->contentHandlerConfigs = $contentHandlerConfigs;

        return $this;
    }

    public function getContentHandlerConfigs(): Collection
    {
        return $this->contentHandlerConfigs;
    }

    public function getContentHandlerConfigsByIdent(): array
    {
        $ret = [];

        foreach ($this->getContentHandlerConfigs() as $cfg) {
            $ret[$cfg->getconfigIdent()] = $cfg;
        }

        return $ret;
    }

    public function setInserted(DateTimeInterface $inserted): static
    {
        $this->inserted = $inserted;

        return $this;
    }

    public function getInserted(): ?DateTimeInterface
    {
        return $this->inserted;
    }

    public function setUpdated(DateTimeInterface $updated): static
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
