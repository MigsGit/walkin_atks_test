<?php

namespace App\Imports;

use App\Subcon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Auth;


class SubconEmpImport implements ToModel
{
    /**
    * @param array $row
    * @param Collection $collection
    */
    public function model(array $row)
    {
        return new subconemps([
            'empname' => $row[0],        	
        	'empno' => $row[1],
            'agency' => $row[2],
            'department' => $row[3],
            'address' => $row[4],
        ]);
    }
}
