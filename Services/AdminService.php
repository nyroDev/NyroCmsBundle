<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;

class AdminService extends AbstractService {
	
	protected $userTypes;
	public function getUserTypes() {
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
}