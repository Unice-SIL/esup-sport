<?php

/*
 * Classe - InscriptionAnnulerButton
 *
 * Bouton d'annualtion d'une inscription (filtre render_if)
*/

namespace App\Datatables\Button;

class InscriptionAnnulerButton extends AnnulerButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['initialise', 'attentevalidationencadrant', 'attentevalidationgestionnaire']);
        };
    }
}
