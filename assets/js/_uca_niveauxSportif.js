/* Gestion de plusieurs images */
_uca.niveauxSportif = {};

/** 
 * Function: suppressionDetailNiveau()
 * Supprimer du formulaire une ligne de detail de niveau
 * @param: input, container
*/
_uca.niveauxSportif.suppressionDetailNiveau = function(input) {
  let idLigneCapacite = document.getElementById(input.labels[0].innerHTML);
  if (idLigneCapacite.parentElement !== divDetailNiveauSportif) {
    idLigneCapacite = idLigneCapacite.parentElement;
  }
  divDetailNiveauSportif.removeChild(idLigneCapacite);
};

/** 
 * Function: ajouterDetailNiveau()
 * Ajouter au formulaire une ligne de détail de niveau
 * @param: input, liste profils
*/
_uca.niveauxSportif.ajoutDetailNiveau = function(input, tousProfils, wrapper) {
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
  divDetailNiveauSportif.appendChild(docFrag);
  nouvelleLigne = document.getElementById('nouvelleLigne');
  nouvelleLigne.getElementsByTagName("label")[0].innerHTML = labelCapacite;
  nouvelleLigne.setAttribute('id', labelCapacite);
};

/**
 * FUnction: gestionNiveauSportif()
 * Gère le fonctionnement du collectionType gestionNiveauSportif
 * @param: tousProfils, container
*/
_uca.niveauxSportif.gestionNiveauSportif = function(tousProfils) { 
  let wrapper = document.getElementById('wrapperNiveauSportifDetail');
	let inputs = divDetailNiveauSportif.querySelectorAll('textarea');
  let labels = divDetailNiveauSportif.querySelectorAll('label');	
 
  for (var i = 0 ; i < inputs.length; i++) {
		let labelStr = (labels[i].innerHTML.indexOf('<') !==-1) ? labelStr = labelStr.substring(0,labelStr.indexOf('<')) : labels[i].innerHTML;
		tousProfils.forEach(function(profil){
			if (profil.libelle === labelStr) {
				labels[i].setAttribute('for', (labels[i].getAttribute('for')).substring(0,labels[i].getAttribute('for').length -1) + profil.id);
				inputs[i].setAttribute('name',(inputs[i].name.substring(0,inputs[i].name.lastIndexOf('[')+1) + profil.id +']'));
			}
		});
	}

	Array.from(divNiveauxSportifs.getElementsByTagName("input")).forEach(function(checkboxProfil) {
		checkboxProfil.addEventListener('change', function(e) { 
      if (!this.checked) {
        _uca.niveauxSportif.suppressionDetailNiveau(this);
      } else {
        _uca.niveauxSportif.ajoutDetailNiveau(this, tousProfils, wrapper);
      }
		});
  });
};