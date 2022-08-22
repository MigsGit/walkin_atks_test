<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AtksCanteenInOut extends Model
{
    protected $table = 'atks_canteen_in_outs';

    protected $fillable = [
        'empno', 'empname', 'plate_no','date','time','status'
    ];
}
