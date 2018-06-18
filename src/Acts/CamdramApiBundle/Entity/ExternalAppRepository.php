<?php

namespace Acts\CamdramApiBundle\Entity;

use Acts\CamdramSecurityBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * ExternalAppRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExternalAppRepository extends EntityRepository
{
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->orderBy('a.name')
            ->setParameter('user', $user)
            ->getQuery()->getResult();
    }

    public function getByUserAndId(User $user, $id)
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.randomId = :id')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()->getOneOrNullResult();
    }
}
