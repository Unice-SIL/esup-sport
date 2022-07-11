<?php

namespace App\Repository;

use App\Entity\Statistique\KpiGenerauxEtudiants;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/*
 * Classe - KpiGenerauxEtudiantsRepository:
 *
 * Requêtes à la base de données pour l'éntité KpiGenerauxEtudiants
*/
class KpiGenerauxEtudiantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KpiGenerauxEtudiants::class);
    }
}
