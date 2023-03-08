<?php

/*
 * Classe - TarifDatatable
 *
 * COntient les colonnes à afficher pour la liste des Tarifs
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;

class EmailDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('subject', Column::class, [
                'title' => $this->translator->trans('common.libelle'),
            ])
            ->add('nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_EmailModifier', ['id' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Email', 'objectId' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\Email';
    }

    public function getName()
    {
        return 'Email_datatable';
    }
}
