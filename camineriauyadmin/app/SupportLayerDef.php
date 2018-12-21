<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportLayerDef extends Model
{
    protected $table = 'supportlayerdefs';
    
    protected $fillable = [
        'name',
        'title',
        'isbaselayer',
        'visible',
        'layergroup',
        'url',
        'protocol',
        'api_key',
        'conf'
    ];
}
