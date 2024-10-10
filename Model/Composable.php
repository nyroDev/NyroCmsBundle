<?php

namespace NyroDev\NyroCmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface Composable extends ContentRootable
{
    public function getId();

    public function getTitle(): ?string;

    public function setTranslatableLocale(?string $locale): static;

    public function getTranslatableLocale(): ?string;

    public function getTranslations(): Collection;

    public function getContent(): ?array;

    public function setContent(?array $content);

    public function getContentText(): ?string;

    public function setContentText(?string $contentText): static;

    public function getFirstImage(): ?string;

    public function setFirstImage(?string $firstImage): static;

    public function getParent(): mixed;

    public function getTheme(): ?string;

    public function __toString(): string;
}
