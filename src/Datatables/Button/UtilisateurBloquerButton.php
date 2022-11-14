<?php

/*
 * Classe - UtilisateurBloquerButton
 *
 * Bouton pour bloquer un utilisateur
*/

namespace App\Datatables\Button;

class UtilisateurBloquerButton extends AbstractButton
{
    public function setButtonInfo()
    {
        $this->libelle = 'bouton.bloquer';
        $this->icone = 'fas fa-lock';
        $this->bsClass = 'btn btn-danger btn-form';
        $this->attributsAdditionnels = [
            'data-toggle' => 'modal',
            'data-target' => '#modalConfirmation',
        ];
    }

    public function getRenderIf()
    {
        return function ($row) {
            return 1 == $row['enabled'] && null != $row['statut'] && 1 == $row['statut']['id'] && $this->datatable->getAuthorizationChecker()->isGranted('ROLE_GESTION_UTILISATEUR_ECRITURE');
        };
    }
}
