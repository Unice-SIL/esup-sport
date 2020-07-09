// tooltip Bootstrap (v.4) 

_uca.bootstrap.tooltip = {};

/**
 * Function: Display()
 * Affiche le toolTip
*/
_uca.bootstrap.tooltip.display = function () { 
  $(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
};