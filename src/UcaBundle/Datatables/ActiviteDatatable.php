<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;

class ActiviteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('image', ImageColumn::class, array(
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'class_name' => 'hide-column-sm'
            ))
            ->add('classeActivite.id', Column::class, array(
                'title' => 'ClasseActivite Id',
                'visible' => false
            ))
            ->add('libelle', Column::class, array(
                'title' => $this->translator->trans('common.libelle'),
                'searchable' => true,
                'orderable' => true
            ))
            ->add('description', Column::class, array(
                'title' => $this->translator->trans('common.description'),
                'searchable' => true,
                'class_name' => 'hide-column'
            ))
            // ->add('image', ImageColumn::class, array(
            //     'title' => 'Image',
            //     'imagine_filter' => 'thumb_small',
            //     'relative_path' => 'upload/public/image',
            // ))
            ->add('classeActivite.libelle', Column::class, array(
                'title' => $this->translator->trans('classeactivite.libelle'),
                'class_name' => 'hide-column-md'
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    $this->getActionBoutonConfig('Voir', 'ActiviteVoir', ['id' => 'id']),
                    $this->getActionBoutonConfig('Modifier', 'ActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'ActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'Activite', 'objectId' => 'id']),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Activite';
    }

    public function getName()
    {
        return 'activite_datatable';
    }
}
