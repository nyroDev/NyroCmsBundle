<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\UserRole as UserRoleModel;

/**
 * UserRole.
 *
 * @ORM\Table(name="user_role")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\UserRoleRepository")
 */
class UserRole extends UserRoleModel
{
    /**
     * @ORM\ManyToMany(targetEntity="Content", cascade={"persist"})
     * @ORM\JoinTable(name="user_role_content")
     */
    protected $contents;
}
