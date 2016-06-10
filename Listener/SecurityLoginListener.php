<?php

namespace NyroDev\NyroCmsBundle\Listener;

use NyroDev\UtilityBundle\Services\AbstractService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityLoginListener extends AbstractService
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($this->get('nyrocms_db')->isA($user, 'user') && method_exists($event->getAuthenticationToken(), 'getProviderKey')) {
            $userLogin = $this->get('nyrocms_db')->getNew('user_login');
            /* @var $userLogin \NyroDev\NyroCmsBundle\Model\UserLogin */
            $userLogin->setUser($user);
            $userLogin->setIpAddress($event->getRequest()->getClientIp());
            $userLogin->setPlace($event->getAuthenticationToken()->getProviderKey());
            $this->get('nyrocms_db')->flush();
        }
    }
}
