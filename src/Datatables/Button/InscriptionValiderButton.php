<?php


namespace App\Datatables\Button;

class InscriptionValiderButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->icone = 'fas fa-check';
        $this->libelle = 'bouton.inscription.valider';
        $this->bsClass = 'btn btn-primary btn-form';
        $this->attributsAdditionnels = [];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return $row['commandeTermine'] === '1' && $row['typePaiement'] === '1';
        };
    }
}
