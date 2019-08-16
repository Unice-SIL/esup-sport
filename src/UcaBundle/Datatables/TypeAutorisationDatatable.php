<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class TypeAutorisationDatatable extends AbstractTranslatedDatatable
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
            ->add('comportement.libelle', Column::class, array(
                'title' => $this->translator->trans('typeautorisation.comportement'),
                'class_name' => 'hide-column-sm'
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'TypeAutorisationModifier', ['id' => 'id'], 'ROLE_GESTION_TYPE_AUTORISATION_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'TypeAutorisationSupprimer', ['id' => 'id'], 'ROLE_GESTION_TYPE_AUTORISATION_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'TypeAutorisation', 'objectId' => 'id']),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\TypeAutorisation';
    }

    public function getName()
    {
        return 'TypeAutorisation_datatable';
    }
}
