<?php

namespace UcaBundle\Datatables\Button;

class UtilisateurBloquerButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.bloquer';
        $this->icone = 'fas fa-lock';
        $this->bsClass = 'btn btn-danger btn-form';
        $this->attributsAdditionnels = [
            'data-toggle' => 'modal', 
            'data-target' => '#modalConfirmation'
        ];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return $row['enabled'] == 1 && $row['statut']['id'] == 1 && $this->datatable->getAuthorizationChecker()->isGranted('ROLE_GESTION_UTILISATEUR_ECRITURE');
        };
    }
}
