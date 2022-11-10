<?php

/*
 * Classe - DhtmlxDateRepository
 *
 * Contient les requêtes à la base de données pour l'entité DhtmlxDate
 * Enitté mère pour la libraire Dhtmlx Scheduler
*/

namespace App\Repository;

use App\Entity\Uca\DhtmlxDate;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\DhtmlxEvenement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class DhtmlxDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DhtmlxDate::class);
    }
    
    public function findDhtmlxDateByEncadrant($id)
    {
        $data = [];

        //creneau
        $repository = $this
            ->getEntityManager()
            ->getRepository(DhtmlxSerie::class)
            ;

        $query = $repository->createQueryBuilder('d')
            ->leftJoin('d.creneau', 'c')
            ->leftJoin('c.encadrants', 'e')
            ->leftJoin('c.formatActivite', 'f')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;

        $data = [];
        foreach ($query as $key => $q) {
            $data[] = $q;

            $repository = $this
                ->getEntityManager()
                ->getRepository(DhtmlxEvenement::class)
                ;

            $data = array_merge($data, $repository->createQueryBuilder('e')
                ->leftJoin('e.serie', 's')
                ->where('s.id = :id')
                ->setParameter('id', $q->getId())
                ->getQuery()
                ->getResult());
        }

        return $data;
    }
}
