<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ATKSRecordsExportView;
use Carbon\Carbon;

use App\ATKS;
use App\ATKSHRIS;
use App\ATKSSUBCON;
use App\Subcon;
use App\ATKSSECTION;
use App\PlateNumber; //Reminder
use App\PlateNumberSubcon;
// use App\plate_no_subcon; //Reminder Subcon
use App\AtkSubcon;

use DataTables;

class RecordController extends Controller
{
    public function records_hris(Request $request) //REMINDER: ATKS RECORDS FOR ALL EMPLOYEES==========================
    {
        // $atks = ATKSSUBCON::all();
        $atks_hris = ATKS::all();
        return $atks_hris;
    //     if ($request->ajax()) {
    //         //$atks = ATKS::where('empno', $request->employeeno)->orderBy('created_at', 'desc')->get();
    //         $atks = ATKSSUBCON::all();
    //         // return $atks;
    //         // $plate_no=PlateNumber::where('plate_no',$request->plate_no)->get();
    //         return DataTables::of($atks)
    //                 //REMINDER: EXCEL TABLE
    //                 ->addColumn('type', function($data){

    //                 $result = "<center>";
    //                 $plate_no=$data->plate_no;
    //                 //REMINDER
    //                 if($plate_no == null){
    //                     $result .= '<h3> <p style="background-color:red;color:white;">Walkin</p></h3>';
    //                 }
    //                 else{
    //                     $result .= '<h3 <p style="background-color:green;color:white;">Rider</p></h3>';
    //                 }
    //                     $result .= '</center>';
    //                     return $result;
    //                 })

    //                 ->addColumn('raw_time_in', function ($data) {
    //                     if ($data->status == 1) return $data->time;
    //                     return '-';
    //                 })
    //                 ->addColumn('raw_time_out', function ($data) {
    //                     if ($data->status == 2) return $data->time;
    //                     return '-';
    //                 })
    //                 ->rawColumns(['raw_time_in', 'raw_time_out','type'])
    //                 ->make(true);
    //     }

    //     return view('records')->with('records');

    // }

    }

    public function records_subcon(Request $request)
    {
        $atks=ATKSSUBCON::all();
        return $atks;

        //
        // if ($request->ajax()) {
        //     $atks = ATKSSUBCON::all();
        //     return DataTables::of($atks)
        //             //REMINDER: EXCEL TABLE
        //             ->addColumn('type', function($data){
        //             $result = "<center>";
        //             $plate_no=$data->plate_no;
        //             //REMINDER
        //             if($plate_no == null){
        //                 $result .= '<h3> <p style="background-color:red;color:white;">Walkin</p></h3>';
        //             }
        //             else{
        //                 $result .= '<h3 <p style="background-color:green;color:white;">Rider</p></h3>';
        //             }
        //                 $result .= '</center>';
        //                 return $result;
        //             })
        //             ->addColumn('raw_time_in', function ($data) {
        //                 if ($data->status == 1) return $data->time;
        //                 return '-';
        //             })
        //             ->addColumn('raw_time_out', function ($data) {
        //                 if ($data->status == 2) return $data->time;
        //                 return '-';
        //             })
        //             ->rawColumns(['raw_time_in', 'raw_time_out','type'])
        //             ->make(true);
        // }

        // return view('records')->with('records');

    }
}
