<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 */
abstract class ContentSpec implements Composable {
	
	const STATE_DISABLED = 0;
	const STATE_ACTIVE = 1;
	const STATE_INVISIBLE = 2;
	
    protected $id;
	
    /**
     * @var string
	 * @Assert\NotBlank()
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     */
    protected $title;
	
    /**
     * @var string
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     */
    protected $intro;

    /**
     * @var array
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     */
    protected $content;

    /**
     * @var string
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     */
    protected $contentText;

    /**
     * @var string
     * @Gedmo\Translatable
	 * @Gedmo\Versioned
     */
	protected $firstImage;

    /**
     * @var integer
     * @Gedmo\SortablePosition
	 * @Gedmo\Versioned
     */
    protected $position;

    /**
     * @var boolean
	 * @Gedmo\Versioned
     */
    protected $featured = false;

    /**
     * @var smallint
	 * @Assert\NotBlank()
	 * @Gedmo\Versioned
     */
    protected $state = self::STATE_ACTIVE;

    /**
     * @var \DateTime
	 * @Gedmo\Versioned
     */
    protected $validStart;

    /**
     * @var \DateTime
	 * @Gedmo\Versioned
     */
    protected $validEnd;

    /**
     * @var ContentHandler
     * @Gedmo\SortableGroup
     */
    protected $contentHandler;

    /**
     * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
     */
    protected $inserted;

    /**
     * @var \DateTime
	 * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @var \DateTime
     */
    protected $deleted;
	
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $translations;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	protected $contents;

    /**
     * @var string
	 * @Gedmo\Locale
     */
    protected $locale;
	
    public function setTranslatableLocale($locale) {
        $this->locale = $locale;
    }
	
	public function getTranslatableLocale() {
		return $this->locale;
	}
	

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ContentSpec
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set intro
     *
     * @param string $intro
     * @return ContentSpec
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * Get intro
     *
     * @return string 
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * Set content
     *
     * @param array $content
     * @return ContentSpec
     */
    public function setContent(array $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return array 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set contentText
     *
     * @param string $contentText
     * @return ContentSpec
     */
    public function setContentText($contentText)
    {
        $this->contentText = $contentText;

        return $this;
    }

    /**
     * Get contentText
     *
     * @return string 
     */
    public function getContentText()
    {
        return $this->contentText;
    }

    /**
     * Set firstImage
     *
     * @param string $firstImage
     * @return ContentSpec
     */
    public function setFirstImage($firstImage)
    {
        $this->firstImage = $firstImage;

        return $this;
    }

    /**
     * Get firstImage
     *
     * @return string 
     */
    public function getFirstImage()
    {
        return $this->firstImage;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return ContentSpec
     */
    public function setposition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getposition()
    {
        return $this->position;
    }

    /**
     * Set featured
     *
     * @param boolean $featured
     * @return ContentSpec
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return boolean 
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Set state
     *
     * @param smallint $state
     * @return ContentSpec
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return smallint 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set contentHandler
     *
     * @param ContentHandler $contentHandler
     * @return ContentSpec
     */
    public function setContentHandler($contentHandler)
    {
        $this->contentHandler = $contentHandler;

        return $this;
    }

    /**
     * Get contentHandler
     *
     * @return ContentHandler 
     */
    public function getContentHandler()
    {
        return $this->contentHandler;
    }

    /**
     * Set validStart
     *
     * @param \DateTime $validStart
     * @return ContentSpec
     */
    public function setValidStart($validStart)
    {
        $this->validStart = $validStart;

        return $this;
    }

    /**
     * Get validStart
     *
     * @return \DateTime 
     */
    public function getValidStart()
    {
        return $this->validStart;
    }

    /**
     * Set validEnd
     *
     * @param \DateTime $validEnd
     * @return ContentSpec
     */
    public function setValidEnd($validEnd)
    {
        $this->validEnd = $validEnd;

        return $this;
    }

    /**
     * Get validEnd
     *
     * @return \DateTime 
     */
    public function getValidEnd()
    {
        return $this->validEnd;
    }

    /**
     * Set inserted
     *
     * @param \DateTime $inserted
     * @return ContentSpec
     */
    public function setInserted($inserted)
    {
        $this->inserted = $inserted;

        return $this;
    }

    /**
     * Get inserted
     *
     * @return \DateTime 
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return ContentSpec
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     * @return ContentSpec
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Add translations
     *
     * @param object $translations
     * @return ContentSpec
     */
    public function addTranslation($translations)
    {
        $this->translations[] = $translations;

        return $this;
    }

    /**
     * Remove translations
     *
     * @param object $translations
     */
    public function removeTranslation($translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations()
    {
        return $this->translations;
    }
	
    /**
     * Add contents
     *
     * @param Content $contents
     * @return ContentSpec
     */
    public function addContent(Content $contents)
    {
        $this->contents[] = $contents;

        return $this;
    }

    /**
     * Remove contents
     *
     * @param Content $contents
     */
    public function removeContent(Content $contents)
    {
        $this->contents->removeElement($contents);
    }

    /**
     * Get contents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContents()
    {
        return $this->contents;
    }
	
	
	public function __toString() {
		return $this->getTitle();
	}
	
	public function getSummary($limit = 200) {
		$text = $this->getIntro() ? $this->getIntro() : str_replace("&rsquo;", "'", $this->getContentText());
		if (mb_strlen($text) > $limit)
			$text = mb_substr($text, 0, $limit).'...';
		return $text;
	}
	
	public function getInContent($key) {
		$content = $this->getContent();
		return isset($content[$key]) ? $content[$key] : null;
	}
	
	/**
	 * 
	 * @return Content
	 */
	public function getParent() {
		return $this->getContentHandler()->getContents()->get(0);
	}

	public function getTheme() {
		return $this->getParent()->getTheme();
	}

	public function getVeryParent() {
		return $this->getParent()->getVeryParent();
	}
	
}