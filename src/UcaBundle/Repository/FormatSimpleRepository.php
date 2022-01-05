<?php

/*
 * Classe - FormatSimpleRepository
 *
 * Requêtes à la base de données pour l'entité format simple
*/

namespace UcaBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;

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
