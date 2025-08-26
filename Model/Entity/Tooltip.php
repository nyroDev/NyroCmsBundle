<?php

namespace App\Entity;

use App\Entity\Log\TooltipLog;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\Tooltip as TooltipModel;
use NyroDev\NyroCmsBundle\Repository\Orm\TooltipRepository;

#[ORM\Entity(repositoryClass: TooltipRepository::class)]
#[ORM\Table(name: 'tooltip')]
#[Gedmo\Loggable(logEntryClass: TooltipLog::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
class Tooltip extends TooltipModel
{
}
