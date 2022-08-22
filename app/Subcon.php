<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subcon extends Model
{
    protected $table = 'tbl_subcon_employees';

    protected $fillable = [
    	'empno',
    	'empname',
    	'agency',
    	'department',
    	'address',
    	'created_at',
    	'updated_at'
    ];
    
}
