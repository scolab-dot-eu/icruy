<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartialCertificate extends Model
{
    protected $table = 'partialcertificates';
    
    /**
     * The intervention that owns the certificate.
     */
    public function interventions()
    {
        return $this->belongsTo('App\Intervention', 'id_intervencion');
    }
}
