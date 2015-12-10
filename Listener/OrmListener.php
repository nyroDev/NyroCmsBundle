<?php

namespace NyroDev\NyroCmsBundle\Listener;

use Doctrine\Common\EventSubscriber;
use NyroDev\UtilityBundle\Services\AbstractService;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class OrmListener extends AbstractService implements EventSubscriber {
	
	public function getSubscribedEvents() {
        return array(
            'postLoad',
        );
    }
	
	public function postLoad(LifecycleEventArgs $args = null) {
		$object = $args->getObject();
		if ($this->get('nyrocms_db')->isA($object, 'content_spec')) {
			/* @var $object \NyroDev\NyroCmsBundle\Model\ContentSpec */
			
			// Reload contentHandler to correctly fill contents
			if ($object->getContentHandler() && is_null($object->getContentHandler()->getContents())) {
				$ch = $this->get('nyrocms_db')->getContentHandlerRepository()->find($object->getContentHandler()->getId());
				$object->getContentHandler()->setContents($ch->getContents());
				unset($ch);
				$ch = null;
			}
			
			// Reload content parent to be in the same locale, useful for translated URLs
			if ($object->getParent() && $object->getTranslatableLocale() != $object->getParent()->getTranslatableLocale()) {
				$object->getParent()->setTranslatableLocale($object->getTranslatableLocale());
				$this->get('nyrocms_db')->refresh($object->getParent());
			}
		}
	}
	
}