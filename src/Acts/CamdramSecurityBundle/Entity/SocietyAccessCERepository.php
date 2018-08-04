<?php

namespace Acts\CamdramSecurityBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Acts\CamdramBundle\Entity\Show;
use Acts\CamdramSecurityBundle\Security\OwnableInterface;

class SocietyAccessCERepository extends EntityRepository
{
    public function aceExists(Society $soc, OwnableInterface $entity)
    {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->select('COUNT(e.id) AS c')
                ->where('e.societyId = :socid')
                ->andWhere('e.entityId = :entityId')
                ->andWhere('e.revokedBy IS NULL')
                ->andWhere('e.type = :type')
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
                ->setParameter('socid', $soc->getId())
        ;

        $res = $query->getQuery()->getOneOrNullResult();

        return $res['c'] > 0;
    }

    /**
     * Find an ACE for a registered society, which has not been revoked.
     */
    public function findLiveAce($socId, OwnableInterface $entity)
    {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->where('e.societyId = :societyId')
                ->andWhere('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->setParameter('societyId', $socId)
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find an ACE for a given show and society name, not revoked or pointing
     * at a registered society.
     */
    public function findLiveNameAce($socName, OwnableInterface $entity)
    {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->where('e.society IS NULL')
                ->andWhere('e.societyName = :socName')
                ->andWhere('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->setParameter('socName', $socName)
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find all societies that own an entity.
     * This does not include unregistered societies.
     *
     * @return an array of Society objects.
     */
    public function findOwningSocs(OwnableInterface $entity) {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->select('e.societyId')
                ->where('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->andWhere('e.societyId IS NOT NULL')
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
        ;
        $result = $query->getQuery()->getResult();
        $socs_repo = $this->getEntityManager()->getRepository('ActsCamdramBundle:Society');
        foreach ($result as &$soc) { // TODO put this all in DQL
            $soc = $socs_repo->findOneById($soc['societyId']);
        }

        return $result;
    }

    /**
     * Find all societies that are named for an entity.
     * This *does* include unregistered societies; ordered by display_order.
     *
     * @return an array of SocietyAccessCE objects.
     */
    public function findAllLinkedSocs(OwnableInterface $entity) {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->select('e')
                ->where('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->orderBy('e.display_order', 'ASC')
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
        ;
        $result = $query->getQuery()->getResult();

        return $result;
    }
}
