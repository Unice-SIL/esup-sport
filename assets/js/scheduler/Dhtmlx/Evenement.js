import {transformDate} from "./handleEvent";
import {Load} from "./Load";

var Evenement = {


    // save the data of this object
    // use when the we need to update only one occurence         
    save: function(data, url)
    {
        if(typeof url == "undefined"){
            url = DHTMLXAPI
        }

        Load.start();
        var obj = data.serialize();

        $.ajax({
            method: "POST",
            url: url,
            data: {
                evenement: obj,
                id: scheduler.data.item.id
            }
        }).done(function (data) {
            if(data.oldId !== null){
                scheduler._events[data.oldId].saveCallback(data);
            }
            else{
                scheduler._events[data.id].saveCallback(data);
            }
        }).fail(_uca.ajax.fail);            
    },

    //use to instanciate the object
    load: function(data)
    {
        this.extend(this, data);
        if(data.evenement != null){
            this.serie = data.evenement.serie;
        }
        this.evenement = null;
        this.evenementType = scheduler.data.item.type;
        this.start_date = transformDate(data.dateDebut);
        this.dateDebut = data.dateDebut;
        this.end_date = transformDate(data.dateFin);
        this.dateFin = data.dateFin;
        this.event_pid = null;
        if(data.description != null){
            this.text = data.description;
        }
        else if(data.text != null){
            this.text = data.text;
        }
    },

    //search in scheduler series the parent
    getParent: function()
    {
        if(this.serie == null){
            return "undefined";
        }
        return scheduler._series[this.serie.id];
    },

    send: function(evenement)
    {
        $.ajax({
            method: "POST",
            url: DHTMLXAPI,
            data: {
                evenement: evenement,
                id: scheduler.data.item.id
            }
        }).done(function (item) {
            if (item.oldId in scheduler._series || item.oldId in scheduler._events) {

                //scheduler.getEvent(item.oldId).saveCallback(item);
            }
        }).fail(_uca.ajax.fail);
    },


    extend: function(obj, src) 
    {
        Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
        return obj;
    },

    //call after saveDb return 
    saveCallback: function(data)
    {
        scheduler.updateEvent(data.id);            
    },
    
    /*
    * remove proto function 
    * prevent jquery to callback them
    * if we don't delete them, jquery call saveBd 2 times
    */
    clean: function(obj){
        if(Array.isArray(obj)){
            for (let i = 0; i < obj.length; i++) {
                const element = obj[i];
                if(typeof element.__proto__ !== "undefined"){
                    element.__proto__ = {}; 
                }
            }
        }
        else if (typeof obj != "undefined" && obj != null){
            obj.__proto__ = {};
        }
        return obj;

    },
    
    isSerie: function () {
        if(this.rec_type != null && this.rec_type !== '' && this.rec_type !== 'none' || typeof this.objectClass !== "undefined" && this.objectClass.indexOf("UcaBundle\\Entity\\DhtmlxSerie" == -1)){
            return true;
        }
        else if(this.getParent() != "undefined"){
            return true;
        }
        return false;
    },

};

export {Evenement}