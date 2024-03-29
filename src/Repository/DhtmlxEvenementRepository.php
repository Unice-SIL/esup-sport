<?php

/*
 * Classe - DhtmlxEvenementRepository
 *
 * Contient les requêtes à la base de données pour l'entité DhtmlxEvenement
*/

namespace App\Repository;

use App\Entity\Uca\DhtmlxEvenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DhtmlxEvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DhtmlxEvenement::class);
    }

    public function findDhtmlxDateByReference($type, $id)
    {
        if ('ressource' == $type) {
            return $this->createQueryBuilder('e')
                ->leftJoin('e.reservabilite', 're')
                ->leftJoin('re.ressource', 'r')
                ->where('r.id = :id')
                ->andWhere('e.serie is null')
                ->setParameter('id', $id)
                ->getQuery()
                ->getResult()
            ;
        }
        if ('FormatActivite' == $type) {
            // tous les évenements pour le type FormatActivite font partie d'une serie
        }
    }

    public function findDhtmlxReservabiliteByUser($user)
    {
        return array_merge(
            $this->createQueryBuilder('d')
                ->join('d.serie', 's')
                ->join('s.reservabilite', 'r')
                ->join('r.inscriptions', 'i')
                ->where('i.utilisateur = :user')
                ->setParameter('user', $user)
                ->andWhere('i.statut = :statut')
                ->setParameter('statut', 'valide')
                ->getQuery()
                ->getResult(),
            $this->createQueryBuilder('d')
                ->join('d.reservabilite', 'r')
                ->join('r.inscriptions', 'i')
                ->where('i.utilisateur = :user')
                ->setParameter('user', $user)
                ->andWhere('i.statut = :statut and d.serie is null')
                ->setParameter('statut', 'valide')
                ->getQuery()
                ->getResult()
        );
    }

    public function findDhtmlxReservabiliteAttentePartenaireByUser($user)
    {
        return array_merge(
            $this->createQueryBuilder('d')
                ->join('d.serie', 's')
                ->join('s.reservabilite', 'r')
                ->join('r.inscriptions', 'i')
                ->where('i.utilisateur = :user')
                ->setParameter('user', $user)
                ->andWhere('i.statut = :statutAttentePartenaire')
                ->setParameter('statutAttentePartenaire', 'attentepartenaire')
                ->getQuery()
                ->getResult(),
            $this->createQueryBuilder('d')
                ->join('d.reservabilite', 'r')
                ->join('r.inscriptions', 'i')
                ->where('i.utilisateur = :user')
                ->setParameter('user', $user)
                ->andWhere('i.statut = :statutAttentePartenaire and d.serie is null')
                ->setParameter('statutAttentePartenaire', 'attentepartenaire')
                ->getQuery()
                ->getResult()
        );
    }

    public function findDhtmlxFormatSimpleByUser($user)
    {
        return $this->createQueryBuilder('d')
            ->join('d.formatSimple', 'fs')
            ->join('fs.inscriptions', 'i')
            ->where('i.utilisateur = :user')
            ->setParameter('user', $user)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', 'valide')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDhtmlxFormatSimpleByEncadrant($user)
    {
        return $this->createQueryBuilder('d')
            ->join('d.formatSimple', 'fs')
            ->join('fs.encadrants', 'e')
            ->andWhere('e.id = :userid')
            ->setParameter('userid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPremierEvenementDependantSerieDeChaqueSerieDuFormat($formatActiviteId)
    {
        return $this->createQueryBuilder('d')
            ->addSelect('MIN(d.dateDebut)')
            ->addSelect('next.dateDebut')
            ->leftJoin('d.serie', 'serie')
            ->leftJoin('serie.creneau', 'c')
            ->leftJoin('App\Entity\Uca\DhtmlxEvenement', 'next', 'WITH', 'next.serie = serie and next.dateDebut > :maintenant')
            ->where('c.formatActivite = :formatActivite')
            ->andWhere('d.dependanceSerie = :dependanceSerie')
            ->setParameter('formatActivite', $formatActiviteId)
            ->setParameter('dependanceSerie', true)
            ->setParameter('maintenant', (new \DateTime())->format('Y-m-d H:i:s'))
            ->groupBy('d.serie')
            ->orderBy('d.dateDebut')
            ->getQuery()
            ->getResult()
        ;
    }

    public function suppressionDateBascule($listeActiviteId, $dateDebutEffective)
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('App\Entity\Uca\DhtmlxSerie', 's', 'WITH', 's.id = e.serie')
            ->innerJoin('App\Entity\Uca\Creneau', 'c', 'WITH', 'c.id = s.creneau')
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'formAct.id = c.formatActivite')
            ->where('e.dateDebut > :today AND e.dateFin < :dateDebutEffective')
            ->andWhere('formAct.activite IN (:listeActivite)')
            ->setParameter('listeActivite', $listeActiviteId)
            ->setParameter('today', new \DateTime('tomorrow'))
            ->setParameter('dateDebutEffective', $dateDebutEffective)
            ->select('e.id')
            ->getQuery()->getResult()
        ;

        $this->createQueryBuilder('e')
            ->where('e.id in (:ids)')
            ->setParameter('ids', $qb)
            ->delete()
            ->getQuery()
            ->execute()
        ;
    }

    public function findEvenementChaqueSerieDuFormatBetwennDates($formatActiviteId, $dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.serie', 'serie')
            ->leftJoin('serie.creneau', 'c')
            ->leftJoin('c.formatActivite', 'fa')
            ->where('c.formatActivite = :formatActivite')
            ->andWhere('d.dateDebut <= :dateFin')
            ->andWhere('d.dateFin >= :dateDebut')
            ->andWhere('d.dateFin <= fa.dateFinEffective')
            ->setParameter('formatActivite', $formatActiviteId)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('d.dateDebut')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEvenementChaqueSerieDuFormatBetwennDatesByRessource($ressourceId, $dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.reservabilite', 'resa')
            ->leftJoin('resa.ressource', 'ress')
            ->leftJoin('d.serie', 's')
            ->leftJoin('s.reservabilite', 'revervabilite')
            ->leftjoin('revervabilite.ressource', 'ressource')
            ->where('ressource.id = :idRessource or ress.id = :idRessource')
            ->andWhere('d.dateDebut <= :dateFin')
            ->andWhere('d.dateFin >= :dateDebut')
            ->setParameter('idRessource', $ressourceId)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDhtmlxEvenementBySerie($id)
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.serie = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findDhtmlxEvenementBySerieAndSemester($id, $semester)
    {
        $dates = [];
        1 == $semester ? $dates = $this->createDate('-01-01', '-06-30') : $dates = $this->createDate('-07-01', '-12-31');

        $parameters = [
            'id' => $id,
            'dateDeb' => $dates[0],
            'dateFin' => $dates[1],
        ];

        $qb = $this->createQueryBuilder('d')
            ->where('d.serie = :id')
            ->andWhere('d.dateDebut >= :dateDeb AND d.dateFin <= :dateFin')
            ->setParameters($parameters)
        ;

        return $qb->getQuery()->getResult();
    }

    public function createDate($deb, $fin)
    {
        $retour = [];
        $year = new \DateTime();
        $year = date_format($year, 'Y');
        $retour[0] = new \DateTime($year.$deb);
        $retour[1] = new \DateTime($year.$fin);

        return $retour;
    }

    public function findEventByRessourceAndDate($ressourceId, $dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.reservabilite', 're')
            ->leftJoin('re.ressource', 'r')
            ->where('r.id = :id and e.serie is null')
            ->andWhere('e.dateDebut <= :dateFin')
            ->andWhere('e.dateFin >= :dateDebut')
            ->setParameter('id', $ressourceId)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEventOfSerieByRessourceAndDate($ressourceId, $dateDebut, $dateFin)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.serie', 's')
            ->leftJoin('s.reservabilite', 're')
            ->leftJoin('re.ressource', 'r')
            ->where('r.id = :id')
            ->andWhere('e.dateDebut <= :dateFin')
            ->andWhere('e.dateFin >= :dateDebut')
            ->setParameter('id', $ressourceId)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDhtmlxCreneauByUser($user)
    {
        return $this->createQueryBuilder('d')
            ->join('d.serie', 's')
            ->join('s.creneau', 'c')
            ->join('c.inscriptions', 'i')
            ->join('i.formatActivite', 'fa')
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', 'valide')
            ->andWhere('d.dateFin <= fa.dateFinEffective')
            ->getQuery()
            ->getResult()
        ;
    }
}
