<?php

namespace UserBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UcaBundle\Service\Common\FlashBag;

class PasswordResettingListener implements EventSubscriberInterface
{
    private $router;
    private $em;
    private $flashbag;

    public function __construct(UrlGeneratorInterface $router, EntityManagerInterface $em, FlashBag $flashbag)
    {
        $this->router = $router;
        $this->em = $em;
        $this->flashbag = $flashbag;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE => 'onEmailInitialize',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onPasswordResettingSuccess',
        ];
    }

    public function onEmailInitialize(GetResponseNullableUserEvent $event)
    {
        $statutRepo = $this->em->getRepository('UcaBundle:StatutUtilisateur');
        $usr = $event->getUser();

        if ($usr && $usr->getStatut() == $statutRepo->find(4)) {
            ($this->flashbag)->addMessageFlashBag('utilisateur.compte.bloque', 'danger');
            $url = $this->router->generate('fos_user_security_login');
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function onPasswordResettingSuccess(FormEvent $event)
    {
        $url = $this->router->generate('UcaWeb_Accueil');
        $event->setResponse(new RedirectResponse($url));
    }
}
