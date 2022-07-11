<?php

/*
 * Classe - TwoDatesFilter:
 *
 * Filtre les données d'une datatable entre deux dates données
*/

namespace App\Datatables\Filter;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;

class SelectInVirtualColumnFilter extends SelectFilter
{
    /**
     * {@inheritdoc}
     */
    public function addAndExpression(Andx $andExpr, QueryBuilder $qb, $searchField, $searchValue, $searchTypeOfField, &$parameterCounter)
    {
        $searchField = $searchField[0];

        return parent::addAndExpression($andExpr, $qb, $searchField, $searchValue, $searchTypeOfField, $parameterCounter);
    }
}
