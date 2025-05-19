<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class TemplateCategory implements JsonSerializable
{
    protected $id;

    #[Assert\NotBlank]
    protected ?string $title = null;

    protected ?string $icon = null;

    protected Collection $templates;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected ?DateTimeInterface $deleted = null;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

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

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function addTemplate(Template $template): self
    {
        $this->templates[] = $template;

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        $this->templates->removeElement($template);

        return $this;
    }

    public function setTemplates(Collection $templates): self
    {
        $this->templates = $templates;

        return $this;
    }

    public function getTemplates(): Collection
    {
        return $this->templates;
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'icon' => $this->getIcon(),
        ];
    }
}
