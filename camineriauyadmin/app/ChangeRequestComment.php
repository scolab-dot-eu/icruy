<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangeRequestComment extends Model
{
    protected $table = 'changerequestcomments';
    
    public function changeRequest()
    {
        return $this->belongsTo('App\ChangeRequest');
    }
}
