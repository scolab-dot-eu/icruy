/**
 * Importamos las librerías, estilos y componentes
 */

//window.serviceURL = 'http://geoportal.opp.com';
window.serviceURL = 'http://icr-test.opp.gub.uy';
//window.serviceURL = 'http://geoportal.opp.localhost';
//window.serviceURL = 'http://localhost:8000';
window.editionMode = false;

require('bootstrap_css');
require("leaflet_css");
require('leaflet_logo_css');
require('leaflet_minimap_css');
require('leaflet_draw_css');
require('leaflet_easybutton_css');
require('leaflet_navbar_css');
require('leaflet_topcenter_css');
require('leaflet_graphic_scale_css');
require('leaflet_search_css');
require('leaflet_measure_css');
require('leaflet_timedimension_css');

require('fontawesome');
var L = require('leaflet');

require('leaflet-bing-layer');
require('bootstrap_js');
require('webpack-jquery-ui');
require('webpack-jquery-ui/css');
require('leaflet-easybutton');
require('leaflet-search');
require('leaflet_logo_js');
require('leaflet_topcenter_js');
require('leaflet_timedimension_js');
require('leaflet-graphicscale');


var MiniMap = require('leaflet-minimap');
var Toc = require('./components/toc/Toc.js');
var ToolBar = require('./components/toolbar/ToolBar.js');
var Utils = require('./components/Utils.js');
require("style_css");
require('leaflet-ajax');
require('leaflet-draw');
require('leaflet-navbar');
require('leaflet_measure_css');
require('leaflet_measure_js');
require('leaflet_coordinates_css');
require('leaflet_coordinates_js');

var config = null;
var departamento = null;

L_PREFER_CANVAS = true;

var editableLayers = [];
var inEdition = false;

window.isMobile = false;
if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
    window.isMobile = true;
}

initialize();

function initialize() {

    var mask = document.getElementById("loading-signal");
    document.body.removeChild(mask);

    var map = null;
    var searchParams = new URLSearchParams(window.location.search);

    if (searchParams.has('departamento')) {
        departamento = searchParams.get('departamento');
        $.ajax({
            url: window.serviceURL + '/api/config/department/' + departamento,
            async: false

        }).done(function(resp) {
            //config = JSON.parse(resp);
            config = resp;
            map = L.map('map', { zoomControl:false, preferCanvas: true});
            map.fitBounds([
                [config.department.miny, config.department.minx],
                [config.department.maxy, config.department.maxx]
            ]);

        }).fail(function(error) {
            console.log( "Error al cargar configuración" );
        });

    } else {
        //config = require('./../src/config.json');
        //map = L.map('map', { zoomControl:false, preferCanvas: true}).setView(config.map.center, config.map.zoom);
        
        $.ajax({
            url: window.serviceURL + '/api/config/global',
            async: false

        }).done(function(resp) {
            config = JSON.parse(resp);
            //config = resp;
            map = L.map('map', { 
                zoomControl:false, 
                preferCanvas: true
            }).setView(config.map.center, config.map.zoom);

        }).fail(function() {
            console.log( "Error al cargar configuración" );
        });
    }

    if (config.user) {
        window.editionMode = true;
    }

    var utils = new Utils(config);
    var tocBaseLayers = loadBaseLayers(map);
    var overlaysObject = loadOverlays(map);
    var controls = loadControls(map, tocBaseLayers, overlaysObject, utils);
    registerMapEvents(map, controls, utils);
}



/**
 * Cargamos las capas base
 */
