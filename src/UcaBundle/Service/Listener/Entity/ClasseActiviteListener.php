<?php

namespace UcaBundle\Service\Listener\Entity;

use UcaBundle\Entity\ClasseActivite;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ClasseActiviteListener
{
    public function preFlush(ClasseActivite $classActivite, PreFlushEventArgs $event)
    {
        $classActivite->updateTypeActiviteLibelle();
    }
}
