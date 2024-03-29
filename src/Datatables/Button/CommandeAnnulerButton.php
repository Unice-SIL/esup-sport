<?php

/*
 * Classe - CommandeAnnulerButton
 *
 * Annulation de commande (filtre render_if)
*/

namespace App\Datatables\Button;

class CommandeAnnulerButton extends AnnulerButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['apayer']);
        };
    }
}
