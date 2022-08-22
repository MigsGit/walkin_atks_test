<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ATKSEMPLOYEE extends Model
{
    protected $connection = 'mysql_hris';

    protected $table = 'tbl_EmployeeInfo';
}
