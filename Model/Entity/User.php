<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\User as UserModel;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\UserRepository")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\UserLog")
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @ORM\HasLifecycleCallbacks
 */
class User extends UserModel
{
    /**
     * @ORM\ManyToMany(targetEntity="UserRole", cascade={"persist"})
     * @ORM\JoinTable(name="user_user_role")
     */
    protected $userRoles;

    /**
     * @ORM\PreRemove()
     */
    public function preRemove()
    {
        $this->setEmail('_deleted_'.time().$this->getEmail());
        $this->setPasswordKey(null);
    }
}
