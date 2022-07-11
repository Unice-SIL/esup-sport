<?php

/*
 * Classe - GestionCommandesDatatable:
 *
 * Affiche la liste des détails d'une commande.
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\NumberColumn;
use App\Datatables\Button\CommandeExportButton;
use App\Datatables\Button\VoirButton;
use App\Datatables\Column\TwigVirtualColumn;
use App\Datatables\Filter\RangeFilter;
use App\Datatables\Filter\SelectInVirtualColumnFilter;
use App\Entity\Uca\TypeAutorisation;

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
            'commandeDetails.type',
            'commandeDetails.typeArticle',
            'commandeDetails.libelle',
            'commandeDetails.etablissementRetraitCarte',
        ]);

        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);

        $optionsTypeAutorisations = $this->createCarteAutorisationOptions($this->em->getRepository(TypeAutorisation::class)->findBy(['comportement' => 4], ['libelle' => 'asc']));

        $this->columnBuilder
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
            ->add('numeroCommande', Column::class, [
                'title' => $this->translator->trans('common.numerocommande'),
                'searchable' => true,
            ])
            ->add('numeroRecu', Column::class, [
                'title' => $this->translator->trans('common.numerorecu'),
                'searchable' => true,
            ])
            ->add('utilisateur.nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
                'searchable' => true,
            ])
            ->add('utilisateur.prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
                'searchable' => true,
            ])
            // ->add('avoirCommandeDetails', TwigVirtualColumn::class, [
            //     'title' => $this->translator->trans('commande.avoir.posseder'),
            //     'twigTemplate' => 'AvoirCommandeDetails',
                /*'searchable' => true,
                'search_column' => 'avoirCommandeDetail',
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'classes' => 'selectCommande',
                    'initial_search' => '',
                    'select_options' => [
                        'oui' => $this->translator->trans('common.oui'),
                        'non' => $this->translator->trans('common.non'),
                    ],
                ]],*/
            // ])
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
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'classes' => 'selectCommande',
                    'initial_search' => '',
                    'select_search_types' => [
                        'panier' => 'like',
                        'termine' => 'like',
                        'annule' => 'like',
                        'apayer' => 'like',
                        'avoir' => 'like',
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
            ->add('commandeDetails', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.carte'),
                'twigTemplate' => 'CommandeAchatCarte',
                'searchable' => true,
                'search_column' => 'commandeDetails.libelle',
                'orderable' => true,
                'order_column' => 'commandeDetails.libelle',
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'classes' => 'selectCommande',
                    'initial_search' => '',                    
                    'select_search_types' => $optionsTypeAutorisations['values'],
                    'select_options' => $optionsTypeAutorisations['labels'],
                ]],
            ])
            ->add('etablissementRetraitCarte', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.carte.retrait'),
                'twigTemplate' => 'CommandeAchatCarteRetrait',
                'searchable' => true,
                'search_column' => 'commandeDetails.etablissementRetraitCarte',
                'orderable' => true,
                'order_column' => 'commandeDetails.etablissementRetraitCarte',
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'classes' => 'selectCommande',
                    'initial_search' => '',                    
                    'select_search_types' =>  [
                        '' => 'Any',
                        'oui' => 'isNotNull',
                        'non' => 'isNull'
                    ],
                    'select_options' => [
                        '' => $this->translator->trans('common.all'),
                        'oui' => $this->translator->trans('common.oui'),
                        'non' => $this->translator->trans('common.non')
                    ],
                ]],
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
        return 'App\Entity\Uca\Commande';
    }

    public function getName()
    {
        return 'Commande_datatable';
    }

    /**
     * Fonction qui permet de générer les array utile pour créer le filtre select par carte
     *
     * @param [type] $typeAutorisations
     * @return array
     */
    private function createCarteAutorisationOptions($typeAutorisations): array {
        $optionValues = $optionLabels = [];

        $optionValues['toutes'] = 'neq';
        $optionLabels['toutes'] = $this->translator->trans('common.toutescartes');

        foreach ($typeAutorisations as $typeAutorisation) {
            $optionValues[$typeAutorisation->getLibelle()] = 'eq';
            $optionLabels[$typeAutorisation->getLibelle()] = $typeAutorisation->getLibelle();
        }

        return ['values' => $optionValues, 'labels' => $optionLabels];
    }
}
