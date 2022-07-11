/* Gestion de plusieurs images */
_uca.multipleImages = {};

/**
 * Function: suppressionImageExistante()
 * Supprime du formulaire la ligne d'une image enregistré
 */
_uca.multipleImages.suppressionImageExistante = function() {
    document.querySelectorAll('a[data-toggle=modal]').forEach(function(bouton) {
        bouton.addEventListener('click', function() {
            target = bouton.dataset.target;
            $(target).modal();
            jsBoutonModal.style.color = 'white';
            jsBoutonModal.addEventListener('click', function() {
                var ligne = document.getElementById('ligne_' + bouton.id);
                ligne.parentElement.removeChild(ligne);
                $(target).modal('hide');
            });
        });
    });
};

/**
 * Function: ajoutImageSupplementaire
 * Ajoute au formulaire une nouvelle image
 */
_uca.multipleImages.ajoutImageSupplementaire = function() {
    const libelleFormulaire = referenceFormulaireType.dataset.libelleformulaire;
    const wrapper = document.getElementById('imageSupplementaireWrapper');
    wrapper.dataset.index = divColonneGauche.querySelectorAll('img').length;

    boutonAjoutImage.addEventListener('click', function(e) {
        e.preventDefault();
        let index = parseInt(wrapper.dataset.index);
        let divRow = _uca.common.createDiv('ligneNouvelleImage_' + index, 'row');
        let divImagePreview = _uca.common.createDiv('divPreview_' + index, 'col-sm-2 d-flex align-items-end');
        let divForm = _uca.common.createDiv('divInput_' + index, 'col-sm-8 input-img-preview');
        let divBoutonSuppression = _uca.common.createDiv('divSuppression_' + index, 'col-sm-2 d-flex align-items-end')
        var divs = [divImagePreview, divForm, divBoutonSuppression];
        for (var j = 0; j < divs.length; j++) {
            divRow.appendChild(divs[j]);
        }

        let formImage = wrapper.dataset.prototype;
        formImage = formImage.replace(/__name__/g, index);
        divForm.innerHTML = formImage;

        let boutonSuppression = _uca.common.createBouton('danger', 'trash', "{{ 'bouton.supprimer' | trans }}");
        divBoutonSuppression.appendChild(boutonSuppression);
        boutonSuppression.addEventListener('click', function(e) {
            e.preventDefault();
            initDivImage.removeChild(divRow);
        });

        imagePreview = document.createElement("img");
        imagePreview.alt = "";
        imagePreview.style.height = '50px';
        imagePreview.style.width = '50px';
        imagePreview.src = "{{ asset('/public/upload/public/image/') | imagine_filter('thumb_big') }}";
        imagePreview.id = "image_preview_img_" + index;
        imagePreview.dataset.upload = false;
        imagePreview.dataset.identifier = index;
        divImagePreview.appendChild(imagePreview);

        var docFrag = document.createDocumentFragment();
        docFrag.appendChild(divRow);
        initDivImage.appendChild(docFrag);

        var inputImage = document.getElementById(libelleFormulaire + '_imagesSupplementaires_' + index + '_imageFile_file');
        inputImage.setAttribute('onchange', "_uca.common.lireURL(this);");
        inputImage.dataset.identifier = index;
        inputImage.classList.remove('custom-file-input');
        const label = inputImage.parentNode.querySelector('.custom-file-label');
        label.style.visibility = 'hidden';
        wrapper.dataset.index = index + 1;
    });
};