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
use App\Role;
use App\Http\Controllers\ChangeRequestApiController;
use App\Mail\ChangeRequestCreated;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;

class InterventionChangeRequestProcessor extends ChangeRequestProcessor
{
    /*
    protected function processCreationId($id, &$errors) {
        // nothing to check, insertion is alreday done
    }
    */
    protected function getGeometryInstance(array $feature) {
        return null;
    }
    
    
    public function insertFeature($table_name, &$values_array, $geom=null) {
        $intervention = Intervention::create($values_array);
        return $intervention->id;
        // nothing to do, we insert from the controller using the model
    }
    /*
    protected function updateFeatureStatusFields(array &$values, string $newStatus) {
        if ($newStatus==ChangeRequest::FEATURE_STATUS_VALIDATED) {
            $values['status'] = ChangeRequest::STATUS_VALIDATED;
        }
    }*/
    
    public function getCurrentFeature($layer_name, $id) {
        //DB::enableQueryLog();
        try {
            $table_name = ChangeRequest::getTableName($layer_name);
            $feat = DB::table($table_name)
            ->select(DB::raw('*'))
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
}
