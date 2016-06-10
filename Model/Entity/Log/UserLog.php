<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Log;

use NyroDev\NyroCmsBundle\Model\UserLog as UserLogModel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User.
 *
 * @ORM\Table(name="user_log")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 */
class UserLog extends UserLogModel
{
}
