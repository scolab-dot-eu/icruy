<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use App\Http\Requests\EditableLayerDefCreateFormRequest;
use App\Http\Requests\EditableLayerDefUpdateFormRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
        return view('editablelayerdef.create', ['editablelayerdef'=>$data]);
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
        $validated['protocol'] = array_get($validated, 'protocol', 'wfs');
        //$validated['fields'] = json_encode($validated['fields']);
        Log::error($validated['protocol']);
        Log::error($validated['fields']);
        // FIXME: manage style & uploaded images
        //$validated['geom_style'] = 'marker';
        //$validated['style'] = '{"iconUrl":"marker-baden.png", "iconSize":[35, 41], "iconAnchor":[12, 41], "popupAnchor":[1, -34]}';
        $validated['geom_style'] = 'point';
        if ($validated['name'] == 'cr_alcantarilla') {
            $color = '#686562';
        }
        elseif  ($validated['name'] == 'cr_baden') {
            $color = '#F08A08';
        }
        elseif  ($validated['name'] == 'cr_obstaculo') {
            $color = '#F01D08';
        }
        elseif  ($validated['name'] == 'cr_paso') {
            $color = '#EEEB0D';
        }
        elseif  ($validated['name'] == 'cr_puente') {
            $color = '#25C62F';
        }
        elseif  ($validated['name'] == 'cr_senyal') {
            $color = '#084EF0';
        }
        else {
            $color = '#9F6408';
        }
        $validated['style'] = '{"radius": 5, "fillColor": "'. $color . '", "color": "' . $color .'", "weight": 1, "opacity": 1}';
        $validated['conf'] = '{"visible": true, "download": true, "editable": true, "showTable":true, "showInSearch": true}';

        EditableLayerDef::createTable($validated['name'], $validated['fields'], $validated['geom_type']);
        EditableLayerDef::publishLayer($validated['name'], $validated['title']);
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
        return view('editablelayerdef.edit', ['editablelayerdef'=>$editablelayerdef]);
       
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
        $editablelayerdef->update($request->validated());
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
        // FIXME: should we really allow deletion??
        EditableLayerDef::dropTable($editablelayerdef->name);
        $editablelayerdef->delete();
        return redirect()->route('editablelayerdefs.index');
    }
}
