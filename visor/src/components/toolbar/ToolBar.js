var ExportPNG = require('./ExportPNG.js');
var ExportJPG = require('./ExportJPG.js');
var Print = require('./Print.js');
var View3D = require('./View3D.js');

function ToolBar(map, overlays) {
    this.map = map;
    this.toolbar = null;
    this.overlays = overlays;
    this.controls = [];
    this.initialize();
}
   
ToolBar.prototype = {
    initialize: function() {
        var print = new Print(this.map);
        var exportPNG = new ExportPNG(this.map);
        var exportJPG = new ExportJPG(this.map);
        var view3D = new View3D(this.map, this.overlays);

        this.controls.push(print.getControl());
        if (!window.isMobile) {
            this.controls.push(exportPNG.getControl());
            this.controls.push(exportJPG.getControl());
        }
        this.controls.push(view3D.getControl());
        this.toolbar = L.easyBar(this.controls, {position: 'topright'});
        this.toolbar.addTo(this.map);
    }
}
   
module.exports = ToolBar;