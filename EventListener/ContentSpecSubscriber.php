<?php

namespace NyroDev\NyroCmsBundle\EventListener;

use App\Entity\ContentSpec;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;

class ContentSpecSubscriber
{
    public function __construct(
        private readonly DbAbstractService $dbAbstractService,
    ) {
    }

    public function postLoad(ContentSpec $contentSpec, LifecycleEventArgs $event): void
    {
        // Reload contentHandler to correctly fill contents
        if ($contentSpec->getContentHandler() && is_null($contentSpec->getContentHandler()->getContents())) {
            $ch = $this->dbAbstractService->getContentHandlerRepository()->find($contentSpec->getContentHandler()->getId());
            $contentSpec->getContentHandler()->setContents($ch->getContents());
            unset($ch);
            $ch = null;

            // Reload content parent to be in the same locale, useful for translated URLs
            /*
            if ($contentSpec->getParent() && $contentSpec->getTranslatableLocale() != $contentSpec->getParent()->getTranslatableLocale()) {
                $contentSpec->getParent()->setTranslatableLocale($contentSpec->getTranslatableLocale());
                $this->dbAbstractService->refresh($contentSpec->getParent());
            }
             */
        }
    }
}
