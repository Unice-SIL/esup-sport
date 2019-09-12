_uca = {
}

_uca.imgPreview = function (event) {
    let elemId = $(this).attr('id');
    let fileUrl = URL.createObjectURL(event.target.files[0]);
    $('#' + elemId + '_preview img:first').attr('src', fileUrl);
    $('#' + elemId + '_preview').removeClass('d-none');
}

_uca.toggleFormDisplay = function (ReferenceValues) {
    return function (event) {
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

_uca.showEncadrants = _uca.toggleFormDisplay({ '0': 'nonEncadre', '1': 'encadre' });
_uca.showTarifs = _uca.toggleFormDisplay({ '0': 'nonPayant', '1': 'payant' });

/* Gestion globale de l'ajax */
_uca.ajax = {}
_uca.ajax.fail = function (data) {
    if (uca.sf_env == 'dev') {
        $('html').html(data.responseText);
    }
    else {
        window.location.href = Routing.generate('UcaWeb_Erreur500');
    }
};

/* Gestion des timers javascript */
_uca.timer = {}
_uca.timer.value = null;
_uca.timer.htmlId = null;
_uca.timer.h = function () {
    let t = _uca.timer.value;
    return (t.getDate() - 1) * 24 + t.getHours() + _uca.timer.value.getTimezoneOffset() / 60;
}
_uca.timer.m = function () {
    let t = _uca.timer.value;
    return ('0' + t.getMinutes()).slice(-2);
}
_uca.timer.s = function () {
    let t = _uca.timer.value;
    return ('0' + t.getSeconds()).slice(-2);
}
_uca.timer.init = function (htmlId, t) {
    _uca.timer.htmlId = htmlId;
    _uca.timer.phpValue = t;
    let val = 'expired';
    if (t == null) {
        $('#' + _uca.timer.htmlId).html(val);
    }
    else {
        _uca.timer.value = new Date((((t.d * 24 + t.h) * 60 + t.i) * 60 + (t.s + t.f)) * 1000);
        setInterval(() => {
            _uca.timer.value = new Date(_uca.timer.value.getTime() - 1000);
            if (_uca.timer.value.getTime() >= 0) {
                val = _uca.timer.h() + ':' + _uca.timer.m() + ':' + _uca.timer.s()
            }
            else {
                val = 'expired';
            }
            $('#' + _uca.timer.htmlId).html(val);
        }, 1000);
    }
}

/* Gestion des inscriptions */
_uca.inscription = {};

_uca.inscription.init = function () {
    _uca.inscription.htmlSpinner = $('#modalInscription .modal-dialog').html();
    _uca.inscription.type = '';
    _uca.inscription.id = '';
    _uca.inscription.idFormat = '';
    _uca.inscription.callback = null;
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

_uca.inscription.formValidation = function (data) {
    $('#modalInscription .modal-dialog').html($(data.html).find('.modal-dialog').html());
    // $('#form-inscription').submit(function (e) {
    //     // $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
    //     return true;
    // });
    $('#ajax-form-iframe').on('load', _uca.inscription.iframeAjaxLoad);
    if (_uca.inscription.callback != null) {
        window[_uca.inscription.callback](data.itemId, data.statut);
    }
};

_uca.inscription.addButtonEvent = function () {
    $(this).click(function () {
        _uca.inscription.type = $(this).data('type');
        _uca.inscription.id = $(this).data('id');
        _uca.inscription.idFormat = $(this).data('id-format');
        _uca.inscription.callback = $(this).attr('callback');
        if (_uca.inscription.id == null) {
            return;
        }
        $('#modalInscription .modal-dialog').html(_uca.inscription.htmlSpinner);
        $('#modalInscription').modal();

        $.ajax({
            method: "POST",
            url: Routing.generate('UcaWeb_Inscription'),
            data: {
                statut: 'validation',
                type: _uca.inscription.type,
                id: _uca.inscription.id,
                idFormat: _uca.inscription.idFormat
            }
        })
            .done(_uca.inscription.formValidation)
            .fail(_uca.ajax.fail);
    });
};