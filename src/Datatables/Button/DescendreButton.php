<?php

/*
 * Classe -  DescendreButton
 *
 * Bouton pour descendre (classer) un élément du datatable
*/

namespace App\Datatables\Button;

class DescendreButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.descendre';
        $this->icone = 'fas fa-arrow-circle-down';
        $this->bsClass = 'btn-form js-descendre my-2';
        $this->attributsAdditionnels = ['data-action' => 'descendre'];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return $row['max_ordre'] != $row['ordre'] ;
        };
    }
}
