<?php

namespace App\Service\Common;

class ListeVariables
{
    private $liste;

    public function __construct()
    {
        $this->liste = [
          'MailPourTousLesInscripts' => [
            'objet', 'message', 'formatActivite', 'dateDebut', 'dateFin'
          ],
          'AnulationCommande' => [
            'numeroCommande'
          ],
          'CommandeARegler' => [
            'numeroCommande', 'timerBds'
          ],
          'ErreurAnnulationInscription' => [
            'id_inscription'
          ],
          'ErreurMontantPaybox' => [
            'numeroCommande', 'montantPaybox', 'montantTotal'
          ],
          'ValidationCommande' => [
            'lienFacture', 'numeroCommande'
          ],
          'ContactEmail' => [
            'contact_from', 'objet', 'message'
          ],
          'ContactEncadrantEmail' => [
            'contact_from', 'message', 'event_date', 'event_start_hour', 'event_end_hour', 'format_activite'
          ],
          'ContactEmailing' => [
            'message', 'objet'
          ],
          'Desinscription' => [
            'inscription'
          ],
          'DesinscriptionPartenaire' => [
            'inscription'
          ],
          'InscriptionAvecValidation' => [
            'inscription', 'date', 'listeEncadrants'
          ],
          'InscriptionDemandeValidation' => [
            'date', 'prenom', 'nom', 'mail', 'inscription', 'statut', 'lienInscription'
          ],
          'InscriptionPartenaire' => [
            'inscription', 'prenom', 'nom', 'formatActivite', 'dateDebut', 'dateFin', 'etablissement', 'ressource', 'evenement',
            'lienInscription'
          ],
          'InscriptionRefusee' => [
            'date', 'inscription', 'motifAnnulation', 'commentaireAnnulation'
          ],
          'InscriptionValidee' => [
            'date', 'inscription', 'lienInscription', 'timerPanierApresValidation','timerPanier'
          ],
          'ConfirmationEmail' => [
            'user', 'lienPreInscription'
          ],
          'DemandeValidationEmail' => [
            'prenom', 'nom', 'lienUtilisateur'
          ],
          'PreInscriptionEmail' => [
            'nom', 'prenom'
          ],
          'RefusEmail' => [
          ],
          'UtilisateurDebloquerEmail' => [
          ],
          'UtilisateurBloquerEmail' => [
          ],
        ];
    }

    public function getListe()
    {
        return $this->liste;
    }
}
