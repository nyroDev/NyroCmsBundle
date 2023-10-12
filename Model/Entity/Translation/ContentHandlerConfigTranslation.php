<?php

namespace App\Entity\Translation;

use App\Entity\ContentHandlerConfig;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'content_handler_config_translation')]
#[ORM\UniqueConstraint(name: 'content_handler_config_translation_unique_idx', columns: ['locale', 'object_id', 'field'])]
class ContentHandlerConfigTranslation extends AbstractPersonalTranslation
{
    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    #[ORM\ManyToOne(targetEntity: ContentHandlerConfig::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;
}
