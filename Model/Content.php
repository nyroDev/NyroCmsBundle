<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use NyroDev\UtilityBundle\Model\AbstractUploadable;

/**
 * @Gedmo\Tree(type="nested")
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 */
abstract class Content extends AbstractUploadable implements Composable, ComposableHandler {
	
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
    protected $url;

    /**
     * @var string
	 * @Gedmo\Versioned
     */
    protected $theme;

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
     * @var string
	 * @Assert\Url()
	 * @Gedmo\Versioned
     */
    protected $goUrl;

    /**
     * @var boolean
	 * @Gedmo\Versioned
     */
    protected $goBlank;

    /**
     * @var smallint
	 * @Assert\NotBlank()
	 * @Gedmo\Versioned
     */
    protected $state = self::STATE_ACTIVE;

    /**
     * @var string
	 * @Gedmo\Versioned
     */
    protected $handler;

    /**
     * @var string
	 * @Gedmo\Versioned
     */
    protected $host;

    /**
     * @var string
	 * @Gedmo\Versioned
     */
    protected $locales;

    /**
     * @var boolean
	 * @Gedmo\Versioned
     */
    protected $xmlSitemap;

    /**
     * @var ContentHandler
	 * @Gedmo\Versioned
     */
    protected $contentHandler;

    /**
     * @var string
	 * @Gedmo\Versioned
     */
    protected $menuOption;

    /**
     * @var integer
     * @Gedmo\TreeLeft
     */
    protected $lft;

    /**
     * @var integer
     * @Gedmo\TreeRight
     */
    protected $rgt;

    /**
     * @var integer
     * @Gedmo\TreeLevel
     */
    protected $level;

    /**
     * @var integer
     * @Gedmo\TreeRoot
     */
    protected $root;

    /**
     * @var Content
     * @Gedmo\TreeParent
	 * @Gedmo\Versioned
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $children;

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
	protected $relateds;
	
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $translations;

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
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relateds = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Content
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
     * Set url
     *
     * @param string $url
     * @return Content
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set theme
     *
     * @param string $theme
     * @return Content
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string 
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set content
     *
     * @param array $content
     * @return Content
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
     * @return Content
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
     * @return Content
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
     * Set goUrl
     *
     * @param string $goUrl
     * @return Content
     */
    public function setGoUrl($goUrl)
    {
        $this->goUrl = $goUrl;

        return $this;
    }

    /**
     * Get goUrl
     *
     * @return string 
     */
    public function getGoUrl()
    {
        return $this->goUrl;
    }

    /**
     * Set goBlank
     *
     * @param boolean $goBlank
     * @return Content
     */
    public function setGoBlank($goBlank)
    {
        $this->goBlank = $goBlank;

        return $this;
    }

    /**
     * Get goBlank
     *
     * @return boolean 
     */
    public function getGoBlank()
    {
        return $this->goBlank;
    }

    /**
     * Set state
     *
     * @param smallint $state
     * @return Content
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
     * Set handler
     *
     * @param string $handler
     * @return Content
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Get handler
     *
     * @return string 
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return Content
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set locales
     *
     * @param string $locales
     * @return Content
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Get locales
     *
     * @return string 
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Set xmlSitemap
     *
     * @param boolean $xmlSitemap
     * @return Content
     */
    public function setXmlSitemap($xmlSitemap)
    {
        $this->xmlSitemap = $xmlSitemap;

        return $this;
    }

    /**
     * Get xmlSitemap
     *
     * @return boolean 
     */
    public function getXmlSitemap()
    {
        return $this->xmlSitemap;
    }

    /**
     * Set contentHandler
     *
     * @param ContentHandler $contentHandler
     * @return Content
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
     * Set menuOption
     *
     * @param string $menuOption
     * @return Content
     */
    public function setMenuOption($menuOption)
    {
        $this->menuOption = $menuOption;

        return $this;
    }

    /**
     * Get menuOption
     *
     * @return string 
     */
    public function getMenuOption()
    {
        return $this->menuOption;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     * @return Content
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer 
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return Content
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer 
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Content
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set root
     *
     * @param integer $root
     * @return Content
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set inserted
     *
     * @param \DateTime $inserted
     * @return Content
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
     * @return Content
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
     * @return Content
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
     * Set parent
     *
     * @param Content $parent
     * @return Content
     */
    public function setParent(Content $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Content 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param Content $children
     * @return Content
     */
    public function addChild(Content $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Content $children
     */
    public function removeChild(Content $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add relateds
     *
     * @param Content $relateds
     * @return Content
     */
    public function addRelated(Content $relateds)
    {
        $this->relateds[] = $relateds;

        return $this;
    }

    /**
     * Remove relateds
     *
     * @param Content $relateds
     */
    public function removeRelated(Content $relateds)
    {
        $this->relateds->removeElement($relateds);
    }

    /**
     * Get relateds
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelateds()
    {
        return $this->relateds;
    }

    /**
     * Add translations
     *
     * @param object $translations
     * @return Content
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
	
	
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * 
	 * @return Content
	 */
	public function getVeryParent() {
		return $this->getParent() ? $this->getParent()->getVeryParent() : $this;
	}
	
	/**
	 * 
	 * @return Content
	 */
	public function getParentLvl($level) {
		return $this->getLevel() > $level && $this->getParent() ? $this->getParent()->getParentLvl($level) : $this;
	}
	
	public function getParentTheme() {
		if (!$this->getParent())
			return $this->getTheme();
		
		return $this->getTheme() ? $this->getTheme() : $this->getParent()->getParentTheme();
	}

	public function getSummary($limit = 250) {
		$text = str_replace("&rsquo;", "'", $this->getContentText());
		if (mb_strlen($text) > $limit)
			$text = mb_substr ($text, 0, $limit).'...';
		return $text;
	}

	protected function getFileFields() {
		return array();
	}
	
}