<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller {
	
	public function loginAction(Request $request) {
		$authenticationUtils = $this->get('security.authentication_utils');

		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();

		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('NyroDevNyroCmsBundle:Admin:login.html.php', array(
			// last username entered by the user
			'last_username' => $lastUsername,
			'error'         => $error,
		));
	}
	
    public function indexAction() {
        return $this->redirectToRoute('nyrocms_admin_data_content_tree');
    }
	
	public function forgotAction(Request $request, $id = null, $key = null, $welcome = false) {
		return $this->render('NyroDevNyroCmsBundle:Admin:forgot.html.php', $this->get('nyrocms_user')->handleForgot('admin', $request, $id, $key, $welcome));
	}
	
	public function accountAction(Request $request) {
		return $this->render('NyroDevNyroCmsBundle:Admin:account.html.php', $this->get('nyrocms_user')->handleAccount('admin', $request));
	}
	
}
