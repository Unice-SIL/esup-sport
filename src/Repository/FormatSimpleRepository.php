<?php

/*
 * Classe - FormatSimpleRepository
 *
 * Requêtes à la base de données pour l'entité format simple
*/

namespace App\Repository;

use App\Entity\Uca\FormatSimple;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class FormatSimpleRepository extends FormatActiviteRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatSimple::class);
    }

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
        // $qb->andWhere($qb->expr()->isInstanceOf('a', FormatSimple::class));

        return $qb->getQuery()->getResult();
    }
}
