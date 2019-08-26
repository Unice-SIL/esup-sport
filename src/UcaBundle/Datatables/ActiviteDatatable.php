<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Entity\Activite;

class ActiviteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'classeActivite.id',
        ]);

        $this->columnBuilder
            ->add('image', ImageColumn::class, array(
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'class_name' => 'hide-column-sm',
                'orderable' => false,
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
            ->add('classeActivite.libelle', Column::class, array(
                'title' => $this->translator->trans('classeactivite.libelle'),
                'class_name' => 'hide-column-md'
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_ActiviteVoir', ['id' => 'id']))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_ActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Activite', 'objectId' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'))->getConfig(),
                ]
            ]);
    }

    public function getEntity()
    {
        return Activite::class;
    }

    public function getName()
    {
        return 'activite_datatable';
    }
}
