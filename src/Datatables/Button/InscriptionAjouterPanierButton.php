<?php

/*
 * Classe -  InscriptionAjoutePPanier:
 *
 * Bouton pour l'ajout au panier
*/

namespace App\Datatables\Button;

class InscriptionAjouterPanierButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.panier.ajouter';
        $this->icone = 'fas fa-shopping-basket';
        $this->bsClass = 'btn btn-primary btn-form';
        $this->attributsAdditionnels = [];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['attenteajoutpanier']);
        };
    }
}
