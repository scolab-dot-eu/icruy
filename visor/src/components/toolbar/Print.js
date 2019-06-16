function Print(map, printUtils) {
    this.map = map;
    this.printUtils = printUtils;
    this.control = null;
    this.PIXEL_SIZE = 3.779528;   	
    this.MAP_WIDTH_MM = 220;
    this.MAP_HEIGHT_MM = 250;
    this.initialize(); 
}
   
Print.prototype = {
    initialize: function() {
        var _this = this;
        var m = this.map;
        this.control = L.easyButton('fa-print', function(btn, m){
            _this.print();
        }, 'Imprimir');
    },

    print: function() {
        var _this = this;

        var mask = '<div id="animationload" class="animationload"><div class="osahanloading"></div></div>';
        $('body').append(mask);

        var doc = new jsPDF('landscape', 'mm', 'a4');

        doc.addImage(this.printUtils.getLogoOpp(), 'PNG', 15, 15, 30, 10);
        doc.addImage(this.printUtils.getLogoPresidencia(), 'PNG', 256, 8, 30, 20);

        doc.setFontSize(24);
        doc.setTextColor(5, 90, 5);
        doc.text(70, 22, 'Sistema de inventario de Caminería Rural');

        leafletImage(this.map, function(err, canvas) {
            var dataUrl = _this.printUtils.canvasToImage(canvas, '#ffffff');

            var pdfMapWidthInPx = parseInt(_this.MAP_WIDTH_MM * _this.PIXEL_SIZE);
			var pdfMapHeightInPx = parseInt(_this.MAP_HEIGHT_MM * _this.PIXEL_SIZE);
            var newSize = _this.printUtils.calculateAspectRatioFit(_this.map.getSize().x, _this.map.getSize().y, pdfMapWidthInPx, pdfMapHeightInPx);		
			var mmNewWidth = newSize.width / _this.PIXEL_SIZE;
			var mmNewHeight= newSize.height / _this.PIXEL_SIZE;
            doc.addImage(dataUrl, 'PNG', 15, 40, mmNewWidth, mmNewHeight);

            doc.setFontSize(12);
            doc.setTextColor(40, 40, 40);
            doc.text(255, 45, 'LEYENDA');

            doc.setFontSize(9);

            doc.setLineWidth(0.5);
            doc.setDrawColor(80, 80, 80);
            doc.setFillColor(80, 80, 80);
            doc.circle(240, 51, 1, 'FD');
            doc.text(245, 51, 'Alcantarillas (VALIDADO)');

            doc.circle(240, 56, 1);
            doc.text(245, 56, 'Alcantarillas (PENDIENTE)');

            doc.setDrawColor(90, 90, 5);
            doc.setFillColor(90, 90, 5);
            doc.circle(240, 61, 1, 'FD');
            doc.text(245, 61, 'Badenes (VALIDADO)');

            doc.circle(240, 66, 1);
            doc.text(245, 66, 'Badenes (PENDIENTE)');

            doc.setDrawColor(0, 255, 0);
            doc.setFillColor(0, 255, 0);
            doc.circle(240, 71, 1, 'FD');
            doc.text(245, 71, 'Puentes (VALIDADO)');

            doc.circle(240, 76, 1);
            doc.text(245, 76, 'Puentes (PENDIENTE)');

            doc.setDrawColor(255, 0, 0);
            doc.setFillColor(255, 0, 0);
            doc.circle(240, 81, 1, 'FD');
            doc.text(245, 81, 'Obstáculos (VALIDADO)');

            doc.circle(240, 86, 1);
            doc.text(245, 86, 'Obstáculos (PENDIENTE)');

            doc.setDrawColor(255, 255, 0);
            doc.setFillColor(255, 255, 0);
            doc.circle(240, 91, 1, 'FD');
            doc.text(245, 91, 'Pasos (VALIDADO)');

            doc.circle(240, 96, 1);
            doc.text(245, 96, 'Pasos (PENDIENTE)');

            doc.setDrawColor(0, 0, 255);
            doc.setFillColor(0, 0, 255);
            doc.circle(240, 101, 1, 'FD');
            doc.text(245, 101, 'Señales (VALIDADO)');

            doc.circle(240, 106, 1);
            doc.text(245, 106, 'Señales (PENDIENTE)');

            doc.setFontSize(10);
            doc.setTextColor(12, 12, 12);
            doc.text(100, 195, 'Torre Ejecutiva Sur, piso 7 | Liniers 1324, Montevideo - Uruguay');
            doc.text(120, 200, 'Tel. (+598 2) 150  | www.opp.gub.uy');

            var uri = doc.output('dataurlstring');
            _this.printUtils.openDataUriWindow(uri);
            $('#animationload').remove();
        });
    },

    getControl: function(){
        return this.control;
    }
}
   
module.exports = Print;