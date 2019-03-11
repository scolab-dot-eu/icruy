<?php

namespace App\Mail;

use App\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangeRequestCreated extends Mailable
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
        ->subject('ICR - Nueva petición - '.$this->changeRequest->id)
        ->markdown('emails.changerequest.created')
        ->with([
            'departamento'=>$this->changeRequest->departamento,
            'layer'=>$this->changeRequest->layer,
            'operation'=>$this->changeRequest->operationLabel,
            'changeRequestUrl' => $changeRequestUrl
        ]);
    }
}
