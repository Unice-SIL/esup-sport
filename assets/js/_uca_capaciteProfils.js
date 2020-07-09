/* Gestion de plusieurs images */
_uca.capaciteProfils = {};

/** 
 * Function: suppressionCapacite()
 * Supprimer du formulaire une ligne de capacite
 * @param: input, container
*/
_uca.capaciteProfils.suppressionCapacite = function(input) {
  let idLigneCapacite = document.getElementById(input.labels[0].innerHTML);
  if (idLigneCapacite.parentElement !== divCapaciteProfil) {
    idLigneCapacite = idLigneCapacite.parentElement;
  }
  divCapaciteProfil.removeChild(idLigneCapacite);
};

/** 
 * Function: ajouterCapacite()
 * Ajouter au formulaire une ligne de capacite
 * @param: input, liste profils
*/
_uca.capaciteProfils.ajoutCapacite = function(input, tousProfils, wrapper) {
  let docFrag = document.createDocumentFragment();
  let divCapacite = document.createElement('div');
  let formCapacite = wrapper.dataset.prototype
  let labelCapacite = input.labels[0].innerHTML;

  tousProfils.forEach(function(profil){
  if (profil.libelle === labelCapacite) {
    formCapacite = formCapacite.replace(/__name__/g, profil.id);
  }
  });

  divCapacite.innerHTML = formCapacite;			
  docFrag.appendChild(divCapacite);
  divCapaciteProfil.appendChild(docFrag);
  nouvelleLigne = document.getElementById('nouvelleLigne');
  nouvelleLigne.getElementsByTagName("label")[0].innerHTML = labelCapacite;
  nouvelleLigne.setAttribute('id', labelCapacite);
};

/**
 * FUnction: gestionCapacitéProfils()
 * Gère le fonctionnement du collectionType gestionCapacitéProfil
 * @param: tousProfils, container
*/
_uca.capaciteProfils.gestionCapaciteProfil = function(tousProfils) { 
  let wrapper = document.getElementById('wrapperCapaciteProfilUtilisateur');
	let inputs = divCapaciteProfil.querySelectorAll('input');
  let labels = divCapaciteProfil.querySelectorAll('label');	
 
  for (var i = 0 ; i < inputs.length; i++) {
		let labelStr = (labels[i].innerHTML.indexOf('<') !==-1) ? labelStr = labelStr.substring(0,labelStr.indexOf('<')) : labels[i].innerHTML;
		tousProfils.forEach(function(profil){
			if (profil.libelle === labelStr) {
				labels[i].setAttribute('for', (labels[i].getAttribute('for')).substring(0,labels[i].getAttribute('for').length -1) + profil.id);
				inputs[i].setAttribute('name',(inputs[i].name.substring(0,inputs[i].name.lastIndexOf('[')+1) + profil.id +']'));
			}
		});
	}

	Array.from(divProfilsUtilisateurs.getElementsByTagName("input")).forEach(function(checkboxProfil) {
		checkboxProfil.addEventListener('change', function(e) { 
      if (!this.checked) {
        _uca.capaciteProfils.suppressionCapacite(this);
      } else {
        _uca.capaciteProfils.ajoutCapacite(this, tousProfils, wrapper);
      }
		});
  });
};