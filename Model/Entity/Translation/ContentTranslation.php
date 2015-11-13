<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Log;

use NyroDev\NyroCmsBundle\Model\ContentTranslation as ContentTranslationModel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table(name="content_translation")
 * @ORM\Entity()
 */
class ContentTranslation extends ContentTranslationModel {
	
}