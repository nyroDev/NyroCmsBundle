<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm\Traits;

trait TransatableHintTrait
{
    public function setHint($query)
    {
        if (defined('NYRO_LOCALE')) {
            $q->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, NYRO_LOCALE);
            $q->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_FALLBACK,
                1 // fallback to default values in case if record is not translated
            );
        }

        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
    }
}
