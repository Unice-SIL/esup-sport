<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;
use UcaBundle\Datatables\Button\CommandeExportButton;
use UcaBundle\Datatables\Button\CommandeAnnulerButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigDataColumn;
use UcaBundle\Datatables\Column\TwigVirtualColumn;

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
                    'order' => [3, 'DESC']
                ],
                'callbacks' => [
                    'init_complete' => ['template' => "@Uca/Datatables/Callback/MesCommandesFilterChange.js.twig"],
                ]
            ]
        );

        $this->addInvisibleColumns([
            'id',
            'statut',
        ]);

        $this->columnBuilder
            ->add('dateCommande', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.dateCommande'),
                'twigTemplate' => 'Date',
                'searchable' => false,
            ))
            ->add('datePaiement', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.datepaiement'),
                'twigTemplate' => 'Date',
                'searchable' => false,
            ))
            ->add('moyenPaiement', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.moyenpaiement'),
                'twigTemplate' => 'Trans',
                'searchable' => false,
            ))
            ->add('statutTraduit', TwigVirtualColumn::class, array(
                'title' => $this->translator->trans('common.statut'),
                'twigTemplate' => 'Trans',
                'field' => 'statut',
                'searchable' => true,
                'search_column' => 'statut',
                'filter' => array(SelectFilter::class, array(
                    'classes' => 'selectCommande',
                    'initial_search' => 'apayer',
                    'select_search_types' => array(
                        'apayer' => 'like',
                        'panier' => 'neq',
                    ),
                    'select_options' => array(
                        'apayer' =>  $this->translator->trans('common.commandeencours'),
                        'panier' => $this->translator->trans('common.toutescommandes'),
                    ),
                ))
            ))
            ->add('montantTotal', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.montant'),
                'twigTemplate' => 'Montant',
                'searchable' => false,
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaWeb_MesCommandesVoir', ['id' => 'id'], 'ROLE_USER'))->getConfig(),
                    (new CommandeAnnulerButton($this, 'UcaWeb_MesCommandesAnnuler', ['id' => 'id']))->getConfig(),
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
