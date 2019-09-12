<?php

namespace UcaBundle\Datatables\Button;

class CommandeExportButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['termine']) && $row['montantTotal'] !== "0.00";
        };
    }
}
