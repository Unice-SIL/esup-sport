<?php

namespace UcaBundle\Service\Listener\Securite\Utilisateur;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use UcaBundle\Entity\LogConnexion;

class LoginListener
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Get the User entity.
        $user = $event->getAuthenticationToken()->getUser();

        $logConnexion = new LogConnexion($user);

        // Persist the data to database.
        $this->em->persist($logConnexion);
        $this->em->flush();
    }
}
