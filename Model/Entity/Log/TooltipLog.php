<?php

namespace App\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use NyroDev\NyroCmsBundle\Model\TooltipLog as TooltipLogModel;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
#[ORM\Table(name: 'tooltip_log')]
class TooltipLog extends TooltipLogModel
{
}
