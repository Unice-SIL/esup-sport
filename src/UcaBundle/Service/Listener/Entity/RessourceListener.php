<?php

/*
 * classe - RessourceListener
 *
 * Service intervant lors des modification en base de données de l'entité ressource
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\Ressource;

class RessourceListener
{
    public function preFlush(Ressource $ressource, PreFlushEventArgs $event)
    {
        $ressource->updateTarifLibelle();
        $ressource->updateEtablissementLibelle();
        $ressource->updateListeProfils();
    }
}
