<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\EditableLayerDef;

class EditableLayerDefTableSeeder extends Seeder
{
    
    protected function define_layer($name, $abrev, $title, $fields, $color) {
        error_log("Publishing layer ".$name);
        $WFS_URL = env('WFS_URL','');
        $lyr = new EditableLayerDef();
        $lyr->name = $name;
        $lyr->abrev = $abrev;
        $lyr->title = $title;
        $lyr->geom_type = 'point';
        $lyr->protocol = 'wfs';
        $lyr->url = $WFS_URL;
        $lyr->fields = $fields;
        /*
         $lyr->style = '{"iconUrl": "marker-alcantarilla.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
         /$lyr->geom_style = 'marker';
         * */

        $lyr->style = '{"radius": 5, "fillColor": "'.$color.'", "color": "'.$color.'", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        
        $lyr->metadata = '';
        $lyr->conf = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';
        $lyr->save();
        try {
            EditableLayerDef::createTable($lyr->name, $lyr->abrev, $lyr->fields, $lyr->geom_type);
        } catch (Exception $e) {
            error_log("Error creating layer ".$e);
            Log::error($e);
        }
        try {
            EditableLayerDef::publishLayer($lyr->name, $lyr->title);
            EditableLayerDef::publishStyle($lyr->name, $lyr->title, $color);
            EditableLayerDef::setLayerStyle($lyr->name, $lyr->name);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $this->define_layer('cr_alcantarillas', 'AL', 'Alcantarillas',
            '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador numérico"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_alcantarilla", "type": "stringdomain", "label": "Tipo de alcantarilla", "domain": [{"code": "CRUCE CAÑO", "definition": "CRUCE CAÑO"}, {"code": "Z", "definition": "Z"}, {"code": "H", "definition": "H"}, {"code": "A", "definition": "A"}, {"code": "B", "definition": "B"}, {"code": "C", "definition": "C"}, {"code": "D", "definition": "D"}, {"code": "E", "definition": "E"}, {"code": "F", "definition": "F"}, {"code": "F", "definition": "F"}], "definition": "CARACTERIZACIÓN DE ALCANTARILLAS SEGÚN TIPOS ESTABLECIDOS POR EL MTOP"}, {"name": "rodadura", "type": "stringdomain", "label": "Rodadura", "domain": [{"code": "r1", "definition": "GRANULAR"}, {"code": "R2", "definition": "HORMIGON"}, {"code": "R3", "definition": "CARPETA ASFALTICA"}, {"code": "R4", "definition": "TRATAMIENTO BITUMINOSO"}, {"code": "R5", "definition": "MEJORADO"}, {"code": "R6", "definition": "CEMENTADO"}, {"code": "R7", "definition": "CEMENTADO Y TRATAMIENTO BITUMINOSO"}, {"code": "R8", "definition": "TERRENO NATURAL"}, {"code": "R9", "definition": "EMPEDRADO"}, {"code": "R99", "definition": "OTRO"}], "definition": "TIPO DE MATERIAL DE RECUBRIMIENTO"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "cantidad_de_bocas", "type": "intdecimal", "label": "Cantidad de bocas", "definition": "NÚMERO DE CONDUCTOS"},  {"name": "dimensiones", "type": "string", "label": "Dimensiones", "definition": "MEDIDA DE LOS CONDUCTOS"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}]',
            "#686562");
        
        $this->define_layer('cr_baden', 'BA', 'Badenes',
            '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador numérico"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "rodadura", "type": "stringdomain", "label": "Rodadura", "domain": [{"code": "r1", "definition": "GRANULAR"}, {"code": "R2", "definition": "HORMIGON"}, {"code": "R3", "definition": "CARPETA ASFALTICA"}, {"code": "R4", "definition": "TRATAMIENTO BITUMINOSO"}, {"code": "R5", "definition": "MEJORADO"}, {"code": "R6", "definition": "CEMENTADO"}, {"code": "R7", "definition": "CEMENTADO Y TRATAMIENTO BITUMINOSO"}, {"code": "R8", "definition": "TERRENO NATURAL"}, {"code": "R9", "definition": "EMPEDRADO"}, {"code": "R99", "definition": "OTRO"}], "definition": "TIPO DE MATERIAL DE RECUBRIMIENTO"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "dimensiones", "type": "string", "label": "Dimensiones", "definition": "MEDIDA DE LOS CONDUCTOS"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}]',
            "#F08A08");
        
        $this->define_layer('cr_obstaculo', 'OB', 'Obstáculos',
            '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador numérico"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo", "type": "stringdomain", "label": "Tipo de obstáculo", "domain": [{"code": "PORTERA", "definition": "PORTERA"}, {"code": "LOMO_DE_BURRO", "definition": "LOMO DE BURRO"}, {"code": "MATABURRO", "definition": "MATABURRO"}, {"code": "PORTERA_CON_MATABURRO", "definition": "PORTERA CON MATABURRO"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "TIPO DE OBSTÁCULO"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"},{"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}]',
            "#F01D08");
        
        $this->define_layer('cr_paso', 'PA', 'Pasos',
            '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador numérico"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"},{"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}]',
            "#EEEB0D");
        
        $this->define_layer('cr_puente', 'PU', 'Puentes',
            '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador numérico"}, {"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_puente", "type": "stringdomain", "label": "Tipo de puente", "domain": [{"code": "ANGOSTO", "definition": "ANGOSTO"}, {"code": "ANCHO_NORMAL", "definition": "ANCHO NORMAL"}, {"code": "VIADUCTO", "definition": "VIADUCTO"}, {"code": "PASO_BAJO_VIADUCTO", "definition": "PASO BAJO VIADUCTO"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "TIPO PUENTE"}, {"name": "estructura", "type": "stringdomain", "label": "Estructura", "domain": [{"code": "HORMIGON", "definition": "HORMIGON"}, {"code": "HIERRO", "definition": "HIERRO"}, {"code": "HIERRO_HORMIGON", "definition": "HIERRO Y HORMIGON"}, {"code": "HIERRO_MADERA", "definition": "HIERRO Y MADERA"}, {"code": "MADERA_HORMIGON", "definition": "MADERA Y HORMIGON"}, {"code": "MADERA", "definition": "MADERA"}, {"code": "TRONCOS", "definition": "TRONCOS"}, {"code": "OTRO", "definition": "OTRO"}], "definition": "ESTRUCTURA"}, {"name": "galibo", "type": "stringdomain", "label": "Galibo", "domain": [{"code": "SI", "definition": "SI"}, {"code": "NO", "definition": "NO"}], "definition": "GALIBO"}, {"name": "medida_galibo", "type": "decimal", "label": "Medida galibo", "definition": "MEDIDA GALIBO", "typeparams": "2,0"}, {"name": "ancho", "type": "decimal", "label": "Ancho", "definition": "ANCHO", "typeparams": "2,0"}, {"name": "estado_de_conservacion", "type": "stringdomain", "label": "Estado de conservación", "domain": [{"code": "BUENO", "definition": "BUENO"}, {"code": "MALO", "definition": "MALO"}, {"code": "REGULAR", "definition": "REGULAR"}], "definition": "CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "}, {"name": "restriccion_peso", "type": "stringdomain", "label": "Restricción de peso", "domain": [{"code": "SI", "definition": "SI"}, {"code": "NO", "definition": "NO"}], "definition": "RESTRICCIÓN PESO"}, {"name": "carga_maxima", "type": "decimal", "label": "Carga Máxima", "definition": "CARGA MÁXIMA", "typeparams": "2,0"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"}, {"name": "status", "type": "string", "label": "Estado", "definition": "ESTADO", "typeparams": "255"}]',
            "#25C62F");
        
        $this->define_layer('cr_senyal', 'SE', 'Señales',
            '[{"name": "id", "type": "intdecimal", "label": "id", "definition": "Identificador numérico"},{"name": "updated_at", "type": "dateTime", "label": "ACTUALIZACIÓN DE ATRIBUTOS", "definition": "FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"}, {"name": "created_at", "type": "dateTime", "label": "FECHA DE CREACIÓN", "definition": "FECHA EN LA CREA EL REGISTRO"}, {"name": "codigo_camino", "type": "string", "label": "Código de camino", "definition": "IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP", "typeparams": "8"}, {"name": "tipo_senyal", "type": "stringdomain", "label": "Tipo de señal", "domain": [{"code": "REGLAMENTACION", "definition": "REGLAMENTACION"}, {"code": "INFORMATIVA", "definition": "INFORMATIVA"}, {"code": "PREVENCION", "definition": "PREVENCION"}], "definition": "TIPO SEÑAL"}, {"name": "observaciones", "type": "string", "label": "Observaciones", "definition": "ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS", "typeparams": "255"}, {"name": "departamento", "type": "string", "label": "Departamento", "definition": "DEPARTAMENTO"},{"name": "status", "type": "string", "label": "Estatus", "definition": "Si hay una petición de cambios abierta sobre el registro", "typeparams": "20"}]',
            "#084EF0");
    }
}
