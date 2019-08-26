<?php

namespace UcaBundle\Datatables\Button;

class AjouterButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.ajouter';
        $this->icone = 'fas fa-eye';
        $this->bsClass = 'btn btn-primary btn-form';
    }
}
