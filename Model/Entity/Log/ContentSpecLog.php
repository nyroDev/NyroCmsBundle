<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\ContentSpecLog as ContentSpecLogModel;

/**
 * Content.
 *
 * @ORM\Table(name="content_spec_log")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 * @Gedmo\TranslationEntity(class="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentSpecTranslation")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\ContentSpecLog")
 */
class ContentSpecLog extends ContentSpecLogModel
{
}
