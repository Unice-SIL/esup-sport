<?php

/*
 * Classe -  ExportPdfButton
 *
 * Bouton pour l'exportPdf (classer) un datatable
*/

namespace App\Datatables\Button;

class ExportPdfButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.exportpdf';
        $this->icone = 'fas fa-file-download';
        $this->bsClass = 'btn btn-primary btn-form';
        $this->attributsAdditionnels = ['target' => '_blank'];
    }
}
