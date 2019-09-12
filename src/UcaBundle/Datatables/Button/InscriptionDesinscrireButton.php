<?php

namespace UcaBundle\Datatables\Button;

class InscriptionDesinscrireButton extends AnnulerButton
{
    public function setButtonInfo()
    {
        parent::setButtonInfo();
        $this->libelle = 'bouton.desinscrire';
        $this->attributsAdditionnels = ['data-toggle' => 'modal', 'data-target' => '#modalDesinscription'];
    }
    
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['valide']) && ($row['creneau'] != null || $row['reservabilite'] != null || $row['formatActivite']['format'] == "FormatSimple");
        };
    }
}
