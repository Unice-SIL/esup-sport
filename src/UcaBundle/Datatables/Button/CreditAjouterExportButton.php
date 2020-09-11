<?php

namespace UcaBundle\Datatables\Button;

class CreditAjouterExportButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return 'Ajout manuel de crédit' == $row['operation'];
        };
    }
}
