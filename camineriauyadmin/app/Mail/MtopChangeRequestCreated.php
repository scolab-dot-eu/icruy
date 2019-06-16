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
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MtopChangeRequest $mtopChangeRequest)
    {
        $this->mtopChangeRequest = $mtopChangeRequest;
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
        return $this->from('icr@opp.gub.uy')
                ->subject('ICR - Nueva petición MTOP - '.$this->mtopChangeRequest->id)
                ->markdown('emails.mtopchangerequest.created')
                ->attachData($this->mtopChangeRequest->feature, $fileName,  ['mime'=>'application/json'])
                ->with([
                    'changeRequestUrl' => $changeRequestUrl
                ]);
    }
}
