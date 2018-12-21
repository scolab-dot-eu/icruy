<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\Http\Requests\ChangeRequestApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Grimzy\LaravelMysqlSpatial\Types\Point;
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
        $changerequest->status = ChangeRequest::STATUS_PENDING;
        $changerequest->layer = $validated['layer'];
        $changerequest->operation = $validated['operation'];
        $feature = $validated['feature'];
        $feature_id = array_get($feature, "properties.id");
        $changerequest->departamento = array_get($feature, "properties.departamento");
        $changerequest->feature_previous = ChangeRequest::getCurrentFeatureAsGeojson($validated['layer'], $feature_id);
        Log::error('feature_previous');
        Log::error($changerequest->feature_previous);
        
        // parse as a Geometry object to ensure we have a valid geom
        $geom = Geometry::fromJson(json_encode($feature));
        // validate all the fields before storing the ChR
        ChangeRequest::prepareFeature($validated['layer'], $feature, $validated['operation']);
        Log::error('store feature');
        Log::error($feature);
        if ($request->user()->isAdmin()) {
            ChangeRequest::applyValidatedChangeRequest($validated['layer'], $validated['operation'], $feature, $geom);
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
            //ChangeRequest::setValidated($changerequest, $request->user());
            
        }
        else {
            $changerequest->status = ChangeRequest::STATUS_PENDING;
            ChangeRequest::applyPendingChangeRequest($validated['layer'], $validated['operation'], $feature, $geom);
        }
        Log::error('CMI');
        Log::error(json_encode($feature));
        $changerequest->feature_id = array_get($feature, "properties.id");
        $changerequest->feature = json_encode($feature);
        $request->user()->changeRequests()->save($changerequest);
        
        $response = $changerequest->toArray();
        $response['feature'] = $feature;
        $response['status_label'] = ChangeRequest::$STATUS_LABELS[$response['status']];
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
