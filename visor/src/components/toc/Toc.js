require('leaflet_sidebar_css');
require('leaflet_sidebar_js');

var LayerSwitcher = require('../../components/toc/LayerSwitcher.js');
var Legend = require('../../components/toc/Legend.js');
var Search = require('../../components/toc/Search.js');
var Login = require('../../components/toc/Login.js');

function Toc(config, map, baseLayers, overlays, utils) {
    this.map = map;
    this.baseLayers = baseLayers;
    this.overlays = overlays.overlays;
    this.groupedOverlays = overlays.groupedOverlays;
    this.sidebar = null;
    this.utils = utils;
    this.config = config;
    this.render();
    this.initialize();
    this.loadLayerSwitcher();
    this.loadLegend();
    if (!window.isMobile) {
        this.loadSearch();
    }
    this.loadLogin();
}
   
Toc.prototype = {
    render: function(name) {
        var html = '';
        html += '<div id="sidebar" class="leaflet-sidebar collapsed">';
        html += '<div class="leaflet-sidebar-tabs">';
        html += '    <ul role="tablist">';
        html += '        <li><a href="#toc-layers" title="Árbol de capas" role="tab"><i class="fa fa-layer-group"></i></a></li>';
        html += '        <li><a href="#toc-legend" title="Leyenda" role="tab"><i class="fa fa-list-ul"></i></a></li>';
        if (!window.isMobile) {
            //html += '        <li><a href="#toc-search" title="Búsqueda" role="tab"><i class="fa fa-search"></i></a></li>';
            html += '        <li><a href="#toc-result" title="Resultados" role="tab"><i class="fa fa-calendar-check"></i></a></li>';
        }
        html += '    </ul>';
        if (!window.isMobile) {      
            html += '    <ul role="tablist">';
            html += '        <li><a href="#toc-profile" title="Perfil de usuario" role="tab"><i class="fa fa-user"></i></a></li>';
            html += '    </ul>';
        }
        html += '</div>';
                
        html += '<div class="leaflet-sidebar-content">';
        html += '    <div class="leaflet-sidebar-pane" id="toc-layers">';
        html += '            <h1 class="leaflet-sidebar-header">Capas<div class="leaflet-sidebar-close"><i class="fa fa-caret-left"></i></div></h1>';
        html += '    </div>';
                
        html += '    <div class="leaflet-sidebar-pane" id="toc-legend">';
        html += '        <h1 class="leaflet-sidebar-header">Leyenda<div class="leaflet-sidebar-close"><i class="fa fa-caret-left"></i></div></h1>';
        html += '        <div style="margin-top: 10px;" id="toc-legend-content"></div>'; 
        html += '    </div>';
        if (!window.isMobile) {       
            html += '    <div class="leaflet-sidebar-pane" id="toc-search">';
            html += '        <h1 class="leaflet-sidebar-header">Buscar<div class="leaflet-sidebar-close"><i class="fa fa-caret-left"></i></div></h1>';
            html += '    </div>';
            html += '    <div class="leaflet-sidebar-pane" id="toc-result">';
            html += '        <h1 class="leaflet-sidebar-header">Resultados<div class="leaflet-sidebar-close"><i class="fa fa-caret-left"></i></div></h1>';
            html += '        <div style="margin-top: 10px;" id="toc-result-content">No hay resultados que mostrar</div>';
            html += '    </div>';
            html += '    <div class="leaflet-sidebar-pane" id="toc-profile">';
            html += '       <h1 class="leaflet-sidebar-header">Perfil de usuario<div class="leaflet-sidebar-close"><i class="fa fa-caret-left"></i></div></h1>';  
            html += '    </div>';
        }
        html += '</div>';
        html += '</div>';

        $('body').append(html);
    },
   
    initialize: function() {
        this.sidebar = L.control.sidebar({
            autopan: false,       // whether to maintain the centered map point when opening the sidebar
            closeButton: true,    // whether t add a close button to the panes
            container: 'sidebar', // the DOM container or #ID of a predefined sidebar container that should be used
            position: 'left',     // left or right
        }).addTo(this.map);

        this.sidebar.on('opening', function() {
            $('.leaflet-control-mouseposition').css('margin-left', '380px');
            $('.leaflet-bar-timecontrol').css('margin-left', '380px');
        });

        this.sidebar.on('closing', function() {
            $('.leaflet-control-mouseposition').css('margin-left', '0px');
            $('.leaflet-bar-timecontrol').css('margin-left', '0px');
        });
    },

    loadLayerSwitcher: function() {
        new LayerSwitcher(this.config, this.map, this.baseLayers, this.groupedOverlays, this.sidebar, this.utils);
    },

    loadLegend: function() {
        this.legend = new Legend(this.map, this.overlays);
    },

    loadSearch: function() {
        //new Search(this.map, this.groupedOverlays);
    },

    loadLogin: function() {
        new Login(this.config);
    },

    getSideBar: function() {
        return this.sidebar;
    },

    getLegend: function() {
        return this.legend;
    }
}
   
module.exports = Toc;