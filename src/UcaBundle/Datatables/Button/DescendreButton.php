<?php

namespace UcaBundle\Datatables\Button;

class DescendreButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.descendre';
        $this->icone = 'fas fa-arrow-circle-down';
        $this->bsClass = 'btn btn-form js-descendre';
        $this->attributsAdditionnels = ['data-action' => 'descendre'];
    }
}
