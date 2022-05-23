import { Creneau } from "./Creneau";
import { Reservation } from "./Reservation";
import { Evenement } from "./Evenement";
import { dateToStr } from "./Date";
import { Serie } from "./Serie";

scheduler.attachEvent("onEventPasted", function(isCopy, pasted_ev, original_ev) {

    var serie = {};
    let orEv = scheduler._events[original_ev.id]

    let eventDhtmlx = Object.create(Evenement);
    eventDhtmlx.extend(eventDhtmlx, original_ev);
    eventDhtmlx.reference_id = scheduler.data.item.id
    eventDhtmlx = orEv.getCopie();

    let delay = pasted_ev.start_date.getTime() - eventDhtmlx.start_date.getTime();
    //case of serie, we need to get the parent and copie all children
    if (original_ev.getParent() != "undefined") {
        let copie = original_ev.getParent().getCopie();
        copie.enfants = [];
        copie.reference_id = scheduler.data.item.id
        copie.updateSerie(pasted_ev, "insert");

        delete scheduler._events[pasted_ev.id];
    }
    //should be a unique evenement
    else {
        //pasted_ev.event_length = (pasted_ev.end_date.getTime() - pasted_ev.start_date.getTime()) / 1000;
        pasted_ev.dateDebut = dateToStr(pasted_ev.start_date);
        pasted_ev.dateFin = dateToStr(pasted_ev.end_date);
        pasted_ev.description = pasted_ev.text;
        pasted_ev.informations = pasted_ev.infos;
        let copie = original_ev.getCopie();
        copie.load(pasted_ev);
        copie.id = pasted_ev.id + "_copy";
        scheduler._events[copie.id] = copie;
        copie.save("insert");
        delete scheduler._events[pasted_ev.id];
    }

    scheduler.updateView();

    return true;
});



// fires when the user adds a new event to the scheduler
scheduler.attachEvent("onEventAdded", function(id, ev) {

    let item = scheduler.data.item;

    let evenement = Object.create(Evenement);

    evenement.extend(evenement, ev);
    evenement.reference_id = item.id;

    if (!scheduler.isCopied(evenement.id)) {
        addEl(evenement);
    }

    return true;
});

function addEl(evenement, item, serie, isSerie) {

    if ((typeof evenement.isSerie != "undefined" && evenement.isSerie() && item == null) || isSerie) {
        addSerie(evenement);
    } else {
        let eventC;
        if (scheduler.data.item.type == "creneau") {
            eventC = Object.create(Creneau);
            eventC.evenementType = 'creneau';
        } else if (scheduler.data.item.type == "reservation") {
            eventC = Object.create(Reservation);
            eventC.evenementType = 'ressource';
            eventC.resources_ids = scheduler.data.item.id;
            eventC.reference_id = scheduler.data.item.id;


        } else if (scheduler.data.item.type == "ressource") {
            eventC = Object.create(Reservation);
            eventC.evenementType = 'ressource';
            eventC.resources_ids = scheduler.data.item.id;
            eventC.reference_id = scheduler.data.item.id;
        }

        if (serie == null) {
            item = evenement;
        }

        eventC.load(evenement);
        eventC = extend(eventC, evenement);
        eventC.hasSerie = true;
        eventC.dateDebut = dateToStr(item.start_date);
        eventC.dateFin = dateToStr(item.end_date);
        eventC.end_date = dateToStr(item.end_date);
        eventC.start_date = dateToStr(item.start_date);
        eventC.text = evenement.text;
        eventC.infos = evenement.infos;
        eventC.dependanceSerie = true;
        eventC.action = "insert";
        scheduler._events[eventC.id] = eventC;

        if (serie != null) {
            if (serie.enfants == null) {
                serie.enfants = [];
            }
            serie.enfants.push(eventC);
        } else {
            eventC.save("insert");
        }

        return event;
    }
}

var extend = function(obj, src) {
    Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
    return obj;
}

