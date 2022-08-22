<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ATKS extends Model
{
    protected $table = 'tbl_hris_time_in_out';

    //protected $connection='mysql';
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