function loadBaseLayers(map) {
    var tocBaseLayers = [];
    for (var i=0; i<config.baselayers.groups.length; i++) {
        var group = {
            groupName: config.baselayers.groups[i].title,
            expanded: config.baselayers.groups[i].expanded,
            layers: {}
        }

        for (var j=0; j<config.baselayers.groups[i].layers.length; j++) {
            var layer = null;
            if (config.baselayers.groups[i].layers[j].type == 'bing') {
                layer = L.tileLayer.bing({
                    bingMapsKey: config.baselayers.groups[i].layers[j].api_key,
                    imagerySet: config.baselayers.groups[i].layers[j].name
                });

            } else if (config.baselayers.groups[i].layers[j].type == 'tilelayer') {
                layer = L.tileLayer(config.baselayers.groups[i].layers[j].url, {
                    maxZoom: 18
                });

            } else if (config.baselayers.groups[i].layers[j].type == 'wms') {
                layer = L.tileLayer.wms(config.baselayers.groups[i].layers[j].url, {
                    layers: config.baselayers.groups[i].layers[j].name
                });

            } else if (config.baselayers.groups[i].layers[j].type == 'empty') {
                layer = L.tileLayer('');
            }
            if (config.baselayers.groups[i].layers[j].visible) {
                layer.addTo(map);
            }
            group.layers[config.baselayers.groups[i].layers[j].title] = layer;
        }
        tocBaseLayers.push(group);
    }

    return tocBaseLayers;
}

/**
 * Cargamos el resto de capas
 */