var addSerie = function(evenement) {
    let serie = Object.create(Serie);
    serie.load(evenement);

    scheduler._series[evenement.id] = serie;
    serie.id = evenement.id;
    serie.dateDebut = evenement.dateDebut;
    serie.dateFin = evenement.dateFin;
    serie.dependanceSerie = true;
    var childs = [];
    //create evenement and add to scheduler and serie
    evenement.enfants = scheduler.getRecDates(evenement, 1000 * 1000).map(function(item) {
        addEl(evenement, item, serie);
    });

    // save the serie
    serie.recurrence = evenement.rec_type;
    serie.action = "insert";

    serie.dateFinSerie = dateToStr(evenement.end_date);
    serie.saveBd();
}


// fires when the user clicks on the 'save' button in the lightbox
scheduler.attachEvent("onEventSave", function(id, ev, is_new) {
    //call the lightbox again
    if (!scheduler.config.lightbox.control(ev, is_new)) {
        return false;
    }
    if (!is_new) {

        let eventDhtmlx = scheduler.getEvent(id);

        let isSerie = eventDhtmlx.evenement.isSerie();
        let dependance = eventDhtmlx.dependanceSerie;
        if (dependance) {
            ev.dateDebut = dateToStr(eventDhtmlx.start_date);
            ev.dateFin = dateToStr(eventDhtmlx.end_date);
        } else {
            ev.dateDebut = dateToStr(ev.start_date);
            ev.dateFin = dateToStr(ev.end_date);
        }

        ev.nature = 'nativeEvent';
        ev.id = eventDhtmlx.id;
        if (isSerie) {
            ev.serie = { id: eventDhtmlx.evenement.serie.id }


            eventDhtmlx.load(ev);
            eventDhtmlx.text = ev.text
            eventDhtmlx.infos = ev.infos
            _uca.ajax.showLoader();
            isOccurrenceDependance(ev.serie, function(result) {
                _uca.ajax.hideLoader();
                // if (result == true) {
                // eventDhtmlx.update();
                // } else {
                //     if (dependance) {
                // scheduler.displaySerieModalBox('update', eventDhtmlx);
                scheduler.displaySerieModalBox('update', eventDhtmlx, result);
                //     } else {
                //         eventDhtmlx.update();
                //     }
                // }
            });


        } else {

            eventDhtmlx.load(ev);
            eventDhtmlx.text = ev.text
            eventDhtmlx.infos = ev.infos
            eventDhtmlx.dependanceSerie = false;
            eventDhtmlx.save('update');
        }
    } else {
        //need this to test if series
        let evenement = Object.create(Evenement);

        evenement.load(ev);
        if (scheduler.data.item.type == "creneau") {
            if (!evenement.isSerie()) {
                //open modal

                dhtmlx.modalbox({
                    text: "Cela doit être une série",
                    width: "500px",
                    position: "middle",
                    buttons: [
                        "Ok",
                    ],
                });

                return false;
            }
        }

    }
    return true;
});
// fires when the event has been changed by drag-n-drop, but the changes aren't saved yet
scheduler.attachEvent("onBeforeEventChanged", function(ev, e, is_new, original) {
    let evenement = Object.create(Evenement);
    evenement.load(ev);

    if (!is_new) {
        if (evenement.isSerie()) {
            if (ev.dependanceSerie) {
                scheduler.displaySerieModalBox('update', ev, false);
            } else {
                ev.update();
            }

        } else {
            ev.dependanceSerie = false;
            let eventDhtmlx = scheduler._events[ev.id];
            eventDhtmlx.extend(eventDhtmlx, ev);
            eventDhtmlx.update();
        }
    }
    return true;
});


scheduler.attachEvent("onBeforeEventDelete", function(id, ev) {
    let evenement = Object.create(Evenement);
    evenement.load(ev);
    if (evenement.isSerie()) {
        var ev = ev;
        isOccurrenceDependance(evenement.serie, function(result) {
            scheduler.displaySerieModalBox('delete', ev, result);
        });
    } else {
        scheduler.displayDeleteModalBox(function(rep) {
            if (rep == 0) {
                ev.save('delete');
            } else {
                return false;
            }
        })
    }
    return true;
});

