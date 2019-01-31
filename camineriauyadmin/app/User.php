<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
    
    /**
     * The roles that belong to the user.
     */
    public function departments()
    {
        return $this->belongsToMany('App\Department');
    }
    
    /**
     * Get the changeRequests for the user.
     */
    public function changeRequests()
    {
        return $this->hasMany('App\ChangeRequest', 'requested_by_id');
    }
    
    /**
     * Get the changeRequests that have been validated by the user.
     */
    public function validatedChangeRequests()
    {
        return $this->hasMany('App\ChangeRequest', 'validated_by_id');
    }
    
    /**
     * Get the mtopChangeRequests for the user.
     */
    public function mtopChangeRequests()
    {
        return $this->hasMany('App\MtopChangeRequest', 'requested_by_id');
    }
    
    /**
     * Get the mtopChangeRequests that have been processed  by the user.
     */
    public function processedMtopChangeRequests()
    {
        return $this->hasMany('App\MtopChangeRequest', 'processed_by_id');
    }
    
    public function authorizeRoles($roles)
    {
        if ($this->hasAnyRole($roles)) {
            return true;
        }
        abort(401, 'Esta acción no está autorizada.');
    }
    
    public function isAdmin() {
        if ($this->roles()->where('name', Role::getAdminRoleName())->first()) {
            return true;
        }
        return false;
    }
    
    
    public function isManager() {
        if ($this->roles()->where('name', Role::getManagerRoleName())->first()) {
            return true;
        }
        return false;
    }
    
    public function isMtopManager() {
        if ($this->roles()->where('name', Role::getMtopManagerRoleName())->first()) {
            return true;
        }
        return false;
    }
    
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
        } else {
            if ($this->hasRole($roles)) {
                return true;
            }
        }
        return false;
    }
    
    public function hasRole($role)
    {
        if ($this->roles()->where('name', $role)->first()) {
            return true;
        }
        return false;
    }
    
    public static function checkPasswordClasses($pass) {
        $lower_case = preg_match("/[a-z]/", $pass);
        $upper_case = preg_match("/[A-Z]/", $pass);
        $digit = preg_match("/[\d]/", $pass);
        $special = preg_match("/[^a-zA-Z0-9]+/", $pass);
        $count = $lower_case + $upper_case + $digit + $special;
        return ($count>=3);
    }
}
