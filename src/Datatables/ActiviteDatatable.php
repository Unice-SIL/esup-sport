<?php

/*
 * Classe - ActiviteDatable:
 * 
 * COntient les champs à afficher pour les activités.
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Style;
use App\Datatables\Button\DescendreButton;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\MonterButton;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Button\VoirButton;
use App\Entity\Uca\Activite;

class ActiviteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $qb = $this->em->createQueryBuilder();
        $qb1 = $this->em->createQueryBuilder();
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'classeActivite.id',
        ]);

        $this->columnBuilder
            ->add(null, ActionColumn::class, [
                'title' => '',
                'actions' => [
                    (new MonterButton($this, null, ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                    (new DescendreButton($this, null, ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                ],
            ])

            ->add('ordre', Column::class, [
                'title' => $this->translator->trans('common.ordre'),
                'orderable' => true,
            ])

            ->add('image', ImageColumn::class, [
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'class_name' => 'hide-column-sm',
                'orderable' => false,
            ])
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('common.libelle'),
                'searchable' => true,
                'orderable' => true,
            ])
            ->add('description', Column::class, [
                'title' => $this->translator->trans('common.description'),
                'searchable' => true,
                'class_name' => 'hide-column',
            ])
            ->add('classeActivite.libelle', Column::class, [
                'title' => $this->translator->trans('classeactivite.libelle'),
                'class_name' => 'hide-column-md',
            ])

            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_ActiviteVoir', ['id' => 'id']))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_ActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Activite', 'objectId' => 'id'], 'ROLE_GESTION_ACTIVITE_ECRITURE'))->getConfig(),
                ],
            ])
            ->add('max_ordre', Column::class, [
                'visible' => false,
                'dql' => '('.$qb->select('MAX(a1.ordre)')
                    ->from(Activite::class, 'a1')
                    ->getDQL().')',
                'type_of_field' => 'string',
                'searchable' => false,
            ])
            ->add('min_ordre', Column::class, [
                'visible' => false,
                'dql' => '('.$qb1->select('MIN(a2.ordre)')
                    ->from(Activite::class, 'a2')
                    ->getDQL().')',
                'type_of_field' => 'string',
                'searchable' => false,
            ])
        ;
        $this->options->set([
            'row_id' => 'id',
            'order' => [[3, 'asc']],
            'classes' => Style::BOOTSTRAP_4_STYLE,
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
