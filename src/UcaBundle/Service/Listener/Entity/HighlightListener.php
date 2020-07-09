<?php

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\Highlight;

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
