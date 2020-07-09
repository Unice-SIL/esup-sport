
/* Gestion modal OpenLayersMap */

_uca.openlayersmap = {};

_uca.openlayersmap.init = function() {
    _uca.openlayersmap.htmlTemplate = $('#modalOpenLayersMap .modal-dialog').html();
    _uca.openlayersmap.libelle = '';
    _uca.openlayersmap.pmr = '';
    _uca.openlayersmap.adresse = '';
    _uca.openlayersmap.villeCP = '';
    _uca.openlayersmap.campus = '';
    _uca.openlayersmap.latitude = '';
    _uca.openlayersmap.longitude = '';
}

_uca.openlayersmap.createmap = function(){
    let ol = require('ol');
    let source = require('ol/source');
    let layer = require('ol/layer');    
    let geom = require('ol/geom');
    let style = require('ol/style');
    require('ol/ol.css');

    _uca.openlayersmap.latitude = $(this).data('latitude');
    _uca.openlayersmap.longitude = $(this).data('longitude');
    _uca.openlayersmap.id = "openlayersmap"+$(this).data('id');

    if(_uca.openlayersmap.latitude != undefined && _uca.openlayersmap.longitude != undefined && _uca.openlayersmap.latitude != '' && _uca.openlayersmap.longitude != ''){
        //Création de la carte
        let map = new ol.Map({
            target: _uca.openlayersmap.id,
            layers: [
                new layer.Tile({
                    source: new source.OSM()
                }),
                new layer.Vector({ //ajout pointeur
                    source: new source.Vector({
                        features: [
                            new ol.Feature({
                                geometry: new geom.Point([_uca.openlayersmap.longitude, _uca.openlayersmap.latitude ])
                            })
                        ]
                    }),
                    style: new style.Style({
                        image: new style.Icon({
                            anchor: [0.5, 0.5],
                            anchorXUnits: "fraction",
                            anchorYUnits: "fraction",
                            src:"https://upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Map_pin_icon.svg/25px-Map_pin_icon.svg.png"
                        })
                    })
                })
            ],
            view: new ol.View({              
                projection: 'EPSG:4326',
                center: [_uca.openlayersmap.longitude, _uca.openlayersmap.latitude ],
                zoom: 15
            })              
        });

        //Supprime des éléments de la carte mis par défaut inutile
        $('.ol-attribution').remove();

        //On définit la taille de la carte en px suivant taille écran client           
        screen.width >= 500 ? map.values_.size[0] = 500 : map.values_.size[0] = screen.width; //width
        map.values_.size[1] = 300; //height

    }else{
        _uca.openlayersmap.id = "#"+_uca.openlayersmap.id;
        $(_uca.openlayersmap.id).hide();
    }    
}

_uca.openlayersmap.addButtonEvent = function() {    
    $(this).click(function () {
        
        let ol = require('ol');
        let source = require('ol/source');
        let layer = require('ol/layer');    
        let geom = require('ol/geom');
        let style = require('ol/style');
        require('ol/ol.css');

        _uca.openlayersmap.libelle = $(this).data('libelle');
        _uca.openlayersmap.pmr = $(this).data('pmr');
        _uca.openlayersmap.adresse = $(this).data('adresse');
        _uca.openlayersmap.villeCP = $(this).data('villecp');
        _uca.openlayersmap.campus = $(this).data('campus');
        _uca.openlayersmap.latitude = $(this).data('latitude');
        _uca.openlayersmap.longitude = $(this).data('longitude');
        _uca.openlayersmap.visiteVirtuelle = $(this).data('visitevirtuelle');

        $('#modalOpenLayersMap .modal-dialog').html(_uca.openlayersmap.htmlTemplate);
        $("#ressource_libelle")[0].textContent = _uca.openlayersmap.libelle;
        $("#ressource_adresse")[0].textContent = _uca.openlayersmap.adresse;
        $("#ressource_villeCP")[0].textContent = _uca.openlayersmap.villeCP;
        $("#ressource_campus")[0].textContent = _uca.openlayersmap.campus;
        if(!_uca.openlayersmap.pmr){
            $("#logo_acces_pmr").hide();
        }
        if(_uca.openlayersmap.visiteVirtuelle){
            let visite = '<a href="lienvisitevirtuelle"><i class="fas fa-vr-cardboard"></i></a>';
            visite = visite.replace("lienvisitevirtuelle", _uca.openlayersmap.visiteVirtuelle);
            $("#logo_visite_virtuelle").append(visite);
        }

        if(_uca.openlayersmap.latitude != undefined && _uca.openlayersmap.longitude != undefined && _uca.openlayersmap.latitude != '' && _uca.openlayersmap.longitude != ''){
            //Création de la carte
            let map = new ol.Map({
                target: "openlayersmap",
                layers: [
                    new layer.Tile({
                        source: new source.OSM()
                    }),
                    new layer.Vector({ //ajout pointeur
                        source: new source.Vector({
                            features: [
                                new ol.Feature({
                                    geometry: new geom.Point([_uca.openlayersmap.longitude, _uca.openlayersmap.latitude ])
                                })
                            ]
                        }),
                        style: new style.Style({
                            image: new style.Icon({
                                anchor: [0.5, 0.5],
                                anchorXUnits: "fraction",
                                anchorYUnits: "fraction",
                                src:"https://upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Map_pin_icon.svg/25px-Map_pin_icon.svg.png"
                            })
                        })
                    })
                ],
                view: new ol.View({              
                    projection: 'EPSG:4326',
                    center: [_uca.openlayersmap.longitude, _uca.openlayersmap.latitude ],
                    zoom: 15
                })              
            });

            //Supprime des éléments de la carte mis par défaut inutile
            $('.ol-attribution').remove();

            //On définit la taille de la carte en px suivant taille écran client           
            screen.width >= 500 ? map.values_.size[0] = 500 : map.values_.size[0] = screen.width; //width
            map.values_.size[1] = 300; //height

        }else{
            $("#openlayersmap").hide();
        }    
    });
};
