// Libraire Select2 (JQuery)
_uca.select2 = {};

/**
 * Function: showOrHideDetails();
 * Affiche ou masque un select2 en fonction d'une checkboxe
 * @param: libelle 
 */
_uca.select2.showOrHideDetails = function (libelle) {
  let idSelect = '.hidden_' + libelle;
  let idCheckBox = "#ucabundle_extraction_" + libelle;

  if ($(idCheckBox).is(':checked')) {
    $(idSelect).closest('.form-group').show();
  } else {
    $(idSelect).closest('.form-group').hide();
  }
};

/** 
  * Function: changeSelectOption()
  * Fonction qui cache les options de select inutiles dues au choix fait sur le select 'parent'
*/
_uca.select2.changeSelectOption = function (idValue, dataIdSelect, dataIdChanged, prefix) {
  $(prefix + dataIdSelect).select2('destroy');
  $(prefix + dataIdSelect + ' option').each(function (index) {
    if ($(this).attr('data-' + dataIdChanged + '-id') != idValue && idValue != 0) {
      $(this).attr('disabled', 'disabled');
    } else {
      $(this).removeAttr('disabled');
    }
  });

  $(prefix + dataIdSelect + ' option[value="0"]').removeAttr('disabled');
  $(prefix + dataIdSelect).select2();
  $(prefix + dataIdSelect).val(0);
  $(prefix + dataIdSelect).trigger('change.select2');
};