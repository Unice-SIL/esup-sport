<?php

/*
 * Classe - GroupeDatatable
 *
 * COntient les champs à afficher pour la table des groupes
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigDataColumn;

class GroupeDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('name', Column::class, [
                'title' => 'name',
                'visible' => false,
            ])
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('common.nom'),
                'class_name' => 'max-w-33 max-w-50-sm',
            ])
            ->add('roles', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.role'),
                'twigTemplate' => 'Array',
                'class_name' => 'max-w-33 hide-column-sm',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_GroupeVoir', ['groupName' => 'name']))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_GroupeModifier', ['groupName' => 'name'], 'ROLE_GESTION_GROUPE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_GroupeSupprimer', ['id' => 'id'], 'ROLE_GESTION_GROUPE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Groupe', 'objectId' => 'id'], 'ROLE_GESTION_GROUPE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
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
