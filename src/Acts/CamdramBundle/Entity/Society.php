<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Society
 *
 * @ORM\Entity(repositoryClass="Acts\CamdramBundle\Entity\SocietyRepository")
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\XmlRoot("society")
 * @Hateoas\Relation(
 *      "events",
 *      href = @Hateoas\Route(
 *          "get_venue_events",
 *          parameters={"identifier" = "expr(object.getSlug())"},
 *          absolute=true
 *      )
 * )
 * @Hateoas\Relation(
 *      "shows",
 *      href = @Hateoas\Route(
 *          "get_venue_shows",
 *          parameters={"identifier" = "expr(object.getSlug())"},
 *          absolute=true
 *      )
 * )
 */
class Society extends Organisation
{

    /**
     * @ORM\OneToMany(targetEntity="Show", mappedBy="society")
     */
    private $shows;

    /**
     */
    protected $entity_type = 'society';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->shows = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add shows
     *
     * @param \Acts\CamdramBundle\Entity\Show $shows
     * @return Society
     */
    public function addShow(\Acts\CamdramBundle\Entity\Show $shows)
    {
        $this->shows[] = $shows;

        return $this;
    }

    /**
     * Remove shows
     *
     * @param \Acts\CamdramBundle\Entity\Show $shows
     */
    public function removeShow(\Acts\CamdramBundle\Entity\Show $shows)
    {
        $this->shows->removeElement($shows);
    }

    /**
     * Get shows
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShows()
    {
        return $this->shows;
    }

    public function getEntityType()
    {
        return $this->entity_type;
    }

    public function getIndexDate()
    {
        return null;
    }
}
