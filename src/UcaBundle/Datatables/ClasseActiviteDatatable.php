<?php

/*
 * Classe - ClasseActiviteDatatable:
 *
 * COntient les champs à afficher pour les classes d'activités
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;

class ClasseActiviteDatatable extends AbstractTranslatedDatatable
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
            ->add('typeActivite.libelle', Column::class, [
                'title' => $this->translator->trans('common.type.activite'),
                'class_name' => 'hide-column-sm',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_ClasseActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ClasseActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ClasseActivite', 'objectId' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
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
