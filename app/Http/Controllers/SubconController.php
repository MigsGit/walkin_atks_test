<?php

namespace App\Http\Controllers;

use App\Subcon;
use App\User;
use App\Imports\SubconEmpImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DataTables;
use Auth;
use \DNS1D;

class SubconController extends Controller
{
    public function view_subconemployees(){
        $subcon = Subcon::where('status', 1)->get();

        return DataTables::of($subcon)
            ->addColumn('label1', function($subconemployees){
                $result = "";

                if($subconemployees->status == 1){
                    $result .= '<span class="label label-success badge-pil">Activated</span>';
                }
                else{
                    $result .= '<span class="label label-danger badge-pill">Deactivated</span>';
                }

                return $result;
            })
            ->addColumn('action1', function($subconemployees){
                $result = '<center><div class="dropdown">
                          <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Action">
                            <i class="fa fa-cog"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-left">';

                $result .= '<button class="dropdown-item aEditSubconEmpModal" type="button" subconemployees-id="' . $subconemployees->id . '" style="padding: 1px 1px; text-align: center;" data-toggle="modal" data-target="#EditSubconEmpModal" data-keyboard="false">Edit</button>';

                if($subconemployees->status == 1){
                    $result .= '<button class="dropdown-item aChangeSubconEmployeeStat" type="button" subconemployees-id="' . $subconemployees->empno . '" status="2" style="padding: 1px 1px; text-align: center;" data-toggle="modal" data-target="#modalChangeSubconEmpStat" data-keyboard="false">Deactivate</button>';
                }
                else{

                    $result .= '<button class="dropdown-item aChangeSubconEmployeeStat" type="button" style="padding: 1px 1px; text-align: center;" subconemployees-id="' . $subconemployees->empno . '" status="1" data-toggle="modal" data-target="#modalChangeSubconEmpStat" data-keyboard="false">Activate</button>';
                }

                $result .= '</div>
                        </div></center>';

                return $result;
            })
            ->addColumn('checkbox', function($subconemployees){
                return '<center><input type="checkbox" class="chkEMP" subconemployees-id="' . $subconemployees->empno . '"></center>';
            })
            ->rawColumns(['label1', 'action1', 'checkbox'])
            ->make(true);
    }
	public function add_subconemployees(Request $request){
        date_default_timezone_set('Asia/Manila');

        $data = $request->all();

        $validator = Validator::make($data, [
            'empno' => ['required', 'string', 'max:255'],
            'fullname' => ['string', 'max:255'],
            'agency' => ['string', 'max:255'],
            'department' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],

        ]);

        if ($validator->fails()) {
            return response()->json(['result' => '0', 'error' => $validator->messages()]);
        }
        else{
            DB::beginTransaction();

            try{
                Subcon::insert([
                    'empno' => $request->empno,
                    'empname' => $request->fullname,
                    'agency' => $request->agency,
                    'department' => $request->department,
                    'address' => $request->address,
                    'status' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                DB::commit();

                return response()->json(['result' => "1"]);
            }
            catch(\Exception $e) {
                DB::rollback();
                // throw $e;
                return response()->json(['result' => "0"]);
            }
        }
    }

    public function change_subconemp_stat(Request $request){
        date_default_timezone_set('Asia/Manila');

        $data = $request->all();

        $validator = Validator::make($data, [
            'subcon_id' => 'required',
            'status' => 'required',
        ]);

        if($validator->passes()){
            try{
                Subcon::where('id', $request->subcon_id)
                    ->update(
                        [
                            'status' => $request->status,
                        ]
                    );
                DB::commit();
                return response()->json(['result' => "1"]);
            }
            catch(\Exception $e) {
                DB::rollback();
                // throw $e;
                return response()->json(['result' => "0"]);
            }

            return response()->json(['result' => 1]);
        }
        else{
            return response()->json(['result' => "0", 'error' => $validator->messages()]);
        }
    }

    // Get User By Batch
    public function get_subconemp_by_batch(Request $request){

        $subconemp;

        if($request->subconemp_id == 0){
            $subconemp = Subcon::all();
        }
        else{
            $subconemp = Subcon::whereIn('empno', $request->subconemp_id)->get();
        }
        $barcode = [];

        if($subconemp->count() > 0){
            for($index = 0; $index < $subconemp->count(); $index++){
                $barcode[] = "data:image/png;base64," . DNS1D::getBarcodePNG( $subconemp[$index]->empno, 'C93');
            }
        }
        return response()->json(['subconemp' => $subconemp, 'barcode' => $barcode]);
    }

    public function import_subconemp(Request $request)
    {
        DB::beginTransaction();
        try{

            if(request()->file('import_file_subconemp')){
                $subconemp = Excel::toCollection(new SubconEmpImport, request()->file('import_file_subconemp'));

                if ($subconemp != null) {

                    for($index = 2; $index < count($subconemp[0]); $index++){
                        Subcon::insert([
                            'empname' => $subconemp[0][$index][0],
                            'empno' => $subconemp[0][$index][1],
                            'agency' => $subconemp[0][$index][2],
                            'department' => $subconemp[0][$index][3],
                            'address' => $subconemp[0][$index][4],
                            'status' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['result' => 1]);
        }

        catch(\Exception $e) {
            DB::rollback();
            return response()->json(['result' => $e]);
        }
    }

    public function get_subconemp_by_id(Request $request){
        $subconemp = Subcon::where('id', $request->subconemp_id)->get();
        return response()->json(['subconemps' => $subconemp]);
    }

    public function edit_subconemp(Request $request){
        date_default_timezone_set('Asia/Manila');

        $data = $request->all();

        $validator = Validator::make($data, [
            'fullname' => 'string|max:255'. $request->subconemp_id,
            'agency' => 'string|max:255'. $request->subconemp_id,
            'department' => 'string|max:255'. $request->subconemp_id,
            'address' => 'string|max:255'. $request->subconemp_id,
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => '0', 'error' => $validator->messages()]);
        }
        else{
            DB::beginTransaction();

            try{
                Subcon::where('id', $request->subconemp_id)
                ->update(
                [
                'empname'=> $request->fullname,
                'agency'=> $request->agency,
                'department'=> $request->department,
                'address'=> $request->address,
                'updated_at' => date('Y-m-d H:i:s')
                ]);

                DB::commit();

                return response()->json(['result' => "1"]);
            }
            catch(\Exception $e) {
                DB::rollback();
                // throw $e;
                return response()->json(['result' => "0"]);
            }
        }
    }

}
