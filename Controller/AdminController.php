<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Services\UserService;
use NyroDev\UtilityBundle\Controller\AbstractController as NyroDevAbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends NyroDevAbstractController
{
    use Traits\SubscribedServiceTrait {
        getSubscribedServices as protected traitGetSubscribedServices;
    }

    public static function getSubscribedServices()
    {
        return array_merge(self::traitGetSubscribedServices(), [
            AuthenticationUtils::class,
        ]);
    }

    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get(AuthenticationUtils::class);

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('NyroDevNyroCmsBundle:Admin:login.html.php', array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    public function indexAction()
    {
        return $this->redirectToRoute('nyrocms_admin_data_content_tree');
    }

    public function forgotAction(Request $request, $id = null, $key = null, $welcome = false)
    {
        return $this->render('NyroDevNyroCmsBundle:Admin:forgot.html.php', $this->get(UserService::class)->handleForgot('admin', $request, $id, $key, $welcome));
    }

    public function accountAction(Request $request)
    {
        return $this->render('NyroDevNyroCmsBundle:Admin:account.html.php', $this->get(UserService::class)->handleAccount('admin', $request));
    }

    public function ccAction()
    {
        $fs = new Filesystem();

        $cacheDir = $this->get(KernelInterface::class)->getCacheDir();

        $ret = 'Nothing to remove';
        try {
            if ($fs->exists($cacheDir)) {
                $fs->remove($cacheDir);
                $ret = 'removed';
            }
        } catch (\Exception $e) {
            $ret = $e->getMessage();
        }

        return new Response($ret);
    }
}
