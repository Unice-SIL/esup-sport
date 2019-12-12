<?php

namespace UcaBundle\Datatables\Filter;

use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;

class TwoDatesFilter extends TextFilter
{
    /**
     * Get an expression.
     *
     * @param string $searchType
     * @param string $searchField
     * @param mixed  $searchValue
     * @param string $searchTypeOfField
     * @param int    $parameterCounter
     *
     * @return Composite
     */
    protected function getExpression(Composite $expr, QueryBuilder $qb, $searchType, $searchField, $searchValue, $searchTypeOfField, &$parameterCounter)
    {
        if (false !== stripos($searchValue, '--')) {
            $orX = $qb->expr()->orX();
            $searchValueExploded = explode('|', $searchValue);
            if (!empty($searchValueExploded[1]) && !empty($searchValueExploded[2])) {
                $orX->add($searchField.' BETWEEN :dateDebut AND :dateFin');
                ++$parameterCounter;
                $qb->setParameter('dateDebut', $searchValueExploded[1]);
                ++$parameterCounter;
                $qb->setParameter('dateFin', $searchValueExploded[2]);
            } elseif (!empty($searchValueExploded[1]) && empty($searchValueExploded[2])) {
                $orX->add($searchField.' > :dateDebut');
                ++$parameterCounter;
                $qb->setParameter('dateDebut', $searchValueExploded[1]);
            } elseif (empty($searchValueExploded[1]) && !empty($searchValueExploded[2])) {
                $orX->add($searchField.' < :dateFin');
                ++$parameterCounter;
                $qb->setParameter('dateFin', $searchValueExploded[2]);
            }

            $expr->add($orX);
        }

        return $expr;
    }
}
