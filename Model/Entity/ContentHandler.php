<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\ContentHandler as ContentHandlerModel;

/**
 * Content.
 *
 * @ORM\Table(name="content_handler")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentHandlerRepository")
 */
class ContentHandler extends ContentHandlerModel
{
    /**
     * @ORM\OneToMany(targetEntity="Content", mappedBy="contentHandler")
     */
    protected $contents;

    /**
     * @ORM\OneToMany(targetEntity="ContentHandlerConfig", mappedBy="contentHandler")
     */
    protected $contentHandlerConfigs;
}
