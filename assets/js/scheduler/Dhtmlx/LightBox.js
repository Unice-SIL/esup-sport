scheduler.showLightboxBase = scheduler.showLightbox_rec;
scheduler.showLightboxRecurring = scheduler.showLightbox;
if ("UcaBundle\\Entity\\Utilisateur" != scheduler.data.item.objectClass) {
    scheduler.config.lightbox.get = {
        description: function () {
            return {
                name: Translator.trans("common.description"),
                type: "textarea",
                map_to: "text",
                height: 30,
                dependanceSerie: true,
            };
        },
        tarif: function () {
            return {
                name: Translator.trans("common.tarif"),
                height: 21,
                type: "select",
                map_to: "tarif_id",
                options: scheduler.data.fn.toOptions({
                    data: scheduler.data.lists.tarifs,
                    id: "id",
                    libelle: ["libelle"],
                    firstValueEmpty: true
                }),
                controls: {
                    require: true,
                },
                dependanceSerie: true,
            };
        },
        profils: function () {
            return {
                name: Translator.trans("common.profils"),
                type: "multiselect",
                map_to: "profil_ids",
                options: scheduler.data.fn.toOptions({
                    data: scheduler.data.lists.profils,
                    id: "id",
                    libelle: ["libelle"]
                }),
                dependanceSerie: true,
            };
        },
        niveauSportif: function () {
            return {
                name: Translator.trans("common.niveauSportif"),
                type: "multiselect",
                map_to: "niveau_sportif_ids",
                options: scheduler.data.fn.toOptions({
                    data: scheduler.data.lists.niveauxSportifs,
                    id: "id",
                    libelle: ["libelle"]
                }),
                dependanceSerie: true,
            };
        },
        resources: function () {
            return {
                name: Translator.trans("common.ressources"),
                type: "multiselect",
                map_to: "resources_ids",
                options: scheduler.data.fn.toOptions({
                    data: scheduler.data.lists.ressources,
                    id: "id",
                    libelle: ["libelle"]
                }),
                dependanceSerie: true,
            };
        },
        capacite: function () {
            return {
                name: Translator.trans("ressource.capacite"),
                type: "textarea",
                map_to: "capacite",
                controls: {
                    require: true,
                    type: "int"
                },
                height: 30,
                dependanceSerie: true,
            };
        },
        encadrant: function () {
            return {
                name: Translator.trans("common.encadrants"),
                type: "multiselect",
                map_to: "encadrant_ids",
                controls: {
                    require: true,
                },
                options: scheduler.data.fn.toOptions({
                    data: scheduler.data.lists.encadrant,
                    id: "id",
                    libelle: ["nom", "prenom"]
                }),
                dependanceSerie: false,

            };
        },
        lieu: function () {
            return {
                name: Translator.trans("common.lieu"),
                type: "select",
                map_to: "lieu_id",
                controls: {
                    require: true,
                },
                options: scheduler.data.fn.toOptions({
                    data: scheduler.data.lists.lieu,
                    id: "id",
                    libelle: ["etablissementLibelle", "libelle"],
                    libelleSeparateur: " - ",
                    firstValueEmpty: true
                }),
                dependanceSerie: true,
            };
        },
        recurring: function () {
            return { name: "recurring", type: "recurring", map_to: "rec_type", button: "recurring", form: "myForm" , dependanceSerie: false};
        },
        eligibilite: function () {
            return { name: Translator.trans("common.eligible"), type: "checkbox", map_to: "eligible_bonus", dependanceSerie: false}
        },
        time: function () {
            return { name: "time", height: 72, type: "calendar_time", map_to: "auto", dependanceSerie: false };
        }

    };

    if(typeof scheduler.data.item.profilsUtilisateurs !== "undefined")
    {
        scheduler.data.item.profilsUtilisateurs.forEach(function (formatProfil) {
            let keyStr = 'capaciteProfil_' + formatProfil.profilUtilisateur.id;
            scheduler.config.lightbox.get[keyStr] = function () {
                return {
                    name: formatProfil.profilUtilisateur.libelle,
                    type: 'textarea',
                    map_to: 'capaciteProfil_' + formatProfil.profilUtilisateur.id,
                    dependanceSerie: true,  
                    controls: {
                        type:'capacite',
                    }
                };
            }
        });
    }
    

    scheduler.config.lightbox.init = function (params, eventId) {
        scheduler.config.lightbox.sections = [];
        scheduler.resetLightbox();
        sections = scheduler.config.lightbox.get;
        if (eventId !== null && !scheduler._events[eventId].dependanceSerie) {
            sections = new Object();
            Object.keys(scheduler.config.lightbox.get).forEach(function(sectionId){
                if (!scheduler.config.lightbox.get[sectionId]().dependanceSerie) {
                    sections[sectionId] = scheduler.config.lightbox.get[sectionId];
                }
            });
        }
        params.forEach(function (element) {
            if ('undefined' !== typeof sections[element]) {
                scheduler.config.lightbox.sections.push(sections[element]());
            }
        });
    }

    // message d'en-tête pour prévenir que certains champs ne sont pas modifiable sur les éveènements isolés
    // scheduler.templates.event_bar_text = function () { 
    //     let message ="<span class='alert alert-warning'>" + Translator.trans('scheduler.message.fields')+" ";
    //     scheduler.config.lightbox.sections.forEach(function(section) {
    //         if (section.dependanceSerie) {
    //             message += section.name + " ";   
    //         }
    //     });
    //     message += Translator.trans('scheduler.message.information.modification.serie')+"</span>"; 
    // //console.log(message);
    //     return  message;
    // };

    //check if the input are correct


    scheduler.config.lightbox.control = function (params, isNew) {
        // let totalCapacites = 0;
        if(typeof DATEFINEFFECTIVEVERIFICATION !== "undefined" && typeof DATEFINEFFECTIVE !== "undefined")
        {
            if(params['_end_date'] > DATEFINEFFECTIVEVERIFICATION || params['end_date'] > DATEFINEFFECTIVEVERIFICATION){
                displayErrorMessage(Translator.trans("scheduler.error.date.invalid") + DATEFINEFFECTIVE);
                return false;
            }
        }
        
        for (var idElement in scheduler.config.lightbox.get) {
            let element = scheduler.config.lightbox.get[idElement]();
            let typeEvent = isNew ? "new" : "update";

            if ($('.dhtmlx_modal_box')[0]) {
                return false;
            }

            if (scheduler.config.lightbox.toDisplay[typeEvent].indexOf(idElement) == -1) {
                continue;
            }

            if (element.controls == null) {
                continue;
            }

            if (element.controls.require && params[element.map_to] == "") {
                displayErrorMessage(Translator.trans("scheduler.error.field") + " " + element.name + " " + Translator.trans("scheduler.error.isEmpty"));
                params.event_pid = "";
                return false;
            }
            if (element.controls.type == "int") {
                if (!isNormalInteger(params[element.map_to])) {
                    displayErrorMessage(Translator.trans("scheduler.error.field") + " " + element.name + " " + Translator.trans("scheduler.error.type"));
                    return false;
                }

                if(element.name == "Capacité") {
                    if(params['capacite'] > CAPACITE) {
                        displayErrorMessage(Translator.trans("scheduler.error.capacite.format") + " : " + CAPACITE );
                        return false;
                    }
                }
            }
            if (element.controls.type == "capacite") {
                let capactiteTotale = params['capacite'];
                let reg = new RegExp('^[0-9]+$');
                let currentProfil = "";            
                if (!reg.test(params[element.map_to]) || '' === params[element.map_to]) {
                    displayErrorMessage(Translator.trans("scheduler.error.field") + " " + element.name + " " + Translator.trans("scheduler.error.type"));
                    return false;
                } 
                // totalCapacites += parseInt(params[element.map_to]);
                if(params[element.map_to] > capactiteTotale) {
                    displayErrorMessage(Translator.trans("scheduler.error.capacite.somme") + " : " + capactiteTotale );
                    return false;
                }
                scheduler.data.item.profilsUtilisateurs.forEach(function(profil){
                    if(profil.profilUtilisateur.libelle == element.name) {
                        currentProfil = profil;
                    }
                })
                if(params[element.map_to] > currentProfil.capaciteProfil) {
                    displayErrorMessage(Translator.trans("scheduler.error.capacite.profil.debut") + " " +element.name + " " + Translator.trans("scheduler.error.capacite.profil.fin") + " " + currentProfil.capaciteProfil );
                    return false;
                }            
            }

        }


        return true;
    }

    var isNormalInteger = function (str) {
        return /^\+?(0|[1-9]\d*)$/.test(str);
    }




    displayErrorMessage = function (message) {
        dhtmlx.modalbox({
            text: message,
            width: "500px",
            position: "middle",
            buttons: [
                "Ok",
            ],
        });
    }

    /**  
     * Gestion des capacité par profils
    */
    scheduler.attachEvent("onLightbox", function (id) {
        // Initialisation lightbox
        let ev = scheduler._events[id];
        if (ev.dependanceSerie) {
            let hiddenElemIds = new Array();
            let shownElemIds = new Array();
            if(typeof scheduler.data.item.profilsUtilisateurs !== "undefined"){
                scheduler.data.item.profilsUtilisateurs.forEach(function (formatProfil) {
                    let keyStr = 'capaciteProfil_' + formatProfil.profilUtilisateur.id;
                    let section = scheduler.config.lightbox.sections.find(elem => elem.map_to == keyStr);
                    if (ev.hasOwnProperty(keyStr) || ev.hasOwnProperty("generatedId") ) {
                        shownElemIds.push(section);
                    } else { 
                        hiddenElemIds.push(section);
                    }
                });
                show_hide(shownElemIds, hiddenElemIds);
                scheduler.setLightboxSize();
                        
                // Modifications des classes
                let divProfils = document.querySelector(".dhx_multi_select_control");
                let listCheckboxes = Array.apply(null,divProfils.querySelectorAll('input'));
                listCheckboxes.forEach(function(checkbox,index) {
                    let keyStr = 'capaciteProfil_' + checkbox.value;
                // console.log(scheduler.config.lightbox.sections);
                    let targetId = scheduler.config.lightbox.sections.find(input => input.map_to == keyStr).id;
                    if('' === checkbox.id) {
                        checkbox.id = 'checkbox_profil_'+index;
                    }
                    checkbox.setAttribute('onchange','_uca.common.afficherMasquer('+ checkbox.id + ',' + targetId+');');
                });
            }
        }
    });

    function show_hide(shownElems, hiddenElems) { 
        if (Array.isArray(shownElems) && shownElems.length) {
            shownElems.forEach(function(shownElem) {
                let section = document.getElementById(shownElem.id).parentElement;
                section.style.display = "block";
            });
        }
        if (Array.isArray(hiddenElems) && hiddenElems.length) {
            hiddenElems.forEach(function(hiddenElem) {
                let section = document.getElementById(hiddenElem.id).parentElement;
                section.style.display = "none";
        });
        }
    }
}