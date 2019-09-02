<?php

namespace NyroDev\NyroCmsBundle\Handler;

class ContactWrapped extends Contact
{
    public function isWrappedAs()
    {
        return 'text2';
    }

    public function isWrapped()
    {
        return 'column2';
    }
}
