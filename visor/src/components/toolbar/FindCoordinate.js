function FindCoordinate(map) {
    this.map = map;
    this.initialize(); 
}
   
FindCoordinate.prototype = {
    initialize: function() {
        var _this = this;
        var m = this.map;

        var html = '';
        html += '<div id="find-coordinate-dialog">';
        html +=     '<div class="form-row">';
        html +=         '<div class="form-group col-md-6">';
        html +=             '<label for="longitud_x">Longitud (x)</label>';
        html +=             '<input type="number" class="form-control" step="0.1" id="longitud_x" placeholder="Longitud (ej: -56,1872)">';
        html +=         '</div>';
        html +=         '<div class="form-group col-md-6">';
        html +=             '<label for="latitud_y">Latitud (y)</label>';
        html +=             '<input type="number" class="form-control" step="0.1" id="latitud_y" placeholder="Latitud (ej: -34,8902)">';
        html +=         '</div>';
        html +=     '</div>';
        html +=     '<button id="find-coord" type="button" class="btn btn-warning m-r-5">Encontrar</button>';
        html +=     '<button id="cancel-find-coord" type="button" class="btn btn-secondary m-r-5">Cerrar</button>';
        html += '</div>';
        
        $('body').append(html);
        $('#find-coordinate-dialog').dialog({
            autoOpen: false,
            width: 400
        });

        $('#find-coord').on('click', function(){
            var longitud = $('#longitud_x').val();
            var latitud = $('#latitud_y').val();

            var latlng = L.latLng(latitud, longitud);
            _this.map.flyTo(latlng, 15);

            $('#find-coordinate-dialog').dialog('close');
        });
    
        $('#cancel-find-coord').on('click', function(){
            $('#find-coordinate-dialog').dialog('close');
        });

        this.control = L.easyButton({
            states: [{
                stateName: 'find',
                icon:      'fa-crosshairs',
                title:     'Buscar coordenadas',
                onClick: function(btn, m) {
                    _this.openDialog();
                }
            }]
        });
        
        this.control.addTo(m);
    },

    openDialog: function() {
        $('#longitud_x').val();
        $('#latitud_y').val();
        $('#find-coordinate-dialog').dialog('open');
    },

    find: function(){
    },

    getControl: function(){
        return this.control;
    }
}
   
module.exports = FindCoordinate;