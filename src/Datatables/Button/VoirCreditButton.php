<?php

/*
 * Classe - VoirCreditButton
 *
 * Bouton perrmettant de visualiser l'avoir associé au crédit (filtre rendeer_if)
*/

namespace App\Datatables\Button;

class VoirCreditButton extends VoirButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['operation'], ["Report d'avoir", "Génération d'avoir"]);
        };
    }
}
