<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ChangeRequestComment extends Model
{
    protected $table = 'changerequestcomments';
    
    public function changeRequest()
    {
        return $this->belongsTo('App\ChangeRequest', 'changerequest_id');
    }
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function getCreatedAtFormattedAttribute(){
        return Carbon::parse($this->created_at)->format('d/m/Y H:i:s');
    }
}
