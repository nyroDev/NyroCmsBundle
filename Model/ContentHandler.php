<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ContentHandler
{
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $class;

    /**
     * @var bool
     */
    protected $hasAdmin;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $contents;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $contentHandlerConfigs;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->contents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contentHandlerConfigs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ContentHandler
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set class.
     *
     * @param string $class
     *
     * @return ContentHandler
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set hasAdmin.
     *
     * @param bool $hasAdmin
     *
     * @return ContentHandler
     */
    public function setHasAdmin($hasAdmin)
    {
        $this->hasAdmin = $hasAdmin;

        return $this;
    }

    /**
     * Get hasAdmin.
     *
     * @return bool
     */
    public function getHasAdmin()
    {
        return $this->hasAdmin;
    }

    /**
     * Add contents.
     *
     * @return Content
     */
    public function addContent(Content $contents)
    {
        $this->contents[] = $contents;

        return $this;
    }

    /**
     * Remove contents.
     */
    public function removeContent(Content $contents)
    {
        $this->contents->removeElement($contents);
    }

    /**
     * Set contents.
     *
     * @return ContentHandler
     */
    public function setContents(\Doctrine\Common\Collections\Collection $contents)
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * Get contents.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Add contentHandlerConfigs.
     *
     * @return ContentHandlerConfig
     */
    public function addContentHandlerConfig(ContentHandlerConfig $contentHandlerConfigs)
    {
        $this->contentHandlerConfigs[] = $contentHandlerConfigs;

        return $this;
    }

    /**
     * Remove contentHandlerConfigs.
     */
    public function removeContentHandlerConfig(ContentHandlerConfig $contentHandlerConfigs)
    {
        $this->contentHandlerConfigs->removeElement($contentHandlerConfigs);

        return $this;
    }

    /**
     * Set contentHandlerConfigs.
     *
     * @return ContentHandlerConfigHandler
     */
    public function setContentHandlerConfigs(\Doctrine\Common\Collections\Collection $contentHandlerConfigs)
    {
        $this->contentHandlerConfigs = $contentHandlerConfigs;

        return $this;
    }

    /**
     * Get contentHandlerConfigs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContentHandlerConfigs()
    {
        return $this->contentHandlerConfigs;
    }

    /**
     * Get contentHandlerConfigs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContentHandlerConfigsByIdent()
    {
        $ret = [];
        foreach ($this->getContentHandlerConfigs() as $cfg) {
            $ret[$cfg->getconfigIdent()] = $cfg;
        }

        return $ret;
    }

    /**
     * Set inserted.
     *
     * @param \DateTime $inserted
     *
     * @return ContentHandler
     */
    public function setInserted($inserted)
    {
        $this->inserted = $inserted;

        return $this;
    }

    /**
     * Get inserted.
     *
     * @return \DateTime
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return ContentHandler
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
