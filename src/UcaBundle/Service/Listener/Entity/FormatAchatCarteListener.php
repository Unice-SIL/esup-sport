<?php

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

class FormatAchatCarteListener extends FormatActiviteListener
{
    public function preFlush($formatAchatCarte, PreFlushEventArgs $event)
    {
        parent::preFlush($formatAchatCarte, $event);
        $formatAchatCarte->updateCarteLibelle();
    }
}
