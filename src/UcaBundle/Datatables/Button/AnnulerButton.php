<?php

namespace UcaBundle\Datatables\Button;

class AnnulerButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.annuler';
        $this->icone = 'fas fa-trash';
        $this->bsClass = 'btn btn-danger btn-form';
        $this->attributsAdditionnels = ['data-toggle' => 'modal', 'data-target' => '#modalSuppression'];
    }
}
