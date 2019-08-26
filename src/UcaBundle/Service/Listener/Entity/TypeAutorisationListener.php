<?php

namespace UcaBundle\Service\Listener\Entity;

use UcaBundle\Entity\TypeAutorisation;
use Doctrine\ORM\Event\PreFlushEventArgs;

class TypeAutorisationListener
{
    public function preFlush(TypeAutorisation $typeAutorisation, PreFlushEventArgs $event)
    {
        $typeAutorisation->updateTarifLibelle();
        $typeAutorisation->updateComportementLibelle();
    }
}
