<?php

/*
 * Classe - ClasseActiviteDatatable:
 *
 * COntient les champs à afficher pour les classes d'activités
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\SupprimerButton;

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
                    (new ModifierButton($this, 'UcaGest_gestClasseActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_gestClasseActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ClasseActivite', 'objectId' => 'id'], 'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\ClasseActivite';
    }

    public function getName()
    {
        return 'classeactivite_datatable';
    }
}
