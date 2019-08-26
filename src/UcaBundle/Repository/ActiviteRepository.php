<?php

namespace UcaBundle\Repository;


class ActiviteRepository extends \Doctrine\ORM\EntityRepository
{

    public function findByClassActivite($idClassActivite, $user)
    {

        $qb = $this->createQueryBuilder('a')
        ->leftJoin('a.classeActivite', "ca")
        ->leftJoin('a.formatsActivite', "f")
        ->andWhere("ca.id = :idClassActivite")
        ->andWhere("f.statut = 1")
        ->andWhere("f.dateDebutPublication <= :today")
        ->andWhere("f.dateFinPublication >= :today")
        ->setParameter('today', new \Datetime('now'))
        ->setParameter("idClassActivite", $idClassActivite);
      
        if($user !== null){
            $qb
            ->leftJoin("f.profilsUtilisateurs", "p")
            ->leftJoin("p.utilisateur", "u")
            ->andWhere("u.id = :idUtilisateur")
            ->setParameter("idUtilisateur", $user->getId());            
        }

        return $qb->getQuery()->getResult();

    }

}
