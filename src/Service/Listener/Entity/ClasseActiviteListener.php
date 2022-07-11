<?php

/*
 * classe - ClasseActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entité classe d'activité
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\ClasseActivite;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ClasseActiviteListener
{
    public function onLateKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
    }

    public function preFlush(ClasseActivite $classActivite, PreFlushEventArgs $event)
    {
        $classActivite->updateTypeActiviteLibelle();
    }
}
