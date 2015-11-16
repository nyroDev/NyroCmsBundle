<?php

namespace NyroDev\NyroCmsBundle\Repository;

interface UserRoleRepositoryInterface {

	public function getAdminListQueryBuilder($isDev = false);
	
	public function getFormQueryBuilder();
	
}