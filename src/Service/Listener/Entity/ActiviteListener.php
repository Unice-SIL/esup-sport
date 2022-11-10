<?php

/*
 * classe - ActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entitt Activité
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\Activite;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ActiviteListener
{
    public function onLateKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
    }

    public function preFlush(Activite $activite, PreFlushEventArgs $event)
    {
        $activite->updateClasseActiviteLibelle();
        $maxOrdreActivite = (($event->getEntityManager())->getRepository(Activite::class))->maxOrdreActivite();
        if (null === $activite->getOrdre()) {
            $ordre = (null !== $maxOrdreActivite) ? $maxOrdreActivite + 1 : 0;
            $activite->setOrdre($ordre);
        }
    }
}
