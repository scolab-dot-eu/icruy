<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\Department;
use App\MtopChangeRequest;
use App\Role;
use App\User;
use App\Mail\MtopChangeRequestUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\MtopChangeRequestComment;

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
    
    protected function addComment(MtopChangeRequest $changeRequest, $message, User $user) {
        $comment = new MtopChangeRequestComment();
        $comment->message = $message;
        $comment->updated_at = now();
        $comment->created_at = now();
        $comment->user_id = $user->id;
        $changeRequest->comments()->save($comment);
        return $comment;
    }
    
    protected function sendNotification(MtopChangeRequest $changerequest, User $requestorUser, $newComment = null) {
        $notification = new MtopChangeRequestUpdated($changerequest, $newComment);
        $notification->onQueue('email');
        if ($requestorUser->isMtopManager()) {
            Mail::to($changerequest->author)->queue($notification);
        }
        else {
            $admins = Role::mtopManagers()->first()->users()->get();
            Mail::to($admins)->queue($notification);
        }
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(MtopChangeRequest $mtopchangerequest)
    {
        $comments = $mtopchangerequest->comments()->with('user')->get();
        
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

        $camineria_wfs_url = env('CAMINERIA_WMS_URL', ViewerConfigApiController::CAMINERIA_DEFAULT_WFS_URL);
        $dep_code = $mtopchangerequest->departamento;
        $gid = $mtopchangerequest->feature_id;
        $dep = Department::where('code', $dep_code)->first();
        $currentFeatureUrl = $camineria_wfs_url . "?service=WFS&version=1.0.0&request=getFeature&typeName=".$dep->layer_name."&outputFormat=application/json&Filter=<Filter><PropertyIsEqualTo><PropertyName>gid</PropertyName><Literal>".$gid."</Literal></PropertyIsEqualTo></Filter>";
        return view('mtopchangerequest.edit', ['mtopchangerequest'=>$mtopchangerequest,
            'previousFeature'=>$previousFeature,
            'proposedFeature'=>$proposedFeature,
            'comments'=>$comments,
            'currentFeatureUrl'=>$currentFeatureUrl
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ChangeRequest  $changeRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MtopChangeRequest $mtopchangerequest)
    {
        $user = $request->user();
        $newComment = $request->input('newcomment');
        
        if (!empty($request->action_comment)) {
            $this->addComment($mtopchangerequest, $newComment, $user);
            $this->sendNotification($mtopchangerequest, $user, $newComment);
            return redirect()->to(route('mtopchangerequests.edit', $mtopchangerequest->id).'#theComments');
        }
        if (!$mtopchangerequest->isOpen) {
            $message = 'Se intentó modificar una petición ya cerrada';
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        if (!empty($request->action_cancel)) {
            // FIXME: we need a more meaningful name
            if ($user->id != $mtopchangerequest->requested_by_id) {
                $message = 'El usuario intentó cancelar una petición que no inició: '.$request->user()->email;
                Log::error($message);
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'user' => [$message],
                ]);
                throw $error;
            }
            MtopChangeRequest::setCancelled($mtopchangerequest, $user);
            $this->sendNotification($mtopchangerequest, $user, $newComment);
            return redirect()->route('mtopchangerequests.edit', $mtopchangerequest->id);
        }
        if (!$user->isMtopManager()) {
            $message = 'Un usuario no-administrador MTOP intentó modificar una petición: '.$request->user()->email;
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        
        if (!empty($newComment)) {
            $this->addComment($mtopchangerequest, $newComment, $user);
        }
        if (!empty($request->action_validate)) {
            MtopChangeRequest::setValidated($mtopchangerequest, $user);
        }
        elseif (!empty($request->action_reject)) {
            // FIXME: we need a more meaningful name
            MtopChangeRequest::setRejected($mtopchangerequest, $user);
        }
        try {
            $mtopchangerequest = $mtopchangerequest->fresh();
            $this->sendNotification($mtopchangerequest, $user, $newComment);
        }
        catch(\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex);
        }
        
        return redirect()->route('mtopchangerequests.edit', $mtopchangerequest->id);
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
