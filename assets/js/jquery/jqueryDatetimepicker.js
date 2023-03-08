/**
 * Initialisation du date-time-picker
 * Parametrage:
 *      - s'applique sur les champs dont la classe est "datetimepicker"
 *      - le format doit etre preciser dans le "ata-datetimepicker-format"
 * Options :
 *      - pour preciser le pas pour le choix des minutes, remplir le champ "data-datetimepicker-step" (pas de 60 minutes par defaut)
 *      - pour preciser l'heure minimum, remplir le champ "data-datetimepicker-mintime"
 *      - pour preciser l'heure maximum, remplir le champ "data-datetimepicker-maxtime"
 */

jQuery.datetimepicker.setLocale($("html").attr("lang"));
$( ".datetimepicker" ).each(function( index ) {
    var dateValue = $(this).val();
    var format = $(this).data("datetimepicker-format");
    var timepicker = format.indexOf('H') >= 0 || format.indexOf('i') >= 0;
    var datepicker = format.indexOf('d') >= 0 || format.indexOf('M') >= 0 || format.indexOf('y') >= 0;
    var step = $(this).data("datetimepicker-step");
    var minTime = $(this).data("datetimepicker-mintime");
    var maxTime = $(this).data("datetimepicker-maxtime");
    var defaultDate = $(this).data("datetimepicker-defaultdate");
    if(typeof defaultDate == 'undefined'){
        defaultDate = dateValue;
    }
    
    $(this).datetimepicker({
        format:format,
        timepicker:timepicker,
        datepicker: datepicker,
        step:step,
        minTime:minTime,
        maxTime:maxTime,
        value:dateValue+"",
        defaultDate:defaultDate+"",
    });
});