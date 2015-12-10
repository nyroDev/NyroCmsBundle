<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

abstract class UserRole {
	
	protected $id;

	/**
     * @var string
     *
	 * @Assert\NotBlank()
     */
    protected $name;

	/**
     * @var string
     */
    protected $roleName;

	/**
     * @var boolean
     */
    protected $internal = false;

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
	 * @var \Doctrine\Common\Collections\Collection
	 */
	protected $contents;
	
	
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
     * @return UserRole
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
     * Set roleName
     *
     * @param string $roleName
     * @return UserRole
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * Get roleName
     *
     * @return string 
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * Set internal
     *
     * @param boolean $internal
     * @return UserRole
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * Get internal
     *
     * @return boolean 
     */
    public function getInternal()
    {
        return $this->internal;
    }

    /**
     * Set inserted
     *
     * @param \DateTime $inserted
     * @return UserRole
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
     * @return UserRole
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
     * Add contents
     *
     * @param Content $contents
     * @return UserRole
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
		return $this->getName();
	}
	
	public function getSecurityRoleName() {
		$name = $this->getRoleName() ? $this->getRoleName() : $this->getName();
		return 'ROLE_'.strtoupper(str_replace(' ', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
	}
	
}