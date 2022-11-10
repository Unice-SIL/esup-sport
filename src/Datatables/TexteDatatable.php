<?php

/*
 * Classe - TexteDatatable
 *
 * COntient les colonnes à afficher pour la liste des zone de texte editable
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\BooleanColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;

class TexteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('emplacement', Column::class, [
                'title' => $this->translator->trans('common.emplacement'),
                'visible' => true,
            ])
            ->add('titre', Column::class, [
                'title' => $this->translator->trans('common.titre'),
            ])
            ->add('texte', Column::class, [
                'title' => $this->translator->trans('common.texte'),
            ])
            ->add('mobile', BooleanColumn::class, [
                'title' => $this->translator->trans('common.mobile'),
                'visible' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_TexteModifier', ['id' => 'id'], 'ROLE_GESTION_TEXTE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Texte', 'objectId' => 'id'], 'ROLE_GESTION_TEXTE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\Texte';
    }

    public function getName()
    {
        return 'texte_datatable';
    }
}
