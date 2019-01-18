require('datatables_css');
require('datatables_js');

function AttributeTable(map) {
    this.map = map;
    this.initialize();
}
   
AttributeTable.prototype = {  
    initialize: function(){
        var html = '';
        html += '<div id="att-table-dialog">';
        html += '</div>';
        
        $('body').append(html);
    },

    createTable: function(layer, layer_id) {
        var _this = this;

        var properties = null;
        var data = [];
        layer.eachLayer(function (l) {
            properties = l.feature.properties;
            var row = [];
            row.push(l.feature.id);
            for (key in properties) {
                row.push(properties[key]);
            }                  
            data.push(row);  
        });

        var html = '';
        html += '<table id="table-' + layer_id + '" class="display stripe nowrap cell-border hover" style="width:100%">';
        html +=     '<thead>';
        html +=         '<tr>';
        html +=         '<th>fid</th>';
        for (key in properties) {
            html +=     '<th>' + key + '</th>';
        }
        html +=         '</tr>';
        html +=     '</thead>';
        html +=     '<tbody>';
        for (i in data) {
            html +=         '<tr>';
            for (j in data[i]) {
                html +=     '<td>' + data[i][j] + '</td>';
            }
        html +=         '</tr>';
        }
        html +=     '</tbody>';
        html += '</table>';

        $('#att-table-dialog').empty();
        $('#att-table-dialog').append(html);
        var dt = $('#table-' + layer_id).DataTable({
            language: {
                processing		: "Procesando petición...",
                search			: "Buscar",
                lengthMenu		: "Mostrando _MENU_ registros",
                info			: "Mostrando desde _START_ a _END_ de _TOTAL_ registros",
                infoEmpty		: "Mostrando 0 a 0, de 0 registros",
                infoFiltered	: "(Filtrando _MAX_ registros)",
                infoPostFix		: "",
                loadingRecords	: "Cargando ...",
                zeroRecords		: "No hay registros disponibles",
                emptyTable		: "No hay registros disponibles",
                paginate: {
                    first		: "Primero",
                    previous	: "Anterior",
                    next		: "Siguiente",
                    last		: "Último"
                },
                aria: {
                    sortAscending:  ": Orden ascendente",
                    sortDescending: ": Orden descendente"
                }
            },
            "columnDefs": [
                {
                    "targets": [0],
                    "visible": false,
                    "searchable": false
                }
            ],
            sCharSet: 'utf-8',
            //scrollX: '100%',
            //scrollY: '50vh',
            //scrollCollapse: true,
            buttons: [
                'csv', 'print'
            ],
            dom: 'Bfrtp<"top"l><"bottom"i>',
            "bSort" : false,
	        "lengthMenu": [[5, 10], [5, 10]]
        });

        $('#table-' + layer_id + ' tbody').on( 'click', 'tr', function () {
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                dt.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                var fid = dt.row('.selected').data()[0];
                _this.zoomToFeature(layer, fid);
            }
        });

        $('#att-table-dialog').dialog({
            minWidth: '50%'
        });
        $( "#att-table-dialog" ).dialog( "open" );

    },

    zoomToFeature: function(layer, fid) {
        var _this = this;
        layer.eachLayer(function (l) {
            if (l.feature.id == fid) {
                if (l.getLatLng) {
                    _this.map.setView(l.getLatLng(), 17);
                }
                else {
                    _this.map.fitBounds(l.getBounds());
                }
            }
        });
    }
}
   
module.exports = AttributeTable;
