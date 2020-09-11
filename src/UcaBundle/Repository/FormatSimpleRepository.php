<?php

/*
 * Classe - FormatSimpleRepository
 *
 * Requêtes à la base de données pour l'entité format simple
*/

namespace UcaBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use UcaBundle\Service\Common\Previsualisation;

class FormatSimpleRepository extends FormatActiviteRepository
{
    public function PromotionsPagination($page, $nbMaxParPage)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->orderBy('a.dateDebutEffective', 'ASC');

        $this->previsualisation($qb, 'a');

        $query = $qb->getQuery();

        $premierResultat = ($page - 1) * $nbMaxParPage;
        $query->setFirstResult($premierResultat)->setMaxResults($nbMaxParPage);

        return new Paginator($query);
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

    public function findFormatSimpleByDate($date)
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->where('f.dateFinEffective < :date')
            ->setParameter('date', $date)
        ;

        return $qb->getQuery()->getResult();
    }
}
