<?php

/*
 * Classe - UtilisateurCreditHistoriqueDatatable
 *
 * COntient les colonnes à afficher pour le reporting crédit
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\NumberColumn;
use UcaBundle\Datatables\Button\CommandeExportAvoirButton;
use UcaBundle\Datatables\Button\CommandeExportPaiementButton;
use UcaBundle\Datatables\Button\CreditAjouterExportButton;
use UcaBundle\Datatables\Button\VoirCommandeButton;
use UcaBundle\Datatables\Button\VoirCreditButton;
use  UcaBundle\Datatables\Column\TwigDataColumn;

class UtilisateurCreditHistoriqueDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'avoir',
            'utilisateur.id',
            'commandeAssociee',
        ]);

        $qb = $this->em->createQueryBuilder();

        $this->columnBuilder

            ->add('date', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
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
            ])
            ->add('statut', Column::class, [
                'title' => $this->translator->trans('common.statut'),
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
                    (new VoirCommandeButton($this, 'UcaGest_ReportingCommandeDetails', ['id' => 'commandeAssociee'], 'ROLE_GESTION_COMMANDE'))->getConfig(),
                    (new CommandeExportPaiementButton($this, 'UcaWeb_MesCommandesExport', ['id' => 'commandeAssociee']))->getConfig(),
                    (new CreditAjouterExportButton($this, 'UcaWeb_MesCreditsExport', ['id' => 'id']))->getConfig(),
                    (new CommandeExportAvoirButton($this, 'UcaWeb_MesAvoirsExport', ['id' => 'commandeAssociee', 'refAvoir' => 'avoir']))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\UtilisateurCreditHistorique';
    }

    public function getName()
    {
        return 'Utilisateur_credit_datatable';
    }
}
