<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * ApplicationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApplicationRepository extends EntityRepository
{
    private function getLatestQuery($limit, \DateTime $now)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.show', 's')
            ->where($qb->expr()->orX('a.deadlineDate > :current_date',
                $qb->expr()->andX('a.deadlineDate = :current_date', 'a.deadlineTime >= :current_time')))
            ->andWhere($qb->expr()->orX(
                'a.show IS NULL',
                $qb->expr()->andX('s.authorised_by is not null')
            ))
            ->orderBy('a.deadlineDate', 'DESC')
            ->addOrderBy('a.deadlineTime', 'DESC')
            ->setParameter('current_date', $now, \Doctrine\DBAL\Types\Type::DATE)
            ->setParameter('current_time', $now, \Doctrine\DBAL\Types\Type::TIME);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function findOneByShowSlug($slug, \DateTime $now)
    {
        $qb = $this->createQueryBuilder('a');
        return $qb
            ->join('a.show', 's')
            ->where('s.slug = :slug')
            ->andWhere($qb->expr()->orX('a.deadlineDate > :current_date',
                $qb->expr()->andX('a.deadlineDate = :current_date', 'a.deadlineTime >= :current_time')))
            ->andWhere('s.slug = :slug')
            ->andWhere('s.authorised_by is not null')
            ->setParameter('slug', $slug)

            ->setParameter('current_date', $now, \Doctrine\DBAL\Types\Type::DATE)
            ->setParameter('current_time', $now, \Doctrine\DBAL\Types\Type::TIME)
            ->setMaxResults(1)
        ->getQuery()->getOneOrNullResult();
    }

    public function findLatest($limit, \DateTime $now)
    {
        return $this->getLatestQuery($limit, $now)->getQuery()->getResult();
    }

    public function findLatestBySociety(Society $society, $limit, \DateTime $now)
    {
        $qb = $this->getLatestQuery($limit, $now);
        $qb->andWhere(
                    $qb->expr()->orX('s.society = :society', 'a.society = :society')
            )->setParameter('society', $society);

        return $qb->getQuery()->getResult();
    }

    public function findLatestByVenue(Venue $venue, $limit, \DateTime $now)
    {
        $qb = $this->getLatestQuery($limit, $now);

        return $qb->leftJoin('s.venue', 'v')->andWhere('v = :venue')->setParameter('venue', $venue)
            ->getQuery()->getResult();
    }

    public function findOneBySlug($slug, \DateTime $now)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb->where($qb->expr()->orX('a.deadlineDate > :current_date',
                $qb->expr()->andX('a.deadlineDate = :current_date', 'a.deadlineTime >= :current_time')))
            ->leftJoin('a.show', 's')
            ->leftJoin('a.society', 'o')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX('s.id IS NOT NULL', 's.slug = :slug', 's.authorised_by is not null'),
                $qb->expr()->andX('o.id IS NOT NULL', 'o.slug = :slug')
            ))
            ->setParameter('slug', $slug)
            ->setParameter('current_date', $now, \Doctrine\DBAL\Types\Type::DATE)
            ->setParameter('current_time', $now, \Doctrine\DBAL\Types\Type::TIME)
            ->getQuery()->getOneOrNullResult();
    }
}
