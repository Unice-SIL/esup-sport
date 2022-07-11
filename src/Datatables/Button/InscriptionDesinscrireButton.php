<?php

/*
 * Classe - InscriptionDesinscriptionButton
 *
 * Bouton de desincription à une activité
*/

namespace App\Datatables\Button;

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
            return in_array($row['statut'], ['valide', 'attentepartenaire']) && (null != $row['creneau'] || null != $row['reservabilite'] || 'FormatSimple' == $row['formatActivite']['format']);
        };
    }
}
