<?php

/*
 * Classe - ActiviteRepository
 *
 * Contient les requêtes à la base de données pour l'entité Activité
*/

namespace App\Repository;

use App\Entity\Uca\Activite;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    public function findByClassActivite($idClassActivite, $user)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.classeActivite', 'ca')
            ->leftJoin('a.formatsActivite', 'f')
            ->andWhere('ca.id = :idClassActivite')
            ->andWhere('f.statut = 1')
            ->andWhere('f.dateDebutPublication <= :today')
            ->andWhere('f.dateFinPublication >= :today')
            ->setParameter('today', new \Datetime('now'))
            ->setParameter('idClassActivite', $idClassActivite)
        ;

        if (null !== $user) {
            $qb
                ->leftJoin('f.profilsUtilisateurs', 'fp')
                ->leftJoin('fp.profilUtilisateur', 'p')
                ->leftJoin('p.enfants', 'e')
                ->leftJoin('p.utilisateur', 'u')
                ->leftJoin('e.utilisateur', 'ue')
                ->andWhere('u.id = :idUtilisateur or ue.id = :idUtilisateur')
                ->setParameter('idUtilisateur', $user->getId())
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findByParameters($idTypeActivite, $idClasseActivite, $idActivite, $idEtablissement, $idLieu)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftjoin('a.formatsActivite', 'f')
            ->leftJoin('a.classeActivite', 'ca')
            ->leftJoin('f.lieu', 'l')
            ->andWhere('f.statut = 1')
            ->andWhere('f.dateDebutPublication <= :today')
            ->andWhere('f.dateFinPublication >= :today')
            ->setParameter('today', new \Datetime('now'))
        ;

        if (null != $idActivite && 0 != $idActivite) {
            $qb
                ->andWhere('a.id = :idActivite')
                ->setParameter('idActivite', $idActivite)
            ;
        }

        if (null != $idClasseActivite && 0 != $idClasseActivite) {
            $qb
                ->andWhere('ca.id = :idClasseActivite')
                ->setParameter('idClasseActivite', $idClasseActivite)
            ;
        }

        if (null != $idTypeActivite && 0 != $idTypeActivite) {
            $qb
                ->leftJoin('ca.typeActivite', 'ta')
                ->andWhere('ta.id = :idTypeActivite')
                ->setParameter('idTypeActivite', $idTypeActivite)
            ;
        }

        if (null != $idLieu && 0 != $idLieu) {
            $qb
                ->andWhere('l.id = :idLieu')
                ->setParameter('idLieu', $idLieu)
            ;
        }

        if (null != $idEtablissement && 0 != $idEtablissement) {
            $qb
                ->leftJoin('l.etablissement', 'e')
                ->andWhere('e.id = :idEtablissemment')
                ->setParameter('idEtablissemment', $idEtablissement)
            ;
        }
        $qb->orderBy('a.ordre', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findActiviteByCreneau($id)
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'formAct.activite = a.id')
            ->innerJoin('App\Entity\Uca\Creneau', 'cre', 'WITH', 'cre.formatActivite = formAct.id')
            ->where('cre.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function maxOrdreActivite()
    {
        $qb = $this->createQueryBuilder('activite')->select('MAX(activite.ordre)');

        return $result = $qb->getQuery()->getSingleScalarResult();
    }

    public function findRecherche($idActivite = null, $idEtablissement = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.formatsActivite', 'fa')
            ->andWhere('fa.dateFinEffective < :now')
            ->setParameter('now', new DateTime())
        ;

        if (null !== $idActivite && '' !== $idActivite && '0' !== $idActivite && 0 !== $idActivite) {
            $qb->andWhere('a.id = :idActivite')
                ->setParameter('idActivite', $idActivite)
            ;
        }

        if (null !== $idEtablissement && '' !== $idEtablissement && '0' !== $idEtablissement && 0 !== $idEtablissement) {
            $qb->leftJoin('fa.lieu', 'l')
                ->andWhere('l.etablissement = :idEtablissement')
                ->setParameter('idEtablissement', $idEtablissement)
            ;
        }

        return $qb->orderBy('a.libelle', 'asc')->getQuery()->getResult();
    }

    public function findActivitePubliee()
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.formatsActivite', 'fa')
            ->andWhere('fa.dateFinEffective <= :now')
            ->setParameter(':now', new DateTime())
            ->orderBy('a.libelle', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
