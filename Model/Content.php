<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Model\Sharable;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\Tree(type: 'nested')]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class Content extends AbstractUploadable implements Composable, ComposableHandler, Sharable
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
        $this->children = new ArrayCollection();
        $this->relateds = new ArrayCollection();
        $this->translations = new ArrayCollection();
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

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setTheme(?string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
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

    public function setFirstImage(?string $firstImage): static
    {
        $this->firstImage = $firstImage;

        return $this;
    }

    public function getFirstImage(): ?string
    {
        return $this->firstImage;
    }

    public function setGoUrl(?string $goUrl): static
    {
        $this->goUrl = $goUrl;

        return $this;
    }

    public function getGoUrl(): ?string
    {
        return $this->goUrl;
    }

    public function setGoBlank(?bool $goBlank): static
    {
        $this->goBlank = $goBlank;

        return $this;
    }

    public function getGoBlank(): ?bool
    {
        return $this->goBlank;
    }

    public function setRedirectToChildren(?bool $redirectToChildren): static
    {
        $this->redirectToChildren = $redirectToChildren;

        return $this;
    }

    public function getRedirectToChildren(): ?bool
    {
        return $this->redirectToChildren;
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

    public function setHandler(?string $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function setHost(?string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setLocales(?string $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function getLocales(): ?string
    {
        return $this->locales;
    }

    public function setXmlSitemap(?bool $xmlSitemap): static
    {
        $this->xmlSitemap = $xmlSitemap;

        return $this;
    }

    public function getXmlSitemap(): ?bool
    {
        return $this->xmlSitemap;
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

    public function setMenuOption(?string $menuOption): static
    {
        $this->menuOption = $menuOption;

        return $this;
    }

    public function getMenuOption(): ?string
    {
        return $this->menuOption;
    }

    public function setLft(?int $lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setRgt(?int $rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setRoot(?int $root): static
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot(): ?int
    {
        return $this->root;
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

    public function setParent(Content $parent = null): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Content
    {
        return $this->parent;
    }

    public function addChild(Content $children): static
    {
        $this->children[] = $children;

        return $this;
    }

    public function removeChild(Content $children): static
    {
        $this->children->removeElement($children);

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addRelated(Content $relateds): static
    {
        $this->relateds[] = $relateds;

        return $this;
    }

    public function removeRelated(Content $relateds): static
    {
        $this->relateds->removeElement($relateds);

        return $this;
    }

    public function getRelateds(): Collection
    {
        return $this->relateds;
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

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getVeryParent(): static
    {
        return $this->getParent() ? $this->getParent()->getVeryParent() : $this;
    }

    public function getParentLvl(int $level): static
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
        $text = str_replace('&rsquo;', "'", $this->getContentText());
        if (mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit).'...';
        }

        return $text;
    }

    protected function getFileFields()
    {
        return $this->sharableGetFileFields();
    }
}
