// Formulaire de réinitialisation du mot de passe
let formulaireResetPassword = $("form[name='reset-password-form']");
// etat d'erreur du formulaire
let error = $("form[name='login-form']").data("error");

// Champs du formulaire
let fieldUsernameResetPassword = $("#username-reset-password");
let fieldPasswordResetPassword = $("#password-reset-password");
let fieldPasswordConfirmationResetPassword = $("#password-confirmation-reset-password");

// Modal
let modal = $('#modal-reset-password');

//à l'affichage de la modal, on cache l'alert de mot de passe identique
modal.on("show.bs.modal", function() {
        passwordMessageError.hide();
    })
    // Message d'erreur du modal reset
let passwordMessageError = $("#password-message-error");

// Etat spécifique lors du changement de mot de passe
if (formulaireResetPassword.data("instance") === "reset-password" && !error) {
    // On empèche le modal de se fermer et on l'ouvre
    modal.modal('show');

    // Focus sur le champ
    fieldPasswordResetPassword.focus();
}

// Gestionnaire d'évènement pour le lien d'oubli de mot de passe
$("form[name='login-form'] #forget-password").on("click", function() {
    // On désactive la redirection du lien
    event.preventDefault();

    // On set la valeur au champ du modal
    fieldUsernameResetPassword.val($("#username").val());

    // Affichage du modal
    modal.modal('show');

    // Focus sur le champ
    fieldUsernameResetPassword.focus();
});

// Gestionnaire de submit du formulaire
formulaireResetPassword.find("input[type='submit']").on("click", function() {
    // Etat d'instruction pour le changement de mot de passe
    if (formulaireResetPassword.data("instance") === "instruction-reset-password") {
        // Le champ username n'est pas vide
        if (fieldUsernameResetPassword.val() !== "") {
            // On désactive la redirection du formulaire
            event.preventDefault();

            // Ajax
            gestionAjax(formulaireResetPassword.data("instance"))
        }
    }
    // Etat permettant le changement de mot de passe
    else if (formulaireResetPassword.data("instance") === "reset-password") {
        // Les champs password ne sont vides
        if (fieldPasswordResetPassword.val() !== "" && fieldPasswordConfirmationResetPassword.val() !== "") {
            // On désactive la redirection du formulaire
            event.preventDefault();

            // Les champs password sont identiques
            if (fieldPasswordResetPassword.val() === fieldPasswordConfirmationResetPassword.val()) {
                // Ajax
                gestionAjax(formulaireResetPassword.data("instance"));
            }
            // Les champs password ne sont pas identiques
            else {
                passwordMessageError.show('slow')
                    .delay(4000)
                    .hide('fast');
            }
        }
    }
});

let screenWidth = $(window).width();
if (screenWidth < 540 || (screenWidth >= 768 && screenWidth < 1070)) {
    $('#username').attr('placeholder', 'Identifiant');
    $('#password').attr('placeholder', 'Mot de passe');
}


// Gestionnaire des requêtes Ajax en fonction des états
function gestionAjax(etat) {
    if (etat === "instruction-reset-password") {
        $.ajax({
            url: Routing.generate("instruction_reset_password"),
            method: "POST",
            dataType: "JSON",
            data: {
                username: fieldUsernameResetPassword.val(),
            },
            complete: function(resultat) {
                // On masque le modal
                modal.modal("hide");
                // Status à true, on affiche le popup de réussite
                if (resultat.responseJSON.status === true) {
                    // le systeme de flash notification va prendre le relais sur l'evenement "ajaxComplete" de jquery pour affichier le message de success
                    console.log(resultat.responseJSON.status)
                }
            }
        });
    } else if (etat === "reset-password") {
        $.ajax({
            url: Routing.generate("reset_password"),
            method: "POST",
            dataType: "JSON",
            data: {
                username: fieldUsernameResetPassword.val(),
                password: fieldPasswordResetPassword.val(),
            },
            complete: function(resultat) {
                // Status à true, on affiche le popup de réussite
                if (resultat.responseJSON.status === true) {
                    // le systeme de flash notification va prendre le relais sur l'evenement "ajaxComplete" de jquery pour affichier le message de success
                    console.log(resultat.responseJSON.status)
                }

                // On masque le modal
                modal.modal("hide");

                // Changement de l'instance
                formulaireResetPassword.data("instance", "instruction-reset-password");

                // On change les fields pour être à l'identique avec la précédente instance
                fieldUsernameResetPassword.prop("disabled", false);
                fieldPasswordResetPassword.parent().parent().addClass("d-none");
                fieldPasswordConfirmationResetPassword.parent().parent().addClass("d-none");
            }
        });
    }
}