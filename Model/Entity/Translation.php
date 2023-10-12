<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use NyroDev\NyroCmsBundle\Model\Translation as TranslationModel;

#[ORM\Entity]
#[ORM\Table(name: 'translation')]
class Translation extends TranslationModel
{
}
