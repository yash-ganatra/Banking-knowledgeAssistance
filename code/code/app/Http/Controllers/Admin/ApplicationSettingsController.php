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
use App\Http\Controllers\Admin\ExceptionController;
use Carbon\Carbon;
use Cache;

class ApplicationSettingsController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/ApplicationSettingsController','applicationsettings','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

    /*
    * Method Name: applicationsettings
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to get application settings details
    *
    * Input Params:
    *
    * Output:
    * Returns view template
    */
    public function applicationsettings(Request $request)
    {
        try {
            //fetch application settings fields
            $applicationSettings = DB::table('APPLICATION_SETTINGS')
                                        ->select('FIELD_NAME','FIELD_VALUE','SECURE','COMMENTS')
                                        ->orderBy('FIELD_NAME','ASC')
                                        ->get()->toArray();
            //render applicationsettings template
            return view('admin.applicationsettings')->with('applicationSettings',$applicationSettings)
                                                ;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    * Method Name: updateSettings
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to update applciation settings
    *
    * Input Params:
    * @$field_name,@$field_value,@$secure
    *
    * Output:
    * Returns Json
    */
    public function updateSettings(Request $request)
    {
        try{
            if ($request->ajax()) {
                //fetch data from request
                $requestData = $request->get('data');
                //remove extra columns which are not updated
                $updateData = Arr::except($requestData, ['functionName','field_name']);
                //check field is secure or not
                if($requestData['secure'] == 1){
                    //fetch filed value from encryption
                    $updateData['field_value'] = CommonFunctions::encrypt256($updateData['field_value'],CommonFunctions::getrandomIV());
                }
                //update application settings
                $updateSettings = DB::table('APPLICATION_SETTINGS')->where('FIELD_NAME',$requestData['field_name'])
                                                ->update($updateData);

                $givenInput = trim($requestData['field_value']);
                $msg = 'NotSet';
                if(substr($requestData['field_name'],0,1) == '_' && $givenInput != ''){
                    $token = explode('_', $givenInput);
                    Cache::put($requestData['field_name'], $token[0], now()->addMinutes(15));  
                    $msg = 'debugUser: '.$token[0].' set!';
                }
                                 
                if($updateSettings)
                {
                    return json_encode(['status'=>'success','msg'=>'Settings Updated Successfully','data'=>[$msg]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
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