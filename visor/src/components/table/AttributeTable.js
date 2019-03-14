window.JSZip = require('js_zip');

require('datatables_css');
require('datatables_js');
require('datatables_buttons_css');
require('datatables_buttons_js');
require('datatables_buttons_html5_js');
require('fixed_header_js');
require('responsive_js');
require('fixed_header_css');
require('responsive_css');

function AttributeTable(map) {
    this.map = map;
    this.departamento = null;
    this.initialize();
}
   
AttributeTable.prototype = {  
    initialize: function(){
        var html = '';
        html += '<div id="att-table-dialog">';
        html += '</div>';
        
        $('body').append(html);

        var searchParams = new URLSearchParams(window.location.search);
        if (searchParams.has('departamento')) {
            this.departamento = searchParams.get('departamento');
        }
    },

    sortObject: function(obj) {
        return Object.keys(obj).sort().reduce(function (result, key) {
            result[key] = obj[key];
            return result;
        }, {});
    },

    createTable: function(layer, layer_id) {
        var _this = this;

        var properties = null;
        var data = [];
        layer.eachLayer(function (l) {
            if (l.feature) {
                properties = _this.sortObject(l.feature.properties);
                if (_this.departamento != null) {
                    if (properties['departamento'] == _this.departamento) {
                        var row = [];
                        row.push(l.feature.id);
                        for (key in properties) {
                            if (key != 'id' && key != 'created_at' && key != 'updated_at' && key != 'version' && key != 'origin') {
                                row.push(properties[key]);
                            }                      
                        }                  
                        data.push(row);
                    }

                } else {
                    var row = [];
                    row.push(l.feature.id);
                    for (key in properties) {
                        if (key != 'id' && key != 'created_at' && key != 'updated_at' && key != 'version' && key != 'origin') {
                            row.push(properties[key]);
                        } 
                    }                  
                    data.push(row);
                }
            } 
        });

        var html = '';
        html += '<table id="table-' + layer_id + '" class="display stripe nowrap cell-border hover" style="width:100%">';
        html +=     '<thead>';
        html +=         '<tr>';
        html +=         '<th>fid</th>';
        for (key in properties) {
            if (key != 'id' && key != 'created_at' && key != 'updated_at' && key != 'version' && key != 'origin') {
                html +=     '<th>' + key + '</th>';
            }
        }
        html +=         '</tr>';
        html +=     '</thead>';
        html +=     '<tbody>';
        for (i in data) {
            html +=         '<tr>';
            for (j in data[i]) {
                var d = data[i][j];
                if (d == null || d == 'null') {
                    d = '';
                }
                html +=     '<td>' + d + '</td>';
            }
        html +=         '</tr>';
        }
        html +=     '</tbody>';
        html += '</table>';

        $('#att-table-dialog').empty();
        $('#att-table-dialog').append(html);
        var responsive = false;
        if (window.isMobile) {
            responsive = true;
        }
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
            responsive: responsive,
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
                {
                    text: 'CSV',
                    action: function ( e, dt, node, config ) {
                        window.open(layer.definedUrl + '?service=WFS&request=GetFeature&version=1.0.0&outputFormat=csv&typeName=' + layer.name, '_blank');
                    }
                }, {
                    text: 'Excel',
                    action: function ( e, dt, node, config ) {
                        window.open(layer.definedUrl + '?service=WFS&request=GetFeature&version=1.0.0&outputFormat=excel2007&typeName=' + layer.name, '_blank');
                    }
                }
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

        if (window.isMobile) {
            new $.fn.dataTable.FixedHeader( dt );
            $('#att-table-dialog').dialog({});
        } else {
            $('#att-table-dialog').dialog({width: 800});
        }

        
        $( "#att-table-dialog" ).dialog( "open" );

    },

    zoomToFeature: function(layer, fid) {
        var _this = this;
        layer.eachLayer(function (l) {
            if (l.feature.id == fid) {
                if (l.getLatLng) {
                    _this.map.setView(l.getLatLng(), 17);
                    l.openPopup();
                }
                else {
                    _this.map.fitBounds(l.getBounds());
                    l.openPopup();
                }
            }
        });
    }
}
   
module.exports = AttributeTable;
