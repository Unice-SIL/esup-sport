<?php

/*
 * Classe - SuppressionButton
 *
 * Bouton de suppression d'une entrée
*/

namespace App\Datatables\Button;

class SupprimerButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.supprimer';
        $this->icone = 'fas fa-trash';
        $this->bsClass = 'btn btn-danger btn-form';
        $this->attributsAdditionnels = ['data-toggle' => 'modal', 'data-target' => '#modalSuppression'];
    }
}
