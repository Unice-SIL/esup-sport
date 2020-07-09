<?php

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\Activite;

class ActiviteListener
{
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
