<?php

/*
 * Classe - DatsFilter:
 *
 * Permet de flitrer une datatable selon une date
*/

namespace UcaBundle\Datatables\Filter;

use DateTime;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilter extends TextFilter
{
    public $attributes;

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'attributes' => null,
        ]);

        $resolver->setAllowedTypes('attributes', ['null', 'array']);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@Uca/Datatables/Filter/DateFilter.html.twig';
    }

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
        ++$parameterCounter;
        $date = DateTime::createFromFormat('d/m/Y', $searchValue);

        if ($date) {
            $qb->andWhere('commande.datePaiement BETWEEN :date_start AND :date_end')
                ->setParameter('date_start', $date->format('Y-m-d 00:00:00'))
                ->setParameter('date_end', $date->format('Y-m-d 23:59:59'))
      ;
        }

        return $expr;
    }
}
