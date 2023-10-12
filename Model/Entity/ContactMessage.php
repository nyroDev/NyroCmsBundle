<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\ContactMessage as ContactMessageModel;

#[ORM\Entity]
#[ORM\Table(name: 'contact_message')]
class ContactMessage extends ContactMessageModel
{
}
