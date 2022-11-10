<?php

namespace App\Repository;

use App\Entity\Statistique\NbUserByElement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/*
 * Classe - NbUserByElementRepository:
 *
 * Requêtes à la base de données pour l'éntité NbUserByElement
*/
class NbUserByElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NbUserByElement::class);
    }
}
