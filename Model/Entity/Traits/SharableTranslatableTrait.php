<?php

namespace NyroDev\NyroCmsBundle\Model\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SharableTranslatableTrait
{

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=250, nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $metaKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="og_title", type="string", length=250, nullable=true)
     * @Gedmo\Translatable
     */
    protected $ogTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="og_description", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $ogDescription;
}
