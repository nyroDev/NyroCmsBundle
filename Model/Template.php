<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class Template implements Composable, JsonSerializable
{
    public const STATE_DISABLED = 0;
    public const STATE_ACTIVE = 1;

    protected $id;

    #[Gedmo\Versioned]
    protected ?TemplateCategory $templateCategory = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $title = null;

    #[Gedmo\Versioned]
    protected ?string $icon = null;

    #[Gedmo\Versioned]
    protected ?bool $custom = true;

    #[Gedmo\Versioned]
    protected ?string $defaultFor = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected array $enabledFor = [];

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

    public function setTemplateCategory(?TemplateCategory $templateCategory): self
    {
        $this->templateCategory = $templateCategory;

        return $this;
    }

    public function getTemplateCategory(): ?TemplateCategory
    {
        return $this->templateCategory;
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

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setCustom(?bool $custom): self
    {
        $this->custom = $custom;

        return $this;
    }

    public function getCustom(): ?bool
    {
        return $this->custom;
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

    public function getEnabledFor(): array
    {
        return $this->enabledFor;
    }

    public function setEnabledFor(array $enabledFor): self
    {
        $this->enabledFor = $enabledFor;

        return $this;
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'category' => $this->getTemplateCategory(),
            'title' => $this->getTitle(),
            'icon' => $this->getIcon(),
            'custom' => $this->getCustom(),
            'theme' => $this->getTheme(),
        ];
    }
}
