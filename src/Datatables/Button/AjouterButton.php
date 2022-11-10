<?php

/*
 * Classe - AjouterButton
 *
 * Mise en forme des boutons ajouter
*/

namespace App\Datatables\Button;

class AjouterButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.ajouter';
        $this->icone = 'fas fa-eye';
        $this->bsClass = 'btn btn-primary btn-form';
    }
}
