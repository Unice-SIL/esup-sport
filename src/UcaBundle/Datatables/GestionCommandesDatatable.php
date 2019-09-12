<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Column\TwigDataColumn;
use UcaBundle\Datatables\Filter\DateFilter;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;
use UcaBundle\Datatables\Button\CommandeExportButton;
use UcaBundle\Datatables\Button\ExportPdfButton;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use UcaBundle\Datatables\Button\VoirButton;

class GestionCommandesDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = []){

        $this->setUcaDefault(['options' => [
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order_cells_top' => true,
            'global_search_type' => 'like'
        ]]);
        

        $this->addInvisibleColumns([
            'id',
            'statut',
            'datePaiement',
            'dateAnnulation',
            'dateCommande',
            'montantTotal',
        ]);


        $this->columnBuilder
            
            ->add('commandeDetails.libelle', Column::class, array(
                'title' => 'commande',
                'visible' => false,
                'default_content' => ''
            ))
            ->add('commandeDetails.formatActivite', Column::class, array(
                'title' => 'commande',
                'visible' => false,
                'default_content' => ''
            ))
            ->add('numeroCommande', Column::class, array(
                'title' => $this->translator->trans('common.numerocommande'),
                'searchable' => false,
            ))
            ->add('numeroRecu', Column::class, array(
                'title' => $this->translator->trans('common.numerorecu'),
                'searchable' => false,
            ))
            ->add('utilisateur.nom', Column::class, array(
                'title' => $this->translator->trans('common.nom'),
                'searchable' => false,
            ))
            ->add('utilisateur.prenom', Column::class, array(
                'title' => $this->translator->trans('common.prenom'),
                'searchable' => false,
            
            ))
            ->add('montantTotalFormated', TwigVirtualColumn::class, array(
                'title' => $this->translator->trans('common.montant'),
                'field' => 'montantTotal',
                'twigTemplate' => 'Montant',
                'searchable' => true,
                'search_column' => 'montantTotal',
                'filter' => array(SelectFilter::class, array(
                    'select_search_types' => array(
                        '' => null,
                        '0' => 'neq',
                    ),
                    'select_options' => array(
                        '' => 'Tout montants',
                        '0' => 'Commandes non gratuites',
                    ),
                )),
            ))
            ->add('statutTraduit', TwigVirtualColumn::class, array(
                'title' => $this->translator->trans('common.statut'),
                'twigTemplate' => 'Trans',
                'field' => 'statut',
                'searchable' => true,
                'search_column' => 'statut',
                'orderable' => true,
                'order_column' => 'statut',
                'filter' => array(SelectFilter::class, array(
                    'classes' => 'selectCommande',
                    'initial_search' => '',
                    'select_search_types' => array(
                        'panier' => 'neq',
                        'termine' => 'eq',
                        'annule' => 'eq',
                        'apayer' => 'eq',
                    ),
                    'select_options' => array(
                        'panier' =>  $this->translator->trans('common.toutescommandes'),
                        'termine' =>  $this->translator->trans('common.termine'),
                        'annule' => $this->translator->trans('common.annule'),
                        'apayer' => $this->translator->trans('common.apayer'),
                    ),
                ))

            ))
            ->add('paiement', Column::class, array(
                'title' => $this->translator->trans('common.moyenpaiement'),
                'dql' => "CONCAT(commande.moyenPaiement, ' - ', commande.typePaiement)",
                'type_of_field' => 'string',
                'searchable' => false,
            ))
            ->add('date', TwigVirtualColumn::class, array(
                'title' => $this->translator->trans('common.date'),
                'search_column' => 'date',
                'twigTemplate' => 'DateOnlyCommande',
                'searchable' => true,
                'filter' => array(DateFilter::class, array(
                    'classes' => 'datetimepicker',
                    'attributes' => array('data-datetimepicker-format' => 'd/m/Y')
                ))
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_ReportingCommandeDetails', ['id' => 'id'], 'ROLE_GESTION_COMMANDES'))->getConfig(),
                    (new CommandeExportButton($this, 'UcaWeb_MesCommandesExport', ['id' => 'id']))->getConfig(),
                ]
            ]);
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
