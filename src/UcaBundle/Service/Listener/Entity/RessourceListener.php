<?php

namespace UcaBundle\Service\Listener\Entity;

use UcaBundle\Entity\Ressource;
use Doctrine\ORM\Event\PreFlushEventArgs;

class RessourceListener
{
    public function preFlush(Ressource $ressource, PreFlushEventArgs $event)
    {
        $ressource->updateTarifLibelle();
        $ressource->updateEtablissementLibelle();
    }
}
