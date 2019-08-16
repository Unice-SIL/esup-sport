// const moment = require('moment');
var __dir = {
    vendor: '../vendor/',
    js: '/Resources/public/js/', 
    getJs: function(bundle, file) {
        return __dir.vendor + bundle + __dir.js + file;
    }
}

const $ = require('jquery');
global.$ = global.jQuery = $;

const moment = require('moment');
global.moment = moment;

require('bootstrap');
const dt = require('datatables.net-bs4');

require('select2/dist/css/select2.css');
require('select2');
require('@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.css');


const pipeline   = require('./bundles/sgdatatables/js/pipeline.js');

//require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

/* bazingajstranslation */
const Translator = require("./bundles/bazingajstranslation/js/translator.min.js");
global.Translator = Translator;
require("./bundles/bazingajstranslation/translations/fr.js");
require("./bundles/bazingajstranslation/translations/en.js");

const routes     = require('./bundles/fosjsrouting/fos_routes.json');
const Routing    = require('./bundles/fosjsrouting/js/router.min.js');
Routing.setRoutingData(routes);
if(ENV.getAttribute("data-value") == 'dev')
    Routing.setBaseUrl('/Uca/web/app_dev.php');
else 
    Routing.setBaseUrl('');

global.Routing = Routing;

import "./libs/dhtmlxScheduler-5.1.6/sources/dhtmlxscheduler.js";
// Attention modification dhtmlxscheduler.js ligne 298 :
// window.convertStringToBoolean = convertStringToBoolean;

import * as scheduler_lang from "./js/scheduler_lang.js";
global.scheduler_lang = scheduler_lang.load(["fr","en", "it"]);




import "./libs/dhtmlxScheduler-5.1.6/sources/locale/recurring/locale_recurring_fr.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_editors.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_key_nav.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_minical.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_multiselect.js";
import "./libs/dhtmlxScheduler-5.1.6/sources/ext/dhtmlxscheduler_recurring.js";

import "./js/_uca.js";
