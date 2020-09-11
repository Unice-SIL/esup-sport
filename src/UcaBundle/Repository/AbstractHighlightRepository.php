<?php

/*
 * Classe - AbstractHighlightRepository:
 *
 * Classe mère des pour les repositories des highlight
*/

namespace UcaBundle\Repository;

abstract class AbstractHighlightRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllExceptFirstChoose($id)
    {
        $qb = $this->createQueryBuilder('h')
            ->where('h.id <> :id')
            ->setParameter('id', $id)
            ->orderBy('h.ordre', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findFirstExceptFirstChoose($id)
    {
        $qb = $this->createQueryBuilder('h')
            ->where('h.id <> :id')
            ->setParameter('id', $id)
            ->orderBy('h.ordre', 'ASC')
            ->setMaxResults(3)
        ;

        return $qb->getQuery()->getResult();
    }

    public function max($field)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('MAX(a.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }
}
