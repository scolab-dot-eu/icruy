<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use App\Helpers\Helpers;
use App\Http\Requests\EditableLayerDefCreateFormRequest;
use App\Http\Requests\EditableLayerDefUpdateFormRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
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
        $this->validate_fields($validated['fields']);
        $old_color = $this->get_previous_colour($editablelayerdef->style);
        if ($old_color!==null && strcasecmp($validated['color'], $old_color)!=0){
            $validated['style'] = $this->get_leaflet_color_def($validated['color']);
            try {
                EditableLayerDef::updateStyle($editablelayerdef->name, $validated['title'], $validated['color']);
            }
            catch(StyleUpdateException $e) {
                // TODO can we ignore the error?
            }
        }
        Helpers::set_boolean_value($validated, 'isvisible');
        Helpers::set_boolean_value($validated, 'download');
        Helpers::set_boolean_value($validated, 'showTable');
        Helpers::set_boolean_value($validated, 'showInSearch');
        $editablelayerdef->update($validated);
        return redirect()->route('editablelayerdefs.index');
    }
    
    protected function get_previous_colour($style) {
        $stylejson = json_decode($style, true);
        return array_get($stylejson, "color", null);
    }

    protected function get_leaflet_color_def($color) {
        return '{"radius": 5, "fillColor": "'. $color . '", "color": "' . $color .'", "weight": 1, "opacity": 1}';
    }
    
    protected function validate_fields() {
        
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
