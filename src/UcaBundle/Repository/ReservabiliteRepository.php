<?php

namespace UcaBundle\Repository;

class ReservabiliteRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByDhtmlxDateByWeek($idRessource, $YearWeek){
        $dateA = new \DateTime($YearWeek);
        $dateB = new \DateTime($YearWeek);
        $dateB = $dateB->modify("+7 day");
        $data = $this->createQueryBuilder("re")
        ->leftJoin("re.evenement", "e")
        ->leftJoin("re.ressource", "r")
        ->addSelect("re")
        ->where("r.id = :id")
        ->andWhere("e.dateDebut BETWEEN :dateA and :dateB")
        ->orderBy("e.dateDebut")
        ->setParameter("id", $idRessource)
        ->setParameter("dateA", $dateA)
        ->setParameter("dateB", $dateB)
        ->getQuery()
        ->getResult(); 
    
        return $data;
    }

    public function findReservabilite($idRessource, $dateA){
        $data = $this->createQueryBuilder("re")
        ->leftJoin("re.evenement", "e")
        ->leftJoin("re.ressource", "r")
        ->addSelect("re")
        ->where("r.id = :id")
        ->andWhere('e IS NOT NULL')
        ->andWhere("e.dateDebut > :dateA")
        ->orderBy("e.dateDebut")
        ->setParameter("id", $idRessource)
        ->setParameter("dateA", $dateA)
        ->getQuery()
        ->getResult(); 
        
        return $data;
    }
    
}
