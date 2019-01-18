function TimeControl(map, sidebar) {
    this.map = map;
    this.sidebar = sidebar;
    this.timeControl = null;
    this.tdWmsLayer = null;
    this.initialize();   
}
   
TimeControl.prototype = {  
    initialize: function() {
        this.timeDimension = new L.TimeDimension({
            period: "PT1D"
        });
        this.map.timeDimension = this.timeDimension;

        this.player = new L.TimeDimension.Player({
            loop: true
        }, this.timeDimension);

        this.timeDimensionControlOptions = {
            player: this.player,
            timeDimension: this.timeDimension,
            position: 'topcenter',
            autoPlay: true,
            playButton: false,
            backwardButton: false,
            forwardButton: false,
            speedSlider: false,
            timeSliderDragUpdate: true
        };
    },
    startTime: function(layer) {
        this.timeControl = new L.Control.TimeDimension(this.timeDimensionControlOptions).addTo(this.map);
        if ($('#sidebar').hasClass('collapsed')) {
            $('.leaflet-bar-timecontrol').css('margin-left', '0px');
        } else {
            $('.leaflet-bar-timecontrol').css('margin-left', '320px');
        }

        var wmsLayer = L.tileLayer.wms(layer.StyledLayerControl.wmsUrl, {
            layers: layer.StyledLayerControl.historyLayerName.split(':')[1],
            format: 'image/png',
            transparent: true
        });

        // Create and add a TimeDimension Layer to the map
        this.tdWmsLayer = L.timeDimension.layer.wms(wmsLayer);
        this.tdWmsLayer.addTo(this.map);
    },

    stopTime: function(){
        this.map.removeLayer(this.tdWmsLayer);
        this.tdWmsLayer = null;
        this.map.removeControl(this.timeControl);
        this.timeControl = null;
    },

    isActive: function(){
        var active = false;
        if (this.timeControl != null) {
            active = true;
        }
        return active;
    }
}
   
module.exports = TimeControl;