<?php

namespace App\Http\Controllers\ExtApi;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use Illuminate\Http\Request;
use Redirect;
use Session;
use Cookie;
use Crypt;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Config;
Use Cache;

class UAMApiController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/UserActivityLogController','useractivitylog','Unauthorized attempt detected by '.$this->userId,'','','1');
                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

    public function uamapicall(){
        try{

            return view('admin/uamapi');

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function usercreatewithrole(Request $request)
    {
        try{
            
            $empNo = $request->empid;
            $roleId = $request->roleid;
            $rmCode = $request->rmcode;
            $filtertype = $request->filtertype;
            $filterid = $request->filterid;
            $getReqApiKey = $request->EXT_UAM_API_KEY;

            $getreqIp = $request->ip();
            // Self::preventMaximuntryApi($getreqIp);
            
            $getIp = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_IP');
            $apiKey = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_KEY');

            // if($getIp != $getreqIp){
            //     echo "Unauthorized attempt detected.";
            //     Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getreqIp);
            //     die();
            // }
            if($apiKey != $getReqApiKey){
                echo "Unauthorized attempt detected.";
                Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getReqApiKey);
                die();
            }

            $requestData = array();
            $employeeApiDetails = array();
            $employeeDetails = DB::table('USERS')->where('HRMSNO',$empNo)->get()->toArray();
           
            if(count($employeeDetails) > 0){
                Log::channel('uamuseradddeleteeditlog')->info('User Already present in table -'.$empNo);
                return json_encode(['status'=>'fail','msg'=>'User Already present in table.','data'=>[]]);  
            }

            if($empNo != ''){
                $employeeApiDetails = Api::hrapiconnection($empNo);
         
                if($employeeApiDetails == ''){
                    return json_encode(['status'=>'fail','msg'=>'Api Failed Please try again later.','data'=>[]]);  
                }
                $employeeApiDetails = (array) $employeeApiDetails;
              
            }else{
                Log::channel('uamuseradddeleteeditlog')->info('Please enter mandatory HRMS number -'.$empNo);
                return json_encode(['status'=>'fail','msg'=>'Please enter mandatory HRMS number.','data'=>[]]);  
            }

            if($roleId == '2'){
                $sourceCodeDetails = CommonFunctions::fetchSourceCodeDetails($empNo);
                if (!$sourceCodeDetails) {
                    Log::channel('uamuseradddeleteeditlog')->info('Source code does not exist in Finacle -'.$empNo);
                    return json_encode(['status'=>'fail','msg'=>'Source code does not exist in Finacle','data'=>[]]);
                }
            }

            if ($rmCode == '' && $roleId == '2') {
                Log::channel('uamuseradddeleteeditlog')->info('Please enter mandatory RM Code -'.$rmCode);
                return json_encode(['status'=>'fail','msg'=>'Please enter mandatory RM Code.','employeeDetails'=>$employeeDetails]);    
            }

            if(env('APP_SETUP') != 'DEV'){

                if ($rmCode != '') {
                    $fetchRmCodeDetails = CommonFunctions::fetchRmCodeDetails($rmCode, $empNo);
                    if (!$fetchRmCodeDetails) {
                        Log::channel('uamuseradddeleteeditlog')->info('RM code does not exist in Finacle -'.$rmCode);
                        return json_encode(['status'=>'fail','msg'=>'RM code does not exist in Finacle','data'=>[]]);
                    }
                }
            }
            
            $requestData['HRMSNO'] = $empNo;
            $requestData['EMPSOL'] = $employeeApiDetails['EMPSOL'];
            $requestData['ROLE'] = $roleId;
            $requestData['EMPTITLE'] = $employeeApiDetails['EMPTITLE'];
            $requestData['EMP_FIRST_NAME'] = $employeeApiDetails['EMP_FIRST_NAME'];
            $requestData['EMP_MIDDLE_NAME'] = $employeeApiDetails['EMP_MIDDLE_NAME'];
            $requestData['EMP_LAST_NAME'] = $employeeApiDetails['EMP_LAST_NAME'];
            $requestData['EMPGENDER'] = $employeeApiDetails['EMPGENDER'];
            $requestData['EMPMOBILENO'] = $employeeApiDetails['EMPMOBILENO'];
            $requestData['EMPEMAILID'] = $employeeApiDetails['EMPEMAILID'];
            $requestData['EMPLDAPUSERID'] = $employeeApiDetails['EMPLDAPUSERID'];
            $requestData['EMPBUSINESSUNIT'] = $employeeApiDetails['EMPBUSINESSUNIT'];
            $requestData['EMPLOCATION'] = $employeeApiDetails['EMPLOCATION'];
            $requestData['EMPREGION'] = $employeeApiDetails['EMPREGION'];
            $requestData['EMPSTATE'] = $employeeApiDetails['EMPSTATE'];
            $requestData['EMPBRANCH'] = $employeeApiDetails['EMPBRANCH'];
            $requestData['RM_CODE'] = $rmCode;
            $requestData['EMPSTATUS']  = 'Y';
            $requestData['CREATED_BY']  = Session::get('userId');
            $requestData['CREATED_AT']  = Carbon::now();

            if($roleId == 14){
                $requestData['FILTER_TYPE']  = '';
                $requestData['FILTER_IDS']  = '';
            }
        
            DB::beginTransaction();
            $users = DB::table('USERS')->insertGetId($requestData);
            $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$requestData['CREATED_BY'],$users,'Add User: '.$roleId,'',$roleId);
            $addUAMUpdateUserLog = CommonFunctions::uamcreateAddEditUserLog($request,$requestData['CREATED_BY'],$users,'Add User: '.$roleId,'',$roleId);

            if($users){
                DB::commit();
                Log::channel('uamuseradddeleteeditlog')->info('User Details Added Successfully -'.$empNo);
                return json_encode(['status'=>'success','msg'=>'User Details Added Successfully.','employeeDetails'=>$requestData]);    
            }else{
                DB::rollback();
                Log::channel('uamuseradddeleteeditlog')->info('Error! Please try again later -'.$empNo);
                return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','employeeDetails'=>$requestData]);
            }
           
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateuser(Request $request)
    {
        try{
            $empNo = $request->empid;
            $roleId = $request->roleid;
            $rmCode = $request->rmcode;
            $filtertype = $request->filtertype;
            $filterid = $request->filterid;
            $getReqApiKey = $request->EXT_UAM_API_KEY;

            $getreqIp = $request->ip();
            // Self::preventMaximuntryApi($getreqIp);
            $getIp = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_IP');
            $apiKey = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_KEY');

            // if($getIp != $getreqIp){
            //     echo "Unauthorized attempt detected.";
            //     Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getreqIp);
            //     die();
            // }
            if($apiKey != $getReqApiKey){
                echo "Unauthorized attempt detected.";
                Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getReqApiKey);
                die();
            }

            $requestData = array();
            $employeeApiDetails = array();
            $employeeDetails = DB::table('USERS')->where('HRMSNO',$empNo)->get()->toArray();

            if($empNo != ''){
                $employeeApiDetails = Api::hrapiconnection($empNo);
              
                if($employeeApiDetails == ''){
                    return json_encode(['status'=>'fail','msg'=>'Api Failed Please try again later.','data'=>[]]);  
                }
                  $employeeApiDetails = (array) $employeeApiDetails;
            }else{
                Log::channel('uamuseradddeleteeditlog')->info('Please enter mandatory HRMS number -'.$empNo);
                return json_encode(['status'=>'fail','msg'=>'Please enter mandatory HRMS number.','data'=>[]]);  
            }

            if($roleId == '2'){
                $sourceCodeDetails = CommonFunctions::fetchSourceCodeDetails($empNo);
                if (!$sourceCodeDetails) {
                    Log::channel('uamuseradddeleteeditlog')->info('Source code does not exist in Finacle -'.$empNo);
                    return json_encode(['status'=>'fail','msg'=>'Source code does not exist in Finacle','data'=>[]]);
                }
            }

            if ($rmCode == '' && $roleId == '2') {
                Log::channel('uamuseradddeleteeditlog')->info('Please enter mandatory RM Code -'.$rmCode);
                return json_encode(['status'=>'fail','msg'=>'Please enter mandatory RM Code.','employeeDetails'=>$employeeDetails]);    
            }

            if(env('APP_SETUP') != 'DEV'){

                if ($rmCode != '') {
                    $fetchRmCodeDetails = CommonFunctions::fetchRmCodeDetails($rmCode, $empNo);
                    if (!$fetchRmCodeDetails) {
                        Log::channel('uamuseradddeleteeditlog')->info('RM code does not exist in Finacle -'.$rmCode);
                        return json_encode(['status'=>'fail','msg'=>'RM code does not exist in Finacle','data'=>[]]);
                    }
                }
            }
                
            $requestData['HRMSNO'] = $empNo;
            $requestData['EMPSOL'] = $employeeApiDetails['EMPSOL'];
            $requestData['ROLE'] = $roleId;
            $requestData['EMPTITLE'] = $employeeApiDetails['EMPTITLE'];
            $requestData['EMP_FIRST_NAME'] = $employeeApiDetails['EMP_FIRST_NAME'];
            $requestData['EMP_MIDDLE_NAME'] = $employeeApiDetails['EMP_MIDDLE_NAME'];
            $requestData['EMP_LAST_NAME'] = $employeeApiDetails['EMP_LAST_NAME'];
            $requestData['EMPGENDER'] = $employeeApiDetails['EMPGENDER'];
            $requestData['EMPMOBILENO'] = $employeeApiDetails['EMPMOBILENO'];
            $requestData['EMPEMAILID'] = $employeeApiDetails['EMPEMAILID'];
            $requestData['EMPLDAPUSERID'] = $employeeApiDetails['EMPLDAPUSERID'];
            $requestData['EMPBUSINESSUNIT'] = $employeeApiDetails['EMPBUSINESSUNIT'];
            $requestData['EMPLOCATION'] = $employeeApiDetails['EMPLOCATION'];
            $requestData['EMPREGION'] = $employeeApiDetails['EMPREGION'];
            $requestData['EMPSTATE'] = $employeeApiDetails['EMPSTATE'];
            $requestData['EMPBRANCH'] = $employeeApiDetails['EMPBRANCH'];
            $requestData['RM_CODE'] = $rmCode;
            $requestData['EMPSTATUS']  = 'Y';
            $requestData['UPDATED_BY']  = Session::get('userId');
            $requestData['UPDATED_AT']  = Carbon::now();

            if($roleId == 14){
                $requestData['FILTER_TYPE']  = '';
                $requestData['FILTER_IDS']  = '';
            }
            DB::beginTransaction();
            $users = DB::table('USERS')->where('HRMSNO',$empNo)->update($requestData);
            $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,Session::get('userId'),$users,'Update User: '.$roleId,'',$roleId);
            $addUAMUpdateUserLog = CommonFunctions::uamcreateAddEditUserLog($request,Session::get('userId'),$users,'Update User: '.$roleId,'',$roleId);

            if($users){
                DB::commit();
                Log::channel('uamuseradddeleteeditlog')->info('User Details Updated Successfully -'.$empNo);
                return json_encode(['status'=>'success','msg'=>'User Details Updated Successfully.','employeeDetails'=>$requestData]);    
            }else{
                DB::rollback();
                Log::channel('uamuseradddeleteeditlog')->info('Error! Please try again later -'.$empNo);
                return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','employeeDetails'=>$requestData]);
            }

        }catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function userdeactivate(Request $request){
        try{
            $empNo = $request->empid;
            $status = $request->status;
            $getReqApiKey = $request->EXT_UAM_API_KEY;
            $getreqIp = $request->ip();
            // Self::preventMaximuntryApi($getreqIp);
            $getIp = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_IP');
            $apiKey = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_KEY');

            // if($getIp != $getreqIp){
            //     echo "Unauthorized attempt detected.";
            //     Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getreqIp);
            //     die();
            // }
            if($apiKey != $getReqApiKey){
                echo "Unauthorized attempt detected.";
                Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getReqApiKey);
                die();
            }

            if($status == 'D' || $status == 'N'){
            }else{
                Log::channel('uamuseradddeleteeditlog')->info('Please Give Valid Status -'.$empNo);
                return json_encode(['status'=>'success','msg'=>'Please Give Valid Status.','data'=>[]]);
            }
            $employeeDetails = DB::table('USERS')->where('HRMSNO',$empNo)->get()->toArray();
            $currDate = Carbon::now();
            $update_at = Session::get('userId');
            if(count($employeeDetails)>0){
                DB::beginTransaction();
                $employeeDetails = DB::table('USERS')->where('HRMSNO',$empNo)->update(['EMPSTATUS'=>$status,
                                                                                        'UPDATED_AT'=>$currDate,
                                                                                        'UPDATED_BY'=>$update_at
                                                                                        ]);
                    
                $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$update_at,json_encode($employeeDetails),'Deactivate User: ','','');
                $addUAMUpdateUserLog = CommonFunctions::uamcreateAddEditUserLog($request,$update_at,json_encode($employeeDetails),'Deactivate User: ','','');

                if($employeeDetails){
                    DB::commit();
                    Log::channel('uamuseradddeleteeditlog')->info('User Deactivated Successfully -'.$empNo);
                    return json_encode(['status'=>'success','msg'=>'User Deactivated Successfully.','employeeDetails'=>$empNo]);    
                }else{
                    DB::rollback();
                    Log::channel('uamuseradddeleteeditlog')->info('Error! Please try again later -'.$empNo);
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','employeeDetails'=>$empNo]);
                }
            }else{
                Log::channel('uamuseradddeleteeditlog')->info('User not present in table -'.$empNo);
                return json_encode(['status'=>'fail','msg'=>'User not present in table.','data'=>$empNo]);
            }

        }catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function useractivate(Request $request){
        try{
            $empNo = $request->empid;
            $getReqApiKey = $request->EXT_UAM_API_KEY;
            $getreqIp = $request->ip();
            
            // Self::preventMaximuntryApi($getreqIp);
            $getIp = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_IP');
            $apiKey = Config('constants.APPLICATION_SETTINGS.EXT_UAM_API_KEY');

            // if($getIp != $getreqIp){
            //     echo "Unauthorized attempt detected.";
            //     Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getreqIp);
            //     die();
            // }
            if($apiKey != $getReqApiKey){
                echo "Unauthorized attempt detected.";
                Log::channel('uamuseradddeleteeditlog')->info('Unauthorized attempt detected -'.$getReqApiKey);
                die();
            }

            $employeeDetails = DB::table('USERS')->select('EMPSTATUS')->where('HRMSNO',$empNo)->where('EMPSTATUS','N')->get()->toArray();
            $currDate = Carbon::now();
            $update_at = Session::get('userId');

            $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$update_at,json_encode($employeeDetails),'Activated User','','');
            $addUAMUpdateUserLog = CommonFunctions::uamcreateAddEditUserLog($request,$update_at,json_encode($employeeDetails),'Activated User','','');
            if(count($employeeDetails)>0){

                $employeeDetails = (array) current($employeeDetails);
                
                DB::beginTransaction();
                $employeeDetails = DB::table('USERS')->where('HRMSNO',$empNo)->update(['EMPSTATUS'=>'Y',
                                                                                        'UPDATED_AT'=>$currDate,
                                                                                        'UPDATED_BY'=>$update_at
                                                                                        ]);
                if($employeeDetails){
                    DB::commit();
                    Log::channel('uamuseradddeleteeditlog')->info('User Activated Successfully -'.$empNo);
                    return json_encode(['status'=>'success','msg'=>'User Activated Successfully.','employeeDetails'=>$empNo]);    
                }else{
                    DB::rollback();
                    Log::channel('uamuseradddeleteeditlog')->info('Error! Please try again later -'.$empNo);
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','employeeDetails'=>$empNo]);
                }
            
            }else{
                Log::channel('uamuseradddeleteeditlog')->info('Permanent deleted user cannot be activated -'.$empNo);
                return json_encode(['status'=>'fail','msg'=>'Permanent deleted user cannot be activated.','data'=>$empNo]);
            }
        }catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function preventMaximuntryApi($getreqIp){

        $maxTry = Config('constants.APPLICATION_SETTINGS.MAX_TRY_API_CALL');
        $ctn = 1;
        $cacheIp = Cache::get($getreqIp);
        $checkBlockIp = Cache::get($getreqIp.'-block');

        if($checkBlockIp != 'Y'){

            if($cacheIp != ''){

                $getValue = explode('-',$cacheIp);
                $getCacheIp = $getValue[0];
                $getCacheCtn = $getValue[1];

                if($getCacheIp == $getreqIp){
                    if($getCacheCtn <= $maxTry){
                        $ctn = $getCacheCtn+1;
                        Cache::put($getreqIp,$getreqIp.'-'.$ctn);
                    }else{
                        Cache::put($getreqIp.'-block','Y',now()->addHours(24));
                        echo "Maximun Attempt try";
                        die();
                    }
                }
            }else{
                Cache::put($getreqIp,$getreqIp.'-'.$ctn,now()->addMinutes(1));
            }
        }else{
            echo "Today limit over.";
            die();
        }
    }
}
