<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\EditableLayerDef;

class EditableLayerDefTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $WFS_URL = env('WFS_URL','');
        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_alcantarillas';
        $lyr->title = 'Alcantarillas';
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        
        $lyr->fields = '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_alcantarilla", "type": "stringdomain", "label": "Tipo de alcantarilla", "domain": [{"code": "CRUCE CAÑO", "definition": "CRUCE CAÑO"}, {"code": "Z", "definition": "Z"}, {"code": "H", "definition": "H"}, {"code": "A", "definition": "A"}, {"code": "B", "definition": "B"}, {"code": "C", "definition": "C"}, {"code": "D", "definition": "D"}, {"code": "E", "definition": "E"}, {"code": "F", "definition": "F"}, {"code": "F", "definition": "F"}], "definition": "CARACTERIZACIÓN DE ALCANTARILLAS SEGÚN TIPOS ESTABLECIDOS POR EL MTOP"}, {"name": "rodadura", "type": "stringdomain", "label": "Rodadura", "domain": [{"code": "r1", "definition": "GRANULAR"}, {"code": "R2", "definition": "HORMIGON"}, {"code": "R3", "definition": "CARPETA ASFALTICA"}, {"code": "R4", "definition": "TRATAMIENTO BITUMINOSO"}, {"code": "R5", "definition": "MEJORADO"}, {"code": "R6", "definition": "CEMENTADO"}, {"code": "R7", "definition": "CEMENTADO Y TRATAMIENTO BITUMINOSO"}, {"code": "R8", "definition": "TERRENO NATURAL"}, {"code": "R9", "definition": "EMPEDRADO"}, {"code": "R99", "definition": "OTRO"}], "definition": "TIPO DE MATERIAL DE RECUBRIMIENTO"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "cantidad_de_bocas", "type": "intdecimal", "label": "Cantidad de bocas", "definition": "NÚMERO DE CONDUCTOS"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}]';
        /*
        $lyr->style = '{"iconUrl": "marker-alcantarilla.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
        /$lyr->geom_style = 'marker';
         * */
        $lyr->style = '{"radius": 5, "fillColor": "#686562", "color": "#686562", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name , $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            Log::error($e);
        }

        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_baden';
        $lyr->title = 'Badenes';
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        $lyr->fields = '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "rodadura", "type": "stringdomain", "label": "Rodadura", "domain": [{"code": "r1", "definition": "GRANULAR"}, {"code": "R2", "definition": "HORMIGON"}, {"code": "R3", "definition": "CARPETA ASFALTICA"}, {"code": "R4", "definition": "TRATAMIENTO BITUMINOSO"}, {"code": "R5", "definition": "MEJORADO"}, {"code": "R6", "definition": "CEMENTADO"}, {"code": "R7", "definition": "CEMENTADO Y TRATAMIENTO BITUMINOSO"}, {"code": "R8", "definition": "TERRENO NATURAL"}, {"code": "R9", "definition": "EMPEDRADO"}, {"code": "R99", "definition": "OTRO"}], "definition": "TIPO DE MATERIAL DE RECUBRIMIENTO"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "dimensiones", "type": "string", "label": "Dimensiones", "definition": "MEDIDA DE LOS CONDUCTOS"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}]';
        /*
        $lyr->style = '{"iconUrl": "marker-baden.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
        $lyr->geom_style = 'marker';
        */
        $lyr->style = '{"radius": 5, "fillColor": "#F08A08", "color": "#F08A08", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name , $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            Log::error($e);
        }
        
        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_obstaculo';
        $lyr->title = 'Obstáculos';
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        $lyr->fields = '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo", "type": "stringdomain", "label": "Tipo de obstáculo", "domain": [{"code": "PORTERA", "definition": "PORTERA"}, {"code": "LOMO_DE_BURRO", "definition": "LOMO DE BURRO"}, {"code": "MATABURRO", "definition": "MATABURRO"}, {"code": "PORTERA_CON_MATABURRO", "definition": "PORTERA CON MATABURRO"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "TIPO DE OBSTÁCULO"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"},{"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}]';
        
        $lyr->style = '{"radius": 5, "fillColor": "#F01D08", "color": "#F01D08", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        /*
        $lyr->style = '{"iconUrl": "marker-obstaculo.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
        $lyr->geom_style = 'marker';
        */
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name , $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            Log::error($e);
        }

        
        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_paso';
        $lyr->title = 'Pasos';
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        $lyr->fields = '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"},{"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}]';
        /*
        $lyr->style = '{"iconUrl": "marker-paso.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
        $lyr->geom_style = 'marker';
        */
        $lyr->style = '{"radius": 5, "fillColor": "#EEEB0D", "color": "#EEEB0D", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name , $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            Log::error($e);
        }
        
        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_puente';
        $lyr->title = 'Puentes';
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        $lyr->fields = '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"}, {"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_puente", "type": "stringdomain", "label": "Tipo de puente", "domain": [{"code": "ANGOSTO", "definition": "ANGOSTO"}, {"code": "ANCHO_NORMAL", "definition": "ANCHO NORMAL"}, {"code": "VIADUCTO", "definition": "VIADUCTO"}, {"code": "PASO_BAJO_VIADUCTO", "definition": "PASO BAJO VIADUCTO"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "TIPO PUENTE"}, {"name": "estructura", "type": "stringdomain", "label": "Estructura", "domain": [{"code": "HORMIGON", "definition": "HORMIGON"}, {"code": "HIERRO", "definition": "HIERRO"}, {"code": "HIERRO_HORMIGON", "definition": "HIERRO Y HORMIGON"}, {"code": "HIERRO_MADERA", "definition": "HIERRO Y MADERA"}, {"code": "MADERA_HORMIGON", "definition": "MADERA Y HORMIGON"}, {"code": "MADERA", "definition": "MADERA"}, {"code": "TRONCOS", "definition": "TRONCOS"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "ESTRUCTURA"}, {"name": "galibo", "type": "stringdomain", "label": "Galibo", "domain": [{"code": "SI", "definition": "SI"}, {"code": "NO", "definition": "NO"}], "definition": "GALIBO"}, {"name": "medida_galibo", "type": "decimal", "label": "Medida galibo", "definition": "MEDIDA GALIBO", "typeparams": "2,0"}, {"name": "ancho", "type": "decimal", "label": "Ancho", "definition": "ANCHO", "typeparams": "2,0"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "restriccion_peso", "type": "stringdomain", "label": "Restricción de peso", "domain": [{"code": "SI", "definition": "SI"}, {"code": "NO", "definition": "NO"}], "definition": "RESTRICCIÓN PESO"}, {"name": "carga_maxima", "type": "decimal", "label": "Carga Máxima", "definition": "CARGA MÁXIMA", "typeparams": "2,0"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}, {"name": "status", "type": "string", "label": "Estado", "definition": "ESTADO", "typeparams": "255"}]';
        /*
        $lyr->style = '{"iconUrl": "marker-puente.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
        $lyr->geom_style = 'marker';
        */
        $lyr->style = '{"radius": 5, "fillColor": "#25C62F", "color": "#25C62F", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name , $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            Log::error($e);
        }
        
        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_senyal';
        $lyr->title = 'Señales';
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        $lyr->fields = '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_senyal", "type": "stringdomain", "label": "Tipo de señal", "domain": [{"code": "REGLAMENTACION", "definition": "REGLAMENTACION"}, {"code": "INFORMATIVA", "definition": "INFORMATIVA"}, {"code": "PREVENCION", "definition": "PREVENCION"}], "definition": "TIPO SEÑAL"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"},{"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}]';
        /*
        $lyr->style = '{"iconUrl": "marker-signal.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
        $lyr->geom_style = 'marker';
        */
        $lyr->style = '{"radius": 5, "fillColor": "#084EF0", "color": "#084EF0", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name , $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            Log::error($e);
        }
        
        /*
        $lyr = new EditableLayerDef();
        $lyr->name = 'camineria:cr_puente';
        $lyr->conf = '{"url": "http://geoportal.opp.com/geoserver/camineria/wfs", "name": "camineria:cr_puente", "type": "wfs", "style": {"iconUrl": "marker-puente.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}, "title": "Puentes", "fields": [{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"}, {"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_puente", "type": "stringdomain", "label": "Tipo de puente", "domain": [{"code": "ANGOSTO", "definition": "ANGOSTO"}, {"code": "ANCHO_NORMAL", "definition": "ANCHO NORMAL"}, {"code": "VIADUCTO", "definition": "VIADUCTO"}, {"code": "PASO_BAJO_VIADUCTO", "definition": "PASO BAJO VIADUCTO"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "TIPO PUENTE"}, {"name": "estructura", "type": "stringdomain", "label": "Estructura", "domain": [{"code": "HORMIGON", "definition": "HORMIGON"}, {"code": "HIERRO", "definition": "HIERRO"}, {"code": "HIERRO_HORMIGON", "definition": "HIERRO Y HORMIGON"}, {"code": "HIERRO_MADERA", "definition": "HIERRO Y MADERA"}, {"code": "MADERA_HORMIGON", "definition": "MADERA Y HORMIGON"}, {"code": "MADERA", "definition": "MADERA"}, {"code": "TRONCOS", "definition": "TRONCOS"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "ESTRUCTURA"}, {"name": "galibo", "type": "stringdomain", "label": "Galibo", "domain": [{"code": "SI", "definition": "SI"}, {"code": "NO", "definition": "NO"}], "definition": "GALIBO"}, {"name": "medida_galibo", "type": "decimal", "label": "Medida galibo", "definition": "MEDIDA GALIBO", "typeparams": "2,0"}, {"name": "ancho", "type": "decimal", "label": "Ancho", "definition": "ANCHO", "typeparams": "2,0"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "restriccion_peso", "type": "stringdomain", "label": "Restricción de peso", "domain": [{"code": "SI", "definition": "SI"}, {"code": "NO", "definition": "NO"}], "definition": "RESTRICCIÓN PESO"}, {"name": "carga_maxima", "type": "decimal", "label": "Carga Máxima", "definition": "CARGA MÁXIMA", "typeparams": "2,0"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}, {"name": "status", "type": "string", "label": "Estado", "definition": "ESTADO", "typeparams": "255"}], "visible": true, "download": true, "editable": true, "metadata": "", "geom_type": "point", "showTable": true, "geom_style": "marker", "hasMetadata": true, "showInSearch": true}';
        $lyr->save();

        $lyr = new EditableLayerDef();
        $lyr->name = 'camineria:cr_paso';
        $lyr->conf = '{"url": "http://geoportal.opp.com/geoserver/camineria/wfs", "name": "camineria:cr_paso", "type": "wfs", "style": {"iconUrl": "marker-paso.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}, "title": "Pasos", "fields": [{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}], "visible": true, "download": true, "editable": true, "metadata": "https://vanilla.geocat.net/geonetwork/srv/spa/catalog.search;jsessionid=0590273B370B98076472E1023A494182#/metadata/spacarcapaemerppmax25awmstematica20140408", "geom_type": "point", "showTable": true, "geom_style": "marker", "hasMetadata": true, "showInSearch": true}';
        $lyr->save();
        
        $lyr = new EditableLayerDef();
        $lyr->name = 'camineria:cr_senyal';
        $lyr->conf = '{"url": "http://geoportal.opp.com/geoserver/camineria/wfs", "name": "camineria:cr_senyal", "type": "wfs", "style": {"iconUrl": "marker-signal.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}, "title": "Señales", "fields": [{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_senyal", "type": "stringdomain", "label": "Tipo de señal", "domain": [{"code": "REGLAMENTACION", "definition": "REGLAMENTACION"}, {"code": "INFORMATIVA", "definition": "INFORMATIVA"}, {"code": "PREVENCION", "definition": "PREVENCION"}], "definition": "TIPO SEÑAL"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}], "visible": true, "download": true, "editable": true, "metadata": "https://vanilla.geocat.net/geonetwork/srv/spa/catalog.search;jsessionid=0590273B370B98076472E1023A494182#/metadata/spacarcapaemerppmax25awmstematica20140408", "geom_type": "point", "showTable": true, "geom_style": "marker", "hasMetadata": true, "showInSearch": true}';
        $lyr->save();
        
        $lyr = new EditableLayerDef();
        $lyr->name = 'camineria:cr_obstaculo';
        $lyr->conf = '{"url": "http://geoportal.opp.com/geoserver/camineria/wfs", "name": "camineria:cr_obstaculo", "type": "wfs", "style": {"iconUrl": "marker-obstaculo.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}, "title": "Obstáculos", "fields": [{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo", "type": "stringdomain", "label": "Tipo de obstáculo", "domain": [{"code": "PORTERA", "definition": "PORTERA"}, {"code": "LOMO_DE_BURRO", "definition": "LOMO DE BURRO"}, {"code": "MATABURRO", "definition": "MATABURRO"}, {"code": "PORTERA_CON_MATABURRO", "definition": "PORTERA CON MATABURRO"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "TIPO DE OBSTÁCULO"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}], "visible": true, "download": true, "editable": true, "metadata": "https://vanilla.geocat.net/geonetwork/srv/spa/catalog.search;jsessionid=0590273B370B98076472E1023A494182#/metadata/spacarcapaemerppmax25awmstematica20140408", "geom_type": "point", "showTable": true, "geom_style": "marker", "hasMetadata": true, "showInSearch": true}';
        $lyr->save();*/
    }
}
