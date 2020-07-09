<?php

namespace UcaBundle\Datatables\Button;

class CommandeExportPaiementButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return
                "Règlement d'une commande" == $row['operation']
                && in_array($row['statut'], ['termine', 'avoir'])
                && '0.00' !== $row['montantTotal']
            ;
        };
    }
}
