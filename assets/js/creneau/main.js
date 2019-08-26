import { cpus } from "os";

$(function(){
    $('#selectDays').change(function(){

        document.location.href = $('option:selected', this).attr('url');;
    });
});

