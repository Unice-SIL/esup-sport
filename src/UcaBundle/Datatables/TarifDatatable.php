<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Column\TwigVirtualColumn;

class TarifDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('libelle', Column::class, array(
                'title' => $this->translator->trans('common.libelle'),
            ))
            ->add('data', TwigVirtualColumn::class, array(
                'title' => $this->translator->trans('common.montants'),
                'twigTemplate' => 'MontantData',
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'TarifModifier', ['id' => 'id'], 'ROLE_GESTION_TARIF_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'TarifSupprimer', ['id' => 'id'], 'ROLE_GESTION_TARIF_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'Tarif', 'objectId' => 'id']),
                ]
            ]);
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
