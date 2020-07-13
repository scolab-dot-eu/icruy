const path = require("path")
const CleanWebpackPlugin = require('clean-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const webpack = require('webpack');

module.exports = {
  entry: {
    app: './src/app.js'
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'app.bundle.js'
  },
  //devtool: 'inline-source-map',
  devServer: {
    //public: "https://geoportal.opp.localhost:443",
    //publicPath: "https://geoportal.opp.localhost:443/visor/assets/",
    //sockHost: "geoportal.opp.localhost",
    //sockPort: "443",
    //sockPath: "/visor/sockjs-node",
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept'
    },
    allowedHosts: ['*']
  },
  plugins: [
    new CleanWebpackPlugin(['dist']),
    new HtmlWebpackPlugin({template: './src/index.html'}),
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      "window.jQuery": "jquery'",
      "window.$": "jquery"
    })
  ],
  resolve: {
    extensions: ['.html', '.js', '.json', '.css'],
    alias: {
        style_css: __dirname + "/src/style.css",
        bootstrap_css: __dirname + "/node_modules/bootstrap/dist/css/bootstrap.min.css",
        bootstrap_js: __dirname + "/node_modules/bootstrap/dist/js/bootstrap.min.js",
        leaflet_css: __dirname + "/node_modules/leaflet/dist/leaflet.css",
        leaflet_marker: __dirname + "/node_modules/leaflet/dist/images/marker-icon.png",
        leaflet_marker_2x: __dirname + "/node_modules/leaflet/dist/images/marker-icon-2x.png",
        leaflet_marker_shadow: __dirname + "/node_modules/leaflet/dist/images/marker-shadow.png",
        leaflet_sidebar_js: __dirname + "/node_modules/leaflet-sidebar-v2/js/leaflet-sidebar.min.js",
        leaflet_sidebar_css: __dirname + "/node_modules/leaflet-sidebar-v2/css/leaflet-sidebar.min.css",
        fontawesome: __dirname + "/node_modules/@fortawesome/fontawesome-free/css/all.min.css",
        leaflet_logo_css: __dirname + "/vendors/leaflet-logo.min.css",
        leaflet_logo_js: __dirname + "/vendors/leaflet-logo.min.js",
        leaflet_minimap_css: __dirname + "/node_modules/leaflet-minimap/dist/Control.MiniMap.min.css",
        leaflet_draw_css: __dirname + "/node_modules/leaflet-draw/dist/leaflet.draw.css",
        leaflet_easybutton_css: __dirname + "/node_modules/leaflet-easybutton/src/easy-button.css",
        leaflet_navbar_css: __dirname + "/node_modules/leaflet-navbar/Leaflet.NavBar.css",
        leaflet_styledlc_css: __dirname + "/vendors/styled-layer-control/css/styledLayerControl.css",
        leaflet_styledlc_js: __dirname + "/vendors/styled-layer-control/styledLayerControl.js",
        leaflet_topcenter_css: __dirname + "/vendors/leaflet-control-topcenter.css",
        leaflet_topcenter_js: __dirname + "/vendors/leaflet-control-topcenter.js",
        leaflet_measure_css: __dirname + "/vendors/leaflet-measure/leaflet-measure.css",
        leaflet_measure_js: __dirname + "/vendors/leaflet-measure/leaflet-measure.es.js",
        leaflet_coordinates_css: __dirname + "/vendors/leaflet-mouseposition/L.Control.MousePosition.css",
        leaflet_coordinates_js: __dirname + "/vendors/leaflet-mouseposition/L.Control.MousePosition.js",
        leaflet_locate_css: __dirname + "/vendors/locate/L.Control.Locate.min.css",
        leaflet_locate_js: __dirname + "/vendors/locate/L.Control.Locate.min.js",
        datatables_js: __dirname + "/vendors/DataTables/datatables.min.js",
        datatables_css: __dirname + "/vendors/DataTables/datatables.min.css",
        datatables_buttons_js: __dirname + "/vendors/DataTables/Buttons-1.5.4/js/dataTables.buttons.min.js",
        js_zip: __dirname + "/vendors/DataTables/JSZip-2.5.0/jszip.min.js",
        proj4_js: __dirname + "/node_modules/proj4leaflet/lib/proj4-compressed.js",
        datatables_buttons_html5_js: __dirname + "/vendors/DataTables/Buttons-1.5.4/js/buttons.html5.min.js",
        datatables_buttons_css: __dirname + "/vendors/DataTables/Buttons-1.5.4/css/buttons.dataTables.min.css",
        leaflet_graphic_scale_css: __dirname + "/node_modules/leaflet-graphicscale/dist/Leaflet.GraphicScale.min.css",
        leaflet_timedimension_css: __dirname + "/node_modules/leaflet-timedimension/dist/leaflet.timedimension.control.min.css",
        leaflet_timedimension_js: __dirname + "/node_modules/leaflet-timedimension/dist/leaflet.timedimension.min.js",
        notify: __dirname + "/vendors/bootstrap-notify.min.js",
        responsive_js: __dirname + "/vendors/DataTables/responsive/js/dataTables.responsive.min.js",
        responsive_css: __dirname + "/vendors/DataTables/responsive/css/responsive.dataTables.min.css",
        fixed_header_js: __dirname + "/vendors/DataTables/FixedHeader-3.1.4/js/dataTables.fixedHeader.min.js",
        fixed_header_css: __dirname + "/vendors/DataTables/FixedHeader-3.1.4/css/fixedHeader.dataTables.min.css",
        leaflet_search_css: __dirname + "/node_modules/leaflet-search/dist/leaflet-search.min.css",
        leaflet_search_js: __dirname + "/node_modules/leaflet-search/dist/leaflet-search.src.js",

    }
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [ 'style-loader', 'css-loader' ]
      }, {
        test: /\.(ttf|eot|woff|woff2|png|svg|jpg|gif)$/,
        use: {
          loader: "file-loader",
          options: {
            name: '[path][name].[ext]',
          },
        },
      },
    ]
  }
}
