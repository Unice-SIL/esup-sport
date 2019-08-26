<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Filter\LikeFilter;

class TraductionDatatable extends AbstractNotTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault(['options' => [
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order_cells_top' => true,
            'global_search_type' => 'like'
        ]]);

        $this->columnBuilder
            ->add('entity', Column::class, array(
                'title' => $this->translator->trans('column.entite'),
                'searchable' => false,
            ))
            ->add('field', Column::class, array(
                'title' => $this->translator->trans('column.champ'),
                'searchable' => false,
            ));
        foreach ($options['queryInfo']->getCols() as $alias => $col) {
            $config = [
                'title' => $this->translator->trans('column.' . $alias),
                'twigTemplate' => 'RowData',
                'field' => $alias,
                'type_of_field' => 'string',
                'orderable' => false,
                'visible' => strpos($col['config'], 'hidden') === false,
                'search_column' => $col['sql'],
                'order_column' => $col['sql']
            ];
            if (strpos($col['config'], 'write') === false && $config['visible'] == false) {
                $config['searchable'] = false;
            } else if(strpos($col['config'], 'write') === false && $config['visible'] == true) {
                $config['searchable'] = true;
            } else {
                $config['searchable'] = true;
                $config['filter'] = [SelectFilter::class, [
                    'select_search_types' => [
                        'all' => null,
                        'isnull' => 'isNull',
                        'notnull' => 'isNotNull'
                    ],
                    'select_options' => [
                        'all' => $this->translator->trans('traduction.tous'),
                        'isnull' => $this->translator->trans('traduction.pas.encore.traduit'),
                        'notnull' => $this->translator->trans('traduction.deja.traduit')
                    ]
                ]];
            }
            $this->columnBuilder->add($alias, TwigVirtualColumn::class, $config);
        }
        $this->columnBuilder->add(null, ActionColumn::class, [
            'title' => $this->translator->trans('sg.datatables.actions.title'),
            'actions' =>  [
                (new ModifierButton($this, 'UcaGest_TraductionModifier', ['id' => 'entityid', 'entity' => 'entity', 'field' => 'field'], 'ROLE_GESTION_TRADUCTION_ECRITURE'))->getConfig(),
            ]
        ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Annotation';
    }

    public function getName()
    {
        return 'Traduction_datatable';
    }
}
