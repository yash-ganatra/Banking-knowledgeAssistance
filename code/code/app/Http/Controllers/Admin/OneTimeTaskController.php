<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use DB;
use Cookie;
use Crypt;
use Illuminate\Support\Arr;
use SoulDoit\DataTable\SSP;
use App\Helpers\CommonFunctions;
use Carbon\Carbon;

class OneTimeTaskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //declare userId as global variable
    protected $userId;
    //declare roleId as global variable
    protected $roleId;

    public function __construct()
    {
        //checks token exists or not
        if(Cookie::get('token') != ''){
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            //get claims from token
            $claims = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($claims),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];
             
            if($this->roleId != 1){

                $isAutherized = false;
            }else{

               $isAutherized = true;
            }
            if(!$isAutherized)
            {
                echo "<div class='container RefreshRestrictMsg' style='
                    width: 63%;
                    margin: 0 auto;
                    height: 4em;
                    padding: 2em;
                    text-align: center;
                    border-radius: 10px red;
                    border-radius: 6px;
                    margin-top: 12em;
                    background-color: #fff0d3;
                    font-family:Arial;
                    line-height: 35px;'>

                    <p style='margin-top: 0%;
                    font-size: 1.375rem;
                    font-weight: 500;'>Unauthorized attempt detected.<br>Event logged for Audit and Admin team.</p>
                 
                  </div>";
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/OneTimeTaskController','onetimetask','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

    public function onetimetask(Request $request)
    {
    	try{
            $activeButton = false;
            $todaysDate = Carbon::now();
            $newYearDate = Carbon::parse('first day of January');

            $aofGenerated = DB::table('ACCOUNT_DETAILS')->where('APPLICATION_STATUS','!=',45) //not rejected today's form
                                                        ->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".Carbon::now()->format('d-m-Y')."','DD-MM-YYYY')")
                                                        ->get()->toArray();
            
            // $current_time = Carbon::now()->format('H');
            // echo "<pre>";print_r(count($aofGenerated));exit;
            if($todaysDate == $newYearDate && count($aofGenerated) == 0) {
                    $activeButton = true;
            }

            return view('admin.onetimetask')->with('activeButton', $activeButton);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }

    public function resetaofcounter(Request $request)
    {
    	try{
    		if ($request->ajax()){
                $requestData =  $request->get('data');

                if ($requestData['resetAofComment'] == '') {
                    return json_encode(['status'=>'fail','msg'=>'Please enter Mandatory comment','data'=>[]]);
                }

                $counter = 1;
                $restartSequence = DB::statement('ALTER SEQUENCE ACCOUNT_SEQUENCE RESTART START WITH '.$counter);
                $restartSequence = DB::statement('ALTER SEQUENCE AMEND_SEQUENCE RESTART START WITH '.$counter);


                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/OneTimeTaskController','resetaofcomment',$requestData['resetAofComment'],'','','1');

                if ($restartSequence) {
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Counter is reseted','data'=>[]]);
                }

                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to reset Counter','data'=>[]]);
           }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

}
?>