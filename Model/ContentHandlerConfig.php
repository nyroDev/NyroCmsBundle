<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Model\StringablePropertyable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ContentHandlerConfig extends AbstractUploadable implements StringablePropertyable
{
    public const TYPE_STRING = 'string';
    public const TYPE_TEXT = 'text';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_NUMBER = 'number';
    public const TYPE_BOOL = 'number';

    public static function getStringableProperty(): string
    {
        return 'name';
    }

    protected $id;

    protected ?ContentHandler $contentHandler = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $name = null;

    #[Assert\NotBlank]
    protected ?string $configIdent = null;

    #[Assert\NotBlank]
    protected ?string $configType = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $valueText;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?DateTimeInterface $valueDate = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?float $valueNumber = null;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected Collection $translations;

    #[Gedmo\Locale]
    protected ?string $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function setTranslatableLocale(?string $locale): self
    {
        $this->locale = $locale;
    }

    public function getTranslatableLocale(): ?string
    {
        return $this->locale;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setContentHandler(?ContentHandler $contentHandler): self
    {
        $this->contentHandler = $contentHandler;

        return $this;
    }

    public function getContentHandler(): ?ContentHandler
    {
        return $this->contentHandler;
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

    public function setConfigIdent(?string $configIdent): self
    {
        $this->configIdent = $configIdent;

        return $this;
    }

    public function getConfigIdent(): ?string
    {
        return $this->configIdent;
    }

    public function setConfigType(?string $configType): self
    {
        $this->configType = $configType;

        return $this;
    }

    public function getConfigType(): ?string
    {
        return $this->configType;
    }

    public function setValueText(?string $valueText): self
    {
        $this->valueText = $valueText;

        return $this;
    }

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueDate(?DateTimeInterface $valueDate): self
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    public function getValueDate(): ?DateTimeInterface
    {
        return $this->valueDate;
    }

    public function setValueNumber(?float $valueNumber): self
    {
        $this->valueNumber = $valueNumber;

        return $this;
    }

    public function getValueNumber(): ?float
    {
        return $this->valueNumber;
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

    public function addTranslation(object $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }

        return $this;
    }

    public function removeTranslation(object $translations): self
    {
        $this->translations->removeElement($translations);

        return $this;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getField(): string
    {
        $field = 'valueText';
        switch ($this->getConfigType()) {
            case self::TYPE_NUMBER:
            case self::TYPE_BOOL:
                $field = 'valueNumber';
                break;
            case self::TYPE_DATE:
            case self::TYPE_DATETIME:
                $field = 'valueDate';
                break;
        }

        return $field;
    }

    public function getValue(): mixed
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $field = $this->getField();
        $value = $accessor->getValue($this, $field);

        switch ($this->getConfigType()) {
            case self::TYPE_BOOL:
                $value = (bool) $value;
                break;
        }

        return $value;
    }

    public function setValue(mixed $value): self
    {
        $field = $this->getField();

        switch ($this->getConfigType()) {
            case self::TYPE_BOOL:
                $value = $value ? 1 : 0;
                break;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($this, $field, $value);

        return $this;
    }

    public function getTranslationsByLocale(): array
    {
        $ret = [];

        foreach ($this->getTranslations() as $tr) {
            $ret[$tr->getLocale()] = $tr;
        }

        return $ret;
    }

    protected function getFileFields(): array
    {
        return [];
    }
}
