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
     * Find an ACEs that has not been revoked.
     */
    public function findLiveAce(Society $soc, OwnableInterface $entity)
    {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->where('e.society = :society')
                ->andWhere('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->setParameter('society', $soc)
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
        $query = $qb->select('e.societyId') // TODO make distinct. Also get the actual socs, etc.
                ->where('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->andWhere('e.societyId IS NOT NULL')
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
        ;
        $result = $query->getQuery()->getResult();
        error_log(json_encode($result));
        $socs_repo = $this->getEntityManager()->getRepository('ActsCamdramBundle:Society');
        foreach ($result as &$soc) { // TODO put this all in DQL
            $soc = $socs_repo->findOneById($soc['societyId']);
        }

        return $result;
    }

    /**
     * Find all societies that are named for an entity.
     * This *does* include unregistered societies.
     *
     * @return an array of SocietyAccessCE objects.
     */
    public function findAllLinkedSocs(OwnableInterface $entity) {
        $qb = $this->createQueryBuilder('e');
        $query = $qb->select('e') // TODO make distinct. Also get the actual socs, etc.
                ->where('e.entityId = :entityId')
                ->andWhere('e.type = :type')
                ->andWhere('e.revokedBy IS NULL')
                ->setParameter('entityId', $entity->getId())
                ->setParameter('type', $entity->getAceType())
        ;
        $result = $query->getQuery()->getResult();

        return $result;
    }
}
