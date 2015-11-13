<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\Content as ContentModel;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Content
 *
 * @ORM\Table(name="content")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentRepository")
 * @Gedmo\TranslationEntity(class="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentTranslation")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\ContentLog")
 */
class Content extends ContentModel {
	
}