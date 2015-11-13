<?php

namespace NyroDev\NyroCmsBundle\Model;

abstract class ContentSpecTranslation {
	
    protected $id;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var ContentSpecSpec
     */
    protected $object;

    /**
     * @var string
     */
    protected $content;
	
	
    /**
     * Convenient constructor
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
	
	
	public function getId()
    {
        return $this->id;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return static
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set field
     *
     * @param string $field
     *
     * @return static
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string $field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set object related
     *
     * @param ContentSpecSpec $object
     *
     * @return static
     */
    public function setObject(ContentSpec $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get related object
     *
     * @return ContentSpec
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return static
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}