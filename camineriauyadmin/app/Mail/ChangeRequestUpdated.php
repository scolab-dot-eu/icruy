<?php

namespace App\Mail;

use App\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeRequestUpdated extends Mailable
{
    use Queueable, SerializesModels;

    protected $changeRequest;
    protected $newComment;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ChangeRequest $changeRequest, $newComment=null)
    {
        $this->changeRequest = $changeRequest;
        $this->newComment = $newComment;
    }
    

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $changeRequestUrl = route('changerequests.edit', $this->changeRequest->id);
        $from = env("MAIL_FROM_ADDRESS", "icr@opp.gub.uy");
        return $this->from($from)
        ->subject('ICR - Petición actualizada - '.$this->changeRequest->id)
        ->markdown('emails.changerequest.updated')
        ->with([
            'departamento'=>$this->changeRequest->departamento,
            'layer'=>$this->changeRequest->layer,
            'operation'=>$this->changeRequest->operationLabel,
            'status'=>$this->changeRequest->statusLabel,
            'changeRequestUrl' => $changeRequestUrl,
            'changeRequestId' => $this->changeRequest->id,
            'newComment' => $this->newComment
        ]);
    }
}
