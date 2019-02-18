L.Control.MousePosition = L.Control.extend({
    options: {
      position: 'bottomleft',
      separator: ' : ',
      emptyString: 'Unavailable',
      lngFirst: false,
      numDigits: 5,
      lngFormatter: undefined,
      latFormatter: undefined,
      prefix: ""
    },
  
    onAdd: function (map) {
      this._container = L.DomUtil.create('div', 'leaflet-control-mouseposition');
      this._container.id = 'leaflet-control-mouseposition';
      this._$container = $(this._container);
      this._$container.css('display', 'inherit');
      this._$container.append('<div id="output"></div>');
      this._$container.append('<div id="epsg" style="margin-left: 10px;"></div>');
      L.DomEvent.disableClickPropagation(this._container);
      map.on('mousemove', this._onMouseMove, this);
      //this._container.innerHTML=this.options.emptyString;
      $('#output').html(this.options.emptyString);

      return this._container;
    },
  
    onRemove: function (map) {
      map.off('mousemove', this._onMouseMove)
    },
  
    _onMouseMove: function (e) {
      var epsg = $("#epsg-selector option").filter(":selected").val();
      if (epsg == 'wgs84_latlon') {
        var lng = this.options.lngFormatter ? this.options.lngFormatter(e.latlng.lng) : L.Util.formatNum(e.latlng.lng, this.options.numDigits);
        var lat = this.options.latFormatter ? this.options.latFormatter(e.latlng.lat) : L.Util.formatNum(e.latlng.lat, this.options.numDigits);
        var value = this.options.lngFirst ? lng + this.options.separator + lat : lat + this.options.separator + lng;
        var prefixAndValue = this.options.prefix + ' ' + value;
        $('#output').html(prefixAndValue);

      } else if (epsg == 'wgs84_degrees') {
        var dmsCoords = this._ddToDms(e.latlng.lat, e.latlng.lng);
        $('#output').html(dmsCoords);

      } else if (epsg == 'utm') {
        var lng = this.options.lngFormatter ? this.options.lngFormatter(e.latlng.lng) : L.Util.formatNum(e.latlng.lng, this.options.numDigits);
        var lat = this.options.latFormatter ? this.options.latFormatter(e.latlng.lat) : L.Util.formatNum(e.latlng.lat, this.options.numDigits);

        var latlon_utm = window.proj4('+proj=utm +zone=21 +south +datum=WGS84 +units=m +no_defs',[lng,lat]);
        var value = this.options.lngFirst ? latlon_utm[1] + this.options.separator + latlon_utm[0] : latlon_utm[0] + this.options.separator + latlon_utm[1];
        var prefixAndValue = 'x/y' + ' ' + value;
        $('#output').html(prefixAndValue);

      }
      
    },

    _ddToDms: function(lat, lng) {

      var lat = lat;
      var lng = lng;
      var latResult, lngResult, dmsResult;
  
      lat = parseFloat(lat);  
      lng = parseFloat(lng);
  
      // Check the correspondence of the coordinates for latitude: North or South.
      latResult = (lat >= 0)? 'N' : 'S';
  
      // Call to getDms(lat) function for the coordinates of Latitude in DMS.
      // The result is stored in latResult variable.
      latResult += this.getDms(lat);
  
      // Check the correspondence of the coordinates for longitude: East or West.
      lngResult = (lng >= 0)? 'E' : 'W';
  
      // Call to getDms(lng) function for the coordinates of Longitude in DMS.
      // The result is stored in lngResult variable.
      lngResult += this.getDms(lng);
  
      // Joining both variables and separate them with a space.
      dmsResult = latResult + ' ' + lngResult;
  
      // Return the resultant string.
      return dmsResult;
    },

    getDms: function(val) {

      // Required variables
      var valDeg, valMin, valSec, result;
  
      // Here you'll convert the value received in the parameter to an absolute value.
      // Conversion of negative to positive.
      // In this step does not matter if it's North, South, East or West,
      // such verification was performed earlier.
      val = Math.abs(val); // -40.601203 = 40.601203
  
      // ---- Degrees ----
      // Stores the integer of DD for the Degrees value in DMS
      valDeg = Math.floor(val); // 40.601203 = 40
  
      // Add the degrees value to the result by adding the degrees symbol "º".
      result = valDeg + "º"; // 40º
  
      valMin = Math.floor((val - valDeg) * 60); // 36.07218 = 36
  
      // Add minutes to the result, adding the symbol minutes "'".
      result += valMin + "'"; // 40º36'
  
      valSec = Math.round((val - valDeg - valMin / 60) * 3600 * 1000) / 1000; // 40.601203 = 4.331 
  
      // Add the seconds value to the result,
      // adding the seconds symbol " " ".
      result += valSec + '"'; // 40º36'4.331"
  
      // Returns the resulting string.
      return result;
    }
  
  });

  L.Map.mergeOptions({
      positionControl: false
  });
  
  L.Map.addInitHook(function () {
      if (this.options.positionControl) {
          this.positionControl = new L.Control.MousePosition();
          this.addControl(this.positionControl);
      }
  });
  
  L.control.mousePosition = function (options) {
      return new L.Control.MousePosition(options);
  };