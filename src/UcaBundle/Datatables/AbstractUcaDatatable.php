<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\AbstractDatatable;
use Sg\DatatablesBundle\Datatable\Style;

abstract class AbstractUcaDatatable extends AbstractDatatable
{
    public function getActionBoutonConfig($action, $route, $params, $droit = null)
    {
        if ($action == 'Voir') {
            $libelle = 'bouton.voir';
            $icone = 'fas fa-eye';
            $bsClass = 'btn btn-dark btn-form';
            $attributsAdditionnels = [];
        } elseif ($action == 'Ajouter') {
            $libelle = 'bouton.ajouter';
            $icone = 'far fa-eye';
            $bsClass = 'btn btn-primary btn-form';
            $attributsAdditionnels = [];
        } elseif ($action == 'Modifier') {
            $libelle = 'bouton.modifier';
            $icone = 'fas fa-edit';
            $bsClass = 'btn btn-primary btn-form';
            $attributsAdditionnels = [];
        } elseif ($action == 'Supprimer') {
            $libelle = 'bouton.supprimer';
            $icone = 'fas fa-trash';
            $bsClass = 'btn btn-danger btn-form';
            $attributsAdditionnels = ['data-toggle' => 'modal', 'data-target' => '#modalSuppression'];
        } elseif ($action == 'Log') {
            $libelle = 'bouton.log';
            $icone = 'fas fa-history';
            $bsClass = 'btn btn-warning btn-form';
            $attributsAdditionnels = [];
        }

        $attributs = array_merge([
            'rel' => 'tooltip',
            'title' => $this->translator->trans($libelle),
            'class' => $bsClass,
            'role' => 'button',
            'aria-label' => $this->translator->trans($libelle)
        ], $attributsAdditionnels);

        $btnConfig =  [
            'route' => $route,
            'route_parameters' => $params,
            'button_value' => $this->translator->trans($libelle),
            'icon' => $icone,
            'attributes' => $attributs
        ];

        if ($droit !== null) {
            $btnConfig['render_if'] = function () use ($droit) {
                return $this->authorizationChecker->isGranted($droit);
            };
        }

        return $btnConfig;
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
        $this->callbacks->set(array_merge(['init_complete' => ['template' => "@Uca/Common/Modal/Modal.Suppression.js.twig"]], $callbacks));
        $this->features->set(array_merge([], $features));
    }
}
