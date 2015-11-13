<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\ContentHandler as ContentHandlerModel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table(name="content_handler")
 * @ORM\Entity()
 */
class ContentHandler extends ContentHandlerModel {
	
}