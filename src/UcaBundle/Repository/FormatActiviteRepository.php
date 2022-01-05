<?php

/*
 * Classe - FormatActiviteRepository
 *
 * Requêtes à la base de données pour l'entité format d'activité
 * Il s'agit de methodes globales aux trois formats
*/

namespace UcaBundle\Repository;

use UcaBundle\Service\Common\Previsualisation;

class FormatActiviteRepository extends \Doctrine\ORM\EntityRepository
{
    public function enCoursPublication($qb, $alias = 'f')
    {
        $qb->andWhere($alias.'.statut = 1')
            ->andWhere($alias.'.dateDebutPublication <= :today')
            ->andWhere($alias.'.dateFinPublication >= :today')
            ->setParameter('today', new \Datetime('now'))
        ;
    }

    public function findFormatPublie($activite = null, $user)
    {
        $qb = $this
            ->createQueryBuilder('f')
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

        if (!Previsualisation::$IS_ACTIVE) {
            $this->enCoursPublication($qb);
        }

        if (!empty($activite)) {
            $qb->andWhere('f.activite = :activite')
                ->setParameter('activite', $activite)
            ;
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function previsualisation($qb, $alias)
    {
        $now = new \DateTime();

        if (!Previsualisation::$IS_ACTIVE) {
            $qb
                ->andWhere($alias.'.dateDebutPublication < :date')
                ->andWhere($alias.'.dateFinPublication > :date')
                ->andWhere($alias.'.statut = 1')
                ->setParameter('date', $now->format('Y-m-d H:i:s'))
            ;
        } else {
            $this->enCoursPublication($qb, $alias);
        }

        return $qb;
    }

    public function findByPromouvoir()
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->andWhere('f.promouvoir = true')
            ->orderBy('f.dateDebutEffective', 'ASC')
        ;

        $this->previsualisation($qb, 'f');

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
