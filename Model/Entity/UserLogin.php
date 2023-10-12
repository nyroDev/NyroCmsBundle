<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\UserLogin as UserLoginModel;

#[ORM\Entity]
#[ORM\Table(name: 'user_login')]
class UserLogin extends UserLoginModel
{
}
