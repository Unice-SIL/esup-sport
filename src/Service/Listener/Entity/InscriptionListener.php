<?php

namespace App\Service\Listener\Entity;

use App\Entity\Uca\Inscription;
use Doctrine\ORM\Event\LifecycleEventArgs;

class InscriptionListener {

    public function prePersist(Inscription $inscription, LifecycleEventArgs $args) {
        $inscription->updateNbInscrits(true);
    }
}