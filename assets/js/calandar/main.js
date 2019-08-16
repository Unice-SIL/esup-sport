$(".calandar-element").on("click", function(){
    if($(this).hasClass("notFull")){
        defaultElement();
        $(this).addClass("activeCalandar");
    }
});

var defaultElement = function (){
    let el = $($(".activeCalandar")[0]);
    el.removeClass("activeCalandar")

}

$("#inscription").on("click", function(){
    id = $($(".activeCalandar")[0]).attr("elId");

    console.log(id);
});