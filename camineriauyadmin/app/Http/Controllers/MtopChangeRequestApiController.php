<?php

namespace App\Http\Controllers;

use App\Camino;
use App\MtopChangeRequest;
use App\ChangeRequest;
use App\Http\Requests\MtopChangeRequestApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MtopChangeRequestCreated;

class MtopChangeRequestApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
    
    protected function createMtopChangeRequest($operation, $feature, $user) {
        if ($operation==ChangeRequest::OPERATION_CREATE) {
            $gid = null; 
        }
        elseif ($operation==ChangeRequest::OPERATION_UPDATE || $operation==ChangeRequest::OPERATION_DELETE) {
            $gid = array_get($feature, "properties.gid", null);
        }
        $codigo_camino = array_get($feature, "properties.codigo_camino", null);
        $mtopchangerequest = new MtopChangeRequest();
        $mtopchangerequest->operation = $operation;
        $mtopchangerequest->feature_id = $gid;
        $mtopchangerequest->codigo_camino = $codigo_camino;
        $mtopchangerequest->departamento = array_get($feature, "properties.departamento");
        if ($user->isAdmin()) {
            $mtopchangerequest->status = ChangeRequest::STATUS_VALIDATED;
        }
        else {
            $mtopchangerequest->status = ChangeRequest::STATUS_PENDING;
        }
        // encode to keep only the geometry (the rest of properties will be cleaned)
        $feature_previous = MtopChangeRequest::getCurrentMtopFeature($mtopchangerequest->departamento, $mtopchangerequest->codigo_camino, $gid);
        Log::info("feature_previous:");
        Log::info(json_encode($feature_previous));
        if ($feature_previous!==null) {
            $mtopchangerequest->feature_previous = json_encode($feature_previous);
        }
        $feature['properties'] = [
            'codigo'=>$codigo_camino,
            'gid'=>$gid
        ];
        $mtopchangerequest->feature = json_encode($feature);
        $user->mtopChangeRequests()->save($mtopchangerequest);
        
        try {
            //Mail::to($request->user())->send(new MtopChangeRequestCreated($mtopchangerequest));
            //Mail::to('cmartinez@scolab.es')->send(new MtopChangeRequestCreated($mtopchangerequest));
        }
        catch(\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex);
        }
        
        return $mtopchangerequest;
    }
    
    protected function throwChangeRequestExists() {
        $errors = ['Error' => "No se puede modificar un camino pendiente de validación"];
        $error = \Illuminate\Validation\ValidationException::withMessages($errors);
        throw $error;
    }

    protected function createChangeRequest($mtopOperation, $feature, $user, $mtopchangerequest_id=null) {
        $codigo_camino = array_get($feature, "properties.codigo_camino");
        
        $layer = Camino::LAYER_NAME;
        $feature_previous = MtopChangeRequest::getCurrentFeature($layer, $codigo_camino);
        // la operación para el camino MTOP no es la misma que la operación en la tabla de caminos
        if ($feature_previous == null) {
            if ($mtopOperation==ChangeRequest::OPERATION_UPDATE) {
                $operation = ChangeRequest::OPERATION_CREATE;
            }
            else {
                // we don't need a ChR on cr_caminos table in this case
                return;
            }
        }
        else {
            if (ChangeRequest::equalValues($feature['properties'], $feature_previous)) {
                // don't need to create the ChR if values are equal
                return;
            }
            if ($feature_previous->status != ChangeRequest::FEATURE_STATUS_VALIDATED) {
            
                $this->throwChangeRequestExists();
            }
            else {
                $feature['properties']['created_at'] = $feature_previous->created_at;
                if ($mtopOperation==ChangeRequest::OPERATION_UPDATE || ($mtopOperation==ChangeRequest::OPERATION_DELETE)) {
                    $operation = $mtopOperation;
                }
                else {
                    $operation = ChangeRequest::OPERATION_UPDATE;
                }
            }
        }
        
        $changerequest = new ChangeRequest();
        if ($user->isAdmin()) {
            $changerequest->status = ChangeRequest::STATUS_VALIDATED;
        }
        else {
            $changerequest->status = ChangeRequest::STATUS_PENDING;
        }
        $changerequest->layer = $layer;
        $changerequest->operation = $operation;
        $changerequest->departamento = array_get($feature, "properties.departamento");
        $changerequest->codigo_camino = $codigo_camino;
        
        if ($operation != ChangeRequest::OPERATION_CREATE) {
            if (ChangeRequest::open()->where('layer', $layer)->where('feat_id', $codigo_camino)->count()>0) {
                /* if ($changerequest && $changerequest->requested_by!=$user) {}*/
                
                // ya existe un ChR sobre este camino
                $this->throwChangeRequestExists();
            }
            $changerequest->feature_previous = MtopChangeRequest::feature2json($feature_previous);
        }
        
        if ($user->isAdmin()) {
            $changerequest->validator()->associate($user);
        }

        // don't store the MTOP geom
        $the_feat = [];
        $the_feat['properties'] = $feature['properties'];
        $changerequest->feature = json_encode($the_feat);
        $user->changeRequests()->save($changerequest);
        // validate all the fields before storing the ChR
        $feature['properties'] = ChangeRequest::prepareFeature($layer, $feature, $operation);
        // feature status will always be PENDING (even for administrator) because MTOP request also has to be validated
        $feature = ChangeRequest::prepareInternalFields($feature, $operation, ChangeRequest::STATUS_PENDING, $feature_previous);
        /*
        $feature['statusmtop'] = ChangeRequest::STATUS_PENDING;
        */
        $newId = ChangeRequest::applyChangeRequest($layer, $operation, $feature);
        if ($newId) {
            $feature['properties']['id'] = $newId;
        }
        
        return $changerequest;
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MtopChangeRequestApiFormRequest $request)
    {
        $user = $request->user();
        $values = $request->validated();
        $operation = $values['operation'];
        $feature = $values['feature'];
        $transResult = DB::transaction(function () use ($operation, $feature, $user) {
            $mtopchangerequest = $this->createMtopChangeRequest($operation, $feature, $user);
            $changerequest = $this->createChangeRequest($operation, $feature, $user);
            $result = [
                "mchr"=>$mtopchangerequest,
                "chr"=>$changerequest
            ];
            return $result;
        });
        $mtopchangerequest = $transResult['mchr'];
        $changerequest = $transResult['chr'];
        $response = $mtopchangerequest->toArray();
        $response['feature'] = $feature;
        $response['status_label'] = $mtopchangerequest->statusLabel;
        $response['operation_label'] = $mtopchangerequest->operationLabel;
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function show(MtopChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function edit(MtopChangeRequest $changerequest)
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
    public function update(MtopChangeRequestApiFormRequest $request, ChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EditableLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function destroy(MtopChangeRequest $changerequest)
    {
        return response()->json(['status'=> 'error', 'msg'=> 'not implemented'], 400);
    }
}
