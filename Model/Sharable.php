<?php

namespace NyroDev\NyroCmsBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Sharable
{

    public function getMetaTitle();

    public function setMetaTitle($metaTitle);

    public function getMetaDescription();

    public function setMetaDescription($metaDescription);

    public function getMetaKeywords();

    public function setMetaKeywords($metaKeywords);

    public function getOgTitle();

    public function setOgTitle($ogTitle);

    public function getOgDescription();

    public function setOgDescription($ogDescription);

    public function getOgImageFile();

    public function setOgImageFile($ogImageFile);

	public function setOgImage(UploadedFile $ogImage = null);

	public function getOgImage();
}
