<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Event\AdminMenuEvent;
use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Controller\AbstractController as NyroDevAbstractController;
use NyroDev\UtilityBundle\Services\MemberService;
use Symfony\Component\HttpFoundation\Request;

class AdminTplController extends NyroDevAbstractController
{
    use Traits\SubscribedServiceTrait;

    protected $sessionRootName = 'rootContent';

    public function switchRootContentAction(Request $request, $id)
    {
        $request->getSession()->set($this->sessionRootName, $id);

        return $this->redirectToRoute('nyrocms_admin_data_content_tree', ['id' => $id]);
    }

    public function header(Request $request)
    {
        $vars = [
            'logged' => $this->get(MemberService::class)->isLogged(),
        ];
        if ($vars['logged']) {
            $tmpUriInit = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $adminPrefix = $this->getParameter('adminPrefix').'/';
            $tmpUriT = substr($tmpUriInit, strpos($tmpUriInit, $adminPrefix) + strlen($adminPrefix));
            $tmpUri = array_merge(explode('/', trim($tmpUriT, '/')), array_fill(0, 2, false));

            $adminPerRoot = $this->getParameter('nyrocms.content.admin_per_root');
            $rootContents = [];
            $tmp = $this->get(DbAbstractService::class)->getContentRepository()->findBy(['level' => 0], ['title' => 'ASC']);
            $firstRoot = 1;
            foreach ($tmp as $t) {
                $rootContents[$t->getId()] = $t;
                if (!$firstRoot) {
                    $firstRoot = $t->getId();
                }
            }
            $curRootId = $request->getSession()->get($this->sessionRootName, $firstRoot);

            $menu = [
                'contents' => [],
            ];

            $vars['adminPerRoot'] = $adminPerRoot;
            if ($adminPerRoot) {
                $menu['contents']['root_'.$curRootId] = [
                    'uri' => $this->generateUrl('nyrocms_admin_data_content_tree', ['id' => $curRootId]),
                    'name' => $rootContents[$curRootId]->getTitle(),
                    'active' => 'content' == $tmpUri[0] && $this->get(AdminService::class)->getContentParentId() == $curRootId,
                ];
                $vars['rootContents'] = $rootContents;
                $vars['curRootId'] = $curRootId;
            } else {
                foreach ($rootContents as $rootContent) {
                    $menu['contents']['root_'.$rootContent->getId()] = [
                        'uri' => $this->generateUrl('nyrocms_admin_data_content_tree', ['id' => $rootContent->getId()]),
                        'name' => $rootContent->getTitle(),
                        'active' => 'content' == $tmpUri[0] && $this->get(AdminService::class)->getContentParentId() == $rootContent->getId(),
                    ];
                }
            }

            $nyrocms = $this->get(NyroCmsService::class);
            $nyrocmsAdmin = $this->get(AdminService::class);

            $modules = $modulesIdent = [];
            $contentHandlers = $this->get(DbAbstractService::class)->getContentHandlerRepository()->findBy(['hasAdmin' => 1]);
            foreach ($contentHandlers as $contentHandler) {
                $canAdmin = false;
                foreach ($contentHandler->getContents() as $content) {
                    $canAdmin = $canAdmin || $nyrocmsAdmin->canAdminContent($content) && (!$adminPerRoot || $content->getRoot() == $curRootId);
                }
                if ($canAdmin) {
                    $handler = $nyrocms->getHandler($contentHandler);
                    if ($handler->hasAdminMenuLink()) {
                        $uri = $this->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
                        $name = $adminPerRoot ? trim(str_replace($rootContents[$curRootId]->getTitle(), '', $contentHandler->getName())) : $contentHandler->getName();
                        $modulesIdent['module_'.$contentHandler->getId()] = mb_strtolower($name);
                        $modules['module_'.$contentHandler->getId()] = [
                            'uri' => $uri,
                            'name' => $name,
                            'active' => $uri == $tmpUriInit || false !== strpos($tmpUriInit, $uri.'/'),
                        ];

                        $otherRoutes = $handler->getOtherAdminRoutes();
                        if (is_array($otherRoutes) && count($otherRoutes)) {
                            foreach ($otherRoutes as $k => $route) {
                                $uri = $this->generateUrl($route['route'], $route['routePrm']);
                                $name = $adminPerRoot ? trim(str_replace($rootContents[$curRootId]->getTitle(), '', $route['name'])) : $route['name'];
                                $modulesIdent['module_'.$contentHandler->getId().'_'.$k] = mb_strtolower($name);
                                $modules['module_'.$contentHandler->getId().'_'.$k] = [
                                    'uri' => $uri,
                                    'name' => $name,
                                    'active' => $uri == $tmpUriInit || false !== strpos($tmpUriInit, $uri.'/'),
                                ];
                            }
                        }
                    }
                }
            }

            if (count($modules)) {
                if (!isset($menu['modules'])) {
                    $menu['modules'] = [];
                }
                asort($modulesIdent);
                foreach ($modulesIdent as $k => $name) {
                    $menu['modules'][$k] = $modules[$k];
                }
            }

            if ($nyrocmsAdmin->isSuperAdmin()) {
                // Don't forget to protect these URLs in security.yaml!

                if ($nyrocmsAdmin->isDeveloper()) {
                    $menu['contents']['contenthandler'] = [
                        'uri' => $this->generateUrl('nyrocms_admin_data_contentHandler'),
                        'name' => $this->trans('admin.contentHandler.viewTitle'),
                        'active' => 'contentHandler' == $tmpUri[0],
                    ];
                }

                if (!isset($menu['modules'])) {
                    $menu['modules'] = [];
                }

                $menu['access'] = [
                    'user' => [
                        'uri' => $this->generateUrl('nyrocms_admin_data_user'),
                        'name' => $this->trans('admin.user.viewTitle'),
                        'active' => 'user' == $tmpUri[0],
                    ],
                    'userRole' => [
                        'uri' => $this->generateUrl('nyrocms_admin_data_userRole'),
                        'name' => $this->trans('admin.userRole.viewTitle'),
                        'active' => 'userRole' == $tmpUri[0],
                    ],
                ];
            }

            $adminMenuEvent = new AdminMenuEvent($tmpUri, $adminPerRoot, $rootContents, $curRootId);
            $adminMenuEvent->setMenu($menu);
            $this->get('event_dispatcher')->dispatch($adminMenuEvent, AdminMenuEvent::ADMIN_MENU);

            $vars['menu'] = $adminMenuEvent->getMenu();
        }

        return $this->render('@NyroDevNyroCms/AdminTpl/header.html.php', $vars);
    }

    public function footer()
    {
        return $this->render('@NyroDevNyroCms/AdminTpl/footer.html.php');
    }
}
