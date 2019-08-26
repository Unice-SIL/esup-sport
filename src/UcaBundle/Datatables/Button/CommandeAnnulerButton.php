<?php

namespace UcaBundle\Datatables\Button;

class CommandeAnnulerButton extends AnnulerButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return in_array($row['statut'], ['apayer']);
        };
    }
}
