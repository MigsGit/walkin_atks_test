<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ATKSRecordsExportView;
use App\Exports\ExportReport;
use Carbon\Carbon;

use App\ATKS;
use App\ATKSHRIS;
use App\ATKSSUBCON;
use App\Subcon;
use App\ATKSSECTION;
use App\PlateNumber; //Reminder
use App\PlateNumberSubcon;
use App\CanteenStaffList;
use App\AtksCanteenInOut;

use App\AtkSubcon;


use DataTables;

class ATKSController extends Controller
{
	public function index(Request $request) //ATKS FOR ALL EMPLOYEES==========================
    {

        // return count($data->plate_no);

        $atks = ATKS::where('empno', $request->employeeno)->orderBy('created_at', 'desc')->limit(3)->get();
        $subcon = ATKSSUBCON::where('EmpNo', $request->employeeno)->orderBy('created_at', 'desc')->limit(3)->get();


        $merge_table = $atks->toBase()->merge($subcon); // REMINDER:TO MERGE DATATABLES OF HRIS AND SUBCON
        return DataTables::of($merge_table)
            ->addColumn('type', function($data){

                $result = "<center>";
                $plate_no=$data->plate_no;
                //REMINDER: TYPE EITHER WALKIN OR RIDERS
                if($plate_no == null){
                    $result .= '<h3> <p style="background-color:rgb(212, 168, 21);color:black;">Walk-In</p></h3>';

                }
                else{
                    $result .= '<h3 <p style="background-color:green;color:white;">Rider</p></h3>';
                }
                    $result .= '</center>';
                    return $result;
            })
            ->addColumn('plate_no',function($employee){ //MODIFY: WHEN PLATE_NO >=7 ITS MEANS THIS IS FILE NO.
                $result = "";

                if(strlen($employee->plate_no) >=21){ //LIVE:
                    $result .=' <center>';
                    $result .=' <div style="color:green; font-weight: bold; "> '.$employee->plate_no.'  </div>';
                    $result .=' </center>';
                }else if( strlen($employee->plate_no) >=9 && strlen($employee->plate_no) <=19 ){
                    $result .=' <center>';
                        $result .= '<div style="color: red;  "> '.$employee->plate_no.'  </div>';
                        $result .=' <div class="badge" style="background-color:red;color:white; font-weight: bold; "> Please Register Your Plate Number! </div>';
                    $result .=' </center>';
                }else if( strlen($employee->plate_no) >=5 && strlen($employee->plate_no) <=8 ) {
                    $result .=' <center>';
                        $result .=' <div style="color: green; font-weight: bold; ">  '.$employee->plate_no.'  </div>';
                        $result .=' </center>';
                }
                else{
                    $result .= '<div style="color: green;  "> '.$employee->plate_no.'  </div>';
                }

                return $result;


            })
            ->addColumn('raw_time_in', function ($data) {
                if ($data->status == 1) return $data->time;
                return '-';
            })
            ->addColumn('raw_time_out', function ($data) {
                if ($data->status == 2) return $data->time;
                return '-';
            })
            ->rawColumns(['raw_time_in', 'raw_time_out','type','plate_no'])
            ->make(true);

    }public function records_hris(Request $request) //REMINDER: ATKS RECORDS FOR ALL EMPLOYEES==========================

    {
            date_default_timezone_set('Asia/Manila');
            $date_now= date('Y-m-d');

            // $request->date = Carbon::now()->format('Y-m-d');
            $atks = ATKS::where('date', $date_now)->orderBy('created_at', 'asc')->get(); // NEED TO ORDER BY
            return DataTables::of($atks)
                    //REMINDER: EXCEL TABLE
                    ->addColumn('type', function($data){

                    $result = "<center>";
                    $plate_no=$data->plate_no;
                    //REMINDER
                    if($plate_no == null){
                        $result .= '<span class="badge" style="background-color:red;color:white;">Walkin</span>';
                    }
                    else{

                        $result .= '<span class="badge " style="background-color:green;color:white;">Rider</span>';

                    }
                        $result .= '</center>';
                        return $result;
                    })

                    ->addColumn('plate_no', function($data){  //REMINDER: PLATE NUMBER
                        if ($data->plate_no !=null ) return $data->plate_no;
                        return '-';
                    })

                    ->addColumn('raw_time_in', function ($data) {
                        if ($data->status == 1) return $data->time;
                        return '-';
                    })
                    ->addColumn('raw_time_out', function ($data) {
                        if ($data->status == 2) return $data->time;
                        return '-';
                    })
                    ->rawColumns(['raw_time_in', 'raw_time_out','type'])
                    ->make(true);


    }public function records_subcon(Request $request)
    {
              date_default_timezone_set('Asia/Manila');
            $date_now= date('Y-m-d');

            // REMINDER: ATKS HRIS IS LIMITED FOR DATE TODAY, TIME IN ONLY, LAST
            // $request->date = Carbon::now()->format('Y-m-d');
            $atks = ATKSSUBCON::where('date',$date_now)->orderBy('created_at', 'desc')->get();
            return DataTables::of($atks)
                    //REMINDER: EXCEL TABLE
                    ->addColumn('type', function($data){
                    $result = "<center>";
                    $plate_no=$data->plate_no;
                    //REMINDER
                    if($plate_no == null){
                        $result .= '<span class="badge" style="background-color:red;color:white;">Walkin</span>';
                    }
                    else{
                        $result .= '<span class="badge " style="background-color:green;color:white;">Rider</span>';
                    }
                        $result .= '</center>';
                        return $result;
                    })
                    ->addColumn('plate_no', function($data){  //REMINDER: PLATE NUMBER
                        if ($data->plate_no !=null ) return $data->plate_no;
                        return '-';
                    })
                    ->addColumn('raw_time_in', function ($data) {
                        if ($data->status == 1) return $data->time;
                        return '-';
                    })
                    ->addColumn('raw_time_out', function ($data) {
                        if ($data->status == 2) return $data->time;
                        return '-';
                    })
                    ->rawColumns(['raw_time_in', 'raw_time_out','type'])
                    ->make(true);


        return view('records')->with('records');

    }

