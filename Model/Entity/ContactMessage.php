<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\ContactMessage as ContactMessageModel;

/**
 * ContactMessage.
 *
 * @ORM\Table(name="contact_message")
 * @ORM\Entity()
 */
class ContactMessage extends ContactMessageModel
{
}
