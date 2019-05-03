<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use App\Intervention;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\ChangeRequests\ChangeRequestProcessor;
use App\Exports\InterventionsExport;
use App\Exports\InterventionsSummaryExport;
use App\Helpers\Helpers;
use App\User;
use App\Camino;
use App\ChangeRequest;
use App\Exceptions\ImportLayerException;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class ImportLayerController extends Controller
{
    protected function getFormVariables(User $user) {
        $all_layers = EditableLayerDef::enabled()->get();
        $inventory_layers = [];
        foreach ($all_layers as $current_lyr) {
            $inventory_layers[$current_lyr->name] = $current_lyr->title;
        }
        return [
            'inventory_layers'=>$inventory_layers
        ];
    }
    
    public function query(Request $request)
    {
        $variables = $this->getFormVariables($request->user());
        return view('imports.query', $variables);
    }
    
    protected function getFieldMapping(array $headerRow, array $layerFieldDefs) {
        $count = 0;
        $errors = [];
        $mapping = [];
        $presentFields = [];

        foreach ($headerRow as $fieldName) {
            foreach ($layerFieldDefs as $fieldDef) {
                if (strcasecmp($fieldName, $fieldDef->name)==0 ||
                    strcasecmp(explode(",", $fieldName)[0], $fieldDef->name)==0 ||
                    strcasecmp($fieldName, substr($fieldDef->name, 0, 10))==0 ||
                    strcasecmp(explode(",", $fieldName)[0], substr($fieldDef->name, 0, 10))==0) {
                    $mapping[$count] = $fieldDef;
                    $presentFields[] = $fieldDef->name;
                }
            }
            $count = $count + 1;
        }
        foreach ($layerFieldDefs as $fieldDef) {
            if (isset($fieldDef->mandatory) && !in_array($fieldDef->name, $presentFields)) {
                $errors[$fieldDef->name] = 'El campo es obligatorio';
            }
        }
        if (count($errors)>0) {
            $error = \Illuminate\Validation\ValidationException::withMessages($errors);
            throw $error;
        }
        return $mapping;
    }
    
    protected function insertRow(array $row, string $tableName, array $fieldMapping, $status, $departments) {
        $errors = [];

        if (count($row) != count($fieldMapping)) {
            // $errors[] = ['Número de campos' => 'La capa contiene un número de campos distinto del esperado'];
        }
        $values = [];
        $departamento = null;
        $codigo_camino = null;
        $x = null;
        $y = null;
        foreach ($fieldMapping as $fieldNumber => $fieldDef) {
            $value = $row[$fieldNumber];
            if ($fieldDef->name == 'x') {
                $x = $value;
            }
            elseif ($fieldDef->name == 'y') {
                $y = $value;
            }
            elseif ($fieldDef->name == 'departamento') {
                $departamento = $value;
                if (! in_array($value, $departments)) {
                    $errors['departamento'.$value] = ['El usuario no tiene permisos para editar el departamento: '.$value];
                }
                $values[$fieldDef->name] = $value;
            }
            elseif ($fieldDef->name == 'codigo_camino') {
                $codigo_camino = $value;
                $values[$fieldDef->name] = $value;
            }
            else {
                if ($fieldDef->type == 'decimal' || $fieldDef->type == 'intdecimal' ||
                    $fieldDef->type == 'double' || $fieldDef->type == 'integer')
                {
                    if (!is_numeric($value)) {
                        if ($value!==null || !empty($fieldDef->mandatory)) {
                            $errors[] = [$fieldDef->name => 'El valor del campo '.$fieldDef->name.' no es numérico: '.$value];
                        }
                    }
                    $values[$fieldDef->name] = $value;
                }
                elseif ($fieldDef->type == 'boolean') {
                    $values[$fieldDef->name] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
                elseif ($fieldDef->type == 'date') {
                    if ($fieldDef->name == 'updated_at') {
                        $values['updated_at'] = date('Y-m-d');
                    }
                    elseif ($value!==null) {
                        $theDate = false;
                        try {
                            $theDate = Date::excelToDateTimeObject($value);
                        }
                        catch (\Exception $e) {
                            $theDate = \DateTime::createFromFormat('d/m/Y', $value);
                            if ($theDate===false) {
                                $theDate = \DateTime::createFromFormat('Y/m/d', $value);
                            }
                            if ($theDate===false) {
                                $theDate = \DateTime::createFromFormat('Y-m-d', $value);
                            }
                        }
                        if ($theDate===false) {
                            $errors[] = [$fieldDef->name => 'El formato de fecha del campo '.$fieldDef->name.' no es válido: '.$value];
                            $values[$fieldDef->name] = $value;
                        }
                        else {
                            $theDateStr = $theDate->format('Y-m-d');
                            $values[$fieldDef->name] = $theDateStr;
                        }
                    }
                    elseif ($fieldDef->name == 'created_at') {
                        $values['created_at'] = date('Y-m-d');;
                    }
                }
                elseif ($fieldDef->type == 'dateTime') {
                    if ($value!==null) {
                        $theDate = false;
                        try {
                            $theDate = Date::excelToDateTimeObject($value);
                        }
                        catch (\Exception $e) {
                            $theDate = \DateTime::createFromFormat('d/m/Y H:i:s', $value);
                            if ($theDate===false) {
                                $theDate = \DateTime::createFromFormat('Y/m/d H:i:s', $value);
                            }
                            if ($theDate===false) {
                                $theDate = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                            }
                        }
                        if ($theDate===false) {
                            $errors[] = [$fieldDef->name => 'El formato de fecha del campo '.$fieldDef->name.' no es válido: '.$value];
                            $values[$fieldDef->name] = $value;
                        }
                        else {
                            $theDateStr = $theDate->format('Y-m-d H:i:s');
                            $values[$fieldDef->name] = $theDateStr;
                        }
                    }
                }
                else {
                    $values[$fieldDef->name] = $value;
                }
            }
        }
        if ($departamento==null) {
            $errors['departamento'] = ['No se ha especificado el departamento'];
        }
        elseif ($codigo_camino==null) {
            $errors['codigo_camino'] = ['No se ha especificado el código de camino'];
        }
        elseif (!ChangeRequest::comprobarEstructuraCodigoCamino($codigo_camino, $departamento)) {
            $errors['codigo_camino'] = ['El código de camino '.$codigo_camino.' no es válido para el departamento '.$departamento];
        }
        if ($x != null && $y != null) {
            if (is_numeric($x) && is_numeric($y)) {
                $geom = new Point($x, $y);
                $values['thegeom'] = ChangeRequestProcessor::prepareGeom($geom);
            }
            else {
                $errors['thegeom'] = ['Las coordinadas no son válidas'];
            }
        }
        
        $values['status'] = $status;
        unset($values['id']);
        if ($tableName == Intervention::LAYER_NAME) {
            unset($values['version']);
            $tipo_elem = array_get($values, 'tipo_elem', '');
            $tipo_elem_found = EditableLayerDef::enabled()->where('name', $tipo_elem)->first();
            if ($tipo_elem_found == null) {
                $errors['tipo_elem'] = ['Tipo de elemento no válido: '.$tipo_elem];
            }
        }
        else {
            $values['version'] = 1;
        }
        if (count($errors) == 0) {
            try {
                $values['id'] = DB::table($tableName)->insertGetId($values);
                return $values;
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error($e);
            
                if ($e->getCode()=="01000"
                    && (strpos($e->getMessage(), 'Warning: 1265'))) {
                        $errors['sql'] = "El valor del campo no pertenece al dominio de valores definido para el campo. Error: ".$e->getMessage();
                }
                else {
                    $errors['sql'] = "Error insertando el registro. El registro contiene datos inválidos. \nError: ".$e->getMessage()."\nSQL: ".$e->getSql();
                }
            }
        }
        $error = new ImportLayerException($errors, $values);
        throw $error;
    }
    
    protected function createChangeRequest(string $layer, array $values, Geometry $geom=null) {
        $user = Auth::user();
        $changerequest = new ChangeRequest();
        $changerequest->layer = $layer;
        $changerequest->operation = ChangeRequest::OPERATION_CREATE;
        $changerequest->codigo_camino = array_get($values, "codigo_camino");
        $changerequest->departamento = array_get($values, "departamento");
        $changerequest->feature_id = array_get($values, "id");
        if ($user->isAdmin()) {
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        }
        else {
            $changerequest->status = ChangeRequest::STATUS_PENDING;
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
        $feature['properties'] = $values;
        
        $changerequest->feature = json_encode($feature);
        if ($user->isAdmin()) {
            $changerequest->validator()->associate($user);
        }
        $user->changeRequests()->save($changerequest);
        if (!$user->isAdmin()) {
            ChangeRequestProcessor::notifyChangeRequest($user, $changerequest);
        }
    }
    
    protected function importSpreadsheet(string $path, string $layerName, array $fieldDef, string $status, array $departments) {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $header = true;
        $count = 1;
        
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            if ($header) {
                $headerRow = [];
                foreach ($cellIterator as $cell) {
                    $headerRow[]  = $cell->getValue();
                }
                $header = false;
                if ($layerName != Camino::LAYER_NAME && $layerName != Intervention::LAYER_NAME) {
                    $fieldDefWithGeom = $fieldDef;
                    $fieldDefWithGeom[] = (object)["name"=>'x','type'=>'double','label'=>'x','definition'=>'Coordenada X'];
                    $fieldDefWithGeom[] = (object)["name"=>'y','type'=>'double','label'=>'y','definition'=>'Coordenada Y'];
                    $fieldMapping  = $this->getFieldMapping($headerRow, $fieldDefWithGeom);
                }
                else {
                    $fieldMapping  = $this->getFieldMapping($headerRow, $fieldDef);
                }
            }
            else {
                $row = [];
                foreach ($cellIterator as $cell) {
                    $row[] = $cell->getValue();
                }
                try {
                    $values = $this->insertRow($row, $layerName, $fieldMapping, $status, $departments);
                    $this->createChangeRequest($layerName, $values);
                }
                catch (ImportLayerException $e) {
                    $messages = $e->messages;
                    $values = $e->values;
                    $messages["registro.".$count] = ["El registro número ".$count." no es válido. No se importarán los registros restantes. Registro: ".json_encode($row)." - Insertando: ".json_encode($values)];
                    
                    $error = \Illuminate\Validation\ValidationException::withMessages($messages);
                    throw $error;
                }
            }
            $count = $count + 1;
        }
    }

    public function import(Request $request) {
        //Log::debug(json_encode($request->all()));
        $layerName = $request->input('layer');
        $layerConf = EditableLayerDef::enabled()->where('name', $layerName)->first();
        if ($layerConf==null) {
            $error = \Illuminate\Validation\ValidationException::withMessages([$layerName => "Capa no válida"]);
            throw $error;
        }
        $user = $request->user();
        if ($user->isAdmin()) {
            $status = ChangeRequest::FEATURE_STATUS_VALIDATED;
        }
        elseif ($user->isManager()){
            $status = ChangeRequest::FEATURE_STATUS_PENDING_CREATE;
        }
        else {
            $error = \Illuminate\Validation\ValidationException::withMessages(["Error"=>["El usuario no tiene permisos para importar datos"]]);
            throw $error;
        }
        $departments = [];
        foreach ($user->departments as $department) {
            $departments[] = $department->code;
        }
        $fieldDef = json_decode($layerConf->fields);
        
        if (is_array($request->importfile)) {
            if (count($request->importfile)==1) {
                $file = $request->importfile[0];
            }
            else {
                $error = \Illuminate\Validation\ValidationException::withMessages(['Error' => "Formato no válido"]);
                throw $error;
            }
        }
        else {
            $file = $request->importfile;
        }
        /*
        foreach ($request->importfile as $file) {
            Log::debug("real path: ".$file->getRealPath());
            Log::debug("client name: ".$file->getClientOriginalName());
        }*/
        $path = $file->getRealPath();
        try {
            $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            //if ($layerName == Intervention::LAYER_NAME || $layerName == Camino::LAYER_NAME) {
                if ($ext == 'csv' || $ext == 'xls' || $ext == 'xlsx' || $ext == 'ods') {
                    $this->importSpreadsheet($path, $layerName, $fieldDef, $status, $departments);
                }
                else {
                    $error = \Illuminate\Validation\ValidationException::withMessages([$ext => "Formato no válido"]);
                    throw $error;
                }
            /*}
            elseif ($ext == "shp") {
                // TODO
            }
            else {
                $error = \Illuminate\Validation\ValidationException::withMessages([$ext => "Formato no válido"]);
                throw $error;
            }*/
        }
        finally {
            Storage::delete($path);
        }
        $request->session()->flash('message', 'Datos importados correctamente.');
        $request->session()->flash('_old_input.layer', $layerName);
        return redirect()->route('imports.query')->with(
            ['status'=> 'Datos importados correctamente.',
                'layer'=> $layerName]);
    }
}