    public function tapin_info(Request $request){ //ATKS FOR ALL EMPLOYEES==========================

            // REMINDER: ATKS SUBCON IS LIMITED FOR DATE TODAY, TIME IN ONLY, ORDER BY DESC
        $employee_info = ATKS::where('empno', $request->employeeno)->orderBy('created_at', 'desc')->get();

        return DataTables::of($employee_info)
        ->addColumn('empno',function($emp){
            $result = $emp->empno;

            return $result;
        })
        ->addColumn('empname',function($emp){
            $result = $emp->empname;

            return $result;

        })

        ->addColumn('department',function($emp){
            $result = $emp->department;

            return $result;

        })

        ->addColumn('plate_no',function($emp){
            $result = $emp->department;

            return $result;
        // REMINDER
        })
        ->addColumn('type', function($employee){
        $result = '';
        $plate_no=$employee->plate_no;

        $result = "<center>";

        if($employee->plate_no == null){
            $result .= '<span class="badge badge-pill badge-success">Walkin</span>';
        }
        else{
            $result .= '<span class="badge badge-pill badge-danger">Rider</span>';
        }
            $result .= '</center>';
            return $result;
        })
           //END REMINDER
        ->addColumn('date',function($emp){
            $result = $emp->date;

            return $result;

        })
        ->addColumn('time_in',function($emp){
            $result = $emp->time_in;

            return $result;

        })
        ->addColumn('time_out',function($emp){
            $result = $emp->time_out;

            return $result;

        })
        ->make(true);

    }

    public function tapin_info_subcon(Request $request){

        $employee_info_subcon = ATKSSUBCON::where('empno', $request->employeeno)->orderBy('created_at', 'desc')->get();

        return DataTables::of($employee_info_subcon)
        ->addColumn('empno',function($emp){
            $result = $emp->empno;

            return $result;
        })
        ->addColumn('empname',function($emp){
            $result = $emp->empname;

            return $result;

        })
        ->addColumn('department',function($emp){
            $result = $emp->department;

            return $result;

        })
         // REMINDER
        ->addColumn('plate_no',function($emp){
            $result = $emp->plate_no;

            return $result;

        })
         ->addColumn('type', function($employee){
        $result = '';
        $plate_no=$employee->plate_no;

        $result = "<center>";

        if($employee->plate_no == null){
            $result .= '<span class="badge badge-pill badge-success">Walkin</span>';
        }
        else{
            $result .= '<span class="badge badge-pill badge-danger">Rider</span>';
        }
            $result .= '</center>';
            return $result;
        })
         //END  REMINDER
        ->addColumn('date',function($emp){
            $result = $emp->date;

            return $result;

        })
        ->addColumn('time_in',function($emp){
            $result = $emp->time_in;

            return $result;

        })
        ->addColumn('time_out',function($emp){
            $result = $emp->time_out;

            return $result;

        })
        ->make(true);

    }

    public function return_info(Request $request) //ATKS FOR ALL EMPLOYEES==========================
    {
        $employee_info = ATKS::where('empno',$request->empno)->orderBy('created_at','desc')->first();

        return response()->json(['employee_info' => $employee_info]);
    }

    public function return_info_subcon(Request $request)
    {
        $employee_info_subcon = ATKSSUBCON::where('empno',$request->empno)->orderBy('created_at','desc')->first();

        return response()->json(['employee_info_subcon' => $employee_info_subcon]);
    }

    // public function get_department(Request $request)
    // {
    //     $department_info = ATKS::where('empno',$request->empno)->orderBy('created_at','desc')->first();

    //     return response()->json(['department_info' => $department_info]);
    // }



