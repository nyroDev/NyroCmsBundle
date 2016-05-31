<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;

abstract class ContactMessage {
	
    protected $id;

    /**
     * @var ContentHandler
     */
    protected $contentHandler;
	
    /**
     * @var string
     */
    protected $to;
	
    /**
     * @var string
     */
    protected $lastname;
	
    /**
     * @var string
     */
    protected $firstname;
	
    /**
     * @var string
     */
    protected $company;
	
    /**
     * @var string
     */
    protected $phone;
	
    /**
     * @var string
     */
    protected $email;
	
    /**
     * @var string
     */
    protected $message;
	
    /**
     * @var \DateTime
	 * @Gedmo\Timestampable(on="create")
     */
    protected $inserted;

	 public function getId()
    {
        return $this->id;
    }

    /**
     * Set contentHandler
     *
     * @param Content $contentHandler
     * @return ContactMessage
     */
    public function setContentHandler(ContentHandler $contentHandler)
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
     * Set to
     *
     * @param string $to
     * @return ContactMessage
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return string 
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return ContactMessage
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return ContactMessage
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return ContactMessage
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return ContactMessage
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return ContactMessage
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return ContactMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set inserted
     *
     * @param \DateTime $inserted
     * @return ContactMessage
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
	
}