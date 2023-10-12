<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\UserRole as UserRoleModel;
use NyroDev\NyroCmsBundle\Repository\Orm\UserRoleRepository;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ORM\Table(name: 'user_role')]
class UserRole extends UserRoleModel
{
    #[ORM\ManyToMany(targetEntity: Content::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'user_role_content')]
    protected Collection $contents;
}
