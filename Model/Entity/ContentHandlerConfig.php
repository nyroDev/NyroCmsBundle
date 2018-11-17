<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\ContentHandlerConfig as ContentHandlerConfigModel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Content.
 *
 * @ORM\Table(name="content_handler_config")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentHandlerConfigRepository")
 * @Gedmo\TranslationEntity(class="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentHandlerConfigTranslation")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\ContentHandlerConfigLog")
 */
class ContentHandlerConfig extends ContentHandlerConfigModel
{
    /**
     * @ORM\OneToMany(
     *   targetEntity="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentHandlerConfigTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;
}
