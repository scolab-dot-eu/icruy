<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name', 'code', 'minx', 'maxx', 'miny', 'maxy'
    ];
    
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
    
    public function editableLayers()
    {
        return $this->belongsToMany('App\EditableLayer');
    }
}
