const { controllers } = require("chart.js");
/* Gestion des extractions */
const { param } = require("jquery");

 

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
 * Function setUrlParametersCredit
 * Initialise les paramêtres pour les urls d'extraction (Excel et PDF) pour les crédits
 * @param plage,nom,prenom 
 */
_uca.extraction.setUrlParametersCredit = function (plage, nom, prenom, operation, statut, montant, picker, recherche) {
  let parameters = {};
  if ('undefined' != typeof(picker) && picker != "") {
    parameters.dateDebut = ('undefined' != typeof(picker.dateDebut) && picker.dateDebut != "") ? picker.dateDebut : 'null';
    parameters.dateFin = ('undefined' != typeof(picker.dateFin) && picker.dateFin != "") ? picker.dateFin : 'null';
  } else {
    parameters.dateDebut = ('undefined' != typeof(plage) && plage != "") ? plage.substr(0,plage.indexOf(' ')) : 'null';
    parameters.dateFin = ('undefined' != typeof(plage) && plage != "") ? plage.substr(plage.lastIndexOf(" ") + 1, plage.length) : 'null';  
  }
  parameters.nom =  ('undefined' != typeof(nom) && nom !="") ? nom : 'null';
  parameters.prenom = ('undefined' != typeof(prenom) && prenom !="") ? prenom : 'null';
  parameters.operation = ('undefined' != typeof(operation) && operation !="") ? operation : 'null';
  parameters.statut = ('undefined' != typeof(statut) && statut !="") ? statut : 'null';
  parameters.montant = ('undefined' != typeof(montant) && montant !="") ? montant : 'null';
  parameters.recherche = ('undefined' != typeof(recherche) && recherche !="") ? recherche : 'null';

  return parameters;
};

/**
 * Function setUrlParametersCommande
 * Initialise les paramêtres pour les urls d'extraction (Excel et PDF) pour les commandes
 * @param plage,nom,prenom 
 */
_uca.extraction.setUrlParametersCommande = function (numCommande, numRecu, nom, prenom, montant, statut, moyen, plage, picker, recherche, carte, carteRetrait) {
  let parameters = {};
  if ('undefined' != typeof(picker) && picker != "") {
    parameters.dateDebut = ('undefined' != typeof(picker.dateDebut) && picker.dateDebut != "") ? picker.dateDebut : 'null';
    parameters.dateFin = ('undefined' != typeof(picker.dateFin) && picker.dateFin != "") ? picker.dateFin : 'null';
  } else {
    parameters.dateDebut = ('undefined' != typeof(plage) && plage != "") ? plage.substr(0,plage.indexOf(' ')) : 'null';
    parameters.dateFin = ('undefined' != typeof(plage) && plage != "") ? plage.substr(plage.lastIndexOf(" ") + 1, plage.length) : 'null';  
  }
  parameters.numCommande = ('undefined' != typeof(numCommande) && numCommande != "") ? numCommande : 'null';
  parameters.numRecu = ('undefined' != typeof(numRecu) && numRecu != "") ? numRecu : 'null';
  parameters.nom =  ('undefined' != typeof(nom) && nom !="") ? nom : 'null';
  parameters.prenom = ('undefined' != typeof(prenom) && prenom !="") ? prenom : 'null';
  parameters.moyen = ('undefined' != typeof(moyen) && moyen !="") ? moyen : 'null';
  parameters.statut = ('undefined' != typeof(statut) && statut !="") ? statut : 'null';
  parameters.carte = ('undefined' != typeof(carte) && carte !="") ? carte : 'null';
  parameters.carteRetrait = ('undefined' != typeof(carteRetrait) && carteRetrait !="") ? carteRetrait : 'null';
  parameters.montant = ('undefined' != typeof(montant) && montant !="") ? montant : 'null';
  parameters.recherche = ('undefined' != typeof(recherche) && recherche !="") ? recherche : 'null';

  return parameters;
};

/**
 * Function setUrlExtractionExcel()
 * Contruit l'url d'extraction en fonction des dates
 * @param url,boutonExtraction
 */
