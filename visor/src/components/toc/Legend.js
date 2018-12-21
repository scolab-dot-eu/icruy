function Legend(map, overlays) {
    this.map = map;
    this.overlays = overlays;
    this.legendContainer = $('#toc-legend-content');
    this.initialize();
}
   
Legend.prototype = {  

    initialize: function() {
	    var legends = this.getLegendsFromVisibleLayers();
	    this.legendContainer.append(legends);
    },

    reloadLegend: function() {
	    var legends = this.getLegendsFromVisibleLayers();
	    this.legendContainer.empty();	
	    this.legendContainer.append(legends);
    },

    getLegendsFromVisibleLayers: function() {
        var _this = this;

        var html = '';
        html += '<table>';
        html +=     '<tbody>';
        for (var i=0; i<this.overlays.length; i++) {
            if (this.map.hasLayer(this.overlays[i])) {
                if (this.overlays[i].type == 'wms') {
                    var url = this.overlays[i]._url + '?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=30&HEIGHT=30&LAYER=' + this.overlays[i].wmsParams.layers;
                    html += '<tr>';
                    html +=     '<td><img style="height: 30px; width: 30px;" src="' + url + '"></td>';
                    html +=     '<td>' + this.overlays[i].title + '</td>';
                    html += '</tr>';

                } else if (this.overlays[i].type == 'wfs') {
                    if (this.overlays[i].geom_style == 'marker') {
                        var url1 = window.serviceURL + '/visor/assets/images/validated-' + this.overlays[i].style.iconUrl;
                        var url2 = window.serviceURL + '/visor/assets/images/pending-' + this.overlays[i].style.iconUrl;
                        html += '<tr>';
                        html +=     '<td><img style="height: 30px; width: 30px;" src="' + url1 + '"></td>';
                        html +=     '<td>' + this.overlays[i].title + ' (VALIDADO)</td>';
                        html += '</tr>';
                        html += '<tr>';
                        html +=     '<td><img style="height: 30px; width: 30px;" src="' + url2 + '"></td>';
                        html +=     '<td>' + this.overlays[i].title + ' (PENDIENTE)</td>';
                        html += '</tr>';

                    } else if (this.overlays[i].geom_style == 'point') {
                        html += '<tr>';
                        html +=     '<td>';
                        html +=         '<svg height="25" width="25">';
                        html +=             '<circle cx="12" cy="12" r="' + this.overlays[i].style.radius + '" stroke="' + this.overlays[i].style.color + '" opacity="1" stroke-width="' + this.overlays[i].style.weight + '" fill="' + this.overlays[i].style.fillColor + '" />';
                        html +=         '</svg>';
                        html +=     '</td>';
                        html +=     '<td>' + this.overlays[i].title + ' (VALIDADO)</td>';
                        html += '</tr>';
                        html += '<tr>';
                        html +=     '<td>';
                        html +=         '<svg height="25" width="25">';
                        html +=             '<circle cx="12" cy="12" r="' + this.overlays[i].style.radius + '" stroke="' + this.overlays[i].style.color + '" opacity="0.5" stroke-width="' + this.overlays[i].style.weight + '" fill="' + this.overlays[i].style.fillColor + '" />';
                        html +=         '</svg>';
                        html +=     '</td>';
                        html +=     '<td>' + this.overlays[i].title + ' (PENDIENTE)</td>';
                        html += '</tr>';

                    } else if (this.overlays[i].geom_style == 'line') {
                        html += '<tr>';
                        html +=     '<td>';
                        html +=         '<svg height="25" width="25">';
                        html +=             '<line x1="0" y1="25" x2="25" y2="0" style="stroke:' + this.overlays[i].style.color + '; stroke-width:' + this.overlays[i].style.weight + '" />';
                        html +=         '</svg>';
                        html +=     '</td>';
                        html +=     '<td>' + this.overlays[i].title + '</td>';
                        html += '</tr>';

                    } else if (this.overlays[i].geom_style == 'polygon') {
                    
                    }
                }
            }
        }
        html +=     '</tbody>';
        html += '</table>';
        
        return html;
    }
}
   
module.exports = Legend;