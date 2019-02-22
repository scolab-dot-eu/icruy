<?php

namespace App\Mail;

use App\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeRequestCreated extends Mailable
{
    use Queueable, SerializesModels;
    public $changeRequest;

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
        return $this->from('icr@opp.gob.uy')->markdown('emails.changerequest.created')->attachData($data, $name);
        
    }
}
