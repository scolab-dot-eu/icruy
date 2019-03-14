<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MtopChangeRequestComment extends Model
{
    protected $table = 'mtopchangerequestcomments';
    
    public function changeRequest()
    {
        return $this->belongsTo('App\MtopChangeRequest', 'mtopchangerequest_id');
    }
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function getCreatedAtFormattedAttribute(){
        return Carbon::parse($this->created_at)->format('d/m/Y H:i:s');
    }
}
