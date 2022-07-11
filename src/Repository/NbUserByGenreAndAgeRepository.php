<?php

namespace App\Repository;

use App\Entity\Statistique\NbUserByGenreAndAge;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/*
 * Classe - NbUserByGenreAndAgeRepository:
 *
 * Requêtes à la base de données pour l'éntité NbUserByGenreAndAge
*/
class NbUserByGenreAndAgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NbUserByGenreAndAge::class);
    }
}
