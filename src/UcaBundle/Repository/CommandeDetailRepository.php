<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;

class CommandeDetailRepository extends \Doctrine\ORM\EntityRepository
{

    // public function criteriaByUtilisateur($utilisateur)
    // {
    //     return Criteria::create()
    //         ->andWhere(Criteria::expr()->eq('commande.utilisateur', $utilisateur));
    // }

    public function criteriaByAutorisation($autorisation)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('typeAutorisation', $autorisation));
    }

    // public function findCommandeDetailByUtilisateurAndTypeAutorisation($utilisateur, $autorisations)
    // {
    //     $qb = $this->createQueryBuilder('cd');
    //     $qb->andWhere('cd.typeAutorisation in (:autorisations)');
    //     $qb->setParameter('autorisations', $autorisations);
    //     $qb->join('cd.commande', 'c');
    //     $qb->addSelect('c');
    //     $qb->andWhere('c.statut in (:statuts)');
    //     $qb->setParameter('statuts', ['apayer', 'panier']);
    //     $qb->andWhere('c.utilisateur = :utilisateur');
    //     $qb->setParameter('utilisateur', $utilisateur);
    //     return $qb->getQuery()->getResult();
    // }
}
