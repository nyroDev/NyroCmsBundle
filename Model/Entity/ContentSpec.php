<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\ContentSpec as ContentSpecModel;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTranslatableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Content.
 *
 * @ORM\Table(name="content_spec")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentSpecRepository")
 * @Gedmo\TranslationEntity(class="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentSpecTranslation")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\ContentSpecLog")
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @ORM\HasLifecycleCallbacks
 */
class ContentSpec extends ContentSpecModel
{
    use SharableTranslatableTrait { getFileFields as protected sharableGetFileFields; }

    /**
     * @ORM\ManyToMany(targetEntity="Content", cascade={"remove", "persist"})
     * @ORM\JoinTable(name="content_spec_content")
     */
    protected $contents;

    /**
     * @ORM\OneToMany(
     *   targetEntity="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentSpecTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        parent::preUpload();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        parent::upload();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        parent::removeUpload();
    }
}
