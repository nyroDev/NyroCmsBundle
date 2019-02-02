<?php

namespace NyroDev\NyroCmsBundle\Listener;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as nyroDevAbstractService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityLoginListener extends nyroDevAbstractService
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($this->get(DbAbstractService::class)->isA($user, 'user') && method_exists($event->getAuthenticationToken(), 'getProviderKey')) {
            $userLogin = $this->get(DbAbstractService::class)->getNew('user_login');
            /* @var $userLogin \NyroDev\NyroCmsBundle\Model\UserLogin */
            $userLogin->setUser($user);
            $userLogin->setIpAddress($event->getRequest()->getClientIp());
            $userLogin->setPlace($event->getAuthenticationToken()->getProviderKey());
            $this->get(DbAbstractService::class)->flush();
        }
    }
}
