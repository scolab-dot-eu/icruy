<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Camino extends Model
{
    public const LAYER_NAME = 'cr_caminos';
    public const FIELD_DEF = '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8"},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"version","type":"intdecimal","label":"Versión","definition":"Versión interna del elemento en la plataforma ICR"},{"name":"nombre","type":"string","label":"Nombre","definition":"NOMBRE O CÓDIGO ALFANUMÉRICO DEL ELEMENTO","typeparams":"255"},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"},{"name":"ancho_calzada","type":"decimal","label":"Ancho calzada (metros)","definition":"ANCHO MEDIDO EN METROS","typeparams":"2,1"},{"name":"rodadura","type":"stringdomain","label":"Rodadura","domain":[{"code":"R1","definition":"GRANULAR"},{"code":"R2","definition":"HORMIGON"},{"code":"R3","definition":"CARPETA ASFALTICA"},{"code":"R4","definition":"TRATAMIENTO BITUMINOSO"},{"code":"R5","definition":"MEJORADO"},{"code":"R6","definition":"CEMENTADO"},{"code":"R7","definition":"CEMENTADO Y TRATAMIENTO BITUMINOSO"},{"code":"R8","definition":"TERRENO NATURAL"},{"code":"R9","definition":"EMPEDRADO"},{"code":"R99","definition":"OTRO"}],"definition":"TIPO DE MATERIAL DE RECUBRIMIENTO"},{"name":"banquina","type":"boolean","label":"Banquina","definition":"SI EXISTE O NO BANQUINA"},{"name":"cordon","type":"boolean","label":"Cordón","definition":"SI EXISTE O NO CORDÓN"},{"name":"cuneta","type":"stringdomain","label":"Cuneta","domain":[{"code":"NO","definition":"NO"},{"code":"PA","definition":"PASTO"},{"code":"RE","definition":"REVESTIDA"}],"definition":"MATERIAL DE LA CUNETA O INEXISTENCIA DE LA MISMA"},{"name":"senaliz_horiz","type":"stringdomain","label":"Señalización horizontal","domain":[{"code":"NO","definition":"NO"},{"code":"EJ","definition":"EJE"},{"code":"BD","definition":"BORDE"},{"code":"EB","definition":"EJE Y BORDE"}],"definition":"UBICACIÓN DE LA SEÑAL O INEXISTENCIA DE LA MISMA"},{"name":"observaciones","type":"string","label":"Observaciones","definition":"ESPECIFICACIONES NO CONTEMPLADAS EN LOS ATRIBUTOS","typeparams":"255"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO"}]';
    protected $table = Camino::LAYER_NAME;
    
    protected $fillable = [
        'departamento', 'codigo_camino'
    ];
}
