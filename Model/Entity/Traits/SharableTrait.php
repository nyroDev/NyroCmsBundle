<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

trait SharableTrait
{
    #[ORM\Column(length: 250, nullable: true)]
    protected ?string $metaTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $metaDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $metaKeywords = null;

    #[ORM\Column(length: 250, nullable: true)]
    protected ?string $ogTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $ogDescription = null;

    #[ORM\Column(length: 250, nullable: true)]
    protected ?string $ogImageFile = null;

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): self
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): self
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    public function getShareOthers(): ?array
    {
        return null;
    }

    public function getOgImageFile(): ?string
    {
        return $this->ogImageFile;
    }

    public function setOgImageFile(?string $ogImageFile): self
    {
        $this->ogImageFile = $ogImageFile;

        return $this;
    }

    #[Assert\Image]
    protected $ogImage;

    public function setOgImage(?UploadedFile $ogImage = null)
    {
        $this->setUploadFile('ogImage', $ogImage);
    }

    public function getOgImage(): ?UploadedFile
    {
        return $this->ogImage;
    }

    protected function getFileFields(): array
    {
        return [
            'ogImage' => [
                AbstractUploadable::CONFIG_FIELD => 'ogImageFile',
                AbstractUploadable::CONFIG_DIR => 'uploads/sharable',
            ],
        ];
    }

    public function getShareOgImage(): ?string
    {
        return $this->getAbsolutePath('ogImage');
    }
}
