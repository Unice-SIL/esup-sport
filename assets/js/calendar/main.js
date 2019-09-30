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

//mobile
$(document).ready(function(){
    changeJour(1,$("#dayWeekParameter").val());
});

$("#header-left").on("click", function(){
    changeDay("left");
});



$("#header-right").on("click", function(){
    changeDay("right");
});

$(document).on("click", ".changeJour", function(){
    let targetDay = $(this).attr('targetday');
    let baseDay= $(this).attr('baseday');
    changeJour(baseDay, targetDay);
});

function changeJour(baseDay, targetDay){
    var i;
    for (i = 0; i < targetDay-baseDay; i++) {
        changeDay("right");
    }
}

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

