/* Gestion des extractions */ 

/* Gestion des extractions globale (reporting) */
_uca.extraction = {};

/** Paramêtrage: Champs à récupérer pour les créneaux */
_uca.extraction.detailsCreneau = {
  'Description créneau': 0,
  'Tarif créneau': 0,
  'Capacité créneau': 0,
  'Profils autorisés créneau': 0,
  'Niveaux sportifs créneau': 0,
  'Eligibilité créneau': 0,
  'Période créneau': 0,
  'Campus': 0,
  'Lieu': 0,
};

/** Paramêtrage: Champs à récupérer pour les formats d'activité */
_uca.extraction.detailsFormatActivite = {
  'Description format': 0,
  'Dates effectives': 0,
  'Dates inscriptions': 0,
  'Dates publications': 0,
  'Capacité format': 0,
  'Statut format': 0,
  'Payant': 0,
  'Tarif format': 0,
  'Niveaux sportifs format': 0,
  'Profils autorisés format': 0,
  'Autorisations requises format': 0,
  'Ressource format': 0,
  'Carte à acheter': 0,
};

/** Paramêtrage: Champs à récupérer pour les inscriptions */
_uca.extraction.detailsInscription = {
  'Nom et prénom inscrit': 0,
  'Date d\'inscription': 0,                
  'Date de validation': 0,
  'Date de desincription': 0,
  'Motif d\'annulation': 0,
  'Commentaire d\'annulation': 0,
};

/**
 * Function: setArrayDetails()
 * Transformation du tableau des paramêtres
 * @param: value, array
*/
_uca.extraction.setArrayDetails = function (value, array){
  let size = Object.keys(array).length;
  for(let i = 0; i < size; i++){
      let index = i + 1;
      index = index.toString();
      let name = Object.keys(array)[i];
      if(value.includes(index)){                    
          array[name] = 1;
      }else{
          array[name] = 0;
      }               
  }
};

/**
 * Function: getCheckBoxValue()
 * Vérifie si une case est coché et retourne une valeur numérique
 * @param: checkbox 
 */
_uca.extraction.getCheckBoxValue = function(checkbox) {
  return document.getElementById('ucabundle_extraction_' + checkbox).checked ? 1 : 0;
}

/**
 * Function: setData()
 * Set the correct datas to be extracted
*/
_uca.extraction.setData = function() {
  let statut = 0;
  if($('#ucabundle_extraction_inscription').is(':checked')){
    statut = $('#ucabundle_extraction_statut').val();
    _uca.extraction.setArrayDetails($("#ucabundle_extraction_inscriptionDetails").val(), _uca.extraction.detailsInscription);
  }

  if($('#ucabundle_extraction_creneau').is(':checked')){
    _uca.extraction.setArrayDetails($("#ucabundle_extraction_creneauDetails").val(), _uca.extraction.detailsCreneau);
  }

  if($('#ucabundle_extraction_formatActivite').is(':checked')){
    _uca.extraction.setArrayDetails($("#ucabundle_extraction_formatActiviteDetails").val(), _uca.extraction.detailsFormatActivite);
  } 
  
  let data = {
      "Encadrants": _uca.extraction.getCheckBoxValue("encadrant"),
      "Type d'activité": _uca.extraction.getCheckBoxValue("type_activite"),
      "Classe d'activité": _uca.extraction.getCheckBoxValue("classe_activite"),
      "Activité": _uca.extraction.getCheckBoxValue("activite"),
      "Format d'activité": _uca.extraction.getCheckBoxValue("formatActivite"),
      "Créneau": _uca.extraction.getCheckBoxValue("creneau"),
      "Inscription": _uca.extraction.getCheckBoxValue("inscription"),
      "Statut": statut,
      "Détails créneau": _uca.extraction.detailsCreneau,
      "Détails format d'activité": _uca.extraction.detailsFormatActivite,
      "Détails inscription": _uca.extraction.detailsInscription,
  };

  return data;
};

