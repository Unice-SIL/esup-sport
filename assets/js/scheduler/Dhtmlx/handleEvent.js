import {ACL} from "./ACL.js";
 
 
 //transform date for IE
var transformDate = function(date){
		if(typeof date == "undefined"){
				return "undefned";
		}
		let strDate = date.split(" ");
		let d = strDate[0].split("-");
		let h = strDate[1].split(":");
		let month = d[1] - 1;
		date = new Date(d[0], month, d[2], h[0], h[1]);
		return date;
		
}
var attavhEventsList = {};
//redefine the attach event and set the right event with the user role
scheduler.attachEvent = function(name, catcher, callObj){

    var old_catcher = catcher;
    catcher = function() {
        if(!ACL.action(name)){
            return false;
        }
        return old_catcher.apply(this, arguments);

    };
    name='ev_'+name.toLowerCase();
    if (!this[name])
        this[name]=new this.eventCatcher(callObj||this);

    attavhEventsList[name] = catcher;

    return(name+':'+this[name].addEvent(catcher)); //return ID (event name & event ID)
}

scheduler._click.buttons["edit"]= function(id){ 
    if(!ACL.action("edit")){
        return false;
    }
    scheduler.edit(id);
}
scheduler._click.buttons["save"]= function(id){ 
    if(!ACL.action("save")){
        return false;
    }
    scheduler.editStop(true);
}
scheduler._click.buttons["details"]=function(id){ 
    if(!ACL.action("details")){
        return false;
    }
    scheduler.showLightbox(id);
}
scheduler._click.buttons["cancel"]= function(id){ 
    if(!ACL.action("cancel")){
        return false;
    }
    scheduler.editStop(false);
}

export {transformDate}