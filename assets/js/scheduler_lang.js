
var scheduler_lang = [];


//laoding lang
var loadLang = function(lang){
    require("../libs/dhtmlxScheduler-5.1.6/sources/locale/locale_"+lang+".js");
    scheduler_lang[lang] =  scheduler.locale;

}

var load = function(lang){
    lang.forEach(loadLang);

    return scheduler_lang;
}

export {load};