    public function store(Request $request) //ATKS STORE DATA ======================
    {

        date_default_timezone_set('Asia/Manila');

        $employeeno = $request->txtScanner;

        $stat = $request->status;

    if ($stat==1){
            if (ATKSHRIS::where('empno', $employeeno)->exists()) {

                        $request->date = Carbon::now()->format('Y-m-d');
                        $request->time = Carbon::now()->format('H:i:s');

                        //+++++++++++++++++++++++++++++++++ DEPLOYMENT: NO PARKING LOT SPACE +++++++++++++++++++++++++++++++++



                        // date_default_timezone_set('Asia/Manila');
                        // $date_now= date('Y-m-d');




                        // $slot_count=ATKS::where('status',1)->whereNotNull('plate_no')->where('date',$date_now)->count();
                        // $slot_count_subcon=ATKSSUBCON::where('status',1)->whereNotNull('plate_no')->where('date',$date_now)->count();

                        // $slot_count_out=ATKS::where('status',2)->whereNotNull('plate_no')->where('date',$date_now)->count();
                        // $slot_count_subcon_out=ATKSSUBCON::where('status',2)->whereNotNull('plate_no')->where('date',$date_now)->count();


                        // // $slot_count=ATKS::where('status',1)->whereNotNull('plate_no')->count();
                        // // $slot_count_subcon=ATKSSUBCON::where('status',1)->whereNotNull('plate_no')->count();

                        // // $slot_count_out=ATKS::where('status',2)->whereNotNull('plate_no')->count();
                        // // $slot_count_subcon_out=ATKSSUBCON::where('status',2)->whereNotNull('plate_no')->count();
                        // // // $available_slot=88;


                        // $total_riders_count=69-($slot_count+$slot_count_subcon); //TOTAL RIDERS FOR TODAY'S TIME IN == HRIS_RIDERS PLUS SUBCON_RIDERS
                        // // $total_riders_count=822-($slot_count+$slot_count_subcon);
                        // //   $remaining_slot= $available_slot-$total_riders_count;//REMAINING SLOT FOR RIDERS (BOTH SUBCON AN HRIS)

                        // $out_total_riders= $slot_count_out+$slot_count_subcon_out; //TOTAL RIDERS FOR TODAY'S TIME OUT == HRIS_RIDERS PLUS SUBCON_RIDERS

                        // $out_total_slot= $total_riders_count+$out_total_riders;














                        $firstname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('FirstName');
                        $lastname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('LastName');
                        $dept = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('fkDepartment');
                        $departments = ATKSSECTION::where('pkid', $dept)->value('department');
                        $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //REMINDERS
                        $latest = ATKS::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();


                        // ==========================START OF MODIFICATION==========================

                            if ($latest != null) {

                                $latestdatetime = Carbon::parse($latest->time);
                                $previousdatetime = Carbon::parse($request->time);
                                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                                return response()->json(['result' => 3]);

                            }else if($plate_no !=null){

                                return response()->json(['result' => 5]);
                                // if ($out_total_slot==0){
                                //     return response()->json(['result' => 11]); //++++++++++++++ DEPLOYMENT +++++++++++++++
                                // }else{
                                //     return response()->json(['result' => 5]);
                                // }
                            }else{
                                    ATKS::updateOrCreate(['id' => $request->walkin_id],
                                    ['empno' => $employeeno,
                                    'empname' => $firstname ." ". $lastname,
                                    'department' => $departments,
                                    'plate_no'=>$plate_no, //REMINDERS
                                    'date' => $request->date,
                                    'time' =>  $request->time,
                                    'status' =>  $stat,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')

                                    ]);

                                    return response()->json(['result' => 1]);
                            }


                }else if(AtkSubcon::where('EmpNo', $employeeno)->exists()){ //STATUS 2  - SUBCON EMPLOYEES EXISTED

                            $request->date = Carbon::now()->format('Y-m-d');
                            $request->time = Carbon::now()->format('H:i:s');

                            $firstname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('FirstName'); //REMINDERS: COLUMN = COLUMN DATABASE OF SUBCON DB
                            $lastname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('LastName');
                            $dept = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('fkDepartment');
                            $departments = ATKSSECTION::where('pkid', $dept)->value('department');
                            $plate_no = PlateNumberSubcon::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //REMINDERS
                            $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();
                            // return $plate_no;
                            if ($latest != null) {

                                $latestdatetime = Carbon::parse($latest->time);
                                $previousdatetime = Carbon::parse($request->time);
                                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                                return response()->json(['result' => 4]);

                            }else if($plate_no !=null){
                                // if ($out_total_slot==0){
                                //     return response()->json(['result' => 11]); //++++++++++++++ DEPLOYMENT +++++++++++++++
                                // }else{
                                //     return response()->json(['result' => 5]);
                                // }
                                return response()->json(['result' => 5]); //MODIFY SUBCON:
                            }
                            else{

                                ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                                    ['empno' => $request->txtScanner,
                                    'empname' => $firstname ." ". $lastname,
                                    'department' => $departments ,
                                    'plate_no'=>$plate_no, //REMINDER SUBCON
                                    'date' => $request->date,
                                    'time' =>  $request->time,
                                    'status' =>  $stat,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                    ]);

                                    return response()->json(['result' => 2]);
                                }

                }else if(CanteenStaffList::where('empno', $employeeno)->exists()){ //++++++++++++++++++++++ CANTEEN EMPLOYEES:TIME IN ++++++++++++++++


                            $request->date = Carbon::now()->format('Y-m-d');
                            $request->time = Carbon::now()->format('H:i:s');

                            $name=CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('empname');
                            $departments = CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('section');
                            $plate_no = CanteenStaffList::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //REMINDERS
                           $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                            // return $plate_no;
                            if ($latest != null) {

                                $latestdatetime = Carbon::parse($latest->time);
                                $previousdatetime = Carbon::parse($request->time);
                                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                                return response()->json(['result' => 7]);

                            }else if($plate_no !=null){
                                // if ($out_total_slot==0){
                                //     return response()->json(['result' => 11]); //++++++++++++++ CANTEEN +++++++++++++++
                                // }else{
                                //     return response()->json(['result' => 5]);
                                // }
                                return response()->json(['result' => 5]); //MODIFY SUBCON:
                            }
                            else{

                                ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                                    ['empno' => $request->txtScanner,
                                    'empname' => $name,
                                    'department' => $departments ,
                                    'plate_no'=>$plate_no, //REMINDER SUBCON
                                    'date' => $request->date,
                                    'time' =>  $request->time,
                                    'status' =>  $stat,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                    ]);

                                    return response()->json(['result' => 2]);
                            }

                }else{
                        return response()->json(['result' => 10]); //STATUS 2  - NO DATA WILL SAVES
                }


    }else if($stat==2){              //************* STATUS 2 *********************
                        //STATUS2: IF RECORDS EXISTED FOR TIME IN COLLECT THE DATA THEN INSERT THE STATUS == 2 MEANS OUT
                date_default_timezone_set('Asia/Manila');
                $date_now= date('Y-m-d');


            if (ATKS::where('empno',$request->txtScanner)->where('status', 1)->whereNotNull('plate_no')->where('date', $date_now)->exists()) {  // STATUS 2: IF RECORDS EXISTED FOR TIME IN COLLECT THE DATA THEN INSERT THE STATUS == 2 MEANS OUT

                    $request->date = Carbon::now()->format('Y-m-d');
                    $request->time = Carbon::now()->format('H:i:s');

                    $empno = ATKS::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empno');
                    $empname = ATKS::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empname');
                    $department = ATKS::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('department');
                    $plate_no = ATKS::where('empno', $employeeno)->whereNotNull('plate_no')->orderBy('created_at', 'asc')->where('date', $request->date)->value('plate_no');
                    // $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                    $latest = ATKS::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                //    return $plate_no;
                        if ($latest != null) {

                            // return 'true';
                            $latestdatetime = Carbon::parse($latest->time);
                            $previousdatetime = Carbon::parse($request->time);
                            $difference = $latestdatetime->diffInSeconds($previousdatetime);

                            return response()->json(['result' => 3]);
                        }else{
                                ATKS::updateOrCreate(['id' => $request->walkin_id],
                                ['empno' => $empno,
                                'empname' =>$empname,
                                'department' => $department,
                                'plate_no'=>$plate_no, //REMINDERS
                                'date' => $request->date,
                                'time' =>  $request->time,
                                'status' =>  $stat,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')

                                ]);

                                return response()->json(['result' => 1]);
                        }

            }else if(ATKS::where('empno',$request->txtScanner)->where('status', 1)->whereNull('plate_no')->where('date', $date_now)->exists()){ //STATUS 2: IF THE PLATENUMBER IS NULL

                    // return true;
                    $request->date = Carbon::now()->format('Y-m-d');
                    $request->time = Carbon::now()->format('H:i:s');

                    $empno = ATKS::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empno');
                    $empname = ATKS::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empname');
                    $department = ATKS::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('department');
                    $plate_no = ATKS::where('empno', $employeeno)->whereNull('plate_no')->orderBy('created_at', 'asc')->value('plate_no');
                    // $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                    // $plate_no = '';
                    $latest = ATKS::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                    // return $plate_no;

                        if ($latest != null) {

                            // return 'true';
                            $latestdatetime = Carbon::parse($latest->time);
                            $previousdatetime = Carbon::parse($request->time);
                            $difference = $latestdatetime->diffInSeconds($previousdatetime);

                            return response()->json(['result' => 3]);
                        }
                        else{
                                ATKS::updateOrCreate(['id' => $request->walkin_id],
                                ['empno' => $empno,
                                'empname' =>$empname,
                                'department' => $department,
                                'plate_no'=>$plate_no, //REMINDERS
                                'date' => $request->date,
                                'time' =>  $request->time,
                                'status' =>  $stat,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')

                                ]);

                                return response()->json(['result' => 1]);
                        }

            } else if(ATKSSUBCON::where('empno',$request->txtScanner)->where('status', 1)->whereNotNull('plate_no')->where('date', $date_now)->exists()){ //STATUS 2 - SUBCON YES RIDER
                    // return 'for yes rider subcon';
                    $request->date = Carbon::now()->format('Y-m-d');
                    $request->time = Carbon::now()->format('H:i:s');

                    $empno = ATKSSUBCON::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empno');
                    $empname = ATKSSUBCON::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empname');
                    $department = ATKSSUBCON::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('department');
                    $plate_no = ATKSSUBCON::where('empno', $employeeno)->whereNotNull('plate_no')->orderBy('created_at', 'asc')->where('date', $request->date)->value('plate_no');
                    // $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                    $latest = ATKSSUBCON::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                //    return $plate_no;
                        if ($latest != null) {

                            // return 'true';
                            $latestdatetime = Carbon::parse($latest->time);
                            $previousdatetime = Carbon::parse($request->time);
                            $difference = $latestdatetime->diffInSeconds($previousdatetime);

                            return response()->json(['result' => 3]);
                        }
                        else{
                            ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                                ['empno' => $empno,
                                'empname' =>$empname,
                                'department' => $department,
                                'plate_no'=>$plate_no, //REMINDERS
                                'date' => $request->date,
                                'time' =>  $request->time,
                                'status' =>  $stat,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')

                                ]);

                                return response()->json(['result' => 2]);
                        }

            }else if(ATKSSUBCON::where('empno',$request->txtScanner)->where('status', 1)->whereNull('plate_no')->where('date', $date_now)->exists()){ //STATUS 2 - SUBCON NO RIDER
                // return 'for no rider subcon';

                    $request->date = Carbon::now()->format('Y-m-d');
                    $request->time = Carbon::now()->format('H:i:s');

                    $empno = ATKSSUBCON::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empno');
                    $empname = ATKSSUBCON::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('empname');
                    $department = ATKSSUBCON::where('empno', $employeeno)->orderBy('created_at', 'asc')->value('department');
                    $plate_no = ATKSSUBCON::where('empno', $employeeno)->whereNull('plate_no')->orderBy('created_at', 'asc')->value('plate_no');
                    // $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                    // $plate_no = '';
                    $latest = ATKSSUBCON::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                    // return $plate_no;

                        if ($latest != null) {

                            // return 'true';
                            $latestdatetime = Carbon::parse($latest->time);
                            $previousdatetime = Carbon::parse($request->time);
                            $difference = $latestdatetime->diffInSeconds($previousdatetime);

                            return response()->json(['result' => 3]);
                        }
                        else{
                            ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                                ['empno' => $empno,
                                'empname' =>$empname,
                                'department' => $department,
                                'plate_no'=>$plate_no, //REMINDERS
                                'date' => $request->date,
                                'time' =>  $request->time,
                                'status' =>  $stat,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')

                                ]);

                                return response()->json(['result' => 2]);
                        }

            }else{ //IF EMPLOYEE IS NOT TIME IN - THIS EMPLOYEE WILL BE DEFAULT AS WALKIN

                if (ATKSHRIS::where('empno', $employeeno)->exists()) {

                        $request->date = Carbon::now()->format('Y-m-d');
                        $request->time = Carbon::now()->format('H:i:s');

                        $firstname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('FirstName');
                        $lastname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('LastName');
                        $dept = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('fkDepartment');
                        $departments = ATKSSECTION::where('pkid', $dept)->value('department');
                        // $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //++++++++++++++ DEPLOYMENT: TAP OUT FAILED FOR RIDERS +++++++++++++++
                        $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                        $latest = ATKS::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();


                        // ==========================START OF MODIFICATION==========================

                            if ($latest != null) {

                                $latestdatetime = Carbon::parse($latest->time);
                                $previousdatetime = Carbon::parse($request->time);
                                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                                return response()->json(['result' => 3]);

                            }
                            // else if($plate_no !=null){
                            //     // return "rider has not time in";
                            //     return response()->json(['result' => 6]); //++++++++++++++ DEPLOYMENT: TAP OUT FAILED FOR RIDERS +++++++++++++++
                            // }
                            else{
                                    ATKS::updateOrCreate(['id' => $request->walkin_id],
                                    ['empno' => $employeeno,
                                    'empname' => $firstname ." ". $lastname,
                                    'department' => $departments,
                                    'plate_no'=>$plate_no, //REMINDERS
                                    'date' => $request->date,
                                    'time' =>  $request->time,
                                    'status' =>  $stat,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')

                                    ]);

                                    return response()->json(['result' => 1]);
                            }


                }
                else if(AtkSubcon::where('EmpNo', $employeeno)->exists()){ //STATUS 2  - SUBCON EMPLOYEES EXISTED

                        $request->date = Carbon::now()->format('Y-m-d');
                        $request->time = Carbon::now()->format('H:i:s');



                        $firstname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('FirstName'); //REMINDERS: COLUMN = COLUMN DATABASE OF SUBCON DB
                        $lastname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('LastName');
                        $dept = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('fkDepartment');
                        $departments = ATKSSECTION::where('pkid', $dept)->value('department');
                        // $plate_no = PlateNumberSubcon::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //++++++++++++++ DEPLOYMENT: TAP OUT FAILED FOR RIDERS +++++++++++++++
                        $plate_no = PlateNumberSubcon::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                        $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();
                        // return $plate_no;
                        if ($latest != null) {

                            $latestdatetime = Carbon::parse($latest->time);
                            $previousdatetime = Carbon::parse($request->time);
                            $difference = $latestdatetime->diffInSeconds($previousdatetime);

                            return response()->json(['result' => 4]);

                        }
                        // else if($plate_no !=null){
                            //     // return "rider has not time in";
                            //     return response()->json(['result' => 6]); //++++++++++++++ DEPLOYMENT: TAP OUT FAILED FOR RIDERS +++++++++++++++
                        // }
                        else{

                            ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                                ['empno' => $request->txtScanner,
                                'empname' => $firstname ." ". $lastname,
                                'department' => $departments ,
                                'plate_no'=>$plate_no, //REMINDER SUBCON
                                'date' => $request->date,
                                'time' =>  $request->time,
                                'status' =>  $stat,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                                ]);

                                return response()->json(['result' => 2]);
                            }

                }else if(CanteenStaffList::where('empno', $employeeno)->exists()){ //++++++++++++++++++++++ CANTEEN EMPLOYEES:TIME IN ++++++++++++++++


                        $request->date = Carbon::now()->format('Y-m-d');
                        $request->time = Carbon::now()->format('H:i:s');

                        $name=CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('empname');
                        $departments = CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('section');
                        // $plate_no = CanteenStaffList::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //CANTEEN
                        $plate_no = CanteenStaffList::where('empno',$employeeno)->where('logdel',0)->value('walk_in');
                        $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                        // return $plate_no;
                        if ($latest != null) {

                            $latestdatetime = Carbon::parse($latest->time);
                            $previousdatetime = Carbon::parse($request->time);
                            $difference = $latestdatetime->diffInSeconds($previousdatetime);

                            return response()->json(['result' => 7]);

                        }else{

                            ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                                ['empno' => $request->txtScanner,
                                'empname' => $name,
                                'department' => $departments ,
                                'plate_no'=>$plate_no, //REMINDER SUBCON
                                'date' => $request->date,
                                'time' =>  $request->time,
                                'status' =>  $stat,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                                ]);

                                return response()->json(['result' => 2]);
                        }


                }else{
                            return response()->json(['result' => 10]);
                }


            }//END CONDITION FOR STATUS 2 PLATE NO

    } //END OF CONDITION


}

    //========= MODIFY=========
