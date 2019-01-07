function Draw(config, map, sidebar, utils) {
    this.map = map;
    this.config = config;
    this.sidebar = sidebar;
    this.utils = utils;
    this.drawControl = null;
    this.editableLayer = null;
    this.departamento = null;
    this.inEdition = false;

    L.drawLocal.draw.toolbar.buttons.marker = 'Dibujar punto';
    L.drawLocal.draw.toolbar.buttons.polyline = 'Dibujar linea';

    L.drawLocal.draw.handlers.marker.tooltip.start = 'Hacer click en el mapa para situar el punto';
    L.drawLocal.draw.handlers.polyline.error = '<strong>Error:</strong> Los bordes no pueden cruzarse!';
    L.drawLocal.draw.handlers.polyline.error = '<strong>Error:</strong> Los bordes no pueden cruzarse!';
    L.drawLocal.draw.handlers.polyline.tooltip.start = 'Hacer click para comenzar a dibujar';
    L.drawLocal.draw.handlers.polyline.tooltip.cont = 'Hacer click para continuar dibujando';
    L.drawLocal.draw.handlers.polyline.tooltip.end = 'Click en el último punto para terminar de dibujar';

    var searchParams = new URLSearchParams(window.location.search);

    if (searchParams.has('departamento')) {
        this.departamento = searchParams.get('departamento');
    }

    this.registerEvents();
}
   
