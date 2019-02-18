<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\MtopChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MtopChangeRequestController extends Controller
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
            $data = MtopChangeRequest::open();
        }
        elseif ($status=='pending') {
            $data = MtopChangeRequest::where('status', ChangeRequest::STATUS_PENDING);
        }
        elseif ($status=='all') {
            $data = MtopChangeRequest::with(['author', 'validator']);
        }
        elseif ($status=='closed') {
            $data = MtopChangeRequest::closed();
        }
        elseif ($status=='admininfo') {
            $data = MtopChangeRequest::where('status', ChangeRequest::STATUS_ADMININFO);
        }
        elseif ($status=='userinfo') {
            $data = MtopChangeRequest::where('status', ChangeRequest::STATUS_USERINFO);
        }
        elseif ($status=='validated') {
            $data = MtopChangeRequest::where('status', ChangeRequest::STATUS_VALIDATED);
        }
        elseif ($status=='rejected') {
            $data = MtopChangeRequest::where('status', ChangeRequest::STATUS_REJECTED);
        }
        elseif ($status=='cancelled') {
            $data = MtopChangeRequest::where('status', ChangeRequest::STATUS_CANCELLED);
        }
        else {
            $data = MtopChangeRequest::open();
        }
        if (!$request->user()->isAdmin()) {
            $data = $data->where('requested_by_id', $request->user()->id);
        }
        $data->with(['author', 'validator'])->orderBy('updated_at', 'desc');
        return view('mtopchangerequest.index', ['changerequests' => $data->get()]);
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
    public function show(MtopChangeRequest $changerequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(MtopChangeRequest $changerequest)
    {
        //$previousFeature = json_decode($changerequest->feature_previous);
        $previousFeature = null;
        if ($changerequest->operation == ChangeRequest::OPERATION_DELETE) {
            $proposedFeature = null;
        }
        else {
            $proposedFeature = json_decode($changerequest->feature);
        }

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
        $origChangerequest = MtopChangeRequest::findOrFail($id);
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
            MtopChangeRequest::setCancelled($origChangerequest, $request->user());
            return redirect()->route('mtopchangerequests.index');
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
            MtopChangeRequest::setValidated($origChangerequest, $request->user());
        }
        elseif (!empty($request->action_reject)) {
            // FIXME: we need a more meaningful name
            MtopChangeRequest::setRejected($origChangerequest, $request->user());
        }
        return redirect()->route('mtopchangerequests.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(MtopChangeRequest $changeRequest)
    {
        //
    }
}
