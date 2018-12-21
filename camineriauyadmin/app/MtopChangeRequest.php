<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MtopChangeRequest extends Model
{
    protected $table = 'mtopchangerequests';
    
    protected $fillable = [
    ];
    
    public function author()
    {
        return $this->belongsTo('App\User', 'requested_by');
    }
    
    public function processor()
    {
        return $this->belongsTo('App\User', 'processed_by');
    }
    
    public function comments()
    {
        return $this->hasMany('App\MtopChangeRequestComment');
    }
}
