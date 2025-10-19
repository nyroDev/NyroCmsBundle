<?php

namespace NyroDev\NyroCmsBundle\EventListener;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Model\SecurityUserEntityableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }

    public function __construct(
        private readonly DbAbstractService $dbAbstractService,
    ) {
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user && $user instanceof SecurityUserEntityableInterface) {
            $user = $user->getEntityUser();
        }

        if ($this->dbAbstractService->isA($user, 'user') && method_exists($event->getAuthenticationToken(), 'getFirewallName')) {
            $userLogin = $this->dbAbstractService->getNew('user_login');
            /* @var $userLogin \NyroDev\NyroCmsBundle\Model\UserLogin */
            $userLogin
                ->setUser($user)
                ->setIpAddress($event->getRequest()->getClientIp())
                ->setPlace($event->getAuthenticationToken()->getFirewallName())
            ;
            $this->dbAbstractService->flush();
        }
    }
}
