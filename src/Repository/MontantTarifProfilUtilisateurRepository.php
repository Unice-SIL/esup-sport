<?php

namespace App\Repository;

use App\Entity\Uca\MontantTarifProfilUtilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * MontantTarifProfilUtilisateurRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MontantTarifProfilUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MontantTarifProfilUtilisateur::class);
    }
}