Draw.prototype = {  
    startDraw: function(layer) {
        this.editableLayer = layer;
        var options = {
            position: 'topleft',
            draw: {},
            edit: false
        };
        var features = new L.FeatureGroup();
        layer.eachLayer(
            function(l){
                features.addLayer(l);
            }
        );

        var geomType = 'Point';
        for (key in features._layers) {
            geomType = features._layers[key].feature.geometry.type;
            break;
        }
        if (geomType == 'Point') {
            options['draw'].marker = false;
            options['draw'].polyline = false;
            options['draw'].polygon = false;
            options['draw'].circle = false;
            options['draw'].circle = false;
            options['draw'].circlemarker = true;
            options['draw'].rectangle = false;

        } else if (geomType == 'LineString' || geomType == 'MultiLineString') {
            options['draw'].marker = false;
            options['draw'].polygon = false;
            options['draw'].circle = false;
            options['draw'].circle = false;
            options['draw'].circlemarker = false;
            options['draw'].rectangle = false;
        }
        else if (geomType == 'Polygon' || geomType == 'MultiPolygon') {
            options['draw'].marker = false;
            options['draw'].polyline = false;
            options['draw'].circle = false;
            options['draw'].circle = false;
            options['draw'].circlemarker = false;
            options['draw'].rectangle = false;
        }
        options.edit.featureGroup = features;
        this.drawControl = new L.Control.Draw(options);
        this.map.addControl(this.drawControl);
    },

    stopDraw: function(layer){
        this.map.removeControl(this.drawControl);
        this.drawControl = null;
    },

    isActive: function(){
        var active = false;
        if (this.drawControl != null) {
            active = true;
        }
        return active;
    },

    registerEvents: function(){
        var _this = this;
        this.map.on(L.Draw.Event.CREATED, function (e) {
            var type = e.layerType,
                layer = e.layer;
            if (type === 'marker') {
                // Do marker specific actions
            }
            // Do whatever else you need to. (save to db; add to map etc)
            //var icon = L.icon(_this.editableLayer.style);
            //icon.options.iconUrl = require('../../../assets/images/pending-' + icon.options.iconUrl)
            //layer.setIcon(icon);
            layer.setStyle(_this.editableLayer.style);
            _this.editableLayer.addLayer(layer);
            _this.loadFeatureForm(layer);
         });
    },

    loadFeatureForm: function(layer) {
        var _this = this;
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
        html +=     '<div id="creation-errors" class="form-group row">';
        html +=     '</div>';
        for(i in this.editableLayer.fields){
            if (this.editableLayer.fields[i].name != 'id' && this.editableLayer.fields[i].name != 'created_at' && this.editableLayer.fields[i].name != 'updated_at') {
                html += this.utils.getAttributeEmptyInput(this.editableLayer.fields[i]);
            }
        }
        html += '</form>';
    
        $('#toc-result-content').append(html);
    
        $('#save-edition').on('click', function(){
            var serielizedData = $("#feature-form").serializeArray();
            layer.feature = {};
            layer.feature.properties = {};
            layer.feature['type'] = 'Feature';
            layer.feature['geometry_name'] = 'thegeom';
            layer.feature['geometry'] = {
                type: 'Point',
                coordinates: [layer._latlng.lat, layer._latlng.lng]
            };
            for (i in serielizedData) {
                layer.feature.properties[serielizedData[i].name] = serielizedData[i].value;
            }
            layer.feature.properties['departamento'] = _this.departamento;
            _this.saveElement(layer);
        });
    
        $('#cancel-edition').on('click', function(){
            layer.editing.disable();
            layer.remove();
            _this.map.closePopup();
            $('#toc-result-content').empty();
            _this.sidebar.open('toc-layers');
        });
        
        this.sidebar.open('toc-result');
    },

    bindPopup: function(layer) {
        var _this = this;
        var html = '';
        html += '<div>';
        html += '<table>';
        for (var key in layer.feature.properties) {
            if (key != 'created_at' && key != 'updated_at' && key != 'modified_by' && key != 'last_modification') {
                html += '<tr>';
                html +=     '<td style="padding: 2px; text-transform: uppercase; color: #e0a800;">' + key + '</td>';
                html +=     '<td style="padding: 2px;">' + layer.feature.properties[key] + '</td>';
                html += '</tr>';
            }
        }
        html += '</table>';
        html += '</div>';
        html += '<ul class="custom-actions">';
        html +=     '<li><a href="#" data-layername="' + this.editableLayer.name + '" data-fid="' + layer.feature.id + '" class="popup-toolbar-button-info"><i class="fa fa-info m-r-5"></i> Información</a></li>';
        html +=     '<li><a href="#" data-layername="' + this.editableLayer.name + '" data-fid="' + layer.feature.id + '" class="popup-toolbar-button-edit"><i class="fa fa-edit m-r-5"></i> Editar</a></li>';
        html +=     '<li><a href="#" data-layername="' + this.editableLayer.name + '" data-fid="' + layer.feature.id + '" class="popup-toolbar-button-delete"><i class="fa fa-trash m-r-5"></i> Eliminar</a></li>';
        html += '</ul>';

        layer.bindPopup(html, {closeOnClick: false});

        this.map.on('popupopen', function() {
            $('.popup-toolbar-button-info').click(function(e){
                var id = this.dataset.fid;
                _this.editableLayer.eachLayer(function(layer) {
                    if (layer.feature.id == id) {
                        _this.loadInfo(layer);
                    }
                });
            });

            $('.popup-toolbar-button-edit').click(function(e){
                if (!_this.inEdition) {
                    var id = this.dataset.fid;
                    _this.editableLayer.eachLayer(function(layer) {
                        if (layer.feature.id == id) {
                            var clonedLayer = layer;
                            if (layer.feature.geometry.type == 'Point') {
                                _this.map.setView(layer.getLatLng(), 15);
                            } else {
                                var bounds = layer.getBounds();
                                _this.map.fitBounds(bounds);
                            }
                            layer.editing.enable();
                            _this.inEdition = true;
                            _this.loadFeatureForm2(layer, clonedLayer);
                        }
                    });

                } else {
                    alert('Otra capa en edición');
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
                            var id = dataset.fid;
                            _this.editableLayer.eachLayer(function(layer) {
                                if (layer.feature.id == id) {
                                    _this.deleteElement(layer);
                                }
                            });
                            $(this).dialog("close");
                        },
                        Cancel: function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        });
    },

    saveElement: function(element) {
        var _this = this;
        var data = {
            'operation': 'create',
            'layer': this.editableLayer.name,
            'feature': element.toGeoJSON()
        };

        var stringData = JSON.stringify(data);
        var objectData = JSON.parse(stringData);
    
        //CAMBIAR_ANTES_DE_SUBIR
        $.ajax({
            url: window.serviceURL + '/api/changerequest',
            type: 'POST',
            async: false,
            data: JSON.stringify(objectData),
            contentType: "application/json; charset=utf-8",
    
        }).done(function(resp) {
            if (resp.feature.properties.status == 'VALIDADO') {
                element.feature.id = _this.editableLayer.name.split(':')[1] + '.' + resp.feature.properties.id.toString();
                element.feature.properties.id = resp.feature.properties.id;
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
                element.feature.properties.status = 'PENDIENTE:CREACIÓN';
                element.feature.id = _this.editableLayer.name.split(':')[1] + '.' + _this.getRndInteger(9999, 99999);
                element.feature.properties.id = resp.feature.properties.id;
            }
            _this.map.closePopup();
            $('#toc-result-content').empty();
            _this.sidebar.open('toc-layers');

            _this.bindPopup(element);

        }).fail(function(resp) {
            $('#creation-errors').empty();
            for (var key in resp.responseJSON.errors) {
                $('#creation-errors').append('<p style="padding: 5px 20px; color: #ff0000;">' + key + ': ' + resp.responseJSON.errors[key][0] + '</p>');
            }         
        });
    },

    getRndInteger: function(min, max) {
        return Math.floor(Math.random() * (max - min) ) + min;
    },

    deleteElement: function(element) {
        var _this = this;
        var data = {
            'operation': 'delete',
            'layer': this.editableLayer.name,
            'feature': element.toGeoJSON()
        };
    
        $.ajax({
            url: window.serviceURL + '/api/changerequest',
            type: 'POST',
            async: false,
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
    
        }).done(function(resp) {
            if (config.user.isadmin) {
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
    },
    
    updateElement: function(element) {
        var _this = this;
        var data = {
            'operation': 'update',
            'layer': this.editableLayer.name,
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
            _this.map.closePopup();
            $('#toc-result-content').empty();
            _this.sidebar.open('toc-layers');
            _this.inEdition = false;
            
    
        }).fail(function(resp) {
            $('#modification-errors').empty();
            for (var key in resp.responseJSON.errors) {
                $('#modification-errors').append('<p style="padding: 5px 20px; color: #ff0000;">' + key + ': ' + resp.responseJSON.errors[key][0] + '</p>');
            } 
        });
    },
    
    loadInfo: function(layer) {
    
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
        
        this.sidebar.open('toc-result');
    },
    
    loadFeatureForm2: function(layer, clonedLayer) {
        var _this = this;

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
                html += _this.utils.getAttributeInput(this.editableLayer.fields, key, layer.feature.properties[key]);
            }      
        }
        html += '</form>';
    
        $('#toc-result-content').append(html);
    
        $('#save-edition').on('click', function(){
            var serielizedData = $("#feature-form").serializeArray();
            for (i in serielizedData) {
                layer.feature.properties[serielizedData[i].name] = serielizedData[i].value;
            }
            _this.updateElement(layer);
        });
    
        $('#cancel-edition').on('click', function(){
            layer.editing.disable();
            layer.remove();
            clonedLayer.addTo(_this.map);
            _this.map.closePopup();
            $('#toc-result-content').empty();
            _this.sidebar.open('toc-layers');
            _this.inEdition = false;
        });
        
        this.sidebar.open('toc-result');
    }
}
   
module.exports = Draw;