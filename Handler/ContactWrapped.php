<?php

namespace NyroDev\NyroCmsBundle\Handler;

class ContactWrapped extends Contact
{
    public function isWrappedAs(): string
    {
        return 'text2';
    }

    public function isWrapped(): string
    {
        return 'column2';
    }
}
