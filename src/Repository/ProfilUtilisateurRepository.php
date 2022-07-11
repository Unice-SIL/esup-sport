<?php

/*
 * Classe - ProfilUtilisateur repository
 *
 * Contient les requêtes à la base de données pour l'entité profil utilisateur
*/

namespace App\Repository;

use App\Entity\Uca\ProfilUtilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProfilUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfilUtilisateur::class);
    }
    
    public function findMaxInscription($id)
    {
        return $this->createQueryBuilder('profil')
            ->select('profil.nbMaxInscriptions')
            ->where('profil.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
