<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Log;

use NyroDev\NyroCmsBundle\Model\ContentHandlerConfigLog as ContentHandlerConfigLogModel;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Content.
 *
 * @ORM\Table(name="content_handler_config_log")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 */
class ContentHandlerConfigLog extends ContentHandlerConfigLogModel
{
}
