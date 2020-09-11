<?php

/*
 * classe - FormatAvecReservationListener
 *
 * Service intervant lors des modification en base de données de l'entité format avec réservations
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

class FormatAvecReservationListener extends FormatActiviteListener
{
    public function preFlush($formatAvecReservation, PreFlushEventArgs $event)
    {
        parent::preFlush($formatAvecReservation, $event);
        $formatAvecReservation->updateListeRessources();
    }
}
