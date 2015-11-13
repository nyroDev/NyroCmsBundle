<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\ContentSpec as ContentSpecModel;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Content
 *
 * @ORM\Table(name="content_spec")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentSpecRepository")
 * @Gedmo\TranslationEntity(class="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentSpecTranslation")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\ContentSpecLog")
 */
class ContentSpec extends ContentSpecModel {
	
	/**
	 * @ORM\ManyToMany(targetEntity="Content", cascade={"persist"})
	 * @ORM\JoinTable(name="content_spec_content")
	 */
	protected $contents;
	
}