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
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ChangeRequest $changeRequest)
    {
        $this->changeRequest = $changeRequest;
    }
    

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $changeRequestUrl = route('changerequests.edit', $this->changeRequest->id);
        return $this->from('icr@opp.gub.uy')
        ->subject('ICR - Petición actualizada - '.$this->changeRequest->id)
        ->markdown('emails.changerequest.updated')
        ->with([
            'departamento'=>$this->changeRequest->departamento,
            'layer'=>$this->changeRequest->layer,
            'operation'=>$this->changeRequest->operationLabel,
            'status'=>$this->changeRequest->statusLabel,
            'changeRequestUrl' => $changeRequestUrl
        ]);
    }
}
