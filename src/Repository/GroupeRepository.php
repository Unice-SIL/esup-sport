<?php

/*
 * Classe - GroupeRepository
 *
 * Requêtes à la base de données pour l'entité groupe
*/

namespace App\Repository;

use App\Entity\Uca\Groupe;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupe::class);
    }
    
    public function findGroupeEncadrant()
    {
        $qb = $this->createQueryBuilder('g')
            ->where("g.libelle = 'Encadrant'")
        ;

        return $qb->getQuery()->getResult();
    }
}
