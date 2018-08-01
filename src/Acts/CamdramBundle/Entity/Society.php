<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Acts\CamdramApiBundle\Configuration\Annotation as Api;

/**
 * Society
 *
 * @ORM\Entity(repositoryClass="Acts\CamdramBundle\Entity\SocietyRepository")
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\XmlRoot("society")
 */
class Society extends Organisation
{
    private $entity_type = 'society';

    public function getEntityType()
    {
        return $this->entity_type;
    }

    public function getIndexDate()
    {
        return null;
    }

    public function getOrganisationType()
    {
        return 'society';
    }
}
