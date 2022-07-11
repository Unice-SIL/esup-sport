<?php

/*
 * Classe - EtablissementRepository
 *
 * Requêtes à la base de données pour l'entité etabllssement
*/

namespace App\Repository;

use App\Entity\Uca\Etablissement;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class EtablissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etablissement::class);
    }
    
    public function findEtablissementByActivite($idActivite)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->where('a.id = :id')
            ->andWhere('f.dateDebutPublication <= :date')
            ->andWhere('f.dateFinPublication >:date')
            ->andWhere('f.statut=1')
            ->setParameters(['id' => $idActivite, 'date' => new \DateTime()])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByCreneau($id)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->join('App\Entity\Uca\Creneau', 'c', Join::WITH, 'l = c.lieu')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByFormatActivite($id)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->where('f.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByClasseActivite($id)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->leftJoin('a.classeActivite', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByTypeActivite($id)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->leftJoin('a.classeActivite', 'c')
            ->leftJoin('c.typeActivite', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByInscription($id)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->join('App\Entity\Uca\Inscription', 'i', Join::WITH, 'f = i.formatActivite')
            ->where('i.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByEncadrant($id)
    {
        return $this->createQueryBuilder('e')
            ->join('App\Entity\Uca\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->join('App\Entity\Uca\Utilisateur', 'u', Join::WITH, 'f = u.formatsActivite')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }
}
