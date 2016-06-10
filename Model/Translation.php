<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translation.
 */
abstract class Translation
{
    protected $id;

    /**
     * @var string
     
     * @Assert\NotBlank()
     */
    protected $domain;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $locale;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $ident;

    /**
     * @var string
     */
    protected $translation;

    /**
     * @var bool
     */
    protected $html = false;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected $inserted;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set domain.
     *
     * @param string $domain
     *
     * @return Translation
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set locale.
     *
     * @param string $locale
     *
     * @return Translation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set ident.
     *
     * @param string $ident
     *
     * @return Translation
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Get ident.
     *
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * Set translation.
     *
     * @param string $translation
     *
     * @return Translation
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get translation.
     *
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set html.
     *
     * @param bool $html
     *
     * @return Translation
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html.
     *
     * @return bool
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set inserted.
     *
     * @param \DateTime $inserted
     *
     * @return Translation
     */
    public function setInserted($inserted)
    {
        $this->inserted = $inserted;

        return $this;
    }

    /**
     * Get inserted.
     *
     * @return \DateTime
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Translation
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
