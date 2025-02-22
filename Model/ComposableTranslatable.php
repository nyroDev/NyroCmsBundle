<?php

namespace NyroDev\NyroCmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface ComposableTranslatable extends Composable
{
    public function setTranslatableLocale(?string $locale): self;

    public function getTranslatableLocale(): ?string;

    public function getTranslations(): Collection;
}
