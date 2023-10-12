<?php

namespace App\Entity;

use App\Entity\Log\UserLog;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\User as UserModel;
use NyroDev\NyroCmsBundle\Repository\Orm\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[Gedmo\Loggable(logEntryClass: UserLog::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
#[ORM\HasLifecycleCallbacks]
class User extends UserModel
{
    #[ORM\ManyToMany(targetEntity: UserRole::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'user_user_role')]
    protected Collection $userRoles;

    #[ORM\PreRemove]
    public function preRemove()
    {
        $this->setEmail('_deleted_'.time().$this->getEmail());
        $this->setPasswordKey(null);
    }
}
