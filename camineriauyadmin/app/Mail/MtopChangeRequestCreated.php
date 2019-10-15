<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\MtopChangeRequest;

class MtopChangeRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $mtopChangeRequest;
    protected $selfNotification;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MtopChangeRequest $mtopChangeRequest, bool $selfNotification)
    {
        $this->mtopChangeRequest = $mtopChangeRequest;
        $this->selfNotification = $selfNotification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fileName = 'feature-'.$this->mtopChangeRequest->codigo_camino;
        if ($this->mtopChangeRequest->feature_id!=null) {
            $fileName = $fileName.'-'.$this->mtopChangeRequest->feature_id;
        }
        $fileName = $fileName.".geojson";
        
        $changeRequestUrl = route('mtopchangerequests.edit', $this->mtopChangeRequest->id);
        $from = env("MAIL_FROM_ADDRESS", "icr@opp.gub.uy");
        
        if ($this->selfNotification) {
            $markdown = 'emails.mtopchangerequest.createdself';
        }
        else {
            $markdown = 'emails.mtopchangerequest.created';
        }
        return $this->from($from)
                ->subject('ICR - Nueva petición MTOP - '.$this->mtopChangeRequest->id)
                ->markdown($markdown)
                ->attachData($this->mtopChangeRequest->feature, $fileName,  ['mime'=>'application/json'])
                ->with([
                    'changeRequestUrl' => $changeRequestUrl
                ]);
    }
}
