<?php

/*
 * Classe - VoirCommandeButton
 *
 * Bouton pour afficbher une commande (par opposition à un avoir)
*/

namespace UcaBundle\Datatables\Button;

class VoirCommandeButton extends VoirButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return "Règlement d'une commande" == $row['operation'];
        };
    }
}
