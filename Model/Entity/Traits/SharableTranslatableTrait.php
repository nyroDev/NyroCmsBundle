<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SharableTranslatableTrait
{
    #[ORM\Column(length: 250, nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $metaTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $metaDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $metaKeywords = null;

    #[ORM\Column(length: 250, nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $ogTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $ogDescription = null;
}
