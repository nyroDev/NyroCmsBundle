<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use NyroDev\NyroCmsBundle\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function loadUserByUsername($username)
    {
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
