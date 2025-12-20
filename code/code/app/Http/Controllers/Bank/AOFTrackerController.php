<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Session;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use File;
use Cookie;
use Crypt;

class AOFTrackerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
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


            $isAutherized = true;
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AOFTrackerController','AOFTrackerController','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    public function aoftracking($aof='')
    {
        //fetch user names from applcations
        // $customerNames = CommonFunctions::getCustomerDetails();
        $userSol = Session::get('branchId');
        if($userSol == '' || $userSol == null) return false;

        $userRole = Session::get('role');
        if($userRole == '' || $userRole == null) return false;

        // Show all branches for L1, L2, QC, Audit, L3, Manco, Inward
        if(in_array($userRole, [1,3, 4, 5, 6, 8, 13, 7])){
            $customerNames = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ACCOUNT_DETAILS.AOF_NUMBER',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME || ' - ' || AOF_NUMBER AS user_name"))
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->whereNull('ACCOUNT_DETAILS.EXTERNAL_ID')
                            ->where('APPLICANT_SEQUENCE',1)
                            ->whereNotNull('LAST_NAME')
                            ->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(2))
                            ->orderBy('ACCOUNT_DETAILS.ID','DESC')
                            ->pluck('user_name','aof_number')->toArray();
        }
        elseif(in_array($userRole, [11])){
            $customerNames = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ACCOUNT_DETAILS.AOF_NUMBER',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME || ' - ' || AOF_NUMBER AS user_name"))
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->where('APPLICANT_SEQUENCE',1)
                            //->where('ACCOUNT_DETAILS.BRANCH_ID',$userSol)
                            ->whereNull('ACCOUNT_DETAILS.EXTERNAL_ID')
                            ->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(2))
                            ->where('ACCOUNT_DETAILS.SOURCE','=','CC')
                            ->whereNotNull('LAST_NAME')
                            ->orderBy('ACCOUNT_DETAILS.ID','DESC')
                            ->pluck('user_name','aof_number')->toArray();
        }else{
            $customerNames = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ACCOUNT_DETAILS.AOF_NUMBER',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME || ' - ' || AOF_NUMBER AS user_name"))
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->where('APPLICANT_SEQUENCE',1)
                            ->whereNull('ACCOUNT_DETAILS.EXTERNAL_ID')
                            ->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(2))
                            ->whereNotNull('LAST_NAME')
                            ->where('ACCOUNT_DETAILS.BRANCH_ID',$userSol)
                            ->orderBy('ACCOUNT_DETAILS.ID','DESC')
                            ->pluck('user_name','aof_number')->toArray();
        }

        return view('bank.aoftracking')->with('customerNames',$customerNames)->with('aof',$aof);
    }

    public function trackingdetails(Request $request)
    {
        try{
            if ($request->ajax()){
                $trackingDetails = array();
                $requestData = $request->get('data');

                 foreach($requestData as $key => $value){
            
                    if(($key == 'aof_tracking_no' || $key == 'customerName') && !preg_match('/^[0-9]+$/', $value)){

                    $requestData[$key]='';

                 }

                }

                //get tracking details
                $trackingDetails = DB::table('STATUS_LOG')
                                            ->select('AOF_STATUS.ID',
                                                'AOF_STATUS.STATUS','AOF_STATUS.PROCESS_TAT','STATUS_LOG.CREATED_AT',
                                                'STATUS_LOG.COMMENTS',DB::raw("USERS.EMP_FIRST_NAME || ' ' ||
                                                    USERS.EMP_MIDDLE_NAME || ' ' || USERS.EMP_LAST_NAME AS created_by"))
                                            ->leftjoin('AOF_STATUS','AOF_STATUS.ID','STATUS_LOG.STATUS')
                                            ->leftjoin('USERS','USERS.ID','STATUS_LOG.CREATED_BY')
                                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','STATUS_LOG.FORM_ID');

                if($requestData['aof_tracking_no'] != '')
                {
                    $trackingDetails = $trackingDetails->where('ACCOUNT_DETAILS.AOF_NUMBER',$requestData['aof_tracking_no']);
                }

                if(isset($requestData['customerName']))
                {
                    $trackingDetails = $trackingDetails->where('ACCOUNT_DETAILS.AOF_NUMBER',$requestData['customerName']);
                }

                $trackingDetails = $trackingDetails->orderBy('STATUS_LOG.CREATED_AT')
                                                    ->get()->toArray();


                if(count($trackingDetails)==0){
                    return json_encode(['status'=>'fail','msg'=>'No tracking details in CUBE','data'=>[]]);
                }


                $trackingStatus = DB::table('STATUS_LOG')
                                                ->select('STATUS_LOG.STATUS','ACCOUNT_DETAILS.ACCOUNT_NO','ACCOUNT_DETAILS.TD_ACCOUNT_NO','CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
                                                ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','STATUS_LOG.FORM_ID')
                                                ->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','STATUS_LOG.FORM_ID');
                

                if($requestData['aof_tracking_no'] != '')
                {
                    $trackingStatus = $trackingStatus->where('ACCOUNT_DETAILS.AOF_NUMBER',$requestData['aof_tracking_no']);
                }

                if(isset($requestData['customerName']))
                {
                    $trackingStatus = $trackingStatus->where('ACCOUNT_DETAILS.AOF_NUMBER',$requestData['customerName']);
                }
                    $trackingStatus = $trackingStatus->orderBy('STATUS_LOG.STATUS','DESC')->limit(1)
                                                ->get()->toArray();
                $trackingStatus = (array) current($trackingStatus);

                $accountdetails = DB::table('ACCOUNT_DETAILS')
                                                              ->where('AOF_NUMBER',$requestData['customerName'])
                                                               ->get()->toArray();

                $accountdetails = (array) current($accountdetails);
                if($accountdetails['account_type'] == 2){
                    $trackingStatus['entity_account_number'] = DB::table('ENTITY_DETAILS')->where('FORM_ID',$accountdetails['id'])->value('entity_account_no');
                }else{
                    $trackingStatus['entity_account_number'] = '';
                }
                $ovddetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$accountdetails['id'])
                                                               ->get()->toArray();
                $ovddetails = (array) current($ovddetails);

                $ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$accountdetails['id'])
                                                               ->get()->toArray();



                if($accountdetails['is_new_customer'] == 0)
                {
                    $customerType = "ETB";
                }else{
                    $customerType = "NTB";
                }
                
                      




                $flowStatusDetails = DB::table('AOF_STATUS')
                                                // ->leftjoin('STATUS_LOG','STATUS_LOG.STATUS','AOF_STATUS.ID')
                                                ->where('AOF_STATUS.ID','>',$trackingStatus['status'])
                                                ->where('IS_NORMAL',1)
                                                ->orderBy('AOF_STATUS.ID')
                                                ->pluck('aof_status.status','aof_status.id')->toArray();


                if($ovddetails['initial_funding_type'] != 1){
                 $flowStatusDetails = Arr::except($flowStatusDetails,23);
                }

                if ($accountdetails['account_type'] == 3) {//for TD account "SAVINGS FUND TRASNFER" status removed
                 $flowStatusDetails = Arr::except($flowStatusDetails,25);
                }

        
                $filterArray = array();
                $formsubmitted=false;

                for ($i=0; $i < count($trackingDetails); $i++) { 
                    
                    if($trackingDetails[$i]->status != 'Savings Fund transferred'){
                        array_push($filterArray, $trackingDetails[$i]);
                    }

                    if($trackingDetails[$i]->id == '1'){
                        if($formsubmitted==false){
                            $formsubmitted=true;
                        }else{
                            $trackingDetails[$i]->status='AOF Updated';
                        }
                    }
                    // echo "<pre>";print_r($accountdetails['td_account_no']);exit;
                    if($trackingDetails[$i]->id == '24' && $accountdetails['account_type'] == 3 && $accountdetails['td_account_no'] == '' && $accountdetails['td_entry_done'] == 'Y'){
                        $trackingDetails[$i]->status ='Account request submitted';  
                    }else if ($trackingDetails[$i]->id == '24' && $accountdetails['account_type'] == 3 && $accountdetails['td_account_no'] != '') 
                    {
                        $trackingDetails[$i]->status ='Account created (Account No: '.$accountdetails['td_account_no'].' )';  
                    }
                }


                if($accountdetails['account_type'] == 3){

                   $trackingDetails = $filterArray;
                }

                //echo "<pre>";print_r($trackingDetails);exit;

                return view('bank.trackingdetails')->with('trackingDetails',$trackingDetails)
                                                    ->with('trackingStatus',$trackingStatus)
                                                    ->with('ovd',$ovd)
                                                    ->with('customerType',$customerType)
                                                    ->with('accountdetails',$accountdetails)
                                                    ->with('flowStatusDetails',$flowStatusDetails);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function formdetails(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $customer_type = '';
                $formDetails = DB::table('ACCOUNT_DETAILS')
                                    ->select('ACCOUNT_DETAILS.ID','USERS.EMPLDAPUSERID','ACCOUNT_DETAILS.CREATED_AT')
                                    ->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY');
                if($requestData['aof_tracking_no'] != '')
                {
                    $formDetails = $formDetails->where('ACCOUNT_DETAILS.AOF_NUMBER',$requestData['aof_tracking_no']);
                }

                if(isset($requestData['customerName']))
                {
                    $formDetails = $formDetails->where('ACCOUNT_DETAILS.AOF_NUMBER',$requestData['customerName']);
                }
                $formDetails = $formDetails->get()->toArray();
                if(count($formDetails) == 0){

                    return json_encode(['status'=>'fail','msg'=>'AOF number not found. Please recheck .','data'=>[]]);

                }
                $formDetails = (array) current($formDetails);
                $formId = $formDetails['id'];

                
                $accountStatusMatrics = DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
                                    ->where('FORM_ID', $formId)
                                    ->get()->toArray();

                if ( count($accountStatusMatrics) == 0) {
                    return json_encode(['status'=>'fail','msg'=>'Account not yet generated from Branch.','data'=>[]]);
                }

                //get form details
                $formDetailsArray = CommonFunctions::getFormDetails($formId);
                
                $accountType = $formDetailsArray['accountDetails']['account_type_id'];
                if($formDetailsArray['accountDetails']['is_new_customer'] == 0)
                {
                    $customer_type = "ETB";
                }
                $no_of_account_holders = $formDetailsArray['accountDetails']['no_of_account_holders'];
                $segmentList = CommonFunctions::getSegment();

                $currUrlPath = parse_url(\Request::getRequestUri(), PHP_URL_PATH);
                $pathComponents = explode("/", trim($currUrlPath, "/"));
                $appName = $pathComponents[0];
                $imgPublicPath = '/'.$appName.CommonFunctions::getImagePublicPath($formId);
                $cifDeclarationDetails = DB::table('SUBMISSION_DECLARATION_FIELDS')->where('FORM_ID',$formId)->get()->toArray();
                //  echo "<pre>";print_r($cifDeclarationDetails);exit;
                $cifDeclarationDetails = current($cifDeclarationDetails);
                $callCenterEmailImage = [];
                if($formDetailsArray['accountDetails']['source'] == 'CC'){
                    $callCenterEmailImage = DB::table('CUSTOMER_DECLARATIONS')->select('DYNA_TEXT')
                                                                                ->where('FORM_ID',$formId)
                                                                                //  ->where('DECLARATION_ID',144)
                                                                                ->whereIn('DECLARATION_ID',[53,142])
                                                                                ->get()->toArray();
                        if(count($callCenterEmailImage) >0){

                            $callCenterEmailImage  = (array) current($callCenterEmailImage);                   
                        }
                }

                $l3DeclarationsImage = DB::table('CUSTOMER_DECLARATIONS')->where('FORM_ID',$formId)
                                                                         ->whereIn('DECLARATION_ID',[40,41,42,43,44])
                                                                         ->get()->toArray();


                $entityL1Images = [];                                                            
                if(Session::get('role') != 2){
                    $entityL1Images = DB::table('CLEARANCE')->where('FORM_ID',$formId)->whereNotNull('CLEARANCE_IMG')->get()->toArray();
                }

                $callCenterDeclaration = false;
                if($accountType == 3 && $formDetailsArray['accountDetails']['source'] == 'CC'){
                    $schemeDeclaration = DB::table('TD_SCHEME_CODES')->where('SCHEME_CODE',$formDetailsArray['accountDetails']['scheme_code'])->where('RI_NRI','NRI')->count();
                    if($schemeDeclaration == 1){
                        $callCenterDeclaration = true;
                    } 
                }

                $tdSchemeCodecc = DB::table('TD_SCHEME_CODES')->where('SCHEME_CODE',$formDetailsArray['accountDetails']['tdscheme_code'])->where('RI_NRI','NRI')->count();

                if(isset($formDetailsArray['userDetails'][0]['customerOvdDetails']) && !empty($formDetailsArray['userDetails'][0]['customerOvdDetails'])){
                    for($i=0;count($formDetailsArray['userDetails'])>$i;$i++){
                        if($formDetailsArray['userDetails'][$i]['customerOvdDetails']->{'proof_of_identity'} == 'E-KYC'){
                            $getekycPhoto =  DB::table('EKYC_DETAILS')->select('EKYC_PHOTO')->where('FORM_ID',$formDetailsArray['userDetails'][$i]['customerOvdDetails']->{'form_id'})
                                                                      ->where('APPLICANT_SEQUENCE',$formDetailsArray['userDetails'][$i]['customerOvdDetails']->{'applicant_sequence'})
                                                                      ->where('EKYC_NO',$formDetailsArray['userDetails'][$i]['customerOvdDetails']->{'id_proof_card_number'})
                                                                      ->orderBy('ID','DESC')
                                                                      ->take('1')
                                                                      ->get()->toArray();
                            if(count($getekycPhoto)>0){
                                $getekycPhoto =  (array) current($getekycPhoto);
                                $ekycPhoto = json_decode(base64_decode($getekycPhoto['ekyc_photo']),true)['ekyc_photo_details']['photo'];
                                $formDetailsArray['userDetails'][$i]['customerOvdDetails']->{'ekyc_photo'} = $ekycPhoto;
                            }
                        }
                    }
                }

                $huf_cop_row=[];
                if($formDetailsArray['accountDetails']["constitution"]=="NON_IND_HUF"){
                    $huf_cop_row = DB::table("NON_IND_HUF as HUF")->select("HUF.*","REL.DISPLAY_DESCRIPTION as relation")
                    ->leftJoin("RELATIONSHIP as REL","REL.ID","=","HUF.HUF_RELATION")
                    ->where("HUF.FORM_ID",$formId)->where("HUF.DELETE_FLG","N")
                    ->get()->toArray();
                    $huf_cop_row = (array) $huf_cop_row;
                }

        //GROSS INCOME CHANGE LOGIC
            for($seq=0;count($formDetailsArray['userDetails'])>$seq;$seq++){

                $grossincomeId = $formDetailsArray['userDetails'][$seq]['riskDetails']->gross_income;

                $getdescgrossIncome = DB::table('GROSS_INCOME')->select('GROSS_ANNUAL_INCOME')
                                                               ->whereId($grossincomeId)
                                                               ->get()->toArray();
                if(count($getdescgrossIncome)>0){
                    $getdescgrossIncome =  (array) current($getdescgrossIncome);
                    $formDetailsArray['userDetails'][$seq]['riskDetails']->gross_income = $getdescgrossIncome['gross_annual_income'];
                }
            }

                return view('bank.form')->with('formId',$formId)
                                        ->with('accountDetails',$formDetailsArray['accountDetails'])
                                        ->with('accountType',$accountType)
                                        ->with('callCenterDeclaration',$callCenterDeclaration)
                                        ->with('entityL1Images',$entityL1Images)
                                        ->with('declarationsList',$formDetailsArray['declarationsList'])
                                        ->with('nomineeDetails',$formDetailsArray['nomineeDetails'])
                                        ->with('customerOvdDetails',$formDetailsArray['customerOvdDetails'])
                                        ->with('entityDetails',$formDetailsArray['entityDetails'])
                                        ->with('userDetails',$formDetailsArray['userDetails'])
                                        ->with('delightDetails',$formDetailsArray['delightDetails'])
                                        ->with('customer_type',$customer_type)
                                        ->with('segmentList',$segmentList)
                                        ->with('no_of_account_holders',$no_of_account_holders)
                                        ->with('is_aof_tracker',true)
                                        ->with('l3DeclarationsImage',$l3DeclarationsImage)
                                        ->with('username',$formDetails['empldapuserid'])
                                        ->with('created_at',$formDetails['created_at'])
                                        ->with('files',$formDetailsArray['files'])
                                        ->with('imgPublicPath',$imgPublicPath)
                                        ->with('cifDeclarationDetails', $cifDeclarationDetails)
                                        ->with('callCenterEmailImage',$callCenterEmailImage)
                                        ->with('tdSchemeCodecc',$tdSchemeCodecc)
                                     
                                        ->with("huf_cop_row",$huf_cop_row)
                                        ;
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