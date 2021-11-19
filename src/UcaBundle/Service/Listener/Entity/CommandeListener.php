<?php

/*
 * classe - CommandeListener
 *
 * Service intervant lors des modification en base de données de l'entité commande
*/

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use UcaBundle\Entity\Commande;
use UcaBundle\Service\Common\MailService;

class CommandeListener
{
    private $mailer;

    public function __construct(MailService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function preUpdate(Commande $commande, PreUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        if ($event->hasChangedField('statut')) {
            if ('apayer' != $event->getOldValue('statut') && 'apayer' == $event->getNewValue('statut')) {
                $numero = $em->getRepository(Commande::class)->max('numeroCommande') + 1;
                $commande->setNumeroCommande($numero);

                if ('BDS' != $event->getOldValue('typePaiement') && 'BDS' == $event->getNewValue('typePaiement') && $commande->getUtilisateur()->getEmail()) {
                    $this->mailer->sendMailWithTemplate(
                        'Commande à régler au bureau des sports',
                        $commande->getUtilisateur()->getEmail(),
                        '@Uca/Email/Commande/CommandeARegler.html.twig',
                        ['commande' => $commande]
                    );
                }
            }
            if ('termine' != $event->getOldValue('statut') && 'termine' == $event->getNewValue('statut')) {
                // condition supplémentaire pour les avoirs ?
                if ($event->hasChangedField('montantTotal') && 0 != $event->getNewValue('montantTotal')) {
                    $numero = $em->getRepository(Commande::class)->max('numeroRecu') + 1;
                    $commande->setNumeroRecu($numero);
                } elseif ('avoir' != $event->getNewValue('statut')) {
                    $numero = $em->getRepository(Commande::class)->max('numeroCommande') + 1;
                    $commande->setNumeroCommande($numero);
                }

                if ($commande->getUtilisateur()->getEmail()) {
                    $this->mailer->sendMailWithTemplate(
                        'Validation de la commande',
                        $commande->getUtilisateur()->getEmail(),
                        '@Uca/Email/Commande/ValidationCommande.html.twig',
                        ['commande' => $commande]
                    );
                }
            }
            if ('annule' != $event->getOldValue('statut') && 'annule' == $event->getNewValue('statut') && $commande->getUtilisateur()->getEmail()) {
                $this->mailer->sendMailWithTemplate(
                    'Annulation de la commande',
                    $commande->getUtilisateur()->getEmail(),
                    '@Uca/Email/Commande/AnulationCommande.html.twig',
                    ['commande' => $commande]
                );
            }
        }
    }
}
