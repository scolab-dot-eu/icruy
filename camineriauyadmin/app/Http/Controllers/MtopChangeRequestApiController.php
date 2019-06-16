<?php

namespace App\Http\Controllers;

use App\Camino;
use App\MtopChangeRequest;
use App\ChangeRequest;
use App\Role;
use App\Http\Requests\MtopChangeRequestApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangeRequestCreated;
use App\Mail\MtopChangeRequestCreated;
use App\ChangeRequests\CaminoChangeRequestProcessor;


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
        // always pending status since they have to be validated by MTOP
        $mtopchangerequest->status = ChangeRequest::STATUS_PENDING;
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
            $notification = new MtopChangeRequestCreated($mtopchangerequest);
            $notification->onQueue('email');
            $managers = Role::mtopManagers()->first()->users()->get();
            Mail::to($managers)->queue($notification);
        }
        catch(\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex);
        }
        
        return $mtopchangerequest;
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
        $transResponse = DB::transaction(function () use ($operation, $feature, $user) {
            $mtopchangerequest = $this->createMtopChangeRequest($operation, $feature, $user);
            $changeRequestProcessor = new CaminoChangeRequestProcessor();
            //$changerequest = $changeRequestProcessor->createChangeRequest($operation, $feature, $user);
            $changerequest = $changeRequestProcessor->createChangeRequest(Camino::LAYER_NAME, $operation, $feature["properties"], $user);
            /*
             * FIXME: feature_id
            $changeRequestProcessor->createChangeRequest(Camino::LAYER_NAME,
                $feature['properties'], $operation, $user);
                */
            
            $result = [
                "mtopChangeRequest"=>$mtopchangerequest,
                "changeRequest"=>$changerequest
            ];
            return $result;
        });
        
        $mtopchangerequest = $transResponse['mtopChangeRequest'];
        $changerequest = $transResponse['changeRequest'];
        if ($changerequest !== null) {
            $response = $changerequest->toArray();
            $response['feature'] = json_decode($changerequest->feature, true);
        }
        else {
            $response['feature'] = $feature;
        }
        if ($mtopchangerequest !== null) {
            $response["mtopChangeRequest"] = [
                'id'=>$mtopchangerequest->id,
                'status'=>$mtopchangerequest->status,
                'status_label'=>$mtopchangerequest->status_label,
                'operation'=>$mtopchangerequest->operation,
                'operation_label'=>$mtopchangerequest->operation_label
            ];
        }
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
