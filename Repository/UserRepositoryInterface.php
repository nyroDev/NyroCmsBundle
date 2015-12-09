<?php

namespace NyroDev\NyroCmsBundle\Repository;

use Symfony\Component\Security\Core\User\UserProviderInterface;

interface UserRepositoryInterface extends UserProviderInterface {

	/**
	 * Should return users that are:
	 * - valid
	 * - with password real value "dummy"
	 * - with validStart of today OR passwordKeyEnd with today value
	 */
	public function getForWelcomeEmails();
	
}