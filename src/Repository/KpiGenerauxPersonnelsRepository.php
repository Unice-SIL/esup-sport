<?php

namespace App\Repository;

use App\Entity\Statistique\KpiGenerauxPersonnels;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/*
 * Classe - KpiGenerauxPersonnelsRepository:
 *
 * Requêtes à la base de données pour l'éntité KpiGenerauxPersonnels
*/
class KpiGenerauxPersonnelsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KpiGenerauxPersonnels::class);
    }
}
