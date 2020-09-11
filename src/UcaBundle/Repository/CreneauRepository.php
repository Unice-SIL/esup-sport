<?php

/*
 * Classe - CreneauRepository
 *
 * Contient les requêtes à la base de données pour l'entité creneau
*/

namespace UcaBundle\Repository;

class CreneauRepository extends \Doctrine\ORM\EntityRepository
{
    public function findCreneauByFormatActivite($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.formatActivite = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCreneauByActivite($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.formatActivite', 'fa')
            ->leftJoin('fa.activite', 'act')
            ->where('act.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCreneauByClasseActivite($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.formatActivite', 'fa')
            ->leftJoin('fa.activite', 'act')
            ->leftJoin('act.classeActivite', 'ca')
            ->where('ca.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCreneauByTypeActivite($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.formatActivite', 'fa')
            ->leftJoin('fa.activite', 'act')
            ->leftJoin('act.classeActivite', 'ca')
            ->leftJoin('ca.typeActivite', 'ta')
            ->where('ta.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }
}
