<?php

/*
 * Classe - CommandeExportPaiementButton
 *
 * Bouton d'export des paiements (comamndes pâyées par crédit) (flitre render_if)
*/

namespace UcaBundle\Datatables\Button;

class CommandeExportPaiementButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return "Règlement d'une commande" == $row['operation'];
        };
    }
}
