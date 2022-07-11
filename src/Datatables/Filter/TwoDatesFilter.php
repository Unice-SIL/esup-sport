<?php

/*
 * Classe - TwoDatesFilter:
 *
 * Filtre les données d'une datatable entre deux dates données
*/

namespace App\Datatables\Filter;

use DateTime;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwoDatesFilter extends TextFilter
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
        return 'UcaBundle/Datatables/Filter/DateFilter.html.twig';
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
        // if (false !== stripos($searchValue, '--')) {
        // $orX = $qb->expr()->orX();
        // $searchValueExploded = explode('|', $searchValue);
        // if (!empty($searchValueExploded[1]) && !empty($searchValueExploded[2])) {
        //     $orX->add($searchField.' BETWEEN :dateDebut AND :dateFin');
        //     ++$parameterCounter;
        //     $qb->setParameter('dateDebut', $searchValueExploded[1]);
        //     ++$parameterCounter;
        //     $qb->setParameter('dateFin', $searchValueExploded[2]);
        // } elseif (!empty($searchValueExploded[1]) && empty($searchValueExploded[2])) {
        //     $orX->add($searchField.' > :dateDebut');
        //     ++$parameterCounter;
        //     $qb->setParameter('dateDebut', $searchValueExploded[1]);
        // } elseif (empty($searchValueExploded[1]) && !empty($searchValueExploded[2])) {
        //     $orX->add($searchField.' < :dateFin');
        //     ++$parameterCounter;
        //     $qb->setParameter('dateFin', $searchValueExploded[2]);
        // }

        // $expr->add($orX);

        ++$parameterCounter;
        $date = DateTime::createFromFormat('d/m/Y', $searchValue);

        if ($date) {
            $qb->andWhere('commande.datePaiement BETWEEN :date_start AND :date_end OR commande.dateAnnulation BETWEEN :date_start AND :date_end OR commande.dateCommande BETWEEN :date_start AND :date_end')
                ->setParameter('date_start', $date->format('Y-m-d 00:00:00'))
                ->setParameter('date_end', $date->format('Y-m-d 23:59:59'))
            ;
        }

        return $expr;
    }
}
