<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use NyroDev\NyroCmsBundle\Model\Translation as TranslationModel;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="translation")
 * @ORM\Entity()
 */
class Translation extends TranslationModel {
	
}