/* Gestion globale de l'ajax */

_uca.ajax = {};

_uca.ajax.fail = function (data) {
    if (uca.sf_env == 'dev') {
        $('html').html(data.responseText);
    }
    else {
        window.location.href = Routing.generate('UcaWeb_Erreur500');
    }
};

_uca.ajax.getXmlhttp = function () {
  if ( window.XMLHttpRequest ) return new XMLHttpRequest();
  else if ( window.ActiveXObject ) return new ActiveXObject( "Msxml2.XMLHTTP" );
  else window.location.href = Routing.generate('UcaWeb_Erreur500');
};
  
_uca.ajax.hideLoader = function () {
    $("#load").hide();
}
_uca.ajax.showLoader = function () {
    $("#load").show();
}