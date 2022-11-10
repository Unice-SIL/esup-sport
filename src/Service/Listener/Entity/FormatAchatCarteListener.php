<?php

/*
 * classe - FormatAchatCarteListener
 *
 * Service intervant lors des modification en base de données de l'entité Format achat de carte
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

class FormatAchatCarteListener extends FormatActiviteListener
{
    public function preFlush($formatAchatCarte, PreFlushEventArgs $event)
    {
        parent::preFlush($formatAchatCarte, $event);
        $formatAchatCarte->updateCarteLibelle();
    }
}
