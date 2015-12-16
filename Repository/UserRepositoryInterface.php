<?php

namespace NyroDev\NyroCmsBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

interface UserRepositoryInterface extends UserLoaderInterface {

	/**
	 * Should return users that are:
	 * - valid
	 * - with password real value "dummy"
	 * - with validStart of today OR passwordKeyEnd with today value
	 */
	public function getForWelcomeEmails();
	
}