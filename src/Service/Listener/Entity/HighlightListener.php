<?php

/*
 * classe - HighlightListener
 *
 * Service intervant lors des modification en base de données de l'entité Highlight
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\Highlight;

class HighlightListener
{
    public function __construct()
    {
    }

    public function preFlush(Highlight $highlight, PreFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        if (null == $highlight->getOrdre()) {
            $ordre = $em->getRepository(Highlight::class)->max('ordre') + 1;
            $highlight->setOrdre($ordre);
        }
    }
}
