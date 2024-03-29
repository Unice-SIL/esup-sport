<?php

/*
 * Classe - CreneauRepository
 *
 * Contient les requêtes à la base de données pour l'entité creneau
*/

namespace App\Repository;

use App\Entity\Uca\Creneau;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class CreneauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creneau::class);
    }

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

    public function findCreneauBySerie()
    {
        //$qb = $this->createQueryBuilder('creneau')
        $qb = ($this->_em->createQueryBuilder())
            ->select('creneau.id', 'serie.id as serieId', 'formatActivite.libelle', 'formatActivite.id as formatId', 'evenement.dateDebut', 'evenement.dateFin')
            ->from(Creneau::class, 'creneau')
            ->join('creneau.formatActivite', 'formatActivite')
            ->join('creneau.serie', 'serie')
            ->join('serie.evenements', 'evenement')
            ->andWhere('evenement.dependanceSerie = :dependant')
            ->setParameters(['dependant' => true])
        ;

        return $qb->getQuery()->getResult();
    }
}
