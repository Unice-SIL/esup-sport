<?php

namespace UcaBundle\Datatables\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Andx;
use DateTime;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Orx;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use UcaBundle\Entity\Commande;

class DateFilter extends TextFilter
{

    public $attributes;

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'attributes' => null
        ));

        $resolver->setAllowedTypes('attributes', array('null', 'array'));
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
   * @param Composite    $expr
   * @param QueryBuilder $qb
   * @param string       $searchType
   * @param string       $searchField
   * @param mixed        $searchValue
   * @param string       $searchTypeOfField
   * @param int          $parameterCounter
   *
   * @return Composite
   */
  protected function getExpression(Composite $expr, QueryBuilder $qb, $searchType, $searchField, $searchValue, $searchTypeOfField, &$parameterCounter)
  {

    $parameterCounter++;
    $date = DateTime::createFromFormat('d/m/Y', $searchValue);
    if($date){
      $qb->andWhere('commande.datePaiement >= :date_start')
      ->andWhere('commande.datePaiement <= :date_end')
      ->setParameter('date_start', $date->format('Y-m-d 00:00:00'))
      ->setParameter('date_end',   $date->format('Y-m-d 23:59:59'));
    }
    return $expr;
  }
}
