<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Column\TwigDataColumn;

class GroupeDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('name', Column::class, array(
                'title' => $this->translator->trans('common.nom'),
            ))
            ->add('roles', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.role'),
                'twigTemplate' => 'Array',
                'class_name' => 'hide-column-sm'
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Voir', 'fos_user_group_show', ['groupName' => 'name']),
                    $this->getActionBoutonConfig('Modifier', 'fos_user_group_edit', ['groupName' => 'name'], 'ROLE_GESTION_GROUPE_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'GroupeSupprimer', ['id' => 'id'], 'ROLE_GESTION_GROUPE_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'Groupe', 'objectId' => 'id']),
                ]]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Groupe';
    }

    public function getName()
    {
        return 'Groupe_datatable';
    }
}
