<?php

namespace App\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use NyroDev\NyroCmsBundle\Model\ContentSpecLog as ContentSpecLogModel;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
#[ORM\Table(name: 'content_spec_log')]
class ContentSpecLog extends ContentSpecLogModel
{
}
