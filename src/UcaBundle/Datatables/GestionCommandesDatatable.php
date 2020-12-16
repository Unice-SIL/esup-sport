<?php

/*
 * Classe - GestionCommandesDatatable:
 *
 * Affiche la liste des détails d'une commande.
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\NumberColumn;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;
use UcaBundle\Datatables\Button\CommandeExportButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use UcaBundle\Datatables\Filter\RangeFilter;

class GestionCommandesDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault(['options' => [
            'individual_filtering' => true,
            'order_cells_top' => true,
            'global_search_type' => 'like',
        ],
            'features' => ['state_save' => false],
        ]);

        $this->addInvisibleColumns([
            'id',
            'statut',
            'datePaiement',
            'dateAnnulation',
            'dateCommande',
            'montantTotal',
            'avoirCommandeDetails.referenceAvoir',
            'commandeDetails.typeArticle',
            'commandeDetails.libelle',
        ]);

        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);

        $this->columnBuilder
            ->add('numeroCommande', Column::class, [
                'title' => $this->translator->trans('common.numerocommande'),
                'searchable' => true,
            ])
            ->add('numeroRecu', Column::class, [
                'title' => $this->translator->trans('common.numerorecu'),
                'searchable' => true,
            ])
            ->add('avoirCommandeDetails', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('commande.avoir.posseder'),
                'twigTemplate' => 'AvoirCommandeDetails',
                /*'searchable' => true,
                'search_column' => 'avoirCommandeDetail',
                'filter' => [SelectFilter::class, [
                    'classes' => 'selectCommande',
                    'initial_search' => '',
                    'select_options' => [
                        'oui' => $this->translator->trans('common.oui'),
                        'non' => $this->translator->trans('common.non'),
                    ],
                ]],*/
            ])
            ->add('utilisateur.nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
                'searchable' => true,
            ])
            ->add('utilisateur.prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
                'searchable' => true,
            ])
            ->add('montantTotal', NumberColumn::class, [
                'title' => $this->translator->trans('common.montant'),
                'formatter' => new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY),
                'use_format_currency' => true, // needed for \NumberFormatter::CURRENCY
                'currency' => 'EUR',
                'searchable' => true,
            ])
            ->add('statutTraduit', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.statut'),
                'twigTemplate' => 'Trans',
                'field' => 'statut',
                'searchable' => true,
                'search_column' => 'statut',
                'orderable' => true,
                'order_column' => 'statut',
                'filter' => [SelectFilter::class, [
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
                'searchable' => true,
            ])
            ->add('date', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'search_column' => 'datePaiement',
                'search_column' => 'dateAnnulation',
                'search_column' => 'dateCommande',
                'twigTemplate' => 'DateOnlyCommande',
                'orderable' => true,
                'order_column' => 'datePaiement',
                'searchable' => true,
                //'date_format' => 'L',
                'filter' => [RangeFilter::class, [
                    'cancel_button' => false,
                ]],
            ])
            ->add('commandeDetails', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.carte'),
                'twigTemplate' => 'CommandeAchatCarte',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_ReportingCommandeDetails', ['id' => 'id'], 'ROLE_GESTION_COMMANDES'))->getConfig(),
                    (new CommandeExportButton($this, 'UcaWeb_MesCommandesExport', ['id' => 'id']))->getConfig(),
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