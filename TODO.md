# Non affecté
* Voir pourquoi datatable n'arrive pas a afficher les icones ex: glyphicon glyphicon-edit
* Revoir l'organisation des traductions. C'est trop le bazarre pour le moment.

# Damien
* Tarif  - Suppression ne fonctionne pas
         - Mettre en place un affichage des activités/ressources/créneaux/autorisations/autres liées aux tarifs à mettre à jour avant suppression
* DHTMLX - Ajouter des données en base depuis le calendrier
         - Supprimer des données en base depuis le calendrier 
         - Mettre en place la récurrence d'évènements

# Pierre
 - Bouton ajouter sur les datatable : supprimer si le role écriture n'est pas dispo
 - Utilisaeur : formulaire de modif a changer
 - FoS : page de connexion
 - FoS : ajout de roles aux groupe
 - Securite : tout sécuriser
 - Checker si le changement d'url modifie le calendrier

# Davy
* Etudier Loggable de DoctrineExtension
    * Mise en place sur ClasseActivite => OK
    * Mise en place d'une page ou on peut voir tous les logs => OK
    - Ajout de la possibilité de revenir sur une version précédente => OK mais on ne peut pas récuperer un enregistrement supprimé
    - A voir le comportement quand il y a des liens comme par exemple pour une activité
    - On ne gère pas non plus l'historisation des modification dans les autres langues que le français
* Trouver quelque chose pour l'édition d'un image dans les activités. Pour l'instant on ne peut pas le faire.
* Sauvegarder les intervalles de date quand on créer les créneaux
    * OK pour la création des intervalles à la création
    * OK pour la modification et la suppression
    - attention, il faudra vérifier qu'un créneau n'est lié à rien avant de proposer la suppresion ou la modifiction.
* Gestion des roles
    - Créer les droits et configurer les roles en conséquence
* Affichage du user logger dans la barre du menu ?
* Réorganisation du menu ?
* Le twig pour l'affichage des activité est commun dans la liste et dans Voir mais il se trouve dans le répertoire Column. Mieux organiser l'emplacement des fichiers.
* basculer la gestion des intervalles de date du CreneauController vers l'entité Creneau car c'est moche dans le controller !
* FlashBag : voir si le controller ne gère pas déjà ce que j'ai mis dans un service.
* Etudier DoctrineMigrationsBundle
* Voir avec Laura l'organisation des droits avec les groupes FOSUserBundle

TODO du 15/04/2019
* Encadrants
    - Créer des profis d'utilisateurs et leur affecter un profil d'encadrant
    - Affecter des encadrants à des formats d'activité (ajout d'une liste avec la liste des utilisateurs encadrants - possibilité d'en choisir plusieurs)
    - Sur le profil de l'utilisateur encadrant, afficher toutes les dates / créneaux des formats d'activités sur lesquels il est encadrant
        -> Si Davy a pu terminer avec le planning DHTMLX, afficher un planning en lecture seule avec la liste des créneaux / activités sur lesquelles il est encadrant
    - Mettre en place le bundle SwiftMailer, pour préparer les envois de mails
        -> Mettre en place un formulaire pour envoyer un email aux inscrits d'une activité (adresse fixe pour le moment)
        -> En face de chaque date/créneau sur le planning de l'encadrant (ou sur le calendrier si possible), Mettre en place un bouton qui affichera une popup pour envoyer un email aux inscrits à une activité (adresse mail fixe pour le moment)
        -> Lors de la suppression d'un format d'activité ou d'un créneau -> Prévoir l'envoi de mail automatique aux inscrits (adresse mail fixe pour le moment)

* Tarifs
    - Sur la page des /Tarifs, afficher le montant pour chacun des profils utilisateurs (Indiquer "Non défini" si le profil n'a pas de montant associé au tarif)
    - Lors de l'ajout d'un profil utilisateur, afficher une popup ("Veuillez mettre à jour tous les tarifs pour ce nouveau profil")
    - La suppression d'un tarif pète une erreur quand le tarif est affecté à quelquechose (format d'activité, autorisation, créneau ou ressource). Afficher plutôt un message dans une popup (Le tarif ne peut pas être supprimé car il est affecté à un élément. Merci d'affecter un nouveau tarif à ces éléments avant de le supprimer) -> Et afficher si possible les éléments concernés.
    - L'ajout d'un profil utilisateur (Popup : pensez à faire les tarifs pour ce nouveau profil!)

* Paiement en Ligne
    - Continuer le paiement via PayBox (Davy a déjà mis une bonne partie en place - Blocage sur le retour serveur du paiement)
    - Tester si besoin le bundle "lexik/paybox-bundle"

* Activités :   
    - Lors de l'ajout d'un encadrant sur un format d'activité ou sur un créneau, Renseigner à chaque saisie d'encadrant 1 des 3 choix suivants :
        * Enseignant
        * Moniteur Diplômé
        * Surveillant
    - Afficher sur la page de chaque format d'activité : les encadrants avec leur profil affecté (enseignat, moniteur diplomé, surveillant)

* Autorisations :
    - Pour les autorisations médicales, l'utilisateur doit pouvoir confirmer lors de son inscription via une case à cocher qu'il est en possession d'un certificat médical.
        -> Sur le formulaire d'ajout d'un type d'autorisation :
            - ajouter un booléen indiquant s'il doit y avoir une case à cocher pour valider son autorisation
            - Ajouter un champ texte pour afficher le message lié à la case à cocher
    - Ajouter aussi des catégories d'autorisation (Autorisations médicales, Cartes, Cotisation) : 1 seule catégorie autorisée

* Formats d'activité :
    - Sur les formats d'activité Simple :
        * Sélectionner s'il s'agit d'un évènement / d'une sortie / d'un achat de carte
        * S'il s'agit d'un achat de carte, afficher une liste déroulante des autorisations "Cartes" -> Une seule doit être sélectionnable