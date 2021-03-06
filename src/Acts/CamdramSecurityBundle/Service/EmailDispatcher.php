<?php

namespace Acts\CamdramSecurityBundle\Service;

use Acts\CamdramBundle\Entity\Event;
use Acts\CamdramBundle\Entity\Show;
use Acts\CamdramBundle\Entity\Society;
use Acts\CamdramBundle\Entity\Venue;
use Acts\CamdramSecurityBundle\Entity\AccessControlEntry;
use Acts\CamdramSecurityBundle\Entity\PendingAccess;
use Acts\CamdramSecurityBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class for constructing and sending emails. Emails are typically sent as a
 * result of an event occurring, such as a user changing their email address.
 */
class EmailDispatcher
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var \Twig\Environment */
    private $twig;
    /** @var string */
    private $from_address;

    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, \Twig\Environment $twig, string $adminEmail)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->from_address = $adminEmail;
    }

    public function sendRegistrationEmail(User $user, string $token): void
    {
        $message = (new \Swift_Message('Welcome to Camdram'))
            ->setFrom($this->from_address)
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    'email/create_account.txt.twig',
                    array(
                        'user' => $user,
                        'email_confirmation_token' => $token
                    )
                )
            )
        ;
        $this->mailer->send($message);
    }

    public function resendEmailVerifyEmail(User $user, string $token): void
    {
        $message = (new \Swift_Message('Verify your email address'))
            ->setFrom($this->from_address)
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    'email/resend_email_verification.txt.twig',
                    array(
                        'user'                     => $user,
                        'email_confirmation_token' => $token
                    )
                )
            )
        ;
        $this->mailer->send($message);
    }

    public function sendEmailVerifyEmail(User $user, string $token): void
    {
        $message = (new \Swift_Message('Verify your new email address'))
            ->setFrom($this->from_address)
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    'email/change_email.txt.twig',
                    array(
                        'user' => $user,
                        'email_confirmation_token' => $token
                    )
                )
            )
        ;
        $this->mailer->send($message);
    }

    /**
     * Send an email informing someone that they've been granted access to a
     * resource (show, society, or venue).
     */
    public function sendAceEmail(AccessControlEntry $ace): void
    {
        $message = (new \Swift_Message)
            ->setFrom($this->from_address)
            ->setTo($ace->getUser()->getEmail());

        switch ($ace->getType()) {
            case 'event':
                $entity = $this->em->getRepository(Event::class)->findOneById($ace->getEntityId());
                break;
            case 'show':
                $entity = $this->em->getRepository(Show::class)->findOneById($ace->getEntityId());
                break;
            case 'society':
                $entity = $this->em->getRepository(Society::class)->findOneById($ace->getEntityId());
                break;
            case 'venue':
                $entity = $this->em->getRepository(Venue::class)->findOneById($ace->getEntityId());
                break;
            default:
                throw new \LogicException("Cannot send ACE email for {$ace->getType()}");
        }

        $message->setSubject('Access to '.$entity->getName().' on Camdram granted')
            ->setBody(
                $this->twig->render(
                    'email/ace.txt.twig',
                    array(
                        'is_pending' => false,
                        'ace' => $ace,
                        'entity' => $entity
                    )
                )
            );
        $this->mailer->send($message);
    }

    /**
     * Send an email informing someone that they've been granted access to a
     * resource (show, society, or venue).
     */
    public function sendPendingAceEmail(PendingAccess $ace): void
    {
        $message = (new \Swift_Message)
            ->setFrom($this->from_address)
            ->setTo($ace->getEmail());
        /* Get the resource and pass it to the template. */
        switch ($ace->getType()) {
            case 'event':
                $entity = $this->em->getRepository(Event::class)->findOneById($ace->getRid());
                break;
            case 'show':
                $entity = $this->em->getRepository(Show::class)->findOneById($ace->getRid());
                break;
            case 'society':
                $entity = $this->em->getRepository(Society::class)->findOneById($ace->getRid());
                break;
            case 'venue':
                $entity = $this->em->getRepository(Venue::class)->findOneById($ace->getRid());
                break;
            default:
                throw new \LogicException("Cannot send ACE email for {$ace->getType()}");
        }

        $message->setSubject('Access to '.$entity->getName().' on Camdram granted')
            ->setBody(
                $this->twig->render(
                    'email/ace.txt.twig',
                    array(
                        'is_pending' => true,
                        'ace' => $ace,
                        'entity' => $entity
                    )
                )
            );
        $this->mailer->send($message);
    }

    /**
     * Request administrator privileges for a show
     */
    public function sendShowAdminReqEmail(AccessControlEntry $ace): void
    {
        $show = $this->em->getRepository(Show::class)->findOneById($ace->getEntityId());
        $owners = $this->em->getRepository(User::class)
                    ->getEntityOwners($show);
        $emails = array();
        foreach ($owners as $user) {
            $emails[$user->getEmail()] = $user->getName();
        }

        $message = (new \Swift_Message('Show access request on Camdram: '.$show->getName()))
            ->setFrom($this->from_address)
            ->setTo($emails)
            ->setBody(
                $this->twig->render(
                    'email/show_access_requested.txt.twig',
                    array(
                        'ace' => $ace,
                        'show' => $show
                    )
                )
            )
        ;
        $this->mailer->send($message);
    }
}
