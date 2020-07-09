
/* Gestion des inscriptions */
_uca.inscription = {};

_uca.inscription.init = function () {
    _uca.inscription.htmlSpinner = $('#modalInscription .modal-dialog').html();
    _uca.inscription.type = '';
    _uca.inscription.id = '';
    _uca.inscription.idFormat = '';
    _uca.inscription.vData = '';
};

_uca.inscription.iframeAjaxLoad = function (data) {
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

_uca.inscription.isFormatAvecReservation = function () {
    return $('#header-calendar').length;
}

_uca.inscription.formatAvecReservationValidation = function (data) {
    let calendarElement = $('.calendar-time-slot[elid="' + data.itemId + '"]');

    calendarElement.removeClass("notFull");
    calendarElement.addClass("register");
    calendarElement.find(".available").remove()

    //remove data on the register buttun to prevent the user to subscribe to the same event
    $('.js-inscription').each(function () {
        $(this).removeData("id");
    });
    document.getElementById('blocInscription').style.visibility = 'hidden';
}

_uca.inscription.formatAutreValidation = function (data) {
    let el = $('.js-inscription[data-id="' + data.itemId + '"]');
    $(el.parent()).html($("#js-text-inscrit-clone")[0].innerHTML);
    if (data.maxCreneauAtteint) {
        _uca.inscription.maxCreneauAtteint();
    }
}

_uca.inscription.maxCreneauAtteint = function () {
    let listeBoutonsInscription = document.querySelectorAll('.js-inscription');
    let boutonIndisponible = document.getElementById('js-text-indisponible-clone'); 
    // let boutonIndisponible = document.getElementById('js-text-indisponible-clone').cloneNode(true);
    listeBoutonsInscription.forEach(function (boutonInscription) {
        boutonInscription.parentElement.innerHTML = boutonIndisponible.innerHTML;
        // boutonInscription.parentElement.replaceChild(boutonIndisponible,boutonInscription);
        // boutonInscription.appendChild(boutonIndisponible);
    });
    $('[data-toggle="tooltip"]').tooltip();
};

_uca.inscription.formValidation = function (data) {
    $('#modalInscription .modal-dialog').html($(data.html).find('.modal-dialog').html());
    // $('#form-inscription').submit(function (e) {
    //     // $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
    //     return true;
    // });
    $('#ajax-form-iframe').on('load', _uca.inscription.iframeAjaxLoad);
    if (data.statut == 0) {
        if (_uca.inscription.isFormatAvecReservation()) {
            _uca.inscription.formatAvecReservationValidation(data);
        }
        else {
            _uca.inscription.formatAutreValidation(data);
        }
    }
};

_uca.inscription.addButtonEvent = function () {
    $(this).click(function () {
        _uca.inscription.type = $(this).data('type');
        _uca.inscription.id = $(this).data('id');
        _uca.inscription.idFormat = $(this).data('id-format');
        if (_uca.inscription.id == null) {
            return;
        }

        $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
        $('#modalInscription').modal();
       
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
            .done(function(data){
                
                let html_content = "";

                if(data.html){
                    html_content= data.html;
                }else{
                    html_content= data;
                }

                $('#modalInscription .modal-dialog').html($(html_content).find('.modal-dialog').html());
                $('#modalInscription').modal();
                   
                $(".btn-confirmation").click(function() {
                    if(this.value == 'true'){
                        $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
                        $('#modalInscription').modal();

                        $.ajax({
                                method: "POST",
                                url: Routing.generate('UcaWeb_Inscription'),
                                data: {
                                    statut: 'validation',
                                    type: _uca.inscription.type,
                                    id: _uca.inscription.id,
                                    idFormat: _uca.inscription.idFormat,
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