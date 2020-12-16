<?php

/*
 * classe - ActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entitt Activité
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use UcaBundle\Entity\Activite;

class ActiviteListener
{
    public function onLateKernelRequest(GetResponseEvent $event)
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
