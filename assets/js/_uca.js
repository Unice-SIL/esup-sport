_uca = {};

/**
 * Function imgPreview()
 * Prévisualisation des iamges dans le formulaire
 * @param: event 
 */
_uca.imgPreview = function(event) {
    let elemId = $(this).attr('id');
    let fileUrl = URL.createObjectURL(event.target.files[0]);
    $('#' + elemId + '_preview img:first').attr('src', fileUrl);
    $('#' + elemId + '_preview').removeClass('d-none');
};

_uca.toggleFormDisplay = function(ReferenceValues) {
    return function(event) {
        if ($(this).is(':checked')) {
            let val = $(this).val();
            let code = ReferenceValues[val];
            $(".form-group:has(." + code + "ToShow)").show();
            $(".form-group:has(." + code + "ToHide)").hide();
            $('.' + code + 'ToShow').prop('required', true);
            $('.' + code + 'ToHide').prop('required', false);
        }
    }
}

_uca.vichImageStyle = function() {
    const fileInput = document.querySelectorAll('.form-control-file');
    for (let i = 0; i < fileInput.length; i++) {
        const input = fileInput[i];
        input.classList.remove('custom-file-input');
        const label = input.parentNode.querySelector('.custom-file-label');
        label.style.visibility = 'hidden';
    }
}

_uca.showEncadrants = _uca.toggleFormDisplay({ '0': 'nonEncadre', '1': 'encadre' });
_uca.showTarifs = _uca.toggleFormDisplay({ '0': 'nonPayant', '1': 'payant' });

/**
 * Function: redirectionProfil()
 * Redirige vers la bonne page de connexion
 * @param: id 
 */
_uca.redirectionProfil = function(id, bouton) {
    let select = document.getElementById(id);
    $(select).select2({ placeholder: $(this).attr('placeholder') });
    $(select).on('change ', function(e) {
        let listOptions = select.querySelectorAll('option');
        for (let i = 0; i < listOptions.length; i++) {
            if (listOptions[i].selected) {
                bouton.setAttribute('href', listOptions[i].dataset.redirection);
            }
        }
    });
};

/**
 * Function: changeVisibilityInputDependingCheckedBoxTva()
 * Modifie l'affichage de la TVA
 */
_uca.changeVisibilityInputDependingCheckedBoxTva = function() {
    if ($("#tarif_tva").is(':checked')) {
        $('#pourcentageTVA_tarif_hide').hide();
        $('#tvaNonApplicable_tarif_hide').show();
        $('#tarif_pourcentageTVA').attr('value', 0);
    } else {
        $('#pourcentageTVA_tarif_hide').show();
        $('#tvaNonApplicable_tarif_hide').hide();
        // $('#tarif_tvaNonApplicable').attr('value',"");
        $('#tarif_tvaNonApplicable').val('');
    }
};


_uca.calendrier = {};

/** 
 * Function: changePeridoe()
 * Modifie la période de calendrier
 * @param: forNextPeriode, nbDays
 */
_uca.calendrier.changePeriode = function(forNextPeriode, nbDays) {
    var date = new Date(currentDate.replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$2/$1/$3"));
    var facteur = forNextPeriode ? 1 : -1;

    if (typeVisualisation == "semaine") {
        date.setDate(date.getDate() + nbDays * facteur);
    } else if (typeVisualisation == "mois" || typeVisualisation == 'jour') {
        let oldMonth = date.getMonth();
        date.setMonth(date.getMonth() + 1 * facteur);
        if (oldMonth == 0 && forNextPeriode == false) {
            oldMonth = 12;
        } else if (oldMonth == 11 && forNextPeriode == true) {
            oldMonth = -1;
        }
        while (date.getMonth() != oldMonth + 1 * facteur) {
            date.setDate(date.getDate() - 1 * facteur * (date.getMonth() - oldMonth + 1 * facteur));
        }
    }
    var options = { day: '2-digit', year: 'numeric', month: '2-digit' };
    currentDate = date.toLocaleDateString("fr-FR", options);

    _uca.calendrier.loadData();
};

/**
 * Function: loadData()
 * Charge les donnée du calendrier
 */
_uca.calendrier.loadData = function() {
    let api_url = Routing.generate('api_activite_creneau');
    let valueHeightDiv = [];
    let widthWindow = $(window).width();
    _uca.ajax.showLoader();
    $.post(api_url, {
        data: {
            itemId: itemId,
            typeVisualisation: typeVisualisation,
            currentDate: currentDate,
            typeFormat: typeFormat,
            idRessource: idRessource,
            widthWindow: widthWindow,
        }
    }, function(data) {
        $("#sectionCalendrier").text('');
        $("#sectionCalendrier").append(data.content);
        _uca.ajax.hideLoader();

        $('.js-inscription').each(_uca.inscription.addButtonEvent);
        _uca.bootstrap.tooltip.display();
        $(".data-div").each(function() {
            valueHeightDiv.push($(this).height());
        })
        $('.cell-col-center').height(Math.max.apply(Math, valueHeightDiv));
        // _uca.openlayersmap.createMap();
        $('.toggle').on('click', function() {
            const currentElement = $(this);
            $($('h4.show').data('target')).toggleClass('show');
            $('h4.show').toggleClass('show');
            $(`${ currentElement.prop('tagName').toLowerCase() }.toggle.show`).each(function(index, element) {
                if (currentElement.data('target') != $(element).data('target')) {
                    $(element).toggleClass('show');
                    $($(element).data('target')).toggleClass('show');
                }
            });
            currentElement.toggleClass('show');
            $(currentElement.data('target')).toggleClass('show');
        });

    }).fail(_uca.ajax.fail);
};

/** function changeTypeVisualisation()
 * Change l'affichage du calendrier
 * @param: type
 */
_uca.calendrier.changeTypeVisualisation = function(newTypeVisualisation) {
    typeVisualisation = newTypeVisualisation;
    _uca.calendrier.loadData();
};


_uca.calendrier.listenClickBtnGetModalDetailCreneau = function() {
    $('a.detail_creneau').on('click', function() {
        $('#modal_container').empty('');
        const id = $(this).data('id');
        const typeFormat = $(this).data('format');
        const idFormat = $(this).data('idformat');
        const target = $(this).data('target');
        _uca.ajax.showLoader();
        $.ajax({
            url: Routing.generate('api_detail_creneau', { id, typeFormat, idFormat }),
            type: "GET"
        }).done(function(data) {
            _uca.ajax.hideLoader();
            $('#modal_container').append(data.html);
            $(target).modal('show');
            $('.js-inscription').each(_uca.inscription.addButtonEvent);
        }).fail(_uca.ajax.fail);
    });
}

_uca.evenement = {};
_uca.evenement.limitChar = function() {
    $('.text_evenement_too_long').each(function(index) {
        if (this.offsetHeight < this.scrollHeight ||
            this.offsetWidth < this.scrollWidth) {
            $(this).parent().find('button[data-toggle="modal"]').show();
        }
    });
}