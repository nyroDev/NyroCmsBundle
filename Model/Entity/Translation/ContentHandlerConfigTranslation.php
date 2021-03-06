<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="content_handler_config_translation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="content_handler_config_translation_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class ContentHandlerConfigTranslation extends AbstractPersonalTranslation
{
    /**
     * Convenient constructor.
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    /**
     * @ORM\ManyToOne(targetEntity="NyroDev\NyroCmsBundle\Model\Entity\ContentHandlerConfig", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
