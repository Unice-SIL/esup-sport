<?php

/*
 * classe - TimeoutService
 *
 * Service gérant le détails d'expiration des ommandes
*/

namespace UcaBundle\Service\Securite;

use Doctrine\ORM\EntityManagerInterface;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\Inscription;

class TimeoutService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
            $commande->changeStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null]);
            $this->em->persist($commande);
        });
    }

    public function nettoyageInscription()
    {
        $inscriptions = $this->em->getRepository(Inscription::class)->aNettoyer();
        $inscriptions->map(function ($inscription) {
            $inscription->setStatut('annule', ['motifAnnulation' => 'timeout', 'commentaireAnnulation' => null]);
            $this->em->persist($inscription);
        });
    }
}
