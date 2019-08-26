import { cpus } from "os";

$(".calendar-time-slot").on("click", function(){
    if($(this).hasClass("notFull")){
        defaultElement();
        $(this).addClass("activeCalendar");
        $("#inscription").data("id", $(this).attr("elId"));
        $("#inscription").data("id-format", $(this).attr("formatId"));
    }
});

var defaultElement = function (){
    let el = $($(".activeCalendar")[0]);
    el.removeClass("activeCalendar")
}

var changeStatut = function(itemid, error){

    //the user is register to the reservation
    //change event statut
    if(error == 0){
        let calendarElement = $('.calendar-time-slot[elid="'+itemid+'"]');

        calendarElement.removeClass("notFull");
        calendarElement.addClass("register");
        calendarElement.find(".available").remove()

        //remove data on the register buttun to prevent the user to subscribe to the same event
        $('.js-inscription').each(function(){
            $(this).removeData("id");
        });
    }

}

global.changeStatut = changeStatut;


//mobile

$("#header-left").on("click", function(){
    changeDay("left");
});



$("#header-right").on("click", function(){
    changeDay("right");
});


var changeDay = function(direction){
    let day = parseInt($(".week-active").attr("day"));
    //we need to change the week
    if((direction == "left" && day == 1 ) || (direction == "right" && day == 7)){
        document.location.href = $("#header-"+direction).attr("url");
        
        return;
    }  
    let newDay = direction == "left" ? day-1 : day+1;
    $(".week-active").addClass("d-none").removeClass("week-active");
    $(".week-mobile[day="+newDay+"]").removeClass("d-none").addClass("week-active");

    //get the day
    $("#header-mid").html($(".week-active").attr("dayTrans"));

}

$("#header-mid").html($(".week-active").attr("dayTrans"));

