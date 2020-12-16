import { loadData } from "./Config"
import { changeColor } from "./Events"
import { AST_Atom } from "terser";

scheduler.getEvent_old = scheduler.getEvent;
scheduler.getEvent = function(id) {
    if (id in scheduler._series) {
        return scheduler._series[id];
    } else {
        return scheduler._events[id];
    }
};
scheduler.isNewEvent = function(id) {
    return !((id in scheduler._series || id in scheduler._events) && id != scheduler.getEvent(id).generatedId);
};
scheduler._buffer_event_id = null;
scheduler.isCopied = function(id) {
    if (scheduler._events[id].copyId != null) {
        return true;
    }
    return false;
    //return scheduler._buffer_event_id == id;
};
scheduler.activateCopie = function(id) {
    scheduler._buffer_event_id = id;
    scheduler._buffer_event = scheduler.getEvent(id).getCopie();
    scheduler._buffer_event.id = id;
    scheduler.updateEvent(id);
};
scheduler.unactivateCopie = function() {
    if (scheduler._buffer_event_id != null) {
        let id = scheduler._buffer_event_id;
        scheduler._buffer_event_id = null;
        scheduler._buffer_event = null;
        scheduler.updateEvent(id);
    }
};

scheduler._click.buttons.delete_old = scheduler._click.buttons.delete;
scheduler._click.buttons.delete = function(id) {
    var res = scheduler.callEvent("onBeforeEventDelete", [id, scheduler.getEvent(id)]);
};

scheduler.displaySerieModalBox = function(action, ev, unique) {

    var action = action;
    var ev = ev;
    ev.dependanceSerie = true;


    dhtmlx.modalbox({
        text: action == 'update' ? Translator.trans("scheduler.message.update") : Translator.trans("scheduler.message.suppression"),
        width: "500px",
        position: "middle",
        buttons: [
            unique ? Translator.trans("scheduler.action." + action) + " " + Translator.trans("scheduler.message.action.occurence") : Translator.trans("scheduler.action." + action) + " " + Translator.trans("scheduler.message.action.serie"),
            //Translator.trans("scheduler.action."+action)+" "+Translator.trans("scheduler.message.action.occurence"),
            scheduler.locale.labels.icon_cancel
        ],
        callback: function(rep) {
            //callback with the data of the modal
            if (rep == 0) {
                let event = scheduler._events[ev.id];
                if (action == "delete") {
                    let serieId = event.evenement.serie.id;
                    isThereInscritValide(serieId, function(result) {
                        if (result) {
                            messageErreurSuppression('impossible');
                        } else {
                            isThereInscritAttente(serieId, function(result) {
                                if (result) {
                                    messageErreurSuppression('prevention', function(result) {
                                        if (result == 0) {
                                            suppressionInscriptionAttente(serieId, function() {
                                                event.dependanceSerie = true;
                                                event.getParent().updateSerie(event, action);
                                            });
                                        }
                                    });
                                } else {
                                    event.dependanceSerie = true;
                                    event.getParent().updateSerie(event, action);
                                }
                            });
                        }
                    });
                } else {
                    event.dependanceSerie = true;
                    event.getParent().updateSerie(event, action);
                }
                // } else if (rep == 1) {
                //     let event = scheduler._events[ev.id];
                //     if (action == "delete") {
                //         ev.save("delete");
                //     } else {
                //         event.update();
                //     }
            } else {
                loadData(scheduler._events[ev.id]);
                changeColor(ev.id);
                return false;
            }
        }
    });
};

scheduler.displayDeleteModalBox = function(callback) {
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

function isThereInscritValide(serieId, callback) {
    $.ajax({
        method: "POST",
        url: Routing.generate('DhtmlxSerieInscrit'),
        data: {
            id: serieId,
            statut: 'valide'
        },
        success: function(code_html, statut) {},
        error: function(resultat, statut, erreur) {},
        complete: function(resultat, statut) {
            callback(resultat.responseJSON);
        }
    });
}

function isThereInscritAttente(serieId, callback) {
    $.ajax({
        method: "POST",
        url: Routing.generate('DhtmlxSerieInscrit'),
        data: {
            id: serieId,
            statut: 'attente'
        },
        success: function(code_html, statut) {},
        error: function(resultat, statut, erreur) {},
        complete: function(resultat, statut) {
            callback(resultat.responseJSON);
        }
    });
}

function messageErreurSuppression(erreur, callback) {
    if ($('.dhtmlx_modal_box')[0]) {
        return false;
    }
    if (erreur == 'impossible') {
        displayErrorMessage(Translator.trans("scheduler.error.suppression.inscrit"));
        return false;
    } else if (erreur == 'prevention') {
        dhtmlx.modalbox({
            text: Translator.trans("scheduler.warning.suppression.inscrit"),
            width: "500px",
            position: "middle",
            buttons: [Translator.trans("common.oui"), Translator.trans("common.non")],
            callback: function(index) {
                callback(index);
            }
        });
        return false;
    }
}

function suppressionInscriptionAttente(serieId, callback) {
    $.ajax({
        method: "POST",
        url: Routing.generate('DhtmlxAnnulerInscription'),
        data: {
            id: serieId
        },
        success: function(code_html, statut) {},
        error: function(resultat, statut, erreur) {},
        complete: function(resultat, statut) {
            callback();
        }
    });
}