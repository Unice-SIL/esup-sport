<?php

namespace UcaBundle\Datatables\Button;

class VoirCreditButton extends VoirButton
{
    public function getRenderIf()
    {
        return function ($row) {
            return "génération d'avoir" == $row['operation'];
        };
    }
}
