<?php

namespace Acts\CamdramSecurityBundle\DataFixtures;

use Acts\CamdramSecurityBundle\Entity\AccessControlEntry;
use Acts\CamdramSecurityBundle\Entity\SocietyAccessCE;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Acts\CamdramSecurityBundle\Entity\User;
use Acts\CamdramBundle\DataFixtures\ShowFixtures;

class AccessControlEntryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        //Make the admin user an admin
        $e = new AccessControlEntry();
        $e->setUser($this->getReference('adminuser'));
        $e->setGrantedBy($this->getReference('testuser1'));
        $e->setEntityId('-2');
        $e->setCreatedAt(new \DateTime('2001-01-01'));
        $e->setType('security');
        $manager->persist($e);

        $shows = $manager->getRepository('ActsCamdramBundle:Show')->findAll();
        $societies = $manager->getRepository('ActsCamdramBundle:Society')->findAll();
        foreach ($shows as $show) {
            // Make user2 owner of all shows.
            $e = new AccessControlEntry();
            $e->setUser($this->getReference('testuser2'));
            $e->setGrantedBy($this->getReference('adminuser'));
            $e->setEntityId($show->getId());
            $e->setCreatedAt(new \DateTime('2001-01-01'));
            $e->setType('show');
            $manager->persist($e);

            // Make random societies own random shows.
            // This is only in the loop so it runs a resaonable number of times,
            // it's not using $show.
            $ace = new SocietyAccessCE();
            $ace->setSociety($societies[array_rand($societies)]);
            $ace->setType('show');
            $ace->setEntityId($shows[array_rand($shows)]->getId());
            $ace->setGrantedBy($this->getReference('testuser2'));
            $ace->setCreatedAt(new \DateTime());
            $ace->setDisplayOrder(0);
            $manager->persist($ace);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ShowFixtures::class
        ];
    }
}
