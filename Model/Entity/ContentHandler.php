<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\ContentHandler as ContentHandlerModel;
use NyroDev\NyroCmsBundle\Repository\Orm\ContentHandlerRepository;

#[ORM\Entity(repositoryClass: ContentHandlerRepository::class)]
#[ORM\Table(name: 'content_handler')]
class ContentHandler extends ContentHandlerModel
{
    #[ORM\OneToMany(targetEntity: Content::class, mappedBy: 'contentHandler')]
    protected Collection $contents;

    #[ORM\OneToMany(targetEntity: ContentHandlerConfig::class, mappedBy: 'contentHandler')]
    protected Collection $contentHandlerConfigs;
}
