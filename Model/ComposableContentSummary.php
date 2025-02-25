<?php

namespace NyroDev\NyroCmsBundle\Model;

interface ComposableContentSummary
{
    public function getContentText(): ?string;

    public function setContentText(?string $contentText): self;

    public function getFirstImage(): ?string;

    public function setFirstImage(?string $firstImage): self;
}