function loadOverlays(map) {

    var tocOverlays = [];
    var groupedOverlays = [];
    var styles = {};
    for (var i=0; i<config.overlays.groups.length; i++) {
        var group = {
            groupName: config.overlays.groups[i].title,
            expanded: config.overlays.groups[i].expanded,
            layers: {}
        }

        for (var j=0; j<config.overlays.groups[i].layers.length; j++) {
            var layer = null;
            if (config.overlays.groups[i].layers[j].type == 'tilelayer') {
                layer = L.tileLayer(config.overlays.groups[i].layers[j].url, {
                    maxZoom: 18
                });

            } else if (config.overlays.groups[i].layers[j].type == 'wms') {
                layer = L.tileLayer.wms(config.overlays.groups[i].layers[j].url, {
                    service: 'WMS',
                    version: '1.1.0',
                    format: 'image/png',
                    transparent: 'true',
                    layers: config.overlays.groups[i].layers[j].name
                });
                layer.type = 'wms';

            } else if (config.overlays.groups[i].layers[j].type == 'wfs') {

                var defaultParameters = {
                    service: 'WFS',
                    version: '1.0.0',
                    request: 'getFeature',
                    typeName: config.overlays.groups[i].layers[j].name,
                    //maxFeatures: 500,
                    outputFormat: 'application/json'
                };

                var customParams = {};
                if (departamento != null) {
                    for (var k=0; k<config.inventory_layers.length; k++) {
                        if (config.inventory_layers[k] == config.overlays.groups[i].layers[j].name) {
                            customParams['cql_filter'] = "departamento='" + departamento + "'";
                        }
                    }
                }
                
                var parameters = L.Util.extend(defaultParameters, customParams);

                function popUp(f,l){
                    var editable = false;
                    var eLayer = null;
                    if (f.properties){
                        var html = '';
                        var fLayerName = f.id.split('.')[0];
                        for (i in editableLayers) {
                            if (editableLayers[i].name == fLayerName) {
                                editable = true;
                                eLayer = editableLayers[i].layer;
                            }
                        }

                        
                        html += '<div>';
                        html += '<table>';
                        for (var key in f.properties) {
                            if (key != 'created_at' && key != 'updated_at' && key != 'modified_by' && key != 'last_modification') {
                                html += '<tr>';
                                html +=     '<td style="padding: 2px; text-transform: uppercase; color: #e0a800;">' + key + '</td>';
                                html +=     '<td style="padding: 2px;">' + f.properties[key] + '</td>';
                                html += '</tr>';
                            }

                        }
                        html += '</table>';
                        html += '</div>';
                        if (!window.isMobile) {
                            html += '<ul class="custom-actions">';
                            if (window.editionMode) {
                                html += '<li><a href="#" data-layername="' + fLayerName + '" data-fid="' + f.id + '" class="popup-toolbar-button-info"><i class="fa fa-info m-r-5"></i> Información</a></li>';
                                html += '<li><a href="#" data-layername="' + fLayerName + '" data-fid="' + f.id + '" class="popup-toolbar-button-edit"><i class="fa fa-edit m-r-5"></i> Editar</a></li>';
                                html += '<li><a href="#" data-layername="' + fLayerName + '" data-fid="' + f.id + '" class="popup-toolbar-button-delete"><i class="fa fa-trash m-r-5"></i> Eliminar</a></li>';
                                
                            } else {
                                html += '<li><a href="#" data-layername="' + fLayerName + '" data-fid="' + f.id + '" class="popup-toolbar-button-info"><i class="fa fa-info m-r-5"></i> Información</a></li>';
                            }
                            html += '</ul>';
                        }
                        
                        l.bindPopup(html, {closeOnClick: false});

                        
                    }
                }

                if (config.overlays.groups[i].layers[j].style) {
                    if (config.overlays.groups[i].layers[j].geom_style == 'marker') {
                        if (config.overlays.groups[i].layers[j].name.split(':').length > 1) {
                            styles[config.overlays.groups[i].layers[j].name.split(':')[1]] = config.overlays.groups[i].layers[j].style;
                        } else {
                            styles[config.overlays.groups[i].layers[j].name] = config.overlays.groups[i].layers[j].style;
                        }

                        layer = L.geoJson.ajax(
                            config.overlays.groups[i].layers[j].url + L.Util.getParamString(parameters),
                            {
                                onEachFeature: popUp,
                                pointToLayer: function (feature, latlng) {
                                    var icon = new L.Icon(styles[feature.id.split('.')[0]]);
                                    if (feature.properties.status == 'PENDIENTE:BORRADO' || feature.properties.status == 'PENDIENTE:CREACIÓN' || feature.properties.status == 'PENDIENTE:ACTUALIZACIÓN') {
                                        icon.options.iconUrl = require('./../assets/images/' + 'pending-' + icon.options.iconUrl);

                                    } else if(feature.properties.status == 'VALIDADO') {
                                        icon.options.iconUrl = require('./../assets/images/' + 'validated-' + icon.options.iconUrl);
                                        
                                    }

                                    //icon.options.iconUrl = require('./../assets/images/' + 'pending-' + icon.options.iconUrl);
                                    
                                    return L.marker(latlng, {
                                        icon: icon
                                    });
                                }

                            },
                        );
                        layer.geom_style = 'marker';

                    } else if (config.overlays.groups[i].layers[j].geom_style == 'point') {
                        if (config.overlays.groups[i].layers[j].name.split(':').length > 1) {
                            styles[config.overlays.groups[i].layers[j].name.split(':')[1]] = config.overlays.groups[i].layers[j].style;
                        } else {
                            styles[config.overlays.groups[i].layers[j].name] = config.overlays.groups[i].layers[j].style;
                        }

                        layer = L.geoJson.ajax(
                            config.overlays.groups[i].layers[j].url + L.Util.getParamString(parameters),
                            {
                                onEachFeature: popUp,
                                pointToLayer: function (feature, latlng) {
                                    var pointStyle = styles[feature.id.split('.')[0]];
                                    if (feature.properties.status == 'PENDIENTE:BORRADO' || feature.properties.status == 'PENDIENTE:CREACIÓN' || feature.properties.status == 'PENDIENTE:ACTUALIZACIÓN') {
                                        pointStyle.fillOpacity = 0.1;
            
                                    } else if(feature.properties.status == 'VALIDADO') {
                                        pointStyle.fillOpacity = 1;
                                        
                                    }
                                    return L.circleMarker(latlng, pointStyle);
                                }
                            },
                        );
                        layer.geom_style = 'point';

                    } else if  (config.overlays.groups[i].layers[j].geom_style == 'line') {
                        var lineStyle = config.overlays.groups[i].layers[j].style;
                        layer = L.geoJson.ajax(
                            config.overlays.groups[i].layers[j].url + L.Util.getParamString(parameters),
                            {
                                onEachFeature:popUp,
                                style: lineStyle
                            },
                        );
                        layer.geom_style = 'line';

                    } else if  (config.overlays.groups[i].layers[j].geom_style == 'polygon') {
                        var polygonStyle = config.overlays.groups[i].layers[j].style;
                        layer = L.geoJson.ajax(
                            config.overlays.groups[i].layers[j].url + L.Util.getParamString(parameters),
                            {
                                onEachFeature:popUp,
                                style: polygonStyle
                            },
                        );
                        layer.geom_style = 'polygon';
                    }                    

                } else {
                    layer = L.geoJson.ajax(
                        config.overlays.groups[i].layers[j].url + L.Util.getParamString(parameters),
                        {
                            onEachFeature:popUp
                        },
                    );
                }
                
                layer.type = 'wfs';
                layer.isGeojsonLayer = true;
                layer.style = config.overlays.groups[i].layers[j].style;
                if(config.overlays.groups[i].layers[j].fields) {
                    layer.fields = config.overlays.groups[i].layers[j].fields;
                }
                
            }
            layer.showInSearch = config.overlays.groups[i].layers[j].showInSearch;
            layer.title = config.overlays.groups[i].layers[j].title;
            layer.name = config.overlays.groups[i].layers[j].name;
            layer.definedUrl = config.overlays.groups[i].layers[j].url;

            if (config.overlays.groups[i].layers[j].editable) {
                if (config.overlays.groups[i].layers[j].name.split(':').length > 1) {
                    editableLayers.push({
                        name: config.overlays.groups[i].layers[j].name.split(':')[1],
                        layer: layer
                    });
                } else {
                    editableLayers.push({
                        name: config.overlays.groups[i].layers[j].name,
                        layer: layer
                    });
                }
            }
            
            if (config.overlays.groups[i].layers[j].visible) {
                layer.addTo(map);
            }
            group.layers[config.overlays.groups[i].layers[j].title] = layer;

            layer.StyledLayerControl = {
                isEditable : config.overlays.groups[i].layers[j].editable,
                showTable : config.overlays.groups[i].layers[j].showTable,
                download : config.overlays.groups[i].layers[j].download,
                hasMetadata: config.overlays.groups[i].layers[j].hasMetadata,
                metadata: config.overlays.groups[i].layers[j].metadata
            };
            if (config.overlays.groups[i].layers[j].history_layer_name){
                layer.StyledLayerControl.historyLayerName = config.overlays.groups[i].layers[j].history_layer_name;
            }
            if (config.overlays.groups[i].layers[j].wms_url) {
                layer.StyledLayerControl.wmsUrl = config.overlays.groups[i].layers[j].wms_url;
            }
            tocOverlays.push(layer);
        }
        groupedOverlays.push(group);
    }

    return {
        overlays: tocOverlays,
        groupedOverlays: groupedOverlays
    };
}

