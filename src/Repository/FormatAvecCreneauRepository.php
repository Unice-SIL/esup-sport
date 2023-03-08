<?php

/*
 * Classe - FormatAvecCreneauRepository
 *
 * Requêtes à la base de données pour l'entité format avec creneau
*/

namespace App\Repository;

use App\Entity\Uca\FormatAvecCreneau;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class FormatAvecCreneauRepository extends FormatActiviteRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatAvecCreneau::class);
    }

    public function findFormatActiviteByDateTimePeriodAndActivite($daysofweek, $timeStart, $timeEnd, $activite, $idEtablissement = null)
    {
        $qb = $this->createQueryBuilder('formAct')
            ->distinct()
            ->innerJoin('formAct.creneaux', 'cre')
            ->innerJoin('cre.serie', 's')
            ->innerJoin('s.evenements', 'ev')
            ->andWhere('formAct.statut = 1')
            ->andWhere('formAct.dateDebutPublication <= :now')
            ->andWhere('formAct.dateFinPublication >= :now')
            ->andWhere('DATE(ev.dateDebut) >= DATE(:now)')
            ->andWhere('TIME(ev.dateDebut) BETWEEN TIME(:timeStart) AND TIME(:timeEnd)')
            ->andWhere('TIME(ev.dateFin) BETWEEN TIME(:timeStart) AND TIME(:timeEnd)')
            ->andWhere('formAct.activite = :activite')
            ->orderBy('formAct.libelle', 'ASC')
            ->setParameter(':now', new \DateTime())
            ->setParameter(':timeStart', $timeStart)
            ->setParameter(':timeEnd', $timeEnd)
            ->setParameter(':activite', $activite)
        ;

        if (null !== $daysofweek && '' !== $daysofweek && '0' !== $daysofweek && 0 !== $daysofweek) {
            $qb->andWhere('DAYOFWEEK(ev.dateDebut) IN (:daysofweek)')
                ->setParameter(':daysofweek', $daysofweek)
            ;
        }

        if (null !== $idEtablissement && '' !== $idEtablissement && '0' !== $idEtablissement && 0 !== $idEtablissement) {
            $qb->innerJoin('formAct.lieu', 'l')
                ->andWhere('l.etablissement = :idEtablissement')
                ->setParameter('idEtablissement', $idEtablissement)
            ;
        }

        return $qb->getQuery()
                ->setHint(Query::HINT_READ_ONLY, true)
                ->getResult()
        ;
    }
}
