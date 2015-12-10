<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ContentHandler {
	
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
     * @var boolean
     */
    protected $hasAdmin;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $contents;

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
     * Constructor
     */
    public function __construct()
    {
        $this->contents = new \Doctrine\Common\Collections\ArrayCollection();
    }
	

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ContentHandler
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return ContentHandler
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set hasAdmin
     *
     * @param boolean $hasAdmin
     * @return ContentHandler
     */
    public function setHasAdmin($hasAdmin)
    {
        $this->hasAdmin = $hasAdmin;

        return $this;
    }

    /**
     * Get hasAdmin
     *
     * @return boolean 
     */
    public function getHasAdmin()
    {
        return $this->hasAdmin;
    }

    /**
     * Add contents
     *
     * @param Content $contents
     * @return Content
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
     * Set contents
     *
     * @param \Doctrine\Common\Collections\Collection $contents
     * @return ContentHandler
     */
    public function setContents(\Doctrine\Common\Collections\Collection $contents) {
        $this->contents = $contents;

        return $this;
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

    /**
     * Set inserted
     *
     * @param \DateTime $inserted
     * @return ContentHandler
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
     * @return ContentHandler
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

	
	public function __toString() {
		return $this->getName();
	}

}