<?php

/*
 * Classe : AbstractUcaDatatable:
 *
 * Classe mère des datatables toute les classes de datatable sont liees aux entites
 * les tableau (liste) affichent les données et boutons selectionner dans les classes filels
 * Dans certains cas des requêtes personnalitées ajoutent des flitres supplémentaires.
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\AbstractDatatable;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Style;

abstract class AbstractUcaDatatable extends AbstractDatatable
{
    public function getTranslator()
    {
        return $this->translator;
    }

    public function getAuthorizationChecker()
    {
        return $this->authorizationChecker;
    }

    public function addInvisibleColumns($cols = [])
    {
        foreach ($cols as $col) {
            $this->columnBuilder->add($col, Column::class, [
                'default_content' => '',
                'visible' => false,
            ]);
        }
    }

    public function setUcaDefault($params = [])
    {
        $language = isset($params['language']) ? $params['language'] : [];
        $ajax = isset($params['ajax']) ? $params['ajax'] : [];
        $options = isset($params['options']) ? $params['options'] : [];
        $callbacks = isset($params['callbacks']) ? $params['callbacks'] : [];
        $features = isset($params['features']) ? $params['features'] : [];

        $this->language->set(array_merge(['language_by_locale' => true], $language));
        $this->ajax->set(array_merge([], $ajax));
        $this->options->set(array_merge([
            // 'individual_filtering' => true,
            // 'individual_filtering_position' => 'head',
            // 'order_cells_top' => true,
            'classes' => Style::BOOTSTRAP_4_STYLE,
            'search_in_non_visible_columns' => true,
        ], $options));
        $this->callbacks->set(array_merge(
            ['draw_callback' => ['template' => 'UcaBundle/Common/Modal/Modal.Suppression.js.twig']],
            ['state_loaded' => ['template' => 'UcaBundle/Datatables/Callback/StateLoaded.js.twig']],
            $callbacks
        ));
        $this->features->set(array_merge(['processing' => true, 'state_save' => true], $features));
    }
}
