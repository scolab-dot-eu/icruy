<?php

namespace App\ChangeRequests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use DateTime;
use App\Camino;
use App\ChangeRequest;
use App\EditableLayerDef;
use App\Intervention;
use App\MtopChangeRequest;
use App\Role;
use App\Mail\ChangeRequestCreated;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;
use App\User;
use App\Http\Controllers\ChangeRequestApiController;
use App\Helpers\Helpers;

class ChangeRequestProcessor
{
    public function createChangeRequest(
        string $layer,
        string $requestedOperation,
        array $properties,
        User $user,
        Geometry $geom=null) {
        
        $changerequest = new ChangeRequest();
        if ($user->isAdmin()) {
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        }
        else {
            $changerequest->status = ChangeRequest::STATUS_PENDING;
        }
        $changerequest->layer = $layer;
        $originalOperation = $requestedOperation;
        $changerequest->operation = $requestedOperation;
        $feature_id = array_get($properties, "id");
        $changerequest->codigo_camino = array_get($properties, "codigo_camino");
        $changerequest->departamento = array_get($properties, "departamento");
        $feature_previous = $this->getCurrentFeature($layer, $feature_id);
        if ($feature_previous) {
            if ($feature_previous->status != ChangeRequest::FEATURE_STATUS_VALIDATED) {
                if ($changerequest->operation == ChangeRequest::OPERATION_CREATE || $requestedOperation == ChangeRequest::OPERATION_DELETE) {
                    ChangeRequestApiController::throwPendingElementNotModifiableError();
                }
                else { // update operation, let's check the existing ChangeRequest & the feature version
                    if (isset($feature_previous->version) && array_get($properties, "version", -1) <= $feature_previous->version) {
                        // If proposed version is higher that current version, we assume the user is requesting an update of the ChangeRequest
                        // But we don't allow the change otherwise
                        ChangeRequestApiController::throwPendingElementNotModifiableError();
                    }
                    else {
                        $existingChangeRequest = ChangeRequest::open()->where('layer', $changerequest->layer)->where('feature_id', $feature_id)
                        ->where('requested_by_id', $user->id)->get()->first();
                        if ($existingChangeRequest==null) {
                            // existing change request was not created by current user
                            ChangeRequestApiController::throwPendingElementNotModifiableError();
                        }
                        elseif (($existingChangeRequest->operation == ChangeRequest::OPERATION_DELETE) ||
                            ($existingChangeRequest->departamento != $changerequest->departamento)){
                                ChangeRequestApiController::throwPendingElementNotModifiableError();
                        }
                        else {
                            $originalOperation = $existingChangeRequest->operation;
                            $changerequest = $existingChangeRequest;
                        }
                    }
                }
            }
            $changerequest->feature_previous = ChangeRequest::feature2geojson($feature_previous);
        }
        // get a clean feature
        $feature = [];
        if ($geom !== null) {
            $feature["type"] = "Feature";
            $feature["geometry"] = $geom->jsonSerialize()->jsonSerialize();
            //$feature = json_decode(json_encode($geom), true);
            //$feature = json_encode($geom);
        }
        else {
            $feature = [];
        }
        // validate all the fields before storing the ChR. In this case, we use the proposed operation
        $feature['properties'] = $this->prepareFeature($layer, $properties, $requestedOperation);
        // we use the changeRequest operation just in case the user is updating a pending changerequest
        $feature = $this->setFeatureStatus($feature, $originalOperation, $changerequest->status);
        $feature = $this->prepareInternalFields($feature, $requestedOperation, $changerequest->status, $feature_previous);
        $newId = null;
        if ($requestedOperation == $originalOperation) { // we don't need to apply the request if we are updating an existing one
            $newId = $this->applyChangeRequest($layer, $requestedOperation, $feature, $geom);
        }
        if ($newId) {
            $feature['properties']['id'] = $newId;
        }
        else {
            Log::debug("the feature id is: ".array_get($feature, "properties.id"));
            Log::debug("the feature id should be: ".$feature_id);
        }
        if ($user->isAdmin()) {
            $changerequest->validator()->associate($user);
        }
        $changerequest->feature_id = array_get($feature, "properties.id");
        $changerequest->feature = json_encode($feature);
        $user->changeRequests()->save($changerequest);
        
        if (!$user->isAdmin()) {
            ChangeRequestProcessor::notifyChangeRequest($user, $changerequest);
        }
        
        $response = $changerequest->toArray();
        if ($requestedOperation != $originalOperation && $originalOperation == ChangeRequest::OPERATION_CREATE
            && isset($feature['properties']['version'])) {
            // hack to allow editing creations
            $feature['properties']['version'] = $feature['properties']['version'] + 1;
        }
        $response['feature'] = $feature;
        $response['status_label'] = $changerequest->statusLabel;
        $response['operation_label'] = $changerequest->operationLabel;
        return $response;
    }
    
    
    public static function getProcessor(string $layer_name):ChangeRequestProcessor {
        $tableName = ChangeRequest::getTableName($layer_name);
        if ($tableName==Camino::LAYER_NAME) {
            return (new CaminoChangeRequestProcessor());
        }
        elseif ($tableName==Intervention::LAYER_NAME) {
            return (new InterventionChangeRequestProcessor());
        }
        else {
            return (new ChangeRequestProcessor());
        }
    }
    
