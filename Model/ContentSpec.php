<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Model\Sharable;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class ContentSpec extends AbstractUploadable implements Composable, Sharable
{
    public const STATE_DISABLED = 0;
    public const STATE_ACTIVE = 1;
    public const STATE_INVISIBLE = 2;

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

    public function setTranslatableLocale(?string $locale): static
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

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setIntro(?string $intro): static
    {
        $this->intro = $intro;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setContent(?array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContentText(?string $contentText): static
    {
        $this->contentText = $contentText;

        return $this;
    }

    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setFirstImage(?string $firstImage): static
    {
        $this->firstImage = $firstImage;

        return $this;
    }

    public function getFirstImage(): ?string
    {
        return $this->firstImage;
    }

    public function setposition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getposition(): ?int
    {
        return $this->position;
    }

    public function setDateSpec(?DateTimeInterface $dateSpec): static
    {
        $this->dateSpec = $dateSpec;

        return $this;
    }

    public function getDateSpec(): ?DateTimeInterface
    {
        return $this->dateSpec;
    }

    public function setFeatured(?bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function getFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setState(?int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setContentHandler(?ContentHandler $contentHandler): static
    {
        $this->contentHandler = $contentHandler;

        return $this;
    }

    public function getContentHandler(): ?ContentHandler
    {
        return $this->contentHandler;
    }

    public function setValidStart(?DateTimeInterface $validStart): static
    {
        $this->validStart = $validStart;

        return $this;
    }

    public function getValidStart(): ?DateTimeInterface
    {
        return $this->validStart;
    }

    public function setValidEnd(?DateTimeInterface $validEnd): static
    {
        $this->validEnd = $validEnd;

        return $this;
    }

    public function getValidEnd(): ?DateTimeInterface
    {
        return $this->validEnd;
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

    public function setDeleted(?DateTimeInterface $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): ?DateTimeInterface
    {
        return $this->deleted;
    }

    public function addTranslation(object $translations): static
    {
        $this->translations[] = $translations;

        return $this;
    }

    public function removeTranslation(object $translations): static
    {
        $this->translations->removeElement($translations);

        return $this;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
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
        $text = $this->getIntro() ? $this->getIntro() : str_replace('&rsquo;', "'", $this->getContentText());
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

    public function setInContent(string $key, mixed $value): static
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

    public function setInData(string $key, mixed $value): static
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

    protected function getFileFields()
    {
        return $this->sharableGetFileFields();
    }
}
