<?php

/*
 * classe - TimeoutService
 *
 * Service gérant le détails d'expiration des ommandes
*/

namespace UcaBundle\Service\Securite;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\Inscription;
use UcaBundle\Service\Common\MailService;
use UcaBundle\Service\Common\Parametrage;

class TimeoutService
{
    private $em;
    private $logger;
    private $mailer;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MailService $mailer)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public function nettoyageCommandeEtInscription()
    {
        $this->nettoyageCommande();
        $this->nettoyageInscription();
        $this->em->flush();
    }

    public function nettoyageCommande()
    {
        $commandes = $this->em->getRepository(Commande::class)->aNettoyer();
        $commandes->map(function ($commande) {
            $typeTimeout = '';
            if ('apayer' == $commande->getStatut() && 'PAYBOX' == $commande->getTypePaiement()) {
                $typeTimeout = 'PAYBOX';
            } elseif ('apayer' == $commande->getStatut() && 'BDS' == $commande->getTypePaiement()) {
                $typeTimeout = 'BDS';
            } elseif ('panier' == $commande->getStatut()) {
                $typeTimeout = 'Panier';
            }

            $this->logger->info('TIMEOUT - '.$typeTimeout.' Info commande : id => '.$commande->getId().', statut => '.$commande->getStatut().', n° Commande => '.$commande->getNumeroCommande());

            $commande->changeStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null]);
            $this->em->persist($commande);
        });
    }

    public function nettoyageInscription()
    {
        $inscriptions = $this->em->getRepository(Inscription::class)->aNettoyer();
        $inscriptions->map(function ($inscription) {
            // On vérifie si la commande liée à l'inscription n'est pas au statut "termine"
            if ($inscription->getFirstCommande() && $inscription->getFirstCommande()->getStatut() == 'termine') {
                $this->logger->info('TIMEOUT FAILED - Inscription, La commande liée à l\'inscription est terminée impossible d\'annuler l\'inscription. Info inscription : id => '.$inscription->getId().', statut => '.$inscription->getStatut());

                $this->mailer->sendMailWithTemplate(
                    'TIMEOUT : erreur annulation inscription',
                    Parametrage::getMailContact(),
                    '@Uca/Email/Commande/ErreurAnnulationInscription.html.twig',
                    ['inscription' => $inscription]
                );
            } else {
                $this->logger->info('TIMEOUT - Inscription, Info inscription : id => '.$inscription->getId().', statut => '.$inscription->getStatut());
                $inscription->setStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null])->updateNbInscrits(false);
                $this->em->persist($inscription);
            }
        });
    }
}