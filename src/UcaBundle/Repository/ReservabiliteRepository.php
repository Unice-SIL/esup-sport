<?php

/*
 * Classe - ReservabiliteRepository
 *
 * Requêtes à la base de données pour l'entité réservabilité
*/

namespace UcaBundle\Repository;

class ReservabiliteRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByDhtmlxDateByWeek($idRessource, $YearWeek)
    {
        $dateA = new \DateTime($YearWeek);
        $dateB = new \DateTime($YearWeek);
        $dateB = $dateB->modify('+7 day');

        return $this->createQueryBuilder('re')
            ->leftJoin('re.evenement', 'e')
            ->leftJoin('re.ressource', 'r')
            ->addSelect('re')
            ->where('r.id = :id')
            ->andWhere('e.dateDebut BETWEEN :dateA and :dateB')
            ->orderBy('e.dateDebut')
            ->setParameter('id', $idRessource)
            ->setParameter('dateA', $dateA)
            ->setParameter('dateB', $dateB)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findReservabilite($idRessource, $dateA)
    {
        return $this->createQueryBuilder('re')
            ->leftJoin('re.evenement', 'e')
            ->leftJoin('re.ressource', 'r')
            ->addSelect('re')
            ->where('r.id = :id')
            ->andWhere('e IS NOT NULL')
            ->andWhere('e.dateDebut > :dateA')
            ->orderBy('e.dateDebut')
            ->setParameter('id', $idRessource)
            ->setParameter('dateA', $dateA)
            ->getQuery()
            ->getResult()
        ;
    }
}
