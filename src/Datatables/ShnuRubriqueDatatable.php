<?php

/*
 * Classe - ShnuHighlight
 *
 * COntient les colonnes à afficher pour la liste des highlight poru le sport de haut niveau
*/

namespace App\Datatables;

use App\Datatables\Button\DescendreButton;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\MonterButton;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Column\TwigVirtualColumn;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Style;

class ShnuRubriqueDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'texte',
            'lien',
        ]);

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => '',
                'actions' => [
                    (new MonterButton($this, null, ['id' => 'id'], 'ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE'))->getConfig(),
                    (new DescendreButton($this, null, ['id' => 'id'], 'ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE'))->getConfig(),
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
            ->add('titre', Column::class, [
                'title' => $this->translator->trans('common.titre'),
                'searchable' => true,
                'orderable' => true,
            ])
            ->add('type.libelle', Column::class, [
                'title' => $this->translator->trans('common.type'),
                'searchable' => true,
                'orderable' => true,
            ])
            ->add('Complement', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.complement'),
                'searchable' => false,
                'orderable' => false,
                'twigTemplate' => 'RubriqueInfo',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_ShnuRubriqueModifier', ['id' => 'id'], 'ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ShnuRubriqueSupprimer', ['id' => 'id'], 'ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ShnuRubrique', 'objectId' => 'id'], 'ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
        $this->options->set([
            'classes' => '',
            'row_id' => 'id',
            'order' => [[4, 'asc']],
            'classes' => Style::BOOTSTRAP_4_STYLE,
            'search_in_non_visible_columns' => true,
        ]);
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\ShnuRubrique';
    }

    public function getName()
    {
        return 'Rubrique_datatable';
    }
}
