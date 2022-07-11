<?php

/*
 * Classe - AppelRepository
 *
 * Contient les requêtes à la base de données pour l'entité appel
*/

namespace App\Repository;

use App\Entity\Uca\ImageFond;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class ImageFondRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImageFond::class);
    }
    
    public function findByEmplacementName($name)
    {
        $qb = $this->createQueryBuilder('af')
            ->where('af.emplacement = :name')
            ->setParameter('name', $name)
        ;

        return $qb->getQuery()->getResult()[0];
    }
}
