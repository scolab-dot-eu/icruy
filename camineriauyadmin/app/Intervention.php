<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    public const LAYER_NAME = 'interventions';
    public const FIELD_DEF = '[{"name":"id","type":"intdecimal","label":"id","definition":"Identificador numérico"},{"name":"updated_at","type":"date","label":"ACTUALIZACIÓN DE ATRIBUTOS","definition":"FECHA EN LA QUE SE REALIZAN CAMBIOS A NIVEL DE ALGÚN ATRIBUTO"},{"name":"created_at","type":"date","label":"FECHA DE CREACIÓN","definition":"FECHA EN LA CREA EL REGISTRO"},{"name":"status","type":"string","label":"Estatus","definition":"Si hay una petición de cambios abierta sobre el registro","typeparams":"23"},{"name":"codigo_camino","type":"string","label":"Código de camino","definition":"IDENTIFICADOR DEL CAMINO SEGÚN ESPECIFICACIÓN TÉCNICA DE CODIFICACIÓN Y DEBE COINCIDIR CON EL CÓDIGO ASIGNADO A CADA CAMINO EN LA CAPA DE CAMINOS DEL MTOP","typeparams":"8"},{"name":"tipo_elem","type":"string","label":"Tipo elemento","definition":"TIPO DE ELEMENTO (ALCANTARILLA, BADÉN, etc)","typeparams":"255"},{"name":"id_elem","type":"intdecimal","label":"Identificador de elemento","definition":"IDENTIFICADOR DE OBJETO SOBRE EL QUE SE REALIZA LA INTERVENCIÓN"},{"name":"fecha_interv","type":"date","label":"Fecha intervención","definition":"FECHA EN LA QUE SE REALIZA ALGÚN TIPO DE INTERVENCIÓN"},{"name":"longitud","type":"decimal","label":"Longitud (km)","definition":"MEDIDA EN KILÓMETROS INTERVENIDOS","typeparams":"3,2"},{"name":"monto","type":"decimal","label":"Monto","definition":"MONTO DE LA INTERVENCIÓN","typeparams":"12,2","mandatory":true},{"name":"tarea","type":"stringdomain","label":"Tarea","domain":[{"code":"ME","definition":"ME: MANTENIMIENTO EXTRAORDINARIO"},{"code":"ME+AP+OA","definition":"ME+AP+OA: MANTENIMIENTO EXTRAORDINARIO+APORTE+OBRA DE ARTE"},{"code":"ME+OA","definition":"ME+OA: MANTENIMIENTO EXTRAORDINARIO+OBRA DE ARTE"},{"code":"MO","definition":"MO: MANTENIMIENTO ORDINARIO"},{"code":"MO+AP","definition":"MO+AP: MANTENIMIENTO+ORDINARIO+APORTE"},{"code":"MO+AP+OA","definition":"MO+AP+O: MANTENIMIENTO ORDINARIO+APORTE+OBRA DE ARTE"},{"code":"MO+ME","definition":"MO+ME: MANTENIMIENTO ORDINARIO+MANTENIMIENTO EXTRAORDINARIO"},{"code":"MO+ME+OA","definition":"MO+ME+OA: MANTENIMIENTO ORDINARIO+MANTENIMIENTO EXTRAORDINARIO+OBRA DE ARTE"},{"code":"MO+MF","definition":"MO+MF:MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA"},{"code":"MO+MF+AP","definition":"MO+MF+AP: MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA+APORTE"},{"code":"MO+MF+AP+OA","definition":"MO+MF+AP+OA: MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA+APORTE+OBRA DE ARTE"},{"code":"MO+OA","definition":"MO+OA: MANTENIMIENTO ORDINARIO+OBRA DE ARTE"},{"code":"OA","definition":"OA: OBRA DE ARTE"},{"code":"PE","definition":"PE: PERFILADO"},{"code":"PE+AP","definition":"PE+AP: PERFILADO+APORTE"},{"code":"TBS","definition":"TBS: TRATAMIENTO BITUMINOSO SIMPLE"},{"code":"TBD","definition":"TBD: TRATAMIENTO BITUMINOSO DOBLE"},{"code":"TBD/S","definition":"TBD/S: TRATAMIENTO BITUMINOSO DOBLE CON SELLADO"},{"code":"ImpR","definition":"ImpR: IMPRIMACIÓN"}],"definition":"TIPO DE INTERVENCIÓN"},{"name":"financiacion","type":"stringdomain","label":"Financiación","domain":[{"code":"IND","definition":"INTENDENCIA DEPARTAMENTAL"},{"code":"OPP","definition":"OPP"},{"code":"PRI","definition":"PRIVADA"},{"code":"OTR","definition":"OTROS"}],"definition":"ENTIDAD FINANCIADORA"},{"name":"forma_ejecucion","type":"stringdomain","label":"Forma de ejecución","domain":[{"code":"ADM","definition":"ADMINISTRACIÓN"},{"code":"CON","definition":"CONTRATO"},{"code":"MIX","definition":"MIXTA"}],"definition":"FORMA DE EJECUCIÓN"},{"name":"departamento","type":"stringdomain","label":"Departamento","domain":[{"code":"UYAR","definition":"ARTIGAS"},{"code":"UYCA","definition":"CANELONES"},{"code":"UYCL","definition":"CERRO LARGO"},{"code":"UYCO","definition":"COLONIA"},{"code":"UYDU","definition":"DURAZNO"},{"code":"UYFS","definition":"FLORES"},{"code":"UYFD","definition":"FLORIDA"},{"code":"UYLA","definition":"LAVALLEJA"},{"code":"UYMA","definition":"MALDONADO"},{"code":"UYMO","definition":"MONTEVIDEO"},{"code":"UYPA","definition":"PAYSANDÚ"},{"code":"UYRN","definition":"RÍO NEGRO"},{"code":"UYRV","definition":"RIVERA"},{"code":"UYRO","definition":"ROCHA"},{"code":"UYSA","definition":"SALTO"},{"code":"UYSJ","definition":"SAN JOSE"},{"code":"UYSO","definition":"SORIANO"},{"code":"UYTA","definition":"TACUAREMBÓ"},{"code":"UYTT","definition":"TREINTA Y TRES"}],"definition":"DEPARTAMENTO"}]';
    protected $table = Intervention::LAYER_NAME;
    protected $fillable = [
        'status', 'nombre', 'fecha_interv', 'departamento', 'codigo_camino', 'tipo_elem', 'id_elem',
        'monto', 'longitud', 'tarea', 'financiacion', 'forma_ejecucion', 'observaciones'
    ];
    
    /**
     * Get the changeRequests for the user.
     */
    public function partialCertificate()
    {
        return $this->hasMany('App\PartialCertificate', 'id_intervencion');
    }
    
    public function scopeOpen($query)
    {
        return $query->where('status', '!=', ChangeRequest::FEATURE_STATUS_VALIDATED);
    }
    
    /*
     * TODO
    public function scopeClosed($query)
    {
        return $query->where('status', '>=', ChangeRequest::STATUS_VALIDATED);
    }*/
    
    public function scopeValidated($query)
    {
        return $query->where('status', ChangeRequest::FEATURE_STATUS_VALIDATED);
    }
    
    
    public function scopeConsolidated($query)
    {
        return $query->where('status', '!=', ChangeRequest::FEATURE_STATUS_PENDING_CREATE);
    }
    
    public function setStatus($status) {
        $this->status = $status;
        $this->save();
    }
    
    public function department()
    {
        return $this->belongsTo('App\Department', 'departamento', 'code');
    }

    public function getFechaIntervFormattedAttribute(){
        return Carbon::parse($this->fecha_interv)->format('d/m/Y');
    }

}
