<?php

namespace App\Http\Controllers;

use App\ChangeRequest;
use App\User;
use App\ChangeRequests\ChangeRequestProcessor;
use App\Mail\ChangeRequestUpdated;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Intervention;
use function GuzzleHttp\json_encode;
use App\ChangeRequestComment;

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
        $comments = $changerequest->comments()->with('user')->get();
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
        return view('changerequest.edit', ['changerequest'=>$changerequest,
            'previousFeature'=>$previousFeature,
            'proposedFeature'=>$proposedFeature,
            'comments'=>$comments
        ]);
    }
    
    protected function addComment($changeRequest, $message, $user) {
        $comment = new ChangeRequestComment();
        $comment->message = $message;
        $comment->updated_at = now();
        $comment->created_at = now();
        $comment->user_id = $user->id;
        $changeRequest->comments()->save($comment);
        return $comment;
    }
    
    protected function sendNotification(ChangeRequest $origChangerequest, User $requestorUser, $newComment = null) {
        $notification = new ChangeRequestUpdated($origChangerequest, $newComment);
        $notification->onQueue('email');
        if ($requestorUser->isAdmin()) {
            Mail::to($origChangerequest->author)->queue($notification);
        }
        else {
            $admins = Role::admins()->first()->users()->get();
            Mail::to($admins)->queue($notification);
        }
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
        $user = $request->user();
        $newComment = $request->input('newcomment');
        Log::debug('all');
        Log::debug(json_encode($request->all()));
        if (!empty($request->action_comment)) {
            $this->addComment($origChangerequest, $newComment, $user);
            $this->sendNotification($origChangerequest, $user, $newComment);
            return redirect()->to(route('changerequests.edit', $origChangerequest->id).'#theComments');
        }
        if (!$origChangerequest->isOpen) {
            $message = 'Se intentó modificar una petición ya cerrada';
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        $changeRequestProcessor = ChangeRequestProcessor::getProcessor($origChangerequest->layer);

        if (!empty($request->action_cancel)) {
            // FIXME: we need a more meaningful name
            if ($user->id != $origChangerequest->requested_by_id) {
                $message = 'El usuario intentó cancelar una petición que no inició: '.$user->email;
                Log::error($message);
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'user' => [$message],
                ]);
                throw $error;
            }
            if (!empty($request->newcomment)) {
                $this->addComment($origChangerequest, $request->newcomment, $user);
            }
            $changeRequestProcessor->setCancelled($origChangerequest, $request->user());
            $this->sendNotification($origChangerequest, $user, $newComment);
            return redirect()->route('changerequests.edit', $origChangerequest->id);
        }
        if (!$user->isAdmin()) {
            $message = 'Un usuario no-administrador intentó modificar una petición: '.$user->email;
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
        
        if (!empty($newComment)) {
            $this->addComment($origChangerequest, $newComment, $user);
        }
        if (!empty($request->action_validate)) {
            // FIXME: we need a more meaningful name
            $changeRequestProcessor->setValidated($origChangerequest, $user);
        }
        elseif (!empty($request->action_reject)) {
            // FIXME: we need a more meaningful name
            $changeRequestProcessor->setRejected($origChangerequest, $user);
        }
        
        try {
            $origChangerequest = $origChangerequest->fresh();
            $this->sendNotification($origChangerequest, $user, $newComment);
        }
        catch(\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex);
        }
        
        return redirect()->route('changerequests.edit', $origChangerequest->id);
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
