<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\Http\Requests\ChangeRequestApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;

class ChangeRequestApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Auth::user()->changeRequests()->open()->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ChangeRequestApiFormRequest $request)
    {
        $validated = $request->validated();
        $changerequest = new ChangeRequest;
        if ($request->user()->isAdmin()) {
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        }
        else {
            $changerequest->status = ChangeRequest::STATUS_PENDING;
        }
        $changerequest->layer = $validated['layer'];
        $changerequest->operation = $validated['operation'];
        $feature = $validated['feature'];
        $feature_id = array_get($feature, "properties.id");
        $changerequest->codigo_camino = array_get($feature, "properties.codigo_camino");
        $changerequest->departamento = array_get($feature, "properties.departamento");
        $feature_previous = ChangeRequest::getCurrentFeature($validated['layer'], $feature_id);
        if ($changerequest->operation != ChangeRequest::OPERATION_CREATE) {
            if ($feature_previous && $feature_previous->status != ChangeRequest::FEATURE_STATUS_VALIDATED) {
                $errors = ['Error' => "No se puede modificar un elemento que está pendiente de validación"];
                $error = \Illuminate\Validation\ValidationException::withMessages($errors);
                throw $error;
            }
            $changerequest->feature_previous = ChangeRequest::feature2geojson($feature_previous);
        }
        
        // parse as a Geometry object to ensure we have a valid geom
        $geom = Geometry::fromJson(json_encode($feature));
        // validate all the fields before storing the ChR
        $feature['properties'] = ChangeRequest::prepareFeature($validated['layer'], $feature, $validated['operation']);
        $feature = ChangeRequest::prepareInternalFields($feature, $validated['operation'], $changerequest->status, $feature_previous);
        $newId = ChangeRequest::applyChangeRequest($validated['layer'], $validated['operation'], $feature, $geom);
        if ($newId) {
            $feature['properties']['id'] = $newId;
        }
        if ($request->user()->isAdmin()) {
            //Log::error("user is admin!");
            $changerequest->validator()->associate($request->user());
        }
        $changerequest->feature_id = array_get($feature, "properties.id");
        $changerequest->feature = json_encode($feature);
        $request->user()->changeRequests()->save($changerequest);
        
        $response = $changerequest->toArray();
        $response['feature'] = $feature;
        $response['status_label'] = $changerequest->statusLabel;
        $response['operation_label'] = $changerequest->operationLabel;
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function show(ChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function edit(ChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function update(ChangeRequestApiFormRequest $request, ChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }
}
