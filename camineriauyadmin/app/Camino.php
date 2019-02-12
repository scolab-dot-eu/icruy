<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Camino extends Model
{
    protected $table = 'cr_caminos';
    
    protected $fillable = [
        'departamento', 'codigo_camino'
    ];
}
