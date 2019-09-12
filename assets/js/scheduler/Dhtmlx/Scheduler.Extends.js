import {loadData} from "./Config"
import {changeColor} from "./Events"

scheduler.getEvent_old = scheduler.getEvent;
scheduler.getEvent = function (id) {
    if (id in scheduler._series) {
        return scheduler._series[id];
    } else {
        return scheduler._events[id];
    }
};
scheduler.isNewEvent = function (id) {
    return !((id in scheduler._series || id in scheduler._events) && id != scheduler.getEvent(id).generatedId);
};
scheduler._buffer_event_id = null;
scheduler.isCopied = function (id) {
    if(scheduler._events[id].copyId != null){
        return true;
    }
    return false;
    //return scheduler._buffer_event_id == id;
};
scheduler.activateCopie = function (id) {
    scheduler._buffer_event_id = id;
    scheduler._buffer_event = scheduler.getEvent(id).getCopie();
    scheduler._buffer_event.id = id;
    scheduler.updateEvent(id);
};
scheduler.unactivateCopie = function () {
    if (scheduler._buffer_event_id != null) {
        let id = scheduler._buffer_event_id;
        scheduler._buffer_event_id = null;
        scheduler._buffer_event = null;
        scheduler.updateEvent(id);
    }
};

scheduler._click.buttons.delete_old = scheduler._click.buttons.delete;
scheduler._click.buttons.delete = function (id) {
    var res = scheduler.callEvent("onBeforeEventDelete", [id, scheduler.getEvent(id)]);
};

scheduler.displaySerieModalBox = function (action, ev) {

    var action = action;
    var ev = ev;

    dhtmlx.modalbox({
        text:  Translator.trans("scheduler.message.titre"),
        width: "500px",
        position: "middle",
        buttons: [  
            Translator.trans("scheduler.action."+action)+" "+Translator.trans("scheduler.message.action.serie"),
            Translator.trans("scheduler.action."+action)+" "+Translator.trans("scheduler.message.action.occurence"),
            scheduler.locale.labels.icon_cancel
        ],
        callback: function (rep) {
            //callback with the data of the modal
            if (rep == 0) {
                let event = scheduler._events[ev.id];
                event.dependanceSerie = true;
                event.getParent().updateSerie(event, action);
            } else if (rep == 1) {
                let event = scheduler._events[ev.id];
                if(action == "delete"){
                    ev.save("delete");
                }
                else{
                    event.update();

                }
            } else {
                loadData(scheduler._events[ev.id]);
                changeColor(ev.id);
                return false;
            }
        }
    });
};

scheduler.displayDeleteModalBox = function (callback) {
    dhtmlx.modalbox({
        text: "Confirmez-vous la suppression",
        width: "500px",
        position: "middle",
        buttons: [
            "Oui", "Non"
        ],
        callback: callback
    });
};
