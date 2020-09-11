/* Gestion de la bascule */

_uca.bascule = {};
_uca.bascule.annuelle = {};

/** 
 * Function: selectionActivite()
 * Permet de sélection (ou non) les activés et options associées à une classe d'activté
*/
_uca.bascule.annuelle.selectionActivite = function () {
  $(".classe-activite").each(function () {
    _uca.bascule.annuelle.checkAssociatedActivities($(this).attr('id').substring(38), $(this).is(':checked'));
    $(this).click(function () {
      _uca.bascule.annuelle.checkAssociatedActivities($(this).attr('id').substring(38), $(this).is(':checked'));
    });
  });

  $(".activite").each(function () {
    $(this).click(function () {
      let idAct = $(this).attr('id').substring(37).split("-")[0];
      let idClassAct = $(this).attr('id').substring(37).split("-")[1];
      _uca.bascule.annuelle.checkAssociatedActivtyClasses(idClassAct, $(this).is(':checked'));
      _uca.bascule.annuelle.activateAssociatedOptions(idAct, $(this).is(':checked'));
    });
  });
};

/**
 * Function: checkAssociatedActivites
 * Vérifier les activités associés
 * @param: id, check 
 */
_uca.bascule.annuelle.checkAssociatedActivities = function (id, check) {
  $('.activite' + id).each(function () {
    $(this).prop('checked', check);
      let idAct = $(this).attr('id').slice(37, $(this).attr('id').length - id.length - 1);
      _uca.bascule.annuelle.activateAssociatedOptions(idAct, $(this).is(':checked'));
  });
};

/**
 * Function: checkAssociatedActivityClasses()
 * Vérifie les classes d'activité associées
 * @param: id,  check 
 */
_uca.bascule.annuelle.checkAssociatedActivtyClasses = function (id, check) {
  let canICheck = [];
  $(".classe-activite" + id).each(function () {
    $(".activite" + id).each(function () {
      canICheck.push($(this).is(':checked'));
    });
    $(this).prop('checked', canICheck.includes(true));
  });
};

/**
 * Function: activateAssociatedOptions()
 * Active/désactive les options associée
 * @param: id, check
 */
_uca.bascule.annuelle.activateAssociatedOptions = function(id, check) {
    $('.option-creneau' + id).each(function () {
        let idString = $(this).attr('id');
        $('#' + idString + '_0').attr('disabled', !check);
        $('#' + idString + '_0').attr('checked', check);
        $('#' + idString + '_1').attr('disabled', !check);
    });
};

