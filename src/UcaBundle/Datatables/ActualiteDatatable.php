<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;

class ActualiteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('ordre', Column::class, array(
                'title' => $this->translator->trans('common.ordre'),
                'visible' => true,
            ))
            ->add('titre', Column::class, array(
                'title' => $this->translator->trans('common.titre'),
                'searchable' => true,
                'orderable' => true
            ))
            ->add('texte', Column::class, array(
                'title' => $this->translator->trans('common.texte'),
                'searchable' => true,
                'orderable' => true
            ))
            ->add('image', ImageColumn::class, array(
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'ActualiteModifier', ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'ActualiteSupprimer', ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Actualite';
    }

    public function getName()
    {
        return 'Actualite_datatable';
    }
}
