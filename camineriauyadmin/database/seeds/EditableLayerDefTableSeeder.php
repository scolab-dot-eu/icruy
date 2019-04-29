<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\EditableLayerDef;
use App\Intervention;
use App\Camino;

class EditableLayerDefTableSeeder extends Seeder
{
    
    protected function define_layer($name, $title, $fields, $color) {
        //error_log("Publishing layer ".$name);
        $lyr = $this->create_def($name, $title, $fields, $color);
        try {
            EditableLayerDef::createTable($lyr->name, $lyr->fields, $lyr->geom_type);
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

    protected function create_def($name, $title, $fields, $color) {
        $lyr = new EditableLayerDef();
        $lyr->name = $name;
        $lyr->title = $title;
        $lyr->geom_type = 'point';
        $lyr->fields = $fields;
        /*
         $lyr->style = '{"iconUrl": "marker-alcantarilla.png", "iconSize": [35, 41], "iconAnchor": [12, 41], "popupAnchor": [1, -34]}';
         /$lyr->geom_style = 'marker';
         * */
        $lyr->style = '{"radius": 5, "fillColor": "'.$color.'", "color": "'.$color.'", "weight": 1, "opacity": 1}';
        $lyr->geom_style = 'point';
        $lyr->metadata = '';
        $lyr->save();
        return $lyr;
    }
    
    protected function create_caminos_def() {
        $lyr = new EditableLayerDef();
        $lyr->name = 'cr_caminos';
        $lyr->title = 'Caminos';
        $lyr->geom_type = 'external:linestring';
        $lyr->fields = Camino::FIELD_DEF;
        $lyr->geom_style = 'line';
        $lyr->metadata = '';
        $lyr->save();
    }
    
    protected function create_intervenciones_def() {
        $lyr = new EditableLayerDef();
        $lyr->name = Intervention::LAYER_NAME;
        $lyr->title = 'Intervenciones';
        $lyr->geom_type = 'none';
        $lyr->fields = Intervention::FIELD_DEF;
        $lyr->metadata = '';
        $lyr->save();
    }
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $this->define_layer('cr_alcantarilla', 'Alcantarillas',
            '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8","mandatory":true},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"},{"name":"tipo_alcantarilla","type":"stringdomain","label":"Tipo de alcantarilla","domain":[{"code":"CRUCE CAÑO","definition":"CRUCE CAÑO"},{"code":"Z","definition":"Z"},{"code":"H","definition":"H"},{"code":"A","definition":"A"},{"code":"B","definition":"B"},{"code":"C","definition":"C"},{"code":"D","definition":"D"},{"code":"E","definition":"E"},{"code":"F","definition":"F"},{"code":"G","definition":"G"}],"definition":"CARACTERIZACIÓN DE ALCANTARILLAS SEGÚN TIPOS ESTABLECIDOS POR EL MTOP"},{"name":"rodadura","type":"stringdomain","label":"Rodadura","domain":[{"code":"R1","definition":"GRANULAR"},{"code":"R2","definition":"HORMIGON"},{"code":"R3","definition":"CARPETA ASFALTICA"},{"code":"R4","definition":"TRATAMIENTO BITUMINOSO"},{"code":"R5","definition":"MEJORADO"},{"code":"R6","definition":"CEMENTADO"},{"code":"R7","definition":"CEMENTADO Y TRATAMIENTO BITUMINOSO"},{"code":"R8","definition":"TERRENO NATURAL"},{"code":"R9","definition":"EMPEDRADO"},{"code":"R99","definition":"OTRO"}],"definition":"TIPO DE MATERIAL DE RECUBRIMIENTO"},{"name":"st_conservacion","type":"stringdomain","label":"Estado de conservación","domain":[{"code":"BUENO","definition":"BUENO"},{"code":"MALO","definition":"MALO"},{"code":"REGULAR","definition":"REGULAR"}],"definition":"CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "},{"name":"num_bocas","type":"intdecimal","typeparams":"2","label":"Cantidad de bocas","definition":"NÚMERO DE CONDUCTOS"},{"name":"dimensiones","type":"string","label":"Dimensiones (metros)","definition":"MEDIDA DE LOS CONDUCTOS (metros)"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO","mandatory":true}]',
            "#37af9b");
        
        $this->define_layer('cr_baden', 'Badenes',
            '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8","mandatory":true},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"},{"name":"rodadura","type":"stringdomain","label":"Rodadura","domain":[{"code":"R1","definition":"GRANULAR"},{"code":"R2","definition":"HORMIGON"},{"code":"R3","definition":"CARPETA ASFALTICA"},{"code":"R4","definition":"TRATAMIENTO BITUMINOSO"},{"code":"R5","definition":"MEJORADO"},{"code":"R6","definition":"CEMENTADO"},{"code":"R7","definition":"CEMENTADO Y TRATAMIENTO BITUMINOSO"},{"code":"R8","definition":"TERRENO NATURAL"},{"code":"R9","definition":"EMPEDRADO"},{"code":"R99","definition":"OTRO"}],"definition":"TIPO DE MATERIAL DE RECUBRIMIENTO"},{"name":"st_conservacion","type":"stringdomain","label":"Estado de conservación","domain":[{"code":"BUENO","definition":"BUENO"},{"code":"MALO","definition":"MALO"},{"code":"REGULAR","definition":"REGULAR"}],"definition":"CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "},{"name":"dimensiones","type":"string","label":"Dimensiones (metros)","definition":"MEDIDA (metros)"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO","mandatory":true}]',
            "#F08A08");
        
        $this->define_layer('cr_obstaculo', 'Obstáculos',
            '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8","mandatory":true},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"tipo","type":"stringdomain","label":"Tipo de obstáculo","domain":[{"code":"PORTERA","definition":"PORTERA"},{"code":"LOMO_DE_BURRO","definition":"LOMO DE BURRO"},{"code":"MATABURRO","definition":"MATABURRO"},{"code":"PORTERA_CON_MATABURRO","definition":"PORTERA CON MATABURRO"},{"code":"OTRO","definition":"OTRO"}],"definition":"TIPO DE OBSTÁCULO"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO","mandatory":true},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"}]',
            "#F01D08");
        
        $this->define_layer('cr_paso', 'Pasos',
            '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8","mandatory":true},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO","mandatory":true}]',
            "#EEEB0D");
        
        $this->define_layer('cr_puente', 'Puentes',
            '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8","mandatory":true},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"status","type":"string","label":"Estado","definition":"ESTADO","typeparams":"255"},{"name":"tipo_puente","type":"stringdomain","label":"Tipo de puente","domain":[{"code":"ANGOSTO","definition":"ANGOSTO"},{"code":"ANCHO_NORMAL","definition":"ANCHO NORMAL"},{"code":"VIADUCTO","definition":"VIADUCTO"},{"code":"PASO_BAJO_VIADUCTO","definition":"PASO BAJO VIADUCTO"},{"code":"OTRO","definition":"OTRO"}],"definition":"TIPO PUENTE"},{"name":"estructura","type":"stringdomain","label":"Estructura","domain":[{"code":"HORMIGON","definition":"HORMIGON"},{"code":"HIERRO","definition":"HIERRO"},{"code":"HIERRO_HORMIGON","definition":"HIERRO Y HORMIGON"},{"code":"HIERRO_MADERA","definition":"HIERRO Y MADERA"},{"code":"MADERA_HORMIGON","definition":"MADERA Y HORMIGON"},{"code":"MADERA","definition":"MADERA"},{"code":"TRONCOS","definition":"TRONCOS"},{"code":"OTRO","definition":"OTRO"}],"definition":"ESTRUCTURA"},{"name":"galibo","type":"stringdomain","label":"Galibo","domain":[{"code":"SI","definition":"SI"},{"code":"NO","definition":"NO"}],"definition":"GALIBO"},{"name":"medida_galibo","type":"decimal","label":"Medida galibo (metros)","definition":"MEDIDA GALIBO (metros)","typeparams":"3,1"},{"name":"ancho","type":"decimal","label":"Ancho (metros)","definition":"ANCHO (metros)","typeparams":"3,1"},{"name":"st_conservacion","type":"stringdomain","label":"Estado de conservación","domain":[{"code":"BUENO","definition":"BUENO"},{"code":"MALO","definition":"MALO"},{"code":"REGULAR","definition":"REGULAR"}],"definition":"CATEGORÍA DE CONSERVACIÓN PREESTABLECIDAS "},{"name":"restriccion_peso","type":"stringdomain","label":"Restricción de peso","domain":[{"code":"SI","definition":"SI"},{"code":"NO","definition":"NO"}],"definition":"RESTRICCIÓN PESO"},{"name":"carga_max","type":"decimal","label":"Carga Máxima (toneladas)","definition":"CARGA MÁXIMA (toneladas)","typeparams":"4,2"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO","mandatory":true}]',
            "#25C62F");
        
        $this->define_layer('cr_senyal', 'Señales',
            '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8","mandatory":true},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"tipo_senyal","type":"stringdomain","label":"Tipo de señal","domain":[{"code":"REGLAMENTACION","definition":"REGLAMENTACION"},{"code":"INFORMATIVA","definition":"INFORMATIVA"},{"code":"PREVENCION","definition":"PREVENCION"}],"definition":"TIPO SEÑAL"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO","mandatory":true},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"}]',
            "#084EF0");
        
        $this->create_caminos_def();
        $this->create_intervenciones_def();
    }
}
