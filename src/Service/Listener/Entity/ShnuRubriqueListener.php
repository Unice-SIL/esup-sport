<?php

/*
 * classe - ShnuRubriqueListener
 *
 * Service intervant lors des modification en base de données de l'entité Shnu Rubrique
*/

namespace App\Service\Listener\Entity;

use App\Entity\Uca\ShnuRubrique;
use Doctrine\ORM\Event\PreFlushEventArgs;

class ShnuRubriqueListener
{
    public function __construct()
    {
    }

    public function preFlush(ShnuRubrique $shnuRubrique, PreFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        if (null == $shnuRubrique->getOrdre()) {
            $ordre = $em->getRepository(ShnuRubrique::class)->max('ordre') + 1;
            $shnuRubrique->setOrdre($ordre);
        }
    }
}
