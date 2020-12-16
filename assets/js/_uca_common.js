/* Function générales */

_uca.common = {};

/** 
 * Function createDiv()
 * Create a new div element
 * @param : id, classe
 * @return : div element
*/
_uca.common.createDiv = function(id, className) {
	var div = document.createElement('div');
	div.className = className;
	div.setAttribute('id',id);
	return div;
};

/** 
 * Function createBouton()
 * Create a new bouton element
 * @param : couleur, icône et libelle
 * @return : bouton element
*/
_uca.common.createBouton = function (color, icone, label) { 
	var bouton = document.createElement('a');
	bouton.className = 'btn btn-' + color;
	var iconeBouton = document.createElement('spsn');
	iconeBouton.className = "fas fa-"+icone;
	bouton.setAttribute('aria-label', label);
	bouton.style.color = 'white';
    bouton.appendChild(iconeBouton);		
    return bouton;
};
/** 
 * Function lireURL()
 * Lire URL depuis un fichier (utilisé pour la prévisualisation d'images)
 * @param : input
*/
_uca.common.lireURL= function (input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.getElementById("image_preview_img_" + input.dataset.identifier);
            img.src = e.target.result ;
            img.dataset.upload = true;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        throw new Error("Ce navigateur ne supporte pas fileReader");
    }
};

/** 
 * Function: afficherMasquer
 * Afficher / Masquer un élément
 * @param: inpui, target
 */
_uca.common.afficherMasquer = function (input, target) {
    if (input.type == 'checkbox') { 
        if (input.checked === true) {
            target.parentElement.style.display = "block";
        } 
        if (input.checked === false) {
            target.parentElement.style.display = "none";
            //si on masque une checkbox profil, on met le champs à 0
            let label = $(target).find('label');
            if(label.length) {
                let textarea = $("#" + label.attr('for'));
                if(textarea.length) {
                    textarea.val(0);
                }

            }
        }
    }
};

/** 
 * Function: afficher
 * Afficher un élément
 * @param: target
 */
_uca.common.afficherElement = function (target) {
    target.style.display = "block";
}

/** 
 * Function: cocherDecocherTous()
 * coche ou décoche toutes les checkbox d'une liste
 * @param: action, listeCheckboxes
*/
_uca.common.toutCocherDeccocher = function (action, liste) {
    if (action == 'cocher') {
        liste.forEach( function (elem) {
            if (!elem.checked) {
                elem.checked = true;
            } 
        });
    } else if ( action == 'decocher' ) {
        liste.forEach( function ( elem ) {
            if ( elem.checked ) {
                elem.checked = false;
            }
        });
    }
};

/** 
 * Function: cocherDecocherTousListener()
 * ajoute le listener pour éxécuter tousCocherDecocher()
 * @param: listeBoutons, listeCheckboxes
*/
_uca.common.toutCocherDecocherListener = function(listeBoutons, listeCheckboxes) {
    listeBoutons.forEach(function(bouton) {
        let action = bouton.dataset.action;
        bouton.addEventListener('click', function() {
            _uca.common.toutCocherDeccocher(action, listeCheckboxes);
        });
    });
};

/**
 * Function: inverserChaîne()
 * Inverse la chaine de caractères donnée
 * @param chaine
*/
_uca.common.inverserChaine = function(chaine) {
    return (chaine !== "" ? _uca.common.inverserChaine(chaine.substr(1)) + chaine[0] : chaine);
};
