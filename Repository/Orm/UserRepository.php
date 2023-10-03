<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use NyroDev\NyroCmsBundle\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function loadUserByUsername(string $username): ?UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier(string $username): ?UserInterface
    {
        return $this
            ->createQueryBuilder('m')
            ->where('m.email LIKE :username')
                ->setParameter('username', $username)
            ->andWhere('m.valid = 1')
            ->andWhere('(m.validStart IS NULL OR m.validStart <= :now)')
            ->andWhere('(m.validEnd IS NULL OR m.validEnd >= :now)')
                ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getForWelcomeEmails()
    {
        return $this->createQueryBuilder('u')
                        ->andWhere('u.valid = 1')
                        ->andWhere('u.password = :password')
                            ->setParameter('password', 'dummy')
                        ->andWhere('(u.validStart LIKE :today OR u.passwordKeyEnd LIKE :today)')
                            ->setParameter('today', date('Y-m-d').'%')
                        ->getQuery()
                        ->execute();
    }
}
