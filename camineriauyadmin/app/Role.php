<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 'desc'
    ];
    
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public static function getAdminRoleName( ) {
        return 'admin';
    }
    
    public static function getManagerRoleName( ) {
        return 'manager';
    }
    
    public static function getMtopManagerRoleName( ) {
        return 'mtopManager';
    }
    
    
    public function scopeMtopManagers($query)
    {
        return $query->where('name', Role::getMtopManagerRoleName());
    }
    
    public function scopeManagers($query)
    {
        return $query->where('name', Role::getManagerRoleName());
    }
    
    public function scopeAdmins($query)
    {
        return $query->where('name', Role::getAdminRoleName());
    }
}
