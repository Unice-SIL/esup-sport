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

}

