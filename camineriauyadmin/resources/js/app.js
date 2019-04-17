
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import "core-js/modules/es6.promise";
import "core-js/modules/es6.array.iterator";

require('./bootstrap');
//require('jquery-editable-select');
if (document.getElementById('map-proposed-feat')) {
    import('./leaflet' /* webpackChunkName: "js/leaflet" */).then(function(p1, p2, p3) {
	    $(document).trigger('icrLeafletJsLibLoaded');
    }).catch(function(err) {
	    console.log("Error loading leaflet");
	    console.log(err);
    });
}

if (document.getElementById('dt-icr-index')) {
    import( './datatables' /* webpackChunkName: "js/datatables" */).then(function(p1, p2, p3) {
	    $(document).trigger('icrDataTablesJsLibLoaded');
    }).catch(function(err) {
	    console.log("Error loading datatables");
	    console.log(err);
    });

}

$(document).ready(function () {

    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});