_uca.extraction.setUrlExtractionExcel = function (url, parameters, boutonExtraction) {
  console.log(parameters);
  boutonExtraction.href = Routing.generate(url, parameters);
};

/**
 * Function setUrlExtractionPDF()
 * Contruit l'url d'extraction des fichiers PDF
 * @param url,boutonExtraction
 */
_uca.extraction.setUrlExtractionPDF = function (url, parameters, boutonExtraction) {
  boutonExtraction.href = Routing.generate(url, parameters);
};


/**
 * Function initUrls()
 * Initalise les urls au chargment de la page (permet le rafraichissement)
 * @param params
 */
_uca.extraction.initUrls = function (params) {
  
  if (null != document.querySelector('.alert-danger')) {
    document.querySelector('.alert-danger').style.display = 'none';
  }
  let  urlParameters;
  if ('undefined' != typeof(params.isCommande)) {
    urlParameters = _uca.extraction.setUrlParametersCommande(params.numeroCommande.value, params.numeroRecu.value, params.utilisateurNom.value, params.utilisateurPrenom.value, params.montant.value, params.statut, params.moyen.value, params.plage.value, params.picker, params.recherche.value.replaceAll("/","-"), params.carte, params.carteRetrait);
  } else { 
    urlParameters = _uca.extraction.setUrlParametersCredit(params.plage.value, params.utilisateurNom.value, params.utilisateurPrenom.value, params.operation.value, params.statut.value, params.montant.value, params.picker, params.recherche.value); 
  }

   _uca.extraction.setUrlExtractionExcel(params.urlExcel, urlParameters, params.boutonExtractionExcel);
  _uca.extraction.setUrlExtractionPDF(params.urlPDF, urlParameters, params.boutonExtractionPDF);
};
/* Il peut être intéressant de dissocier l'aficahge de la div alert pour plus de fluidité */
/**
 * Function: preparationExtraction()
 * Filtre le datatable en fonction des champs de l'extraction
 * Construit les liens associées
 * @param params
 */
_uca.extraction.preparationExtraction = function (params) {
  params.plage.on('apply.daterangepicker', function(ev, picker) {
    params.picker = {};
    params.picker.dateDebut = picker.startDate.format('YYYY-MM-DD');
    params.picker.dateFin = picker.endDate.format("YYYY-MM-DD");
    _uca.extraction.initUrls(params);
  });
  params.plage.on('cancel.daterangepicker', function(ev, picker) {
    params.picker.dateDebut = "";
    params.picker.dateFin = "";
    _uca.extraction.initUrls(params);
  });
  params.recherche.addEventListener('change', function() {
    _uca.extraction.initUrls(params);
  });
  params.delegator.addEventListener('change', function() {
    _uca.extraction.initUrls(params);
  });
  $(document).on('change', '.selectCommande', function(){
    params.statut = $('#sg-datatables-Commande_datatable-head-filter-17').val();
    params.carte = $('#sg-datatables-Commande_datatable-head-filter-19').val();
    params.carteRetrait = $('#sg-datatables-Commande_datatable-head-filter-20').val();
    _uca.extraction.initUrls(params);
  });

  let urlParameters;
  if ('undefined' != typeof(params.isCommande)) {
    urlParameters = _uca.extraction.setUrlParametersCommande(params.numeroCommande.value, params.numeroRecu.value, params.utilisateurNom.value, params.utilisateurPrenom.value, params.montant.value, params.statut, params.moyen.value, params.plage.val(),params.picker, params.recherche.value.replaceAll('/','-'),params.carte,params.carteRetrait);
  } else { 
    urlParameters = _uca.extraction.setUrlParametersCredit(params.plage.val(), params.utilisateurNom.value, params.utilisateurPrenom.value, params.operation.value, params.statut.value, params.montant.value, params.recherche.value.replaceAll('/','-')); 
  }

  _uca.extraction.setUrlExtractionExcel(params.urlExcel, urlParameters, params.boutonExtractionExcel);
  _uca.extraction.setUrlExtractionPDF(params.urlPDF, urlParameters, params.boutonExtractionPDF);
  
};
