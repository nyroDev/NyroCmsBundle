<?php

namespace NyroDev\NyroCmsBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\Translation as TranslationModel;

/**
 * User.
 *
 * @ORM\Table(name="translation")
 * @ORM\Entity()
 */
class Translation extends TranslationModel
{
}
