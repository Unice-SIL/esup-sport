/* Gestion des inscriptions */
_uca.inscription = {};

_uca.inscription.init = function() {
    _uca.inscription.htmlSpinner = $('#modalInscription .modal-dialog').html();
    _uca.inscription.type = '';
    _uca.inscription.id = '';
    _uca.inscription.idFormat = '';
    _uca.inscription.vData = '';
};

_uca.inscription.iframeAjaxLoad = function(data) {
    if ($(this).contents().attr('URL') == "about:blank") {
        return;
    }
    try {
        resultat = JSON.parse($(this).contents().find('pre').first().html());
        _uca.inscription.formValidation(resultat);
    } catch (e) {
        _uca.ajax.fail({ responseText: $(this).contents().find('html').html() });
    }
};

_uca.inscription.isFormatAvecReservation = function() {
    return $('#header-calendar').length;
}

_uca.inscription.formatAvecReservationValidation = function(data) {
    let calendarElement = $('.calendar-time-slot[elid="' + data.itemId + '"]');

    calendarElement.removeClass("notFull");
    calendarElement.addClass("register");
    calendarElement.find(".available").remove()

    //remove data on the register buttun to prevent the user to subscribe to the same event
    $('.js-inscription').each(function() {
        $(this).removeData("id");
    });
    document.getElementById('blocInscription').style.visibility = 'hidden';
}

_uca.inscription.formatAutreValidation = function(data) {
    let el = $('.js-inscription[data-id="' + data.itemId + '"]');
    $(el.parent()).html($("#js-text-inscrit-clone")[0].innerHTML);
    if (data.maxCreneauAtteint) {
        _uca.inscription.maxCreneauAtteint();
    }
}

_uca.inscription.maxCreneauAtteint = function() {
    let listeBoutonsInscription = document.querySelectorAll('.js-inscription');
    let boutonIndisponible = document.getElementById('js-text-indisponible-clone');
    // let boutonIndisponible = document.getElementById('js-text-indisponible-clone').cloneNode(true);
    listeBoutonsInscription.forEach(function(boutonInscription) {
        boutonInscription.parentElement.innerHTML = boutonIndisponible.innerHTML;
        // boutonInscription.parentElement.replaceChild(boutonIndisponible,boutonInscription);
        // boutonInscription.appendChild(boutonIndisponible);
    });
    $('[data-toggle="tooltip"]').tooltip();
};

_uca.inscription.formValidation = function(data) {
    if (data.error) {
        // On ajout un message flash

        $('h1').after(`
            <div class="alert alert-danger mx-3" role="alert">
                ${ data.error }
            </div>        
        `);

        $('.modal').modal('hide');
    } else {
        $('#modalInscription .modal-dialog').html($(data.html).find('.modal-dialog').html());
        // $('#form-inscription').submit(function (e) {
        //     // $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
        //     return true;
        // });
        $('#ajax-form-iframe').on('load', _uca.inscription.iframeAjaxLoad);
        if (data.statut == 0) {
            if (_uca.inscription.isFormatAvecReservation()) {
                _uca.inscription.formatAvecReservationValidation(data);
            } else {
                _uca.inscription.formatAutreValidation(data);
            }
        }
    }
};

_uca.inscription.addButtonEvent = function() {
    $(this).click(function() {
        _uca.inscription.type = $(this).data('type');
        _uca.inscription.id = $(this).data('id');
        _uca.inscription.idFormat = $(this).data('id-format');
        if (_uca.inscription.id == null) {
            return;
        }

        $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
        $('#modalInscription').modal();
        listenModalClosing();

        $.ajax({
                method: "POST",
                url: Routing.generate('UcaWeb_Inscription'),
                data: {
                    statut: 'confirmation',
                    type: _uca.inscription.type,
                    id: _uca.inscription.id,
                    idFormat: _uca.inscription.idFormat,
                }
            })
            .done(function(data) {
                let html_content = "";
                if (data.html) {
                    html_content = data.html;
                } else {
                    html_content = data;
                }

                $('#modalInscription .modal-dialog').html($(html_content).find('.modal-dialog').html());
                $('#modalInscription').modal();

                // Gestion de l'activation/desactivation du bouton d'ajout au panier pour le cas des partenaires
                const partenairesInputs = $('input[type="email"]');
                checkInputEmail(partenairesInputs);

                // Gestion de l'activation/desactivation du bouton d'ajout au panier pour le cas des partenaires
                checkInputAutorisations();

                listenModalClosing();

                $(".btn-confirmation").click(function() {
                    $('.modal').css('position', 'fixed');
                    if (this.value == 'true') {
                        if($('input[type="checkbox"][name="autorisation"]:not(:checked)').length > 0){
                            return;
                        }
                        
                        $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
                        $('#modalInscription').modal();

                        let partenaires = [];
                        if (partenairesInputs.length > 0) {
                            partenairesInputs.each(function(index, input) {
                                if ($(input).val() != '') {
                                    partenaires.push($(input).val());
                                }
                            });
                        }

                        $.ajax({
                                method: "POST",
                                url: Routing.generate('UcaWeb_Inscription'),
                                data: {
                                    statut: 'validation',
                                    type: _uca.inscription.type,
                                    id: _uca.inscription.id,
                                    idFormat: _uca.inscription.idFormat,
                                    partenaires: partenaires
                                }
                            })
                            .done(_uca.inscription.formValidation)
                            .fail(_uca.ajax.fail);
                    }
                });
            })
            .fail(_uca.ajax.fail);
    });
};

const checkInputAutorisations = function() {
    var autorisationsInputs = $('input[type="checkbox"][name="autorisation"]');
    if (autorisationsInputs.length > 0) {
        autorisationsInputs.on('change', function() {
            let disabled = $('input[type="checkbox"][name="autorisation"]:not(:checked)').length > 0;

            if (disabled) {
                $('#btn-confirmation').attr('disabled', true);
            } else {
                $('#btn-confirmation').removeAttr('disabled');
            }
        });
    }
}

const checkInputEmail = function(partenairesInputs) {

    if (partenairesInputs.length > 0) {
        $('.modal').css('position', 'absolute');
        partenairesInputs.on('keyup', function() {
            let disabled = false;


            let requiredEmails = [];
            // let emails = [];
            partenairesInputs.each(function(index, input) {
                // emails.push($(input).val());
                if ($(input).prop('required')) {
                    requiredEmails.push($(input).val());
                }
            });

            // const nbEmails = emails.length;
            const nbRequiredEmails = requiredEmails.length;
            for (let i = 0; i < nbRequiredEmails; i++) {
                if (!validateEmail(requiredEmails[i]) || ((new Set(requiredEmails)).size !== nbRequiredEmails)) {
                    disabled = true;
                }
            }
            // for (let i = 0; i < nbEmails; i++) {
            //     if (emails[i] !== '' && (!validateEmail(emails[i]) || ((new Set(emails)).size !== nbEmails))) {
            //         disabled = true;
            //     }
            // }
            if (disabled) {
                $('#btn-confirmation').attr('disabled', true);
            } else {
                $('#btn-confirmation').removeAttr('disabled');
            }
        });
    }
}

const listenModalClosing = function() {
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal').css('position', 'fixed');
    });
}

const validateEmail = (email) => {
    return String(email)
        .toLowerCase()
        .match(
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        ) &&
        email != '' &&
        email != USER_MAIL;
};
