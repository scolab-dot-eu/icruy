<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Camino extends Model
{
    public const LAYER_NAME = 'cr_caminos';
    protected $table = Camino::LAYER_NAME;
    
    protected $fillable = [
        'departamento', 'codigo_camino'
    ];
}
