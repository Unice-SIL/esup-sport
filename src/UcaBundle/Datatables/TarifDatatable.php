<?php

/*
 * Classe - TarifDatatable
 *
 * COntient les colonnes à afficher pour la liste des Tarifs
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Column\TwigDataColumn;
use UcaBundle\Datatables\Column\TwigVirtualColumn;

class TarifDatatable extends AbstractTranslatedDatatable
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
            ->add('data', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.montants'),
                'twigTemplate' => 'MontantData',
            ])
            ->add('pourcentageTVA', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.tva'),
                'twigTemplate' => 'TVAData',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_TarifModifier', ['id' => 'id'], 'ROLE_GESTION_TARIF_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_TarifSupprimer', ['id' => 'id'], 'ROLE_GESTION_TARIF_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Tarif', 'objectId' => 'id'], 'ROLE_GESTION_TARIF_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Tarif';
    }

    public function getName()
    {
        return 'Tarif_datatable';
    }
}
