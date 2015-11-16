<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 */
abstract class User implements UserInterface, \Symfony\Component\Security\Core\User\EquatableInterface, \Serializable {
	
    protected $id;
	
	/**
     * @var string
     *
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 * @Gedmo\Versioned
     */
    protected $email;

	/**
     * @var string
     *
	 * @Assert\NotBlank()
	 * @Gedmo\Versioned
     */
    protected $firstname;

	/**
     * @var string
     *
	 * @Assert\NotBlank()
	 * @Gedmo\Versioned
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $password = 'dummy';

    /**
     * @var string
     */
    protected $salt = 'dummy';

    /**
     * @var string
     *
	 * @Assert\NotBlank()
	 * @Gedmo\Versioned
     */
    protected $userType;

    /**
     * @var boolean
     *
	 * @Gedmo\Versioned
     */
	protected $developper = false;

    /**
     * @var boolean
     *
	 * @Gedmo\Versioned
     */
	protected $valid = true;

    /**
	 * @Gedmo\Versioned
     */
    protected $validStart;

    /**
	 * @Gedmo\Versioned
     */
    protected $validEnd;

    /**
     * @var string
     *
	 * @Gedmo\Versioned
     */
    protected $passwordKey;

    /**
	 * @Gedmo\Versioned
     */
    protected $passwordKeyEnd;

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
	protected $userRoles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userRoles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
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
     * Set firstname
     *
     * @param string $firstname
     * @return User
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
     * Set lastname
     *
     * @param string $lastname
     * @return User
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
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set userType
     *
     * @param string $userType
     * @return User
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * Get userType
     *
     * @return string 
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * Set developper
     *
     * @param boolean $developper
     * @return User
     */
    public function setDevelopper($developper)
    {
        $this->developper = $developper;

        return $this;
    }

    /**
     * Get developper
     *
     * @return boolean 
     */
    public function getDevelopper()
    {
        return $this->developper;
    }

    /**
     * Set valid
     *
     * @param boolean $valid
     * @return User
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return boolean 
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set validStart
     *
     * @param \DateTime $validStart
     * @return User
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
     * @return User
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
     * Set passwordKey
     *
     * @param string $passwordKey
     * @return User
     */
    public function setPasswordKey($passwordKey)
    {
        $this->passwordKey = $passwordKey;

        return $this;
    }

    /**
     * Get passwordKey
     *
     * @return string 
     */
    public function getPasswordKey()
    {
        return $this->passwordKey;
    }

    /**
     * Set passwordKeyEnd
     *
     * @param \DateTime $passwordKeyEnd
     * @return User
     */
    public function setPasswordKeyEnd($passwordKeyEnd)
    {
        $this->passwordKeyEnd = $passwordKeyEnd;

        return $this;
    }

    /**
     * Get passwordKeyEnd
     *
     * @return \DateTime 
     */
    public function getPasswordKeyEnd()
    {
        return $this->passwordKeyEnd;
    }

    /**
     * Set inserted
     *
     * @param \DateTime $inserted
     * @return User
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
     * @return User
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
     * @return User
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
     * Add userRoles
     *
     * @param UserRole $userRoles
     * @return User
     */
    public function addUserRole(UserRole $userRoles)
    {
        $this->userRoles[] = $userRoles;

        return $this;
    }

    /**
     * Remove userRoles
     *
     * @param UserRole $userRoles
     */
    public function removeUserRole(UserRole $userRoles)
    {
        $this->userRoles->removeElement($userRoles);
    }

    /**
     * Get userRoles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }
	
	
	
	protected $serializeVars = array(
		'id',
		'email',
		'password',
		'salt',
		'userType'
	);
	
	public function eraseCredentials() {}

	public function isEqualTo(UserInterface $user) {
        if (!$user instanceof User ||
				$this->getPassword() !== $user->getPassword() ||
				$this->getSalt() !== $user->getSalt() ||
				$this->getUsername() !== $user->getUsername() ||
				$this->getId() !== $user->getId())
            return false;
        return true;
	}

	public function serialize() {
		$vars = array();
		foreach($this->serializeVars as $field)
			$vars[$field] = $this->$field;
        return serialize($vars);
	}

	public function unserialize($serialized) {
		$vars = unserialize($serialized);
		foreach($vars as $k=>$v)
			$this->$k = $v;
	}

	public function getRoles() {
		$ret = array(
			'ROLE_USER',
			'ROLE_'.strtoupper($this->getUserType())
		);
		if ($this->getValid()) {
			foreach($this->getUserRoles() as $role) {
				$ret[] = $role->getRoleName();
			}
		}
		return $ret;
	}
	
	public function hasRole($role) {
		return in_array($role, $this->getRoles());
	}

	public function getUsername() {
		return $this->getEmail();
	}
	
	public function __toString() {
		return $this->getFirstname().' '.$this->getLastname().' ('.$this->getEmail().')';
	}

}