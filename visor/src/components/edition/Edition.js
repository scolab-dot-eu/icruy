function Edition(map) {
    this.map = map;
}
   
Edition.prototype = {  
    startEdition: function(layer) {
        var options = {
            position: 'topleft',
            draw: false,
            edit: {}
        };
        var features = new L.FeatureGroup();
        layer.eachLayer(
            function(l){
                features.addLayer(l);
            }
        );
        options.edit.featureGroup = features;
        this.drawControl = new L.Control.Draw(options);
        this.map.addControl(this.drawControl);
    },

    stopEdition: function(layer){
        this.map.removeControl(this.drawControl);
        this.drawControl = null;
    },

    isActive: function(){
        var active = false;
        if (this.drawControl != null) {
            active = true;
        }
        return active;
    }
}
   
module.exports = Edition;