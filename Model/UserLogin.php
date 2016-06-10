<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;

abstract class UserLogin
{
    protected $id;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string
     */
    protected $place;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected $inserted;

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return UserLogin
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set ipAddress.
     *
     * @param string $ipAddress
     *
     * @return UserLogin
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress.
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set place.
     *
     * @param string $place
     *
     * @return UserLogin
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place.
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set inserted.
     *
     * @param \DateTime $inserted
     *
     * @return UserLogin
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
}
