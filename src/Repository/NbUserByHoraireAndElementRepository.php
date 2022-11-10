<?php

namespace App\Repository;

use App\Entity\Statistique\NbUserByHoraireAndElement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/*
 * Classe - NbUserByHoraireAndElementRepository:
 *
 * Requêtes à la base de données pour l'éntité NbUserByHoraireAndElement
*/
class NbUserByHoraireAndElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NbUserByHoraireAndElement::class);
    }
}
