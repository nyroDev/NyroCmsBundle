<?php

namespace NyroDev\NyroCmsBundle\Listener;

use Gedmo\Loggable\LoggableListener as SrcLoggableListener;

class LoggableListener extends SrcLoggableListener {
	
    protected function prePersistLogEntry($logEntry, $object) {
		if (method_exists($object, 'getTranslatableLocale') && method_exists($logEntry, 'setLocale') && $object->getTranslatableLocale())
			$logEntry->setLocale($object->getTranslatableLocale());
    }
	
}