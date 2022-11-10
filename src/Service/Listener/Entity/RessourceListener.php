<?php

/*
 * classe - RessourceListener
 *
 * Service intervant lors des modification en base de données de l'entité ressource
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\Ressource;

class RessourceListener
{
    public function preFlush(Ressource $ressource, PreFlushEventArgs $event)
    {
        $ressource->updateTarifLibelle();
        $ressource->updateEtablissementLibelle();
        $ressource->updateListeProfils();
    }
}
