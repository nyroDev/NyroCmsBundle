<?php

namespace NyroDev\NyroCmsBundle\Listener;

use NyroDev\UtilityBundle\Services\AbstractService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityLoginListener extends AbstractService {
	
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
		$user = $event->getAuthenticationToken()->getUser();
		
		if ($user instanceof \NyroDev\NyroCmsBundle\Model\User && method_exists($event->getAuthenticationToken(), 'getProviderKey')) {
			$userLogin = new \NyroDev\NyroCmsBundle\Model\UserLogin();
			$userLogin->setUser($user);
			$userLogin->setIpAddress($event->getRequest()->getClientIp());
			$userLogin->setPlace($event->getAuthenticationToken()->getProviderKey());
			$this->getDoctrine()->getManager()->persist($userLogin);
			$this->getDoctrine()->getManager()->flush();
		}
	}
	
}