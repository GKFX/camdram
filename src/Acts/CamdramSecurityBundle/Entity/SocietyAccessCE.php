<?php

namespace Acts\CamdramSecurityBundle\Entity;

use Acts\CamdramBundle\Entity\Society;
use Doctrine\ORM\Mapping as ORM;

/**
 * Society access control entry
 * Links societies to entities, so that the society admins can administrate the entity.
 * Can also identify societies by name not ID, in which case they don't get access.
 *
 * @ORM\Table(name="acts_society_access")
 * @ORM\Entity(repositoryClass="SocietyAccessCERepository")
 */
class SocietyAccessCE
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="rid", type="integer", nullable=false)
     */
    private $entityId;

    /**
     * @var String
     *
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;

    /**
     * Nullable because it might be an unregistered society.
     *
     * @var Society
     *
     * @ORM\ManyToOne(targetEntity="\Acts\CamdramBundle\Entity\Society")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="socid", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * })
     */
    private $society;

    /**
     * @var int
     *
     * @ORM\Column(name="socid", type="integer", nullable=true)
     */
    private $societyId;

    /**
     * Society name, if it's not registered in the DB already.
     * @var String
     *
     * @ORM\Column(name="socname", type="string", length=255, nullable=true)
     */
    private $societyName;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ace_grants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="issuerid", referencedColumnName="id")
     * })
     */
    private $grantedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationdate", type="date", nullable=false)
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="revokeid", referencedColumnName="id", nullable=true)
     * })
     */
    private $revokedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="revokedate", type="date", nullable=true)
     */
    private $revokedAt;

    /**
     * @var int
     * The order to display this society in on screen
     *
     * @ORM\Column(name="display_order", type="integer", nullable=false)
     */
    private $display_order;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return SocietyAccessCE
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set revoked_at
     *
     * @param \DateTime $revokedAt
     *
     * @return SocietyAccessCE
     */
    public function setRevokedAt($revokedAt)
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * Get revoked_at
     *
     * @return \DateTime
     */
    public function getRevokedAt()
    {
        return $this->revokedAt;
    }

    /**
     * Set society
     *
     * @param Society $society
     *
     * @return SocietyAccessCE
     */
    public function setSociety(Society $society)
    {
        $this->society = $society;

        return $this;
    }

    /**
     * Get society
     *
     * @return Society
     */
    public function getSociety()
    {
        return $this->society;
    }

    /**
     * Set granted_by
     *
     * @param User $grantedBy
     *
     * @return SocietyAccessCE
     */
    public function setGrantedBy(User $grantedBy = null)
    {
        $this->grantedBy = $grantedBy;

        return $this;
    }

    /**
     * Get granted_by
     *
     * @return User
     */
    public function getGrantedBy()
    {
        return $this->grantedBy;
    }

    /**
     * Set revoker
     *
     * @param User $revoker
     *
     * @return SocietyAccessCE
     */
    public function setRevokedBy(User $revoker = null)
    {
        $this->revokedBy = $revoker;

        return $this;
    }

    /**
     * Get revoker
     *
     * @return User
     */
    public function getRevokedBy()
    {
        return $this->revokedBy;
    }

    /**
     * Set societyId
     *
     * @param int $societyId
     *
     * @return SocietyAccessCE
     */
    public function setSocietyId($societyId)
    {
        $this->societyId = $societyId;

        return $this;
    }

    /**
     * Get societyd
     *
     * @return int
     */
    public function getSocietyId()
    {
        return $this->societyId;
    }

    /**
     * Get societyName
     *
     * @return int
     */
    public function getSocietyName()
    {
        return $this->societyName;
    }

    /**
     * Set societyName
     *
     * @param int $societyName
     *
     * @return SocietyAccessCE
     */
    public function setSocietyName($societyName)
    {
        $this->societyName = $societyName;

        return $this;
    }

    /**
     * Set type
     *
     * @param String $type
     *
     * @return SocietyAccessCE
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set entityId
     *
     * @param int $entityId
     *
     * @return SocietyAccessCE
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return int 
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set display_order
     *
     * @param int $display_order
     *
     * @return SocietyAccessCE
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = $display_order;

        return $this;
    }

    /**
     * Get display_order
     *
     * @return int 
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }
}
