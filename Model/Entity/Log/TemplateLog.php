<?php

namespace App\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use NyroDev\NyroCmsBundle\Model\TemplateLog as TemplateLogModel;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
#[ORM\Table(name: 'template_log')]
class TemplateLog extends TemplateLogModel
{
}
