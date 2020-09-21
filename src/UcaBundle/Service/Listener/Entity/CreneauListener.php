<?php

/*
 * classe - CreneauListener
 *
 * Service intervant lors des modification en base de données de l'entité créneau
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

class CreneauListener
{
    public function preFlush($creneau, PreFlushEventArgs $event)
    {
        $creneau->updateListeEncadrants();
    }
}
