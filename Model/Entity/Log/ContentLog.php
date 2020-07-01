<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\ContentLog as ContentLogModel;

/**
 * Content.
 *
 * @ORM\Table(name="content_log")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 */
class ContentLog extends ContentLogModel
{
}
