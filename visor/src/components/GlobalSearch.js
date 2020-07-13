require('leaflet-search');
L.Control.GlobalSearch = L.Control.Search.extend({
    options: {
        tipAutoSubmit: false,
        searchLayer: null,
        overlays: null,
        departamento: null,
        departamentos: [],
        camineriaUrls: []
    },
	initialize: function (options) {
		this._partialRecords = {};
		L.Control.Search.prototype.initialize.call(this, options);
	},
    onAdd(map) {
		var container = L.Control.Search.prototype.onAdd.call(this, map);
		var self = this;
		/*
		this._map.on("popupclose", function(evt) {
			self.options.searchLayer.clearLayers();
		});*/
		return container;
    },
    _getCaminoLayerName(searchRecord) {
		var departamento;
		if (this.options.departamento != null) {
			departamento = this.options.departamento;
		}
		else {
			departamento = searchRecord.departamento;
		}
		for (var i = 0; i<this.options.departamentos.length; i++) {
			var dep = this.options.departamentos[i];
			if (dep.code == departamento) {
				return dep.layer_name;
			}
		}
		return null;
	},
    _findLeafletCaminoRecord(searchRecord, layers) {
		var codigo_camino;
		if (searchRecord.codigo_camino) {
			codigo_camino = searchRecord.codigo_camino;
		}
		else if (searchRecord.properties && searchRecord.properties.codigo) {
			codigo_camino = searchRecord.properties.codigo;
		}
		
		var fullRecord = false;
		var wfsLayerName = this._getCaminoLayerName(searchRecord);
		for (var i=0; fullRecord==false && i<layers.length; i++) {
			if (layers[i].name == wfsLayerName) {
				var enabled = false;
				this._map.eachLayer(function(l){ if (l==layers[i]) {
					enabled = true;
					}});
				var bounds = null;
				this.options.searchLayer.clearLayers();
				var firstRecord = false;
				for (var j=0; j<layers[i].getLayers().length; j++) {
					if (layers[i].getLayers()[j].feature.properties.codigo_camino == codigo_camino) {
						fullRecord = layers[i].getLayers()[j];
						if (firstRecord===false) {
							firstRecord = layers[i].getLayers()[j];
						}
						if (!enabled) {
							this.options.searchLayer.addLayer(fullRecord);
						}
						if (bounds===null) {
							bounds = fullRecord.getBounds();
						}
						else {
							bounds.extend(fullRecord.getBounds());
						}
					}
				}
				if (fullRecord!==false) {
					firstRecord.openPopup();
					this._map.fitBounds(bounds);
					this.fire('search:locationfound', {
						bounds: bounds,
						latlng: null,
						text: this._input.value,
						layer: fullRecord
					});
					break;
				}
			}
		}
	},
    _findLeafletRecord(searchRecord, layers) {
		var layerName = searchRecord.layer;
		var feat_id = searchRecord.feat_id;
		var fullRecord = false;
		for (var i=0; fullRecord==false && i<layers.length; i++) {
			if (layers[i].name == 'camineria:'+layerName) {
				var enabled = false;
				this._map.eachLayer(function(l){ if (l==layers[i]) {
					enabled = true;
					}});
				for (var j=0; fullRecord==false && j<layers[i].getLayers().length; j++) {
					if (layers[i].getLayers()[j].feature.properties.id == feat_id) {
						fullRecord = layers[i].getLayers()[j];
						if (!enabled) {
							this.options.searchLayer.clearLayers();
							this.options.searchLayer.addLayer(fullRecord);
						} 
						
						this._map.setView(fullRecord.getLatLng(), 11);
						fullRecord.openPopup();                                            
					}
				}
				//this.showLocation(loc, this._input.value);
				if (fullRecord!==false) {
					this.fire('search:locationfound', {
							latlng: fullRecord.getLatLng(),
							text: this._input.value,
							layer: fullRecord
						});
				}
			}
		}
	},
	_handleFromLayers(record) {
		var layers = this.options.overlays.overlays;
		var layerName = record.layer;
		if (layerName=='cr_caminos') {
			this._findLeafletCaminoRecord(record, layers);
		}
		else if (record.id && typeof(record.id) == 'string' && record.id.startsWith('v_camineria_')) {
			this._findLeafletCaminoRecord(record, layers);
		}
		else {
			this._findLeafletRecord(record, layers);
		}
	},
    _handleSubmit: function() {    //button and tooltip click and enter submit
        this._hideAutoType();
        
        this.hideAlert();
        this._hideTooltip();

        if(this._input.style.display == 'none')    //on first click show _input only
            this.expand();
        else
        {
            if(this._input.value === '')    //hide _input only
                this.collapse();
            else
            {
                var id = this._input.getAttribute("data-recordid");
                var record;
                if( this._recordsCache.hasOwnProperty(id) )
                    record = this._recordsCache[id];
                else
                    record = false;
                
                if(record===false)
                    this.showAlert();
                else
                {
					this._map.closePopup();
					this._handleFromLayers(record);
                }
            }
        }
    },
    _defaultFormatData: function(json) {    //format data to indexed data
        var i, jsonret = {};
        for (i in json) {
			if (json[i]["feat_id"]) {
				jsonret[json[i]["layer"] + ":" + json[i]["feat_id"]] = json[i];
			}
			else {
				jsonret[json[i]["id"]] = json[i];
			}
        }
        return jsonret;
    },
    _createTip: function(text, val) {//val is object in recordCache, usually is Latlng
        var tip;
        if(this.options.buildTip)
        {
            tip = this.options.buildTip.call(this, text, val); //custom tip node or html string
            if(typeof tip === 'string')
            {
                var tmpNode = L.DomUtil.create('div');
                tmpNode.innerHTML = tip;
                tip = tmpNode.firstChild;
            }
        }
        else
        {
            tip = this._defaultBuildTip.call(this, text, val);
        }
        
        L.DomUtil.addClass(tip, 'search-tip');
        tip._text = text; //value replaced in this._input and used by _autoType

        if(this.options.tipAutoSubmit)
            L.DomEvent
                .disableClickPropagation(tip)        
                .on(tip, 'click', L.DomEvent.stop, this)
                .on(tip, 'click', function(e) {
                    this._input.value = text;
                    this._handleAutoresize();
                    this._input.focus();
                    this._hideTooltip();    
                    this._handleSubmit();
                }, this);

        return tip;
    },
    _defaultBuildTip: function(id, record) {
        var tip = L.DomUtil.create('li', '');
        var nombre = '';
        if (record.nombre) {
			nombre = record.nombre;
		}
		else if (record.properties.nombre && record.properties.codigo) {
			nombre = record.properties.nombre + " - " + record.properties.codigo;
		}
		tip.innerHTML = nombre;
        L.DomEvent
            .disableClickPropagation(tip)        
            .on(tip, 'click', L.DomEvent.stop, this)
            .on(tip, 'click', function(e) {
                this._input.value = nombre;
                this._input.setAttribute('data-recordid', id);
                this._handleAutoresize();
                this._input.focus();
                this._hideTooltip();    
                this._handleSubmit();
            }, this);
        return tip;
    },
	_myRecordsFromAjax: function(text) {	//Ajax request
		var self = this;
		if (window.XMLHttpRequest === undefined) {
			window.XMLHttpRequest = function() {
				try { return new ActiveXObject("Microsoft.XMLHTTP.6.0"); }
				catch  (e1) {
					try { return new ActiveXObject("Microsoft.XMLHTTP.3.0"); }
					catch (e2) { throw new Error("XMLHttpRequest is not supported"); }
				}
			};
		}
		var IE8or9 = ( L.Browser.ie && !window.atob && document.querySelector ),
			request = IE8or9 ? new XDomainRequest() : new XMLHttpRequest();
		var url = L.Util.template(this._getUrl(text), {s: text});

		//rnd = '&_='+Math.floor(Math.random()*10000);
		//TODO add rnd param or randomize callback name! in recordsFromAjax			
		
		request.open("GET", url);
		

		request.onload = function() {
			self._manageRetrievedData(JSON.parse(request.responseText));
		};
		request.onreadystatechange = function() {
		    if(request.readyState === 4 && request.status === 200) {
		    	this.onload();
		    }
		};

		request.send();
		return request;   
	},
	_myRecordsFromAjaxCamineria: function(text, url) {	//Ajax request
		var self = this;
		if (window.XMLHttpRequest === undefined) {
			window.XMLHttpRequest = function() {
				try { return new ActiveXObject("Microsoft.XMLHTTP.6.0"); }
				catch  (e1) {
					try { return new ActiveXObject("Microsoft.XMLHTTP.3.0"); }
					catch (e2) { throw new Error("XMLHttpRequest is not supported"); }
				}
			};
		}
		var IE8or9 = ( L.Browser.ie && !window.atob && document.querySelector ),
			request = IE8or9 ? new XDomainRequest() : new XMLHttpRequest();
		 // remove cql_filter and add OGC filter
		url = url.split("&cql_filter")[0];
		var filterStr = "&Filter=<Filter><Or><PropertyIsLike wildCard='*' singleChar='.' escape='!' matchCase='false'><PropertyName>codigo</PropertyName><Literal>*{s}*</Literal></PropertyIsLike><PropertyIsLike wildCard='*' singleChar='.' escape='!'><PropertyName>nombre</PropertyName><Literal>*{s}*</Literal></PropertyIsLike></Or></Filter>";
		url += filterStr;
		url = L.Util.template(url, {s: text});

		//rnd = '&_='+Math.floor(Math.random()*10000);
		//TODO add rnd param or randomize callback name! in recordsFromAjax			
		
		request.open("GET", url);
		

		request.onload = function() {
			var jsonData = JSON.parse(request.responseText);
			var features = jsonData.features;
			// TODO: merge results with camineria layer
			self._manageRetrievedData(features);
		};
		request.onreadystatechange = function() {
		    if(request.readyState === 4 && request.status === 200) {
		    	this.onload();
		    }
		};

		request.send();
		return request;   
	},
	_manageRetrievedData: function(data) {
		var records, rawRecords = this._formatData(data);
		L.extend(this._recordsCache, rawRecords);

		if(this.options.sourceData)
			records = this._filterData( this._input.value, rawRecords );
		else
			records = rawRecords;
		
		L.extend(this._partialRecords, records);
		this.showTooltip( this._partialRecords );

		L.DomUtil.removeClass(this._container, 'search-load');
	},
	_fillRecordsCache: function() {

		var self = this,
			inputText = this._input.value, records;

		if(this._curReq && this._curReq.abort)
			this._curReq.abort();
			this._partialRecords = {};
		//abort previous requests

		L.DomUtil.addClass(this._container, 'search-load');	

		if(this.options.layer)
		{
			//TODO _recordsFromLayer must return array of objects, formatted from _formatData
			this._recordsCache = this._recordsFromLayer();
			
			records = this._filterData( this._input.value, this._recordsCache );

			this.showTooltip( records );

			L.DomUtil.removeClass(this._container, 'search-load');
		}
		else
		{
			if(this.options.url)	//jsonp or ajax
				this._curReq = this._myRecordsFromAjax(inputText);
			
			for (var i=0; i<this.options.camineriaUrls.length; i++) {
				var url = this.options.camineriaUrls[i];
				this._curReq = this._myRecordsFromAjaxCamineria(inputText, url);
			}
		}
	},
	collapse: function() {
		this._hideTooltip();
		//this.cancel();
		this._alert.style.display = 'none';
		this._input.blur();
		if(this.options.collapsed)
		{
			this._input.style.display = 'none';
			//this._cancel.style.display = 'none';			
			L.DomUtil.removeClass(this._container, 'search-exp');		
			if (this.options.hideMarkerOnCollapse) {
				this._map.removeLayer(this._markerSearch);
			}
			this._map.off('dragstart click', this.collapse, this);
		}
		this.fire('search:collapsed');
		return this;
	},
	cancel: function() {
		this._input.value = '';
		this._handleKeypress({ keyCode: 8 });//simulate backspace keypress
		this._input.size = this._inputMinSize;
		this._input.focus();
		this._cancel.style.display = 'none';
		this._hideTooltip();
		this.options.searchLayer.clearLayers();
		this.fire('search:cancel');
		return this;
	}
});

// module.exports = Search;
