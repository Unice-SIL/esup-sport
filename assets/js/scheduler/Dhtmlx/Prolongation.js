import {Mail} from "./Mail.js";
import {Registered} from "./Registered.js";
import {Evenement} from "./Evenement";
import {transformDate} from "./handleEvent";
import {dateToStr} from "./Date";
import {loadData} from "./Config";
import {changeColor} from "./Events";
import {Load} from "./Load";
import {More} from "./More";


//Ajax pour le controller qui ajoutera les nouveaux creneaux
$('#btn-extend-send').on('click', function() {
    if($('.dhtmlx_modal_box')[0]){
        return false;
    }
    if($('#dhx_extend_date_occurence').val() < 1 || !$('#dhx_extend_date_occurence').val()){
        displayErrorMessage(Translator.trans("scheduler.error.occurence.prolongation"));
        return false;
    }else if(!$('#dhx_extend_date').val()){
        displayErrorMessage(Translator.trans("scheduler.error.date.prolongation"));
        return false;
    }

    var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
    var dateDebut = $('#dhx_extend_date').val();
    var repetition = $('#dhx_extend_date_occurence').val();
    var pathUrl = Routing.generate('DhtmlxApi');
    ev.action = 'extend';
    ev.dateDebutRepetition = dateDebut;
    ev.nbRepetition = repetition;
    ev.itemId = ITEM.id;
    ev.typeA = typeA;
    var obj = ev.serialize();
    
    $.ajax({
        type: 'POST',
        url: pathUrl,
        data: {
            evenement: obj
        }
    })
    .done(function(data) {
        initLoadData(data);
        $('#dhx_extend_date').val('');
        $('#dhx_extend_date_occurence').val('');
    }).fail(_uca.ajax.fail);      
    
    scheduler.endLightbox(true, custom_form);
});

//needs to be attached to the 'cancel' button
$('#btn-extend-back').on('click', function() {
    scheduler.endLightbox(false, custom_form);
});

function displayErrorMessage (message){
    dhtmlx.modalbox({
        text: message,
        width: "500px",
        position: "middle",
        buttons: [
            "Ok",
        ],
    });
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
    Load.stop();
}

//Prolongation
$(document).ready(function (){
    
    scheduler._click.buttons.prolonger = function (id) {
        scheduler.resetLightbox();
        scheduler.startLightbox(id, document.getElementById("custom_form"));
    };

});
