// Libraire DataTable (JQuery)

_uca.datatable = {};

/** 
 * Function : stateLoaded()
 * callback lorsque le datatable est chargé
*/
_uca.datatable.stateLoaded = function () {
    let oTable = $('.dataTable').DataTable();
    let state = oTable.state.loaded();
    if (state) {
        oTable.columns().eq(0).each(function (colIdx) {
            let colSearch = state.columns[colIdx].search;
            if (colSearch.search !== "") {
                $('input[data-search-column-index="' + colIdx + '"]').val(colSearch.search);
                $('select[data-search-column-index="' + colIdx + '"]').val(colSearch.search).trigger('change');
            }
        });
        //oTable.draw();
    }
};

/**
 * Function : ordonnerElements
 * Monter/descendre un elément du DT (NB: l'ordre est en BDD)
 */
_uca.datatable.ordonnerElements = function (url, idDt, timeOut = 1000, idLoader = 'load') {
    $(document).on("click", ".js-monter, .js-descendre", function () {
        _uca.datatable.loader(idLoader);
        let id = this.parentElement.parentElement.id;
        let pathUrl = Routing.generate(url, { 'id': id, 'action': this.dataset.action });
        let xhr = _uca.ajax.getXmlhttp();
        xhr.open("GET", pathUrl, true);
        xhr.send();
        xhr.onload = function () {
            _uca.datatable.loader(idLoader);
            if (xhr.readyState == 4 && xhr.status == 200) {
                if (200 == xhr.response) {
                    $(idDt).DataTable().draw();
                }
            } else {
                _uca.ajax.fail(xhr);
            }
        }
    });
};

/** 
 * Function loader
 * Affiche/masque le loader d'en-tête (ce n'est pas le loader de la librairie)
*/
_uca.datatable.loader = function (id) {
    let loaderDiv = document.getElementById(id);
    if (loaderDiv.classList.contains('d-none')) {
        loaderDiv.classList.remove('d-none');
    } else {
        loaderDiv.classList.add('d-none');
    }
};

/* Filtre des datatables */
_uca.datatable.filter = {};

/**
 * Function getInscriptionsForEmailing()
 * Filtre la DT des inscriptions en fonction des filtres sélectionnés
 * @param: oTable 
 */
