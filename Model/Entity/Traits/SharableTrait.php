<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Mapping\Annotation as Gedmo;

trait SharableTrait
{

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=250, nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="og_title", type="string", length=250, nullable=true)
     * @Gedmo\Translatable
     */
    protected $ogTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="og_description", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $ogDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="og_image_file", type="string", length=250, nullable=true)
     */
    protected $ogImageFile;

    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getOgTitle()
    {
        return $this->ogTitle;
    }

    public function setOgTitle($ogTitle)
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    public function getOgDescription()
    {
        return $this->ogDescription;
    }

    public function setOgDescription($ogDescription)
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    public function getOgImageFile()
    {
        return $this->ogImageFile;
    }

    public function setOgImageFile($ogImageFile)
    {
        $this->ogImageFile = $ogImageFile;

        return $this;
    }


	/**
	 * @Assert\Image()
	 */
	protected $ogImage;

	/**
	 * Sets Image.
	 *
	 * @param UploadedFile $image
	 */
	public function setOgImage(UploadedFile $ogImage = null) {
		$this->setUploadFile('ogImage', $ogImage);
	}

	/**
	 * Get Image.
	 *
	 * @return UploadedFile
	 */
	public function getOgImage() {
		return $this->ogImage;
	}

    protected function getFileFields() {
        return array(
            'ogImage' => array(
                AbstractUploadable::CONFIG_FIELD=>'ogImageFile',
                AbstractUploadable::CONFIG_DIR=>'uploads/sharable'
            ),
        );
    }
}