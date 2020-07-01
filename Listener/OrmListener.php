<?php

namespace NyroDev\NyroCmsBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as nyroDevAbstractService;

class OrmListener extends nyroDevAbstractService implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    public function postLoad(LifecycleEventArgs $args = null)
    {
        $object = $args->getObject();
        if ($this->get(DbAbstractService::class)->isA($object, 'content_spec')) {
            /* @var $object \NyroDev\NyroCmsBundle\Model\ContentSpec */

            // Reload contentHandler to correctly fill contents
            if ($object->getContentHandler() && is_null($object->getContentHandler()->getContents())) {
                $ch = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($object->getContentHandler()->getId());
                $object->getContentHandler()->setContents($ch->getContents());
                unset($ch);
                $ch = null;
            }

            // Reload content parent to be in the same locale, useful for translated URLs
            /*
            if ($object->getParent() && $object->getTranslatableLocale() != $object->getParent()->getTranslatableLocale()) {
                $object->getParent()->setTranslatableLocale($object->getTranslatableLocale());
                $this->get(DbAbstractService::class)->refresh($object->getParent());
            }
             */
        }
    }
}
