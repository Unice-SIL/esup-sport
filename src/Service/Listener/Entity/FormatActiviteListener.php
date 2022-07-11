<?php

/*
 * classe - FomratActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entitt format d'activité
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class FormatActiviteListener
{
    public function onLateKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
    }

    public function preFlush($formatActivite, PreFlushEventArgs $event)
    {
        $formatActivite->updateTarifLibelle();
        $formatActivite->updateListeLieux();
        $formatActivite->updateListeAutorisations();
        $formatActivite->updateListeNiveauxSportifs();
        $formatActivite->updateListeProfils();
        $formatActivite->updateListeEncadrants();
    }
}
