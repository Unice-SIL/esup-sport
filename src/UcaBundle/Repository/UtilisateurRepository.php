<?php

namespace UcaBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class UtilisateurRepository extends \Doctrine\ORM\EntityRepository
{
    public function findtByGroupsName($name) {
        $query = $this->createQueryBuilder("d")
        ->join("d.formatsActivite", "f")
        ->join("d.groups", "g")
        ->where("g.name = :name")
        ->setParameter("name", $name)
        ->getQuery()
        ->getResult();
        return $query;
    }

    public function findByRole($role) { 
    $qb = ($this->createQueryBuilder('u')) 
        ->select('u.email,u.nom,u.prenom')
        ->join('u.groups','g')
        ->where('g.roles LIKE :roles')
        ->setParameter('roles', '%"'.$role.'"%');
    return $qb->getQuery()->getResult();
    }

    public function findUtilisateurByEvenement($idDhtmlxEvenement)
    {
        $qb = $this->createQueryBuilder('u')
        ->leftJoin('u.appels', "a")
        ->leftJoin('a.dhtmlxEvenement', "d")
        ->andWhere("d.id = :idDhtmlxEvenement")
        ->setParameter('idDhtmlxEvenement', $idDhtmlxEvenement);
      
        return $qb->getQuery()->getResult();

    }
}

