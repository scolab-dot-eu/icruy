var ExportPNG = require('./ExportPNG.js');
var ExportJPG = require('./ExportJPG.js');
var Print = require('./Print.js');

function ToolBar(map) {
    this.map = map;
    this.toolbar = null;
    this.controls = [];
    this.initialize();
}
   
ToolBar.prototype = {
    initialize: function() {
        var print = new Print(this.map);
        var exportPNG = new ExportPNG(this.map);
        var exportJPG = new ExportJPG(this.map);

        this.controls.push(print.getControl());
        this.controls.push(exportPNG.getControl());
        this.controls.push(exportJPG.getControl());
        this.toolbar = L.easyBar(this.controls, {position: 'topright'});
        this.toolbar.addTo(this.map);
    }
}
   
module.exports = ToolBar;