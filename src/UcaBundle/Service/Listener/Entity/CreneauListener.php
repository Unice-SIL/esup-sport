<?php

/*
 * classe - CreneauListener
 *
 * Service intervant lors des modification en base de données de l'entité créneau
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class CreneauListener
{
    public function onLateKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
    }

    public function preFlush($creneau, PreFlushEventArgs $event)
    {
        $creneau->updateListeEncadrants();
    }
}
