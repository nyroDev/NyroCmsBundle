<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class Template implements Composable
{
    public const STATE_DISABLED = 0;
    public const STATE_ACTIVE = 1;

    protected $id;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $title = null;

    #[Gedmo\Versioned]
    protected ?string $defaultFor = null;

    #[Gedmo\Versioned]
    protected ?string $theme = null;

    #[Gedmo\Versioned]
    protected ?array $content = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?int $state = self::STATE_ACTIVE;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected ?DateTimeInterface $deleted = null;

    public function getId()
    {
        return $this->id;
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

    public function setDefaultFor(?string $defaultFor): self
    {
        $this->defaultFor = $defaultFor;

        return $this;
    }

    public function getDefaultFor(): ?string
    {
        return $this->defaultFor;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setContent(?array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
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

    public function getParent(): mixed
    {
        return false;
    }
}
