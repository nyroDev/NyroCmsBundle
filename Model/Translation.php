<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

abstract class Translation
{
    protected $id;

    #[Assert\NotBlank]
    protected ?string $domain = null;

    #[Assert\NotBlank]
    protected ?string $locale = null;

    #[Assert\NotBlank]
    protected ?string $ident = null;

    protected ?string $translation = null;

    protected ?bool $html = false;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    public function getId()
    {
        return $this->id;
    }

    public function setDomain(?string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setIdent(?string $ident): static
    {
        $this->ident = $ident;

        return $this;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setTranslation(?string $translation): static
    {
        $this->translation = $translation;

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setHtml(?bool $html): static
    {
        $this->html = $html;

        return $this;
    }

    public function getHtml(): ?bool
    {
        return $this->html;
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
}
