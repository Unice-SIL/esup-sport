<?php

/*
 * Classe - AppelRepository
 *
 * Contient les requêtes à la base de données pour l'entité appel
*/

namespace App\Repository;

use App\Entity\Uca\Texte;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class TexteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Texte::class);
    }
    
    public function findByEmplacementName($name)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.emplacement = :name')
            ->setParameter('name', $name)
        ;

        return $qb->getQuery()->getResult()[0];
    }
}
