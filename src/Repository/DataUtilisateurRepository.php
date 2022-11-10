<?php

/*
 * Classe - DataUtilisateurRepository:
 *
 * Requêtes à la base de données pour l'éntité DataUtilisateur
*/

namespace App\Repository;

use App\Entity\Statistique\DataUtilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class DataUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataUtilisateur::class);
    }
    
    public function findByCategorieNotNull($anneeUniversitaire)
    {
        $qb = $this->createQueryBuilder('du')
            ->andWhere('du.categorie is not null')
            ->andWhere('du.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getCodEtu($anneeUniversitaire, $estMembrePersonnel)
    {
        $qb = $this->createQueryBuilder('du')
            ->select('GROUP_CONCAT(du.codEtu), COUNT(DISTINCT du.codEtu)')
            ->andWhere('du.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->andWhere('du.estMembrePersonnel = :estMembrePersonnel')
            ->setParameter('estMembrePersonnel', $estMembrePersonnel)
        ;

        return $qb->getQuery()->getSingleResult();
    }

    public function getNbreBoursier($anneeUniversitaire, $codEtu)
    {
        $qb = $this->createQueryBuilder('du')
            ->select('COUNT(DISTINCT du.codEtu)')
            ->andWhere('du.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->andWhere('du.boursier = :boursier')
            ->setParameter('boursier', 'O')
            ->andWhere('du.codEtu IN (:codEtu)')
            ->setParameter('codEtu', $codEtu)
        ;

        return $qb->getQuery()->getSingleResult()[1];
    }

    public function getNbreShnu($anneeUniversitaire, $codEtu)
    {
        $qb = $this->createQueryBuilder('du')
            ->select('COUNT(DISTINCT du.codEtu)')
            ->andWhere('du.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->andWhere('du.shnu = :shnu')
            ->setParameter('shnu', 'O')
            ->andWhere('du.codEtu IN (:codEtu)')
            ->setParameter('codEtu', $codEtu)
        ;

        return $qb->getQuery()->getSingleResult()[1];
    }

    public function getNbreByCategorie($anneeUniversitaire, $codEtu)
    {
        $qb = $this->createQueryBuilder('du')
            ->select('du.categorie, COUNT(DISTINCT du.codEtu)')
            ->andWhere('du.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->andWhere('du.categorie is not null')
            ->andWhere('du.codEtu IN (:codEtu)')
            ->setParameter('codEtu', $codEtu)
            ->groupBy('du.categorie')
            ->orderBy('du.categorie')
        ;

        return $qb->getQuery()->getResult();
    }
}
