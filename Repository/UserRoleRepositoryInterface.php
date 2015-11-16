<?php

namespace NyroDev\NyroCmsBundle\Repository;

interface UserRoleRepositoryInterface {

	public function getQueryBuilder($isDev = false);
	
	public function getFormQueryBuilder();
	
}