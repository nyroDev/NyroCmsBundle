<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Model\Sharable;
use NyroDev\UtilityBundle\Model\StringablePropertyable;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class ContentSpec extends AbstractUploadable implements ContentRootable, ComposableTranslatable, ComposableContentSummary, Sharable, StringablePropertyable
{
    public const STATE_DISABLED = 0;
    public const STATE_ACTIVE = 1;
    public const STATE_INVISIBLE = 2;

    public static function getStringableProperty(): string
    {
        return 'title';
    }

    protected $id;

    #[Assert\NotBlank]
    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $title = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $intro = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?array $content = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $contentText = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $contentReadableText = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?array $data = null;

    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $firstImage = null;

    #[Gedmo\SortablePosition]
    #[Gedmo\Versioned]
    protected ?int $position = null;

    protected ?DateTimeInterface $dateSpec = null;

    #[Gedmo\Versioned]
    protected ?bool $featured = false;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?int $state = self::STATE_ACTIVE;

    #[Gedmo\Versioned]
    protected ?DateTimeInterface $validStart = null;

    #[Gedmo\Versioned]
    protected ?DateTimeInterface $validEnd = null;

    #[Gedmo\SortableGroup]
    protected ?ContentHandler $contentHandler = null;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected ?DateTimeInterface $deleted = null;

    protected Collection $translations;

    protected Collection $contents;

    #[Gedmo\Locale]
    protected ?string $locale = null;

    public function setTranslatableLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslatableLocale(): ?string
    {
        return $this->locale;
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->contents = new ArrayCollection();
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

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
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

    public function setContentText(?string $contentText): self
    {
        $this->contentText = $contentText;

        return $this;
    }

    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    public function setContentReadableText(?string $contentReadableText): self
    {
        $this->contentReadableText = $contentReadableText;

        return $this;
    }

    public function getContentReadableText(): ?string
    {
        return $this->contentReadableText;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setFirstImage(?string $firstImage): self
    {
        $this->firstImage = $firstImage;

        return $this;
    }

    public function getFirstImage(): ?string
    {
        return $this->firstImage;
    }

    public function setposition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getposition(): ?int
    {
        return $this->position;
    }

    public function setDateSpec(?DateTimeInterface $dateSpec): self
    {
        $this->dateSpec = $dateSpec;

        return $this;
    }

    public function getDateSpec(): ?DateTimeInterface
    {
        return $this->dateSpec;
    }

    public function setFeatured(?bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function getFeatured(): ?bool
    {
        return $this->featured;
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

    public function setContentHandler(?ContentHandler $contentHandler): self
    {
        $this->contentHandler = $contentHandler;

        return $this;
    }

    public function getContentHandler(): ?ContentHandler
    {
        return $this->contentHandler;
    }

    public function setValidStart(?DateTimeInterface $validStart): self
    {
        $this->validStart = $validStart;

        return $this;
    }

    public function getValidStart(): ?DateTimeInterface
    {
        return $this->validStart;
    }

    public function setValidEnd(?DateTimeInterface $validEnd): self
    {
        $this->validEnd = $validEnd;

        return $this;
    }

    public function getValidEnd(): ?DateTimeInterface
    {
        return $this->validEnd;
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

    public function addTranslation(object $translations): self
    {
        $this->translations[] = $translations;

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
        return $this->getTitle();
    }

    public function getSummary($limit = 200): ?string
    {
        $text = $this->getIntro() ? $this->getIntro() : str_replace('&rsquo;', "'", $this->getContentReadableText());
        if (mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit).'...';
        }

        return $text;
    }

    public function getInContent(string $key): mixed
    {
        $content = $this->getContent();

        return isset($content[$key]) ? $content[$key] : null;
    }

    public function setInContent(string $key, mixed $value): self
    {
        $content = $this->getContent();
        if (is_null($value)) {
            if (isset($content[$key])) {
                unset($content[$key]);
            }
        } else {
            $content[$key] = $value;
        }

        return $this->setContent($content);
    }

    public function getInData(string $key): mixed
    {
        $content = $this->getData();

        return isset($content[$key]) ? $content[$key] : null;
    }

    public function setInData(string $key, mixed $value): self
    {
        $content = $this->getData();
        if (is_null($value)) {
            if (isset($content[$key])) {
                unset($content[$key]);
            }
        } else {
            $content[$key] = $value;
        }

        return $this->setData($content);
    }

    public function getParent(): Content
    {
        return $this->getContentHandler()->getContents()->get(0);
    }

    public function getTheme(): ?string
    {
        return $this->getParent()->getTheme();
    }

    public function getVeryParent()
    {
        return $this->getParent()->getVeryParent();
    }

    protected function getFileFields(): array
    {
        return $this->sharableGetFileFields();
    }
}
