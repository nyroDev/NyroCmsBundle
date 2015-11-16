<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\ContentHandler as ContentHandlerModel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table(name="content_handler")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentHandlerRepository")
 */
class ContentHandler extends ContentHandlerModel {
	
    /**
     * @ORM\OneToMany(targetEntity="Content", mappedBy="contentHandler")
     */
    protected $contents;

}