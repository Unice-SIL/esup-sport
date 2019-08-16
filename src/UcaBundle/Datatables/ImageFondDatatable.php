<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;

class ImageFondDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('emplacement', Column::class, array(
                'title' => $this->translator->trans('common.emplacement'),
                'visible' => true,
            ))
            ->add('titre', Column::class, array(
                'title' => $this->translator->trans('common.libelle'),
                'searchable' => true,
                'orderable' => true,
                'class_name' => 'hide-column-sm'
            ))
            ->add('image', ImageColumn::class, array(
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'ImageFondModifier', ['id' => 'id'], 'ROLE_GESTION_TEXTE_ECRITURE')
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\ImageFond';
    }

    public function getName()
    {
        return 'ImageFond_datatable';
    }
}
