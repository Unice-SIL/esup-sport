<?php

/*
 * Classe - MesCréditsDatatable
 *
 * COntient les colonnes à afficher pour la page mes credits
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\NumberColumn;
use App\Datatables\Button\CommandeExportAvoirButton;
use App\Datatables\Button\CommandeExportPaiementButton;
use App\Datatables\Button\CreditAjouterExportButton;
use App\Datatables\Column\TwigDataColumn;

class MesCreditsDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'avoir',
            'commandeAssociee',
        ]);

        $this->columnBuilder
            ->add('date', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
            ])
            ->add('operation', Column::class, [
                'title' => $this->translator->trans('utilisateur.credit.operation'),
            ])
            ->add('typeOperation', Column::class, [
                'title' => $this->translator->trans('utilisateur.credit.typeoperation'),
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
                    (new CommandeExportAvoirButton($this, 'UcaWeb_MesAvoirsExport', ['id' => 'commandeAssociee', 'refAvoir' => 'avoir']))->getConfig(),
                    (new CommandeExportPaiementButton($this, 'UcaWeb_MesCommandesExport', ['id' => 'commandeAssociee']))->getConfig(),
                    (new CreditAjouterExportButton($this, 'UcaWeb_MesCreditsExport', ['id' => 'id']))->getConfig(),
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
        return 'UcaWeb_Credit_datatable';
    }
}
