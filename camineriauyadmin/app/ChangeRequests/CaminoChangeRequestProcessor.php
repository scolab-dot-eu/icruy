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
use App\User;
use App\Http\Controllers\ChangeRequestApiController;
use Carbon\Carbon;
use App\Mail\ChangeRequestCreated;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;

class CaminoChangeRequestProcessor extends ChangeRequestProcessor
{
    
    public function getCurrentFeature($layer_name, $id) {
        //DB::enableQueryLog();
        try {
            $table_name = ChangeRequest::getTableName($layer_name);
            $feat = DB::table($table_name)
            ->where('codigo_camino', $id)
            ->first();
            //$db_log = json_encode(DB::getQueryLog());
            //Log::error($db_log);
            return $feat;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e);
        }
        return null;
    }

    public function createChangeRequest(
        string $layer,
        string $mtopOperation,
        array $properties,
        User $user,
        Geometry $geom=null) {
        $codigo_camino = array_get($properties, "codigo_camino");
        
        $feature_previous = $this->getCurrentFeature($layer, $codigo_camino);
        // la operación para el camino MTOP no es la misma que la operación en la tabla de caminos
        if ($feature_previous == null) {
            if ($mtopOperation==ChangeRequest::OPERATION_UPDATE) {
                $operation = ChangeRequest::OPERATION_CREATE;
            }
            else {
                // we don't need a ChR on cr_caminos table in this case
                return null;
            }
        }
        else {
            if (ChangeRequestProcessor::equalValues($properties, $feature_previous)) {
                // don't need to create the ChR if values are equal
                return null;
            }
            if ($feature_previous->status != ChangeRequest::FEATURE_STATUS_VALIDATED) {
                
                ChangeRequestApiController::throwPendingElementNotModifiableError();
            }
            else {
                $properties['created_at'] = $feature_previous->created_at;
                if ($mtopOperation==ChangeRequest::OPERATION_UPDATE || ($mtopOperation==ChangeRequest::OPERATION_DELETE)) {
                    $operation = $mtopOperation;
                }
                else {
                    $operation = ChangeRequest::OPERATION_UPDATE;
                }
            }
        }
        
        $changerequest = new ChangeRequest();
        if ($user->isAdmin()) {
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        }
        else {
            $changerequest->status = ChangeRequest::STATUS_PENDING;
        }
        $changerequest->layer = $layer;
        $changerequest->operation = $operation;
        $changerequest->departamento = array_get($properties, "departamento");
        $changerequest->codigo_camino = $codigo_camino;
        
        if ($operation != ChangeRequest::OPERATION_CREATE) {
            if (ChangeRequest::open()->where('layer', $layer)->where('feature_id', $codigo_camino)->count()>0) {
                /* if ($changerequest && $changerequest->requested_by!=$user) {}*/
                
                // ya existe un ChR sobre este camino
                ChangeRequestApiController::throwPendingElementNotModifiableError();
            }
            $changerequest->feature_previous = ChangeRequest::feature2geojson($feature_previous);
        }
        // validate all the fields before storing the ChR
        $feature = [];
        $feature['properties'] = $this->prepareFeature($layer, $properties, $operation);
        $feature = $this->setFeatureStatus($feature, $operation, $changerequest->status);
        $feature = $this->prepareInternalFields($feature, $operation, $changerequest->status, $feature_previous);
        $newId = $this->applyChangeRequest($layer, $operation, $feature);
        if ($newId) {
            $feature['properties']['id'] = $newId;
        }
        $changerequest->feature_id = $feature['properties']['id'];
        
        if ($user->isAdmin()) {
            $changerequest->validator()->associate($user);
        }
        
        // don't store the MTOP geom
        $the_feat = [];
        $the_feat['properties'] = $feature['properties'];
        $changerequest->feature = json_encode($the_feat);
        $user->changeRequests()->save($changerequest);
        
        if (!$user->isAdmin()) {
            try {
                $notification = new ChangeRequestCreated($changerequest);
                $notification->onQueue('email');
                $admins = Role::admins()->first()->users()->get();
                Mail::to($admins)->queue($notification);
            }
            catch(\Exception $ex) {
                Log::error($ex->getMessage());
                Log::error($ex);
            }
        }
        
        return $changerequest;
    }
}
