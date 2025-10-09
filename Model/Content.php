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

#[Gedmo\Tree(type: 'nested')]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class Content extends AbstractUploadable implements ContentRootable, ComposableTranslatable, ComposableContentSummary, ComposableHandler, Sharable, StringablePropertyable
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
    protected ?string $url = null;

    #[Gedmo\Versioned]
    protected ?string $theme = null;

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
    protected ?string $firstImage = null;

    #[Assert\Url]
    #[Gedmo\Translatable]
    #[Gedmo\Versioned]
    protected ?string $goUrl = null;

    #[Gedmo\Versioned]
    protected ?bool $goBlank = null;

    #[Gedmo\Versioned]
    protected ?bool $redirectToChildren = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?int $state = self::STATE_ACTIVE;

    #[Gedmo\Versioned]
    protected ?string $handler = null;

    #[Gedmo\Versioned]
    protected ?string $dynamicHandler = null;

    #[Gedmo\Versioned]
    protected ?string $host = null;

    #[Gedmo\Versioned]
    protected ?string $locales = null;

    #[Gedmo\Versioned]
    protected ?bool $xmlSitemap = null;

    #[Gedmo\Versioned]
    protected ?ContentHandler $contentHandler = null;

    #[Gedmo\Versioned]
    protected ?string $menuOption = null;

    #[Gedmo\TreeLeft]
    protected ?int $lft = null;

    #[Gedmo\TreeRight]
    protected ?int $rgt = null;

    #[Gedmo\TreeLevel]
    protected ?int $level = null;

    #[Gedmo\TreeRoot]
    protected ?int $root = null;

    #[Gedmo\TreeParent]
    #[Gedmo\Versioned]
    protected ?Content $parent = null;

    protected Collection $children;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected ?DateTimeInterface $deleted = null;

    protected Collection $relateds;

    protected Collection $translations;

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
        $this->children = new ArrayCollection();
        $this->relateds = new ArrayCollection();
        $this->translations = new ArrayCollection();
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

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
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

    public function setFirstImage(?string $firstImage): self
    {
        $this->firstImage = $firstImage;

        return $this;
    }

    public function getFirstImage(): ?string
    {
        return $this->firstImage;
    }

    public function setGoUrl(?string $goUrl): self
    {
        $this->goUrl = $goUrl;

        return $this;
    }

    public function getGoUrl(): ?string
    {
        return $this->goUrl;
    }

    public function setGoBlank(?bool $goBlank): self
    {
        $this->goBlank = $goBlank;

        return $this;
    }

    public function getGoBlank(): ?bool
    {
        return $this->goBlank;
    }

    public function setRedirectToChildren(?bool $redirectToChildren): self
    {
        $this->redirectToChildren = $redirectToChildren;

        return $this;
    }

    public function getRedirectToChildren(): ?bool
    {
        return $this->redirectToChildren;
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

    public function setHandler(?string $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function setDynamicHandler(?string $dynamicHandler): self
    {
        $this->dynamicHandler = $dynamicHandler;

        return $this;
    }

    public function getDynamicHandler(): ?string
    {
        return $this->dynamicHandler;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setLocales(?string $locales): self
    {
        $this->locales = $locales;

        return $this;
    }

    public function getLocales(): ?string
    {
        return $this->locales;
    }

    public function setXmlSitemap(?bool $xmlSitemap): self
    {
        $this->xmlSitemap = $xmlSitemap;

        return $this;
    }

    public function getXmlSitemap(): ?bool
    {
        return $this->xmlSitemap;
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

    public function setMenuOption(?string $menuOption): self
    {
        $this->menuOption = $menuOption;

        return $this;
    }

    public function getMenuOption(): ?string
    {
        return $this->menuOption;
    }

    public function setLft(?int $lft): self
    {
        $this->lft = $lft;

        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setRgt(?int $rgt): self
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setRoot(?int $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot(): ?int
    {
        return $this->root;
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

    public function setParent(?Content $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Content
    {
        return $this->parent;
    }

    public function addChild(Content $children): self
    {
        $this->children[] = $children;

        return $this;
    }

    public function removeChild(Content $children): self
    {
        $this->children->removeElement($children);

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addRelated(Content $relateds): self
    {
        $this->relateds[] = $relateds;

        return $this;
    }

    public function removeRelated(Content $relateds): self
    {
        $this->relateds->removeElement($relateds);

        return $this;
    }

    public function getRelateds(): Collection
    {
        return $this->relateds;
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

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getVeryParent(): self
    {
        return $this->getParent() ? $this->getParent()->getVeryParent() : $this;
    }

    public function getParentLvl(int $level): self
    {
        return $this->getLevel() > $level && $this->getParent() ? $this->getParent()->getParentLvl($level) : $this;
    }

    public function getParentTheme(): ?string
    {
        if (!$this->getParent()) {
            return $this->getTheme();
        }

        return $this->getTheme() ? $this->getTheme() : $this->getParent()->getParentTheme();
    }

    public function getSummary(int $limit = 250): string
    {
        $text = str_replace('&rsquo;', "'", $this->getContentReadableText());
        if (mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit).'...';
        }

        return $text;
    }

    protected function getFileFields(): array
    {
        return $this->sharableGetFileFields();
    }
}
