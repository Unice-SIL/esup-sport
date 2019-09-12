
$("#mailButton").on("click", function(){
    $('#modalMail').modal();
});

/* $("#ucabundle_mail_save").on("click", function(e){
    e.preventDefault();
    console.log("save button is pressed");
    console.log( $( this ).serialize() );
}); */
$(function() {
    $('form[name="ucabundle_mail"]').on("submit", function(e){
        e.preventDefault();
        let data = $(this).serializeArray();
        sendForm(data);
        return false;
    });
});

$("#ucabundle_mail_save").on("click", function(){
    $(".modal").modal('toggle'); 
});

var sendForm = function(data){

    $.ajax({
        url: PATH_SEND_MAIL,
        type: "POST",
        data: data
    }).done(function(){
    }).fail(_uca.ajax.fail);
}
