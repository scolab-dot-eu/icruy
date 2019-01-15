function View3D(map) {
    this.map = map;
    this.ww = null;
    this.control = null;
    this.initialize(); 
}
   
View3D.prototype = {
    initialize: function() {
        var _this = this;
        var m = this.map;

        $('#logo-3d').append('<img src="' + window.serviceURL + '/visor/assets/images/logo_opp2.png">');

        this.control = L.easyButton('fa-globe', function(btn, m){
            $('#map').addClass('hidden');
            $('#canvas-wrap').removeClass('hidden');
            _this.ww = new WorldWind.WorldWindow("canvasOne");
            //_this.ww.addLayer(new WorldWind.BMNGOneImageLayer());
            //_this.ww.addLayer(new WorldWind.BMNGLandsatLayer());
            _this.ww.addLayer(new WorldWind.BingAerialLayer("AuhiCJHlGzhg93IqUH_oCpl_-ZUrIE6SPftlyGYUvr9Amx5nzA-WqGcPquyFZl4L"));
            _this.ww.addLayer(new WorldWind.StarFieldLayer());
            _this.ww.addLayer(new WorldWind.AtmosphereLayer());
            _this.ww.addLayer(new WorldWind.CoordinatesDisplayLayer(_this.ww));

            _this.viewControls = new WorldWind.ViewControlsLayer(_this.ww);
            _this.viewControls.placement = new WorldWind.Offset(WorldWind.OFFSET_FRACTION, 0.99, WorldWind.OFFSET_FRACTION, 0.98);
            _this.viewControls.alignment = new WorldWind.Offset(WorldWind.OFFSET_FRACTION, 1, WorldWind.OFFSET_FRACTION, 1);
	        _this.ww.addLayer(_this.viewControls);

            _this.ww.navigator.lookAtLocation.latitude = -32;
            _this.ww.navigator.lookAtLocation.longitude = -55;
            _this.ww.navigator.range = 20e6; // 2 million meters above the ellipsoid

            // Redraw the WorldWindow.
            _this.ww.redraw();

            _this.loadLayers(["camineria:cr_alcantarillas","camineria:cr_baden","camineria:cr_obstaculo","camineria:cr_paso","camineria:cr_senyal","camineria:cr_puente"]);
        }, 'Vista 3D');

        $('.goto-2d').on('click', function(){
            $('#map').removeClass('hidden');
            $('#canvas-wrap').addClass('hidden');
            _this.viewControls = null;
            _this.ww = null;
            _this.control = null;
        });
    },

    getControl: function(){
        return this.control;
    },

    getWorldWind: function(){
        return this.ww;
    },

    loadLayers: function(layers) {
        var _this = this;

        // Web Map Service information from NASA's Near Earth Observations WMS
        var serviceAddress = "http://geoportal.opp.com/geoserver/wms?SERVICE=WMS&REQUEST=GetCapabilities&VERSION=1.3.0";

        $.get(serviceAddress)
            .done(function (xmlDom) {
                for (var i=0; i<layers.length; i++) {
                    // Create a WmsCapabilities object from the XML DOM
                    var wms = new WorldWind.WmsCapabilities(xmlDom);
                    // Retrieve a WmsLayerCapabilities object by the desired layer name
                    var wmsLayerCapabilities = wms.getNamedLayer(layers[i]);
                    // Form a configuration object from the WmsLayerCapability object
                    var wmsConfig = WorldWind.WmsLayer.formLayerConfiguration(wmsLayerCapabilities);
                    // Modify the configuration objects title property to a more user friendly title
                    wmsConfig.title = "Average Surface Temp";
                    // Create the WMS Layer from the configuration object
                    var wmsLayer = new WorldWind.WmsLayer(wmsConfig);

                    // Add the layers to WorldWind and update the layer manager
                    _this.ww.addLayer(wmsLayer);
                }
                
            }).fail(function (jqXhr, text, exception) {
                console.log("There was a failure retrieving the capabilities document: " + text + " exception: " + exception);
            });
    }
}
   
module.exports = View3D;