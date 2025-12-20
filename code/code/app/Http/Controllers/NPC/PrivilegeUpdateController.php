<?php

namespace App\Http\Controllers\NPC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Helpers\CommonFunctions;
use App\Helpers\Rules;
use App\Helpers\EncryptDecrypt;
use App\Helpers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use DB;


class PrivilegeUpdateController extends Controller
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

            if($this->roleId != 8){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('NPC/PrivilegeUpdateController','privilegeupdate','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    public function privilegeupdate(){

    	try {
            $customerNames = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ACCOUNT_DETAILS.AOF_NUMBER',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME || ' - ' || ACCOUNT_DETAILS.AOF_NUMBER AS user_name"),'FINCON.ID')
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->leftjoin('FINCON','FINCON.FORM_ID','ACCOUNT_DETAILS.ID')
                            ->where('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE', '1')
                            //->where('ACCOUNT_DETAILS.APPLICATION_STATUS','!=', '1')
                            //->where('FINCON.FORM_ID','=', '')
                            //->whereNotNull('LAST_NAME')
                            ->whereNotNull('FINCON.FORM_ID')
                            ->where('ACCOUNT_DETAILS.CREATED_AT','>',Carbon::now()->subMonths(3))
                            //->where(['L2_CLEARED_STATUS'=>1,'FUND_TRANSFER_STATUS'=>0])
                            // ->orWhere(function($query) {
                            //         // $query->whereNull('query_id')
                            //         $query->where('dedupe_status','=','Pending')
                            //                 ->orWhereNotNull('query_id');
                            // })
							->orderBy('ACCOUNT_DETAILS.ID','DESC')
                            ->pluck('user_name','aof_number')->toArray();

            // echo "<pre>";print_r($customerNames);exit;
            //returns to template
            return view('npc.privilegeupdate')->with('customerNames',$customerNames);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/PrivilegeUpdateController','privilegeupdate',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    // Called by PrivilegeAccess screen when an AOF is selected
    public function privilegeupdateaccountdetails(Request $request)
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


               $finacle_table_hist = env('FINACLE_TABLE_HIST');
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

                    $kycUpdates = CommonFunctions::getKycUpdates($checkAOFNumber['id']);
                    $kycUpdates = (array) current($kycUpdates);

                    $internetBankUpdates = CommonFunctions::getInternetBankUpdates($checkAOFNumber['id']);
                    $internetBankUpdates = (array) current($internetBankUpdates);


                    $kycupdate = 0;
                    for($c=0; $c<count($kycUpdates); $c++){
                        if($kycUpdates[$c]!='') $kycupdate++;
                    }                                       
                    if($kycupdate != count($kycUpdates)){
                        $kycUpdateButtonReqd = true;
                    }else{
                        $kycUpdateButtonReqd = false;
                    }

                    $internetBankUpdate = 0;
                    for($c=0; $c<count($internetBankUpdates); $c++){
                        if($internetBankUpdates[$c]!='') $internetBankUpdate++;
                    }                                       
                    if($internetBankUpdate != count($internetBankUpdates)){
                        $internetBankUpdatesBtn = true;
                    }else{
                        $internetBankUpdatesBtn = false;
                    }

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
					// $dedupeArray = (array) current($dedupeArray);
					
					// $ddQIDCount = 0; 
					// $ddNoMatchCount = 0;
					
					// $ddQIDs = array_keys($dedupeArray);					
															
					// for($c=0; $c<count($dedupeArray); $c++){
					// 	if(trim($ddQIDs[$c])!='') ++$ddQIDCount;
					// 	if(trim($dedupeArray[$ddQIDs[$c]])=='No Match') ++$ddNoMatchCount;		
					// }

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

                    $application_status= DB::table('ACCOUNT_DETAILS')->whereId($checkAOFNumber['id'])
                                                        ->pluck('application_status')->toArray();
                    
                    $application_status = current($application_status);
                    // echo "<pre>";print_r($application_status);exit;


                    $updatenextrole = config('constants.L3_NEXT_ROLE_UPDATE');

                    $entityAccountNumber = DB::table('ENTITY_DETAILS')->select('ENTITY_ACCOUNT_NO')
                                                                     ->where('FORM_ID',$checkAOFNumber['id'])
                                                                     ->get()
                                                                     ->toArray();
                    $entityAccountNumber = (array) current($entityAccountNumber);
					
                    return view('npc.privilegeupdateaccountdetails')->with('customerDetails',$customerDetails)
                                                    ->with('accountDetails',$accountDetails)
                                                    ->with('checkAccountHolder',$checkAccountHolder)
                                                    ->with('fundingStatus',$fundingStatus)
                                                    //->with('fundingStatusDetails',$fundingStatusDetails)
                                                    ->with('checktdexist',$checktdexist)
                                                    ->with('updatenextrole',$updatenextrole)
                                                    ->with('finacleHist',$finacleHist)
                                                    ->with('formId',$checkAOFNumber['id'])
                                                    ->with('custIds', $custIds)
													->with('kycUpdates', $kycUpdates)
													->with('accountIds', $accountIds)
                                                    ->with('custIdButtonReqd', $custIdButtonReqd)
													->with('kycUpdateButtonReqd', $kycUpdateButtonReqd)
                                                    ->with('internetBankUpdatesBtn', $internetBankUpdatesBtn)
													->with('acctIdButtonReqd', $acctIdButtonReqd)
													->with('ddQIDButtonReqd', $ddQIDButtonReqd)
                                                    ->with('ddStatusButtonReqd', $ddStatusButtonReqd)
													->with('application_status', $application_status)
                                                    ->with('entityAccountNumber',$entityAccountNumber)
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

    

    public function updatededupestatus(Request $request){
       try{
              
            $requestData = Arr::except($request->get('data'),'functionName');
           
           if(count($requestData['DdupeDetails']) > 0){
                foreach ($requestData['DdupeDetails'] as $key => $DdupeDetails) {
                   
                   $dedupe = ucwords(strtolower($DdupeDetails));

                    if((isset($dedupe)) && ($dedupe != 'No Match')){

                        return json_encode(['status'=>'fail','msg'=>'Error! Please Enter valid status','data'=>[]]);
                    }

                    $DdupeDetails = preg_replace('/[^A-Za-z0-9 \-]/','',$DdupeDetails);
                   $dedupestatusupdate = DB::table('CUSTOMER_OVD_DETAILS')
                                ->where('FORM_ID',$requestData['formId'])
                                ->where('applicant_sequence',$key)
                                ->update(['DEDUPE_STATUS'=>ucwords(strtolower($DdupeDetails))]);
                   DB::commit();
                   
                   $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updatededupestatus',$requestData['formId'].' : '.$requestData['dedupe_comment'][$key],'',ucwords(strtolower($DdupeDetails)),Session::get('userId'));

                }
            }
            
            if($dedupestatusupdate){
                return json_encode(['status'=>'success','msg'=>'Dedupe Status is Updated successfully','data'=>[]]);
            }else{
                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatecustomerid(Request $request){
       try{
              
            $requestData = Arr::except($request->get('data'),'functionName');
        
            if(count($requestData['CustomerId']) > 0){
                foreach ($requestData['CustomerId'] as $key => $CustomerId) {
                    $custIupdate = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where('FORM_ID',$requestData['formId'])
                                        ->where('CUSTOMER_ID',null)
                                        ->where('applicant_sequence',$key)
                                        ->update(['CUSTOMER_ID'=>$CustomerId]);

                    DB::commit();
                    Rules::postCustomerIdApiQueue($requestData['form_id'],$CustomerId);
                    $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updatecustomerid',$requestData['formId'].' : '.$requestData['customer_id_comment'][$key],'',$CustomerId,Session::get('userId'));
					$saveStatus = CommonFunctions::saveStatusDetails($requestData['formId'],'22');
                    DB::commit();
                }
            }


            if($custIupdate){
                $customNotif = CommonFunctions::processCustomerNotification($requestData['formId'],'CUSTID_EMAIL');
                $notif = NotificationController::processNotification($requestData['formId'],'CUSTID_CREATED');

                return json_encode(['status'=>'success','msg'=>'Customer id is Updated successfully','data'=>[]]);
            }else{
                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateinternetbank(Request $request){
        try{
            $requestData = Arr::except($request->get('data'),'functionName');
             $getCodData = DB::table('CUSTOMER_OVD_DETAILS')->select('CUSTOMER_ID','INTERNET_BANKING')->where('FORM_ID',$requestData['formId'])->whereNull('INTERNET_BANKING')->get()->toArray();
             if(count($getCodData) > 0){
                foreach ($getCodData as $key => $value) {
                    $value = (array) $value;
                    if($requestData['method'] == 'api' && $value['internet_banking'] != 'Y'){
                        $apiResponse = Api::internetBankRegister($value['customer_id'],$requestData['formId']);
                        if($apiResponse){
                            $internetBankupdate = DB::table('CUSTOMER_OVD_DETAILS')
                                                ->where('CUSTOMER_ID',$value['customer_id'])
                                                ->whereNull('INTERNET_BANKING')
                                                ->update(['INTERNET_BANKING' => 'Y']);
                            if(isset($getCodData[$key+1]) && $getCodData[$key+1] != ''){
                                continue;
                            }                     

                            }else{
                            if(isset($getCodData[$key+1]) && $getCodData[$key+1] != ''){  
                                continue;
                            }  
                            return json_encode(['status'=>'fail','msg'=>$apiResponse,'data'=>[]]);
                        }

                        if($internetBankupdate){
                           return json_encode(['status'=>'success','msg'=>'Internet Bank Response Updated successfully','data'=>[]]);
                        }else{
                            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                        }
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'Value already updated','data'=>[]]);
                    }
                }
             }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatekyc(Request $request){
        try{

            $requestData = Arr::except($request->get('data'),'functionName');

            //check Physical Type DSA || CUBE
            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($requestData['formId'])->get()->toArray();
            $accountDetails = (array) current($accountDetails);
            $physicalType = 'PHYSICAL';
            if($accountDetails['source'] == 'DSA'){
                $physicalType = 'VIDEO';
            }

            $getCodData = DB::table('CUSTOMER_OVD_DETAILS')->select('ID','CUSTOMER_ID','FORM_ID','KYC_UPDATE','APPLICANT_SEQUENCE')->where('FORM_ID',$requestData['formId'])->whereNull('KYC_UPDATE')->get()->toArray();
            // $getCodData = (array) current($getCodData);
            $KycUpdate = false;
            if(count($getCodData) > 0){ 
                foreach ($getCodData as $key => $value) {
                    $value = (array) $value;
                    if($value['kyc_update'] != 'Y'){
                        if($requestData['method'] == 'api'){
                            $apiresponse = Api::kycUpdate($value['form_id'],$value['customer_id'],$physicalType);
                            if($apiresponse == 'Success'){
                                $KycUpdateResponse = true;
                            }else{
                                return json_encode(['status'=>'fail','msg'=>$apiresponse,'data'=>[]]);
                            }
                        }else{
                            $KycUpdateResponse = true;

                        }
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'Value already updated','data'=>[]]);
                    }
                    if($KycUpdateResponse){
                        $KycUpdate = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->whereId($value['id'])
                                        ->where('FORM_ID',$requestData['formId'])
                                        ->whereNull('KYC_UPDATE')
                                        ->update(['KYC_UPDATE' => 'Y']);
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'Value Not Updated. Try Again Later','data'=>[]]);
                    }
                    $applicant_sequence = $value['applicant_sequence'];
                    DB::commit();
                    $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updatekyc',$requestData['formId'].' : '.$requestData['kyc_update_comment'][$applicant_sequence],'','',Session::get('userId'));
                }
            }
            if($KycUpdate){
                   return json_encode(['status'=>'success','msg'=>'Kyc Update Updated successfully','data'=>[]]);
            }else{
                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatefundinstatus(Request $request){
       try{
              
            $requestData = Arr::except($request->get('data'),'functionName');

            $custIupdate = DB::table('FINCON')
                                ->where('FORM_ID',$requestData['formId'])
                                ->where('FUNDING_STATUS','N')
                                ->update(['FUNDING_STATUS'=>'Y']);


            if($custIupdate){

             DB::commit();

            $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updatefundinstatus',$requestData['formId'].' : '.$requestData['funding_comment'],'','Y',Session::get('userId'));

            $saveStatus = CommonFunctions::saveStatusDetails($requestData['formId'],'23',$requestData['funding_comment']);

                return json_encode(['status'=>'success','msg'=>'Fund transferred updated successfully','data'=>[]]);
            }else{
                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



    public function updateaccountno(Request $request){
       try{
              
            $requestData = Arr::except($request->get('data'),'functionName');
            
            // if($requestData['accounttype'] == 1){
            //     $accoutno = 'ACCOUNT_NO';
            // }

            // if($requestData['accounttype'] == 3){
            //     $accoutno = 'TD_ACCOUNT_NO';
            // }

            // if($requestData['accounttype'] == 4){
            //     $accoutno = 'ACCOUNT_NO';
            //     $accoutno = 'TD_ACCOUNT_NO';
            // }



            // switch ($requestData['accounttype']) {
            //     case '1':
            //         $updateaccountno = [$accoutno=>$requestData['sa_account_no']];
            //         break;

            //     case '3':
            //         $updateaccountno = [$accoutno=>$requestData['td_account_no']];
            //         break;

            //      case '4':
            //         $updateaccountno = [$accoutno=>$requestData['sa_account_no']];
            //         $updateaccountno = [$accoutno=>$requestData['td_account_no']];
            //         break;
                
            //     default:
            //         $updateaccountno = [$accoutno=>$requestData['account_no']];
            //         break;
                
            // }
               // echo "<pre>";print_r($requestData);exit;

            if((isset($requestData['sa_account_no'])) && ($requestData['sa_account_no'] != '')){

                if(isset($requestData['accounttype']) && $requestData['accounttype'] =='2'){
                    $accountupdate = DB::table('ENTITY_DETAILS')->where('FORM_ID',$requestData['formId'])
                                                                ->where('ENTITY_ACCOUNT_NO',null)
                                                                ->update(['ENTITY_ACCOUNT_NO'=>$requestData['sa_account_no']]);
                    Rules::postAccountIdApiQueue($requestData['formId'],2);
                }else{

                $accountupdate = DB::table('ACCOUNT_DETAILS')
                                                ->where('ID',$requestData['formId'])
                                                ->where('ACCOUNT_NO',null)
                                                ->update(['ACCOUNT_NO'=>$requestData['sa_account_no']]);
    
                DB::commit();
                Rules::postAccountIdApiQueue($requestData['formId'],1);
                }
                

           

               $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updateaccountno',$requestData['formId'].' : '.$requestData['account_id_comment'],'',$requestData['sa_account_no'],Session::get('userId'));
            }

            if((isset($requestData['td_account_no'])) && ($requestData['td_account_no'] != '')){

                $accountupdate = DB::table('ACCOUNT_DETAILS')
                                                ->where('ID',$requestData['formId'])
                                                ->where('TD_ACCOUNT_NO',null)
                                                ->update(['TD_ACCOUNT_NO'=>$requestData['td_account_no']]);
                 DB::commit();
                 Rules::postAccountIdApiQueue($requestData['formId'],3);

               $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updateaccountno',$requestData['formId'].' : '.$requestData['account_id_comment'],'',$requestData['td_account_no'],Session::get('userId'));
            }

			$saveStatus = CommonFunctions::saveStatusDetails($requestData['formId'],'24',$requestData['account_id_comment']);


            // $accountupdate = DB::table('ACCOUNT_DETAILS')
            //                                     ->where('ID',$requestData['formId'])
            //                                     ->where('ACCOUNT_NO',null)
            //                                     ->update($updateaccountno);
           
    
            if($accountupdate){
                NotificationController::processNotification($requestData['formId'],'ACCOUNTNO_CREATED');

                return json_encode(['status'=>'success','msg'=>'Account number is updated successfully','data'=>[]]);
            }else{
                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateftrfundtransfer(Request $request){
       try{
              
            $requestData = Arr::except($request->get('data'),'functionName');
            
            $roleId = Session::get('userId');

            $userDetails = DB::table('USERS')->where('ID',$roleId)
                                             ->get()->toArray();
            $userDetails = (array) current($userDetails);
    

            $updateFINCON = CommonFunctions::updateFtrStatusY($requestData['formId'],' ','FTR update by'.' '.$userDetails['empldapuserid'].' '.'('.$userDetails['hrmsno'].')');


            $ftrstatusupdate = DB::table('ACCOUNT_DETAILS')
                                                ->where('ID',$requestData['formId'])
                                                ->where('FUND_TRANSFER_STATUS',0)
                                                ->update(['FUND_TRANSFER_STATUS'=>1]);
            
            DB::commit();

            $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updateftrfundtransfer',$requestData['formId'].' : '.$requestData['ftr_status_comment'],'','1',Session::get('userId'));
    
            if($ftrstatusupdate){
                $saveStatus = CommonFunctions::saveStatusDetails($requestData['formId'],'25',$requestData['ftr_status_comment']);
                return json_encode(['status'=>'success','msg'=>'FTR updated successfully','data'=>[]]);
            }else{

                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatesignaturestatus(Request $request){
       try{
            $requestData = Arr::except($request->get('data'),'functionName');
            if(isset($requestData['formId'])){
                $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$requestData['formId'])->get()->toArray();
                $accountDetails = (array) current($accountDetails);

                $msg = ''; $data = '';


                if($requestData['method'] == 'manual'){
                    $signatureResponse = true;
                }else{
                     $apiResponse = Api::uploadSignature($requestData['formId'],$accountDetails['account_no']);
                     
                     if($apiResponse['status'] == 'Success'){
                        $signatureResponse = true;
                     }else{
                        $signatureResponse = false;
                        $msg = $apiResponse['message'];
                        $data = $apiResponse['data'];
                     } 
                }

                if($signatureResponse){
                    $updateSignatureResponse = DB::table('ACCOUNT_DETAILS')->where('ID',$requestData['formId'])
                                                                           ->update(['SIGNATURE_FLAG' => 'Y']);     
                                                                            //->where('ACCOUNT_NO',$accountDetails['account_no'])  

                    $updateSignatureResponse = true;                                                       
                }else{
                    $updateSignatureResponse = false;
                    $msg = $msg.' Signature flag could not be updated!';
                }

                if($updateSignatureResponse){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Signature Status updated successfully','data'=>[]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>$msg,'data'=>$data]);
                }

            }
          }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }

    public function updatecardflagstatus(Request $request){
       try{
            $requestData = Arr::except($request->get('data'),'functionName');
            if(isset($requestData['formId'])){
                $cardDetailsResponse = Api::submitCardDetails($requestData['formId']);
                if($cardDetailsResponse){
                    $updateCardDetailsResponse = DB::table('ACCOUNT_DETAILS')->whereId($requestData['formId'])->update(['CARD_FLAG' => 'Y']);
                }else{
                    return json_encode(['status'=>'fail','msg'=>$cardDetailsResponse,'data'=>[]]);
                }
                
                 $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updatecardflagstatus',$requestData['formId'].' : '.$requestData['card_flag_comment'],'',$requestData['card_flag'],Session::get('userId'));

                if($updateCardDetailsResponse){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Card Status updated successfully','data'=>[]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Card Flag Could not be updated, Please try again','data'=>[]]);
                }

            }
          }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }



    public function updatenextrole(Request $request){
       try{
              
            $requestData = Arr::except($request->get('data'),'functionName');
            
            

            $updatenextrole = DB::table('ACCOUNT_DETAILS')
                                                ->where('ID',$requestData['formId'])
                                                ->update(['NEXT_ROLE'=>$requestData['next_role']]);
            
            DB::commit();

            $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','updatenextrole',$requestData['formId'].' : '.$requestData['next_role_comment'],'',$requestData['next_role'],Session::get('userId'));
    
            if($updatenextrole){
               

                return json_encode(['status'=>'success','msg'=>'Next Role updated successfully','data'=>[]]);
            }else{

                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function abortform(Request $request){
       try{
            $requestData = Arr::except($request->get('data'),'functionName');

            $abortform = DB::table('FINCON')->where('FORM_ID',$requestData['formId'])
                                            ->update(['ABORT'=>$requestData['abort']]);
            
            DB::commit();

            $saveuserlog = CommonFunctions::createUserLogDirect('PrivilegeUpdateController','abortform',$requestData['formId'].' : '.$requestData['form_abort_comment'],'',$requestData['abort'],Session::get('userId'));
    
            if($abortform){
                return json_encode(['status'=>'success','msg'=>'Form Aborted successfully','data'=>[]]);
            }else{
                 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

       }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function markFormReject(Request $request){
        try{
            $requestData = Arr::except($request->get('data'),'functionName');


            DB::beginTransaction();

            $rejectform = CommonFunctions::saveStatusDetails($requestData['formId'],$status='45',$requestData['reject_comment']);

            $update_application_status = DB::table('ACCOUNT_DETAILS')->where('ID',$requestData['formId'])
                                                            ->update(['APPLICATION_STATUS'=>'45']);

            if($rejectform && $update_application_status){
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Form Rejected successfully','data'=>[]]);
            }else{
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>['status'=>$status,'formId'=>$requestData['formId']]]);
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
