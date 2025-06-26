<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Exception;
use NyroDev\NyroCmsBundle\Event\AdminHomeRedirectEvent;
use NyroDev\NyroCmsBundle\Services\UserService;
use NyroDev\UtilityBundle\Controller\AbstractController as NyroDevAbstractController;
use NyroDev\UtilityBundle\Services\MemberService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends NyroDevAbstractController
{
    use Traits\SubscribedServiceTrait;

    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->get(MemberService::class)->isLogged()) {
            return $this->redirectToRoute('nyrocms_admin_homepage');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@NyroDevNyroCms/Admin/login.html.php', [
            // last username entered by the user
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function indexAction(): Response
    {
        $adminHomeRedirectEvent = new AdminHomeRedirectEvent(
            $this->generateUrl('nyrocms_admin_data_content_tree'),
        );
        $this->get('event_dispatcher')->dispatch($adminHomeRedirectEvent, AdminHomeRedirectEvent::ADMIN_HOME_REDIRECT);

        return $this->redirect($adminHomeRedirectEvent->url);
    }

    public function forgotAction(Request $request, ?string $id = null, ?string $key = null, bool $welcome = false): Response
    {
        return $this->render('@NyroDevNyroCms/Admin/forgot.html.php', $this->get(UserService::class)->handleForgot('nyrocms_admin', $request, $id, $key, $welcome));
    }

    public function accountAction(Request $request): Response
    {
        return $this->render('@NyroDevNyroCms/Admin/account.html.php', $this->get(UserService::class)->handleAccount('nyrocms_admin', $request));
    }

    public function passwordAction(Request $request): Response
    {
        return $this->render('@NyroDevNyroCms/Admin/password.html.php', $this->get(UserService::class)->handleAccount('nyrocms_admin', $request));
    }

    public function ccAction(): Response
    {
        $fs = new Filesystem();

        $cacheDir = $this->get(KernelInterface::class)->getCacheDir();

        $ret = 'Nothing to remove';
        try {
            if ($fs->exists($cacheDir)) {
                $fs->remove($cacheDir);
                $ret = 'removed';
            }
        } catch (Exception $e) {
            $ret = $e->getMessage();
        }

        return new Response($ret);
    }
}
