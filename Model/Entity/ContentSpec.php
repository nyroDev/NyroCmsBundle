<?php

namespace App\Entity;

use App\Entity\Log\ContentSpecLog;
use App\Entity\Translation\ContentSpecTranslation;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\ContentSpec as ContentSpecModel;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTrait;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTranslatableTrait;
use NyroDev\NyroCmsBundle\Repository\Orm\ContentSpecRepository;

#[ORM\Table(name: 'content_spec')]
#[ORM\Entity(repositoryClass: ContentSpecRepository::class)]
#[Gedmo\TranslationEntity(class: ContentSpecTranslation::class)]
#[Gedmo\Loggable(logEntryClass: ContentSpecLog::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
#[ORM\HasLifecycleCallbacks]
class ContentSpec extends ContentSpecModel
{
    use SharableTrait { getFileFields as protected sharableGetFileFields; }
    use SharableTranslatableTrait;

    #[ORM\ManyToMany(targetEntity: Content::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinTable(name: 'content_spec_content')]
    protected Collection $contents;

    #[ORM\OneToMany(
        targetEntity: ContentSpecTranslation::class,
        mappedBy: 'object',
        cascade: ['persist', 'remove']
    )]
    protected Collection $translations;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function preUpload()
    {
        parent::preUpload();
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload()
    {
        parent::upload();
    }

    #[ORM\PostRemove]
    public function removeUpload()
    {
        parent::removeUpload();
    }
}
