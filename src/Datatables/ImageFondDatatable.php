<?php

/*
 * Classe - ImageFond
 *
 * COntient les champs à afficher pour la table des iamges de fond
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;

class ImageFondDatatable extends AbstractTranslatedDatatable
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
                'title' => $this->translator->trans('common.libelle'),
                'searchable' => true,
                'orderable' => true,
                'class_name' => 'hide-column-sm',
            ])
            ->add('image', ImageColumn::class, [
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'orderable' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_ImageFondModifier', ['id' => 'id'], 'ROLE_GESTION_TEXTE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ImageFond', 'objectId' => 'id'], 'ROLE_GESTION_TEXTE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\ImageFond';
    }

    public function getName()
    {
        return 'ImageFond_datatable';
    }
}
