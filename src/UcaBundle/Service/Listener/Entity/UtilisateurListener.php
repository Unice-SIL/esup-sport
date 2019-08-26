<?php

namespace UcaBundle\Service\Listener\Entity;

use Doctrine\ORM\Event\preUpdateEventArgs;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\CommandeDetail;

class UtilisateurListener
{
    public function preUpdate(Utilisateur $utilisateur, preUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        // On recupère les autorisations ajoutées à l'utilisateur
        $autorisations = $utilisateur->getAutorisations()->getInsertDiff();
        // On recupère tous les détails de commande qui concernent les autorisations trouvées précédemment
        $cds = $em->getRepository(CommandeDetail::class)->findCommandeDetailByUtilisateurAndTypeAutorisation($utilisateur, $autorisations);
        // On parcours ces autorisations et on supprime toutes celles dont les commandes sont aux statuts 'panier' et 'apayer' (pour ne pas supprimer l'achat)
        foreach ($cds as $cd) {
            if (in_array($cd->getCommande()->getStatut(), ['panier', 'apayer'])) {
                $em->remove($cd);
            }
        }
        // die;
    }
}
