<?php

namespace NyroDev\NyroCmsBundle\Services;

use App\Entity\Template;
use NyroDev\NyroCmsBundle\Event\AdminContentTreeConfigEvent;
use NyroDev\NyroCmsBundle\Event\AdminMenuEvent;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as NyroDevAbstractService;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService as NyroDevDbService;
use NyroDev\UtilityBundle\Services\MemberService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use NyroDev\UtilityBundle\Utility\Menu\Link;
use NyroDev\UtilityBundle\Utility\Menu\RootMenu;
use NyroDev\UtilityBundle\Utility\Menu\Separator;
use NyroDev\UtilityBundle\Utility\Menu\Text;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminService extends NyroDevAbstractService
{
    use AssetsPackagesServiceableTrait;

    public const ROLE_COMPOSER = 'NYROCMS_COMPOSER';
    public const ROLE_TEMPLATE = 'NYROCMS_TEMPLATE';

    public const SESSION_ROOT_NAME = 'rootContent';

    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly NyroDevDbService $nyrodevDbService,
        private readonly MemberService $memberService,
        private readonly NyroCmsService $nyroCmsService,
        private readonly DbAbstractService $dbService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    protected ?array $userTypes = null;

    public function getUserTypeChoices(): array
    {
        if (is_null($this->userTypes)) {
            $this->userTypes = [];
            foreach ($this->getParameter('nyrocms.user_types') as $type) {
                $identTr = 'admin.userTypes.'.$type;
                $tr = $this->trans($identTr);
                if (!$tr || $tr == $identTr) {
                    $tr = ucfirst($type);
                }
                $this->userTypes[$type] = $tr;
            }
        }

        return $this->userTypes;
    }

    protected ?array $userRoles = null;

    public function getUserRoles(): array
    {
        if (is_null($this->userRoles)) {
            $this->userRoles = [];
            foreach ($this->dbService->getRepository('user_role')->findAll() as $tmp) {
                $this->userRoles[$tmp->getId()] = $tmp;
            }
        }

        return $this->userRoles;
    }

    protected mixed $contentParentId;

    public function setContentParentId(mixed $id): void
    {
        $this->contentParentId = $id;
    }

    public function getContentParentId(): mixed
    {
        return $this->contentParentId;
    }

    protected ?array $administrableContentIds = null;

    public function getAdministrableContentIds(): array
    {
        if (is_null($this->administrableContentIds)) {
            $this->administrableContentIds = [];
            if ($this->memberService->isLogged()) {
                $user = $this->memberService->getUser();
                /* @var $user \NyroDev\NyroCmsBundle\Model\User */

                $repoContent = $this->dbService->getContentRepository();
                foreach ($user->getUserRoles() as $userRole) {
                    /* @var $userRole \NyroDev\NyroCmsBundle\Model\UserRole */
                    if (!$userRole->getInternal()) {
                        foreach ($userRole->getContents() as $content) {
                            if (!isset($this->administrableContentIds[$content->getId()])) {
                                $this->administrableContentIds[$content->getId()] = 'root';
                                foreach ($repoContent->children($content) as $c) {
                                    $this->administrableContentIds[$c->getId()] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->administrableContentIds;
    }

    public function getTreeChildren(Content $parent, $directOnly = false): array
    {
        return $this->dbService->getContentRepository()->children($parent, $directOnly);
    }

    protected array $administrableRootContentIds = [];

    public function getAdministrableRootContentIds(string $rolePrefix = 'complete'): array
    {
        if (!isset($this->administrableRootContentIds[$rolePrefix])) {
            $this->administrableRootContentIds[$rolePrefix] = [];
            $fullRootIds = $this->isSuperAdmin() || $this->isDeveloper();

            if (!$fullRootIds) {
                $rolePrefixLn = strlen($rolePrefix);
                foreach ($this->memberService->getUser()->getUserRoles() as $role) {
                    if (
                        (('complete' == $rolePrefix || 'root' == $rolePrefix) && !$role->getInternal())
                        || ($role->getSecurityRoleName() === 'ROLE_'.$rolePrefix || substr($role->getSecurityRoleName(), 0, 6 + $rolePrefixLn) === 'ROLE_'.$rolePrefix.'_')
                    ) {
                        // This is an corresponding role, check it against
                        if ($role->getContents()->count() > 0) {
                            foreach ($role->getContents() as $content) {
                                if ('root' != $rolePrefix || $content->getRoot() == $content->getId()) {
                                    $this->administrableRootContentIds[$rolePrefix][$content->getRoot()] = true;
                                }
                            }
                        } else {
                            $fullRootIds = true;
                        }
                    }
                }
            }

            if ($fullRootIds) {
                $this->administrableRootContentIds[$rolePrefix] = [];
                foreach ($this->dbService->getContentRepository()->children(null, true) as $content) {
                    $this->administrableRootContentIds[$rolePrefix][$content->getId()] = true;
                }
            }
        }

        return $this->administrableRootContentIds[$rolePrefix];
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('ROLE_SUPERADMIN');
    }

    public function hasRole(string $role, mixed $subject = null): bool
    {
        return $this->memberService->isGranted($role, $subject);
    }

    public function isDeveloper(): bool
    {
        return $this->memberService->getUser()->getDevelopper();
    }

    public function canAdmin(Composable $row): bool
    {
        $canAdmin = false;
        $checkComposerRole = false;
        if ($this->dbService->isA($row, 'content')) {
            $canAdmin = $this->canAdminContent($row);
        } elseif ($this->dbService->isA($row, 'content_spec')) {
            /* @var $row \Luxepack\DbBundle\Entity\ContentSpec */
            foreach ($row->getContentHandler()->getContents() as $content) {
                $canAdmin = $canAdmin || $this->canAdmin($content);
            }
        } elseif ($this->dbService->isA($row, 'template')) {
            $canAdmin = $this->canAdminTemplate($row);
            $checkComposerRole = true;
        } elseif ($row->getParent()) {
            $canAdmin = $this->canAdminContent($row->getParent());
            $checkComposerRole = true;
        } else {
            $checkComposerRole = true;
        }

        if ($canAdmin || $checkComposerRole) {
            $canAdmin = $this->hasRole(self::ROLE_COMPOSER, $row);
        }

        return $canAdmin;
    }

    private function canAdminContent(Content $content): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $contentIds = $this->getAdministrableContentIds();

        return isset($contentIds[$content->getId()]) ? $contentIds[$content->getId()] : false;
    }

    private function canAdminTemplate(Template $template): bool
    {
        if (!$this->hasRole(self::ROLE_TEMPLATE, $template)) {
            return false;
        }
        if ($this->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    public function canRootComposer(Content $content): bool
    {
        $event = new AdminContentTreeConfigEvent(
            $content,
            AdminContentTreeConfigEvent::CONFIG_CAN_ROOT_COMPOSER,
            $this->getParameter('nyrocms.content.root_composer')
        );
        $this->eventDispatcher->dispatch($event, AdminContentTreeConfigEvent::ADMIN_CONTENT_TREE_CONFIG);

        return $event->value;
    }

    public function getContentMaxLevel(Content $content): int
    {
        $event = new AdminContentTreeConfigEvent(
            $content,
            AdminContentTreeConfigEvent::CONFIG_CONTENT_MAX_LEVEL,
            $this->getParameter('nyrocms.content.maxlevel')
        );
        $this->eventDispatcher->dispatch($event, AdminContentTreeConfigEvent::ADMIN_CONTENT_TREE_CONFIG);

        return $event->value;
    }

    public function canHaveSub(Content $content): bool
    {
        return $content ? $content->getLevel() < $this->getContentMaxLevel($content) : true;
    }

    public function updateContentUrl(Content $row, bool $isEdit = false, bool $child = true, bool $forceUpdate = false): void
    {
        if (!$isEdit || $this->nyroCmsService->getDefaultLocale($row) == $row->getTranslatableLocale() || !$this->nyroCmsService->disabledLocaleUrls($row->getTranslatableLocale())) {
            $oldUrl = $row->getUrl();

            $prefix = null;
            if ($row->getParent()) {
                $parent = $this->dbService->getContentRepository()->find($row->getParent()->getId());
                $parent->setTranslatableLocale($row->getTranslatableLocale());
                $this->nyrodevDbService->refresh($parent);
                $prefix = $parent->getUrl();
            }

            $url = $prefix.'/'.$this->nyrodevService->urlify(str_replace(['+', '&'], ['plus', 'et'], $row->getTitle()));
            $url = str_replace('//', '/', $url);

            $row->setUrl($url);

            if ($forceUpdate || ($row->getUrl() != $oldUrl && $isEdit)) {
                if ($child) {
                    $this->updateContentUrlRec($row->getId(), $oldUrl.'/', $row->getUrl().'/', $row->getTranslatableLocale(), $forceUpdate);
                }
            }
        }
    }

    protected function updateContentUrlRec(mixed $parentId, string $oldUrl, string $newUrl, string $locale, bool $forceUpdate = false): void
    {
        $rows = $this->dbService->getContentRepository()->findBy(['parent' => $parentId]);
        foreach ($rows as $row) {
            $row->setTranslatableLocale($locale);
            $this->dbService->refresh($row);
            $old = $row->getUrl();
            $new = str_replace([$oldUrl, '//'], [$newUrl, '/'], $row->getUrl());
            if ($forceUpdate || $old != $new) {
                $row->setUrl($new);
                $this->updateContentUrlRec($row->getId(), $oldUrl, $newUrl, $locale, $forceUpdate);
            }
        }
    }

    public function getTemplateStateChoices(): array
    {
        return [
            Template::STATE_ACTIVE => $this->trans('admin.state.state_'.Template::STATE_ACTIVE),
            Template::STATE_DISABLED => $this->trans('admin.state.state_'.Template::STATE_DISABLED),
        ];
    }

    public function getContentStateChoices(): array
    {
        return [
            Content::STATE_ACTIVE => $this->trans('admin.state.state_'.Content::STATE_ACTIVE),
            Content::STATE_INVISIBLE => $this->trans('admin.state.state_'.Content::STATE_INVISIBLE),
            Content::STATE_DISABLED => $this->trans('admin.state.state_'.Content::STATE_DISABLED),
        ];
    }

    public function getContentSpecStateChoices(): array
    {
        return [
            ContentSpec::STATE_ACTIVE => $this->trans('admin.state.state_'.ContentSpec::STATE_ACTIVE),
            ContentSpec::STATE_INVISIBLE => $this->trans('admin.state.state_'.ContentSpec::STATE_INVISIBLE),
            ContentSpec::STATE_DISABLED => $this->trans('admin.state.state_'.ContentSpec::STATE_DISABLED),
        ];
    }

    public function getContentsChoiceTypeOptions(?int $maxLevel = null, array $limitRootIds = []): array
    {
        if (is_null($maxLevel)) {
            $maxLevel = $this->getParameter('nyrocms.content.maxlevel');
        }
        $contents = [];
        $contentsLevel = [];
        $this->getContentsOptionsChoices($contents, $contentsLevel, null, $maxLevel, 0, $limitRootIds);

        return [
            'expanded' => true,
            'choices' => $contents,
            'attr' => [
                'class' => 'contentsList',
            ],
            'choice_attr' => function ($choice, $key) use ($contentsLevel) {
                return [
                    'class' => 'contentLvl'.$contentsLevel[$key].($choice->getParent() ? ' contentPar'.$choice->getParent()->getId() : ' contentRoot'),
                ];
            },
        ];
    }

    protected function getContentsOptionsChoices(array &$contents, array &$contentsLevel, mixed $parent, int $maxLevel, int $curLevel = 0, array $limitRootIds = []): void
    {
        foreach ($this->dbService->getContentRepository()->children($parent, true) as $child) {
            $canUse = count($limitRootIds) > 0 ? isset($limitRootIds[$child->getId()]) && $limitRootIds[$child->getId()] : true;
            if ($canUse) {
                $contents[$child->getId()] = $child;
                $contentsLevel[$child->getId()] = $curLevel;
                if ($maxLevel > 0) {
                    $this->getContentsOptionsChoices($contents, $contentsLevel, $child, $maxLevel - 1, $curLevel + 1);
                }
            }
        }
    }

    public function getHeaderVars(): array
    {
        $rootMenu = new RootMenu();

        $vars = [
            'logged' => $this->memberService->isLogged(),
            'user' => $this->memberService->getUser(),
            'menu' => $rootMenu,
        ];
        if ($vars['logged']) {
            $rootMenu->addChild('home', new Link(
                $this->generateUrl('nyrocms_admin_homepage'),
                $this->trans('admin.menu.home'),
                icon: 'home',
            ));

            $rootMenu->addChild('sep1', new Separator());

            $tmpUriInit = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $adminPrefix = $this->getParameter('adminPrefix').'/';
            $tmpUriT = substr($tmpUriInit, strpos($tmpUriInit, $adminPrefix) + strlen($adminPrefix));
            $tmpUri = array_merge(explode('/', trim($tmpUriT, '/')), array_fill(0, 4, false));

            $vars['uriSplitted'] = $tmpUri;

            $user = $this->memberService->getUser();
            $loggedUserMenu = new Text($user->getUsualName(), icon: 'user');
            $rootMenu->addChild('loggedUser', $loggedUserMenu);

            $loggedUserMenu->addChild('account', new Link(
                $this->generateUrl('nyrocms_admin_account'),
                $this->trans('admin.menu.account'),
                'account' == $tmpUri[0],
                icon: 'user'
            ));

            $loggedUserMenu->addChild('password', new Link(
                $this->generateUrl('nyrocms_admin_password'),
                $this->trans('admin.menu.password'),
                'password' == $tmpUri[0],
                icon: 'password'
            ));

            $loggedUserMenu->addChild('logout', new Link(
                $this->generateUrl('nyrocms_admin_security_logout'),
                $this->trans('admin.menu.logout'),
                attrs: [
                    'class' => 'confirmLink',
                    'data-confirmtxt' => $this->trans('admin.menu.logoutConfirm'),
                    'data-confirmbtntxt' => $this->getIcon('logout').'<span>'.$this->trans('admin.menu.logout').'</span>',
                ],
                icon: 'logout'
            ));

            $rootMenu->addChild('sep2', new Separator());

            $adminPerRoot = $this->getParameter('nyrocms.content.admin_per_root');
            $vars['adminPerRoot'] = $adminPerRoot;

            $rootContents = [];
            $tmp = $this->dbService->getContentRepository()->findBy(['level' => 0], ['title' => 'ASC']);
            $firstRoot = 1;
            foreach ($tmp as $t) {
                $rootContents[$t->getId()] = $t;
                if (!$firstRoot) {
                    $firstRoot = $t->getId();
                }
            }
            $curRootId = $this->get('request_stack')->getSession()->get(self::SESSION_ROOT_NAME, $firstRoot);
            $vars['rootContents'] = $rootContents;
            $vars['curRootId'] = $curRootId;

            $contentsMenu = new Text($this->trans('admin.menu.contents'), icon: 'content');
            $rootMenu->addChild('contents', $contentsMenu);

            $vars['adminPerRoot'] = $adminPerRoot;
            if ($adminPerRoot) {
                $isCurrentRoot = $this->getContentParentId() == $curRootId;
                $contentsMenu->addChild('root_'.$curRootId, new Link(
                    $this->generateUrl($isCurrentRoot ? 'nyrocms_admin_data_content_tree' : 'nyrocms_admin_switch_rootContent', ['id' => $curRootId]),
                    $rootContents[$curRootId]->getTitle(),
                    'content' == $tmpUri[0] && $isCurrentRoot,
                    icon: 'content'
                ));
                $vars['rootContents'] = $rootContents;
                $vars['curRootId'] = $curRootId;
            } else {
                foreach ($rootContents as $rootContent) {
                    $contentsMenu->addChild('root_'.$rootContent->getId(), new Link(
                        $this->generateUrl('nyrocms_admin_data_content_tree', ['id' => $rootContent->getId()]),
                        $rootContent->getTitle(),
                        'content' == $tmpUri[0] && $this->getContentParentId() == $rootContent->getId(),
                        icon: 'content'
                    ));
                }
            }

            $contentHandlers = $this->dbService->getContentHandlerRepository()->findBy(['hasAdmin' => 1]);
            foreach ($contentHandlers as $contentHandler) {
                $canAdmin = false;
                foreach ($contentHandler->getContents() as $content) {
                    $canAdmin = $canAdmin || $this->canAdmin($content) && (!$adminPerRoot || $content->getRoot() == $curRootId);
                }
                if ($canAdmin) {
                    $handler = $this->nyroCmsService->getHandler($contentHandler);
                    if ($handler->hasAdminMenuLink()) {
                        $uri = $this->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
                        $name = $adminPerRoot ? trim(str_replace($rootContents[$curRootId]->getTitle(), '', $contentHandler->getName())) : $contentHandler->getName();

                        $contentsMenu->addChild('module_'.$contentHandler->getId(), new Link(
                            $uri,
                            $name,
                            $uri == $tmpUriInit || false !== strpos($tmpUriInit, $uri.'/'),
                            icon: 'misc'
                        ));

                        $otherRoutes = $handler->getOtherAdminRoutes();
                        if (is_array($otherRoutes) && count($otherRoutes)) {
                            foreach ($otherRoutes as $k => $route) {
                                $uri = $this->generateUrl($route['route'], $route['routePrm']);
                                $name = $adminPerRoot ? trim(str_replace($rootContents[$curRootId]->getTitle(), '', $route['name'])) : $route['name'];

                                $contentsMenu->addChild('module_'.$contentHandler->getId().'_'.$k, new Link(
                                    $uri,
                                    $name,
                                    $uri == $tmpUriInit || false !== strpos($tmpUriInit, $uri.'/'),
                                    icon: 'misc'
                                ));
                            }
                        }
                    }
                }
            }

            if ($this->isSuperAdmin()) {
                // Don't forget to protect these URLs in security.yaml!

                if ($this->isDeveloper()) {
                    $contentsMenu->addChild('contenthandler', new Link(
                        $this->generateUrl('nyrocms_admin_data_contentHandler'),
                        $this->trans('admin.contentHandler.viewTitle'),
                        'contentHandler' == $tmpUri[0],
                        icon: 'misc'
                    ));
                }

                $contentsMenu->addChild('template', new Link(
                    $this->generateUrl('nyrocms_admin_data_template'),
                    $this->trans('admin.template.viewTitle'),
                    'template' == $tmpUri[0],
                    icon: 'composer'
                ));

                $contentsMenu->addChild('templateCategory', new Link(
                    $this->generateUrl('nyrocms_admin_data_templateCategory'),
                    $this->trans('admin.templateCategory.viewTitle'),
                    'templateCategory' == $tmpUri[0],
                    icon: 'composer'
                ));

                $accessMenu = new Text($this->trans('admin.menu.access'), icon: 'rights');
                $rootMenu->addChild('access', $accessMenu);
                $accessMenu->addChild('user', new Link(
                    $this->generateUrl('nyrocms_admin_data_user'),
                    $this->trans('admin.user.viewTitle'),
                    'user' == $tmpUri[0],
                    icon: 'user'
                ));
                $accessMenu->addChild('userRole', new Link(
                    $this->generateUrl('nyrocms_admin_data_userRole'),
                    $this->trans('admin.userRole.viewTitle'),
                    'userRole' == $tmpUri[0],
                    icon: 'rights'
                ));
            }
        }

        $adminMenuEvent = new AdminMenuEvent($vars);
        $this->eventDispatcher->dispatch($adminMenuEvent, AdminMenuEvent::ADMIN_MENU);

        $vars = $adminMenuEvent->vars;

        return $vars;
    }

    public function getIcon(string $name, ?string $class = null, ?string $attrs = null): string
    {
        return $this->nyrodevService->getIconHelper()->getIcon(NyroCmsService::ICON_PATH.'#'.$name, $class, $attrs);
    }

    public function goToUrlDialogResponse(
        string $redirectUrl,
        string $text,
    ): Response {
        return new Response('<a href="'.$redirectUrl.'" class="goToUrl">'.$text.'</a>');
    }
}
