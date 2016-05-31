<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\ContactMessage as ContactMessageModel;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactMessage
 *
 * @ORM\Table(name="contact_message")
 * @ORM\Entity()
 */
class ContactMessage extends ContactMessageModel {

}