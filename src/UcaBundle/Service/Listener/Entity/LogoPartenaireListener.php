<?php

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;
use UcaBundle\Entity\LogoPartenaire;

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
