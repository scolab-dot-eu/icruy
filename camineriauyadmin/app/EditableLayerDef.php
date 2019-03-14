<?php

namespace App;

use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7;
use App\Exceptions\LayerCreationException;
use App\Exceptions\SetStyleException;
use App\Exceptions\StyleCreationException;
use App\Exceptions\StyleUpdateException;
use App\Exceptions\TableCreationException;

class EditableLayerDef extends Model
{
    protected $table = 'editablelayerdefs';
    
    protected $fillable = [
        'name', 'title', 'geom_type', 'protocol',
        'url', 'fields', 'geom_style', 'style',
        'metadata', 'conf', 'isvisible', 'download', 'showTable', 'showInSearch'
    ];

    public function scopeEnabled($query)
    {
        return $query->where('enabled', True);
    }
    public function scopeGeometricLayers($query)
    {
        return $query->where('geom_type', '!=', 'none');
    }
    
    public static function checkTableName($tablename) {
        try {
            // check if the table exists
            DB::table($tablename)
            ->select(DB::raw('1'))->limit(1)->first();
            Log::error('Error creando la capa. La tabla existe: '.$tablename);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'name' => ['Error creando la capa. La tabla existe: '.$tablename],
            ]);
            throw $error;
        }
        catch(QueryException $ex) {
            // do nothing here:
            // the exception will be raised if layer does not exist
        }
        
    }
    
    public static function getHistoricTableName($tablename) {
        return "crh".substr($tablename, 2);
    }
    
    public static function createTable($name, $fields_str, $geom_type) {
        EditableLayerDef::doCreateTable($name, $fields_str, $geom_type);
        try {
            EditableLayerDef::doCreateTable(EditableLayerDef::getHistoricTableName($name), $fields_str, $geom_type, true);
        }
        catch(TableCreationException $e) {
            Schema::dropIfExists($name);
        }
    }
    
    public static function dropTable($name) {
        Schema::dropIfExists($name);
        Schema::dropIfExists(EditableLayerDef::getHistoricTableName($name));
    }
    
    public static function getMysqlVersion() {
        $pdo = DB::connection()->getPdo();
        return $pdo->query('select version()')->fetchColumn();
    }
    
    public static function createFields(Blueprint $table, array $fieldsDef, array $ignoredFields, array &$errors) {
        $createdFields = [];
        foreach ($fieldsDef as $field) {
            if (in_array($field->name, $ignoredFields)) {
                    continue;
                }
                elseif ($field->name == 'feat_id') {
                    $errors[$field->name] = 'No se permite usar feat_id como nombre de campo';
                    continue;
                }
                
            $typeparams = isset($field->typeparams) ? $field->typeparams:'';
            if ($field->type == 'string') {
                if (is_numeric($typeparams)) {
                    $length = intval($typeparams);
                    $field_def = $table->string($field->name, $length);
                }
                else {
                    $field_def = $table->string($field->name);
                }
            }
            elseif ($field->type == 'decimal') {
                $typeparams_parts = explode(",", $typeparams);
                if (count($typeparams_parts)==2
                    && is_numeric($typeparams_parts[0])
                    && is_numeric($typeparams_parts[1])) {
                        $precision = intval($typeparams_parts[0]);
                        $scale = intval($typeparams_parts[1]);
                        $field_def = $table->decimal($field->name, $precision, $scale);
                    }
                    else {
                        $errors[$field->name] = 'La definción de la precisión y la escala es incorrecta';
                        continue;
                    }
            }
            elseif ($field->type == 'intdecimal') {
                if (is_numeric($typeparams)) {
                    $length = intval($typeparams);
                    $field_def = $table->decimal($field->name, $length, 0);
                }
                else {
                    $field_def = $table->integer($field->name);
                }
            }
            elseif ($field->type == 'stringdomain') {
                if ($field->domain !== null) {
                    $domainValues = [];
                    foreach ($field->domain as $domain) {
                        $domainValues[] = $domain->code;
                    }
                    Log::info('domain values: '.json_encode($domainValues));
                    $field_def = $table->enum($field->name, $domainValues);
                }
                else {
                    $errors[$field->name] = 'No se ha definido el dominio';
                    continue;
                }
            }
            elseif ($field->type == 'date') {
                $field_def = $table->date($field->name);
            }
            elseif ($field->type == 'dateTime') {
                $field_def = $table->dateTime($field->name);
            }
            elseif ($field->type == 'boolean') {
                $field_def = $table->boolean($field->name);
            }
            elseif ($field->type == 'double') {
                $field_def = $table->double($field->name);
                
            }
            elseif ($field->type == 'integer') {
                $field_def = $table->integer($field->name);
            }
            elseif ($field->type == 'text') {
                $field_def = $table->text($field->name);
            }
            else {
                $errors[$field->name] = 'Tipo de campo no permitido';
                continue;
            }
            
            if (!isset($field->mandatory) || $field->mandatory!==true) {
                $field_def->nullable();
            }
            
            $createdFields[] = $field->name;
        }
        return $createdFields;
    }
    
    public static function doCreateTable($name, $fields_str, $geom_type, $historic=false) {
        $fields = json_decode($fields_str);
        $errors = [];
        $specificFields = [];
        Schema::create($name, function (Blueprint $table) use ($name, $fields, $geom_type, $historic, &$specificFields, &$errors) {
            $version = EditableLayerDef::getMysqlVersion();
            $table->increments('id');
            
            if (strtolower($geom_type) == 'point') {
                if (version_compare($version, '8.0') >= 0) { // SRID is only supported from MySQL v8.0
                    $table->point('thegeom', 4326);
                }
                else {
                    $table->point('thegeom');
                }
            }
            elseif (strtolower($geom_type) == 'linestring') {
                if (version_compare($version, '8.0') >= 0) { // SRID is only supported from MySQL v8.0
                    $table->lineString('thegeom', 4326);
                }
                else {
                    $table->lineString('thegeom');
                }
            }
            elseif (strtolower($geom_type) == 'polygon') {
                if (version_compare($version, '8.0') >= 0) { // SRID is only supported from MySQL v8.0
                    $table->polygon('thegeom', 4326);
                }
                else {
                    $table->polygon('thegeom');
                }
            }
            elseif (strtolower($geom_type) == 'none' || substr(strtolower($geom_type), 0, 9) == 'external:') {
                // ignore
            }
            else {
                $errors['thegeom'] = 'Tipo de geometría no permitido';
            }
            
            $table->string('departamento', 4);
            $table->string('codigo_camino', 8);
            if ($historic) {
                $table->integer('feat_id');
            }
            else {
                $table->string('status', 23)->default(ChangeRequest::FEATURE_STATUS_PENDING_CREATE)->nullable();
                $table->integer('version')->unsigned()->nullable();
                //$table->string('status', 23)->default('VALIDADO');
                //$table->string('origin', 9)->nullable();
            }
            
            $ignoredFields = [
                'id',
                'origin',
                'cod_elem',
                'status',
                'departamento', 
                'codigo_camino',
                'thegeom',
                'updated_at',
                'created_at',
                'version'
            ];
            $specificFields = EditableLayerDef::createFields($table, $fields, $ignoredFields, $errors);
            
            $table->date('updated_at')->nullable();
            $table->date('created_at')->nullable();
            $table->index('codigo_camino');
            if ($historic) {
                $table->dateTime('valid_from');
                $table->dateTime('valid_to');
                $table->index(['valid_to', 'valid_from', 'codigo_camino']);
                $table->index(['valid_to', 'valid_from', 'departamento', 'codigo_camino'], $name.'_valid_dep_cod_cam_idx');
            }
            else {
                $table->index(['status', 'codigo_camino']);
                $table->index(['departamento', 'status', 'codigo_camino']);
            }
            $table->spatialIndex('thegeom');
            $table->foreign('departamento')->references('code')->on('departments');
        });
        
        if (!$historic) {
            EditableLayerDef::doCreateTriggers($name, $specificFields);
        }
        if (count($errors) > 0) {
            Log::error('Error creating table'.$name);
            Log::error(json_encode($errors));
            Schema::dropIfExists($name);
            $error = TableCreationException::withMessages($errors);
            throw $error;
        }
    }
    
    protected static function doCreateTriggers(string $name, array $specificFields) {
        DB::unprepared("
            INSERT INTO `camineria`.`geometry_columns`
                (`F_TABLE_NAME`,
                `F_GEOMETRY_COLUMN`,
                `COORD_DIMENSION`,
                `SRID`,
                `TYPE`)
            VALUES
                ('".$name."',
                'thegeom',
                2,
                0,
                'POINT')
        ");
        
        $historicName = EditableLayerDef::getHistoricTableName($name);
        DB::unprepared("
            CREATE TRIGGER ".$name."_before_insert
            BEFORE INSERT
               ON ".$name." FOR EACH ROW
            BEGIN
               IF NEW.status IS NULL OR NEW.status <> '".ChangeRequest::FEATURE_STATUS_VALIDATED."' THEN
                       SET NEW.status = '".ChangeRequest::FEATURE_STATUS_PENDING_CREATE."';
               END IF;
               IF NEW.version IS NULL OR NEW.version <> 1 THEN
                   SET NEW.version = 0;
                   SET NEW.created_at = CURDATE();
                   SET NEW.updated_at = CURDATE();
               END IF;
            END
        ");
        
        DB::unprepared("
            CREATE TRIGGER ".$name."_create_changerequest
            AFTER INSERT
                ON ".$name." FOR EACH ROW BEGIN
                IF NEW.version = 0 THEN
                    IF NEW.status = '".ChangeRequest::FEATURE_STATUS_PENDING_CREATE."' THEN
                        INSERT INTO changerequests
                            (requested_by_id, layer, feature_id, departamento, status, operation)
                        VALUES
                            (0, '".$name."', NEW.id, NEW.departamento, 0, '".ChangeRequest::OPERATION_CREATE."');
                    END IF;
                END IF;
            END
        ");
        
        DB::unprepared("
            CREATE TRIGGER ".$name."_after_insert
            AFTER INSERT
                ON ".$name." FOR EACH ROW BEGIN
                IF NEW.status = '".ChangeRequest::FEATURE_STATUS_VALIDATED."' THEN
                    -- Insert the new record into history table
                    INSERT INTO ".$historicName."
                        ( thegeom, feat_id, valid_from, valid_to, departamento, codigo_camino, updated_at, created_at, "
            ."`".implode('`, `', $specificFields)."` )
                    VALUES
                        ( NEW.thegeom, NEW.id, NOW(), '9999-12-31 23:59:59', NEW.departamento, NEW.codigo_camino, NEW.updated_at, NEW.created_at, "
            ."NEW.`".implode('`, NEW.`', $specificFields)."` );
                END IF;
            END
        ");
            
            DB::unprepared("
            CREATE TRIGGER ".$name."_before_update
            BEFORE UPDATE
                ON ".$name." FOR EACH ROW BEGIN
                DECLARE theCurrentTime DATETIME;
                IF NEW.status = '".ChangeRequest::FEATURE_STATUS_VALIDATED."' THEN
                    SELECT NOW() INTO theCurrentTime;
                    -- Insert the new record into history table
                    UPDATE ".$historicName."
                        SET valid_to = theCurrentTime
                    WHERE feat_id = OLD.id AND valid_to = '9999-12-31 23:59:59';
                    INSERT INTO ".$historicName."
                        ( thegeom, feat_id, valid_from, valid_to, departamento, codigo_camino, updated_at, created_at, "
                ."`".implode('`, `', $specificFields)."` )
                    VALUES
                        ( NEW.thegeom, NEW.id, theCurrentTime, '9999-12-31 23:59:59', NEW.departamento, NEW.codigo_camino, NEW.updated_at, NEW.created_at, "
                ."NEW.`".implode('`, NEW.`', $specificFields)."` );
                END IF;
            END
        ");
                
                DB::unprepared("
            CREATE TRIGGER ".$name."_before_delete
            BEFORE DELETE
               ON ".$name." FOR EACH ROW
            BEGIN
              -- Set end of life for the old record
              UPDATE ".$historicName."
              SET valid_to = NOW()
              WHERE feat_id = OLD.id AND valid_to = '9999-12-31 23:59:59';
            END
        ");
    }
    
    public static function publishLayer($name, $title) {
        EditableLayerDef::doPublishLayer($name, $title);
        $history_name = EditableLayerDef::getHistoricTableName($name);
        $history_title = $title . " históricos";
        EditableLayerDef::doPublishLayer($history_name, $history_title, True);
    }
    
    public static function doPublishLayer($name, $title, $historyDim = False) {
        //FIXME: publicar también la simbología
        $client = new Client();
        $baseUrl = env('GEOSERVER_URL');
        $workspace = env('DEFAULT_GEOSERVER_WS', 'camineria');
        $datastore = env('DEFAULT_GEOSERVER_DS', 'mysqlcamineria');
        $url = $baseUrl . "/rest/workspaces/" . $workspace . "/datastores/" . $datastore . "/featuretypes";
        //error_log($url);
        $qualified_store = $workspace . ":" . $datastore;
        $jsonBody = ["featureType"=> [
            "name" => $name,
            "title" => $title,
            "enabled" => True,
            "store" => ["@class" => "dataStore", "name" => $qualified_store],
            "nativeBoundingBox" => ["minx"=> -58.439349, "maxx"=> -53.181052, "miny"=> -34.973977, "maxy"=> -30.085504, "crs" => "EPSG:4326"],
            "srs"=> "EPSG:4326"
        ]];

        if ($historyDim) {
            $jsonBody['featureType']['metadata'] = [
                "entry" => [
                    [
                        "@key" => "elevation",
                        "dimensionInfo" => ["enabled"=> False]
                    ],
                    [
                        "@key" => "time",
                        "dimensionInfo" => [
                            "enabled"=> True,
                            "attribute" => "valid_from",
                            "endAttribute" => "valid_to",
                            "presentation" => "CONTINUOUS_INTERVAL",
                            "units" => "ISO8601",
                            "defaultValue" => ["strategy" => "MAXIMUM"]
                        ]
                        
                    ]
                ]
            ];
        }
        try {
            $response = $client->request('POST', $url, [
                'auth' =>  [env('GEOSERVER_USER', 'admin'), env('GEOSERVER_PASS', 'geoserver')],
                'json' => $jsonBody
            ]);
            if ($response->getStatusCode() != 201) {
                Log::error('Error publishing layer'.$name);
                $error = LayerCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
                throw $error;
            }
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
            $error = LayerCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
            throw $error;
        }
    }
    
    public static function setLayerStyle($layerName, $styleName) {
        EditableLayerDef::doSetLayerStyle($layerName, $styleName);
        $historyName = EditableLayerDef::getHistoricTableName($layerName);
        EditableLayerDef::doSetLayerStyle($historyName, $styleName);
    }
    
    public static function doSetLayerStyle($layerName, $styleName) {
        $client = new Client();
        $baseUrl = env('GEOSERVER_URL');
        $workspace = env('DEFAULT_GEOSERVER_WS', 'camineria');
        //$url = $baseUrl . "/rest/workspaces/" . $workspace . "/layers/".$layerName;
        $url = $baseUrl . "/rest/layers/".$workspace.":".$layerName;
        //error_log($url);
        $response = $client->request('GET', $url, [
            'auth' =>  [env('GEOSERVER_USER', 'admin'), env('GEOSERVER_PASS', 'geoserver')],
            'headers' => [
                'Accept'     => 'application/json',
            ]
        ]);
        if ($response->getStatusCode() != 200) {
            Log::error('Error getting layer configuration: '.$layerName);
            // FIXME: which error should be raised
            $error = \Illuminate\Validation\ValidationException::withMessages(['Error'=>['No se pudo obtener la configuración de capa: '.$layerName]]);
            throw $error;
        }
        $layerConf = json_decode($response->getBody(), true);
        $qualifiedStyleName = $workspace.':'.$styleName;
        $styleUrl = $baseUrl .  "/rest/workspaces/" . $workspace . "/styles/" . $styleName . '.json';
        $layerConf['layer']['defaultStyle'] = ['name' => $qualifiedStyleName, 'workspace'=> $workspace, 'href' => $styleUrl];
        //error_log(json_encode($layerConf));
        try {
            $response = $client->request('PUT', $url, [
                'auth' =>  [env('GEOSERVER_USER', 'admin'), env('GEOSERVER_PASS', 'geoserver')],
                'json' => $layerConf
            ]);
            if ($response->getStatusCode() != 200) {
                Log::error('Error setting layer style: '.$layerName. ' - URL: '.$url);
                Log::error(Psr7\str($response));
                Log::error($response->getReasonPhrase());
                Log::error($response->getBody());
                $error = SetStyleException::withMessages(['Error'=>['No se pudo establecer el estilo de capa: '.$layerName]]);
                throw $error;
            }
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
            $error = SetStyleException::withMessages(['Error'=>['No se pudo establecer el estilo de capa: '.$layerName]]);
            throw $error;
        }
    }
    
    
    public static function getCircleStyle($name, $title, $color) {
        $style = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<StyledLayerDescriptor version="1.0.0"
  xsi:schemaLocation="http://www.opengis.net/sld http://schemas.opengis.net/sld/1.0.0/StyledLayerDescriptor.xsd" xmlns="http://www.opengis.net/sld"
  xmlns:ogc="http://www.opengis.net/ogc" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <NamedLayer>
    <Name>$name</Name>
    <UserStyle>
      <Name>$name</Name>
      <Title>$title</Title>
      <FeatureTypeStyle>
        <Rule>
          <Title>$title</Title>
          <PointSymbolizer>
            <Graphic>
              <Mark>
                <WellKnownName>circle</WellKnownName>
                <Fill>
                  <CssParameter name="fill">
                    <ogc:Literal>$color</ogc:Literal>
                  </CssParameter>
                  <CssParameter name="fill-opacity">
                    <ogc:Literal>1.0</ogc:Literal>
                  </CssParameter>
                </Fill>
                <Stroke>
                  <CssParameter name="stroke">
                    <ogc:Literal>$color</ogc:Literal>
                  </CssParameter>
                  <CssParameter name="stroke-width">
                    <ogc:Literal>1</ogc:Literal>
                  </CssParameter>
                  <CssParameter name="stroke-opacity">
                    <ogc:Literal>1.0</ogc:Literal>
                  </CssParameter>
                </Stroke>
              </Mark>
              <Opacity>
                <ogc:Literal>1.0</ogc:Literal>
              </Opacity>
              <Size>
                <ogc:Literal>10</ogc:Literal>
              </Size>
              
            </Graphic>
          </PointSymbolizer>
        </Rule>
      </FeatureTypeStyle>
    </UserStyle>
  </NamedLayer>
</StyledLayerDescriptor>
EOD;
        return $style;
    }
    
    public static function publishStyle($name, $title, $color) {
        $style = EditableLayerDef::getCircleStyle($name, $title, $color);
        //FIXME: publicar también la simbología
        $client = new Client();
        $baseUrl = env('GEOSERVER_URL');
        $workspace = env('DEFAULT_GEOSERVER_WS', 'camineria');
        $url = $baseUrl . "/rest/workspaces/" . $workspace . "/styles";
        try {
            $response = $client->request('POST', $url, [
                'auth' =>  [env('GEOSERVER_USER', 'admin'), env('GEOSERVER_PASS', 'geoserver')],
                'body' => $style,
                'headers' => ['Content-Type' => 'application/vnd.ogc.sld+xml; charset=utf-8']
            ]);
            if ($response->getStatusCode() != 201) {
                Log::error('Error publishing layer: '.$name.' - URL: '.$url);
                Log::error(Psr7\str($response));                
                Log::error($response->getStatusCode());
                Log::error($response->getReasonPhrase());
                Log::error($response->getBody());
                $error = StyleCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
                throw $error;
            }
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
            $error = StyleCreationException::withMessages(['Error'=>['No se pudo publicar la capa: '.$name]]);
            throw $error;
        }
    }

    public static function updateStyle($name, $title, $color) {
        $style = EditableLayerDef::getCircleStyle($name, $title, $color);
        $client = new Client();
        $baseUrl = env('GEOSERVER_URL');
        $workspace = env('DEFAULT_GEOSERVER_WS', 'camineria');
        $url = $baseUrl . "/rest/workspaces/" . $workspace . "/styles/" . $name;
        try {
            $response = $client->request('PUT', $url, [
                'auth' =>  [env('GEOSERVER_USER', 'admin'), env('GEOSERVER_PASS', 'geoserver')],
                'body' => $style,
                'headers' => ['Content-Type' => 'application/vnd.ogc.sld+xml; charset=utf-8']
            ]);
            if ($response->getStatusCode() != 200) {
                Log::error('Error publishing layer: '.$name.' - URL: '.$url);
                Log::error(Psr7\str($response));
                Log::error($response->getStatusCode());
                Log::error($response->getReasonPhrase());
                Log::error($response->getBody());
                $error = StyleUpdateException::withMessages(['Error'=>['No se pudo actualizar la capa: '.$name]]);
                throw $error;
            }
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            }
            $error = StyleUpdateException::withMessages(['Error'=>['No se pudo actualizar la capa: '.$name]]);
            throw $error;
        }
    }
}
