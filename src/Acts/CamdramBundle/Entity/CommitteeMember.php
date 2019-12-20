<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CommitteeMember
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="acts_committee_member")
 * @ORM\Entity()
 */
class CommitteeMember
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="term_start", type="date")
     */
    private $termStart;

    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="term_end", type="date")
     */
    private $termEnd;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="role", type="string")
     */
    private $role;

    /**
     * Contact information etc.; to be passed through the detectLinks Twig
     * filter.
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="comment", type="string", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Society", inversedBy="committee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="soc_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     * @Gedmo\Versioned
     */
    private $society;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="societyRoles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pid", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     * @Gedmo\Versioned
     */
    private $person;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="`order`", type="integer")
     */
    private $order;

    public function getId()
    {
        return $this->id;
    }

    public function setTermStart(\DateTime $termStart): self
    {
        $this->termStart = $termStart;
        return $this;
    }
    public function getTermStart(): ?\DateTime
    {
        return $this->termStart;
    }

    public function setTermEnd(\DateTime $termEnd): self
    {
        $this->termEnd = $termEnd;
        return $this;
    }
    public function getTermEnd(): ?\DateTime
    {
        return $this->termEnd;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }
    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }
    public function setPerson(Person $person): self
    {
        $this->person = $person;
        return $this;
    }

    public function getSociety(): ?Society
    {
        return $this->society;
    }
    public function setSociety(Society $society): self
    {
        $this->society = $society;
        return $this;
    }
}
