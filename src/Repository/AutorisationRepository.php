<?php

/*
 * Classe - AutorisationRepository
 *
 * Contient les requêtes à la base de données pour l'entité autorisation
*/

namespace App\Repository;

use App\Entity\Uca\Autorisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AutorisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Autorisation::class);
    }
    
    public function findFinishedAutorisations()
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.inscription', 'i')
            ->leftJoin('i.formatActivite', 'fa')
            ->where('fa.dateFinEffective <= :now')
            ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }
}
