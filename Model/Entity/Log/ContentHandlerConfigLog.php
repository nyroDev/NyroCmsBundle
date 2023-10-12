<?php

namespace App\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use NyroDev\NyroCmsBundle\Model\ContentHandlerConfigLog as ContentHandlerConfigLogModel;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
#[ORM\Table(name: 'content_handler_config_log')]
class ContentHandlerConfigLog extends ContentHandlerConfigLogModel
{
}
