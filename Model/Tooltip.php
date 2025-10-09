<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\StringablePropertyable;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class Tooltip implements StringablePropertyable
{
    public static function getStringableProperty(): string
    {
        return 'title';
    }

    protected $id;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $ident = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $title = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $content = null;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected ?DateTimeInterface $deleted = null;

    public function getId()
    {
        return $this->id;
    }

    public function setIdent(?string $ident): self
    {
        $this->ident = $ident;

        return $this;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
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

    public function setDeleted(?DateTimeInterface $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): ?DateTimeInterface
    {
        return $this->deleted;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }
}
