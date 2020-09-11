<?php

/*
 * Classe - AbstractButton
 *
 * Classe abstraite ajoute un bouton à une colonne action du datatable
*/

namespace UcaBundle\Datatables\Button;

abstract class AbstractButton
{
    protected $datatable;

    protected $libelle = '';
    protected $icone = '';
    protected $bsClass = '';
    protected $attributsAdditionnels = [];

    private $route;
    private $params;
    private $droit;

    private $attributs;
    private $btnConfig;

    public function __construct($datatable, $route, $params, $droit = null)
    {
        $this->datatable = $datatable;
        $this->setButtonInfo();
        $this->route = $route;
        $this->params = $params;
        $this->droit = $droit;

        $this->attributs = array_merge([
            'rel' => 'tooltip',
            'title' => $this->datatable->getTranslator()->trans($this->libelle),
            'class' => $this->bsClass,
            'role' => 'button',
            'aria-label' => $this->datatable->getTranslator()->trans($this->libelle),
        ], $this->attributsAdditionnels);

        $this->btnConfig = [
            'route' => $this->route,
            'route_parameters' => $this->params,
            'button_value' => $this->datatable->getTranslator()->trans($this->libelle),
            'icon' => $this->icone,
            'attributes' => $this->attributs,
            'render_if' => $this->getRenderIf(),
        ];
    }

    abstract public function setButtonInfo();

    public function getConfig()
    {
        return $this->btnConfig;
    }

    public function getRenderIf()
    {
        if (null == $this->droit) {
            return null;
        }
        $droit = $this->droit;

        return function ($row) use ($droit) {
            return $this->datatable->getAuthorizationChecker()->isGranted($this->droit);
        };
    }
}
