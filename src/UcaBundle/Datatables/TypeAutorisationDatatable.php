<?php

/*
 * Classe - TypeAutorisationDatatable
 *
 * COntient les colonnes à afficher pour la liste des types d'autorisation
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;

class TypeAutorisationDatatable extends AbstractTranslatedDatatable
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
            ->add('comportement.libelle', Column::class, [
                'title' => $this->translator->trans('typeautorisation.comportement'),
                'class_name' => 'hide-column-sm',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_TypeAutorisationModifier', ['id' => 'id'], 'ROLE_GESTION_TYPE_AUTORISATION_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_TypeAutorisationSupprimer', ['id' => 'id'], 'ROLE_GESTION_TYPE_AUTORISATION_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'TypeAutorisation', 'objectId' => 'id'], 'ROLE_GESTION_TYPE_AUTORISATION_ECRITURE'))->getConfig(),
                ],
            ])
        ;
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
