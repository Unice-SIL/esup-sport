<?php

namespace UcaBundle\Service\Listener\Entity;

use UcaBundle\Entity\Activite;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ActiviteListener
{
    public function preFlush(Activite $classActivite, PreFlushEventArgs $event)
    {
        $classActivite->updateClasseActiviteLibelle();
    }
}
