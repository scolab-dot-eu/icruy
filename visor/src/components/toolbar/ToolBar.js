var ExportPNG = require('./ExportPNG.js');
var ExportJPG = require('./ExportJPG.js');
var Print = require('./Print.js');
var PrintMF = require('./PrintMF.js');
var View3D = require('./View3D.js');
var FindCoordinate = require('./FindCoordinate.js');

function ToolBar(map, overlays, printUtils, toc) {
    this.map = map;
    this.toolbar = null;
    this.overlays = overlays;
    this.controls = [];
    this.printUtils = printUtils;
    this.toc = toc;
    this.initialize();
}
   
ToolBar.prototype = {
    initialize: function() {
        //var print = new Print(this.map, this.printUtils);
        var printMF = new PrintMF(this.map, this.toc, window.serviceURL);
        var exportPNG = new ExportPNG(this.map);
        var exportJPG = new ExportJPG(this.map);
        var view3D = new View3D(this.map, this.overlays);
        var findCoordinate = new FindCoordinate(this.map);

        //this.controls.push(print.getControl());
        this.controls.push(printMF.getControl());
        if (!window.isMobile) {
            this.controls.push(exportPNG.getControl());
            this.controls.push(exportJPG.getControl());
        }
        this.controls.push(view3D.getControl());
        this.controls.push(findCoordinate.getControl());

        this.toolbar = L.easyBar(this.controls, {position: 'topright'});
        this.toolbar.addTo(this.map);
    }
}
   
module.exports = ToolBar;