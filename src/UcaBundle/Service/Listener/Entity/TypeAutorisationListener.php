<?php

/*
 * classe - TypeAutorisation
 *
 * Service intervant lors des modification en base de données de l'entité Type autorisation
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\TypeAutorisation;

class TypeAutorisationListener
{
    public function preFlush(TypeAutorisation $typeAutorisation, PreFlushEventArgs $event)
    {
        $typeAutorisation->updateTarifLibelle();
        $typeAutorisation->updateComportementLibelle();
    }
}