/**
 * Añadimos los controles y componentes
 */
function loadControls(map, tocBaseLayers, overlays, utils) {

    var logoPosition = 'topleft';
    if (window.isMobile) {
        logoPosition = 'topcenter';
    }
    var lc = L.control.logo({
        position: logoPosition,
        link: '#',
        width: '150px',
        height: '36px',
        image: require('./../assets/images/logo_opp2.png')
    }).addTo(map);

    if (window.isMobile) {
        $('.leaflet-logo-control').css('margin-left', '50px !important');
    }
    
    L.control.zoom({ position:'topright'}).addTo(map);

    L.control.navbar().addTo(map);
    
    if (!window.isMobile) {
        var minimap_layer = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18
        });
        new MiniMap(minimap_layer, {toggleDisplay: true}).addTo(map);

        L.control.measure({ 
            primaryLengthUnit: 'meters', 
            secondaryLengthUnit: 'kilometers',
            primaryAreaUnit: 'sqmeters',
            activeColor: '#ff8000',
            completedColor: '#ff8000'
        }).addTo(map);
    
        L.control.graphicScale({doubleLine: true, fill:true}).addTo(map);
    
        L.control.mousePosition({
            position: 'bottomcenter',
            separator: ' / ', //To separate longitude\latitude values
            emptystring: 'Mover el ratón', //Initial text to display. Defaults to 'Unavailable'
            numDigits: 4, //Number of digits. Defaults to 5
            lngFirst: false, //Weather to put the longitude first or not. Defaults to false
            //lngFormatter: Custom function to format the longitude value. Defaults to undefined
            //latFormatter: Custom function to format the latitude value. Defaults to undefined
            prefix: 'Latitud/Longitud: '//A string to be prepended to the coordinates. Defaults to the empty string ‘’.
        }).addTo(map);
    } 
    var toc = new Toc(config, map, tocBaseLayers, overlays, utils);

    new ToolBar(map, overlays);

    return {
        toc: toc
    }

}

