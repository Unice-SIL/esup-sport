<?php

namespace UcaBundle\Datatables\Button;

class UtilisateurDebloquerButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.debloquer';
        $this->icone = 'fas fa-unlock';
        $this->bsClass = 'btn btn-success btn-form';
        $this->attributsAdditionnels = [
            'data-toggle' => 'modal', 
            'data-target' => '#modalConfirmation'
        ];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return $row['enabled'] == 0 && $row['statut']['id'] == 4 && $this->datatable->getAuthorizationChecker()->isGranted('ROLE_GESTION_UTILISATEUR_ECRITURE');
        };
    }
}
