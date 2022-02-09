/* Gestion des emails */

_uca.mail = {};

/**
 * Function sendMailEncadrant()
 * Envoi un mail à l'encadrant d'une activité
 * @param: idEvent, idEncadrant 
 */
_uca.mail.sendMailEncadrant = function(idEvent, idEncadrant) {
    let id = "#contactEncadrant" + idEvent + idEncadrant;
    let textareaId = "#textcontactEncadrant" + idEvent + idEncadrant;
    let message = $(textareaId).val();
    $.ajax({
        url: Routing.generate("api_mailencadrant"),
        type: "POST",
        data: {
            message: message,
            event: idEvent,
            encadrant: idEncadrant,
        },
    }).done(function(data) {
        if (data != null) {
            if (data['response'] == "success") {
                setTimeout(function() { alert(Translator.trans('mail.success')) }, 2000);
                $(textareaId).val("");
                $(id).removeClass("show");
            } else {
                setTimeout(function() { alert(Translator.trans('mail.error')) }, 2000);
            }
        }
    }).fail(_uca.ajax.fail);
};


/* Gestion de l'emailing */
_uca.mail.emailing = {};

/**
 * Function: setListeDestinataires()
 * Construit la liste de destinataires de l'emailing
 * @param: idEmailing, idSend 
 */
_uca.mail.emailing.setListeDestinataires = function(boutonEmailing, boutonEnvoyer) {
    boutonEmailing.addEventListener('click', function(e) {
        _uca.ajax.showLoader();
        $.ajax({
            method: "GET",
            url: boutonEmailing.dataset.url
        }).done(function(data) {
            _uca.ajax.hideLoader();
            $('#modalMail').modal('toggle');
            let listeEmails = JSON.stringify(data.emails);
            document.getElementById('nbTotalDestinataires').innerHTML = data.nbDestinataires;
            boutonEnvoyer.dataset.destinataires = listeEmails;
            if ("[]" == listeEmails) {
                document.getElementById('messageErreurDestinataire').classList.remove("d-none");
                boutonEnvoyer.classList.add("disabled");
            }
        }).fail(_uca.ajax.fail);
    });
};

/**
 * Function: seturlListeDestinataires()
 * Construit l'url permettant d'accèder à la liste des destinataires
 * @param: boutonEmailing
 */
_uca.mail.emailing.seturlListeDestinataires = function(boutonEmailing) {
    var url = _uca.mail.emailing.url_emailing;

    if ($("#ucabundle_inscription_nom").val() == "") {
        url = url.replace('filtre_nom', null);
    } else {
        url = url.replace('filtre_nom', $('#ucabundle_inscription_nom').val());
    }
    if ($("#ucabundle_inscription_prenom").val() == "") {
        url = url.replace('filtre_prenom', null);
    } else {
        url = url.replace('filtre_prenom', $('#ucabundle_inscription_prenom').val());
    }
    url = url.replace('id_typeActivite', $('#ucabundle_inscription_type_activite').val());
    url = url.replace('id_classeActivite', $('#ucabundle_inscription_classe_activite').val());
    url = url.replace('id_activite', $('#ucabundle_inscription_activite').val());
    url = url.replace('id_formatActivite', $('#ucabundle_inscription_formatActivite').val());
    url = url.replace('id_creneau', $('#ucabundle_inscription_creneau').val());
    url = url.replace('id_encadrant', $('#ucabundle_inscription_encadrants').val());
    url = url.replace('id_etablissement', $('#ucabundle_inscription_etablissements').val());
    url = url.replace('id_lieu', $('#ucabundle_inscription_lieux').val());

    boutonEmailing.dataset.url = url;
};

/**
 * getEmailParameters()
 * Formate les données du formulaire d'envoi
 * @param: mail, objet, boutonEnvoyer 
 */
_uca.mail.emailing.getEmailParameters = function(mailContent, objetMail, boutonEnvoyer) {
    let mail = mailContent.value;
    let objet = objetMail.value;
    let dataEmail = boutonEnvoyer.dataset.destinataires;
    if ("[]" != dataEmail) {
        var dataForm = {
            "ucabundle_mail[mail]": mail,
            "ucabundle_mail[objet]": objet,
            destinataires: dataEmail,
        };

        return dataForm;
    }

    return false;
};

/**
 * Function: envoyerMail()
 * Envoi le mail aux destinataires
 * @param: idForm 
 */
_uca.mail.emailing.envoyerMail = function(idForm) {
    $(document).on('submit', idForm, function(event) {
        event.preventDefault();
        let data = _uca.mail.emailing.getEmailParameters(ucabundle_mail_mail, ucabundle_mail_objet, ucabundle_mail_save);
        if (false != data) {
            $.ajax({
                method: "POST",
                url: Routing.generate('UcaGest_EmailingEnvoyer'),
                data: data
            }).done(function(data) {
                if (true == data.success) {
                    $('#modalMail').modal('toggle');
                    $('#messageConfirmationMailSend').removeClass('d-none');
                    window.location.reload(true);
                } else {
                    $("#modalMail form").replaceWith(data.form);
                }
            }).fail(_uca.ajax.fail);
        }
    });
};