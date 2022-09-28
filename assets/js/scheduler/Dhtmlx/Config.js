import { Load } from "./Load.js";
import { ACL } from "./ACL.js";
import { Creneau } from "./Creneau.js";
import { Reservation } from "./Reservation";
import { Serie } from "./Serie.js";
import { Prolongation } from "./Prolongation.js";
import { SchedulerExtends } from "./Scheduler.Extends.js";

scheduler.config.details_on_create = true;
scheduler.config.multi_day = true;
scheduler.config.all_timed = true;
scheduler.config.details_on_dblclick = true;
scheduler.config.mode_modification_serie = 'serie'; // choix: occurence|serie
scheduler.config.first_hour = 6;
scheduler.config.key_nav = true;
scheduler.config.last_hour = 23;
scheduler.config.defaultColor = "#46aed8";
scheduler.config.activeColor = "grey";
scheduler.config.encadrantColor = "#1A1A1A";
scheduler.config.repeat_date = "%d/%m/%Y";
scheduler.config.xml_date = "%d/%m/%Y %H:%i";
scheduler.locale.labels.section_tarif = "Tarifs";
scheduler.locale.labels.section_profils = "Profiiiiiiils";
//scheduler.locale.lables.section_capacites_profil = "Capacités par profil"
scheduler.locale.labels.section_resources = "Ressources";
scheduler.locale.labels.section_template = "Capacite";
scheduler.config.modified_event_id = null;
scheduler.locale = scheduler_lang[$("html").attr("lang")];
scheduler.config.time_step = 15;
scheduler.config.buttons_left = [];
scheduler.config.buttons_right = ["dhx_save_btn", "dhx_cancel_btn"];
scheduler.config.include_end_by = true;
scheduler.config.repeat_precise = true;
scheduler.config.icons_select = [
    "icon_details",
    "icon_delete",
    "icon_prolonger"
];
scheduler.locale.labels.icon_prolonger = "Prolonger";
Load.start();

//hide left toolbar
if (role == "user")
    scheduler.xy.menu_width = 0;

//remove icon_edit
var index = scheduler.config.icons_select.indexOf("icon_edit");
if (index !== -1) scheduler.config.icons_select.splice(index, 1);
scheduler.config.icons_select.splice(1, 0)

ACL.init();
ACL.utilisateur = role;

var dataRender = 0;
scheduler.attachEvent("onDataRender", function() {
    if (dataRender == 1) {
        Load.stop()
    }
    dataRender++;

});

scheduler.templates.event_class = function(start, end, event) {
    if (scheduler.isCopied(event.id)) {
        return "copied_event";
    }
    return ""; // default
};

var initDate = new Date()
if (typeof(ITEM.dateDebutEffective) !== "undefined" && new Date(ITEM.dateDebutEffective) > new Date()) {
    initDate = new Date(ITEM.dateDebutEffective);
}

var format_scheduler = "week";
if(window.innerWidth < 570){
    format_scheduler = "day";
}

scheduler.init('scheduler_here', initDate, format_scheduler);
var type = "formatActivite";
if (
    scheduler.data.item.objectClass == "UcaBundle\\Entity\\Lieu" ||
    scheduler.data.item.objectClass == "UcaBundle\\Entity\\Materiel"
) {
    type = "ressource";
    scheduler.data.item.type = "ressource";
} else {
    scheduler.data.item.type = "creneau";
}


$.ajax({
    method: "GET",
    url: DHTMLXAPI,
    data: {
        action: 'get',
        activite: scheduler.data.item.id,
        type: typeA,

    }
}).done(function(data) {

    initLoadData(data);
}).fail(_uca.ajax.fail);

if (typeA == "encadrant") {
    scheduler.data.item.type = "creneau";
}

if (scheduler.data.item.type == "creneau") {
    Creneau.loadEvent();
} else if (scheduler.data.item.type == 'ressource') {
    Reservation.loadEvent();
}

//use on the first query
function initLoadData(data) {
    scheduler._series = {}
    if (data.series != null) {
        data.series.forEach(loadData);
    }

    if (data.evenements != null) {
        data.evenements.forEach(loadData);
    }
    scheduler.updateView();
    Load.stop()

}

//use to load data
var loadData = function(item) {


    if (item.objectClass.includes("Entity\\DhtmlxSerie")) {
        delete scheduler._series[item.oldId];
        let event = Object.create(Serie);
        event.load(item);
        scheduler._series[item.id] = event;
        if (item.evenements != null) {

            item.evenements.forEach(function(child) {

                delete scheduler._events[child.oldId];
                if (child.action == "delete") {
                    return;
                }
                child.serie = {
                    id: item.id
                };
                loadObjects(child)

            });
        }

    } else {
        loadObjects(item)
    }
    scheduler.updateView();
    Load.stop()

}

//loading objects depending of format
var loadObjects = function(item) {
    let event;

    if (item.serie != null) {
        if (typeof scheduler._series[item.serie.id] != 'undefined' && scheduler._series[item.serie.id].creneau != null) {
            event = Object.create(Creneau);
        } else {
            event = Object.create(Reservation);
            event.resources_ids = scheduler.data.item.id;
        }
    } else if (item.reservabilite != null) {
        event = Object.create(Reservation);
        event.resources_ids = scheduler.data.item.id;
    }
    //item formatSimple is like Reservation
    else if (item.formatSimple != null) {
        event = Object.create(Reservation);
        event.resources_ids = scheduler.data.item.id;
    }

    event.load(item);
    scheduler._events[item.id] = event;
}

var itemType = function() {
    let type;

    if (item.serie != null) {
        if (scheduler._series[item.serie.id].creneau != null) {
            type = "creneau";
        } else {
            type = "reservation";
        }
    } else if (item.reservabilite != null) {
        type = "reservation";
    }

    return type;
}

export { loadData }