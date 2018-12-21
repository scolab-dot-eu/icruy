<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DomainValue extends Model
{
    protected $table = 'domainvalues';
    
    protected $fillable = [
        'table', 'field', 'code', 'def'
    ];
    
}
