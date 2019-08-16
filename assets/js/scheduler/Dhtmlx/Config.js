import {Load} from "./Load.js";
import {ACL} from "./ACL.js";
import {Creneau} from "./Creneau.js";
import {Reservation} from "./Reservation";
import {Serie} from "./Serie.js";

scheduler.config.details_on_create = true;
scheduler.config.details_on_dblclick = true;
scheduler.config.mode_modification_serie = 'serie'; // choix: occurence|serie
scheduler.config.first_hour = 6;
scheduler.config.key_nav = true;
scheduler.config.last_hour = 23;
scheduler.config.defaultColor = "#007ea1";
scheduler.config.activeColor = "grey";
scheduler.config.repeat_date = "%d/%m/%Y";
scheduler.config.xml_date = "%d/%m/%Y %H:%i";
scheduler.locale.labels.section_tarif = "Tarifs";
scheduler.locale.labels.section_profils = "Profils";
scheduler.locale.labels.section_resources = "Ressources";
scheduler.locale.labels.section_template = "Capacite";
scheduler.config.modified_event_id = null;
scheduler.locale = scheduler_lang[$("html").attr("lang")];
scheduler.config.time_step = 15;
Load.start();

ACL.init();
ACL.utilisateur = role;

var dataRender = 0;
scheduler.attachEvent("onDataRender", function (){
    if(dataRender ==1 ){
        Load.stop()
    }
    dataRender ++;

});

scheduler.templates.event_class = function (start, end, event) {
    if (scheduler.isCopied(event.id)) {
        return "copied_event";
    }
    return ""; // default
};
scheduler.init('scheduler_here', new Date(), "week");
var type = "formatActivite";
if(
    scheduler.data.item.objectClass == "UcaBundle\\Entity\\Lieu" 
    || scheduler.data.item.objectClass == "UcaBundle\\Entity\\Materiel"
){
    type = "ressource";
    scheduler.data.item.type = "ressource";
}
$.ajax({
    method: "GET",
    url: DHTMLXAPI,
    data: {
        action: 'get',
        activite: scheduler.data.item.id,
        type: typeA,

    }
}).done(function (data) {

    initLoadData(data);
});
if(typeA == "encadrant"){
    scheduler.data.item.type = "creneau";
}

if(scheduler.data.item.type == "creneau"){
    Creneau.loadEvent();
}

//use on the first query
function initLoadData(data){
    scheduler._series = {}
    if(data.series != null){
        data.series.forEach(loadData);
    }

    if(data.evenements != null){
        data.evenements.forEach(loadData);
    }
    scheduler.updateView();
    Load.stop()

}

//use to load data
var loadData = function(item)
{


    if(item.objectClass == "UcaBundle\\Entity\\DhtmlxSerie"){
        
        delete scheduler._series[item.oldId];
        let event = Object.create(Serie);
        event.load(item);
        scheduler._series[item.id] = event;

        item.evenements.forEach(function(child){
            
            delete scheduler._events[child.oldId];
            if(child.action == "delete"){
                return;
            }
            child.serie = {
                id: item.id
            };
            loadObjects(child)
        });
    }else {
        loadObjects(item)
    }
    scheduler.updateView();
    Load.stop()

}

//loading objects depending of format
var loadObjects = function(item){

    let event;
    if(scheduler.data.item.type == "creneau"){
        event = Object.create(Creneau);
    }
    else if (scheduler.data.item.type == "reservation"){
        event = Object.create(Reservation);
    }
    else if(scheduler.data.item.type == "ressource"){
        event = Object.create(Reservation);
        event.resources_ids = scheduler.data.item.id;               
    }

    event.load(item);
    scheduler._events[item.id] = event;
}

export {loadData}