public function yes_rider(Request $request){

        date_default_timezone_set('Asia/Manila');

        $employeeno = $request->txtScanner;

        $stat = $request->status;

        if (ATKSHRIS::where('empno', $employeeno)->exists()) {

            $request->date = Carbon::now()->format('Y-m-d');
            $request->time = Carbon::now()->format('H:i:s');

            $firstname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('FirstName');
            $lastname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('LastName');
            $dept = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('fkDepartment');
            $departments = ATKSSECTION::where('pkid', $dept)->value('department');
            $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //REMINDERS
            $latest = ATKS::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

            if ($latest != null) {

                $latestdatetime = Carbon::parse($latest->time);
                $previousdatetime = Carbon::parse($request->time);
                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                return response()->json(['result' => 3]);

            }
            else{
                        ATKS::updateOrCreate(['id' => $request->walkin_id],
                        ['empno' => $employeeno,
                         'empname' => $firstname ." ". $lastname,
                         'department' => $departments,
                         'plate_no'=>$plate_no, //REMINDERS
                         'date' => $request->date,
                         'time' =>  $request->time,
                         'status' =>  $stat,
                         'created_at' => date('Y-m-d H:i:s'),
                         'updated_at' => date('Y-m-d H:i:s')

                        ]);

                        return response()->json(['result' => 1]);
                }

        }else if(AtkSubcon::where('EmpNo', $employeeno)->exists()){

            //return 'hello';

            $request->date = Carbon::now()->format('Y-m-d');
            $request->time = Carbon::now()->format('H:i:s');



            $firstname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('FirstName'); //REMINDERS: COLUMN = COLUMN DATABASE OF SUBCON DB
            $lastname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('LastName');
            $dept = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('fkDepartment');
            $departments = ATKSSECTION::where('pkid', $dept)->value('department');
            $plate_no = PlateNumberSubcon::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //REMINDER SUBCON
            $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();
            // return $plate_no;
            if ($latest != null) {

                $latestdatetime = Carbon::parse($latest->time);
                $previousdatetime = Carbon::parse($request->time);
                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                return response()->json(['result' => 4]);

            }
            else{

                ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                    ['empno' => $request->txtScanner,
                    'empname' => $firstname ." ". $lastname,
                     'department' => $departments ,
                     'plate_no'=>$plate_no, //REMINDER SUBCON
                     'date' => $request->date,
                     'time' =>  $request->time,
                     'status' =>  $stat,
                     'created_at' => date('Y-m-d H:i:s'),
                     'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    return response()->json(['result' => 2]);
                }
                // $datas=plate_no_management::all();
                // return $plate_no;

        }else if(CanteenStaffList::where('EmpNo', $employeeno)->exists()){ //++++++++++CANTEEN+++++++++++



                $request->date = Carbon::now()->format('Y-m-d');
                $request->time = Carbon::now()->format('H:i:s');

                $name=CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('empname');
                $departments = CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('section');
                $plate_no = CanteenStaffList::where('empno',$employeeno)->where('logdel',0)->value('plate_no'); //REMINDERS
                $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                // return $plate_no;
                if ($latest != null) {

                    $latestdatetime = Carbon::parse($latest->time);
                    $previousdatetime = Carbon::parse($request->time);
                    $difference = $latestdatetime->diffInSeconds($previousdatetime);

                    return response()->json(['result' => 7]);

                }else{

                    ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                        ['empno' => $request->txtScanner,
                        'empname' => $name,
                        'department' => $departments ,
                        'plate_no'=>$plate_no, //REMINDER SUBCON
                        'date' => $request->date,
                        'time' =>  $request->time,
                        'status' =>  $stat,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        return response()->json(['result' => 2]);
                }


        }




    }
    // ======================END YES RIDER =============================



   // ======================START NO RIDER =============================
    public function no_rider(Request $request){

        //return 'hello';

        date_default_timezone_set('Asia/Manila');

        $employeeno = $request->txtScanner;

        $stat = $request->status;

        if (ATKSHRIS::where('empno', $employeeno)->exists()) {

            $request->date = Carbon::now()->format('Y-m-d');
            $request->time = Carbon::now()->format('H:i:s');

            $firstname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('FirstName');
            $lastname = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('LastName');
            $dept = ATKSHRIS::where('empno', $employeeno)->orderBy('lastupdate', 'asc')->value('fkDepartment');
            $departments = ATKSSECTION::where('pkid', $dept)->value('department');
            $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in'); //MODIFY: Null value default when riders have no motor that day.
            $latest = ATKS::where('empno',$employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

            if ($latest != null) {

                $latestdatetime = Carbon::parse($latest->time);
                $previousdatetime = Carbon::parse($request->time);
                $difference = $latestdatetime->diffInSeconds($previousdatetime);

                return response()->json(['result' => 3]);

            }
            else{
                        ATKS::updateOrCreate(['id' => $request->walkin_id],
                        ['empno' => $employeeno,
                         'empname' => $firstname ." ". $lastname,
                         'department' => $departments,
                         'plate_no'=>$plate_no, //REMINDERS
                         'date' => $request->date,
                         'time' =>  $request->time,
                         'status' =>  $stat,
                         'created_at' => date('Y-m-d H:i:s'),
                         'updated_at' => date('Y-m-d H:i:s')

                        ]);

                        return response()->json(['result' => 1]);
                }
        }else if(AtkSubcon::where('EmpNo', $employeeno)->exists()){

            //return 'hello';

                $request->date = Carbon::now()->format('Y-m-d');
                $request->time = Carbon::now()->format('H:i:s');



                $firstname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('FirstName'); //REMINDERS: COLUMN = COLUMN DATABASE OF SUBCON DB
                $lastname = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('LastName');
                $dept = AtkSubcon::where('EmpNo', $employeeno)->orderBy('LastUpdate', 'asc')->value('fkDepartment');
                $departments = ATKSSECTION::where('pkid', $dept)->value('department');
                $plate_no = PlateNumber::where('empno',$employeeno)->where('logdel',0)->value('walk_in'); //MODIFY: Null value default when riders have no motor that day.
                $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();
                // return $plate_no;
                if ($latest != null) {

                    $latestdatetime = Carbon::parse($latest->time);
                    $previousdatetime = Carbon::parse($request->time);
                    $difference = $latestdatetime->diffInSeconds($previousdatetime);

                    return response()->json(['result' => 4]);

                }
                else{

                    ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                        ['empno' => $request->txtScanner,
                        'empname' => $firstname ." ". $lastname,
                        'department' => $departments ,
                        'plate_no'=>$plate_no, //REMINDER SUBCON
                        'date' => $request->date,
                        'time' =>  $request->time,
                        'status' =>  $stat,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        return response()->json(['result' => 2]);
                    }
                    // $datas=plate_no_management::all();
                    // return $plate_no;
        }else if(CanteenStaffList::where('EmpNo', $employeeno)->exists()){ //++++++++++CANTEEN+++++++++++



                $request->date = Carbon::now()->format('Y-m-d');
                $request->time = Carbon::now()->format('H:i:s');

                $name=CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('empname');
                $departments = CanteenStaffList::where('empno', $employeeno)->where('logdel',0)->value('section');
                $plate_no = CanteenStaffList::where('empno',$employeeno)->where('logdel',0)->value('walk_in'); //REMINDERS
                $latest = ATKSSUBCON::where('empno', $employeeno)->where('date',$request->date)->where('status',$stat)->orderBy('created_at', 'desc')->first();

                // return $plate_no;
                if ($latest != null) {

                    $latestdatetime = Carbon::parse($latest->time);
                    $previousdatetime = Carbon::parse($request->time);
                    $difference = $latestdatetime->diffInSeconds($previousdatetime);

                    return response()->json(['result' => 7]);

                }else{

                    ATKSSUBCON::updateOrCreate(['id' => $request->walkin_id],
                        ['empno' => $request->txtScanner,
                        'empname' => $name,
                        'department' => $departments ,
                        'plate_no'=>$plate_no, //REMINDER SUBCON
                        'date' => $request->date,
                        'time' =>  $request->time,
                        'status' =>  $stat,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        return response()->json(['result' => 2]);
                }


        }



    }
  // ======================END NO RIDER =============================


    public function export_records(Request $request) //EXPORT DATA FOR ALL HRIS==========================
    {
        $start = $request->txt_date_start;
        $end = $request->txt_date_end;
       // $export_total =  ATKS::whereBetween('date',[$start, $end])->count();
        //return  $export_total;
       $export_rec =  ATKS::whereBetween('date',[$start, $end])->orderBy('date','desc')->get();
        // $export_total =  ATKS::whereBetween('date',[$start, $end])->count();
        return Excel::download(new ATKSRecordsExportView($export_rec,$start,$end), 'WITI_Records ('.$start.' to '.$end.').xlsx');
    }

    public function export_records_subcon(Request $request) //EXPORT DATA FOR ALL SUBCON==========================
    {
        $start = $request->txt_date_start_subcon;
        $end = $request->txt_date_end_subcon;

        $export_rec =  ATKSSUBCON::whereBetween('date',[$start, $end])->orderBy('date','desc')->get();

        return Excel::download(new ATKSRecordsExportView($export_rec,$start,$end), 'WITI_Records_Subcon ('.$start.' to '.$end.').xlsx'); //ACCURACY IN TERMS OF where('status',1) or where('status',2)
    }

    public function calculate_incoming_hris(Request $request) //COUNT TOTAL DATA FOR ALL HRIS (NIGHT SHIFT)==========================
    {
        $date = $request->txt_date;

        $count_hris_nightshift =  ATKS::where('status',1)->where('date', $date)->whereBetween('time',['17:00', '24:00'])->count();

        return $count_hris_nightshift;
    }

    public function calculate_incoming_hris_ds(Request $request) //COUNT TOTAL DATA FOR ALL HRIS (DAY SHIFT)==========================
    {
        $date = $request->txt_date;

        $count_hris_dayshift =  ATKS::where('status',1)->where('date', $date)->whereBetween('time',['06:00', '16:30'])->count();

        return $count_hris_dayshift;
    }

    public function calculate_incoming_subcon(Request $request)//COUNT TOTAL DATA FOR ALL SUBCON (NIGHT SHIFT)==========================
    {
        $date = $request->txt_date;

        $count_subcon_nightshift =  ATKSSUBCON::where('status',1)->where('date', $date)->whereBetween('time',['17:00', '24:00'])->count();

        return $count_subcon_nightshift;
    }

    public function calculate_incoming_subcon_ds(Request $request)//COUNT TOTAL DATA FOR ALL HRIS (DAY SHIFT)==========================
    {
        $date = $request->txt_date;

        $count_subcon_dayshift =  ATKSSUBCON::where('status',1)->where('date', $date)->whereBetween('time',['06:00', '16:30'])->count();

        return $count_subcon_dayshift;
    }



// $search_rec_walkin=ATKS::where('status',1)whereNull('plate_no')->whereBetween('date',[$start,$end])->count(); // REMINDER TO CHANGE REQUEST: FOR TAP IN ONLY
    //REMINDER: DISPLAY THE TOTAL NUMBER OF RECORDS
    public function total_pmi_walkin(Request $request){ // COUNT PMI WALKIN ROW TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

            $search_rec_walkin=ATKS:: where('status',1)->whereNull('plate_no')->whereBetween('date',[$start,$end])->count(); // where null

        return response()->json(['result'=>$search_rec_walkin]);
    }
    public function total_pmi_rider(Request $request){ //COUNT PMI RIDER ROW TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $search_rec_rider=ATKS::where('status',1)->whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count(); // where not null

        return response()->json(['result'=>$search_rec_rider]);
    }


    public function total_subcon_walkin(Request $request){ //COUNT SUBCON WALKIN ROW TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

            $search_rec_wsubcon=ATKSSUBCON::where('status',1)->whereNull('plate_no')->whereBetween('date',[$start,$end])->count();

        return response()->json(['result'=>$search_rec_wsubcon]);
    }
    public function total_subcon_rider(Request $request){ //COUNT SUBCON RIDER ROW TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $search_rec_rsubcon=ATKSSUBCON::where('status',1)->whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();

        return response()->json(['result'=>$search_rec_rsubcon]);
    }

    public function total_pmi_all(Request $request){ // TOTAL PMI COLUMN TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $search_rec= ATKS::where('status',1)->whereBetween('date',[$start,$end])->count();

        return response()->json(['result'=>$search_rec]);
    }

    public function total_subcon_all(Request $request){ // TOTAL ALL SUBCON COLUMN TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $search_rec= ATKSSUBCON::where('status',1)->whereBetween('date',[$start,$end])->count();

        return response()->json(['result'=>$search_rec]);
    }

    public function total_walkin_all(Request $request){ // TOTAL ALL WALKIN ROW TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $search_rec_sub= ATKSSUBCON::where('status',1)->whereNull('plate_no')->whereBetween('date',[$start,$end])->count();
        $search_rec_pmi= ATKS::where('status',1)->whereNull('plate_no')->whereBetween('date',[$start,$end])->count();

        $total=($search_rec_pmi +  $search_rec_sub);

        return response()->json(['result'=>$total]);
    }

    public function total_riders_all(Request $request){ // TOTAL ALL RIDER ROW TAP IN
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $search_rec_sub= ATKSSUBCON::where('status',1)->whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();
        $search_rec_pmi= ATKS::where('status',1)->whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();

        $total=($search_rec_pmi +  $search_rec_sub);

        return response()->json(['result'=>$total]);
    }


    public function export_total_reports(Request $request) //EXPORT DATA FOR ALL HRIS==========================
    {
        $start = $request->txt_date_start_total;
        $end = $request->txt_date_end_total;

        $walkin_pmi=ATKS::whereNull('plate_no')->whereBetween('date',[$start,$end])->count();
        $rider_pmi=ATKS::whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();

        $walkin_subcon=ATKSSUBCON::whereNull('plate_no')->whereBetween('date',[$start,$end])->count();
        $rider_subcon=ATKSSUBCON::whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();

        $total_pmi_all= ATKS::whereBetween('date',[$start,$end])->count();
        $total_subcon_all= ATKSSUBCON::whereBetween('date',[$start,$end])->count();


        //===============EXPORT DATA FOR ALL EMPLOYEES (TOTAL ROW)==========================

        $search_rec_sub= ATKSSUBCON::whereNull('plate_no')->whereBetween('date',[$start,$end])->count();
        $search_rec_pmi= ATKS::whereNull('plate_no')->whereBetween('date',[$start,$end])->count();

        $walkin_all=($search_rec_pmi +  $search_rec_sub);


        $search_rec_sub_1= ATKSSUBCON::whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();
        $search_rec_pmi_1= ATKS::whereNotNull('plate_no')->whereBetween('date',[$start,$end])->count();

        $rider_all=($search_rec_pmi_1 +  $search_rec_sub_1);

   // return Excel::download(new ATKSRecordsExportView($export_rec,$export_total,$start,$end,$walkin_pmi), 'WITI_Records ('.$start.' to '.$end.').xlsx');
        return Excel::download(new ExportReport ($walkin_pmi,$rider_pmi,$walkin_subcon,$rider_subcon,
        $total_pmi_all,$total_subcon_all,$walkin_all,$rider_all,$start,$end), 'WITI_Report ('.$start.' to '.$end.').xlsx');

    }



    //====================MODIFIES: SLOT COUNTER==========================
    public function get_slot_count_up(Request $request){
        date_default_timezone_set('Asia/Manila');
        $date_now= date('Y-m-d');

        // $slot_count=ATKS::where('status',1)->whereNotNull('plate_no')->where('date',$date_now)->count();
        // $slot_count_subcon=ATKSSUBCON::where('status',1)->whereNotNull('plate_no')->where('date',$date_now)->count();

        // $slot_count_out=ATKS::where('status',2)->whereNotNull('plate_no')->where('date',$date_now)->count();
        // $slot_count_subcon_out=ATKSSUBCON::where('status',2)->whereNotNull('plate_no')->where('date',$date_now)->count();


        $slot_count=ATKS::where('status',1)->whereNotNull('plate_no')->count();
        $slot_count_subcon=ATKSSUBCON::where('status',1)->whereNotNull('plate_no')->count();

        $slot_count_out=ATKS::where('status',2)->whereNotNull('plate_no')->count();
        $slot_count_subcon_out=ATKSSUBCON::where('status',2)->whereNotNull('plate_no')->count();
        $available_slot=88;


       $total_riders_count=3000-($slot_count+$slot_count_subcon); //TOTAL RIDERS FOR TODAY'S TIME IN == HRIS_RIDERS PLUS SUBCON_RIDERS
        // $total_riders_count=109-($slot_count+$slot_count_subcon); //TOTAL RIDERS FOR TODAY'S TIME IN == HRIS_RIDERS PLUS SUBCON_RIDERS
        // $total_riders_count=998-($slot_count+$slot_count_subcon);
        //   $remaining_slot= $available_slot-$total_riders_count;//REMAINING SLOT FOR RIDERS (BOTH SUBCON AN HRIS)

        $out_total_riders= $slot_count_out+$slot_count_subcon_out; //TOTAL RIDERS FOR TODAY'S TIME OUT == HRIS_RIDERS PLUS SUBCON_RIDERS

        $out_total_slot= $total_riders_count+$out_total_riders;

      if($out_total_slot==0){ //if the total slot is equal to zero

        return response()->json(['result' => 0, 'out_total_slot'=>$out_total_slot]); //MODIFY SUBCON:

      }else if($out_total_slot<=10){// DEPLOYMENT
        return response()->json(['result' => 10, 'out_total_slot'=>$out_total_slot]); //MODIFY SUBCON:

      }else if($out_total_slot>0){//if the total slot is greater than zero

        return response()->json(['result' => 1, 'out_total_slot'=>$out_total_slot]); //MODIFY SUBCON:

      }

    }



    } //END OF THE CONTROLLER
