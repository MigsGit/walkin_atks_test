<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlateNumber extends Model
{
    protected $table = 'plate_no_management';

    protected $fillable = [
        'empno', 'empname', 'plate_no',
    ];
}
