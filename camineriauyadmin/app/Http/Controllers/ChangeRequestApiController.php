<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\Role;
use App\Mail\ChangeRequestCreated;
use App\Http\Requests\ChangeRequestApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;
use App\ChangeRequests\ChangeRequestProcessor;

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
    
    public static function throwPendingElementNotModifiableError() {
        $errors = ['Error' => "No se puede modificar un elemento que está pendiente de validación"];
        $error = \Illuminate\Validation\ValidationException::withMessages($errors);
        throw $error;
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
        $feature = $validated['feature'];
        $properties = $feature["properties"];
        $feature_id = array_get($properties, "id");
        // parse as a Geometry object to ensure we have a valid geom
        $geom = Geometry::fromJson(json_encode($feature));
        
        $changeRequestProcessor = new ChangeRequestProcessor();
        $response = $changeRequestProcessor
                        ->createChangeRequest(
                            $validated['layer'],
                            $validated['operation'],
                            $properties,
                            $request->user(),
                            $geom,
                            $feature_id);
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
