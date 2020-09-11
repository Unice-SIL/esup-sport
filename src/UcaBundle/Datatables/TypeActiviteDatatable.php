<?php

/*
 * Classe - TypeActiviteDatatabl
 *
 * COntient les colonnes à afficher pour la liste des types d'activité
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;

class TypeActiviteDatatable extends AbstractTranslatedDatatable
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
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_TypeActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_TYPE_ACTIVITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_TypeActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_TYPE_ACTIVITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'TypeActivite', 'objectId' => 'id'], 'ROLE_GESTION_TYPE_ACTIVITE_ECRITURE'))->getConfig(),
                ], ])
        ;
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\TypeActivite';
    }

    public function getName()
    {
        return 'TypeActivite_datatable';
    }
}
