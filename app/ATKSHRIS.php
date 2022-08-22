<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ATKSHRIS extends Model
{
    protected $connection = 'mysql_hris';

    protected $table = 'tbl_EmployeeInfo';

    public function department_info(){
        return $this->hasOne(ATKSSECTION::class, 'pkid', 'fkDepartment');
    }

}
