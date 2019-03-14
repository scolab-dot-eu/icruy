<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\MtopChangeRequest;
use App\Mail\MtopChangeRequestUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        if (!$request->user()->isMtopManager() && !$request->user()->isAdmin()) {
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
    public function edit(MtopChangeRequest $mtopchangerequest)
    {
        $user = request()->user();
        if ((!$user->isMtopManager()) && (!$user->isAdmin()) &&
            ($user->id != $mtopchangerequest->requested_by_id)) {
                abort(403, 'Acceso no autorizado');
        }
        
        $previousFeature = json_decode($mtopchangerequest->feature_previous);
        $previousFeature = null;
        if ($mtopchangerequest->operation == ChangeRequest::OPERATION_DELETE) {
            $proposedFeature = null;
        }
        else {
            $proposedFeature = json_decode($mtopchangerequest->feature);
        }

        return view('mtopchangerequest.edit', ['mtopchangerequest'=>$mtopchangerequest,
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
        $origChangerequest = MtopChangeRequest::findOrFail($id);
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
        $user = $request->user();
        if (!empty($request->action_cancel)) {
            // FIXME: we need a more meaningful name
            if ($user->id != $origChangerequest->requested_by_id) {
                $message = 'El usuario intentó cancelar una petición que no inició: '.$request->user()->email;
                Log::error($message);
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'user' => [$message],
                ]);
                throw $error;
            }
            MtopChangeRequest::setCancelled($origChangerequest, $user);
            return redirect()->route('mtopchangerequests.index');
        }
        if (!$user->isMtopManager()) {
            $message = 'Un usuario no-administrador MTOP intentó modificar una petición: '.$request->user()->email;
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        
        if (!empty($request->action_validate)) {
            MtopChangeRequest::setValidated($origChangerequest, $user);
        }
        elseif (!empty($request->action_reject)) {
            // FIXME: we need a more meaningful name
            MtopChangeRequest::setRejected($origChangerequest, $user);
        }
        try {
            $origChangerequest = $origChangerequest->fresh();
            $notification = new MtopChangeRequestUpdated($origChangerequest);
            $notification->onQueue('email');
            Mail::to($origChangerequest->author)->queue($notification);
        }
        catch(\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex);
        }
        
        return redirect()->route('mtopchangerequests.index');
    }

    public function feature(Request $request, $id)
    {
        $mtopChangeRequest = MtopChangeRequest::findOrFail($id);
        $user = $request->user();
        if ((!$user->isMtopManager()) && (!$user->isAdmin()) &&
            ($user->id != $mtopChangeRequest->requested_by_id)) {
                abort(403, 'Acceso no autorizado');
            }
        
        return response($mtopChangeRequest->feature, 200)
            ->header('Content-Type', 'application/json');
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
