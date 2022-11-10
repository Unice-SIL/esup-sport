<?php

/*
 * classe - LoginListener
 *
 * Service surchargant certaines méthodes de FoSUser
*/

namespace App\Service\Listener\Securite\Utilisateur;

use DateTime;
use App\Entity\Uca\LogConnexion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
        $user->setLastLogin(new DateTime());

        $logConnexion = new LogConnexion($user);

        // Persist the data to database.
        $this->em->persist($logConnexion);
        $this->em->flush();
    }
}
