<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use App\Helpers\Helpers;
use App\Http\Requests\EditableLayerDefCreateFormRequest;
use App\Http\Requests\EditableLayerDefUpdateFormRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\LayerCreationException;
use App\Exceptions\StyleCreationException;
use App\Exceptions\SetStyleException;
use App\Exceptions\StyleUpdateException;

class EditableLayerDefController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = EditableLayerDef::all();
        return view('editablelayerdef.index', ['editablelayerdefs' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = new EditableLayerDef();
        $data->geom_type = 'point';
        $data->protocol = 'wfs';
        $data->url = env('WFS_URL','');
        return view('editablelayerdef.create', [
            'editablelayerdef'=>$data,
            'color'=>'#000000'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EditableLayerDefCreateFormRequest $request)
    {
        $validated = $request->validated();
        EditableLayerDef::checkTableName($validated['name']);
        $validated['geom_type'] = array_get($validated, 'geom_type', 'point');
        //$validated['protocol'] = array_get($validated, 'protocol', 'wfs');
        Helpers::set_boolean_value($validated, 'isvisible');
        Helpers::set_boolean_value($validated, 'download');
        Helpers::set_boolean_value($validated, 'showTable');
        Helpers::set_boolean_value($validated, 'showInSearch');
        //$validated['fields'] = json_encode($validated['fields']);
        Log::error($validated['fields']);
        // FIXME: manage style & uploaded images
        //$validated['geom_style'] = 'marker';
        //$validated['style'] = '{"iconUrl":"marker-baden.png", "iconSize":[35, 41], "iconAnchor":[12, 41], "popupAnchor":[1, -34]}';
        $validated['geom_style'] = 'point';
        $validated['style'] = $this->get_leaflet_color_def($validated['color']);

        EditableLayerDef::createTable($validated['name'], $validated['fields'], $validated['geom_type']);
        try {
            EditableLayerDef::publishLayer($validated['name'], $validated['title']);
        }
        catch (LayerCreationException $e) {
            // TODO can we ignore the error?
        }
        try {
            EditableLayerDef::publishStyle($validated['name'], $validated['title'], $validated['color']);
        }
        catch (StyleCreationException $e) {
            // TODO can we ignore the error?
        }
        try {
            EditableLayerDef::setLayerStyle($validated['name'], $validated['name']);
        }
        catch (SetStyleException $e) {
            // TODO can we ignore the error?
        }
        try {
            EditableLayerDef::create($validated);
        } catch(QueryException $e) {
            Log::error('Error creando la definición de capa: '.$e->getMessage());
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'name' => ['Error creando la definición de capa: '.$e->getMessage()],
            ]);
            EditableLayerDef::dropTable($validated['name']);
            throw $error;
        }
        return redirect()->route('editablelayerdefs.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function show(EditableLayerDef $editablelayerdef)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function edit(EditableLayerDef $editablelayerdef)
    {
        return view('editablelayerdef.edit',
            [
                'editablelayerdef'=>$editablelayerdef,
                'color'=>$this->get_previous_colour($editablelayerdef->style)
            ]);
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function update(EditableLayerDefUpdateFormRequest $request, EditableLayerDef $editablelayerdef)
    {
        $validated = $request->validated();
        if ($editablelayerdef->geom_type == 'point') {
            $old_color = $this->get_previous_colour($editablelayerdef->style);
            if ($old_color!==null && strcasecmp($validated['color'], $old_color)!=0){
                $validated['style'] = $this->get_leaflet_color_def($validated['color']);
                try {
                    EditableLayerDef::updateStyle($editablelayerdef->name, $validated['title'], $validated['color']);
                }
                catch(StyleUpdateException $e) {
                    Log::error('Error updating style for layer: '.$editablelayerdef->name);
                    Log::error($e->getMessage());
                    // TODO can we ignore the error?
                }
            }
        }
        Helpers::set_boolean_value($validated, 'isvisible');
        Helpers::set_boolean_value($validated, 'download');
        Helpers::set_boolean_value($validated, 'showTable');
        Helpers::set_boolean_value($validated, 'showInSearch');
        if ($validated['fields'] != $editablelayerdef->fields) {
            $this->updatedDomains($editablelayerdef->name, json_decode($editablelayerdef->fields), json_decode($validated['fields']));
        }
        $editablelayerdef->update($validated);
        return redirect()->route('editablelayerdefs.index');
    }
    
    
    protected function searchDomainCode($code, $domain) {
        foreach ($domain as $domainEntry) {
            if ($domainEntry->code == $code) {
                return true;
            }
        }
        return false;
    }
    
    protected function updatedDomains($tableName, $existingFields, $proposedFields) {
        $errors = [];
        $sqls = [];
        $existingFieldDict = [];
        foreach ($existingFields as $existingField) {
            if ($existingField->type=='stringdomain') {
                $existingFieldDict[$existingField->name] = $existingField;
            }
        }
        $fieldBefore = '';
        foreach ($proposedFields as $currentField) {
            if (!preg_match("/[a-zA-Z][a-zA-Z0-9_]*/", $currentField->name)) {
                $errors[$currentField->name] = 'No se permiten caracteres especiales en los nombres de campo';
            }
            if ($currentField->type=='stringdomain' && isset($existingFieldDict[$currentField->name])) {
                $existingField = $existingFieldDict[$currentField->name];
                $enumValues = "";
                $sameDomain = true;
                foreach ($currentField->domain as $domainEntry) {
                    $code = $domainEntry->code;
                    if (!$this->searchDomainCode($code, $existingField->domain)) {
                        $sameDomain = false;
                    }
                    
                    // check input
                    if (preg_match("/[\'\\\\]+/", $code)) {
                        $errors[$currentField->name.'.'.$code] = 'No se permiten comillas simples ni barras invertidas en las enumeraciones';
                    }
                    if (is_numeric($code)) { // numeric enumerations are a bad idea
                        $errors[$currentField->name] = 'No se permiten códigos numéricos en las enumeraciones';
                    }
                    
                    if ($enumValues=="") {

                        $enumValues = "'".$code."'";
                    }
                    else {
                        $enumValues = $enumValues.", '".$code."'";
                    }
                }
                
                $sql = 'ALTER TABLE `'.$tableName.'` MODIFY COLUMN `'.$currentField->name.'` enum('.$enumValues.')';
                if ($fieldBefore!=='') {
                    $sql = $sql.' AFTER `'.$fieldBefore.'`';
                }
                if (!$sameDomain) {
                    Log::debug("sql: ".$sql);
                    $sqls[] = $sql;
                }
            }
            $fieldBefore = $currentField->name;
        }
        if (count($errors)>0) {
            $error = \Illuminate\Validation\ValidationException::withMessages($errors);
            throw $error;
        }
        if (count($sqls)>0) {
            try {
                DB::transaction(function () use ($sqls) {
                    foreach ($sqls as $alterEnumSql) {
                        DB::statement($alterEnumSql);
                    }
                });
            }
            catch (\Exception $e) {
                Log::error($e);
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    $tableName => ['Actualización de dominios no válida. '.$e->getMessage()]
                ]);
                throw $error;
            }
        }
    }
    
    protected function get_previous_colour($style) {
        $stylejson = json_decode($style, true);
        return array_get($stylejson, "color", null);
    }

    protected function get_leaflet_color_def($color) {
        return '{"radius": 5, "fillColor": "'. $color . '", "color": "' . $color .'", "weight": 1, "opacity": 1}';
    }

    public function enable(Request $request, $id)
    {
        $editablelayerdef = EditableLayerDef::findOrFail($id);
        $editablelayerdef->enabled = True;
        $editablelayerdef->save();
        return redirect()->route('editablelayerdefs.index');
    }
    public function disable(Request $request, $id)
    {
        $editablelayerdef = EditableLayerDef::findOrFail($id);
        $editablelayerdef->enabled = False;
        $editablelayerdef->save();
        return redirect()->route('editablelayerdefs.index');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function destroy(EditableLayerDef $editablelayerdef)
    {
        /*
        // don't allow layer deletion
        EditableLayerDef::dropTable($editablelayerdef->name);
        $editablelayerdef->delete();
        */
        return redirect()->route('editablelayerdefs.index');
    }
}
