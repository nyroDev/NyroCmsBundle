<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\User as UserModel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\UserRepository")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\UserLog")
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 */
class User extends UserModel
{
    /**
     * @ORM\ManyToMany(targetEntity="UserRole", cascade={"persist"})
     * @ORM\JoinTable(name="user_user_role")
     */
    protected $userRoles;
}
