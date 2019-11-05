L.Control.StyledLayerControl = L.Control.Layers.extend({
    options: {
        collapsed: false,
        position: 'topright',
        autoZIndex: true,
        group_togglers: {
            show: false,
            labelAll: 'All',
            labelNone: 'None'
        },
        groupDeleteLabel: 'Delete the group'
    },

    initialize: function(controls, baseLayers, groupedOverlays, options) {
        var i, j;
        L.Util.setOptions(this, options);

        this._layerControlInputs = [];
        this._layers = [];
        this._lastZIndex = 0;
        this._handlingClick = false;
        this._groupList = [];
        this._domGroups = [];
        this._draw = controls.draw;
        this._time = controls.time;
        this._attributeTable = controls.table;

        for (i in baseLayers) {
            for (var j in baseLayers[i].layers) {
                this._addLayer(baseLayers[i].layers[j], j, baseLayers[i], false);
            }
        }

        for (i in groupedOverlays) {
            for (var j in groupedOverlays[i].layers) {
                this._addLayer(groupedOverlays[i].layers[j], j, groupedOverlays[i], true);
            }
        }


    },

    onAdd: function(map) {
        this._initLayout();
        this._update();

        map
            .on('layeradd', this._onLayerChange, this)
            .on('layerremove', this._onLayerChange, this)
            .on('zoomend', this._onZoomEnd, this);

        return this._container;
    },

    onRemove: function(map) {
        map
            .off('layeradd', this._onLayerChange)
            .off('layerremove', this._onLayerChange);
    },

    addBaseLayer: function(layer, name, group) {
        this._addLayer(layer, name, group, false);
        this._update();
        return this;
    },

    addOverlay: function(layer, name, group) {
        this._addLayer(layer, name, group, true);
        this._update();
        return this;
    },

    removeLayer: function(layer) {
        var id = L.Util.stamp(layer);
        delete this._layers[id];
        this._update();
        return this;
    },

    removeGroup: function(group_Name, del) {
        for (group in this._groupList) {
            if (this._groupList[group].groupName == group_Name) {
                for (layer in this._layers) {
                    if (this._layers[layer].group && this._layers[layer].group.name == group_Name) {
                        if (del) {
                            this._map.removeLayer(this._layers[layer].layer);
                        }
                        delete this._layers[layer];
                    }
                }
                delete this._groupList[group];
                this._update();
                break;
            }
        }
    },

    removeAllGroups: function(del) {
        for (group in this._groupList) {
                for (layer in this._layers) {
                    if (this._layers[layer].group && this._layers[layer].group.removable) {
                        if (del) {
                            this._map.removeLayer(this._layers[layer].layer);
                        }
                        delete this._layers[layer];
                    }
                }
                delete this._groupList[group];
        }
        this._update();
    },

    selectLayer: function(layer) {
        this._map.addLayer(layer);
        //this._map.getPane(this._layers[layerId].layer.name).style.display = '';
        this._update();
    },

    unSelectLayer: function(layer) {
        this._map.removeLayer(layer);
        //this._map.getPane(this._layers[layerId].layer.name).style.display = 'none';
        this._update();
    },

    selectGroup: function(group_Name) {
        this.changeGroup(group_Name, true)
    },

    unSelectGroup: function(group_Name) {
        this.changeGroup(group_Name, false)
    },

    changeGroup: function(group_Name, select) {
        for (group in this._groupList) {
            if (this._groupList[group].groupName == group_Name) {
                for (layer in this._layers) {
                    if (this._layers[layer].group && this._layers[layer].group.name == group_Name) {
                        if (select) {
                            this._map.addLayer(this._layers[layer].layer);
                        } else {
                            this._map.removeLayer(this._layers[layer].layer);
                        }
                    }
                }
                break;
            }
        }
        this._update();
    },


    _initLayout: function() {
        var className = 'leaflet-control-layers',
            container = this._container = L.DomUtil.create('div', className);

        //Makes this work on IE10 Touch devices by stopping it from firing a mouseout event when the touch is released
        container.setAttribute('aria-haspopup', true);

        if (!L.Browser.touch) {
            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.on(container, 'wheel', L.DomEvent.stopPropagation);
        } else {
            L.DomEvent.on(container, 'click', L.DomEvent.stopPropagation);
        }

        var section = document.createElement('section');
        section.className = 'ac-container ' + className + '-list styled_lc_section';

        var form = this._form = L.DomUtil.create('form');

        section.appendChild(form);

        if (this.options.collapsed) {
            if (!L.Browser.android) {
                L.DomEvent
                    .on(container, 'mouseover', this._expand, this)
                    .on(container, 'mouseout', this._collapse, this);
            }
            var link = this._layersLink = L.DomUtil.create('a', className + '-toggle', container);
            link.href = '#';
            link.title = 'Layers';

            if (L.Browser.touch) {
                L.DomEvent
                    .on(link, 'click', L.DomEvent.stop)
                    .on(link, 'click', this._expand, this);
            } else {
                L.DomEvent.on(link, 'focus', this._expand, this);
            }

            this._map.on('click', this._collapse, this);
            // TODO keyboard accessibility

        } else {
            this._expand();
        }

        this._baseLayersList = L.DomUtil.create('div', className + '-base', form);
        this._overlaysList = L.DomUtil.create('div', className + '-overlays', form);

        container.appendChild(section);

        // process options of ac-container css class - to options.container_width and options.container_maxHeight
        for (var c = 0; c < (containers = container.getElementsByClassName('ac-container')).length; c++) {
            if (this.options.container_width) {
                containers[c].style.width = this.options.container_width;
            }

            // set the max-height of control to y value of map object
            //this._default_maxHeight = this.options.container_maxHeight ? this.options.container_maxHeight : (this._map.getSize().y - 70);
            //containers[c].style.maxHeight = this._default_maxHeight + "px";

        }

        window.onresize = this._on_resize_window.bind(this);

    },

    _on_resize_window: function() {
        // listen to resize of screen to reajust de maxHeight of container
        for (var c = 0; c < containers.length; c++) {
            // input the new value to height
            //containers[c].style.maxHeight = (window.innerHeight - 90) < this._removePxToInt(this._default_maxHeight) ? (window.innerHeight - 90) + "px" : this._removePxToInt(this._default_maxHeight) + "px";
        }
    },

    // remove the px from a css value and convert to a int
    _removePxToInt: function(value) {
        if (typeof value === 'string') {
            return parseInt(value.replace("px", ""));
        }
        return value;
    },

    _addLayer: function(layer, name, group, overlay) {
        var id = L.Util.stamp(layer);

        this._layers[id] = {
            layer: layer,
            name: name,
            overlay: overlay
        };

        if (group) {
            var groupId = this._groupList.indexOf(group);

            // if not find the group search for the name
            if (groupId === -1) {
                for (g in this._groupList) {
                    if (this._groupList[g].groupName == group.groupName) {
                        groupId = g;
                        break;
                    }
                }
            }

            if (groupId === -1) {
                groupId = this._groupList.push(group) - 1;
            }

            this._layers[id].group = {
                name: group.groupName,
                id: groupId,
                expanded: group.expanded,
                removable: group.removable
            };
        }

        if (this.options.autoZIndex && layer.setZIndex) {
            this._lastZIndex++;
            layer.setZIndex(this._lastZIndex);
        }
    },

    _update: function() {
        if (!this._container) {
            return;
        }

        this._baseLayersList.innerHTML = '';
        this._overlaysList.innerHTML = '';

        this._domGroups.length = 0;

        this._layerControlInputs = [];

        var baseLayersPresent = false,
            overlaysPresent = false,
            i,
            obj;

        for (i in this._layers) {
            obj = this._layers[i];
            this._addItem(obj);
            overlaysPresent = overlaysPresent || obj.overlay;
            baseLayersPresent = baseLayersPresent || !obj.overlay;
        }

    },

    _onLayerChange: function(e) {
        var obj = this._layers[L.Util.stamp(e.layer)];

        if (!obj) {
            return;
        }

        if (!this._handlingClick) {
            this._update();
        }

        var type = obj.overlay ?
            (e.type === 'layeradd' ? 'overlayadd' : 'overlayremove') :
            (e.type === 'layeradd' ? 'baselayerchange' : null);

        this._checkIfDisabled();

        if (type) {
            this._map.fire(type, obj);
        }
    },

    _onZoomEnd: function(e) {
        this._checkIfDisabled();
    },

    _checkIfDisabled: function(layers) {
        var currentZoom = this._map.getZoom();

        for (layerId in this._layers) {
            var esCamino = false;
            if (this._layers[layerId].layer && this._layers[layerId].layer.name && this._layers[layerId].layer.name.indexOf('caminerias_intendencias') !== -1) {
                esCamino = true;
            }

            if (currentZoom < 11) {
                if (this._layers[layerId].layer.isGeojsonLayer) {
                    if (this._map.hasLayer(this._layers[layerId].layer)) {
                        this._map.getPane(this._layers[layerId].layer.name).style.display = 'none';
                    }
                }
                
                
            } else {
                if (this._layers[layerId].layer.isGeojsonLayer) {                      
                    if (this._map.hasLayer(this._layers[layerId].layer)) {
                        this._map.getPane(this._layers[layerId].layer.name).style.display = '';
                        if (!esCamino) {
                            try {
                                this._layers[layerId].layer.bringToFront();
                            } catch(error) {
                                console.log(error);
                            }                           
                        }
                    }
                }
                
            }

            /*if (this._layers[layerId].layer.options && (this._layers[layerId].layer.options.minZoom || this._layers[layerId].layer.options.maxZoom)) {
                var el = document.getElementById('ac_layer_input_' + this._layers[layerId].layer._leaflet_id);
                if (currentZoom < this._layers[layerId].layer.options.minZoom || currentZoom > this._layers[layerId].layer.options.maxZoom) {
                    if (!esCamino && this._layers[layerId].layer.isGeojsonLayer) {
                        //el.disabled = 'disabled';
                        //this.unSelectLayer(this._layers[layerId].layer);
                        if (this._map.hasLayer(this._layers[layerId].layer)) {
                            this._map.getPane(this._layers[layerId].layer.name).style.display = 'none';
                            console.log('No visible');
                        }
                    }
                    
                    
                } else {
                    if (!esCamino && this._layers[layerId].layer.isGeojsonLayer) {
                        //this.selectLayer(this._layers[layerId].layer);
                        //el.disabled = '';                       
                        if (this._map.hasLayer(this._layers[layerId].layer)) {
                            this._map.getPane(this._layers[layerId].layer.name).style.display = '';
                            console.log('Visible');
                        }
                    }
                    
                }
            }*/
        }
    },

    // IE7 bugs out if you create a radio dynamically, so you have to do it this hacky way (see http://bit.ly/PqYLBe)
    _createRadioElement: function(name, checked) {

        var radioHtml = '<input type="radio" class="leaflet-control-layers-selector" name="' + name + '"';
        if (checked) {
            radioHtml += ' checked="checked"';
        }
        radioHtml += '/>';

        var radioFragment = document.createElement('div');
        radioFragment.innerHTML = radioHtml;

        return radioFragment.firstChild;
    },

    _addItem: function(obj) {
        var label = document.createElement('div'),
            input,
            checked = this._map.hasLayer(obj.layer),
            id = 'ac_layer_input_' + obj.layer._leaflet_id,
            container;


        if (obj.overlay) {
            input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'leaflet-control-layers-selector';
            input.defaultChecked = checked;

            label.className = "menu-item-checkbox";
            input.id = id;

        } else {
            input = this._createRadioElement('leaflet-base-layers', checked);

            label.className = "menu-item-radio";
            input.id = id;
        }

        this._layerControlInputs.push(input);
        input.layerId = L.Util.stamp(obj.layer);

        L.DomEvent.on(input, 'click', this._onInputClick, this);

        var name = document.createElement('label');
        name.innerHTML = '<label for="' + id + '">' + obj.name + '</label>';

        label.appendChild(input);
        label.appendChild(name);

        if (obj.layer.StyledLayerControl) {
            if (!window.isMobile) {
                if (obj.layer.fields || obj.layer.type == "wfs") { // loading
                    var bt_loading = document.createElement("label");
                    bt_loading.appendChild(document.createTextNode('\u00A0')); // blank
                    bt_loading.className = "bt_loading";
                    bt_loading.title = "Cargando capa";
                    bt_loading.dataset.layerLID = obj.layer._leaflet_id;
                    bt_loading.id = 'bt_loading_' +  obj.layer.name.replace(":", "_");
                    var bt_loading_icon = document.createElement("i");
                    bt_loading_icon.className = "fa fa-spinner fa-spin";
                    bt_loading.appendChild(bt_loading_icon);
                    label.appendChild(bt_loading);
                }
                if (obj.layer.StyledLayerControl.historyLayerName) {
                    var bt_start_time = document.createElement("button");
                    bt_start_time.type = "button";
                    bt_start_time.className = "bt_start_time";
                    bt_start_time.title = "Iniciar consulta histórico";
                    bt_start_time.dataset.layerLID = obj.layer._leaflet_id;
                    bt_start_time.id = 'bt_start_time_' + obj.layer._leaflet_id;
                    var bt_start_time_icon = document.createElement("i");
                    bt_start_time_icon.className = "fa fa-clock";
                    bt_start_time.appendChild(bt_start_time_icon);
                    L.DomEvent.on(bt_start_time, 'click', this._onStartTimeClick, this);
                    label.appendChild(bt_start_time);

                    var bt_stop_time = document.createElement("button");
                    bt_stop_time.type = "button";
                    bt_stop_time.className = "bt_stop_time hidden";
                    bt_stop_time.title = "Terminar consulta histórico";
                    bt_stop_time.dataset.layerLID = obj.layer._leaflet_id;
                    bt_stop_time.id = 'bt_stop_time_' + obj.layer._leaflet_id;
                    var bt_stop_time_icon = document.createElement("i");
                    bt_stop_time_icon.className = "fa fa-times";
                    bt_stop_time.appendChild(bt_stop_time_icon);
                    L.DomEvent.on(bt_stop_time, 'click', this._onStopTimeClick, this);
                    label.appendChild(bt_stop_time);
                }

                // configure the delete button for layers with attribute isEditable = true
                if (window.editionMode) {
                    if (obj.layer.StyledLayerControl.isEditable) {
                        var bt_start_draw = document.createElement("button");
                        bt_start_draw.type = "button";
                        bt_start_draw.className = "bt_start_draw";
                        bt_start_draw.title = "Editar capa";
                        bt_start_draw.dataset.layerLID = obj.layer._leaflet_id;
                        bt_start_draw.id = 'bt_start_draw_' + obj.layer._leaflet_id;
                        var bt_start_draw_icon = document.createElement("i");
                        bt_start_draw_icon.className = "fa fa-edit";
                        bt_start_draw.appendChild(bt_start_draw_icon);
                        L.DomEvent.on(bt_start_draw, 'click', this._onStartDrawClick, this);
                        label.appendChild(bt_start_draw);
        
                        var bt_stop_draw = document.createElement("button");
                        bt_stop_draw.type = "button";
                        bt_stop_draw.className = "bt_stop_draw hidden";
                        bt_stop_draw.title = "Terminar edición";
                        bt_stop_draw.dataset.layerLID = obj.layer._leaflet_id;
                        bt_stop_draw.id = 'bt_stop_draw_' + obj.layer._leaflet_id;
                        var bt_stop_draw_icon = document.createElement("i");
                        bt_stop_draw_icon.className = "fa fa-times";
                        bt_stop_draw.appendChild(bt_stop_draw_icon);
                        L.DomEvent.on(bt_stop_draw, 'click', this._onStopDrawClick, this);
                        label.appendChild(bt_stop_draw);
                    }
                }

                if (obj.layer.StyledLayerControl.hasMetadata) {
                    var bt_metadata = document.createElement("a");
                    bt_metadata.href = obj.layer.StyledLayerControl.metadata;
                    bt_metadata.target = "_blank";
                    bt_metadata.className = "bt_metadata";
                    bt_metadata.title = "Abrir metadato";
                    bt_metadata.dataset.layerLID = obj.layer._leaflet_id;
                    bt_metadata.id = 'bt_metadata_' + obj.layer._leaflet_id;
                    var bt_metadata_icon = document.createElement("i");
                    bt_metadata_icon.className = "fa fa-external-link-alt";
                    bt_metadata.appendChild(bt_metadata_icon);
                    label.appendChild(bt_metadata);
                }

                

                if (obj.layer.definedUrl && obj.layer.StyledLayerControl.download) {
                    var bt_download = document.createElement("a");
                    bt_download.href = obj.layer.definedUrl + '?service=WFS&request=GetFeature&version=1.0.0&outputFormat=shape-zip&typeName=' + obj.layer.name;
                    bt_download.className = "bt_download";
                    bt_download.title = "Descargar capa en formato SHP";
                    bt_download.dataset.layerLID = obj.layer._leaflet_id;
                    bt_download.id = 'bt_download_' + obj.layer._leaflet_id;
                    var bt_download_icon = document.createElement("i");
                    bt_download_icon.className = "fa fa-download";
                    bt_download.appendChild(bt_download_icon);
                    label.appendChild(bt_download);
                }
                
                var bt_ogc_services = document.createElement("button");
                bt_ogc_services.type = "button";
                bt_ogc_services.className = "bt_ogc_services";
                bt_ogc_services.title = "Link a geoservicios";
                bt_ogc_services.dataset.layerLID = obj.layer._leaflet_id;
                bt_ogc_services.id = 'bt_show_table_' + obj.layer._leaflet_id;
                var bt_ogc_services_icon = document.createElement("i");
                bt_ogc_services_icon.className = "fa fa-link";
                bt_ogc_services.appendChild(bt_ogc_services_icon);
                L.DomEvent.on(bt_ogc_services, 'click', this._onShowOGCServices, this);
                label.appendChild(bt_ogc_services);
            }

            if (obj.layer.isGeojsonLayer && obj.layer.StyledLayerControl.showTable) {
                var bt_show_table = document.createElement("button");
                bt_show_table.type = "button";
                bt_show_table.className = "bt_show_table";
                bt_show_table.title = "Tabla de atributos";
                bt_show_table.dataset.layerLID = obj.layer._leaflet_id;
                bt_show_table.id = 'bt_show_table_' + obj.layer._leaflet_id;
                var bt_show_table_icon = document.createElement("i");
                bt_show_table_icon.className = "fa fa-table";
                bt_show_table.appendChild(bt_show_table_icon);
                L.DomEvent.on(bt_show_table, 'click', this._onShowTableClick, this);
                label.appendChild(bt_show_table);
            }

            // configure the visible attribute to layer
            if (obj.layer.StyledLayerControl.visible) {
                this._map.addLayer(obj.layer);
            }

        }


        if (obj.overlay) {
            container = this._overlaysList;
        } else {
            container = this._baseLayersList;
        }

        var groupContainer = this._domGroups[obj.group.id];

        if (!groupContainer) {

            groupContainer = document.createElement('div');
            groupContainer.id = 'leaflet-control-accordion-layers-' + obj.group.id;

            // verify if group is expanded
            var s_expanded = obj.group.expanded ? ' checked = "true" ' : '';

            // verify if type is exclusive
            var s_type_exclusive = this.options.exclusive ? ' type="radio" ' : ' type="checkbox" ';

            inputElement = '<input id="ac' + obj.group.id + '" name="accordion-1" class="menu" ' + s_expanded + s_type_exclusive + '/>';
            inputLabel = '<label for="ac' + obj.group.id + '">' + obj.group.name + '</label>';

            article = document.createElement('article');
            article.className = 'ac-large';
            article.appendChild(label);

            // process options of ac-large css class - to options.group_maxHeight property
            if (this.options.group_maxHeight) {
                article.style.maxHeight = this.options.group_maxHeight;
            }

            groupContainer.innerHTML = inputElement + inputLabel;
            groupContainer.appendChild(article);

            // Link to toggle all layers
            if (obj.overlay && this.options.group_togglers.show) {

                // Toggler container
                var togglerContainer = L.DomUtil.create('div', 'group-toggle-container', groupContainer);

                // Link All
                var linkAll = L.DomUtil.create('a', 'group-toggle-all', togglerContainer);
                linkAll.href = '#';
                linkAll.title = this.options.group_togglers.labelAll;
                linkAll.innerHTML = this.options.group_togglers.labelAll;
                linkAll.setAttribute("data-group-name", obj.group.name);

                if (L.Browser.touch) {
                    L.DomEvent
                        .on(linkAll, 'click', L.DomEvent.stop)
                        .on(linkAll, 'click', this._onSelectGroup, this);
                } else {
                    L.DomEvent
                        .on(linkAll, 'click', L.DomEvent.stop)
                        .on(linkAll, 'focus', this._onSelectGroup, this);
                }

                // Separator
                var separator = L.DomUtil.create('span', 'group-toggle-divider', togglerContainer);
                separator.innerHTML = ' / ';

                // Link none
                var linkNone = L.DomUtil.create('a', 'group-toggle-none', togglerContainer);
                linkNone.href = '#';
                linkNone.title = this.options.group_togglers.labelNone;
                linkNone.innerHTML = this.options.group_togglers.labelNone;
                linkNone.setAttribute("data-group-name", obj.group.name);

                if (L.Browser.touch) {
                    L.DomEvent
                        .on(linkNone, 'click', L.DomEvent.stop)
                        .on(linkNone, 'click', this._onUnSelectGroup, this);
                } else {
                    L.DomEvent
                        .on(linkNone, 'click', L.DomEvent.stop)
                        .on(linkNone, 'focus', this._onUnSelectGroup, this);
                }

                if (obj.overlay && this.options.group_togglers.show && obj.group.removable) {
                    // Separator
                    var separator = L.DomUtil.create('span', 'group-toggle-divider', togglerContainer);
                    separator.innerHTML = ' / ';
                }

                if (obj.group.removable) {
                    // Link delete group
                    var linkRemove = L.DomUtil.create('a', 'group-toggle-none', togglerContainer);
                    linkRemove.href = '#';
                    linkRemove.title = this.options.groupDeleteLabel;
                    linkRemove.innerHTML = this.options.groupDeleteLabel;
                    linkRemove.setAttribute("data-group-name", obj.group.name);

                    if (L.Browser.touch) {
                        L.DomEvent
                            .on(linkRemove, 'click', L.DomEvent.stop)
                            .on(linkRemove, 'click', this._onRemoveGroup, this);
                    } else {
                        L.DomEvent
                            .on(linkRemove, 'click', L.DomEvent.stop)
                            .on(linkRemove, 'focus', this._onRemoveGroup, this);
                    }
                }

            }

            container.appendChild(groupContainer);

            this._domGroups[obj.group.id] = groupContainer;
        } else {
            groupContainer.getElementsByTagName('article')[0].appendChild(label);
        }


        return label;
    },

    _onShowOGCServices: function(obj) {
        var node = document.getElementById('ac_layer_input_' + obj.currentTarget.dataset.layerLID);

        n_obj = this._layers[node.layerId];

        var html = '';
        html += '<div id="show-ogc-services-dialog">';
        html += '</div>';
        $('body').append(html);

        var ui = '';
        ui +=     '<span style="margin-bottom: 10px; font-size:12px; color: #e0a800;">Servicios OGC</span>';
        ui +=     '<div class="form-row">';
        ui +=         '<a style="margin-left: 10px;" target="_blank" href="' + n_obj.layer.definedUrl + '">' + n_obj.layer.definedUrl + '</a>';
        ui +=     '</div>';
        if (n_obj.layer.StyledLayerControl.wmsUrl) {
            ui +=     '<div class="form-row">';
            ui +=         '<a style="margin-left: 10px;" target="_blank" href="' + n_obj.layer.StyledLayerControl.wmsUrl + '">' + n_obj.layer.StyledLayerControl.wmsUrl + '</a>';
            ui +=     '</div>';
        }
        $('#show-ogc-services-dialog').empty();
        $('#show-ogc-services-dialog').append(ui);

        $('#show-ogc-services-dialog').dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                'Cerrar': function() {
                    $( this ).dialog( "close" );
                }
            }
        });

        return false;
    },

    _onStartDrawClick: function(obj) {
        var node = document.getElementById('ac_layer_input_' + obj.currentTarget.dataset.layerLID);
        var startDrawBtn = document.getElementById('bt_start_draw_' + obj.currentTarget.dataset.layerLID);
        var stopDrawBtn = document.getElementById('bt_stop_draw_' + obj.currentTarget.dataset.layerLID);

        n_obj = this._layers[node.layerId];

        if (node.checked) {
            if (!this._draw.isActive()) {
                this._draw.startDraw(n_obj.layer);
                startDrawBtn.className = 'bt_start_draw hidden';
                stopDrawBtn.className = 'bt_stop_draw';
            } else {
                alert('Existe otra capa en modo edición');
            }
            
        } else {
            alert('La capa debe estar visible');
        }

        return false;
    },

    _onStopDrawClick: function(obj) {
        var node = document.getElementById('ac_layer_input_' + obj.currentTarget.dataset.layerLID);
        var startDrawBtn = document.getElementById('bt_start_draw_' + obj.currentTarget.dataset.layerLID);
        var stopDrawBtn = document.getElementById('bt_stop_draw_' + obj.currentTarget.dataset.layerLID);

        var n_obj = this._layers[node.layerId];

        this._draw.stopDraw(n_obj.layer);
        startDrawBtn.className = 'bt_start_draw';
        stopDrawBtn.className = 'bt_stop_draw hidden';

        return false;
    },

    _onStartTimeClick: function(obj) {
        var node = document.getElementById('ac_layer_input_' + obj.currentTarget.dataset.layerLID);
        var startTimeBtn = document.getElementById('bt_start_time_' + obj.currentTarget.dataset.layerLID);
        var stopTimeBtn = document.getElementById('bt_stop_time_' + obj.currentTarget.dataset.layerLID);

        n_obj = this._layers[node.layerId];

        if (!this._time.isActive()) {
            this._time.startTime(n_obj.layer);
            startTimeBtn.className = 'bt_start_time hidden';
            stopTimeBtn.className = 'bt_stop_time';
        } else {
            alert('Se está consultando el histórico de otra capa. Debe finalizar antes.');
        }

        return false;
    },

    _onStopTimeClick: function(obj) {
        var node = document.getElementById('ac_layer_input_' + obj.currentTarget.dataset.layerLID);
        var startTimeBtn = document.getElementById('bt_start_time_' + obj.currentTarget.dataset.layerLID);
        var stopTimeBtn = document.getElementById('bt_stop_time_' + obj.currentTarget.dataset.layerLID);

        var n_obj = this._layers[node.layerId];

        this._time.stopTime();
        startTimeBtn.className = 'bt_start_time';
        stopTimeBtn.className = 'bt_stop_time hidden';

        return false;
    },

    _onShowTableClick: function(obj) {
        var node = document.getElementById('ac_layer_input_' + obj.currentTarget.dataset.layerLID);
        var n_obj = this._layers[node.layerId];
        this._attributeTable.createTable(n_obj.layer, node.layerId);
        
        return false;
    },

    _onDownloadClick: function(obj) {
        return false;
    },

    _onSelectGroup: function(e) {
        this.selectGroup(e.target.getAttribute("data-group-name"));
    },

    _onUnSelectGroup: function(e) {
        this.unSelectGroup(e.target.getAttribute("data-group-name"));
    },

    _onRemoveGroup: function(e) {
        this.removeGroup(e.target.getAttribute("data-group-name"), true);
    },

    _expand: function() {
        L.DomUtil.addClass(this._container, 'leaflet-control-layers-expanded');
    },

    _collapse: function() {
        this._container.className = this._container.className.replace(' leaflet-control-layers-expanded', '');
    }
});

L.Control.styledLayerControl = function(baseLayers, overlays, options) {
    return new L.Control.StyledLayerControl(baseLayers, overlays, options);
};
