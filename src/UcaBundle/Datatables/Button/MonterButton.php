<?php

namespace UcaBundle\Datatables\Button;

class MonterButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.monter';
        $this->icone = 'fas fa-arrow-circle-up';
        $this->bsClass = 'btn btn-form js-monter';
        $this->attributsAdditionnels = ['data-action' => 'monter'];
    }
}
