function PrintMF(map, toc, printUrl) {
    this.control = null;
    this.map = map;
    this.toc = toc;
    this.printUrl = printUrl + '/print';
    this.detailsTab = $('#toc-result-content');
	this.capabilities = null;
    this.initialize();

    this.MAX_RESOLUTION = 156543.03390625;
	this.MAX_EXTENT = [-20037508.34, -20037508.34, 20037508.34, 20037508.34];
	this.SRS = 'EPSG:3857';
	this.INCHES_PER_METER = 39.3701;
	this.DPI = 72;
	this.UNITS = 'm';
}
   
PrintMF.prototype = {
    initialize: function() {
        var self = this;
        var m = this.map;
        this.control = L.easyButton('fa-print', function(btn, m){
            self.createPrintForm();
        }, 'Imprimir');
    },

    createPrintForm: function() {
        var self = this;
        
        var mask = '<div id="animationload" class="animationload"><div class="osahanloading"></div></div>';
        $('body').append(mask);

		this.showDetailsTab();
		this.detailsTab.empty();
		
		this.capabilities = this.getCapabilities('a4_landscape');
		
		var ui = '';
		ui += '<div>';
		ui += 	'<h3>Parámetros de impresión</h3>';
		ui += '</div>';
		ui += '<div>';
		ui += 	'<div id="field-errors" class="row"></div>';
		ui += 	'<div class="col-md-12 form-group">';
		ui += 		'<label>Título</label>';
		ui += 		'<input id="print-title" type="text" class="form-control" value="Inventario de camineria rural">';
		ui += 	'</div>';
		ui += 	'<div class="col-md-12 form-group">';
		ui += 		'<label>Resolución</label>';
		ui += 		'<select id="print-dpi" class="form-control">';
		ui += 			'<option value="180">180 dpi</option>';
		ui += 			'<option selected value="240">240 dpi</option>';
		ui += 			'<option value="320">320 dpi</option>';
		ui += 			'<option value="400">400 dpi</option>';
		ui += 		'</select>';
		ui += 	'</div>';	
		ui += '</div>';
		ui += '<div>';
        ui +=       '<button id="accept-print" type="button" class="btn btn-warning m-r-5">Imprimir</button>';
        ui +=       '<button id="cancel-print" type="button" class="btn btn-secondary m-r-5">Cerrar</button>';
		ui += '</div>';
		
		this.detailsTab.append(ui);
		this.showDetailsTab();
		
		$('#accept-print').on('click', function () {
			self.createPrintJob();
			
		});
		
		$('#cancel-print').on('click', function () {
			self.showLayersTab();
			self.capabilities = null;
		});
    },

    createPrintJob: function() {
        var self = this;
        var title = $('#print-title').val();
        var dpi = $('#print-dpi').val();

        var mask = '<div id="animationload" class="animationload"><div class="osahanloading"></div></div>';
        $('body').append(mask);
        
        var printLayers = new Array();
        var legends = new Array();
        this.map.eachLayer(function(layer) {
            if (!layer.isBaseLayer) {
                if( layer instanceof L.TileLayer ) {
                    if (layer.wmsParams) {
                        var url = layer._url;
                        printLayers.push({
                            "baseURL": url,
                            "layers": [layer.wmsParams.layers],
                            "opacity": 1,
                            "type": "WMS",
                            "imageFormat": "image/png",
                            "customParams": {
                              "TRANSPARENT": "true"
                            },
                            "mergeableParams": {},
                          }
                        );

                        legends.push({
                            "name": layer.wmsParams.layers,
                            "icons": [ url + '?SERVICE=WMS&VERSION=1.1.1&layer=' + layer.wmsParams.layers + '&REQUEST=getlegendgraphic&FORMAT=image/png']
                        });
                    }
                } else if ( layer instanceof L.GeoJSON ) {
                    if (layer.isGeojsonLayer) {
                        var url = layer.StyledLayerControl.wmsUrl;
                        printLayers.push({
                            "baseURL": url,
                            "layers": [layer.name],
                            "opacity": 1,
                            "type": "WMS",
                            "imageFormat": "image/png",
                            "customParams": {
                              "TRANSPARENT": "true"
                            },
                            "mergeableParams": {},
                          }
                        );

                        legends.push({
                            "name": layer.name,
                            "icons": [ url + '?SERVICE=WMS&VERSION=1.1.1&layer=' + layer.name + '&REQUEST=getlegendgraphic&FORMAT=image/png']
                        });
                    }
                }
            }
        });

        printLayers.push({
            "baseURL": "http://a.tile.openstreetmap.org",
            "type": "OSM",
            "imageExtension": "png"
        });

        var min_xy = window.proj4('+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +wktext  +no_defs',[this.map.getBounds().getSouthWest().lng, this.map.getBounds().getSouthWest().lat]);
        var max_xy = window.proj4('+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +wktext  +no_defs',[this.map.getBounds().getNorthEast().lng, this.map.getBounds().getNorthEast().lat]);
        var bbox = [min_xy[0], min_xy[1], max_xy[0], max_xy[1]];

        var center = window.proj4('+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +wktext  +no_defs',[this.map.getCenter().lng, this.map.getCenter().lat]);

        $.ajax({
            type: 'POST',
            async: true,
            url: self.printUrl + '/print/a4_landscape/report.pdf',
            processData: false,
            contentType: 'application/json',
            data: JSON.stringify({
                "layout": self.capabilities.layouts[0].name,
                "outputFormat": "pdf",
                "attributes": {
                    "title": title,
                    "map": {
                        "projection": "EPSG:3857",
                        "dpi": parseInt(dpi),
                        "center": center,
                        "rotation": 0,
                        "scale": self.getCurrentScale(),
                        "layers": printLayers
                    },
                    "logo_url": 'logo.png',
                    "logo2_url": 'logo2.png',
                    "legend": {
                        "name": "",
                        "classes": legends
                    },
                    "crs": "EPSG:3857",
                }
            }),
            success	:function(response){
                self.getReport(response);
            },
            error: function(){}
        });
        
    },

    getCapabilities: function() {
        var capabilities = null;
        $.ajax({
            type: 'GET',
            async: false,
              url: this.printUrl + '/print/a4_landscape/capabilities.json',
              success	:function(response){
                  capabilities = response;
                  $('#animationload').remove();
              },
              error: function(){}
        });
        return capabilities;
    },

    getCurrentScale: function () {
        var DOTS_PER_INCH = 72;
        var INCHES_PER_METER = 1.0 / 0.02540005080010160020;
        var INCHES_PER_KM = INCHES_PER_METER * 1000.0;
        var sw = this.map.getBounds().getSouthWest();
        var ne = this.map.getBounds().getNorthEast();
        var halflat = ( sw.lat + ne.lat ) / 2.0;
        var midLeft = new L.LatLng(halflat,sw.lng);
        var midRight = new L.LatLng(halflat,ne.lng);
        var mwidth = midLeft.distanceTo(midRight);
        var pxwidth = this.map.getSize().x;
        var kmperpx = mwidth / pxwidth / 1000.0;
        var scale = (kmperpx || 0.000001) * INCHES_PER_KM * DOTS_PER_INCH;
        scale *= 2.0; // no idea why but it's doubled
        scale = 250 * Math.round(scale / 250.0);

		return scale;
    },

    getReport: function(reportInfo) {
        var self = this;
        $.ajax({
            type: 'GET',
            async: true,
              url: window.serviceURL + reportInfo.statusURL,
              success	:function(response){
                  if (response.done) {
                    $('#animationload').remove();
                    window.open(reportInfo.downloadURL);
                  } else {
                      window.setTimeout(self.getReport(reportInfo), 3000);
                  }
              },
              error: function(){}
        });
    },

    showDetailsTab: function(p,f) {
        this.toc.getSideBar().open('toc-result');
    },

    showLayersTab: function(p,f) {
        this.detailsTab.empty();
        this.toc.getSideBar().open('toc-layers');
    },

    getControl: function(){
        return this.control;
    }
}
   
module.exports = PrintMF;