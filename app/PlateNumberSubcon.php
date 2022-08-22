<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlateNumberSubcon extends Model
{
    protected $table = 'tbl_subcon_plate_number';

    protected $fillable = [
        'id','empno', 'empname','department','plate_no','date','time',
    ];

}
