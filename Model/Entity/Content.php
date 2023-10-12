<?php

namespace App\Entity;

use App\Entity\Log\ContentLog;
use App\Entity\Translation\ContentTranslation;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\Content as ContentModel;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTrait;
use NyroDev\NyroCmsBundle\Model\Entity\Traits\SharableTranslatableTrait;
use NyroDev\NyroCmsBundle\Repository\Orm\ContentRepository;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\Table(name: 'content')]
#[Gedmo\Tree(type: 'nested')]
#[Gedmo\TranslationEntity(class: ContentTranslation::class)]
#[Gedmo\Loggable(logEntryClass: ContentLog::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
#[ORM\HasLifecycleCallbacks]
class Content extends ContentModel
{
    use SharableTrait { getFileFields as protected sharableGetFileFields; }
    use SharableTranslatableTrait;

    #[ORM\OneToMany(targetEntity: Content::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected Collection $children;

    #[ORM\ManyToMany(targetEntity: Content::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'content_related')]
    protected Collection $relateds;

    #[ORM\OneToMany(
        targetEntity: ContentTranslation::class,
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
