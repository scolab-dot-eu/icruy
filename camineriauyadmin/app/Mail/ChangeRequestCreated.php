<?php

namespace App\Mail;

use App\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChangeRequestCreated extends Mailable
{
    use Queueable, SerializesModels;
    protected $changeRequest;
    protected $selfNotification;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ChangeRequest $changeRequest, bool $selfNotification)
    {
        $this->changeRequest = $changeRequest;
        $this->selfNotification = $selfNotification;
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
        if ($this->selfNotification) {
            $markdown = 'emails.changerequest.createdself';
        }
        else {
            $markdown = 'emails.changerequest.created';
        }
        return $this->from($from)
        ->subject('ICR - Nueva petición - '.$this->changeRequest->id)
        ->markdown($markdown)
        ->with([
            'departamento'=>$this->changeRequest->departamento,
            'layer'=>$this->changeRequest->layer,
            'operation'=>$this->changeRequest->operationLabel,
            'changeRequestUrl' => $changeRequestUrl
        ]);
    }
}
