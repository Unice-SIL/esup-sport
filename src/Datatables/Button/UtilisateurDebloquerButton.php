<?php

/*
 * Classe - UtilisateurDebloquerButton
 *
 * Bouton pour débbloquer un utilisateur
*/

namespace App\Datatables\Button;

class UtilisateurDebloquerButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.debloquer';
        $this->icone = 'fas fa-unlock';
        $this->bsClass = 'btn btn-success btn-form';
        $this->attributsAdditionnels = [
            'data-toggle' => 'modal',
            'data-target' => '#modalConfirmation',
        ];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return 0 == $row['enabled'] && 4 == $row['statut']['id'] && $this->datatable->getAuthorizationChecker()->isGranted('ROLE_GESTION_UTILISATEUR_ECRITURE');
        };
    }
}