    public static function notifyChangeRequest(User $user, ChangeRequest $changerequest) {
        try {
            $notification = new ChangeRequestCreated($changerequest, false);
            $notification->onQueue('email');
            $admins = Role::admins()->first()->users()->get();
            Mail::to($admins)->queue($notification);
            
            $notification = new ChangeRequestCreated($changerequest, true);
            $notification->onQueue('email');
            Mail::to($user)->queue($notification);
        }
        catch(\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex);
        }
    }
    
    /** 
     * @param string $layer_name
     * @param string $operation
     * @param string $status
     * @param array $feature
     * @param Geometry $geom
     * @return integer If a new feature was created, return the id of the new feature. Return null otherwise
     */
    public function applyChangeRequest($layer_name, $operation, $feature, $geom=null) {
        $values = array_get($feature, 'properties', []);
        $status = $values['status'];
        $table_name = ChangeRequest::getTableName($layer_name);
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            return $this->insertFeature($table_name, $values, $geom);
            /* if ($status == ChangeRequest::FEATURE_STATUS_VALIDATED) {
                ChangeRequest::historyInsert($table_name, $values);
            }*/
        }
        else {
            $id = array_get($feature, 'properties.id', null);
            if ($status == ChangeRequest::FEATURE_STATUS_VALIDATED) {
                if  ($operation==ChangeRequest::OPERATION_UPDATE) {
                    $this->updateFeature($table_name, $id, $values, $geom);
                    //ChangeRequest::historyUpdate($table_name, $values);
                }
                elseif  ($operation==ChangeRequest::OPERATION_DELETE) {
                    $this->deleteFeature($table_name, $id);
                    //ChangeRequest::historyDelete($table_name, $id);
                }
            }
            else {
                if ($operation==ChangeRequest::OPERATION_UPDATE) {
                    $this->commitFeatureStatus($table_name, $id, ChangeRequest::FEATURE_STATUS_PENDING_UPDATE);
                }
                elseif ($operation==ChangeRequest::OPERATION_DELETE) {
                    $this->commitFeatureStatus($table_name, $id, ChangeRequest::FEATURE_STATUS_PENDING_DELETE);
                }
            }
        }
        return null;
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
    
    
    public static function equalValues($values1, $values2) {
        $ignoredFields = [
            'gid'=>true,
            'id'=>true,
            'version'=>true,
            'status'=>true,
            'updated_at'=>true,
            'created_at'=>true,
            'thegeom'=>true,
            'thegeomjson'=>true,
            'changerequest'=>true,
            'mtopchangerequest'=>true,
            'origin'=>true,
            'validated_by_id'=>true,
            'created_by_id'=>true
        ];
        Log::debug("values1:");
        Log::debug(json_encode($values1));
        Log::debug("values2:");
        Log::debug(json_encode($values2));
        foreach ($values1 as $field => $value) {
            /*Log::debug("ignored1");
            Log::debug(array_get($ignoredFields, $field));
            Log::debug("values2");
            Log::debug(array_get($values2, $field));*/
            if (!array_get($ignoredFields, $field) &&
                Helpers::getValue($values2, $field)!=$value) {
                    Log::debug("diferentes: ".$field.' - '.$value." - ".Helpers::getValue($values2, $field));
                    return false;
                }
                $ignoredFields[$field] = true;
        }
        foreach ($values2 as $field => $value) {
            /*Log::debug("ignored2");
            Log::debug(array_get($ignoredFields, $field));
            Log::debug("values1");
            Log::debug(array_get($values1, $field));*/
            if (!array_get($ignoredFields, $field) &&
                (Helpers::getValue($values1, $field)!=$value)) {
                    Log::debug("diferentes: ".$field.' - '.$value." - ".Helpers::getValue($values1, $field));
                    return false;
                }
        }
        Log::debug("are equal");
        return true;
    }
    
    protected function processCreationId($id, &$errors) {
        if ($id != null) {
            $errors['feature.properties.id'] = 'La operación CREAR no puede incluir un campo id';
        }
    }

    public function prepareFeature($layer_name, $properties, $operation) {
        $values = [];
        $errors = [];
        $layer_def = EditableLayerDef::where('name', $layer_name)->first();
        Log::debug("layer_def: ".$layer_name);
        Log::debug(json_encode($layer_def));
        $fields = array_get($layer_def, 'fields', []);
        Log::debug(json_encode($fields));
        $fields_def = json_decode($fields, true);
        foreach ($properties as $field => $value) {
            $field_def = array_first($fields_def, function ($aFieldDef, $key) use ($field) {
                return (array_get($aFieldDef, 'name')===$field);
            }, false);
                if ($field_def) {
                    
                    $type = $field_def['type'];
                    if ($type=='string') {
                        $typeparams = array_get($field_def, 'typeparams', '0');
                        $maxlength = intval($typeparams);
                        if ($maxlength>0 && mb_strlen($value)>$maxlength) {
                            //$errors['feature.properties.'.$field] = '"'.$value.'" excede longitud máxima: '.$maxlength;
                            $errors[$field] = '"'.$value.'" excede longitud máxima: '.$maxlength;
                        }
                    }
                    elseif ($field_def['type']=='stringdomain') {
                        $domainPair = array_first($field_def['domain'], function ($domainPair, $key) use ($value) {
                            return (array_get($domainPair, 'code')===$value);
                        }, null
                        );
                        if ($value!=null && $domainPair==null) {
                            //$errors['feature.properties.'.$field] = 'El valor no pertenece al dominio definido';
                            $errors[$field] = 'El valor no pertenece al dominio definido';
                        }
                    }
                    elseif ($field_def['type']=='intdecimal') {
                        $typeparams = array_get($field_def, 'typeparams', '0');
                        $maxlength = intval($typeparams);
                        if ($maxlength>0 && strlen((string)$value)>$maxlength) {
                            //$errors['feature.properties.'.$field] = xcede longitud máxima: '.$maxlength;
                            $errors[$field] = '"'.$value.'" excede longitud máxima: '.$maxlength;
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
                     elseif ($field_def['type']=='decimal') {
                         $typeparams = array_get($field_def, 'typeparams', '');
                         $typeparamsList = explode(",", $typeparams);
                         if (count($typeparamsList)==2) {
                             $scale = intval($typeparamsList[0]);
                             $precision = intval($typeparamsList[1]);
                             $maxintlength = ($scale - $precision);
                         }
                         elseif (count($typeparamsList)==1) {
                             $maxintlength = intval($typeparams);
                         }
                         else {
                             $maxintlength = -1;
                         }
                         $intPart = intval($value);
                         if ($maxintlength>-1 && strlen((string)$intPart)>$maxintlength) {
                             //$errors['feature.properties.'.$field] = '"'.$value.'" excede longitud máxima de campo: '.$maxintlength;
                             $errors[$field] = '"'.$value.'" excede longitud máxima de campo: '.$maxintlength;
                         }
                         // FIXME: comprobar también parte decimal
                     }
                     /*
                     elseif ($field_def['type']=='boolean') {
                     //FIXME
                     }*/
                    $values[$field] = $value;
                }
        }
        Log::debug(json_encode($errors));
        $id = array_get($properties, 'id', null);
        $department = array_get($properties, 'departamento', null);
        $codigo_camino = array_get($properties, 'codigo_camino', null);
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            $this->processCreationId($id, $errors);
        }
        else {
            $table_name = ChangeRequest::getTableName($layer_name);
            if (!ChangeRequestProcessor::checkFeatureExists($table_name, $id, $department)) {
                $errors['feature.properties.id'] = __('Registro no encontrado para la capa, el departamento y el id proporcionados: '.$table_name." - ".$id." - ".$department);
            }
        }
        if (!ChangeRequest::comprobarEstructuraCodigoCamino($codigo_camino, $department)) {
            $errors['feature.properties.codigo_camino'] = __('El código de camino no es válido');
        }
        if (count($errors)>0) {
            $error = \Illuminate\Validation\ValidationException::withMessages($errors);
            throw $error;
        }
        return $values;
    }
    
    public function setFeatureStatus($feature, $operation, $changeRequestStatus) {
        if ($changeRequestStatus==ChangeRequest::STATUS_VALIDATED) {
            $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_VALIDATED;
        }
        elseif ($operation==ChangeRequest::OPERATION_CREATE) {
            $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_PENDING_CREATE;
        }
        elseif ($operation==ChangeRequest::OPERATION_UPDATE) {
            $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_PENDING_UPDATE;
        }
        elseif ($operation==ChangeRequest::OPERATION_UPDATE) {
            $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_PENDING_DELETE;
        }
        return $feature;
    }
    
    public function prepareInternalFields($feature, $operation, $changeRequestStatus, $previousFeature=null) {
        //$feature['properties']['origin'] = ChangeRequest::FEATURE_ORIGIN_ICRWEB;
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            $feature['properties']['version'] = 1;
            $feature['properties']['created_at'] = date('Y-m-d');
            $feature['properties']['updated_at'] = date('Y-m-d');
            unset($feature['properties']['id']);
        }
        elseif ($operation==ChangeRequest::OPERATION_UPDATE) {
            unset($feature['properties']['created_at']);
            $feature['properties']['created_at'] = $previousFeature->created_at;
            $feature['properties']['updated_at'] = date('Y-m-d');
            if (isset($previousFeature->version)) {
                $feature['properties']['version'] = $previousFeature->version + 1;
            }
        }
        return $feature;
    }
    
    public static function prepareGeom($geom) {
        $raw_expression = "ST_GeomFromText('".$geom->toWKT()."')";
        return DB::raw($raw_expression);
    }
    
    protected static function checkFeatureExists($table_name, $id, $department) {
        if ($id!=null && $department!=null) {
            return (DB::table($table_name)->where([['id', '=', $id],['departamento', '=', $department]])->count()>0);
        }
        return false;
    }
    
    
    public function getCurrentFeature($layer_name, $id) {
        return ChangeRequest::getCurrentFeature($layer_name, $id);
    }
    
    public function insertFeature($table_name, &$values_array, $geom=null) {
        try {
            //DB::enableQueryLog();
            if ($geom !== null) {
                $values_array['thegeom'] = ChangeRequestProcessor::prepareGeom($geom);
            }
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
    
    protected function updateFeature($table_name, $id, &$values_array, $geom=null) {
        try {
            if ($geom !== null) {
                $values_array['thegeom'] = ChangeRequestProcessor::prepareGeom($geom);
            }
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
    
    
    public function commitFeatureStatus($table_name, $id, $status) {
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
    
    
    public function deleteFeature($table_name, $id) {
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
    
    
    private function getNextFeatureStatus($table_name, $feature_id, $featureStatus, $changeRequestStatus) {
        if ($changeRequestStatus==ChangeRequest::STATUS_VALIDATED) {
            if ($table_name==Camino::LAYER_NAME &&
                    MtopChangeRequest::open()->where('feature_id', $feature_id)->count()>0) {
                return $featureStatus;
            }
            else {
                return ChangeRequest::FEATURE_STATUS_VALIDATED;
            }
        }
        else {
            return $featureStatus;
        }
    }
    
    /**
     * Updates the required fields to reflect the proposed status
     * 
     * @param array $values
     * @param string $newStatus
     */
    protected function updateFeatureStatusFields(array &$values, string $newStatus) {
        $values['status'] = $newStatus;
    }
    
    protected function getGeometryInstance(array $feature) {
        return Geometry::fromJson(json_encode($feature));
    }
    
    /**
     * Aplica de forma definitiva los cambios de una petición de cambios.
     *  
     * @param ChangeRequest $changerequest
     * @param $user
     */
    public function setValidated(ChangeRequest $changerequest, $user) {
        DB::transaction(function () use ($changerequest, $user) {
            $table_name = ChangeRequest::getTableName($changerequest->layer);
            if ($changerequest->operation == ChangeRequest::OPERATION_DELETE) {
                $this->deleteFeature($table_name, $changerequest->feature_id);
                //ChangeRequest::historyDelete($table_name, $id);
            }
            elseif ($changerequest->operation == ChangeRequest::OPERATION_CREATE) {
                $this->commitFeatureStatus($table_name, $changerequest->feature_id,
                    ChangeRequest::FEATURE_STATUS_VALIDATED);
                if ($changerequest->feature == null) {
                    // los changerequests creados en la carga masiva tienen el campo feature vacío
                    $featureObj = $this->getCurrentFeature($changerequest->layer, $changerequest->feature_id);
                    $feature = ChangeRequest::feature2array($featureObj);
                    if ($feature) {
                        $changerequest->feature = json_encode($feature);
                    }
                }
                else {
                    $feature = json_decode($changerequest->feature, true);
                }
                //$values = array_get($feature, 'properties', []);
                //$geom = $this->getGeometryInstance($feature);
                //ChangeRequest::historyInsert($table_name, $values);
            }
            else {
                $feature = json_decode($changerequest->feature, true);
                $values = array_get($feature, 'properties', []);
                $this->updateFeatureStatusFields($values, ChangeRequest::FEATURE_STATUS_VALIDATED);
                $geom = $this->getGeometryInstance($feature);
                $this->updateFeature($table_name, $changerequest->feature_id, $values, $geom);
                //ChangeRequest::historyUpdate($table_name, $values);
            }
            
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
            $changerequest->validator()->associate($user);
            $changerequest->save();
        });
    }
    
    public function setRejected(ChangeRequest $changerequest, $user) {
        DB::transaction(function () use ($changerequest, $user) {
            $id = $changerequest->feature_id;
            $table_name = ChangeRequest::getTableName($changerequest->layer);
            if ($changerequest->operation == ChangeRequest::OPERATION_CREATE) {
                if ($changerequest->feature == null) {
                    // los changerequests creados en la carga vacía tienen el campo feature vacío
                    $featureObj = $this->getCurrentFeature($changerequest->layer, $changerequest->feature_id);
                    $changerequest->feature = ChangeRequest::feature2geojson($featureObj);
                }
                $this->deleteFeature($table_name, $id);
            }
            else {
                $this->commitFeatureStatus($table_name, $changerequest->feature_id,
                    ChangeRequest::FEATURE_STATUS_VALIDATED);
            }
            
            $changerequest->status = ChangeRequest::STATUS_REJECTED;
            $changerequest->validator()->associate($user);
            $changerequest->save();
        });
    }
    
    public function setCancelled(ChangeRequest $changerequest, $user) {
        DB::transaction(function () use ($changerequest, $user) {
            $id = $changerequest->feature_id;
            $table_name = ChangeRequest::getTableName($changerequest->layer);
            if ($changerequest->operation == ChangeRequest::OPERATION_CREATE) {
                $this->deleteFeature($table_name, $id);
            }
            else {
                $this->commitFeatureStatus($table_name, $changerequest->feature_id,
                    ChangeRequest::FEATURE_STATUS_VALIDATED);
            }
            
            $changerequest->status = ChangeRequest::STATUS_CANCELLED;
            $changerequest->save();
        });
    }
}
