// Modal Bootstrap (v.4) 

_uca.bootstrap = {};
_uca.bootstrap.modal = {};

/**
 * Function : chargerLienBouton()
 * Charge la cible du modal
 */
_uca.bootstrap.modal.chargerLienBouton = function () {
  document.querySelectorAll('a[data-toggle=modal]').forEach(function (bouton) {
    bouton.addEventListener('click',function() {
      href = bouton.getAttribute('href');
      target = bouton.dataset.target;
      $(target + ' .js-bouton-action').attr('href', href);
      $(target).modal();
    });
  });
}
