<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Column\TwigDataColumn;


class DetailsCommandeDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('libelle', Column::class, array(
                'title' => $this->translator->trans('common.libelle'),
                'class_name' => 'hide-column-sm'
            ))
            ->add('montant', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.montant'),
                'twigTemplate' => 'Montant',
            ))
            ->add('dateAjoutPanier', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.dateajoutpanier'),
                'twigTemplate' => 'Date',
            ));
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\CommandeDetail';
    }

    public function getName()
    {
        return 'CommandeDetail_datatable';
    }
}
