<?php

/*
 * classe - ActiviteListener
 *
 * Service intervant lors des modification en base de données de l'entitt Activité
*/

namespace App\Service\Listener\Entity;

use DateTime;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Uca\CommandeDetail;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CommandeDetailListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
    }

    public function preFlush(CommandeDetail $commandeDetail, PreFlushEventArgs $event)
    {
        if ($commandeDetail->getTypeAutorisation()) {
            if (4 == $commandeDetail->getTypeAutorisation()->getComportement()->getId()) {
                if (!$commandeDetail->getDateCarteFinValidite()) {
                    $dateFin = '';
                    $date = new DateTime();
                    if ($date->format('m') <= 6) {
                        $dateFin = $date->format('Y').'-07-01';
                    } else {
                        $date = $date->modify('+1 year');
                        $dateFin = $date->format('Y').'-07-01';
                    }
                    $commandeDetail->setDateCarteFinValidite(date_create($dateFin));
                }
            }
        }
    }
}
