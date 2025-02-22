<?php

namespace App\Entity;

use App\Entity\Log\TemplateLog;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\Template as TemplateModel;
use NyroDev\NyroCmsBundle\Repository\Orm\TemplateRepository;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Table(name: 'template')]
#[Gedmo\Loggable(logEntryClass: TemplateLog::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
class Template extends TemplateModel
{
}