_uca.datatable.filter.getInscriptionsForEmailing = function (oTable) {
    // On cache les select de format d'activite et de creneau
    $('.hidden').closest('.form-group').hide();
    $('.select2-hidden-accessible').select2('destroy').select2().trigger('change.select2');

    $('.champRechercheDatatableInscription').on('change', function () {
        document.getElementById('messageErreurDestinataire').classList.add("d-none");
        document.getElementById("ucabundle_mail_save").classList.remove("disabled");

        _uca.mail.emailing.seturlListeDestinataires(boutonEmailing);

        var valueNom = $('#ucabundle_inscription_nom').val();
        var valuePrenom = $('#ucabundle_inscription_prenom').val();
        var valueActivite = { id: 0, recherche: '' };
        var valueEtablissement = "";
        var valueLieu = "";
        var valueEncadrant = "";

        // Selon le select et l'option choisie on modifie les options des select qui en decoule
        if ($(this).attr('id') == 'ucabundle_inscription_type_activite') {
            _uca.select2.changeSelectOption($('#ucabundle_inscription_type_activite').val(), 'classe_activite', 'type_activite', '#ucabundle_inscription_');
            _uca.select2.changeSelectOption($('#ucabundle_inscription_type_activite').val(), 'activite', 'type_activite', '#ucabundle_inscription_');
            $('#ucabundle_inscription_creneau').closest('.form-group').hide();
        }
        if ($(this).attr('id') == 'ucabundle_inscription_classe_activite') {
            _uca.select2.changeSelectOption($('#ucabundle_inscription_classe_activite').val(), 'activite', 'classe_activite', '#ucabundle_inscription_');
            $('#ucabundle_inscription_creneau').closest('.form-group').hide();
        }
        if ($(this).attr('id') == 'ucabundle_inscription_activite') {
            _uca.select2.changeSelectOption($('#ucabundle_inscription_activite').val(), 'formatActivite', 'activite', '#ucabundle_inscription_');
            $('#ucabundle_inscription_formatActivite').closest('.form-group').show();
        }
        if ($(this).attr('id') == 'ucabundle_inscription_formatActivite') {
            _uca.select2.changeSelectOption($('#ucabundle_inscription_formatActivite').val(), 'creneau', 'format_activite', '#ucabundle_inscription_');
            if ($('#ucabundle_inscription_formatActivite option:selected').attr('data-creneau') == 'true') {
                $('#ucabundle_inscription_creneau').closest('.form-group').show();
            } else {
                $('#ucabundle_inscription_creneau').closest('.form-group').hide();
            }
        }
        if ($(this).attr('id') == 'ucabundle_inscription_etablissements') {
            _uca.select2.changeSelectOption($('#ucabundle_inscription_etablissements').val(), 'lieux', 'etablissements', '#ucabundle_inscription_');
        }

        // Selon les select modifies on fait apparaitre les select format d'activite/creneau
        if ($('#ucabundle_inscription_formatActivite').val() == '0' && $('#ucabundle_inscription_creneau').css('display') != 'none') {
            $('#ucabundle_inscription_creneau').val('0').trigger('change.select2');
            $('#ucabundle_inscription_creneau').closest('.form-group').hide();
        }
        if ($('#ucabundle_inscription_activite').val() == '0' && $('#ucabundle_inscription_formatActivite').css('display') != 'none') {
            $('#ucabundle_inscription_formatActivite').val('0').trigger('change.select2');
            $('#ucabundle_inscription_formatActivite').closest('.form-group').hide();
        }

        // On indique quelles valeurs a chercher pour datatable en AJAX
        if ($('#ucabundle_inscription_type_activite').val() != '0' && $('#ucabundle_inscription_type_activite').val() != null) {
            valueActivite['id'] = $('#ucabundle_inscription_type_activite').val();
            valueActivite['recherche'] = 'TypeActivite';
        }
        if ($('#ucabundle_inscription_classe_activite').val() != '0' && $('#ucabundle_inscription_classe_activite').val() != null) {
            valueActivite['id'] = $('#ucabundle_inscription_classe_activite').val();
            valueActivite['recherche'] = 'ClasseActivite';
        }
        if ($('#ucabundle_inscription_activite').val() != '0' && $('#ucabundle_inscription_activite').val() != null) {
            valueActivite['id'] = $('#ucabundle_inscription_activite').val();
            valueActivite['recherche'] = 'Activite';
        }
        if ($('#ucabundle_inscription_formatActivite').val() != '0' && $('#ucabundle_inscription_formatActivite').val() != null) {
            valueActivite['id'] = $('#ucabundle_inscription_formatActivite').val();
            valueActivite['recherche'] = 'FormatActivite';
        }
        if ($('#ucabundle_inscription_creneau').val() != '0' && $('#ucabundle_inscription_creneau').val() != null) {
            valueActivite['id'] = $('#ucabundle_inscription_creneau').val();
            if ($('#ucabundle_inscription_creneau option:selected').attr('data-type') == 'format') {
                valueActivite['recherche'] = 'allCreneaux';
            } else {
                valueActivite['recherche'] = 'Creneau';
            }
        }
        if ($('#ucabundle_inscription_encadrants').val() != '0' && $('#ucabundle_inscription_encadrants').val() != null) {
            valueEncadrant = $('#select2-ucabundle_inscription_encadrants-container').attr('title');
        }
        if ($('#ucabundle_inscription_etablissements').val() != '0' && $('#ucabundle_inscription_etablissements').val() != null) {
            valueEtablissement = $('#select2-ucabundle_inscription_etablissements-container').attr('title');
        }
        if ($('#ucabundle_inscription_lieux').val() != '0' && $('#ucabundle_inscription_lieux').val() != null) {
            valueLieu = $('#select2-ucabundle_inscription_lieux-container').attr('title');
        }

        let searchValue = JSON.stringify({ id: valueActivite['id'], recherche: valueActivite['recherche'] });

        oTable.column(14).search(searchValue, true, false);
        oTable.column(13).search(valuePrenom, true, false);
        oTable.column(12).search(valueNom, true, false);
        oTable.column(18).search(valueEncadrant, true, false);
        oTable.column(7).search(valueEtablissement, true, false);
        oTable.column(19).search(valueLieu, true, false);
        oTable.ajax.reload();
    });
};
