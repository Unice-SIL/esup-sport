<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class ClasseActiviteDatatable extends AbstractTranslatedDatatable
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
                'title' => $this->translator->trans('common.libelle')
            ))
            ->add('typeActivite.libelle', Column::class, array(
                'title' => $this->translator->trans('common.type.activite'),
                'class_name' => 'hide-column-sm'
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    $this->getActionBoutonConfig('Modifier', 'ClasseActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'ClasseActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'ClasseActivite', 'objectId' => 'id']),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\ClasseActivite';
    }

    public function getName()
    {
        return 'classeactivite_datatable';
    }
}
