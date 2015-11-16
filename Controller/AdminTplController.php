<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\UtilityBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AdminTplController extends AbstractController {

    public function headerAction(Request $request) {
		$vars = array(
			'logged'=>$this->get('nyrodev_member')->isLogged()
		);
		if ($vars['logged']) {
			$tmpUriInit = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$adminPrefix = $this->getParameter('adminPrefix').'/';
			$tmpUri = substr($tmpUriInit, strpos($tmpUriInit, $adminPrefix) + strlen($adminPrefix));
			$tmp = array_merge(explode('/', trim($tmpUri, '/')), array_fill(0, 2, false));
			
			$vars['menu'] = array(
				'contents'=>array(
					'content'=>array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_content_tree'),
						'name'=>$this->trans('admin.content.viewTitle'),
						'active'=>$tmp[0] == 'content' && $this->get('nyrocms_admin')->getContentParentId() == 1,
					),
				),
			);
			
			$nyrocms = $this->get('nyrocms');
			$nyrocmsAdmin = $this->get('nyrocms_admin');
			
			$contentHandlers = $this->get('nyrocms_db')->getContentHandlerRepository()->findBy(array('hasAdmin'=>1));
			foreach($contentHandlers as $contentHandler) {
				$canAdmin = false;
				foreach($contentHandler->getContents() as $content) {
					$canAdmin = $canAdmin || $nyrocmsAdmin->canAdminContent($content);
				}
				if ($canAdmin) {
					if (!isset($vars['menu']['modules']))
						$vars['menu']['modules'] = array();
					$handler = $nyrocms->getHandler($contentHandler);
					$uri = $this->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
					$vars['menu']['modules']['module_'.$contentHandler->getId()] = array(
						'uri'=>$uri,
						'name'=>$contentHandler->getName(),
						'active'=>$uri == $tmpUriInit || strpos($tmpUriInit, $uri.'/') !== false
					);
				}
			}
			
			
			if ($nyrocmsAdmin->isSuperAdmin()) {
				// Don't forget to protect these URLs in security.yml!
				
				if ($nyrocmsAdmin->isDeveloper()) {
					$vars['menu']['contents']['contenthandler'] = array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_contentHandler'),
						'name'=>$this->trans('admin.contentHandler.viewTitle'),
						'active'=>$tmp[0] == 'contentHandler',
					);
				}
				
				if (!isset($vars['menu']['modules']))
					$vars['menu']['modules'] = array();
				
				// @todo handle custom menu here
			}
			
			if ($nyrocmsAdmin->isSuperAdmin()) {
				$vars['menu']['access'] = array(
					'user'=>array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_user'),
						'name'=>$this->trans('admin.user.viewTitle'),
						'active'=>$tmp[0] == 'user',
					),
					'userRole'=>array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_userRole'),
						'name'=>$this->trans('admin.userRole.viewTitle'),
						'active'=>$tmp[0] == 'userRole',
					),
				);
			}
		}
        return $this->render('NyroDevNyroCmsBundle:AdminTpl:header.html.php', $vars);
    }

    public function footerAction() {
        return $this->render('NyroDevNyroCmsBundle:AdminTpl:footer.html.php');
    }
	
}
