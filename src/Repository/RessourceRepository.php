<?php

/*
 * Classe - RessourceRepository

 * Requêtes à la base de données pour l'entité ressource
*/

namespace App\Repository;

use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\Ressource;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class RessourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ressource::class);
    }
    
    public function findDhtmlxDateByRessourceId($id)
    {
        $data = [];

        $repository = $this
            ->getEntityManager()
            ->getRepository(DhtmlxEvenement::class)
        ;

        $data = $repository->createQueryBuilder('e')
            ->leftJoin('e.reservabilite', 'r')
            ->leftJoin('r.ressource', 'ress')
            ->where('ress.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;

        $repository = $this
            ->getEntityManager()
            ->getRepository(DhtmlxSerie::class)
        ;

        $series = [];

        //get series
        foreach ($data as $key => $d) {
            if (null !== $d->getSerie()) {
                array_push($data, $d->getSerie());
            }
        }

        return $data;
    }

    public function findAllLieu()
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.etablissement IS NOT NULL')
        ;

        return $qb->getQuery()->getResult();
    }
}
