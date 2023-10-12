<?php

namespace App\Entity;

use App\Entity\Log\ContentHandlerConfigLog;
use App\Entity\Translation\ContentHandlerConfigTranslation;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\ContentHandlerConfig as ContentHandlerConfigModel;
use NyroDev\NyroCmsBundle\Repository\Orm\ContentHandlerConfigRepository;

#[ORM\Table(name: 'content_handler_config')]
#[ORM\Entity(repositoryClass: ContentHandlerConfigRepository::class)]
#[Gedmo\TranslationEntity(class: ContentHandlerConfigTranslation::class)]
#[Gedmo\Loggable(logEntryClass: ContentHandlerConfigLog::class)]
class ContentHandlerConfig extends ContentHandlerConfigModel
{
    #[ORM\OneToMany(
        targetEntity: ContentHandlerConfigTranslation::class,
        mappedBy: 'object',
        cascade: ['persist', 'remove']
    )]
    protected Collection $translations;
}
