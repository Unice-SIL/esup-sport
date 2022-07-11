<?php

/*
 * Classe - TarifRepository
 *
 * Requêtes à la base de données pour l'entité tarif
*/

namespace App\Repository;

use App\Entity\Uca\Tarif;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class TarifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarif::class);
    }
    
    public function listAll(QueryBuilder $qb)
    {
        $qb
            ->Join('tarif.montants', 'montant')
            ->leftJoin('montant.profil', 'profil')
            ->Select('tarif,montant,profil')
            ->orderBy('profil.libelle')
        ;

        return $qb;
    }
}
