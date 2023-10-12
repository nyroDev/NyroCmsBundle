<?php

namespace App\Entity\Translation;

use App\Entity\ContentSpec;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'content_spec_translation')]
#[ORM\UniqueConstraint(name: 'content_spec_translation_unique_idx', columns: ['locale', 'object_id', 'field'])]
class ContentSpecTranslation extends AbstractPersonalTranslation
{
    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    #[ORM\ManyToOne(targetEntity: ContentSpec::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;
}
