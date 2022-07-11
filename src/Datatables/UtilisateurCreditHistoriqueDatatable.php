<?php

/*
 * Classe - UtilisateurCreditHistoriqueDatatable
 *
 * COntient les colonnes à afficher pour le reporting crédit
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\DateTimeColumn;
use Sg\DatatablesBundle\Datatable\Column\NumberColumn;
use App\Datatables\Button\CommandeExportAvoirButton;
use App\Datatables\Button\CommandeExportPaiementButton;
use App\Datatables\Button\CreditAjouterExportButton;
use App\Datatables\Button\VoirCommandeButton;
use App\Datatables\Button\VoirCreditButton;
use App\Datatables\Filter\RangeFilter;

class UtilisateurCreditHistoriqueDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault(['options' => [
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order_cells_top' => true,
            'global_search_type' => 'like',
        ]]);

        $this->addInvisibleColumns([
            'id',
            'avoir',
            'utilisateur.id',
            'commandeAssociee',
        ]);

        $qb = $this->em->createQueryBuilder();

        $this->columnBuilder
            ->add('date', DateTimeColumn::class, [
                'title' => $this->translator->trans('common.date'),
                //'searchable' => true,
                'date_format' => 'L',
                'filter' => [RangeFilter::class, [
                    'cancel_button' => false,
                ]],
            ])
            ->add('utilisateur.nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
                'searchable' => true,
            ])
            ->add('utilisateur.prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
                'searchable' => true,
            ])
            ->add('operation', Column::class, [
                'title' => $this->translator->trans('common.operation'),
                'searchable' => true,
            ])
            ->add('statut', Column::class, [
                'title' => $this->translator->trans('common.statut'),
                'searchable' => true,
            ])
            ->add('montant', NumberColumn::class, [
                'title' => $this->translator->trans('common.montant'),
                'formatter' => new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY),
                'use_format_currency' => true, // needed for \NumberFormatter::CURRENCY
                'currency' => 'EUR',
                'searchable' => true,
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirCreditButton($this, 'UcaGest_AvoirDetails', ['id' => 'commandeAssociee', 'refAvoir' => 'avoir'], 'ROLE_GESTION_UTILISATEUR_LECTURE'))->getConfig(),
                    (new VoirCommandeButton($this, 'UcaGest_ReportingCommandeDetails', ['id' => 'commandeAssociee'], 'ROLE_GESTION_COMMANDES'))->getConfig(),
                    (new CommandeExportPaiementButton($this, 'UcaWeb_MesCommandesExport', ['id' => 'commandeAssociee']))->getConfig(),
                    (new CreditAjouterExportButton($this, 'UcaWeb_MesCreditsExport', ['id' => 'id']))->getConfig(),
                    (new CommandeExportAvoirButton($this, 'UcaWeb_MesAvoirsExport', ['id' => 'commandeAssociee', 'refAvoir' => 'avoir']))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\UtilisateurCreditHistorique';
    }

    public function getName()
    {
        return 'Utilisateur_credit_datatable';
    }
}
