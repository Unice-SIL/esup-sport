<?php

/*
 * Classe - ShnuHighlight
 *
 * COntient les colonnes à afficher pour la liste des highlight poru le sport de haut niveau
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Style;
use UcaBundle\Datatables\Button\DescendreButton;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\MonterButton;
use UcaBundle\Datatables\Button\SupprimerButton;

class ShnuHighlightDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => '',
                'actions' => [
                    (new MonterButton($this, null, ['id' => 'id'], 'ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE'))->getConfig(),
                    (new DescendreButton($this, null, ['id' => 'id'], 'ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE'))->getConfig(),
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
            ->add('video', Column::class, [
                'title' => $this->translator->trans('common.url'),
                'searchable' => true,
                'orderable' => true,
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_ShnuHighlightModifier', ['id' => 'id'], 'ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ShnuHighlightSupprimer', ['id' => 'id'], 'ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ShnuHighlight', 'objectId' => 'id'], 'ROLE_GESTION_HIGHLIGHT_ECRITURE'))->getConfig(),
                ],
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
        return 'UcaBundle\Entity\ShnuHighlight';
    }

    public function getName()
    {
        return 'Highlight_datatable';
    }
}
