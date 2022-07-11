<?php

/*
 * classe - CommandeListener
 *
 * Service intervant lors des modification en base de données de l'entité commande
*/

namespace App\Service\Listener\Entity;

use App\Entity\Uca\Commande;
use App\Service\Common\MailService;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class CommandeListener
{
    private $mailer;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(MailService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function preUpdate(Commande $commande, PreUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        if ($event->hasChangedField('statut')) {
            if ('apayer' != $event->getOldValue('statut') && 'apayer' == $event->getNewValue('statut')) {
                if (null === $commande->getNumeroCommande()) {
                    $numero = $em->getRepository(Commande::class)->max('numeroCommande') + 1;
                    $commande->setNumeroCommande($numero);
                }

                if ($event->hasChangedField('typePaiement') && 'BDS' != $event->getOldValue('typePaiement') && 'BDS' == $event->getNewValue('typePaiement') && $commande->getUtilisateur()->getEmail()) {
                    if ('test' !== $_ENV['APP_ENV']) {
                        //@codeCoverageIgnoreStart
                        $this->mailer->sendMailWithTemplate(
                            'Commande à régler au bureau des sports',
                            $commande->getUtilisateur()->getEmail(),
                            'UcaBundle/Email/Commande/CommandeARegler.html.twig',
                            ['commande' => $commande]
                        );
                        //@codeCoverageIgnoreEnd
                    }
                }
            }
            if ('annule' == $event->getOldValue('statut') && 'termine' == $event->getNewValue('statut')) {
                $numero = $em->getRepository(Commande::class)->max('numeroRecu') + 1;
                $commande->setNumeroRecu($numero);
            } elseif ('termine' != $event->getOldValue('statut') && 'termine' == $event->getNewValue('statut')) {
                // condition supplémentaire pour les avoirs ?
                if ($event->hasChangedField('montantTotal') && 0 != $event->getNewValue('montantTotal')) {
                    $numero = $em->getRepository(Commande::class)->max('numeroRecu') + 1;
                    $commande->setNumeroRecu($numero);
                } elseif ('avoir' != $event->getNewValue('statut') && null === $commande->getNumeroCommande()) {
                    $numero = $em->getRepository(Commande::class)->max('numeroCommande') + 1;
                    $commande->setNumeroCommande($numero);
                }

                if ($commande->getUtilisateur()->getEmail()) {
                    if ('test' !== $_ENV['APP_ENV']) {
                        //@codeCoverageIgnoreStart
                        $this->mailer->sendMailWithTemplate(
                            'Validation de la commande',
                            $commande->getUtilisateur()->getEmail(),
                            'UcaBundle/Email/Commande/ValidationCommande.html.twig',
                            ['commande' => $commande]
                        );
                        //@codeCoverageIgnoreEnd
                    }
                }
            }
            if ('annule' != $event->getOldValue('statut') && 'annule' == $event->getNewValue('statut') && $commande->getUtilisateur()->getEmail()) {
                if ('test' !== $_ENV['APP_ENV']) {
                    //@codeCoverageIgnoreStart
                    $this->mailer->sendMailWithTemplate(
                        'Annulation de la commande',
                        $commande->getUtilisateur()->getEmail(),
                        'UcaBundle/Email/Commande/AnulationCommande.html.twig',
                        ['commande' => $commande]
                    );
                    //@codeCoverageIgnoreEnd
                }
            }
        }
    }
}
