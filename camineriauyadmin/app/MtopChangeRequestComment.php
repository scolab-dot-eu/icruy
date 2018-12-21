<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MtopChangeRequestComment extends Model
{
    protected $table = 'mtopchangerequestcomments';
    
    public function changeRequest()
    {
        return $this->belongsTo('App\MtopChangeRequest');
    }
}
