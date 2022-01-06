import { Evenement } from "./Evenement";
import { transformDate } from "./handleEvent";
import { dateToStr } from "./Date";
import { loadData } from "./Config";
import { changeColor } from "./Events";
import { Load } from "./Load";

var Reservation = {
    evenementType: 'ressource',
    hasSerie: true,

    //use to instanciate the object
    load: function(data) {
        this.evenement = Object.create(Evenement)
        this.reference_id = scheduler.data.item.id;

        this.evenementType = "ressource";
        this.evenement.load(data);
        this.evenement.evenementType = 'ressource';
        this.dependanceSerie = data.dependanceSerie;

        //this.extend(this, data);
        this.copyId = null;
        this.id = data.id;
        this.start_date = transformDate(data.dateDebut);
        this.dateDebut = data.dateDebut;
        this.end_date = transformDate(data.dateFin);

        this.dateFin = data.dateFin;
        this.event_pid = null;
        this.text = this.evenement.text;
        if (data.profil_ids != "") {
            this.profil_ids = data.profil_ids;
        }
        if (this.evenement.serie != null) {
            this.serieOffset = this.start_date.getTime() - (transformDate(this.evenement.getParent().dateDebut)).getTime();
        }

        // if data has id, retrieve profil
        if (typeof this.evenement.serie != "undefined" && this.evenement.serie != null && typeof this.getParent().reservabilite != "undefined" && this.getParent().reservabilite != null) {
            //this.text = this.getParent().creneau.formatActivite.description;
            this.serieOffset = this.start_date.getTime() - (transformDate(this.getParent().dateDebut)).getTime();
            this.capacite = this.getParent().reservabilite.capacite;
            //get all the profils
            this.profil_ids = [];
            let profils = this.getParent().reservabilite.profilsUtilisateurs;
            for (var p in profils) {
                let element = profils[p].profilUtilisateur;
                let capacite = profils[p].capaciteProfil;
                let keyStr = 'capaciteProfil_' + element.id;
                this[keyStr] = capacite;
                this.profil_ids.push(element.id);
            }
            this.profil_ids = this.profil_ids.join(",");
        } else if (typeof this.evenement.reservabilite != "undefined" && this.evenement.reservabilite != null) {
            this.capacite = this.evenement.reservabilite.capacite;
            //get all the profils
            this.profil_ids = [];
            let profils = this.evenement.reservabilite.profilsUtilisateurs;
            for (var p in profils) {
                let element = profils[p].profilUtilisateur;
                let capacite = profils[p].capaciteProfil;
                let keyStr = 'capaciteProfil_' + element.id;
                this[keyStr] = capacite;
                this.profil_ids.push(element.id);
            }
            this.profil_ids = this.profil_ids.join(",");
        } else {
            this.capacite = this.evenement.capacite;
            //get all the profils
            for (const [key, value] of Object.entries(this.evenement)) {
                if (key.includes('capaciteProfil_')) {
                    this[key] = value;
                }
            }
            this.profil_ids = this.evenement.profil_ids;
        }

        if (data.reservabilite != null) {
            this.loadReservabilite(data)

        } else if (data.reservabilite != null) {
            this.loadReservabilite(data.evenement)
        }

    },

    loadReservabilite: function(data) {
        //get all the profils
        this.profil_ids = [];
        let profils = data.reservabilite.profilsUtilisateurs;
        for (var p in profils) {
            const element = profils[p].profilUtilisateur;
            this.profil_ids.push(element.id);
        }
        this.profil_ids = this.profil_ids.join(",");

        this.resources_ids = [];
        this.resources_ids = data.reservabilite.ressource.id;
    },

    getNameSelect: function(translatorName) {
        return Translator.trans(translatorName).split(" ")[0];
    },

    //attach an event when the ligthbox is open
    //if is a new event we check all profils, and expant recurring area
    callBackBeforeLigthBox: function() {
        var obj = this;
        scheduler.attachEvent("onLightbox", function(id) {
            //we are on insert mode
            let ev = scheduler._events[id];
            if (typeof ev.generatedId !== "undefined") {
                $($(".dhx_multi_select_" + obj.getNameSelect("common.profils"))[0]).find("input").each(function() {
                    $(this)[0].checked = true
                });
                // $(".dhx_custom_button")[0].click()
            } else {
                $($(".dhx_multi_select_" + obj.getNameSelect("common.profils"))[0]).find("input").each(function(index, input) {
                    let key = `capaciteProfil_${input.value}`;
                    if (typeof ev[key] != 'undefined' && ev[key] != null) {
                        $(input)[0].checked = true;
                    }
                });
            }

            return true;
        });
    },

    loadEvent: function() {
        this.callBackBeforeLigthBox();
    },

    //search in scheduler series the parent
    getParent: function() {
        return this.evenement.getParent();
    },

    extend: function(obj, src) {
        Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
        return obj;
    },

    color: function() {
        this.color = scheduler.config.activeColor;
        return true;
    },

    defaultColor: function() {
        this.color = scheduler.config.defaultColor;
        return true;
    },

    //call after saveDb return 
    saveCallback: function(data, deleteEvent) {
        if (data.id != data.oldId) {
            delete scheduler._events[data.oldId];
            Load.stop();
            scheduler.updateView();

        }
        if (data.action != "delete") {
            scheduler.updateEvent(data.id);
            loadData(data);
        } else {
            scheduler.updateView();
        }
        Load.stop();
        changeColor(data.id);

    },

    //send data to symfony
    save: function(action) {
        this.action = action;
        this.evenement.save(this);
    },


    update: function() {
        this.dateDebut = dateToStr(this.start_date);
        this.dateFin = dateToStr(this.end_date);
        this.dependanceSerie = false;


        this.save("update");
    },

    /*
     * remove proto function 
     * prevent jquery to callback them
     * if we don't delete them, jquery call saveBd 2 times
     */
    clean: function(obj) {
        if (Array.isArray(obj)) {
            for (let i = 0; i < obj.length; i++) {
                const element = obj[i];
                element.__proto__ = {};
            }
        } else {
            obj.__proto__ = {};

        }
        return obj;

    },

    getCopie: function() {
        let copie = Object.create(Reservation)
        copie.load(this)
        copie.id = this.id;
        return copie;
    },

    //delete element we don't want to send
    serialize: function() {

        var obj = {};
        this.extend(obj, this);
        obj = this.evenement.clean(obj);
        delete obj.evenement;
        return obj;
    },

};

export { Reservation }