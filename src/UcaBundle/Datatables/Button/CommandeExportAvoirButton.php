<?php

/*
 * Classe - CommandeExportAvoirButton
 *
 * Bouton d'export d'avoir (filtre render_if)
*/

namespace UcaBundle\Datatables\Button;

class CommandeExportAvoirButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['operation'], ["Génération d'avoir", "Report d'avoir"]);
        };
    }
}
