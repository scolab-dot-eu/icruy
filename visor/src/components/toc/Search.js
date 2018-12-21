function Search(map, groupedOverlays) {
    this.map = map;
    this._layers = [];
    this.searchControl = null;
    this.initialize(groupedOverlays);
    this.render();
}
   
Search.prototype = {  

    initialize: function(groupedOverlays) {
        for (i in groupedOverlays) {
            for (var j in groupedOverlays[i].layers) {
                if (groupedOverlays[i].layers[j].showInSearch) {
                    this._addLayer(groupedOverlays[i].layers[j], j, true);
                }               
            }
        }
    },

    _addLayer: function(layer, name, overlay) {
        var id = L.Util.stamp(layer);

        this._layers[id] = {
            layer: layer,
            name: name,
            overlay: overlay
        };
    },

    render: function(name) {
        var html = '';
        html += '<form>';
        html +=     '<div class="form-group" style="padding: 10px;">';
        html +=         '<select class="form-control m-t-10" id="select-layer">';
        html +=             '<option selected disabled value="empty">Seleccionar capa ...</option>';
        for (key in this._layers) {
            html +=         '<option value="' + this._layers[key].name + '">' + this._layers[key].name + '</option>';
        }
        html +=         '</select>';
        html +=         '<select class="form-control m-t-10" id="select-field">';
        html +=             '<option selected disabled value="empty">Seleccionar campo ...</option>';
        html +=         '</select>';
        html +=         '<div id="search-div"></div>'
        html +=     '</div>'
        html += '</form>';

        $('#toc-search').append(html);

        var _this = this;
        $( "#select-layer" ).change(function() {
            $('#select-field')
                .empty()
                .append('<option selected disabled value="empty">Seleccionar campo ...</option>');

            var properties = null;
            for (key in _this._layers) {
                if (_this._layers[key].name == this.value) {
                    var selectedLayer = _this._layers[key].layer;
                    selectedLayer.eachLayer(function (layer) {
                        properties = layer.feature.properties;                        
                    });
                }
            }
            $.each(properties, function (prop, i) {
                $('#select-field').append($('<option>', { 
                    value: prop,
                    text : prop
                }));
            });
        });
        $( "#select-field" ).change(function() {
            var layer_id = $( "select#select-layer option:checked" ).val();
            _this.addControl(layer_id, this.value);
        });
    },

    addControl: function(layer_id, field) {
        if (this.searchControl != null) {
            this.removeControl();
        }
        var searchLayer = null;
        for (key in this._layers) {
            if (this._layers[key].name == layer_id) {
                searchLayer = this._layers[key].layer;
            }
        }
        this.searchControl = new L.Control.Search({
            container: 'search-div',
            collapsed: false,
            layer: searchLayer,
            propertyName: field,
            marker: false,
            zoom: 15
        }).addTo(this.map);
    },

    removeControl: function() {
        this.map.removeControl(this.searchControl);
        this.searchControl = null;
    }
}
   
module.exports = Search;