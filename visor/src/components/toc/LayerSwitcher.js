require('leaflet_styledlc_css');
require('leaflet_styledlc_js');

var Draw = require('../../components/edition/Draw.js');
var TimeControl = require('../../components/toc/TimeControl.js');
var AttributeTable = require('../../components/table/AttributeTable.js');

function LayerSwitcher(config, map, baseLayers, overlays, sidebar, utils, printUtils) {
    this.map = map;
    this.baseLayers = baseLayers;
    this.overlays = overlays;
    this.sidebar = sidebar;
    this.utils = utils;
    this.printUtils = printUtils;
    this.config = config;
    this.lcoptions = {
        collapsed: false,
        exclusive: false
    };
    this.initialize();
}
   
LayerSwitcher.prototype = {  
    initialize: function() {

        var draw = new Draw(this.config, this.map, this.sidebar, this.utils, this.printUtils);
        var table = new AttributeTable(this.map);
        var time = new TimeControl(this.map, this.sidebar);
        var controls = {
            draw: draw,
            table: table,
            time: time
        };

        // Create the control and add it to the map;
        var control = L.Control.styledLayerControl(controls, this.baseLayers, this.overlays, this.lcoptions);
        control.addTo(this.map);
        
         // Call the getContainer routine.
         var htmlObject = control.getContainer();
         // Get the desired parent node.
         var a = document.getElementById('toc-layers');
        
         // Finally append that node to the new parent, recursively searching out and re-parenting nodes.
         function setParent(el, newParent)
         {
            newParent.appendChild(el);
         }
         setParent(htmlObject, a);
    }
}
   
module.exports = LayerSwitcher;