scheduler.attachEvent("onBeforeLightbox", function(id) {
    if (scheduler.isNewEvent(id)) {

        scheduler._events[id].text = scheduler.data.item.description;
        scheduler._events[id].infos = scheduler.data.item.informations;
        scheduler.config.lightbox.init(scheduler.config.lightbox.toDisplay.new, id);
    } else {
        scheduler.config.lightbox.init(scheduler.config.lightbox.toDisplay.update, id);
    }

    return true;
});

scheduler.attachEvent("onEventDeleted", function(id, ev) {
    scheduler.unactivateCopie();
});

scheduler.attachEvent("onLightbox", function(id) {
    let day = scheduler._events[id].start_date.getDay();
    $('.dhx_repeat_days input[value="' + day + '"]')[0].checked = true

});

scheduler.attachEvent("onEventCopied", function(ev) {
    for (var el in scheduler._events) {
        let elem = scheduler._events[el];

    }
    scheduler.unactivateCopie();
    scheduler.activateCopie(ev.id);
    scheduler._events[ev.id].copyId = ev.id;
});




scheduler.attachEvent("onEventCreated", function(id, e) {
    let item = scheduler.data.item;
    let eventDhtmlx = scheduler.getEvent(id);
    eventDhtmlx.reference_class = item.objectClass;
    eventDhtmlx.reference_id = item.id;
    eventDhtmlx.dependanceSerie = true;
    eventDhtmlx.generatedId = id;
    return true;
});


var lastEventClickTimeStamp = 0;

//for click and double click
// because onDblClick and onClick are not compatible together
scheduler.attachEvent("onClick", function(id, e) {
    changeColor(id);
    scheduler.updateView();
    if (role == "user")
        window.location = PATH_SEE_MORE + id;

    //double click
    if (e.timeStamp - lastEventClickTimeStamp < 200) {
        scheduler.showLightbox(id);
    }
    checkEvents(scheduler._events[id]);
    lastEventClickTimeStamp = e.timeStamp;

    return true;
});


var checkEvents = function(el) {
    let email = scheduler.config.icons_select.indexOf("icon_email");
    let registered = scheduler.config.icons_select.indexOf("icon_register");
    let more = scheduler.config.icons_select.indexOf("icon_more");
    if (email != -1) {
        scheduler.config.icons_select.splice(email, 1);
    }
    if (registered != -1) {
        scheduler.config.icons_select.splice(email, 1);
    }
    if (more != -1) {
        scheduler.config.icons_select.splice(more, 1);
    }

    if (['creneau', 'ressource'].includes(el.evenement.evenementType)) {
        /*             scheduler.config.icons_select.splice(2,0,"icon_email")
                    scheduler.config.icons_select.splice(3,0,"icon_register") */
        scheduler.config.icons_select.splice(1, 0, "icon_more");
        scheduler.locale.labels.icon_more = Translator.trans('scheduler.message.voirplus');

    }
}


scheduler.attachEvent("onBeforeDrag", function(id, e) {
    changeColor(id);

    return true;
});


var changeColor = function(id) {
    let eventDhtmlx = scheduler._events[id];

    for (var s in scheduler._series) {
        let serie = scheduler._series[s];
        serie.defaultColor();
    }
    for (var ev in scheduler._events) {
        let evnt = scheduler._events[ev];
        evnt.defaultColor();
    }
    if (eventDhtmlx == null) {
        return false;
    }


    if (typeof eventDhtmlx.getParent() != "undefined" && eventDhtmlx.getParent() != "undefined") {
        // eventDhtmlx.getParent().color();
        eventDhtmlx.color = scheduler.config.activeColor;
    } else {
        eventDhtmlx.color = scheduler.config.activeColor;
    }
}


function isOccurrenceDependance(serieId, callback) {
    $.ajax({
        method: "POST",
        url: Routing.generate('DhtmlxNbOccurrenceDependance'),
        data: {
            serieId: serieId
        },
        success: function(code_html, statut) {},
        error: function(resultat, statut, erreur) {},
        complete: function(resultat, statut) {
            callback(resultat.responseJSON);
        }
    });
}



export { changeColor }