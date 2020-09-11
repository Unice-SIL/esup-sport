
var __dir = {
    vendor: '../vendor/',
    js: '/Resources/public/js/',
    getJs: function (bundle, file) {
        return __dir.vendor + bundle + __dir.js + file;
    }
}
/* JQuery */
const $ = require('jquery');
global.$ = global.jQuery = $;

/* Moment */
const moment = require('moment');
global.moment = moment;
 
/* Promise */
var ES6Promise = require("es6-promise");
ES6Promise.polyfill();  

/* Boostrap */
require('bootstrap');
const dt = require('datatables.net-bs4');

/* var globales js */ 
var uca = {
    lang: $('html').attr('lang'),
    sf_env: $('html').data('env'),
    base_url: $('html').data('base'),
    host: $('html').data('host')
}
global.uca = uca;

/* JQuery DateTimePicker */
require('jquery-datetimepicker');
require('jquery-datetimepicker/build/jquery.datetimepicker.min.css');
require("./js/jquery/jqueryDatetimepicker.js");


/* Select2 */
require('select2/dist/css/select2.css');
require('select2');
require('@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.css');
require("./js/jquery/jquerySelect2.js");

/* JQuery Datatable */
require("./js/jquery/jqueryDatatable.js")
const pipeline = require('./bundles/sgdatatables/js/pipeline.js');

/* FontAwesome */
import '@fortawesome/fontawesome-free/js/fontawesome'
import '@fortawesome/fontawesome-free/js/solid'
import '@fortawesome/fontawesome-free/js/regular'
import '@fortawesome/fontawesome-free/js/brands'

/* bazingajstranslation */
const Translator = require("./bundles/bazingajstranslation/js/translator.min.js");
global.Translator = Translator;
require("./bundles/bazingajstranslation/translations/fr.js");
require("./bundles/bazingajstranslation/translations/en.js");

/* FoS JS Routing */
const routes = require('./bundles/fosjsrouting/fos_routes.json');
const Routing = require('./bundles/fosjsrouting/js/router.min.js');
Routing.setRoutingData(routes);
Routing.setBaseUrl(uca.base_url);

global.Routing = Routing;
Routing.generateOld = Routing.generate;
Routing.generate = function (route, params = {}) {
    params['_locale'] = uca.lang;
    return Routing.generateOld(route, params);
}

/* DHTMLX Scheduler */
import "./libs/dhtmlxScheduler-5.1.6/sources/dhtmlxscheduler.js";
// Attention modification dhtmlxscheduler.js ligne 298 :
// window.convertStringToBoolean = convertStringToBoolean;

import * as scheduler_lang from "./js/scheduler_lang.js";
global.scheduler_lang = scheduler_lang.load([uca.lang]);

import("./libs/dhtmlxScheduler-5.1.6/sources/locale/recurring/locale_recurring_" + uca.lang + ".js");
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_editors.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_key_nav.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_minical.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_multiselect.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_recurring.js";

/* Custom imports */
import "./js/_uca.js";
import "./js/_uca_ajax.js";
import "./js/_uca_bascule.js";
import "./js/_uca_capaciteProfils.js";
import "./js/_uca_common.js";
import "./js/_uca_extraction.js";
import "./js/_uca_inscription.js";
import "./js/_uca_mail.js";
import "./js/_uca_multipleImages.js";
import "./js/_uca_openlayersmap.js";
import "./js/_uca_timer.js";
import "./js/mail.js";

import "./js/bootstrap/bootstrapModal.js";
import "./js/bootstrap/bootstrapTooltip.js";
import "./js/chartJS/main.js";