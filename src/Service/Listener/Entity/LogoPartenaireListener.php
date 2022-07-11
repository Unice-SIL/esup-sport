<?php

/*
 * classe - LogoPartenaire
 *
 * Service intervant lors des modification en base de données de l'entité Logo partenaires
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\LogoPartenaire;

class LogoPartenaireListener
{
    public function __construct()
    {
    }

    public function preFlush(LogoPartenaire $logoPartenaire, PreFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        if (null == $logoPartenaire->getOrdre()) {
            $ordre = $em->getRepository(LogoPartenaire::class)->max('ordre') + 1;
            $logoPartenaire->setOrdre($ordre);
        }
    }
}
