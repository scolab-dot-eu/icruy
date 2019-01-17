<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;

class ChangeRequest extends Model
{
    protected $table = 'changerequests';
    protected $fillable = [
    ];
    
    /**
     * Status from 0 to 9 are considered to be OPEN change requests
     * @var integer
     */
    const STATUS_PENDING = 0;
    const STATUS_USERINFO = 1;
    const STATUS_ADMININFO = 2;
    /**
     * Status from 10 are considered to be CLOSED change requests
     * 
     * @var integer
     */
    const STATUS_VALIDATED = 10;
    const STATUS_REJECTED = 11;
    const STATUS_CANCELLED = 12;
    const STATUS_CODENAMES = [
        'CANCELADO' => ChangeRequest::STATUS_CANCELLED,
        'VALIDADO' => ChangeRequest::STATUS_VALIDATED,
        'RECHAZADO' => ChangeRequest::STATUS_REJECTED,
        'PENDIENTE' => ChangeRequest::STATUS_PENDING,
        'INFOUSUARIO' => ChangeRequest::STATUS_USERINFO,
        'INFOADMIN' => ChangeRequest::STATUS_USERINFO
    ];
    
    public static $STATUS_LABELS = [
        ChangeRequest::STATUS_PENDING => 'Pendiente',
        ChangeRequest::STATUS_USERINFO => 'Información requerida al usuario',
        ChangeRequest::STATUS_ADMININFO => 'Información requerida al admin.',
        ChangeRequest::STATUS_VALIDATED => 'Validado',
        ChangeRequest::STATUS_REJECTED => 'Rechazado',
        ChangeRequest::STATUS_CANCELLED => 'Cancelado'
    ];
    
    /**
     * We use a simplified, textual status for features. Edition will be blocked
     * while the feature status is peding.
     * 
     * @var string
     */
    const FEATURE_STATUS_PENDING_CREATE = 'PENDIENTE:CREACIÓN';
    const FEATURE_STATUS_PENDING_UPDATE = 'PENDIENTE:ACTUALIZACIÓN';
    const FEATURE_STATUS_PENDING_DELETE = 'PENDIENTE:BORRADO';
    const FEATURE_STATUS_VALIDATED = 'VALIDADO';
    
    const OPERATION_CREATE = 'create';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';
    
    const FEATURE_ORIGIN_ICRWEB = 'icrweb';
    const FEATURE_ORIGIN_BATCHLOAD = 'batchload';
    
    public static $OPERATION_LABELS = [
        ChangeRequest::OPERATION_CREATE => 'Creación',
        ChangeRequest::OPERATION_UPDATE => 'Modificación',
        ChangeRequest::OPERATION_DELETE => 'Borrado',
    ];
    
    const MAX_DATETIME = '9999-12-31 23:59:59';

    
    public function author()
    {
        return $this->belongsTo('App\User', 'requested_by_id');
    }
    
    public function validator()
    {
        return $this->belongsTo('App\User', 'validated_by_id');
    }

    public function comments()
    {
        return $this->hasMany('App\ChangeRequestComment');
    }
    
    public function scopeOpen($query)
    {
        return $query->where('status', '<', ChangeRequest::STATUS_VALIDATED);
    }
    
    public function scopeClosed($query)
    {
        return $query->where('status', '>=', ChangeRequest::STATUS_VALIDATED);
    }
    
    public function getStatusLabelAttribute(){
        return ChangeRequest::$STATUS_LABELS[$this->status];
    }
    
    public function getOperationLabelAttribute(){
        return ChangeRequest::$OPERATION_LABELS[$this->operation];
    }
    
    public function getIsOpenAttribute(){
        return ($this->status < ChangeRequest::STATUS_VALIDATED);
    }
    
    public function getIsClosedAttribute(){
        return ($this->status >= ChangeRequest::STATUS_VALIDATED);
    }
    
