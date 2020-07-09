<?php

namespace UcaBundle\Datatables\Button;

class CommandeExportAvoirButton extends ExportPdfButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return "génération d'avoir" == $row['operation'];
        };
    }
}
