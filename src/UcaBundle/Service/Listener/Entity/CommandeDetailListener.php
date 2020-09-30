<?php

/*
 * classe - ActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entitt Activité
*/

namespace UcaBundle\Service\Listener\Entity;

use DateTime;
use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\CommandeDetail;

class CommandeDetailListener
{
    public function preFlush(CommandeDetail $commandeDetail, PreFlushEventArgs $event)
    {
        if ($commandeDetail->getTypeAutorisation()) {
            if (4 == $commandeDetail->getTypeAutorisation()->getComportement()->getId()) {
                if (!$commandeDetail->getDateCarteFinValidite()) {
                    $dateFin = '';
                    $date = new DateTime();
                    if ($date->format('m') <= 6) {
                        $dateFin = $date->format('Y').'-07-01';
                    } else {
                        $date = $date->modify('+1 year');
                        $dateFin = $date->format('Y').'-07-01';
                    }
                    $commandeDetail->setDateCarteFinValidite(date_create($dateFin));
                }
            }
        }
    }
}