function registerMapEvents(map, controls, utils) {
    map.on('layeradd', function() {
        controls.toc.getLegend().reloadLegend();
    });

    map.on('layerremove', function() {
        controls.toc.getLegend().reloadLegend();
    });

    map.on('popupopen', function() {

        $('.popup-toolbar-button-info').click(function(e){
            for (i in editableLayers) {
                if (editableLayers[i].name == this.dataset.layername) {
                    var id = this.dataset.fid;
                    var editableLayer = editableLayers[i].layer;
                    editableLayer.eachLayer(function(layer) {
                        if (layer.feature.id == id) {
                            loadInfo(controls.toc, layer);
                        }
                    });
                }
            }
        });

        $('.popup-toolbar-button-edit').click(function(e){
            for (i in editableLayers) {
                if (editableLayers[i].name == this.dataset.layername) {
                    var id = this.dataset.fid;
                    var editableLayer = editableLayers[i].layer;
                    editableLayer.eachLayer(function(layer) {
                        if (layer.feature.id == id) {
                            var clonedLayer = layer;
                            if (layer.feature.geometry.type == 'Point') {
                                map.setView(layer.getLatLng(), 15);
                            } else {
                                var bounds = layer.getBounds();
                                map.fitBounds(bounds);
                            }
                            layer.editing.enable();
                            inEdition = true;
                            loadFeatureForm(map, controls.toc, layer, clonedLayer, editableLayer, utils);
                        }
                    });
                }
            }
            
        });

        $('.popup-toolbar-button-delete').click(function(e){
            var dataset = this.dataset;
            $( "#dialog-confirm" ).empty();
            $( "#dialog-confirm" ).append('<p>Va a eliminar el elemento. El cambio será irreversible, ¿Desea continuar?</p>');
            $( "#dialog-confirm" ).dialog({
                resizable: false,
                height: "auto",
                width: "400px",
                modal: true,
                buttons: {
                    "Borrar elemento": function() {
                        $(this).dialog("close");
                        for (i in editableLayers) {
                            if (editableLayers[i].name == dataset.layername) {
                                var id = dataset.fid;
                                var editableLayer = editableLayers[i].layer;
                                editableLayer.eachLayer(function(layer) {
                                    if (layer.feature.id == id) {
                                        deleteElement(editableLayer, layer);
                                    }
                                });
                            }
                        }
                    },
                    Cancel: function() {
                        $(this).dialog("close");
                    }
                }
              });
        });
    });
}

function deleteElement(editableLayer, element) {
    var c = config;
    var data = {
        'operation': 'delete',
        'layer': editableLayer.name,
        'feature': element.toGeoJSON()
    };

    $.ajax({
        url: window.serviceURL + '/api/changerequest',
        type: 'POST',
        async: false,
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",

    }).done(function(resp) {
        if (c.user.isadmin) {
            element.remove();

        } else {
            element.feature.properties.status = 'PENDIENTE:BORRADO';
            /*var pendingIcon = new L.Icon({
                iconUrl: element._icon.src.replace('validated', 'pending'),
                iconSize: [element._icon.width, element._icon.height]
            });
            element.setIcon(pendingIcon);*/
            var style = element.options;
            style.fillOpacity = 0.1;          
            element.setStyle(style);
        }

    }).fail(function(error) {
        console.log( "Error al eliminar" );
    });
}

