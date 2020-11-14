<?php

namespace Acts\CamdramApiBundle\Entity;

use Acts\CamdramSecurityBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Query\Expr;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * AuthorizationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * @extends EntityRepository<Authorization>
 */
class AuthorizationRepository extends EntityRepository
{
    public function findOne(UserInterface $user, ClientInterface $app): ?Authorization
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.client = :app')
            ->setParameter('user', $user)
            ->setParameter('app', $app)
            ->getQuery()->getOneOrNullResult();
    }

    public function findOneByClientId(UserInterface $user, $clientId): ?Authorization
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.client', 'c')
            ->where('c.id = :clientId')
            ->andWhere('a.user = :user')
            ->setParameter('clientId', $clientId)
            ->setParameter('user', $user)
            ->getQuery()->getOneOrNullResult();
    }
}
