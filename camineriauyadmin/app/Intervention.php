<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    protected $fillable = [
        'anyo_interv', 'departamento', 'codigo_camino', 'tipo_elem', 'id_elem',
        'longitud', 'tarea', 'financiacion', 'forma_ejecucion' 
    ];
    
    /**
     * Get the changeRequests for the user.
     */
    public function partialCertificate()
    {
        return $this->hasMany('App\PartialCertificate', 'id_intervencion');
    }
    
}