function updateElement(map, toc, element, editableLayer) {
    var data = {
        'operation': 'update',
        'layer': editableLayer.name,
        'feature': element.toGeoJSON()
    };
    
    $.ajax({
        url: window.serviceURL + '/api/changerequest',
        type: 'POST',
        async: false,
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",

    }).done(function(resp) {
        if (resp.feature.properties.status == 'VALIDADO') {
            element.feature.properties.status = 'VALIDADO';
            var style = element.options;
            style.fillOpacity = 1;          
            element.setStyle(style);

            /*if (element._icon.src.indexOf('pending') >= 0) {
                var pendingIcon = new L.Icon({
                    iconUrl: element._icon.src.replace('pending', 'validated'),
                    iconSize: [element._icon.width, element._icon.height]
                });
                element.setIcon(pendingIcon);               
            }*/

        } else {
            element.feature.properties.status = resp.feature.properties.status;
            /*var pendingIcon = new L.Icon({
                iconUrl: element._icon.src.replace('validated', 'pending'),
                iconSize: [element._icon.width, element._icon.height]
            });
            element.setIcon(pendingIcon);*/
            var style = element.options;
            style.fillOpacity = 0.1;          
            element.setStyle(style);
            
        }
        element.editing.disable();
        map.closePopup();
        $('#toc-result-content').empty();
        toc.getSideBar().open('toc-layers');
        inEdition = false;
        

    }).fail(function(resp) {
        $('#modification-errors').empty();
        for (var key in resp.responseJSON.errors) {
            $('#modification-errors').append('<p style="padding: 5px 20px; color: #ff0000;">' + key + ': ' + resp.responseJSON.errors[key][0] + '</p>');
        } 
    });
}

function loadInfo(toc, layer) {

    $('#toc-result-content').empty();

    var html = '';
    html += '<div class="list-group">';
    for(key in layer.feature.properties){
        html += '<a href="#" class="list-group-item list-group-item-action flex-column align-items-start">';
        html +=     '<div class="d-flex w-100 justify-content-between">';
        html +=         '<h6 style="color: #e0a800;" class="mb-1">' + key + '</h6>';
        html +=     '</div>';
        html +=     '<p class="mb-1">' + layer.feature.properties[key] + '</p>';
        html += '</a>';
    }
    html += '</div>';

    $('#toc-result-content').append(html);
    
    toc.getSideBar().open('toc-result');
}

function loadFeatureForm(map, toc, layer, clonedLayer, editableLayer, utils) {
    $('#toc-result-content').empty();

    var html = '';
    html += '<form id="feature-form">';
    html +=     '<div class="form-group row">';
    html +=         '<div class="col-sm-6">';
    html +=             '<button id="save-edition" type="button" class="btn btn-warning">Guardar cambios</button>';
    html +=         '</div>';
    html +=         '<div class="col-sm-6">';
    html +=             '<button id="cancel-edition" type="button" class="btn btn-secondary">Cancelar edición</button>';
    html +=         '</div>';
    html +=     '</div>';
    html +=     '<div id="modification-errors" class="form-group row">';
    html +=     '</div>';
    for(key in layer.feature.properties){
        if (key != 'created_at' && key != 'updated_at' != key != 'id') {
            html += utils.getAttributeInput(editableLayer.fields, key, layer.feature.properties[key]);
        }      
    }
    html += '</form>';

    $('#toc-result-content').append(html);

    $('#save-edition').on('click', function(){
        var serielizedData = $("#feature-form").serializeArray();
        for (i in serielizedData) {
            layer.feature.properties[serielizedData[i].name] = serielizedData[i].value;
        }
        updateElement(map, toc, layer, editableLayer);
    });

    $('#cancel-edition').on('click', function(){
        layer.editing.disable();
        layer.remove();
        clonedLayer.addTo(map);
        map.closePopup();
        $('#toc-result-content').empty();
        toc.getSideBar().open('toc-layers');
        inEdition = false;
    });
    
    toc.getSideBar().open('toc-result');
}

