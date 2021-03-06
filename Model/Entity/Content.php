<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\Content as ContentModel;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTrait;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTranslatableTrait;

/**
 * Content.
 *
 * @ORM\Table(name="content")
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="NyroDev\NyroCmsBundle\Repository\Orm\ContentRepository")
 * @Gedmo\TranslationEntity(class="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentTranslation")
 * @Gedmo\Loggable(logEntryClass="NyroDev\NyroCmsBundle\Model\Entity\Log\ContentLog")
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @ORM\HasLifecycleCallbacks
 */
class Content extends ContentModel
{
    use SharableTrait { getFileFields as protected sharableGetFileFields; }
    use SharableTranslatableTrait;

    /**
     * @ORM\OneToMany(targetEntity="Content", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\ManyToMany(targetEntity="Content", cascade={"persist"})
     * @ORM\JoinTable(name="content_related")
     */
    protected $relateds;

    /**
     * @ORM\OneToMany(
     *   targetEntity="NyroDev\NyroCmsBundle\Model\Entity\Translation\ContentTranslation",
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
