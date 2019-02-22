<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        if (empty($status) || $status=='open') {
            $data = ChangeRequest::open();
        }
        elseif ($status=='pending') {
            $data = ChangeRequest::where('status', ChangeRequest::STATUS_PENDING);
        }
        elseif ($status=='all') {
            $data = ChangeRequest::with(['author', 'validator']);
        }
        elseif ($status=='closed') {
            $data = ChangeRequest::closed();
        }
        elseif ($status=='admininfo') {
            $data = ChangeRequest::where('status', ChangeRequest::STATUS_ADMININFO);
        }
        elseif ($status=='userinfo') {
            $data = ChangeRequest::where('status', ChangeRequest::STATUS_USERINFO);
        }
        elseif ($status=='validated') {
            $data = ChangeRequest::where('status', ChangeRequest::STATUS_VALIDATED);
        }
        elseif ($status=='rejected') {
            $data = ChangeRequest::where('status', ChangeRequest::STATUS_REJECTED);
        }
        elseif ($status=='cancelled') {
            $data = ChangeRequest::where('status', ChangeRequest::STATUS_CANCELLED);
        }
        else {
            $data = ChangeRequest::open();
        }
        if (!$request->user()->isAdmin()) {
            $data = $data->where('requested_by_id', $request->user()->id);
        }
        $data->with(['author', 'validator'])->orderBy('updated_at', 'desc');
        return view('changerequest.index', ['changerequests' => $data->get()]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function show(ChangeRequest $changerequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(ChangeRequest $changerequest)
    {
        $previousFeature = json_decode($changerequest->feature_previous);
        if ($changerequest->operation == ChangeRequest::OPERATION_DELETE) {
            $proposedFeature = null;
        }
        else {
            if ($changerequest->feature == null && 
                $changerequest->operation == ChangeRequest::OPERATION_CREATE) {
                    // datos cargados directamente de la base de datos
                    $feature = ChangeRequest::getCurrentFeature($changerequest->layer, $changerequest->feature_id);
                    $changerequest->feature = json_encode(ChangeRequest::feature2array($feature));
                    $changerequest->save();
            }
            $proposedFeature = json_decode($changerequest->feature);
        }
        
//        Log::error($changerequest->feature_previous);
//        Log::error($changerequest->feature);
        
        /*
        $layer = $changerequest->layer;
//         Log::error('id: ');
//         Log::error($changerequest->feature_id);
        $feat_id = ChangeRequest::getFeatureId($changerequest->feature_id);
//         Log::error($feat_id);
        
//         Log::error('id bis: ');
//         Log::error($proposedFeature->properties->id);
        
//         $feat_id = array_get($proposedFeature, "properties.id");
//         Log::error($feat_id);
//         $feat_id = ChangeRequest::getFeatureId($feat_id);
//         Log::error($feat_id);
        $previousFeatureArray = [];
        
        $the_geom = '';
        if ($feat_id > 0) {
            $currentFeature = ChangeRequest::getCurrentFeature($layer, $feat_id);
            $the_geom = $currentFeature-> thegeomjson;
            foreach ($currentFeature as $key => $value) {
                if ($key != 'thegeom' && $key != 'thegeomjson') {
                    $previousFeatureArray[$key] = $value;
                }
            }
        }
        */
        return view('changerequest.edit', ['changerequest'=>$changerequest,
            'previousFeature'=>$previousFeature,
            'proposedFeature'=>$proposedFeature
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // for the moment, don't allow any change in the ChR except the status
        $origChangerequest = ChangeRequest::findOrFail($id);
        if (!$origChangerequest->isOpen) {
            $message = 'Se intentó modificar una petición ya cerrada';
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        /*
        $feature = json_decode($origChangerequest->feature, true);
        $geom = Geometry::fromJson($origChangerequest->feature);*/
        if (!empty($request->action_cancel)) {
            // FIXME: we need a more meaningful name
            if ($request->user()->id != $origChangerequest->requested_by_id) {
                $message = 'El usuario intentó cancelar una petición que no inició: '.$request->user()->email;
                Log::error($message);
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'user' => [$message],
                ]);
                throw $error;
            }
            ChangeRequest::setCancelled($origChangerequest, $request->user());
            return redirect()->route('changerequests.index');
        }
        if (!$request->user()->isAdmin()) {
            $message = 'Un usuario no-administrador intentó modificar una petición: '.$request->user()->email;
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        
        if (!empty($request->action_validate)) {
            
            /*
            ChangeRequest::applyValidatedChangeRequest(
                $origChangerequest->layer,
                $origChangerequest->operation,
                $feature, $geom);*/
            // FIXME: we need a more meaningful name
            ChangeRequest::setValidated($origChangerequest, $request->user());
        }
        elseif (!empty($request->action_reject)) {
            // FIXME: we need a more meaningful name
            ChangeRequest::setRejected($origChangerequest, $request->user());
        }
        return redirect()->route('changerequests.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChangeRequest $changeRequest)
    {
        //
    }
}
