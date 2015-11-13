<?php

namespace NyroDev\NyroCmsBundle\Model;

abstract class ContentSpecLog {
	
    protected $id;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var \DateTime
     */
    protected $loggedAt;

    /**
     * @var string
     */
    protected $objectClass;

    /**
     * @var string
     */
    protected $objectId;

    /**
     * @var integer
     */
    protected $version;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $locale;
	
	
	public function getId()
    {
        return $this->id;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action
     *
     * @param string $action
	 * @return ContentSpecLog
     */
    public function setAction($action)
    {
        $this->action = $action;
		return $this;
    }

    /**
     * Get object class
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Set object class
     *
     * @param string $objectClass
	 * @return ContentSpecLog
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
		return $this;
    }

    /**
     * Get object id
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set object id
     *
     * @param string $objectId
	 * @return ContentSpecLog
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
		return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
	 * @return ContentSpecLog
     */
    public function setUsername($username)
    {
        $this->username = $username;
		return $this;
    }

    /**
     * Get loggedAt
     *
     * @return \DateTime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * Set loggedAt to "now"
	 * @return ContentSpecLog
     */
    public function setLoggedAt()
    {
        $this->loggedAt = new \DateTime();
		return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array $data
	 * @return ContentSpecLog
     */
    public function setData($data)
    {
        $this->data = $data;
		return $this;
    }

    /**
     * Get current version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set current version
     *
     * @param integer $version
	 * @return ContentSpecLog
     */
    public function setVersion($version)
    {
        $this->version = $version;
		return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     *
     * @param string $locale
	 * @return ContentSpecLog
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
		return $this;
    }
	
}