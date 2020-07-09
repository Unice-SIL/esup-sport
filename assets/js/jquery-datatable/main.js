// Libraire DataTable (JQuery)

_uca.datatable = {}

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
_uca.datatable.ordonnerElements = function (url, idDt, timeOut = 1000, idLoader = 'loaderDiv') {
    setTimeout(function() {
        document.querySelectorAll('.js-monter, .js-descendre').forEach(function(boutonOrdre) {
            boutonOrdre.addEventListener('click', function(event) {
                _uca.datatable.loader(idLoader);
                let id = this.parentElement.parentElement.id;
                let pathUrl = Routing.generate(url,{'id': id, 'action': this.dataset.action});
                let xhr = _uca.ajax.getXmlhttp();
                xhr.open("GET",pathUrl,true);
                xhr.send();
                xhr.onload = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        if (200 == xhr.response) {
                            _uca.datatable.loader(idLoader);
                            $(idDt).DataTable().draw();
                            _uca.datatable.ordonnerElements(url, idDt);
                        }
                    } else {
                        _uca.datatable.loader(idLoader);
                        _uca.ajax.fail(xhr);
                    }
                }
            });
        });
    }, timeOut);
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
