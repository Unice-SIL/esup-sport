<?php

/*
 * classe - TimeoutService
 *
 * Service gérant le détails d'expiration des ommandes
*/

namespace App\Service\Securite;

use App\Entity\Uca\Commande;
use App\Entity\Uca\Inscription;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TimeoutService
{
    private $em;
    private $inscriptionRepository;
    private $commandeRepository;
    private $logger;
    private $mailer;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MailService $mailer)
    {
        $this->em = $em;
        $this->inscriptionRepository = $em->getRepository(Inscription::class);
        $this->commandeRepository = $em->getRepository(Commande::class);
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public function nettoyageCommandeEtInscription()
    {
        $this->nettoyageCommande();
        $this->nettoyageInscription();
        $this->nettoyageCommandesInscriptionsPartenaires();
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

            $commande->changeStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null, 'em' => $this->em]);
            $this->em->persist($commande);
        });
    }

    public function nettoyageInscription()
    {
        $inscriptions = $this->em->getRepository(Inscription::class)->aNettoyer();
        $inscriptions->map(function ($inscription) {
            // On vérifie si la commande liée à l'inscription n'est pas au statut "termine"
            if ($inscription->getFirstCommande() && 'termine' == $inscription->getFirstCommande()->getStatut()) {
                // @codeCoverageIgnoreStart
                $this->logger->info('TIMEOUT FAILED - Inscription, La commande liée à l\'inscription est terminée impossible d\'annuler l\'inscription. Info inscription : id => '.$inscription->getId().', statut => '.$inscription->getStatut());

                $this->mailer->sendMailWithTemplate(
                    null,
                    Parametrage::getMailContact(),
                    'ErreurAnnulationInscription',
                    ['id_inscription' => $inscription->getId()]
                );
            // @codeCoverageIgnoreEnd
            } else {
                $this->logger->info('TIMEOUT - Inscription, Info inscription : id => '.$inscription->getId().', statut => '.$inscription->getStatut());
                $inscription->setStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null]);
                $this->em->persist($inscription);
            }
        });
    }

    /**
     * Fonction qui va permettre de nettoyer les inscriptions avec co-équipiers et les commandes liées
     * Gestion du timeout différents des autres inscriptions et commandes
     * Le timeout s'applique à la première inscription (celui qui a saisi les coordonnées de ses co-équipiers).
     */
    public function nettoyageCommandesInscriptionsPartenaires(): void
    {
        $inscriptions = $this->inscriptionRepository->findInscriptionPartenairesANettoyer();
        foreach ($inscriptions as $inscription) {
            $inscriptionsPartenaires = $this->inscriptionRepository->findByEstPartenaire($inscription->getId());
            foreach ($inscriptionsPartenaires as $inscriptionPartenaire) {
                $this->annulationInscriptionEtCommande($inscriptionPartenaire);
            }
            $this->annulationInscriptionEtCommande($inscription);
        }
    }

    /**
     * Fonction qui permet d'annuler une inscription (partenaire) et la commande liée.
     */
    private function annulationInscriptionEtCommande(Inscription $inscription): void
    {
        $commande = $this->commandeRepository->findOneByInscription($inscription->getId());
        if ($commande) {
            $commande->changeStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null, 'em' => $this->em]);
        } else {
            $inscription->setStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null]);
        }
    }
}
