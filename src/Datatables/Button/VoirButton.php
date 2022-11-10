<?php

/*
 * Classe - VoirButton
 *
 * Bouton pour afficbher une entr&e
*/

namespace App\Datatables\Button;

class VoirButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.voir';
        $this->icone = 'fas fa-eye';
        $this->bsClass = 'btn btn-dark btn-form';
    }
}
