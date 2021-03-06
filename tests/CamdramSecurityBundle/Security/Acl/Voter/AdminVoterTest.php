<?php

namespace Camdram\Tests\CamdramSecurityBundle\Security\Acl\Voter;

use Acts\CamdramBundle\Entity\Show;
use Acts\CamdramBundle\Entity\Society;
use Acts\CamdramBundle\Entity\Venue;
use Acts\CamdramSecurityBundle\Security\Acl\Voter\AdminVoter;
use Symfony\Component\HttpFoundation\Request;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use PHPUnit\Framework\TestCase;

class AdminVoterTest extends TestCase
{
    /**
     * @var \Acts\CamdramSecurityBundle\Security\Acl\Voter\AdminVoter
     */
    private $voter;

    public function setUp(): void
    {
        $this->voter = new AdminVoter();
    }

    public function testAdminShow()
    {
        $token = new OAuthToken('', ['ROLE_ADMIN']);
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, Show::class, ['CREATE']));
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, new Show, ['EDIT']));
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, new Show, ['DELETE']));
    }

    public function testAdminSociety()
    {
        $token = new OAuthToken('', ['ROLE_ADMIN']);
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, Society::class, ['CREATE']));
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, new Society, ['EDIT']));
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, new Society, ['DELETE']));
    }

    public function testAdminVenue()
    {
        $token = new OAuthToken('', ['ROLE_ADMIN']);
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, Venue::class, ['CREATE']));
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, new Venue, ['EDIT']));
        $this->assertEquals(AdminVoter::ACCESS_GRANTED, $this->voter->vote($token, new Venue, ['DELETE']));
    }

    public function testNonAdmin()
    {
        $token = new OAuthToken('', []);
        $this->assertEquals(AdminVoter::ACCESS_DENIED, $this->voter->vote($token, Show::class, ['CREATE']));
        $this->assertEquals(AdminVoter::ACCESS_DENIED, $this->voter->vote($token, new Show, ['EDIT']));
        $this->assertEquals(AdminVoter::ACCESS_DENIED, $this->voter->vote($token, new Show, ['DELETE']));
    }

    public function testNonCamdramObject()
    {
        $token = new OAuthToken('', ['ROLE_ADMIN']);
        $request = new Request();
        $attributes = array('EDIT');
        $this->assertEquals(AdminVoter::ACCESS_ABSTAIN, $this->voter->vote($token, $request, $attributes));
    }
}
