function ExportPNG(map) {
    this.map = map;
    this.control = null;
    this.initialize(); 
}
   
ExportPNG.prototype = {
    initialize: function() {
        var _this = this;
        var m = this.map;
        this.control = L.easyButton('fa-image', function(btn, m){
            leafletImage(_this.map, function(err, canvas){
                var img = document.createElement('img');
                var dimensions = _this.map.getSize();
                img.width = dimensions.x;
                img.height = dimensions.y;
                img.src = canvas.toDataURL();
                var a = document.createElement('a');
                a.href = img.src;
                a.download = 'map.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        }, 'Exportar a PNG');
    },

    getControl: function(){
        return this.control;
    }
}
   
module.exports = ExportPNG;