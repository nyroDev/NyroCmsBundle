<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as nyroDevAbstractService;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService as nyroDevDbService;
use NyroDev\UtilityBundle\Services\MemberService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

class AdminService extends nyroDevAbstractService
{
    /**
     * @var AssetsHelper
     */
    protected $assetsHelper;

    public function __construct(ContainerInterface $container, AssetsHelper $assetsHelper)
    {
        parent::__construct($container);
        $this->assetsHelper = $assetsHelper;
    }

    protected $userTypes;

    public function getUserTypeChoices()
    {
        if (is_null($this->userTypes)) {
            $this->userTypes = array();
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

    protected $userRoles;

    public function getUserRoles()
    {
        if (is_null($this->userRoles)) {
            $this->userRoles = array();
            foreach ($this->get(DbAbstractService::class)->getRepository('user_role')->findAll() as $tmp) {
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
            $this->administrableContentIds = array();
            if ($this->get(MemberService::class)->isLogged()) {
                $user = $this->get(MemberService::class)->getUser();
                /* @var $user \NyroDev\NyroCmsBundle\Model\User */

                $repoContent = $this->get(DbAbstractService::class)->getContentRepository();
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

    protected $administrableRootContentIds = array();

    public function getAdministrableRootContentIds($rolePrefix = 'complete')
    {
        if (!isset($this->administrableRootContentIds[$rolePrefix])) {
            $this->administrableRootContentIds[$rolePrefix] = array();
            $fullRootIds = $this->isSuperAdmin() || $this->isDeveloper();

            if (!$fullRootIds) {
                $rolePrefixLn = strlen($rolePrefix);
                foreach ($this->get(MemberService::class)->getUser()->getUserRoles() as $role) {
                    if (
                            (('complete' == $rolePrefix || 'root' == $rolePrefix) && !$role->getInternal()) ||
                            ($role->getSecurityRoleName() === 'ROLE_'.$rolePrefix || substr($role->getSecurityRoleName(), 0, 6 + $rolePrefixLn) === 'ROLE_'.$rolePrefix.'_')
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
                $this->administrableRootContentIds[$rolePrefix] = array();
                foreach ($this->get(DbAbstractService::class)->getContentRepository()->children(null, true) as $content) {
                    $this->administrableRootContentIds[$rolePrefix][$content->getId()] = true;
                }
            }
        }

        return $this->administrableRootContentIds[$rolePrefix];
    }

    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('ROLE_SUPERADMIN');
    }

    public function hasRole($role)
    {
        return $this->get(MemberService::class)->isGranted($role);
    }

    public function isDeveloper()
    {
        return $this->get(MemberService::class)->getUser()->getDevelopper();
    }

    public function canAdmin(Composable $row)
    {
        $canAdmin = false;
        if ($this->get(DbAbstractService::class)->isA($row, 'content')) {
            $canAdmin = $this->canAdminContent($row);
        } elseif ($this->get(DbAbstractService::class)->isA($row, 'content_spec')) {
            /* @var $row \Luxepack\DbBundle\Entity\ContentSpec */
            foreach ($row->getContentHandler()->getContents() as $content) {
                $canAdmin = $canAdmin || $this->canAdmin($content);
            }
        } else {
            $canAdmin = $this->canAdminContent($row->getParent());
        }

        return $canAdmin;
    }

    public function canAdminContent(Content $content)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $contentIds = $this->getAdministrableContentIds();

        return isset($contentIds[$content->getId()]) ? $contentIds[$content->getId()] : false;
    }

    public function canRootComposer(Content $content)
    {
        return $this->getParameter('nyrocms.content.root_composer');
    }

    public function getContentMaxLevel(Content $content)
    {
        return $this->getParameter('nyrocms.content.maxlevel');
    }

    public function canHaveSub(Content $content)
    {
        return $content ? $content->getLevel() < $this->getContentMaxLevel($content) : true;
    }

    public function updateContentUrl(Content $row, $isEdit = false, $child = true, $forceUpdate = false)
    {
        if (!$isEdit || $this->get(NyroCmsService::class)->getDefaultLocale($row) == $row->getTranslatableLocale() || !$this->get(NyroCmsService::class)->disabledLocaleUrls($row->getTranslatableLocale())) {
            $oldUrl = $row->getUrl();

            $prefix = null;
            if ($row->getParent()) {
                $parent = $this->get(DbAbstractService::class)->getContentRepository()->find($row->getParent()->getId());
                $parent->setTranslatableLocale($row->getTranslatableLocale());
                $this->get(nyroDevDbService::class)->refresh($parent);
                $prefix = $parent->getUrl();
            }

            $url = $prefix.'/'.$this->get(NyrodevService::class)->urlify(str_replace(array('+', '&'), array('plus', 'et'), $row->getTitle()));
            $url = str_replace('//', '/', $url);

            $row->setUrl($url);

            if ($forceUpdate || ($row->getUrl() != $oldUrl && $isEdit)) {
                if ($child) {
                    $this->updateContentUrlRec($row->getId(), $oldUrl.'/', $row->getUrl().'/', $row->getTranslatableLocale(), $forceUpdate);
                }
            }
        }
    }

    protected function updateContentUrlRec($parentId, $oldUrl, $newUrl, $locale, $forceUpdate = false)
    {
        $rows = $this->get(DbAbstractService::class)->getContentRepository()->findBy(array('parent' => $parentId));
        foreach ($rows as $row) {
            $row->setTranslatableLocale($locale);
            $this->get(DbAbstractService::class)->refresh($row);
            $old = $row->getUrl();
            $new = str_replace(array($oldUrl, '//'), array($newUrl, '/'), $row->getUrl());
            if ($forceUpdate || $old != $new) {
                $row->setUrl($new);
                $this->updateContentUrlRec($row->getId(), $oldUrl, $newUrl, $locale, $forceUpdate);
            }
        }
    }

    public function getContentStateChoices()
    {
        return array(
            Content::STATE_ACTIVE => $this->trans('admin.state.state_'.Content::STATE_ACTIVE),
            Content::STATE_INVISIBLE => $this->trans('admin.state.state_'.Content::STATE_INVISIBLE),
            Content::STATE_DISABLED => $this->trans('admin.state.state_'.Content::STATE_DISABLED),
        );
    }

    public function getContentSpecStateChoices()
    {
        return array(
            ContentSpec::STATE_ACTIVE => $this->trans('admin.state.state_'.ContentSpec::STATE_ACTIVE),
            ContentSpec::STATE_INVISIBLE => $this->trans('admin.state.state_'.ContentSpec::STATE_INVISIBLE),
            ContentSpec::STATE_DISABLED => $this->trans('admin.state.state_'.ContentSpec::STATE_DISABLED),
        );
    }

    public function getContentsChoiceTypeOptions($maxLevel = null, array $limitRootIds = array())
    {
        if (is_null($maxLevel)) {
            $maxLevel = $this->getParameter('nyrocms.content.maxlevel');
        }
        $contents = array();
        $contentsLevel = array();
        $this->getContentsOptionsChoices($contents, $contentsLevel, null, $maxLevel, 0, $limitRootIds);

        return array(
            'expanded' => true,
            'choices' => $contents,
            'attr' => array(
                'class' => 'contentsList',
            ),
            'choice_attr' => function ($choice, $key) use ($contentsLevel) {
                return array(
                    'class' => 'contentLvl'.$contentsLevel[$key].($choice->getParent() ? ' contentPar'.$choice->getParent()->getId() : ' contentRoot'),
                );
            },
        );
    }

    protected function getContentsOptionsChoices(array &$contents, array &$contentsLevel, $parent, $maxLevel, $curLevel = 0, array $limitRootIds = array())
    {
        foreach ($this->get(DbAbstractService::class)->getContentRepository()->children($parent, true) as $child) {
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

    public function getIcon($name)
    {
        return '<svg class="icon icon-'.$name.'">'.
                    '<use xlink:href="'.$this->assetsHelper->getUrl('bundles/nyrodevnyrocms/images/icons.svg').'#'.$name.'"></use>'.
                '</svg>';
    }
}
