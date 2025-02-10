<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm\Traits;

use Doctrine\ORM\Query;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;

trait TranslatableHintTrait
{
    public function setHint($query)
    {
        if (defined('NYRO_LOCALE')) {
            $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, NYRO_LOCALE);
            $query->setHint(
                \Gedmo\Translatable\TranslatableListener::HINT_FALLBACK,
                1 // fallback to default values in case if record is not translated
            );
        }

        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
    }
}
