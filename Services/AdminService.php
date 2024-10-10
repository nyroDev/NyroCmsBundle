<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as NyroDevAbstractService;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService as NyroDevDbService;
use NyroDev\UtilityBundle\Services\MemberService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;

class AdminService extends NyroDevAbstractService
{
    use AssetsPackagesServiceableTrait;

    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly NyroDevDbService $nyrodevDbService,
        private readonly MemberService $memberService,
        private readonly NyroCmsService $nyroCmsService,
        private readonly DbAbstractService $dbService,
    ) {
    }

    protected ?array $userTypes = null;

    public function getUserTypeChoices()
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

    protected $contentParentId;

    public function setContentParentId($id)
    {
        $this->contentParentId = $id;
    }

    public function getContentParentId()
    {
        return $this->contentParentId;
    }

    protected $administrableContentIds;

    public function getAdministrableContentIds()
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

    protected $administrableRootContentIds = [];

    public function getAdministrableRootContentIds(string $rolePrefix = 'complete')
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

    public function hasRole(string $role): bool
    {
        return $this->memberService->isGranted($role);
    }

    public function isDeveloper(): bool
    {
        return $this->memberService->getUser()->getDevelopper();
    }

    public function canAdmin(Composable $row): bool
    {
        $canAdmin = false;
        if ($this->dbService->isA($row, 'content')) {
            $canAdmin = $this->canAdminContent($row);
        } elseif ($this->dbService->isA($row, 'content_spec')) {
            /* @var $row \Luxepack\DbBundle\Entity\ContentSpec */
            foreach ($row->getContentHandler()->getContents() as $content) {
                $canAdmin = $canAdmin || $this->canAdmin($content);
            }
        } else {
            $canAdmin = $this->canAdminContent($row->getParent());
        }

        return $canAdmin;
    }

    public function canAdminContent(Content $content): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $contentIds = $this->getAdministrableContentIds();

        return isset($contentIds[$content->getId()]) ? $contentIds[$content->getId()] : false;
    }

    public function canRootComposer(Content $content): bool
    {
        return $this->getParameter('nyrocms.content.root_composer');
    }

    public function getContentMaxLevel(Content $content)
    {
        return $this->getParameter('nyrocms.content.maxlevel');
    }

    public function canHaveSub(Content $content): bool
    {
        return $content ? $content->getLevel() < $this->getContentMaxLevel($content) : true;
    }

    public function updateContentUrl(Content $row, bool $isEdit = false, bool $child = true, bool $forceUpdate = false)
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

    protected function updateContentUrlRec($parentId, $oldUrl, $newUrl, string $locale, bool $forceUpdate = false)
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

    public function getContentsChoiceTypeOptions($maxLevel = null, array $limitRootIds = [])
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

    protected function getContentsOptionsChoices(array &$contents, array &$contentsLevel, $parent, $maxLevel, $curLevel = 0, array $limitRootIds = [])
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

    public function getIcon(string $name): string
    {
        return '<svg class="icon icon-'.$name.'">'.
                    '<use xlink:href="'.$this->getAssetsPackages()->getUrl('bundles/nyrodevnyrocms/images/icons.svg').'#'.$name.'"></use>'.
                '</svg>';
    }
}
