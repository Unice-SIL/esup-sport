<?php

namespace UcaBundle\Service\Listener\Entity;

use UcaBundle\Entity\Inscription;
use Doctrine\ORM\Event\LifecycleEventArgs;

class InscriptionListener {

    public function prePersist(Inscription $inscription, LifecycleEventArgs $args) {
        $inscription->updateNbInscrits(true);
    }
}