<?php

namespace UcaBundle\Datatables\Button;

class CommandeExportButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['termine', 'avoir']) && '0.00' !== $row['montantTotal'];
        };
    }
}
