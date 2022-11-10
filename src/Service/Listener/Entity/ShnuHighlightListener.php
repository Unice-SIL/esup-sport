<?php

/*
 * classe - ShnuHighlightListener
 *
 * Service intervant lors des modification en base de données de l'entité Shnu highlight
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\ShnuHighlight;

class ShnuHighlightListener
{
    public function __construct()
    {
    }

    public function preFlush(ShnuHighlight $highlight, PreFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        if (null == $highlight->getOrdre()) {
            $ordre = $em->getRepository(ShnuHighlight::class)->max('ordre') + 1;
            $highlight->setOrdre($ordre);
        }
    }
}
