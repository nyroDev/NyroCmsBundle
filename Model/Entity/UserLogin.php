<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\UserLogin as UserLoginModel;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * UserLogin
 *
 * @ORM\Table(name="user_login")
 * @ORM\Entity()
 */
class UserLogin extends UserLoginModel {
	
}