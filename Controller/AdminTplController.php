<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\UtilityBundle\Controller\AbstractController as NyroDevAbstractController;
use Symfony\Component\HttpFoundation\Request;
use NyroDev\NyroCmsBundle\Event\AdminMenuEvent;

class AdminTplController extends NyroDevAbstractController {

	protected $sessionRootName = 'rootContent';
	
	public function switchRootContentAction(Request $request, $id) {
		$request->getSession()->set($this->sessionRootName, $id);
		return $this->redirectToRoute('nyrocms_admin_data_content_tree', array('id'=>$id));
	}
	
    public function headerAction(Request $request) {
		$vars = array(
			'logged'=>$this->get('nyrodev_member')->isLogged()
		);
		if ($vars['logged']) {
			$tmpUriInit = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$adminPrefix = $this->getParameter('adminPrefix').'/';
			$tmpUriT = substr($tmpUriInit, strpos($tmpUriInit, $adminPrefix) + strlen($adminPrefix));
			$tmpUri = array_merge(explode('/', trim($tmpUriT, '/')), array_fill(0, 2, false));
			
			$adminPerRoot = $this->getParameter('nyroCms.content.admin_per_root');
			$rootContents = array();
			$tmp = $this->get('nyrocms_db')->getContentRepository()->findBy(array('level'=>0), array('title'=>'ASC'));
			$firstRoot = 1;
			foreach($tmp as $t) {
				$rootContents[$t->getId()] = $t;
				if (!$firstRoot)
					$firstRoot = $t->getId();
			}
			$curRootId = $request->getSession()->get($this->sessionRootName, $firstRoot);
			
			$menu = array(
				'contents'=>array(),
			);
			
			$vars['adminPerRoot'] = $adminPerRoot;
			if ($adminPerRoot) {
				$menu['contents']['root_'.$curRootId] = array(
					'uri'=>$this->generateUrl('nyrocms_admin_data_content_tree', array('id'=>$curRootId)),
					'name'=>$rootContents[$curRootId]->getTitle(),
					'active'=>$tmpUri[0] == 'content' && $this->get('nyrocms_admin')->getContentParentId() == $curRootId,
				);
				$vars['rootContents'] = $rootContents;
				$vars['curRootId'] = $curRootId;
			} else {
				foreach($rootContents as $rootContent) {
					$menu['contents']['root_'.$rootContent->getId()] = array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_content_tree', array('id'=>$rootContent->getId())),
						'name'=>$rootContent->getTitle(),
						'active'=>$tmpUri[0] == 'content' && $this->get('nyrocms_admin')->getContentParentId() == $rootContent->getId(),
					);
				}
			}
			
			$nyrocms = $this->get('nyrocms');
			$nyrocmsAdmin = $this->get('nyrocms_admin');
			
			$modules = $modulesIdent = array();
			$contentHandlers = $this->get('nyrocms_db')->getContentHandlerRepository()->findBy(array('hasAdmin'=>1));
			foreach($contentHandlers as $contentHandler) {
				$canAdmin = false;
				foreach($contentHandler->getContents() as $content) {
					$canAdmin = $canAdmin || $nyrocmsAdmin->canAdminContent($content) && (!$adminPerRoot || $content->getRoot() == $curRootId);
				}
				if ($canAdmin) {
					$handler = $nyrocms->getHandler($contentHandler);
					if ($handler->hasAdminMenuLink()) {
						$uri = $this->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
						$name = $adminPerRoot ? trim(str_replace($rootContents[$curRootId]->getTitle(), '', $contentHandler->getName())) : $contentHandler->getName();
						$modulesIdent['module_'.$contentHandler->getId()] = mb_strtolower($name);
						$modules['module_'.$contentHandler->getId()] = array(
							'uri'=>$uri,
							'name'=>$name,
							'active'=>$uri == $tmpUriInit || strpos($tmpUriInit, $uri.'/') !== false
						);
						
						$otherRoutes = $handler->getOtherAdminRoutes();
						if (is_array($otherRoutes) && count($otherRoutes)) {
							foreach($otherRoutes as $k=>$route) {
								$uri = $this->generateUrl($route['route'], $route['routePrm']);
								$name = $adminPerRoot ? trim(str_replace($rootContents[$curRootId]->getTitle(), '', $route['name'])) : $route['name'];
								$modules['module_'.$contentHandler->getId().'_'.$k] = array(
									'uri'=>$uri,
									'name'=>$name,
									'active'=>$uri == $tmpUriInit || strpos($tmpUriInit, $uri.'/') !== false
								);
							}
						}
						
					}
				}
			}
			
			if (count($modules)) {
				if (!isset($menu['modules']))
					$menu['modules'] = array();
				asort($modulesIdent);
				foreach($modulesIdent as $k=>$name)
					$menu['modules'][$k] = $modules[$k];
			}
			
			if ($nyrocmsAdmin->isSuperAdmin()) {
				// Don't forget to protect these URLs in security.yml!
				
				if ($nyrocmsAdmin->isDeveloper()) {
					$menu['contents']['contenthandler'] = array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_contentHandler'),
						'name'=>$this->trans('admin.contentHandler.viewTitle'),
						'active'=>$tmpUri[0] == 'contentHandler',
					);
				}
				
				if (!isset($menu['modules']))
					$menu['modules'] = array();
				
				$menu['access'] = array(
					'user'=>array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_user'),
						'name'=>$this->trans('admin.user.viewTitle'),
						'active'=>$tmpUri[0] == 'user',
					),
					'userRole'=>array(
						'uri'=>$this->generateUrl('nyrocms_admin_data_userRole'),
						'name'=>$this->trans('admin.userRole.viewTitle'),
						'active'=>$tmpUri[0] == 'userRole',
					),
				);
			}
			
			$adminMenuEvent = new AdminMenuEvent($tmpUri, $adminPerRoot, $rootContents, $curRootId);
			$adminMenuEvent->setMenu($menu);
			$this->get('event_dispatcher')->dispatch(AdminMenuEvent::ADMIN_MENU, $adminMenuEvent);
			
			$vars['menu'] = $adminMenuEvent->getMenu();
		}
        return $this->render('NyroDevNyroCmsBundle:AdminTpl:header.html.php', $vars);
    }

    public function footerAction() {
        return $this->render('NyroDevNyroCmsBundle:AdminTpl:footer.html.php');
    }
	
}
