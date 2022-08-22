<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ATKSSUBCON extends Model
{
    // protected $connection= 'mysql';

    protected $table = 'tbl_subcon_time_in_out';

    protected $fillable = [
    	'empno',
    	'empname',
    	'department',
        'plate_no',
    	'date',
    	'time',
    	'status',
    	'created_at',
    	'updated_at'
    ];
}
