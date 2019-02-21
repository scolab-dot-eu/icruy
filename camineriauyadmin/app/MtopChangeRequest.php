<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ViewerConfigApiController;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MtopChangeRequest extends Model
{
    protected $table = 'mtopchangerequests';
    
    protected $fillable = [
    ];
    
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
        return $this->hasMany('App\MtopChangeRequestComment');
    }
    
    
    public function scopeOpen($query)
    {
        return $query->where('status', '<', ChangeRequest::STATUS_VALIDATED);
    }
    
    public function scopeClosed($query)
    {
        return $query->where('status', '>=', ChangeRequest::STATUS_VALIDATED);
    }
    
    public function getIsOpenAttribute(){
        return ($this->status < ChangeRequest::STATUS_VALIDATED);
    }
    
    public function getIsClosedAttribute(){
        return ($this->status >= ChangeRequest::STATUS_VALIDATED);
    }
    
    
    public function getStatusLabelAttribute(){
        return ChangeRequest::$STATUS_LABELS[$this->status];
    }
    
    public function getOperationLabelAttribute(){
        Log::debug('cmi label0:'.json_encode($this));
        Log::debug('cmi label:'.$this->operation);
        return ChangeRequest::$OPERATION_LABELS[$this->operation];
    }
    
    public function getCreatedAtFormattedAttribute(){
        return Carbon::parse($this->created_at)->format('d/m/Y');
    }
    /**
     *
     * @return string
     */
    public function getUpdatedAtFormattedAttribute(){
        return Carbon::parse($this->updated_at)->format('d/m/Y');
    }
    
    public static function getCurrentFeature($layer_name, $id) {
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
    
    public static function feature2array($feature) {
        if ($feature) {
            $the_feat = [];
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
    
    public static function feature2json($feature) {
        if ($feature!==null) {
            return json_encode(MtopChangeRequest::feature2array($feature));
        }
        return null;
    }
    
    
    public static function applyValidatedChangeRequest($layer_name, $operation, &$feature) {
        $values = array_get($feature, 'properties', []);
        $table_name = ChangeRequest::getTableName($layer_name);
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            $feature['properties']['id'] = ChangeRequest::insertFeature($table_name, $values);
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
                ChangeRequest::updateFeature($table_name, $id, $values);
                //ChangeRequest::historyUpdate($table_name, $values);
            }
            elseif  ($operation==ChangeRequest::OPERATION_DELETE) {
                ChangeRequest::deleteFeature($table_name, $id);
                //ChangeRequest::historyDelete($table_name, $id);
            }
        }
        $feature['properties']['status'] = ChangeRequest::FEATURE_STATUS_VALIDATED;
    }
    
    /**
     * Aplica de forma definitiva los cambios de una petición de cambios.
     *
     * @param ChangeRequest $changerequest
     * @param $user
     */
    protected function setValidated(MtopChangeRequest $changerequest, $user) {
        $feature_validated = MtopChangeRequest::getCurrentMtopFeature($changerequest->departamento, $changerequest->codigo_camino, $changerequest->feature_id);
        /*$old_properties = [];
        $feature_validated['properties'] = [];*/
        Log::info("feature_previous:");
        Log::info(json_encode($feature_validated));
        //dd($feature_validated);
        if ($feature_validated!==null) {
            $changerequest->feature_validated = json_encode($feature_validated);
        }
        //$changerequest->feature_validated = json_encode(MtopChangeRequest::getCurrentFeature($changerequest->departamento, $changerequest->codigo_camino, $changerequest->feature_id));
        $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        $changerequest->validator()->associate($user);
        $changerequest->save();
    }
    
    protected function setRejected(MtopChangeRequest $changerequest, $user) {
        $changerequest->status = ChangeRequest::STATUS_REJECTED;
        $changerequest->validator()->associate($user);
        $changerequest->save();
    }
    
    protected function setCancelled(MtopChangeRequest $changerequest, $user) {
        $changerequest->status = ChangeRequest::STATUS_CANCELLED;
        $changerequest->save();
    }
    
    
    public static function getCurrentMtopFeature($departamento, $codigo_camino, $gid) {
        //http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wfs?service=WFS&version=1.0.0&request=getFeature&typeName=caminerias_intendencias:v_camineria_cerro_largo&outputFormat=application/json&cql_filter=codigo='UYCL0158'
        //http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wfs?service=WFS&version=1.0.0&request=getFeature&typeName=caminerias_intendencias:v_camineria_cerro_largo&outputFormat=application/json&cql_filter=gid=%27280619%27
        $client = new Client();
        $dep = Department::where('code', $departamento)->first();
        $camineria_wfs_url = env('CAMINERIA_WMS_URL', ViewerConfigApiController::CAMINERIA_DEFAULT_WFS_URL);
        if ($gid!=null) {
            $url = $camineria_wfs_url . "?service=WFS&version=1.0.0&request=getFeature&typeName=".$dep->layer_name."&outputFormat=application/json&cql_filter=gid='".$gid."'";
            //$url = $camineria_wfs_url . "?service=WFS&version=1.0.0&request=getFeature&typeName=".$dep->layer_name."&outputFormat=application/json&cql_filter=gid='".$gid."'%20AND%20"."codigo='".$codigo_camino."'";
        }
        else {
            $url = $camineria_wfs_url . "?service=WFS&version=1.0.0&request=getFeature&typeName=".$dep->layer_name."&outputFormat=application/json&cql_filter=codigo='".$codigo_camino."'";
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept'     => 'application/json',
                ],
                'connect_timeout' => 5
            ]);
            if ($response->getStatusCode() != 200) {
                Log::error('Error getting "camino" from MTOP WFS: '.$url);
                return null;
                /*$error = LayerCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
                 throw $error;*/
            }
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error(\GuzzleHttp\Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(\GuzzleHttp\Psr7\str($e->getResponse()));
            }
            return null;
            /*$error = LayerCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
             throw $error;*/
        }
        return json_decode($response->getBody(), true);
    }
    
    
    public static function comprobarCamino($departamento, $codigo_camino) {
        //http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wfs?service=WFS&version=1.0.0&request=getFeature&typeName=caminerias_intendencias:v_camineria_cerro_largo&outputFormat=application/json&cql_filter=codigo=%27UYCL0158%27&propertyName=codigo&maxFeatures=1
        $client = new Client();
        $dep = Department::where('code', $departamento)->first();
        $camineria_wfs_url = env('CAMINERIA_WMS_URL', ViewerConfigApiController::CAMINERIA_DEFAULT_WFS_URL);

        $url = $camineria_wfs_url . "?service=WFS&version=1.0.0&request=getFeature&typeName=".$dep->layer_name."&outputFormat=application/json&cql_filter=codigo='".$codigo_camino."'&propertyName=codigo&maxFeatures=1";
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept'     => 'application/json',
                ],
                'connect_timeout' => 5
            ]);
            if ($response->getStatusCode() != 200) {
                Log::error('Error getting "camino" from MTOP WFS: '.$url);
                return false;
                /*$error = LayerCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
                 throw $error;*/
            }
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error(GuzzleHttp\Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(GuzzleHttp\Psr7\str($e->getResponse()));
            }
            return false;
            /*$error = LayerCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
             throw $error;*/
        }
        $responseObj = json_decode($response->getBody());
        if ($responseObj->totalFeatures == 1) {
            return true;
        }
        return false;
    }
}
