<?php

namespace NyroDev\NyroCmsBundle\Model;

interface Composable extends ContentRootable
{
    public function getId();

    public function getTitle();

    public function setTranslatableLocale($locale);

    public function getTranslatableLocale();

    public function getTranslations();

    public function getContent();

    public function setContent(array $content);

    public function getContentText();

    public function setContentText($contentText);

    public function getFirstImage();

    public function setFirstImage($firstImage);

    public function getParent();

    public function getTheme();

    public function __toString();
}
