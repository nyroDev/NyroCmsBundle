<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\NoResultException;

class UserRepository extends EntityRepository implements UserProviderInterface {
	
	public function loadUserByUsername($username) {
        $q = $this
            ->createQueryBuilder('m')
            ->where('m.email LIKE :username')
				->setParameter('username', $username)
			->andWhere('m.valid = 1')
			->andWhere('(m.validStart IS NULL OR m.validStart <= :now)')
			->andWhere('(m.validEnd IS NULL OR m.validEnd >= :now)')
				->setParameter('now', new \DateTime())
            ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active User NyroDevNyroCmsBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
	}

	public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user) {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
	}

	public function supportsClass($class) {
        return $this->getEntityName() === $class;
	}

}