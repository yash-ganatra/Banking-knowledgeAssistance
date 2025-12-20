<?php

namespace App\Http\Controllers\NPC;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;
use App\Helpers\Api;
use App\Helpers\CurrentApi;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use DB;

class PrivilegeAccessController extends Controller
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
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if($this->roleId != 4){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('NPC/PrivilegeAccessController','privilegeaccess','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /*
     * Method Name: privilegeaccess
     * Created By : Sharanya T
     * Created At : 24-03-2020

     * Description:
     * Method to show npc review template
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function privilegeaccess(Request $request)
    {
        try {
            $customerNames = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ACCOUNT_DETAILS.AOF_NUMBER',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME || ' - ' || ACCOUNT_DETAILS.AOF_NUMBER AS user_name"),'FINCON.ID')
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->leftjoin('FINCON','FINCON.FORM_ID','ACCOUNT_DETAILS.ID')
                            ->where('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE', '1')
                            ->whereNull('ACCOUNT_DETAILS.EXTERNAL_ID')
                            ->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(2))
                            //->where('ACCOUNT_DETAILS.APPLICATION_STATUS','!=', '1')
                            //->where('FINCON.FORM_ID','=', '')
                            //->whereNotNull('LAST_NAME')
                            ->whereNotNull('FINCON.FORM_ID')
                            //->where(['L2_CLEARED_STATUS'=>1,'FUND_TRANSFER_STATUS'=>0])
                            // ->orWhere(function($query) {
                            //         // $query->whereNull('query_id')
                            //         $query->where('dedupe_status','=','Pending')
                            //                 ->orWhereNotNull('query_id');
                            // })
							->orderBy('CUSTOMER_OVD_DETAILS.ID','DESC')
                            ->pluck('user_name','aof_number')->toArray();

            //returns to template
            return view('npc.privilege')->with('customerNames',$customerNames);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkTdAccountcreated(request $request)
    {
        try {

            if ($request->ajax())
            {
               $requestData = $request->get('data');

                $formId = $requestData['form_id'];

                $accountData = DB::table('ACCOUNT_DETAILS')
                                     ->where('ACCOUNT_DETAILS.ID',$formId)
                                     ->get()->toArray();
               $accountData = (array) current($accountData);

                $checkTdAccountcreatedFunciton = self::checkTdAccountcreatedFunciton($accountData, $formId, 'controller');
                //echo "<pre>";print_r($checkTdAccountcreatedFunciton != '');exit;
                if ($checkTdAccountcreatedFunciton != '') {
                   return $checkTdAccountcreatedFunciton;
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Data Error! Please try again','data'=>[]]);
                }
            }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



    public static function checkTdAccountcreatedFunciton($accountData, $formId, $formCall = '')
    {
        try {


               $finacle_table = env('FINACLE_TABLE_HIST');
               $finacleQueryHist = DB::connection('oracle2')->table($finacle_table)
                                       ->select('AOF_NUMBER','FORACID','TEXT')
                                       ->where('AOF_NUMBER',$accountData['aof_number'])
                                       ->get()->toArray();
                $finacleQueryHist = (array) current($finacleQueryHist);

               if(isset($finacleQueryHist['foracid']) && $finacleQueryHist['foracid'] != ''){
                   
                   $updateAccountDetails = [
                                             'TD_ACCOUNT_NO'=>$finacleQueryHist['foracid'],
                                             'APPLICATION_STATUS'=> '15'
                                            ];

           
                   $saveComments = DB::table('ACCOUNT_DETAILS')->where('ACCOUNT_DETAILS.ID',$formId)->update($updateAccountDetails);				   
				   NotificationController::processNotification($formId,'ACCOUNTNO_CREATED');
                   
                   $comments = 'Via Fincale ';
                   $udpateStatus =  CommonFunctions::saveStatusDetails($formId,'24',$comments);                                        
                   $udpateStatus =  CommonFunctions::saveStatusDetails($formId,'25',$comments);

                   if($accountData['account_type'] == 3){
                     $udpateTDFTRStatus =  CommonFunctions::updateTDFtrStatusY($formId);
                   }

                   DB::commit();
                    DB::disconnect('oracle2');
                   return json_encode(['status'=>'success','msg'=>'TD Account Created','data'=>[]]);
               }else{
                    DB::disconnect('oracle2');

                   if(isset($finacleQueryHist['text']) && $finacleQueryHist['text'] != ''){

                        $saveComments = DB::table('ACCOUNT_DETAILS')->where('ACCOUNT_DETAILS.ID',$formId)->update(['TD_RESPONSE'=>$finacleQueryHist['text']]);
                        DB::commit();

                        if ($formCall != 'cron') {
                            return json_encode(['status'=>'fail','msg'=>'TD Error: '.$finacleQueryHist['text'],'data'=>[$finacleQueryHist['text']]]);
                        }else{
                            // return "TD not found";
                            return true;

                        }

                   }else{

                        if ($formCall != 'cron') {
                            return json_encode(['status'=>'fail','msg'=>'TD Account number Not created','data'=>[]]);
                        }else{
                            // return "TD not found";
                            return true;

                        }
                   }
               }

            

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/PrivilegeAccessController','checkTdAccountcreatedFunciton',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

	// Called by PrivilegeAccess screen when an AOF is selected
    public function accountdetails(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                //echo "<pre>";print_r($requestData);exit;
                $fundingStatus = true;
                $finacleHist = '';
                if($requestData['aof_tracking_no'] != '')
                {
                    $aofNumber = $requestData['aof_tracking_no'];
                }
                if(isset($requestData['customerName']))
                {
                    $aofNumber = $requestData['customerName'];
                }

                $checktdexist = DB::table('ACCOUNT_DETAILS')
                                       ->select('TD_ENTRY_DONE','TD_RESPONSE')
                                       ->where('AOF_NUMBER',$requestData['aof_tracking_no'])
                                       ->get()->toArray();
                $checktdexist = (array) current($checktdexist);


            //    $finacle_table_hist = env('FINACLE_TABLE_HIST');
            //    $finacleHist = DB::connection('oracle2')->table($finacle_table_hist)
            //                            //->select('AOF_NUMBER','FORACID')
            //                            ->where('AOF_NUMBER',$aofNumber)
            //                            ->exists();
            //                            // ->get()->toArray();
            //     //$finacleHist = (array) current($finacleHist);
            //     DB::disconnect('oracle2');


                $checkAOFNumber = DB::table('ACCOUNT_DETAILS')
                                        ->select('ACCOUNT_DETAILS.ID')
                                        ->leftjoin('CUSTOMER_OVD_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                                        ->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE', 'ASC')
                                        ->where('AOF_NUMBER',$aofNumber)
                                        //->where(['L2_CLEARED_STATUS'=>1,'FUND_TRANSFER_STATUS'=>0])
                                        // ->orWhere(function($query) {
                                        //         // $query->whereNull('query_id')
                                        //     $query->where('dedupe_status','=','Pending')
                                        //         ->orWhereNull('query_id');
                                        // })
                                        //or if qid is empty or dedupe status is empty or pending
                                        ->get()->toArray();
                $checkAOFNumber = (array) current($checkAOFNumber);

                $checkAccountHolder = DB::table('ACCOUNT_DETAILS')
                                        ->leftjoin('CUSTOMER_OVD_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                                        ->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE', 'ASC')
                                        ->where('AOF_NUMBER',$aofNumber)
                                        ->get()->toArray();
                //$checkAccountHolder = (array) current($checkAccountHolder);
                //echo "<pre>";print_r($checkAccountHolder['aof_number']);exit;
                $checkCC = DB::table('ACCOUNT_DETAILS')
                                        ->where('AOF_NUMBER',$aofNumber)
                                        ->where('SOURCE','=','CC')
                                        ->get()->toArray();
                $callcenter=false;
                if(count($checkCC) > 0){
                    $callcenter=true;
                }

                if(count($checkAOFNumber) > 0)
                {
                    $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                                ->where('FORM_ID',$checkAOFNumber['id'])
                                                ->orderBy('APPLICANT_SEQUENCE', 'ASC')
                                                ->get()->toArray();
                     $customerDetails = (array) current($customerDetails);

                    $accountDetails = DB::table('ACCOUNT_DETAILS')
                                                ->whereId($checkAOFNumber['id'])
                                                ->get()->toArray();
                    $accountDetails = (array) current($accountDetails);

                    // $fundingStatusDetails = DB::table('CHEQUE_CLEARED')
                    //                                 ->where('INSTRMNT_ID',$aofNumber)
                    //                                 ->get()->toArray();

                    // if(count($fundingStatusDetails) == 0)
                    // {
                    //     $fundingStatus = false;
                    // }

                    // if(!env('CHECK_FUNDING'))
                    // {
                    //     //for testing purpose
                    //     $fundingStatus = true;
                    // }

                    $fundingStatus = DB::table('FINCON')
                                                ->where('FORM_ID',$checkAOFNumber['id'])
                                                ->get()->toArray();
                    $fundingStatus = (array) current($fundingStatus);
                 
                    $custIds = CommonFunctions::getCustIds($checkAOFNumber['id']);
					$custIds = (array) current($custIds);
					$custIdCount = 0;
					for($c=0; $c<count($custIds); $c++){
						if($custIds[$c]!='') $custIdCount++;
					}										
                    if($custIdCount != count($custIds)){
						$custIdButtonReqd = true;
					}else{
						$custIdButtonReqd = false;
					}
					
					$accountIds = CommonFunctions::getAccountIds($checkAOFNumber['id']);
					//$accountIds = (array) current($accountIds);
					if($accountIds['SA'] ==''){
						$acctIdButtonReqd = true;
					}else{
						$acctIdButtonReqd = false;
					}
					
					$dedupeArray = CommonFunctions::getDedupeStatus($checkAOFNumber['id'],'GEN_QUERY');
					// $dedupeArray = (array) current($dedupeArray);
                     if($dedupeArray == 'true'){
						$ddQIDButtonReqd = false; 	
					}else{
                        $ddQIDButtonReqd = true;
					}

					$dedupeArray = CommonFunctions::getDedupeStatus($checkAOFNumber['id'],'DEDUPE_STATUS');
                    if($dedupeArray == 'true'){
                        $ddStatusButtonReqd = false;					
					}else{
						$ddStatusButtonReqd = true; 												
					}
					// $ddQIDCount = 0; 
					// $ddNoMatchCount = 0;
					
					//$ddQIDs = array_keys($dedupeArray);					
															
					// for($c=0; $c<count($dedupeArray); $c++){
					// 	if(trim($ddQIDs[$c])!='') ++$ddQIDCount;
					// 	if(trim($dedupeArray[$ddQIDs[$c]]) == 'No Match') ++$ddNoMatchCount;		
					// }
                    // echo "<pre>";print_r($dedupeArray);
                    // echo "teste<pre>";print_r($ddQIDCount);exit;
                    // if($ddQIDCount != count($dedupeArray)){
					// 	$ddQIDButtonReqd = true; 						
					// }else{
					// 	$ddQIDButtonReqd = false;
					// }					

					
                    // if($ddNoMatchCount != count($dedupeArray)){						
					// 	$ddStatusButtonReqd = true; 												
					// }else{
					// 	$ddStatusButtonReqd = false;
					// }

                    if($accountDetails['delight_scheme'] == '5'){
                        $ddStatusButtonReqd = false;
                    }
                    // if ($customerDetails['is_new_customer'] == 0) { // for ETB Dedupe Qid & status not required at moment
                    //     $ddQIDButtonReqd = false;
                    //     $ddStatusButtonReqd = false;
                    // }

                    $tdApiShow = CommonFunctions::getapplicationSettingsDetails('TD_ACCOUNT');

       

                $currentdetails=DB::table('ENTITY_DETAILS')->where('FORM_ID',$checkAOFNumber['id'])
                                                           ->get()->toArray();
                $currentdetails=(array) current($currentdetails);                                     
					//echo '<pre>'; print_r($ddNoMatchCount); print_r(count($dedupeArray)); exit;
					
                    return view('npc.accountdetails1')->with('customerDetails',$customerDetails)
                                                    ->with('accountDetails',$accountDetails)
                                                    ->with('checkAccountHolder',$checkAccountHolder)
                                                    ->with('fundingStatus',$fundingStatus)
                                                    ->with('currentdetails',$currentdetails)
                                                    ->with('tdApiShow',$tdApiShow)
                                                    //->with('fundingStatusDetails',$fundingStatusDetails)
                                                    ->with('checktdexist',$checktdexist)
                                                    ->with('finacleHist',$finacleHist)
                                                    ->with('formId',$checkAOFNumber['id'])
													->with('custIds', $custIds)
													->with('accountIds', $accountIds)
													->with('custIdButtonReqd', $custIdButtonReqd)
													->with('acctIdButtonReqd', $acctIdButtonReqd)
													->with('ddQIDButtonReqd', $ddQIDButtonReqd)
													->with('ddStatusButtonReqd', $ddStatusButtonReqd)
                                                    ->with('callcenter', $callcenter)
                                                    ;
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Can you please check AOF Number','data'=>[]]);
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
