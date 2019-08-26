<?php

namespace UcaBundle\Datatables\Button;

class LogButton extends AbstractButton
{
    public function setButtonInfo()
    {        
        $this->libelle = 'bouton.log';
        $this->icone = 'fas fa-history';
        $this->bsClass = 'btn btn-warning btn-form';
    }
}
