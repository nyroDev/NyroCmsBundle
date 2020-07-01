<?php

namespace NyroDev\NyroCmsBundle\Model;

use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ContentHandlerConfig extends AbstractUploadable
{
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_NUMBER = 'number';
    const TYPE_BOOL = 'number';

    protected $id;

    /**
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $configIdent;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $configType;

    /**
     * @var string
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     */
    private $valueText;

    /**
     * @var \DateTime
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     */
    private $valueDate;

    /**
     * @var float
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     */
    private $valueNumber;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     */
    protected $inserted;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $translations;

    /**
     * @var string
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getTranslatableLocale()
    {
        return $this->locale;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contentHandler.
     *
     * @param ContentHandler $contentHandler
     *
     * @return ContentHandlerConfig
     */
    public function setContentHandler($contentHandler)
    {
        $this->contentHandler = $contentHandler;

        return $this;
    }

    /**
     * Get contentHandler.
     *
     * @return ContentHandler
     */
    public function getContentHandler()
    {
        return $this->contentHandler;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ContentHandlerConfig
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set configIdent.
     *
     * @param string $configIdent
     *
     * @return ContentHandlerConfig
     */
    public function setConfigIdent($configIdent)
    {
        $this->configIdent = $configIdent;

        return $this;
    }

    /**
     * Get configIdent.
     *
     * @return string
     */
    public function getConfigIdent()
    {
        return $this->configIdent;
    }

    /**
     * Set configType.
     *
     * @param string $configType
     *
     * @return ContentHandlerConfig
     */
    public function setConfigType($configType)
    {
        $this->configType = $configType;

        return $this;
    }

    /**
     * Get configType.
     *
     * @return string
     */
    public function getConfigType()
    {
        return $this->configType;
    }

    /**
     * Set valueText.
     *
     * @param string $valueText
     *
     * @return ContentHandlerConfig
     */
    public function setValueText($valueText)
    {
        $this->valueText = $valueText;

        return $this;
    }

    /**
     * Get valueText.
     *
     * @return string
     */
    public function getValueText()
    {
        return $this->valueText;
    }

    /**
     * Set valueDate.
     *
     * @param \DateTime $valueDate
     *
     * @return ContentHandlerConfig
     */
    public function setValueDate($valueDate)
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    /**
     * Get valueDate.
     *
     * @return \DateTime
     */
    public function getValueDate()
    {
        return $this->valueDate;
    }

    /**
     * Set valueNumber.
     *
     * @param int $valueNumber
     *
     * @return ContentHandlerConfig
     */
    public function setValueNumber($valueNumber)
    {
        $this->valueNumber = $valueNumber;

        return $this;
    }

    /**
     * Get valueNumber.
     *
     * @return int
     */
    public function getValueNumber()
    {
        return $this->valueNumber;
    }

    /**
     * Set inserted.
     *
     * @param \DateTime $inserted
     *
     * @return ContentHandlerConfig
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
     * @return ContentHandlerConfig
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

    /**
     * Add translations.
     *
     * @param object $translation
     *
     * @return ContentHandlerConfig
     */
    public function addTranslation($translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }

        return $this;
    }

    /**
     * Remove translations.
     *
     * @param object $translations
     *
     * @return ContentHandlerConfig
     */
    public function removeTranslation($translations)
    {
        $this->translations->removeElement($translations);

        return $this;
    }

    /**
     * Get translations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getField()
    {
        $field = 'valueText';
        switch ($this->getConfigType()) {
            case self::TYPE_NUMBER:
            case self::TYPE_BOOL:
                $field = 'valueNumber';
                break;
            case self::TYPE_DATE:
            case self::TYPE_DATETIME:
                $field = 'valueDate';
                break;
        }

        return $field;
    }

    public function getValue()
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $field = $this->getField();
        $value = $accessor->getValue($this, $field);

        switch ($this->getConfigType()) {
            case self::TYPE_BOOL:
                $value = (bool) $value;
                break;
        }

        return $value;
    }

    public function setValue($value)
    {
        $field = $this->getField();

        switch ($this->getConfigType()) {
            case self::TYPE_BOOL:
                $value = $value ? 1 : 0;
                break;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($this, $field, $value);

        return $this;
    }

    public function getTranslationsByLocale()
    {
        $ret = [];
        foreach ($this->getTranslations() as $tr) {
            $ret[$tr->getLocale()] = $tr;
        }

        return $ret;
    }

    protected function getFileFields()
    {
        return [];
    }
}
