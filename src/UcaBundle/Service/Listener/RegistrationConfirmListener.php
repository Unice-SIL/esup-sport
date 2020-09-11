<?php

/*
 * classe - RegistrationConfirmListener
 *
 * Service écoutant l'enregistrement d'uitlisateur (changement de statut)
*/

namespace UcaBundle\Service\Listener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use UcaBundle\Entity\StatutUtilisateur;
use UcaBundle\Service\Common\FlashBag;

class RegistrationConfirmListener implements EventSubscriberInterface
{
    private $em;
    private $router;
    private $flashMessage;

    public function __construct(EntityManagerInterface $em, Router $router, FlashBag $flashMessage)
    {
        $this->em = $em;
        $this->router = $router;
        $this->flashMessage = $flashMessage;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
        ];
    }

    public function onRegistrationConfirm(GetResponseUserEvent $event)
    {
        $statut = $this->em->getReference(StatutUtilisateur::class, 1);
        $event->getUser()->setStatut($statut);
        $this->em->persist($event->getUser());

        $this->flashMessage->addMessageFlashBag('registration.confirm.success', 'success');

        $url = $this->router->generate('UcaWeb_CGV');
        $event->setResponse(new RedirectResponse($url));
    }
}
