<?php

/*
 * Classe - GestionPanierDatatable:
 *
 * Affichage des éléments du panier
 */

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use UcaBundle\Datatables\Filter\DateFilter;

class GestionPanierDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'statut',
            'dateCommande',
            'montantTotal',
        ]);

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('numeroCommande', Column::class, [
                'title' => $this->translator->trans('common.numerocommande'),
                'class_name' => 'hide-column-sm',
            ])
            ->add('utilisateur.nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
            ])
            ->add('utilisateur.prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
            ])
            ->add('dateCommande', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'search_column' => 'date',
                'twigTemplate' => 'DateOnly',
                'searchable' => true,
                'filter' => [DateFilter::class, [
                    'classes' => 'datetimepicker',
                    'attributes' => ['data-datetimepicker-format' => 'd/m/Y'],
                ]],
            ])
            ->add('montantTotalFormated', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.montant'),
                'field' => 'montantTotal',
                'twigTemplate' => 'Montant',
                'class_name' => 'hide-column-md',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaWeb_CommandeEnAttenteVoir', ['id' => 'id'], 'ROLE_GESTION_PAIEMENT_COMMANDE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaWeb_CommandeEnAttenteSupprimer', ['id' => 'id'], 'ROLE_GESTION_PAIEMENT_COMMANDE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Commande';
    }

    public function getName()
    {
        return 'Commande_datatable';
    }
}
