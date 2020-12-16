<?php

namespace UcaBundle\Datatables\Filter;

use DateTime;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Imagine\Image\ImageInterface;
use Sg\DatatablesBundle\Datatable\Filter\AbstractFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RangeFilter extends AbstractFilter
{
    public function apply(ImageInterface $image)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@Uca/Datatables/Filter/RangeFilter.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function addAndExpression(Andx $andExpr, QueryBuilder $qb, $searchField, $searchValue, $searchTypeOfField, &$parameterCounter)
    {
        list($_dateStart, $_dateEnd) = explode(' - ', $searchValue);
        $dateStart = new DateTime($_dateStart);
        $dateEnd = new DateTime($_dateEnd);
        $dateEnd->setTime(23, 59, 59);

        $andExpr = $this->getBetweenAndExpression($andExpr, $qb, $searchField, $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $parameterCounter);
        $parameterCounter += 2;

        return $andExpr;
    }

    /**
     * @return $this
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->remove('search_type');

        return $this;
    }

    /**
     * Returns the type for the <input> element.
     *
     * @return string
     */
    public function getType()
    {
        return 'text';
    }
}
