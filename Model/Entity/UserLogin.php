<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\UserLogin as UserLoginModel;

/**
 * UserLogin.
 *
 * @ORM\Table(name="user_login")
 * @ORM\Entity()
 */
class UserLogin extends UserLoginModel
{
}