    public function getCreatedAtFormattedAttribute(){
        return Carbon::parse($this->created_at)->format('d/m/Y');
    }
    public function getUpdatedAtFormattedAttribute(){
        return Carbon::parse($this->updated_at)->format('d/m/Y');
    }
    
    public static function applyValidatedChangeRequest($layer_name, $operation, &$feature, $geom) {
        $values = array_get($feature, 'properties', []);
        $table_name = ChangeRequest::getTableName($layer_name);
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            $feature['properties']['id'] = ChangeRequest::insertFeature($table_name, $values, $geom);
            //ChangeRequest::historyInsert($table_name, $values);
        }
        else {
            $id = array_get($feature, 'properties.id', null);
            /*
             * No es necesaria la comprobación si no es posible crear una changerequest sobre una feature
             * que está en estado pending, puesto que ya se comprobó en el prepareFeature
             * 
             * 
             * $department = array_get($feature, 'properties.departamento', null);
            if (!ChangeRequest::checkFeatureExists($table_name, $id, $department)) {
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'feature' => [__('Registro no encontrado para la capa, el departamento y el id proporcionados')],
                ]);
                throw $error;
            }
            */
            if  ($operation==ChangeRequest::OPERATION_UPDATE) {
                ChangeRequest::updateFeature($table_name, $id, $values, $geom);
                //ChangeRequest::historyUpdate($table_name, $values);
            }
            elseif  ($operation==ChangeRequest::OPERATION_DELETE) {
                ChangeRequest::deleteFeature($table_name, $id);
                //ChangeRequest::historyDelete($table_name, $id);
            }
        }
        $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_VALIDATED;
    }
    
    /*
    public static function historyInsert($table_name, $values_array) {
        $values_array['feat_id'] = $values_array['id'];
        $values_array['valid_from'] = date('Y-m-d H:i:s');
        $values_array['valid_to'] = ChangeRequest::MAX_DATETIME;
        unset($values_array['id']);
        unset($values_array['status']);
        $historyTablename = EditableLayerDef::getHistoricTableName($table_name);
        return DB::table($historyTablename)->insert($values_array);
    }
    
    public static function historyUpdate($table_name, $values_array) {
        $historyTablename = EditableLayerDef::getHistoricTableName($table_name);
        $currentDate = date('Y-m-d H:i:s');
        DB::table($historyTablename)->where('feat_id', '=', $values_array['id'])
                ->where('valid_to', '>', $currentDate)
                ->update(['valid_to' => $currentDate]);
        $values_array['feat_id'] = $values_array['id'];
        $values_array['valid_from'] = $currentDate;
        $values_array['valid_to'] = ChangeRequest::MAX_DATETIME;
        unset($values_array['id']);
        unset($values_array['status']);
        return DB::table($historyTablename)->insert($values_array);
    }
    
    public static function historyDelete($table_name, $id) {
        $historyTablename = EditableLayerDef::getHistoricTableName($table_name);
        $currentDate = date('Y-m-d H:i:s');
        DB::table($historyTablename)->where('feat_id', '=', $id)
                ->where('valid_to', '>', $currentDate)
                ->update(['valid_to' => $currentDate]);
    }*/
    
    /**
     * Marca la feature como pendiente en todos los casos, y la crea en el caso de
     * una operación CREATE.
     * 
     * @param unknown $layer_name
     * @param unknown $operation
     * @param unknown $feature
     * @param unknown $geom
     */
    public static function applyPendingChangeRequest($layer_name, $operation, &$feature, $geom) {
        $values = array_get($feature, 'properties', []);
        $table_name = ChangeRequest::getTableName($layer_name);
        
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            $feature['properties']['id'] = ChangeRequest::insertFeature($table_name, $values, $geom, ChangeRequest::FEATURE_STATUS_PENDING_CREATE);
            $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_PENDING_CREATE;
        }
        else {
            $id = array_get($feature, 'properties.id', null);
            /*
             * No es necesaria la comprobación si no es posible crear una changerequest sobre una feature
             * que está en estado pending, puesto que ya se comprobó en el prepareFeature
             *
             * 
            $department = array_get($feature, 'properties.departamento', null);
            if (!ChangeRequest::checkFeatureExists($table_name, $id, $department)) {
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'feature' => [__('Registro no encontrado para la capa, el departamento y el id proporcionados')],
                ]);
                throw $error;
            }*/
            if ($operation==ChangeRequest::OPERATION_UPDATE) {
                $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_PENDING_UPDATE;
                ChangeRequest::setFeatureStatus($table_name, $id, ChangeRequest::FEATURE_STATUS_PENDING_UPDATE);
            }
            elseif ($operation==ChangeRequest::OPERATION_DELETE) {
                $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_PENDING_DELETE;
                ChangeRequest::setFeatureStatus($table_name, $id, ChangeRequest::FEATURE_STATUS_PENDING_DELETE);
            }
        }
    }
    
    public static function prepareFeature($layer_name, &$feature, $operation) {
        $values = [];
        $errors = [];
        $layer_def = EditableLayerDef::where('name', $layer_name)->first();
        
        $fields_def = json_decode(array_get($layer_def, 'fields', []), true);
        foreach ($feature['properties'] as $field => $value) {
            $field_def = array_first($fields_def, function ($aFieldDef, $key) use ($field) {
                return (array_get($aFieldDef, 'name')===$field);
            }, false);
                if ($field_def) {
                    
                    $type = $field_def['type'];
                    if ($type=='string') {
                        $typeparams = array_get($field_def, 'typeparams', '0');
                        $maxlength = intval($typeparams);
                        if ($maxlength>0 && strlen($value)>$maxlength) {
                            $errors['feature.properties.'.$field] = 'Excede longitud máxima: '.$maxlength;
                        }
                    }
                    elseif ($field_def['type']=='stringdomain') {
                        $domainPair = array_first($field_def['domain'], function ($domainPair, $key) use ($value) {
                            return (array_get($domainPair, 'code')===$value);
                        }, null
                        );
                            if ($domainPair==null) {
                                $errors['feature.properties.'.$field] = 'El valor no pertenece al dominio definido';
                            }
                    }
                    elseif ($field_def['type']=='intdecimal') {
                        $typeparams = array_get($field_def, 'typeparams', '0');
                        $maxlength = intval($typeparams);
                        if ($maxlength>0 && strlen((string)$value)>$maxlength) {
                            $errors['feature.properties.'.$field] = 'Excede longitud máxima: '.$maxlength;
                        }
                    }
                    elseif ($field_def['type']=='date') {
                        $date = DateTime::createFromFormat('Y-m-d', $value);
                        if ($date==false) {
                            // FIXME: should we fail??
                            $value = null;
                        }
                    }
                    elseif ($field_def['type']=='dateTime') {
                        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                        if ($dateTime==false) {
                            // FIXME: should we fail??
                            $value = null;
                        }
                    }
                    /*
                     elseif ($field_def['type']=='decimal') {
                     //FIXME
                     }
                     elseif ($field_def['type']=='boolean') {
                     //FIXME
                     }*/
                    $values[$field] = $value;
                }
        }
        $id = array_get($feature, 'properties.id', null);
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            if ($id != null) {
                $errors['feature.properties.id'] = 'La operación CREAR no puede incluir un campo id';
            }
            $values['updated_at'] = date('Y-m-d H:i:s');
            $values['created_at'] = date('Y-m-d H:i:s');
        }
        else {
            $department = array_get($feature, 'properties.departamento', null);
            $table_name = ChangeRequest::getTableName($layer_name);
            if (!ChangeRequest::checkFeatureExists($table_name, $id, $department)) {
                $errors['feature.properties.id'] = __('Registro no encontrado para la capa, el departamento y el id proporcionados');
            }
            if ($operation==ChangeRequest::OPERATION_UPDATE) {
                $values['updated_at'] = date('Y-m-d H:i:s');
            }
        }
        if (count($errors)>0) {
            $error = \Illuminate\Validation\ValidationException::withMessages($errors);
            throw $error;
        }
        $feature['properties'] = $values;
    }
    
    public static function prepareGeom($geom) {
        $raw_expression = "ST_GeomFromText('".$geom->toWKT()."')";
        return DB::raw($raw_expression);
    }
    
    protected static function getTableName($layer_name) {
        $layer_parts = explode(":", $layer_name);
        
        if (count($layer_parts)==2) {
            return $layer_parts[1];
        }
        else {
            return $layer_parts[0];
        }
    }
    protected static function getFeatureId($id) {
        if (is_numeric($id)) {
            return intval($id);
        }
        elseif (! is_null($id)) {
            $idstr = ChangeRequest::getTableName($id);
            if (is_numeric($idstr)) {
                return intval($idstr);
            }
        }
        return -1;
    }
    
    public static function getCurrentFeature($layer_name, $id) {
        //DB::enableQueryLog();
        try {
            $table_name = ChangeRequest::getTableName($layer_name);
            $feat = DB::table($table_name)
                ->select(DB::raw('*, ST_AsGeoJSON(thegeom) as thegeomjson'))
                ->where('id', $id)
                ->first();
            //$db_log = json_encode(DB::getQueryLog());
            //Log::error($db_log);
            return $feat;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e);
        }
        return null;
    }
    
    public static function feature2array($feature) {
        if ($feature) {
            $the_feat = json_decode($feature-> thegeomjson, true);
            $the_feat['properties'] = [];
            foreach ($feature as $key => $value) {
                if ($key != 'thegeom' && $key != 'thegeomjson') {
                    $the_feat['properties'][$key] = $value;
                }
            }
            return $the_feat;
        }
        return null;
    }
    
    public static function feature2geojson($feature) {
        if ($feature!==null) {
            return json_encode(ChangeRequest::feature2array($feature));
        }
        return null;
    }
    
    protected static function checkFeatureExists($table_name, $id, $department) {
        if ($id!=null && $department!=null) {
            return (DB::table($table_name)->where([['id', '=', $id],['departamento', '=', $department]])->count()>0);
        }
        return false;
    }
    
    public static function insertFeature($table_name, &$values_array, $geom, $status = ChangeRequest::FEATURE_STATUS_VALIDATED) {
        try {
            //DB::enableQueryLog();
            //unset($values_array['cod_elem']);
            $values_array['origin'] = ChangeRequest::FEATURE_ORIGIN_ICRWEB;
            $values_array['updated_at'] = date('Y-m-d H:i:s');
            $values_array['created_at'] = date('Y-m-d H:i:s');
            $values_array['status'] = $status;
            $values_array['thegeom'] = ChangeRequest::prepareGeom($geom);
            $values_array['id'] = DB::table($table_name)->insertGetId($values_array);
            return $values_array['id'];
        } catch (\Illuminate\Database\QueryException $e) {
            error_log('query');
            //$db_log = json_encode(DB::getQueryLog());
            //Log::error($db_log);
            Log::error($e);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'feature' => [__('La petición contiene datos inválidos en la estructura o el dominio del registro')]
            ]);
            throw $error;
        }
    }
    
    protected static function updateFeature($table_name, $id, &$values_array, $geom) {
        try {
            // enforce timestamps in the server side
            unset($values_array['created_at']);
            //unset($values_array['cod_elem']);
            unset($values_array['origin']);
            unset($values_array['id']);
            $values_array['updated_at'] = date('Y-m-d H:i:s');
            $values_array['status'] = ChangeRequest::FEATURE_STATUS_VALIDATED;
            $values_array['thegeom'] = ChangeRequest::prepareGeom($geom);
            DB::table($table_name)
            ->where('id', '=', $id)
            ->update($values_array);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'feature' => [__('La petición contiene datos inválidos en la estructura o el dominio del registro')],
            ]);
            throw $error;
        }
    }
    
    
    public static function setFeatureStatus($table_name, $id, $status) {
        try {
            $values_array = [];
            $values_array['status'] = $status;
            DB::table($table_name)
            ->where('id', '=', $id)
            ->update($values_array);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'feature' => [__('La petición contiene datos inválidos en la estructura o el dominio del registro')],
            ]);
            throw $error;
        }
    }
    
    
    protected static function deleteFeature($table_name, $id) {
        try {
            DB::table($table_name)
            ->where('id', '=', $id)
            ->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'feature' => [__('La petición contiene datos inválidos en la estructura o el dominio del registro')],
            ]);
            throw $error;
        }
    }
    
    /**
     * Aplica de forma definitiva los cambios de una petición de cambios.
     *  
     * @param ChangeRequest $changerequest
     * @param unknown $user
     */
    protected function setValidated(ChangeRequest $changerequest, $user) {
        $table_name = ChangeRequest::getTableName($changerequest->layer);
        if ($changerequest->operation == ChangeRequest::OPERATION_DELETE) {
            $id = $changerequest->feature_id;
            ChangeRequest::deleteFeature($table_name, $id);
            //ChangeRequest::historyDelete($table_name, $id);
        }
        elseif ($changerequest->operation == ChangeRequest::OPERATION_CREATE) {
            ChangeRequest::setFeatureStatus($table_name, $changerequest->feature_id,
                ChangeRequest::FEATURE_STATUS_VALIDATED);
            if ($changerequest->feature == null) {
                // los changerequests creados en la carga vacía tienen el campo feature vacío
                $featureObj = ChangeRequest::getCurrentFeature($changerequest->layer, $changerequest->feature_id);
                $feature = ChangeRequest::feature2array($featureObj);
                if ($feature) {
                    $changerequest->feature = json_encode($feature);
                }
            }
            else {
                $feature = json_decode($changerequest->feature, true);
            }
            $values = array_get($feature, 'properties', []);
            $geom = Geometry::fromJson(json_encode($feature));
            //ChangeRequest::historyInsert($table_name, $values);
        }
        else {
            $feature = json_decode($changerequest->feature, true);
            $values = array_get($feature, 'properties', []);
            $geom = Geometry::fromJson(json_encode($feature));
            ChangeRequest::updateFeature($table_name, $id, $values, $geom);
            //ChangeRequest::historyUpdate($table_name, $values);
        }
        
        $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        $changerequest->validator()->associate($user);
        $changerequest->save();
    }
    protected function setRejected(ChangeRequest $changerequest, $user) {
        $id = $changerequest->feature_id;
        $table_name = ChangeRequest::getTableName($changerequest->layer);
        if ($changerequest->operation == ChangeRequest::OPERATION_CREATE) {
            if ($changerequest->feature == null) {
                // los changerequests creados en la carga vacía tienen el campo feature vacío
                $featureObj = ChangeRequest::getCurrentFeature($changerequest->layer, $changerequest->feature_id);
                $changerequest->feature = ChangeRequest::feature2geojson($featureObj);
            }
            ChangeRequest::deleteFeature($table_name, $id);
        }
        else {
            ChangeRequest::setFeatureStatus($table_name, $changerequest->feature_id,
                ChangeRequest::FEATURE_STATUS_VALIDATED);
        }
        
        $changerequest->status = ChangeRequest::STATUS_REJECTED;
        $changerequest->validator()->associate($user);
        $changerequest->save();
    }
    
    protected function setCancelled(ChangeRequest $changerequest, $user) {
        $id = $changerequest->feature_id;
        $table_name = ChangeRequest::getTableName($changerequest->layer);
        if ($changerequest->operation == ChangeRequest::OPERATION_CREATE) {
            ChangeRequest::deleteFeature($table_name, $id);
        }
        else {
            ChangeRequest::setFeatureStatus($table_name, $changerequest->feature_id,
                ChangeRequest::FEATURE_STATUS_VALIDATED);
        }
        
        $changerequest->status = ChangeRequest::STATUS_CANCELLED;
        $changerequest->save();
    }
}
