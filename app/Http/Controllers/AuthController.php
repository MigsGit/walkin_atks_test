<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\ATKS;

class AuthController extends Controller
{
    //ALTER TABLE login_tbl MODIFy id int AUTO_INCREMENT id PRIMARY KEY;
    public function sign_in(Request $request){

      $data=array('empno'=>$request->empno,
        'password'=>$request->password,
        );
        //return $data;

        $rules=['empno'=>'required',
        'password'=>'required'];
        $validator=Validator::make($data,$rules);

        if($validator->fails()){

            return response()->json(['validator'=>'HasError','error'=>$validator->messages()]);

        }
        else{

            if(Auth::attempt($data)){
                return 'hello';
                // session_start();
                // $_SESSION["id"] = Auth::user()->id; // use for insert/update/delete of every operation in created_by, last_updated_by & logdel columns
                // $_SESSION["empno"] = Auth::user()->empno; // optional

                // // Auth::logout();
                // return response()->json(['result' => "1", 'session' => $_SESSION["id"]]);

            }else{


            }

        }

    }
}
  // if(Auth::attempt($user_data)){
            //     if(Auth::user()->logdel == 1){
            //         return response()->json(['logdel' => "deleted"]);
            //     }
            //     else if(Auth::user()->status == 2){
            //         return response()->json(['status' => "inactive"]);
            //     }
            //     else if(Auth::user()->is_password_changed == 0){
            //         return response()->json(['result' => "2"]); // change pass view
            //     }
            //     else{
            //         session_start();
            //         $_SESSION["user_id"] = Auth::user()->id; // use for insert/update/delete of every operation in created_by, last_updated_by & logdel columns
            //         $_SESSION["user_level_id"] = Auth::user()->user_level_id; // optional
            //         $_SESSION["username"] = Auth::user()->username; // optional
            //         // Auth::logout();
            //         return response()->json(['result' => "1", 'session' => $_SESSION["user_id"]]);
            //     }
            // }
            // else{
            //     return response()->json(['result' => "0", 'error_message' => 'We do not recognize your username and/or password. Please try again.', 'error' => $validator->messages()]);
            // }
