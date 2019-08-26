<?php

namespace UcaBundle\Datatables\Button;

class ModifierButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.modifier';
        $this->icone = 'fas fa-edit';
        $this->bsClass = 'btn btn-primary btn-form';
    }
}
