<?php

namespace NyroDev\NyroCmsBundle\Model;

interface Composable
{
    public function getId();

    public function getTitle(): ?string;

    public function getContent(): ?array;

    public function setContent(?array $content);

    public function getParent(): mixed;

    public function getTheme(): ?string;

    public function __toString(): string;
}
