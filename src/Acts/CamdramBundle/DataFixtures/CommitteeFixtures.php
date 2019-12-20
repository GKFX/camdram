<?php

namespace Acts\CamdramBundle\DataFixtures;

use Acts\CamdramBundle\Entity\CommitteeMember;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommitteeFixtures extends Fixture implements DependentFixtureInterface
{
    private $committeeRoles = [
        "President",
        "Junior Treasurer",
        "Secretary",
        "Technical Director",
        "Technicians’ Rep",
        "Stage Managers’ Rep",
        "Publicist",
        "Designers’ Rep",
        "Directors’ Rep",
        "Producers’ Rep",
        "Actors’ Reps",
        "Membership Secretary",
        "Social and Outreach Secretary",
        "Webmaster"
    ];

    private $person_repo;
    private $people_ids;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->person_repo = $manager->getRepository('ActsCamdramBundle:Person');
        $this->people_ids = array_map(
            function ($val) {
                return $val['id'];
            },
             $this->person_repo->createQueryBuilder('p')->select('p.id')->getQuery()->getArrayResult()
        );

        mt_srand(microtime(true));
        $societies = $manager->getRepository('ActsCamdramBundle:Society')
            ->createQueryBuilder('s')->select('s')->getQuery()->getResult();

        foreach ($societies as $society) {
            $handover = new \DateTime('10 March');
            $handover->add(new \DateInterval('P'.mt_rand(0,10).'D'));
            $year = new \DateInterval('P1Y');
            $past = (clone $handover)->sub($year);
            $future = (clone $handover)->add($year);
            $i = 0;
            foreach ($this->committeeRoles as $role) {
                if (mt_rand(0,10) > 3) {
                    $member = new CommitteeMember();
                    $member->setRole($role);
                    $member->setTermStart($past);
                    $member->setTermEnd($handover);
                    $member->setSociety($society);
                    $member->setPerson($this->getRandomPerson());
                    $member->setOrder($i);
                    $manager->persist($member);
                }
                if (mt_rand(0,10) > 3) {
                    $member = new CommitteeMember();
                    $member->setRole($role);
                    $member->setTermStart($handover);
                    $member->setTermEnd($future);
                    $member->setSociety($society);
                    $member->setPerson($this->getRandomPerson());
                    $member->setOrder($i);
                    if (mt_rand(0,10) > 3) {
                        $member->setComment(preg_replace('/[^a-z]/', '', strtolower($role)).'@example.org');
                    }
                    $manager->persist($member);
                }
                $i++;
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [PeopleFixtures::class, SocietyFixtures::class];
    }

    private function getRandomPerson()
    {
        return $this->person_repo->findOneById($this->people_ids[mt_rand(0, count($this->people_ids) - 1)]);
    }
}
