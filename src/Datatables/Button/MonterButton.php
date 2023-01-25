<?php
/*
 * Classe -  MonterButton
 *
 * Bouton pour monter un élément du (classer) datatable
*/

namespace App\Datatables\Button;

class MonterButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.monter';
        $this->icone = 'fas fa-arrow-circle-up';
        $this->bsClass = 'btn-form js-monter my-2';
        $this->attributsAdditionnels = ['data-action' => 'monter'];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return $row['min_ordre'] != $row['ordre'] ;
        };
    }
}
