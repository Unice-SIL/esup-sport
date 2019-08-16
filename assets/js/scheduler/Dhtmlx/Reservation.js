import {Evenement} from "./Evenement";
import {transformDate} from "./handleEvent";
import {dateToStr} from "./Date";
import {loadData} from "./Config";
import {changeColor} from "./Events";
import {Load} from "./Load";

var Reservation = {
    evenementType: 'ressource',
    hasSerie: true,

    //use to instanciate the object
    load: function(data)
    {
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
        if(data.profil_ids != ""){
            this.profil_ids = data.profil_ids;
        }
        if(this.evenement.serie != null){
            this.serieOffset = this.start_date.getTime() - (transformDate(this.evenement.getParent().dateDebut)).getTime();
        }

        if(data.reservabilite != null){
            this.loadReservabilite(data)

        }
        else if(data.reservabilite != null){
            this.loadReservabilite(data.evenement)
        }
        
    },

    loadReservabilite: function(data){
                        //get all the profils
            this.profil_ids = [];
            let profils = data.reservabilite.profilsUtilisateurs;
            for (var p in profils) {
                const element = profils[p];
                this.profil_ids.push(element.id);
            }
            this.profil_ids = this.profil_ids.join(",");

            this.resources_ids = [];
            let resources_ids = data.reservabilite.ressource;
            for (var r in resources_ids) {
                const element = resources_ids[r];
                this.resources_ids.push(element.id);
            }
            this.resources_ids = this.resources_ids.join(",");
    },

    //search in scheduler series the parent
    getParent: function()
    {
        return this.evenement.getParent();
    },

    extend: function(obj, src) 
    {
        Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
        return obj;
    },

    color: function(){
        this.color = scheduler.config.activeColor;
        return true;
    },

    defaultColor: function(){
        this.color = scheduler.config.defaultColor;
        return true;
    },

    //call after saveDb return 
    saveCallback: function(data, deleteEvent)
    {
        if(data.id != data.oldId){
            delete scheduler._events[data.oldId];
            Load.stop();
            scheduler.updateView();

        }
        if(data.action != "delete"){
            scheduler.updateEvent(data.id);     
            loadData(data);
        }
        else{
            scheduler.updateView();
        }
        Load.stop();
        changeColor(data.id);

    },

    //send data to symfony
    save: function (action){
        this.action = action;
        this.evenement.save(this);
    },
    

    update: function(){
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
    clean: function(obj){
        if(Array.isArray(obj)){
            for (let i = 0; i < obj.length; i++) {
                const element = obj[i];
                element.__proto__ = {}; 
            }
        }
        else{
            obj.__proto__ = {};

        }
        return obj;

    },
    
    getCopie: function(){
        let copie = Object.create(Reservation)
        copie.load(this)
        copie.id = this.id;
        return copie;
    },

    //delete element we don't want to send
    serialize: function(){

        var obj = {};
        this.extend(obj, this);
        obj = this.evenement.clean(obj);
        delete obj.evenement;
        return obj;
    },

};

export {Reservation}