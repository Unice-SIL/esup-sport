import { Mail } from "./Mail.js";
import { Registered } from "./Registered.js";
import { Evenement } from "./Evenement";
import { transformDate } from "./handleEvent";
import { dateToStr } from "./Date";
import { loadData } from "./Config";
import { changeColor } from "./Events";
import { Load } from "./Load";
import { More } from "./More";
import { controllers } from "chart.js";

var Creneau = {

    evenement: {},
    type: "Creneau",
    hasSerie: true,

    //object from database
    load: function(data) {
        this.evenement = Object.create(Evenement)
        this.evenement.load(data);
        //this.extend(this, data);
        this.tarif_id = this.evenement.tarif_id;
        this.copyId = null;
        this.id = data.id;
        this.start_date = transformDate(data.dateDebut);
        this.end_date = transformDate(data.dateFin);
        this.event_pid = null;
        this.dateDebut = data.dateDebut;
        this.dateFin = data.dateFin;
        this.text = this.evenement.text;
        this.infos = this.evenement.infos;
        this.dependanceSerie = data.dependanceSerie;

        if (data.eligibleBonus != undefined) {
            this.eligible_bonus = data.eligibleBonus;
        } else {
            this.eligible_bonus = data.eligible_bonus;
        }

        this.capacite = data.capacite;

        //if action is insert, creneau don't have serie parent yet 
        if (typeof this.evenement.serie != "undefined") {
            //this.text = this.getParent().creneau.formatActivite.description;
            this.serieOffset = this.start_date.getTime() - (transformDate(this.getParent().dateDebut)).getTime();
            this.capacite = this.getParent().creneau.capacite;
            //get all the profils
            this.profil_ids = [];
            let profils = this.getParent().creneau.profilsUtilisateurs;
            for (var p in profils) {
                let element = profils[p].profilUtilisateur;
                let capacite = profils[p].capaciteProfil;
                let keyStr = 'capaciteProfil_' + element.id;
                this[keyStr] = capacite;
                this.profil_ids.push(element.id);
            }
            this.profil_ids = this.profil_ids.join(",");


            //get all encadrants
            this.encadrant_ids = [];
            if (typeof this.getParent().creneau.encadrants != "undefined") {
                let encadrants = this.getParent().creneau.encadrants;
                for (var e in encadrants) {
                    const element = encadrants[e];
                    this.encadrant_ids.push(element.id);
                }
                this.encadrant_ids = this.encadrant_ids.join(",");

                this.eligible_bonus = data.eligibleBonus;
            } else {
                this.encadrant_ids = "";
            }

            //get all the niveau sportif
            this.niveau_sportif_ids = [];

            if (typeof this.getParent().creneau.niveauxSportifs != "undefined") {
                let niveauSportif = this.getParent().creneau.niveauxSportifs;
                for (var n in niveauSportif) {
                    const element = niveauSportif[n];
                    this.niveau_sportif_ids.push(element.id);
                }
                this.niveau_sportif_ids = this.niveau_sportif_ids.join(",");
            } else {
                this.niveau_sportif_ids = "";
            }

            this.tarif_id = this.getParent().creneau.tarif.id;
            if (this.getParent().creneau.lieu != null) {
                this.lieu_id = this.getParent().creneau.lieu.id;
            }
        }
        this.defaultColor();


    },

    loadEvent: function() {
        this.callBackBeforeLigthBox();
        //init the mail event for the Creneau, this add the mail button and open the mail popup
        //when user click in it
        /*         Mail.init();
                Registered.init(); */
        More.init();
    },

    //send data to symfony
    save: function(action) {
        this.action = action;
        this.evenement.save(this);

    },


    update: function() {
        this.dateDebut = dateToStr(this.start_date);
        this.dateFin = dateToStr(this.end_date);
        this.evenement.text = this.text;
        this.evenement.infos = this.infos;
        this.dependanceSerie = false;
        this.evenement.eligible_bonus = this.eligible_bonus;

        this.save("update");
    },

    extend: function(obj, src) {
        Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
        return obj;
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
                $($(".dhx_multi_select_" + obj.getNameSelect("common.encadrants"))[0]).find("input").each(function() {
                    $(this)[0].checked = true
                });
                $($(".dhx_multi_select_" + obj.getNameSelect("common.niveauSportif"))[0]).find("input").each(function() {
                    $(this)[0].checked = true
                });
                $(".dhx_custom_button")[0].click()
            }

            return true;
        });
    },

    getNameSelect: function(translatorName) {
        return Translator.trans(translatorName).split(" ")[0];
    },

    isNew: function() {
        if (typeof this.generatedId === "undefined") {
            return false;
        }
        return true;
    },

    getParent: function() {
        return this.evenement.getParent();
    },

    getCopie: function() {
        let copie = Object.create(Creneau)
        copie.load(this)
        copie.id = this.id;
        return copie;
    },

    //call after saveDb return 
    saveCallback: function(data) {
        if (data.id != data.oldId) {
            delete scheduler._events[data.oldId];
            Load.stop();
            scheduler.updateView();
        }
        if (data.action != "delete") {
            scheduler.updateEvent(data.id);
            loadData(data);
        } else {
            Load.stop();
            scheduler.updateView();
        }

        changeColor(data.id);

    },

    color: function() {
        this.color = scheduler.config.activeColor;
        return true;
    },

    defaultColor: function() {
        if (this.encadrant_ids != null) {
            if (this.encadrant_ids.split(",").indexOf(USERID) != -1) {
                this.color = scheduler.config.encadrantColor;
                return true;
            }
        }

        this.color = scheduler.config.defaultColor;
        return true;
    },

    //delete element we don't want to send
    serialize: function() {

        var obj = {};
        this.extend(obj, this);

        delete obj.evenement;
        obj = this.evenement.clean(obj);

        return obj;
    },

};
export { Creneau }