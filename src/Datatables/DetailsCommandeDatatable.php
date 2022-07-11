<?php

/*
 * Classe - DetailsCommandeDatatable:
 *
 * COntient les champs à afficher pour le détail d'uen commande
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Column\TwigDataColumn;

class DetailsCommandeDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('common.libelle'),
                'class_name' => 'hide-column-sm',
            ])
            ->add('montant', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.montant'),
                'twigTemplate' => 'Montant',
            ])
            ->add('dateAjoutPanier', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.dateajoutpanier'),
                'twigTemplate' => 'Date',
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\CommandeDetail';
    }

    public function getName()
    {
        return 'CommandeDetail_datatable';
    }
}
