<?php

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

class FormatActiviteListener
{
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
