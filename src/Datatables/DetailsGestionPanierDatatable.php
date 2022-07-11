<?php

/*
 * Classe - DetailsGestionPanierDatatable:
 *
 * COntient les champs à afficher pour le détail du panier (commande)
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Column\TwigDataColumn;

class DetailsGestionPanierDatatable extends AbstractTranslatedDatatable
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
            ])
            ->add('commande.statut', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.statut'),
                'twigTemplate' => 'Trans',
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
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new SupprimerButton($this, 'UcaWeb_ArticleSupprimer', ['id' => 'id'], 'ROLE_GESTION_PAIEMENT_COMMANDE'))->getConfig(),
                ],
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
