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
    /**
     * 
     * @return string
     */
    public function getUpdatedAtFormattedAttribute(){
        return Carbon::parse($this->updated_at)->format('d/m/Y');
    }

    public static function getTableName($layer_name) {
        $layer_parts = explode(":", $layer_name);
        
        if (count($layer_parts)==2) {
            return $layer_parts[1];
        }
        else {
            return $layer_parts[0];
        }
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
            if (isset($feature->thegeomjson)) {
                $the_feat = json_decode($feature->thegeomjson, true);
            }
            else {
                $the_feat = [];
            }
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
    
    public static function comprobarEstructuraCodigoCamino($codigo_camino, $codigo_dep) {
        if ($codigo_camino!=null && strlen($codigo_camino)==8) {
            if (substr($codigo_camino, 0, 4)==$codigo_dep) {
                if (is_numeric(substr($codigo_camino, 4, 4))) {
                    return true;
                }
                elseif (is_numeric(substr($codigo_camino, 6, 2)) &&
                    preg_match('/^[A-Z_][A-Z]$/', substr($codigo_camino, 4, 2))) {
                    // camino compartido por dos estados
                        return true;
                }
            }
            elseif ((substr($codigo_camino, 0, 2) == 'UY') &&
                (substr($codigo_camino, 4, 2) == substr($codigo_dep, 2, 2))
                && preg_match('/^[A-Z_][A-Z]$/', substr($codigo_camino, 2, 2))
                && is_numeric(substr($codigo_camino, 6, 2))) {
                // camino compartido por dos estados
                return true;
            }
        }
        return false;
    }
}
