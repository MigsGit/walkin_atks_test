<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\PlateNumber;
use App\ATKSHRIS;
use App\AtkSubcon;
use App\ATKSSUBCON;
use App\ATKS;
use App\PlateNumberSubcon;
use App\CanteenStaffList;
use DataTables;
use QrCode;
use Illuminate\Support\Str;
class EmployeeController extends Controller
{
//=================VIEW THE MAIN DATA FOR HRIS AND SUBCON===================
public function show(Request $request){

    $employee=PlateNumber::where('logdel',0)->get();
    $subcon=PlateNumberSubcon::where('logdel',0)->get();
    $data_length=Platenumber::select('plate_no')->whereNotNull('empno')->where('logdel',0)->get();

    $datas = $employee->toBase()->merge($subcon); // REMINDER:TO MERGE DATATABLES OF HRIS AND SUBCON
    return DataTables::of($datas)

    ->addColumn('action', function ($employee) { //Need to have same column name and data name
        $result = '<center>';

        $result .= '<button class="btn btn-primary btn-sm actionEditEmp" type="button" user-id="' . $employee->empno . '" status="1" data-toggle="modal" data-target="#modalEditEmp" data-keyboard="false"><i class="fas fa-user-edit"></i></button>';

        $result .= '<button type="button" class="btn btn-danger btn-sm ml-1 px-2 actionDeleteEmp" user-id="' . $employee->empno . '" data-toggle="modal" data-target="#modalDeleteEmp" data-keyboard="false">
                            <i class="fas fa-24px fa-user-minus"></i>
                        </button>';
        '</center>';
        return $result;
    })
    ->addColumn('plate_no',function($employee){ //MODIFY: WHEN PLATE_NO >=7 ITS MEANS THIS IS FILE NO.
        $result = "";

        if(strlen($employee->plate_no) >=21){ //LIVE:
            $result .= '<div style="color: green;  "> '.$employee->plate_no.'  </div>';
        }else if( strlen($employee->plate_no) >=9 && strlen($employee->plate_no) <=19 ){

            $result .= '<div style="color: red;  "> '.$employee->plate_no.'  </div>';
        }else if( strlen($employee->plate_no) >=6 && strlen($employee->plate_no) <=8 ) {
            $result .=' <div style="color: green; ">  '.$employee->plate_no.'  </div>';
        }
        else{
            $result .= '<div style="color: green;  "> '.$employee->plate_no.'  </div>';
        }
        return $result;

    })
    ->rawColumns(['action','plate_no'])
    ->make(true);

}//END SHOW


//=================GET EDIT FOR HRIS & SUBCON===================
public function get_emp_by_id(Request $request)
{
    $employeeno=$request->empno;

    if(PlateNumber::where('empno', $employeeno)->exists()){ // GET EDIT FOR HRIS
                                                            // get all users where id is equal to the user-id attribute of the dropdown-item of actions dropdown(Edit)
        $employee = PlateNumber::where('empno', $request->empno)->get(); // pass the $user(variable) to ajax as a response for retrieving and pass the values on the inputs
        return response()->json(['employee' => $employee]);
    }else{
        $employee = PlateNumberSubcon::where('empno', $request->empno)->get(); // GET EDIT FOR SUBCON
        return response()->json(['employee' => $employee]);
    }

}
//=================UPDATE THE DATA FOR HRIS AND SUBCON===================

public function edit_emp(Request $request){

    date_default_timezone_set('Asia/Manila');
    session_start();
    $employeeno=$request->empno;

    if(PlateNumber::where('empno', $employeeno)->exists()){ // UPDATE THE DATA OF HRIS
        $employee = $request->all(); // collect all input fields
    // return $employee;
        $validator = Validator::make($employee, [
            'empno' => 'required|string|max:255',
            'empname' => 'required|string|max:255',
            'plate_no' => 'required|string|max:255|unique:plate_no_management',

        ]);
        if ($validator->fails()) {
            return response()->json(['validation' => 'hasError', 'error' => $validator->messages()]);
        } else {

            PlateNumber::where('empno', $request->empno)
                    ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                        'empname' => $request->empname,
                        'plate_no' => $request->plate_no,
                        // 'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                /*DB::commit();*/
                return response()->json(['result' => "1"]);
        }

    }else{ // UPDATE THE DATA OF SUBCON

        $employee = $request->all(); // collect all input fields
        // return $employee;
        $validator = Validator::make($employee, [
            'empno' => 'required|string|max:255',
            'empname' => 'required|string|max:255',
            'plate_no' => 'required|string|max:255|unique:tbl_subcon_plate_number',

        ]);
        if ($validator->fails()) {
            return response()->json(['validation' => 'hasError', 'error' => $validator->messages()]);
        } else {

            PlateNumberSubcon::where('empno', $request->empno)
                    ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                        'empname' => $request->empname,
                        'plate_no' => $request->plate_no,
                        // 'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                /*DB::commit();*/
                return response()->json(['result' => "1"]);

        }

    }
}

//=================DELETE THE DATA FOR HRIS AND SUBCON===================
public function delete_emp(Request $request){

    date_default_timezone_set('Asia/Manila');
    session_start();
    $employeeno=$request->empno;
    if(PlateNumber::where('empno', $employeeno)->exists()){ // DELETE THE DATA OF HRIS

    $employee = $request->all(); // collect all input fields

    try {
        PlateNumber::where('empno', $request->empno)
            ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                'logdel' => 1, // deleted
                //'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        /*DB::commit();*/

        return response()->json(['result' => "1"]);
    } catch (\Exception $e) {
        DB::rollback();
        // throw $e;
        return response()->json(['result' => "0", 'tryCatchError' => $e->getMessage()]);
    }

    }else{

        $employee = $request->all(); // collect all input fields

        try {
            PlateNumberSubcon::where('empno', $request->empno)
                ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                    'logdel' => 1, // deleted
                    //'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            /*DB::commit();*/

            return response()->json(['result' => "1"]);
        } catch (\Exception $e) {
            DB::rollback();
            // throw $e;
            return response()->json(['result' => "0", 'tryCatchError' => $e->getMessage()]);
        }

    }
}
//=================VIEW THE ARCHIVE DATA FOR HRIS AND SUBCON===================

public function show_archive(Request $request){


    $employee=PlateNumber::where('logdel',1)->get();
    $subcon=PlateNumberSubcon::where('logdel',1)->get();
    $datas = $employee->toBase()->merge($subcon); // TO MERGE DATATABLES

        return DataTables::of($datas)
        ->addIndexColumn()
        ->addColumn('action', function ($employee) { //Need to have same column name and data name
            $result = '<center> ';
            $result .= '<button class="btn btn-success btn-sm actionRestoreEmp" type="button" user-id="' . $employee->empno . '" data-toggle="modal" data-target="#modalRestoreEmp" data-keyboard="false"><i class="fas fa-sync-alt"> Restore</button>';
            '</center>';
            return $result;
        })
        ->addColumn('plate_no',function($employee){ //MODIFY: WHEN PLATE_NO >=7 ITS MEANS THIS IS FILE NO.
            $result = "";

            if(strlen($employee->plate_no) >=21){ //LIVE:
                $result .= '<div style="color: green;  "> '.$employee->plate_no.'  </div>';
            }else if( strlen($employee->plate_no) >=9 && strlen($employee->plate_no) <=19 ){

                $result .= '<div style="color: red;  "> '.$employee->plate_no.'  </div>';
            }else if( strlen($employee->plate_no) >=6 && strlen($employee->plate_no) <=8 ) {
                $result .=' <div style="color: green; ">  '.$employee->plate_no.'  </div>';
            }
            else{
                $result .= '<div style="color: green;  "> '.$employee->plate_no.'  </div>';
            }
            return $result;

        })
        ->rawColumns(['action','plate_no'])
        ->make(true);

}
//=================RESTORE DATA FOR HRIS AND SUBCON===================

public function restore_emp(Request $request){

    date_default_timezone_set('Asia/Manila');
    session_start();

    $employeeno=$request->empno;
    if(PlateNumber::where('empno', $employeeno)->exists()){
        $employee=$request->all();
        try{
            PlateNumber::where('empno',$request->empno)->update([
                'logdel'=>0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return response()->json(['result'=>"1"]);

        }catch(Exception $e){

        }
    }else{
        $employee=$request->all();
        try{
            PlateNumberSubcon::where('empno',$request->empno)->update([
                'logdel'=>0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return response()->json(['result'=>"1"]);

        }catch(Exception $e){
        }

    }
}

//=================GET THE EMPLOYEE INFO AND SAVE IT TO HRIS AND SUBCON===================

public function get_emp_info(Request $request){
    $data=ATKSHRIS::with(['department_info'])->where('empno',$request->empno)->first();
    $sub_data=AtkSubcon::with(['department_info'])->where('empno',$request->empno)->first(); //REMINDER SUBCON
    // $data = SystemOneHRIS::with(['department_info', 'section_info', 'position_info'])->where('EmpNo', $request->emp_no)->first();
    // json_encode($employee);
    // dd($data->no);


    if(ATKSHRIS::with(['department_info'])->where('empno',$request->empno)->exists()){
        return response()->json(['data' => $data]);

    }else{ //REMINDER SUBCON
        return response()->json(['data' => $sub_data]);
    }
}
//=================GET THE EMPLOYEE INFO AND SAVE IT TO HRIS AND SUBCON===================
public function add_emp(Request $request){
    date_default_timezone_set('Asia/Manila');

    $employeeno=$request->empno;

    if(ATKSHRIS::where('empno', $employeeno)->exists()){ //IF EMPLOYEE NUMBER IS EXISTED
        $data=$request->all();

        //1. FIRST CHECK THE VALIDATIONS
        //2. IF DATA IS VALID PROCEED TO ADD FUNCTIONS ===HRIS===
            $rules=[
            'empno'=>'required|string|max:255|unique:plate_no_management', //REMIDERS: NOT SHOWING THE ERRORS
            'empname'=>'required|string|max:255|unique:plate_no_management',
            'plate_no'=>'required|string|max:30|unique:plate_no_management'
            ];


            $validator=Validator::make($data,$rules);

            if ($validator->fails()) {
                return response()->json(['validation' => 'hasError', 'error' => $validator->messages()]);
            }else{

                // return 'Hello';

                $data = PlateNumber::insert([
                    'empno' => $request->empno,
                    'empname' => $request->empname,
                    'plate_no' => $request->plate_no,
                    'logdel' => 0, // 0 is default (active)
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return response()->json(['result' => "1"]);
            }
    }else{

            // return 'HELLO';
            //1. ELSE PROCEED TO THE SUBCON DATABASE
            //2. IF DATA IS VALID PROCEED TO ADD FUNCTIONS
            //3. FIRST CHECK THE VALIDATIONS
            //4. IF DATA IS VALID PROCEED TO ADD FUNCTIONS ===SUBCON===

            $data=$request->all();
            // return $data;


            $rules=[
            'empno'=>'required|string|max:255|unique:tbl_subcon_plate_number', //REMIDERS: NOT SHOWING THE ERRORS
            'empname'=>'required|string|max:255|unique:tbl_subcon_plate_number',
            'plate_no'=>'required|string|max:255|unique:tbl_subcon_plate_number'
            ];


            $validator=Validator::make($data,$rules);

            if ($validator->fails()) {
                return response()->json(['validation' => 'hasError', 'error' => $validator->messages()]);
            }else{

                // return 'Hello';

                $data = PlateNumberSubcon::insert([
                    'empno' => $request->empno,
                    'empname' => $request->empname,
                    'plate_no' => $request->plate_no,
                    'logdel' => 0, // 0 is default (active)
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return response()->json(['result' => "1"]);
            }
    }
}


//================= TABLE: GET THE EMPLOYEE INFO AND SAVE IT TO HRIS AND SUBCON===================

public function get_pmi_riders(Request $request){

    $request->date = Carbon::now()->format('Y-m-d');

    $data_hris=PlateNumber::whereNotNull('plate_no')->where('logdel',0)->count();
    $data_subcon=PlateNumberSubcon::whereNotNull('plate_no')->where('logdel',0)->count();

    $data= $data_hris+$data_subcon ;


    return response()->json(['result'=>$data]);
}

public function get_hris_riders(Request $request){

    $request->date = Carbon::now()->format('Y-m-d');

    $data_hris=PlateNumber::whereNotNull('plate_no')->where('logdel',0)->count();

    return response()->json(['result'=>$data_hris]);
}
public function get_subcon_riders(Request $request){

    $request->date = Carbon::now()->format('Y-m-d');

    $data_subcon=PlateNumberSubcon::whereNotNull('plate_no')->where('logdel',0)->count();

    return response()->json(['result'=>$data_subcon]);
}


public function get_all_pmi_riders(Request $request){

    $request->date = Carbon::now()->format('Y-m-d');

    $data_all_pmi=ATKS::whereNotNull('plate_no')->where('status',1)->where("date", $request->date )->count();


    return response()->json(['result'=>$data_all_pmi]);
}

public function get_all_sub_riders(Request $request){ //DITO AKO NATAPOS

    $request->date = Carbon::now()->format('Y-m-d');

    $data_all_sub=ATKSSUBCON::whereNotNull('plate_no')->where('status',1)->where("date", $request->date )->count();

    return $data_all_sub;
    return response()->json(['result'=>$data_all_sub]);
}




//=================VIEW THE MAIN DATA FOR CANTEEN===================
public function show_canteen(Request $request){
    $data_canteen=CanteenStaffList::where('logdel',0)->get();


    return DataTables::of($data_canteen)

    ->addColumn('action', function ($data) { //Need to have same column name and data name
        $result = '<center>';

        $result .= '<button class="btn btn-primary btn-sm actionEditCanteen" type="button" user-id="' . $data->id . '" status="1" data-toggle="modal" data-target="#modalEditCanteen" data-keyboard="false"><i class="fas fa-user-edit"></i></button>';

        $result .= '<button type="button" class="btn btn-danger btn-sm ml-1 px-2 actionDeleteEmp" user-id="' . $data->id . '" data-toggle="modal" data-target="#modalDeleteEmp" data-keyboard="false">
                            <i class="fas fa-24px fa-user-minus"></i>
                        </button>';
        '</center>';
        return $result;
    })
    ->addColumn('status',function($data){ //MODIFY: WHEN PLATE_NO >=7 ITS MEANS THIS IS FILE NO.
        $result = "";

        if ($data->logdel == 0){
            // $result .= '<div class="badge badge-success" style="color: green;  "> Active  </div>';
            $result = '<center>';
            $result .= '<span class="badge badge-success" style="background-color: green; "  > Active  </span>';
            '</center>';
        }else{
            $result = '<center>';

            $result .= '<span class="badge badge-success" style="background-color: green; "  > Inactive  </span>';
            '</center>';

        }
        return $result;

    })

    ->addColumn('checkbox', function($data){
        return '<center><input type="checkbox" class="chkUser" user-id="' . $data->id . '"></center>';
    })
    ->rawColumns(['action','status','checkbox'])
    ->make(true);

}


//=================VIEW THE ARCHIVE DATA FOR CANTEEN===================
public function show_archive_canteen(Request $request){
    $data_canteen=CanteenStaffList::where('logdel',1)->get();


    return DataTables::of($data_canteen)

    ->addColumn('action', function ($data) { //Need to have same column name and data name
        $result = '<center>';

        $result .= '<button type="button" class="btn btn-success btn-sm ml-1 px-2 actionRestoreEmp" user-id="' . $data->id . '" data-toggle="modal" data-target="#modalRestoreEmp" data-keyboard="false">
                            Restore <i class="fas fa-24px fa-sync-alt"></i>
                    </button>';
        '</center>';
        return $result;
    })
    ->addColumn('status',function($data){ //MODIFY: WHEN PLATE_NO >=7 ITS MEANS THIS IS FILE NO.
        $result = "";

        if ($data->logdel == 1){
            // $result .= '<div class="badge badge-success" style="color: green;  "> Active  </div>';
            $result = '<center>';
            $result .= '<span class="badge" style="background-color: red; "  > Inactive  </span>';
            '</center>';
        }
        return $result;

    })
    // ->addColumn('checkbox', function($data){
    //     return '<center><input type="checkbox" class="chkEMP" user-id="' . $data->empno . '"></center>';
    // })
    ->rawColumns(['action','status','checkbox'])
    ->make(true);
}

//================= CANTEEN EMPLOYEES===================
public function add_canteen_employee(Request $request){ //ADD
    // return 'true';
    date_default_timezone_set('Asia/Manila');

    $data=$request->all();

    $rules=[
        'empno'=>'required|string|max:255|unique:canteen_staff_lists', //REMIDERS: NOT SHOWING THE ERRORS
        'empname'=>'required|string|max:255|unique:canteen_staff_lists',
        'section'=>'required|string|max:30',
    ];
    $validator=Validator::make($data,$rules);

    if ($validator->fails()) {
            return response()->json(['validation' => 'hasError', 'error' => $validator->messages()]);
    }else{

            // return 'Hello';

            $data = CanteenStaffList::insert([
                'empno' => $request->empno,
                'empname' => $request->empname,
                'section' => $request->section,
                'plate_no' => $request->plate_no,
                'logdel' => 0, // 0 is default (active)

                'created_at' => date('Y-m-d H:i:s')
            ]);

            return response()->json(['result' => "1"]);

}

}

//=================GET THE DATA ===================
public function get_canteen_by_id(Request $request){

        $employee = CanteenStaffList::where('id', $request->canteen_id)->get();
        return response()->json(['employee' => $employee]);

}

//=================UPDATE THE DATA ===================
public function edit_canteen(Request $request){
    date_default_timezone_set('Asia/Manila');

    $data=$request->all();

    $rules=[

        'empno'=>'required|string|max:255', //REMIDERS: NOT SHOWING THE ERRORS
        'empname'=>'required|string|max:255',
        'section'=>'required|string|max:30',
    ];
    $validator=Validator::make($data,$rules);

    if ($validator->fails()) {
        return response()->json(['validation' => 'hasError', 'error' => $validator->messages()]);
    }
    else{
        CanteenStaffList::where('id',$request->canteen_id)
            ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                'empno' => $request->empno,
                'empname' => $request->empname,
                'section' => $request->section,
                'plate_no' => $request->plate_no,
                // 'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            /*DB::commit();*/
        return response()->json(['result' => "1"]);
    }
}

//================= DELETE CANTEEN EMPLOYEES ===================
public function delete_canteen(Request $request){
    date_default_timezone_set('Asia/Manila');
    $employee = $request->all(); // collect all input fields
    CanteenStaffList::where('id', $request->id)
            ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                'logdel' => 1, // deleted
                //'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        /*DB::commit();*/

        return response()->json(['result' => "1"]);
}

public function restore_canteen(Request $request){

    date_default_timezone_set('Asia/Manila');
    $employee = $request->all(); // collect all input fields
    CanteenStaffList::where('id', $request->id)
            ->update([ // The update method expects an array of column and value pairs representing the columns that should be updated.
                'logdel' => 0, // deleted
                //'last_updated_by' => $_SESSION['user_id'], // to track edit operation
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        return response()->json(['result' => "1"]);

}


//=================PRINT BATCH QR CODE ===================
public function  get_user_by_batch(Request $request){
    if($request->user_id == 0){
    $users = CanteenStaffList::all();
    }
    else{
        $users = CanteenStaffList::whereIn('id', $request->user_id)->get();
    }
    $qrcode = [];

    if($users->count() > 0){
        for($index = 0; $index < $users->count(); $index++){
            $qrcode[] = "data:image/png;base64," . base64_encode(QrCode::format('png')
                                ->size(200)->errorCorrection('H')
                                ->generate($users[$index]->empno));
        }
    }

    return response()->json(['users' => $users, 'qrcode' => $qrcode]);
}










}//END OF THE CONTROLLER
