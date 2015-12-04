<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Model\Composable;

class AdminService extends AbstractService {
	
	protected $userTypes;
	public function getUserTypeChoices() {
		if (is_null($this->userTypes)) {
			$this->userTypes = array();
			foreach($this->getParameter('nyrocms.user_types') as $type) {
				$identTr = 'admin.userTypes.'.$type;
				$tr = $this->trans($identTr);
				if (!$tr || $tr == $identTr)
					$tr = ucfirst($type);
				$this->userTypes[$type] = $tr;
			}
		}
		return $this->userTypes;
	}
	
	protected $userRoles;
	public function getUserRoles() {
		if (is_null($this->userRoles)) {
			$this->userRoles = array();
			foreach($this->get('nyrocms_db')->getRepository('user_role')->findAll() as $tmp)
				$this->userRoles[$tmp->getId()] = $tmp;
		}
		return $this->userRoles;
	}
	
	protected $contentParentId;
	public function setContentParentId($id) {
		$this->contentParentId = $id;
	}
	public function getContentParentId() {
		return $this->contentParentId;
	}
	
	protected $administrableContentIds;
	
	public function getAdministrableContentIds() {
		if (is_null($this->administrableContentIds)) {
			$this->administrableContentIds = array();
			if ($this->get('nyrodev_member')->isLogged()) {
				$user = $this->get('nyrodev_member')->getUser();
				/* @var $user \NyroDev\NyroCmsBundle\Model\User */
				
				$repoContent = $this->get('nyrocms_db')->getContentRepository();
				foreach($user->getUserRoles() as $userRole) {
					/* @var $userRole \NyroDev\NyroCmsBundle\Model\UserRole */
					foreach($userRole->getContents() as $content) {
						if (!isset($this->administrableContentIds[$content->getId()])) {
							$this->administrableContentIds[$content->getId()] = 'root';
							foreach($repoContent->children($content) as $c) {
								$this->administrableContentIds[$c->getId()] = true;
							}
						}
					}
				}
			}
		}
		return $this->administrableContentIds;
	}
	
	public function isAdmin() {
		return $this->hasRole('ROLE_ADMIN');
	}
	
	public function isSuperAdmin() {
		return $this->hasRole('ROLE_SUPERADMIN');
	}
	
	public function hasRole($role) {
		return $this->get('nyrodev_member')->isGranted($role);
	}
	
	public function isDeveloper() {
		return $this->get('nyrodev_member')->getUser()->getDevelopper();
	}
	
	public function canAdmin(Composable $row) {
		$canAdmin = false;
		if ($this->get('nyrocms_db')->isA($row, 'content')) {
			$canAdmin = $this->canAdminContent($row);
		} else if ($this->get('nyrocms_db')->isA($row, 'content_spec')) {
			/* @var $row \Luxepack\DbBundle\Entity\ContentSpec */
			foreach($row->getContentHandler()->getContents() as $content)
				$canAdmin = $canAdmin || $this->get('nyrocms_admin')->canAdmin($content);
		}
		return $canAdmin;
	}
	
	public function canAdminContent(Content $content) {
		if ($this->isSuperAdmin())
			return true;
		$this->getAdministrableContentIds();
		return isset($this->administrableContentIds[$content->getId()]) ? $this->administrableContentIds[$content->getId()] : false;
	}
	
	public function canHaveSub(Content $content) {
		return $content ? $content->getLevel() < $this->getParameter('nyroCms.content.maxlevel') : true;
	}
	
	
	public function updateContentUrl(Content $row, $isEdit = false, $child = true, $forceUpdate = false) {
		$oldUrl = $row->getUrl();
		$url = ($row->getParent() ? $row->getParent()->getUrl() : null).'/'.$this->get('nyrodev')->urlify(str_replace(array('+', '&'), array('plus', 'et'), $row->getTitle()));
		$url = str_replace('//', '/', $url);
		$row->setUrl($url);
		
		if ($forceUpdate || ($row->getUrl() != $oldUrl && $isEdit)) {
			if ($child)
				$this->updateContentUrlRec($row->getId(), $oldUrl.'/', $row->getUrl().'/', $row->getTranslatableLocale(), $forceUpdate);
		}
	}
	
	protected function updateContentUrlRec($parentId, $oldUrl, $newUrl, $locale, $forceUpdate = false) {
		$rows = $this->get('nyrocms_db')->getContentRepository()->findBy(array('parent'=>$parentId));
		foreach($rows as $row) {
			$row->setTranslatableLocale($locale);
			$this->get('nyrocms_db')->refresh($row);
			$old = $row->getUrl();
			$new = str_replace(array($oldUrl, '//'), array($newUrl, '/'), $row->getUrl());
			if ($forceUpdate || $old != $new) {
				$row->setUrl($new);
				$this->updateContentUrlRec($row->getId(), $oldUrl, $newUrl, $locale, $forceUpdate);
			}
		}
	}
	
	public function getContentStateChoices() {
		return array(
			Content::STATE_ACTIVE=>$this->trans('admin.state.state_'.Content::STATE_ACTIVE),
			Content::STATE_INVISIBLE=>$this->trans('admin.state.state_'.Content::STATE_INVISIBLE),
			Content::STATE_DISABLED=>$this->trans('admin.state.state_'.Content::STATE_DISABLED),
		);
	}
	
	public function getContentSpecStateChoices() {
		return array(
			ContentSpec::STATE_ACTIVE=>$this->trans('admin.state.state_'.ContentSpec::STATE_ACTIVE),
			ContentSpec::STATE_INVISIBLE=>$this->trans('admin.state.state_'.ContentSpec::STATE_INVISIBLE),
			ContentSpec::STATE_DISABLED=>$this->trans('admin.state.state_'.ContentSpec::STATE_DISABLED),
		);
	}
	
	public function getIcon($name) {
		return '<svg class="icon icon-'.$name.'">'.
					'<use xlink:href="'.$this->get('templating.helper.assets')->getUrl('bundles/nyrodevnyrocms/images/icons.svg').'#'.$name.'"></use>'.
				'</svg>';
	}

}