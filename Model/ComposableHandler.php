<?php

namespace NyroDev\NyroCmsBundle\Model;

interface ComposableHandler
{
    public function getContentHandler(): ?ContentHandler;
}