/**
 * Function: preparationFiltres()
 * Affiche/masque les filtres pour l'extraction
 */
_uca.extraction.preparationFiltres = function () {
  $('.hidden').closest('.form-group').hide();
  $('#ucabundle_extraction_inscription').on('click', function(){
    _uca.select2.showOrHideDetails('inscription');
  })
  $('#ucabundle_extraction_creneau').on('click', function(){
    _uca.select2.showOrHideDetails('creneau');
  })
  $('#ucabundle_extraction_formatActivite').on('click', function(){
    _uca.select2.showOrHideDetails('formatActivite');
  })
};

/**
 * Function: envoiExtractionPersonnalise()
 * Envoi la requête d'extraxtion personnalisé au serveur
 * @param: bouton 
 */
_uca.extraction.envoiExtractionPersonnalise = function (bouton) {
  $(bouton).on('click', function(){
    let data = _uca.extraction.setData();
    $.ajax({
        url: Routing.generate("ExtractionApi"),
        type: "POST",
        data: data,
        xhrFields: {
            responseType: 'blob'
        },
    }).done(function(data){
        if(data != null){
            let a = document.createElement('a');
            let url = window.URL.createObjectURL(data);
            a.href = url;
            let now = new Date();
            a.download = "extraction_personnalisee_" + now.getFullYear() + "-" + (now.getMonth()+1) + "-" + now.getDate() + "_" + now.getHours() + "-" + now.getMinutes() + "-" + now.getSeconds() + ".xlsx";
            document.body.append(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        }
    }).fail(_uca.ajax.fail);
  })
};



/**
 * Function: setUrlForExportPDF
 * Contruit l'url pour l'export pdf 
 * @param: dtPaiement 
 */
_uca.extraction.setUrlForExportPDF = function (url, dtPaiement) {
  let exportData = {};
  exportData.recherche = ($('input[type="search"]').val()) ? $('input[type="search"]').val() : 'null';
  exportData.date =  ($(dtPaiement).val()) ? $(dtPaiement).val() : 'null',
  bouton_export_all_pdf.href = Routing.generate(url, exportData);
};

/**
 * Function setUrlExtractionExcel()
 * Contruit l'url d'extraction en fonction des dates
 * @param: dtDebut, dtFin 
 */
_uca.extraction.setUrlExtractionExcel = function (url, dtDebut, dtFin) {
  let exportData = {};
  exportData.dateDebut = ($(dtDebut).val()) ? $(dtDebut).val() : 'null';
  exportData.dateFin = ($(dtFin).val()) ? $(dtFin).val() : 'null';
  bouton_extraction_excel.href = Routing.generate(url, exportData);
};

/* Gestion des extractions des commandes */
_uca.extraction.commande = {};

/**
 * Function setUrlExtraction()
 * Contruit l'url d'extraction en fonction des dates
 * @param: dtDebut, dtFin 
 */
_uca.extraction.commande.setUrlExtraction = function (dtDebut, dtFin) {
  let exportData = {};
  let url = 'UcaWeb_MesCommandesExtraire';
  exportData.dateDebut = ($(dtDebut).val()) ? $(dtDebut).val() : 'null';
  exportData.dateFin = ($(dtFin).val()) ? $(dtFin).val() : 'null';
  bouton_extraction_excel.href = Routing.generate(url, exportData);
};

/**
 * Function: setUrlForExportPDF
 * Contruit l'url pour l'export pdf 
 * @param: dtPaiement 
 */
_uca.extraction.commande.setUrlForExportPDF = function (dtPaiement) {
  let exportData = {};
  let url = 'UcaWeb_MesCommandesExportAll';
  exportData.recherche = ($('input[type="search"]').val()) ? $('input[type="search"]').val() : 'null';
  exportData.datePaiement =  ($(dtPaiement).val()) ? $(dtPaiement).val() : 'null',
  bouton_export_all_facture.href = Routing.generate(url, exportData);
};

