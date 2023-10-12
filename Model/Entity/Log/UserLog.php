<?php

namespace App\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use NyroDev\NyroCmsBundle\Model\UserLog as UserLogModel;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
#[ORM\Table(name: 'user_log')]
class UserLog extends UserLogModel
{
}
