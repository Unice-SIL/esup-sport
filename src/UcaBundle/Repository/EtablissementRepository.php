<?php

namespace UcaBundle\Repository;
use Doctrine\ORM\Query\Expr\Join;


class EtablissementRepository extends \Doctrine\ORM\EntityRepository
{
    public function findEtablissementByActivite($idActivite){

        $data =  $this->createQueryBuilder("e")
         ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, "e = l.etablissement")
         ->leftJoin("l.formatsActivite", "f")
         ->leftJoin("f.activite", "a")
         ->where("a.id = :id")
         ->setParameter("id", $idActivite)
         ->getQuery()
         ->getResult();

         return $data;
     }
}