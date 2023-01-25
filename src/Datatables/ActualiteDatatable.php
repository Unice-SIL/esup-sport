<?php

/*
 * Classe - ActualiteDatatable
 *
 * COntient les champs à afficher pour les actualités
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Style;
use App\Datatables\Button\DescendreButton;
use App\Datatables\Button\InvisibleButton;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\MonterButton;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Column\TwigDataColumn;
use App\Entity\Uca\Actualite;

class ActualiteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $qb = $this->em->createQueryBuilder();
        $qb1 = $this->em->createQueryBuilder();
        $this->setUcaDefault();
        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => '',
                'class_name' => 'align-middle',
                'actions' => [
                    (new MonterButton($this, null, ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                    (new DescendreButton($this, null, ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                ],
            ])
            ->add('ordre', Column::class, [
                'title' => $this->translator->trans('common.ordre'),
                'orderable' => true,
            ])
            ->add('titre', Column::class, [
                'title' => $this->translator->trans('common.titre'),
                'searchable' => true,
                'orderable' => true,
            ])
            ->add('texte', Column::class, [
                'title' => $this->translator->trans('common.texte'),
                'searchable' => true,
                'orderable' => true,
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
                    (new ModifierButton($this, 'UcaGest_ActualiteModifier', ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ActualiteSupprimer', ['id' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Actualite', 'objectId' => 'id'], 'ROLE_GESTION_ACTUALITE_ECRITURE'))->getConfig(),
                ],
            ])
            ->add('max_ordre', Column::class, [
                'visible' => false,
                'dql' => '('.$qb->select('MAX(a1.ordre)')
                    ->from(Actualite::class, 'a1')
                    ->getDQL().')',
                'type_of_field' => 'string',
                'searchable' => false,
            ])
            ->add('min_ordre', Column::class, [
                'visible' => false,
                'dql' => '('.$qb1->select('MIN(a2.ordre)')
                    ->from(Actualite::class, 'a2')
                    ->getDQL().')',
                'type_of_field' => 'string',
                'searchable' => false,
            ])
        ;

        $this->options->set([
            'classes' => '',
            'row_id' => 'id',
            'order' => [[2, 'asc']],
            'classes' => Style::BOOTSTRAP_4_STYLE,
            'search_in_non_visible_columns' => true,
        ]);
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\Actualite';
    }

    public function getName()
    {
        return 'Actualite_datatable';
    }
}
