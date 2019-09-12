scheduler.showLightboxBase = scheduler.showLightbox_rec
scheduler.showLightboxRecurring = scheduler.showLightbox
scheduler.config.lightbox.get = {
    description: function(){
        return { 
            name: Translator.trans("common.description"),
            type: "textarea",
            map_to: "text",
            height: 30,
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
                })
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
                })
        };
    },
    resources: function(){
        return { 
            name: Translator.trans("common.ressources"),
            type: "multiselect",
            map_to: "resources_ids",
            options: scheduler.data.fn.toOptions({
                data: scheduler.data.lists.ressources,
                id: "id",
                libelle: ["libelle"]
                })
        };
    },
    capacite: function(){
        return { 
            name: Translator.trans("ressource.capacite"),
            type: "textarea",
            map_to: "capacite",
            controls: {
                require: true,
                type: "int"
            },
            height: 30,
        };
    },
    encadrant: function(){
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
                })

        };
    },
    lieu: function(){
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
            })

        };
    },
    recurring: function () {
        return {name: "recurring", type: "recurring", map_to: "rec_type", button: "recurring", form: "myForm"};
    },
    time: function () {
        return {name: "time", height: 72, type: "calendar_time", map_to: "auto"};
    }

};
scheduler.config.lightbox.init = function (params) {
    scheduler.config.lightbox.sections = [];
    scheduler.resetLightbox();
    params.forEach(function(element){
        scheduler.config.lightbox.sections.push(scheduler.config.lightbox.get[element]());
    });
}

//check if the input are correct
scheduler.config.lightbox.control = function(params, isNew){
    for(var idElement in scheduler.config.lightbox.get){
        let element = scheduler.config.lightbox.get[idElement]();
        let typeEvent = isNew ? "new" : "update"; 

        if(scheduler.config.lightbox.toDisplay[typeEvent].indexOf(idElement) == -1){
            continue;
        }

        if(element.controls == null){
            continue;
        }

        if(element.controls.require && params[element.map_to] == ""){
            displayErrorMessage(Translator.trans("scheduler.error.field")+" "+element.name+" "+Translator.trans("scheduler.error.isEmpty"));
            params.event_pid = "";
            return false;
        }
        if(element.controls.type == "int"){
            if(!isNormalInteger(params[element.map_to])){
                displayErrorMessage(Translator.trans("scheduler.error.field")+" "+element.name+" "+Translator.trans("scheduler.error.type"));
                return false;
            }
        }
        
    }


    return true;
}

var isNormalInteger = function(str) {
    return /^\+?(0|[1-9]\d*)$/.test(str);
}

displayErrorMessage = function(message){
    dhtmlx.modalbox({
        text: message,
        width: "500px",
        position: "middle",
        buttons: [
            "Ok",
        ],
    });
}
