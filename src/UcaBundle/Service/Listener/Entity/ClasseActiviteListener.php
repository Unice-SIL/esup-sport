<?php

/*
 * classe - ClasseActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entité classe d'activité
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\ClasseActivite;

class ClasseActiviteListener
{
    public function preFlush(ClasseActivite $classActivite, PreFlushEventArgs $event)
    {
        $classActivite->updateTypeActiviteLibelle();
    }
}
