<?php

/*
 * Classe - MesCommandesDatatable
 *
 * COntient les colonnes à afficher pour la page mes commandess
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\CommandeAnnulerButton;
use App\Datatables\Button\CommandeExportButton;
use App\Datatables\Button\VoirButton;
use App\Datatables\Column\TwigVirtualColumn;
use App\Datatables\Filter\DateFilter;
use App\Datatables\Filter\SelectInVirtualColumnFilter;

class MesCommandesDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault(
            [
                'options' => [
                    'individual_filtering' => true,
                    'individual_filtering_position' => 'head',
                    'order_cells_top' => true,
                    'global_search_type' => 'like',
                    'order' => [3, 'DESC'],
                ],
                'callbacks' => [
                    'init_complete' => ['template' => 'UcaBundle/Datatables/Callback/MesCommandesFilterChange.js.twig'],
                ],
            ]
        );

        $this->addInvisibleColumns([
            'id',
            'statut',
            'datePaiement',
            'dateAnnulation',
            'dateCommande',
            'montantTotal',
        ]);

        $this->columnBuilder
            ->add('numeroCommande', Column::class, [
                'title' => $this->translator->trans('common.numerocommande'),
                'searchable' => false,
            ])
            ->add('numeroRecu', Column::class, [
                'title' => $this->translator->trans('common.numerorecu'),
                'searchable' => false,
            ])
            ->add('montantTotalFormated', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.montant'),
                'field' => 'montantTotal',
                'twigTemplate' => 'Montant',
                'searchable' => true,
                'search_column' => 'montantTotal',
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'select_search_types' => [
                        '' => null,
                        '0' => 'neq',
                    ],
                    'select_options' => [
                        '' => 'Tout montants',
                        '0' => 'Commandes non gratuites',
                    ],
                ]],
            ])
            ->add('statutTraduit', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.statut'),
                'twigTemplate' => 'Trans',
                'field' => 'statut',
                'searchable' => true,
                'search_column' => 'statut',
                'orderable' => true,
                'order_column' => 'statut',
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'classes' => 'selectCommande',
                    'initial_search' => '',
                    'select_search_types' => [
                        'panier' => 'neq',
                        'termine' => 'eq',
                        'annule' => 'eq',
                        'apayer' => 'eq',
                        'avoir' => 'eq',
                    ],
                    'select_options' => [
                        'panier' => $this->translator->trans('common.toutescommandes'),
                        'termine' => $this->translator->trans('common.termine'),
                        'annule' => $this->translator->trans('common.annule'),
                        'apayer' => $this->translator->trans('common.apayer'),
                        'avoir' => $this->translator->trans('common.avoir'),
                    ],
                ]],
            ])
            ->add('paiement', Column::class, [
                'title' => $this->translator->trans('common.moyenpaiement'),
                'dql' => "CONCAT(commande.moyenPaiement, ' - ', commande.typePaiement)",
                'type_of_field' => 'string',
                'searchable' => false,
            ])
            ->add('date', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'search_column' => 'date',
                'twigTemplate' => 'DateOnlyCommande',
                'searchable' => true,
                'filter' => [DateFilter::class, [
                    'classes' => 'datetimepicker',
                    'attributes' => ['data-datetimepicker-format' => 'd/m/Y'],
                ]],
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaWeb_MesCommandesVoir', ['id' => 'id'], 'ROLE_USER'))->getConfig(),
                    (new CommandeAnnulerButton($this, 'UcaWeb_MesCommandesAnnuler', ['id' => 'id']))->getConfig(),
                    (new CommandeExportButton($this, 'UcaWeb_MesCommandesExport', ['id' => 'id']))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\Commande';
    }

    public function getName()
    {
        return 'Commande_datatable';
    }
}
