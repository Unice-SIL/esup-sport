import { Evenement } from "./Evenement";
import { transformDate } from "./handleEvent";
import { dateToStr } from "./Date";
import { Load } from "./Load";
import { loadData } from "./Config";
import { changeColor } from "./Events";


var Serie = {

    type: "Serie",

    // save the data of this object and dependant childrens
    saveBd: function() {
        Load.start();
        var me = this;
        $.ajax({
            method: "POST",
            url: DHTMLXAPI,
            data: {
                evenement: this.serialize(),
                id: scheduler.data.item.id
            }
        }).done(function(data) {
            me.saveCallback(data);
        }).fail(_uca.ajax.fail);
    },

    load: function(data) {
        this.extend(this, data);
        this.type = "Serie";
        this.evenementType = scheduler.data.item.type;

        if (typeof data.creneau != "undefined" && data.creneau != null) {
            this.tarif_id = data.creneau.tarif.id;
        }

        this.start_date = transformDate(data.dateDebut);
        this.end_date = transformDate(data.dateFin);
        this.dateDebut = data.dateDebut;
        this.dateFin = data.dateFin;
        this.eligible_bonus = data.eligibleBonus;
        this.forte_frequence = data.forteFrequence;

        this.event_pid = null;
    },

    getParent: function() {
        return false;
    },

    getDependantChildren: function() {
        var obj = this
        return Object.values(scheduler._events).reduce(function(filtered, ev) {
            if (ev.getParent != null && obj != null && typeof ev.getParent() != 'undefined') {
                if (ev.getParent().id == obj.id && ev.hasSerie) {
                    filtered.push(ev);
                }
            }
            return filtered;
        }, []);
    },

    setChildrens: function(childs) {
        this.enfants = childs;
    },

    //update and save the serie and all Evenement
    updateSerie: function(data, action) {
        let start_date = new Date(data.start_date.getTime() - data.serieOffset);
        let end_date = new Date(data.end_date.getTime() - data.serieOffset);
        this.action = action;

        var move_delay_start = this.start_date.getTime() - start_date.getTime();
        var move_delay_end = start_date.getTime() - end_date.getTime();

        this.start_date = start_date;
        this.end_date = end_date;

        if (typeof data.capacite != 'undefined') {
            this.capacite = data.capacite;
        }

        var obj = this;
        obj.tarif_id = parseInt(data.tarif_id);
        obj.lieu_id = parseInt(data.lieu_id);
        this.enfants = [];

        obj.encadrant_ids = data.encadrant_ids;
        obj.profil_ids = data.profil_ids;
        obj.profil_ids.split(",").forEach(function(profil) {
            let keyStr = 'capaciteProfil_' + profil;
            obj[keyStr] = data[keyStr];
        });
        obj.niveau_sportif_ids = data.niveau_sportif_ids;

        this.getDependantChildren().forEach(function(ev) {
            if (data.id != ev.id) {
                if (ev.dependanceSerie && data.dependanceSerie) {
                    ev.start_date = new Date(ev.start_date.getTime() - move_delay_start);
                    ev.end_date = new Date(ev.start_date.getTime() - move_delay_end);

                    ev.profil_ids = data.profil_ids;
                    ev.profil_ids.split(",").forEach(function(profil) {
                        let keyStr = 'capaciteProfil_' + profil;
                        ev[keyStr] = data[keyStr];
                    });
                    ev.capacite = data.capacite;
                    ev.text = data.text;
                    ev.infos = data.infos;
                    ev.eligible_bonus = data.eligible_bonus;
                    ev.forte_frequence = data.forte_frequence;

                    ev.niveau_sportif_ids = data.niveau_sportif_ids;

                    if (typeof data.encadrant_ids !== "undefined") {
                        ev.encadrant_ids = data.encadrant_ids;
                    }
                    if (typeof data.resources_ids !== "undefined") {
                        ev.resources_ids = data.resources_ids;
                    }

                    ev.tarif_id = parseInt(data.tarif_id);
                    ev.lieu_id = parseInt(data.lieu_id);

                    ev.dateDebut = dateToStr(ev.start_date);
                    ev.dateFin = dateToStr(ev.end_date);

                    obj.capacite = ev.capacite;
                    ev.evenementType = data.evenementType;

                    obj.resources_ids = ev.resources_ids;
                    scheduler.updateEvent(ev.id);
                }

            } else {
                ev.tarif_id = parseInt(data.tarif_id);
                ev.lieu_id = parseInt(data.lieu_id);

                ev.start_date = new Date(ev.start_date.getTime());
                //ev.end_date = transformDate(ev.end_date.getTime() );
                ev.dateDebut = dateToStr(ev.start_date);
                ev.dateFin = dateToStr(ev.end_date);
                ev.evenementType = data.evenementType;
                ev.dependanceSerie = data.dependanceSerie;
            }
            ev.action = action;
            obj.enfants.push(ev);

        });
        this.saveBd();

    },

    //take an object and add another 
    extend: function(obj, src) {
        Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
        return obj;
    },

    /*
     * remove proto function 
     * prevent jquery to callback them
     * if we don't delete them, jquery call saveBd 2 times
     */
    clean: function(obj) {
        var me = obj;
        if (Array.isArray(obj)) {
            for (let i = 0; i < obj.length; i++) {
                const element = obj[i];
                if (typeof element.__proto__ !== "undefined") {
                    element.__proto__ = {};
                }
            }
        } else if (typeof obj != "undefined" && obj != null) {
            obj.__proto__ = {};

        }
        return obj;

    },

    //search in schedulers events all object with the id series 
    getChildren: function() {
        var me = this;
        return Object.values(scheduler._events).reduce(function(filtered, ev) {
            if (ev.serie.id == me.id) {
                filtered.push(ev);
            }

            return filtered;
        }, []);
    },

    //call after saveDb return 
    saveCallback: function(data) {
        loadData(data);
        if(data.notCreated !== undefined && data.notCreated !== null) {
            if (data.notCreated.length > 0) {
                let date;
                let msg = Translator.trans('creneau.notCreated');
                msg += '<ul>';
                for (const creneau of data.notCreated) {
                    date = creneau.split(' ')[0].split('-').reverse().join('/');
                    msg += `<li>${date}</li>`;
                }
                msg += '</ul>';
                dhtmlx.modalbox({
                    text: msg,
                    width: "500px",
                    position: "middle",
                    buttons: [
                        "Ok",
                    ],
                });
            }
        }
        Load.stop();
        changeColor(data.enfants[0].id);
        scheduler.updateView();
    },

    getCopie: function() {

        let serie = Object.create(Serie);
        serie.load(this);

        serie.id = serie.start_date.getTime();

        this.getDependantChildren().forEach(function(item) {
            let ev = Object.create(Evenement);
            let c = item.getCopie();
            c.evenement.serie = {}
            c.evenement.serie.id = serie.id;
            c.id = c.id + "_copy";
            scheduler._events[c.id] = c;
        })
        scheduler._series[serie.id] = serie;
        return serie;
    },

    // color: function() {
    //     this.getDependantChildren().forEach(function(item) {
    //         item.color = scheduler.config.activeColor;
    //     });
    // },

    defaultColor: function() {
        this.getDependantChildren().forEach(function(item) {
            item.color = scheduler.config.defaultColor;
        });
    },

    //delete element we don't want to send
    serialize: function() {
        var obj = {};
        this.extend(obj, this);
        obj.enfants = this.clean(obj.enfants);
        for (let i = 0; i < obj.enfants.length; i++) {
            const element = obj.enfants[i];
            obj.enfants[i].evenement = this.clean(obj.enfants[i].evenement);
            delete element.serie;
            delete element.evenement;

        }
        delete obj.creneau;
        delete obj.evenements;
        obj = this.clean(obj);

        return obj;
    },

}

export { Serie }