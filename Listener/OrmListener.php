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
			if ($object->getContentHandler()) {
				$ch = $this->get('nyrocms_db')->getContentHandlerRepository()->find($object->getContentHandler()->getId());
				$object->setContentHandler($ch);
			}
		}
	}
	
}