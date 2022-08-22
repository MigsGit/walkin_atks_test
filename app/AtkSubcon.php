<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AtkSubcon extends Model
{
    protected $connection = 'mysql_subcon';

    protected $table = 'tbl_EmployeeInfo';
    public function department_info(){
        return $this->hasOne(ATKSSECTION::class, 'pkid', 'fkDepartment');
    }

}
