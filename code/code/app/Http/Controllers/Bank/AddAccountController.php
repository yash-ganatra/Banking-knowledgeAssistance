<?php
namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Amend\AmendController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ETBNTB\checkIDOVDController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Helpers\CommonFunctions;
use App\Helpers\DelightFunctions;
use App\Helpers\Rules;
use App\Helpers\Api;
use App\Helpers\AmendApi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use File;
use DB;
use PSpell\Config;

class AddAccountController extends Controller
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

            if(!in_array($this->roleId,[2,11,3,4,8])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AddAccountController','AddAccountController','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }

        }
        ini_set('max_execution_time', '130');
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
     * Method Name: addaccount
     * Created By : Sharanya T
     * Created At : 20-02-2020

     * Description:
     * Method to show add account template
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function addaccount(Request $request)
    {
        try {
            
            $tokenParams = Cookie::get('token');
            $encrypt_key = substr($tokenParams, -5); 
            $formId = '';
            $url = explode('/', url()->previous());
            $accountType = 1;
            if(Session::get('formId') == '')
            {
                Session::forget('UserDetails');
                Session::forget('formId');
                Session::forget('nomineeIds');
                if(Session::get('is_review') == 1){
                    Session::put('is_review',0);
                }
                if(Session::get('in_progress') == 1){
                    Session::put('in_progress',0);
                }
                if((Session::get('role') == 11) || (Session::get('role') == 2)){

                    Session::put('max_screen','');
                    Session::put('last_screen','');
                }
                Session::forget('customer_type');
            }else{
                if(($url[count($url) - 1] == "dashboard") && (Session::get('customer_type') == "ETB"))
                {
                    Session::forget('formId');
                    Session::forget('UserDetails');
                    Session::forget('customer_type');
                }
            }
            $userDetails = array();
            $reviewDetails = array();
            $reviewSectionDetails = array();
            $in_progress = 0;
            //fetch account types
            $accountTypes = CommonFunctions::getAccountTypes();
              //fetch account types
            $accountlevelTypes = CommonFunctions::getAccountlevelTypes();
            //fetch mode of operations
            $modeOfOperations = CommonFunctions::getModeOfOperations();
            //fetch residential status
            $residentialStatus = CommonFunctions::getResidentialStatus();
            //fetch cusotmer account types
            $customerAccountTypes = CommonFunctions::getCustomerAccountTypes();
            //fetch marital status
            $maritalStatus = CommonFunctions::getMaritalStatus();
            //fetch education details
            $educationList = config('constants.EDUCATION');
            //fetch gross income details
            $grossIncome = CommonFunctions::getgrossannualIncome();
            // $grossIncome = config('constants.GROSS_INCOME');
            if(Session::get('is_review') == ''){
                Session::put('is_review',0);
            }
            $checkHuf = '';
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $params = base64_decode($decodedString);
                if($params == 'non_ind_huf_form'){
                    $checkHuf = $params;
                }else{
                $is_review = explode('_',$params)[0];
                $formId = explode('_',$params)[1];
                Session::put('is_review',$is_review);
                Session::put('reviewId',$formId);
                Session::put('formId',$formId);
                if((Session::get('customer_type') != "ETB") && ($is_review == 0))
                {
                    $in_progress = 1;
                    Session::put('in_progress',$in_progress);
                }
            }
            }
            
            $formId = Session::get('formId');
            $scenario = CommonFunctions::getScenario($formId, 'basic_details');

            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }

            if($scenario == 'Update'){
                //$formId = Session::get('reviewId');
                $formId = Session::get('formId');
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
                $accountDetails = (array) current($accountDetails);
                $accountType = $accountDetails['account_type'];
                $schemeCode = $accountDetails['scheme_code'];
                if($accountType == 4)
                {
                    $accountType = 3;
                    $schemeCode = $accountDetails['td_scheme_code'];
                }
                if($accountType == 5)
                {
                    $accountType = 1;
                }
                $schemeDetails = CommonFunctions::getSchemeCodesBySchemeId($accountType,$schemeCode);
                $schemeDetails = (array) current($schemeDetails);
                if($accountType == 3)
                {
                    Session::put('td_schemeData',$schemeDetails);
                }else{
                    Session::put('schemeData',$schemeDetails);
                }
                $userDetails['AccountDetails'] = $accountDetails;
                $customerId = $userDetails['AccountDetails']['customer_id'];
                if($customerId != ''){
                    Session::put('customer_type', "ETB");
                }
                // if(Session::get('in_progress') == 1)
                if((Session::get('in_progress') == 1) && (Session::get('screen') < 1))
                {
                    Session::put('screen',$userDetails['AccountDetails']['screen']);
                }
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->orderBy('applicant_sequence', 'ASC')
                                            ->get()->toArray();
                array_unshift($customerOvdDetails, "phoney");
                unset($customerOvdDetails[0]);
                foreach ($customerOvdDetails as $key => $value) {
                    $customerOvdDetails[$key]->email = CommonFunctions::encrypt256($value->email,$encrypt_key);
                    $customerOvdDetails[$key]->mobile_number = CommonFunctions::encrypt256($value->mobile_number,$encrypt_key);
                    if(isset($value->pf_type)=="pancard"){
                        $customerOvdDetails[$key]->pancard_no =  CommonFunctions::encrypt256( $value->pancard_no,$encrypt_key);
                    }
                }
                $userDetails['customerOvdDetails'] = json_decode(json_encode($customerOvdDetails),true);
                $reviwerRole = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('role_id','DESC')
                                                        ->limit(1)->pluck('role_id')->toArray();
                $reviwerRole = current($reviwerRole);
                $reviewDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->pluck('comments','column_name')->toArray();
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->distinct()->pluck('id','section')->toArray();
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                            ->orderBy('applicant_sequence', 'ASC')
                                                                            ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");

                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['AccountDetails'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }
                }
            }

            if (isset($userDetails['AccountDetails'])) {
                if($userDetails['AccountDetails']['account_type'] == 1 && $userDetails['AccountDetails']['delight_scheme'] == 5){
                    $userDetails['AccountDetails']['account_type'] = 5;
                    $delightSavings = true;
                }else{
                    $delightSavings = false;
                }
            }

            if(Session::get('accountType') != '')
            {
                $accountType = Session::get('accountType');
            }
            //fetch savingscheme code
            // $schemeCodes = CommonFunctions::getSchemeCodes(1);
            $tdSchemeCodes = CommonFunctions::getSchemeCodes(3);
            if(isset($accountDetails['account_type']) && $accountDetails['account_type'] == 4){
                $accountType = 4;
            }

            if($accountType == 4)
            {
                $savingsSchemeCodes = CommonFunctions::getSchemeCodes(1);
            }else{
                if(isset($accountDetails) && $accountDetails['account_type'] == 2){
                    $accountType = 2;
                }


                $savingsSchemeCodes = CommonFunctions::getSchemeCodes($accountType);
            }

            if (isset($delightSavings) && $delightSavings) { //scheme Codes for delight accounts
                $savingsSchemeCodes = DelightFunctions::getDelightSchemeCodes();
            }


            $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
            $saveStatus = CommonFunctions::saveStatusDetails('',1);

            if (isset($accountDetails['delight_kit_id'])) {
                $delightKitNumber = $accountDetails['delight_kit_id'];
            }else{
                $delightKitNumber = '';
            }

            if($scenario == 'Update'){
                $schemeDetails = CommonFunctions::getSchemeDetails($formId);
            }else{
                $schemeDetails = '';
            }

            $allowETB = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE')->where('FIELD_NAME', 'ALLOW_ETB')->get()->toArray();
            $allowETB = current($allowETB)->field_value;

            $allowDeligth = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE')->where('FIELD_NAME', 'ALLOW_DELIGHT')->get()->toArray();
            $allowDeligth = current($allowDeligth)->field_value;

            $allowCurrent = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE')->where('FIELD_NAME', 'ALLOW_CURRENT')->get()->toArray();
            $allowCurrent = (array) current($allowCurrent);

            $fieldValue = $allowCurrent['field_value'];

            $fieldValue  = preg_split("/,\s*/", $fieldValue);

            $currentAllow = true;


            if(strlen($allowCurrent['field_value'] != 3)){
                foreach ($fieldValue as $empsol){
                    $userId = Session::get('userId');

                    $checkEmpSolId = DB::table('USERS')->where('EMPSOL',$empsol)
                                                ->where('ROLE', 2)
                                                ->where('ID', Session::get('userId'))
                                                ->get()->toArray();
                    if(count($checkEmpSolId) > 0){
                        $currentAllow = true;
                        break;
                    }else{
                        $currentAllow = false;
                    }
                }
            }

            if($allowCurrent['field_value'] == 'ALL'){
                $currentAllow = true;
            }
            $currentAllow = true;
            if(!$currentAllow){
                unset($accountTypes[2]);
            }

            if($allowDeligth != 'ALL'){
                unset($accountTypes[5]);
            }
            // print_r($request->all());
           if($checkHuf == "non_ind_huf_form"){
                $nonindividual = "NON_IND_HUF";
                $accountTypes = array_filter($accountTypes, fn($key)=> $key=='1' || $key=="2" ||$key=="3",ARRAY_FILTER_USE_KEY);
           }else{
                $nonindividual = $accountDetails["constitution"] ?? "";
           }

        
           
            //returns to template
            return view('bank.addaccount')->with('accountTypes',$accountTypes)
                                             ->with('accountlevelTypes',$accountlevelTypes)
                                            ->with('modeOfOperations',$modeOfOperations)
                                            ->with('maritalStatus',$maritalStatus)
                                            ->with('residentialStatus',$residentialStatus)
                                            ->with('customerAccountTypes',$customerAccountTypes)
                                            ->with('savingsSchemeCodes',$savingsSchemeCodes)
                                            ->with('tdSchemeCodes',$tdSchemeCodes)
                                            ->with('educationList',$educationList)
                                            ->with('grossIncome',$grossIncome)
                                            ->with('placeOfBirthList',$placeOfBirthList)
                                            ->with('citizenshipList',$citizenshipList)
                                            ->with('formId',$formId)
                                            ->with('userDetails',$userDetails)
                                            ->with('reviewDetails',$reviewDetails)
                                            ->with('reviewSectionDetails',$reviewSectionDetails)
                                            ->with('delightKitNumber',$delightKitNumber)
                                            ->with('schemeDetails',$schemeDetails)
                                            ->with('allowETB',$allowETB)
                                            ->with('allowDeligth',$allowDeligth)
                                            ->with('nonindividual',$nonindividual)
                                            ;
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getschemedata(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                $table = "SCHEME_CODES";
                if(in_array($requestData['account_type'],[3,4])){
                    $table = 'TD_SCHEME_CODES';
                    $acctType = 'TD';
                }else{
                   $acctType = 'SA';
                    if($requestData['account_type'] == 2){
                        $requestData['id'] = '1';
                        $acctType = 'CA';
                        $table = 'CA_SCHEME_CODES';
                    }
                }
                // echo "<pre>";print_r($requestData);exit;
                //fetch scheme data
                $schemeData = DB::table($table)->whereId($requestData['id'])
                                               ->get()->toArray();
                $schemeData = (array) current($schemeData);

                

                if($requestData['account_type'] == 3){
                    Session::put('td_schemeData',$schemeData);
                }else{
                    Session::put('schemeData',$schemeData);
                }
                
                $schemeCodeChanged = false;


                if (isset($requestData['formId']) && $requestData['formId'] != '') {
                    $oldSchemeCodeId = DB::table('ACCOUNT_DETAILS')->select('SCHEME_CODE')
                                                                ->whereId($requestData['formId'])
                                                                ->get()->toArray();


                    $oldSchemeCodeId = current($oldSchemeCodeId)->scheme_code;
                    
                    $old_scheme_Data = DB::table($table)->whereId($oldSchemeCodeId)
                                               ->get()->toArray();
                    $old_scheme_code = current($old_scheme_Data)->scheme_code;

                    if ($schemeData['scheme_code'] != $old_scheme_code) {
                        $schemeCodeChanged = true;
                    }

                }elseif (Session::get('formId') != '') {
               
                    $cust_Type = DB::table('ACCOUNT_DETAILS')->select('IS_NEW_CUSTOMER','ACCOUNT_TYPE')->where('ID',Session::get('formId'))->get()->toArray();

                    $cust_Type = (array) current($cust_Type);
                    $accountType = $cust_Type['account_type'];

                    if($cust_Type['is_new_customer'] == 0){
                       $customerType = 'ETB';
                    }else{
                       $customerType = 'NTB';
                    }
                  
                    if($customerType == 'ETB'){
                        $schemeCodeChanged = true;
                    }
                }

                if(count($schemeData) > 0){
                    return json_encode(['status'=>'success','msg'=>'Scheme Data Found.','data'=>['schemeData'=>$schemeData, 'accountType'=>$acctType,'schemeCodeChanged'=> $schemeCodeChanged]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getgpadata(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch scheme data
                $gpaData = DB::table('GPA_PLANS')->whereId($requestData['id'])
                                               ->get()->toArray();
                $gpaData = (array) current($gpaData);

                if(count($gpaData) > 0){
                    return json_encode(['status'=>'success','msg'=>'Scheme Data Found.','data'=>['gpaData'=>$gpaData]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function delightKit(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch scheme data
                $kitDetails = DB::table('DELIGHT_KIT')->whereId($requestData['id'])
                                               ->get()->toArray();
                $kitDetails = (array) current($kitDetails);

                if(count($kitDetails) > 0){
                    return json_encode(['status'=>'success','msg'=>'Delight Kit Details Found.','data'=>['kitDetails'=>$kitDetails]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getschemecodebyaccounttype(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                $tempAccountType = $requestData['account_type'];
                Session::forget('td_schemeData');
                Session::forget('schemeData');
                if(($requestData['account_type'] == 4) || ($requestData['account_type'] == 5))
                {
                    $requestData['account_type'] = 1;
                }
            if($requestData['constitution'] == 'NON_IND_HUF'){
                    $getschemecodebyaccounttype = CommonFunctions::hufSchemeCodebyAccountType($requestData['account_type']);
    
            }else{
                if ($tempAccountType == 5 && $requestData['account_type'] = 1) {
                    $getschemecodebyaccounttype = DelightFunctions::getDelightSchemeCodes();
                }else{
                    $getschemecodebyaccounttype = CommonFunctions::getSchemeCodebyAccountType($requestData['account_type']);
                }
            }

                if($getschemecodebyaccounttype){
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>['getschemecodebyaccounttype'=>$getschemecodebyaccounttype]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addapplicant(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch residential status
                $residentialStatus = CommonFunctions::getResidentialStatus();
                //fetch education details
                $educationList = config('constants.EDUCATION');
                //fetch gross income details
                $grossIncome = CommonFunctions::getgrossannualIncome();
                // $grossIncome = config('constants.GROSS_INCOME');
                $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
                //fetch cusotmer account typess
                $customerAccountTypes = CommonFunctions::getCustomerAccountTypes();
                //fetch marital status
                $maritalStatus = CommonFunctions::getMaritalStatus();

                $allowETB = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE')->where('FIELD_NAME', 'ALLOW_ETB')->get()->toArray();
                $allowETB = current($allowETB)->field_value;

                //returns to template
                return view('bank.addaccountapplicant')->with('residentialStatus',$residentialStatus)
                                                ->with('educationList',$educationList)
                                                ->with('grossIncome',$grossIncome)
                                                ->with('placeOfBirthList',$placeOfBirthList)
                                                ->with('citizenshipList',$citizenshipList)
                                                ->with('maritalStatus',$maritalStatus)
                                                ->with('customerAccountTypes',$customerAccountTypes)
                                                ->with('customerOvdDetails',array())
                                                ->with('i',$requestData['no_of_applicants'])
                                                ->with('allowETB',$allowETB)
                                                ;
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: saveaccountdetails
    *  Created By : Sharanya T
    *  Created At : 24-02-2020
    *
    *  Description:
    *  Method to save pan details in datatabse
    *
    *  Params:
    *  $AccountDetails,$PanDetails
    *
    *  Output:
    *  Returns SchemeCodes.
    */
    public function saveaccountdetails(Request $request)
    {
        try {

            //fetch get details from request
            $requestData = Arr::except($request->get('data'),'functionName');
            $is_huf = isset($requestData["AccountDetails"]["constitution"]) == "NON_IND_HUF";
            

             if(CommonFunctions::checkSpecialChars($requestData)=='NOT_OK'){
                return json_encode(['status'=>'fail','msg'=>'Invalid inputs detected! Kindly check the data entered and retry.','data'=>[]]);
            }

            //pan,dob,email,mobile client side encrypted
            $userId = $this->userId;
            $getHrmsNo = DB::table('USERS')->select('HRMSNO')->whereId($userId)->get()->toArray();
            $getHrmsNo = (array)current($getHrmsNo);


            //	Commented to allow staff open self SB106 accounts - 20-JAN-2024            
                /*     if(isset($requestData['PanDetails']['1']['empno']) && $requestData['PanDetails']['1']['empno'] != ''){
                            if($requestData['PanDetails']['1']['empno'] == $getHrmsNo['hrmsno']){
                                return json_encode(['status'=>'fail','msg'=>'Opening of self staff account not permitted .','data'=>[]]);
                            }
                        }
            */
          
            if(isset($requestData['PanDetails']) && $requestData['PanDetails']){

                for($i=1;count($requestData['PanDetails']) >= $i;$i++){
                    if($requestData['PanDetails'][$i]['pf_type'] != 'form60'){

                    if(isset($requestData['PanDetails'][$i]['pancard_no']) && $requestData['PanDetails'][$i]['pancard_no'] !=''){
                        $requestData['PanDetails'][$i]['pancard_no'] = CommonFunctions::decryptRS($requestData['PanDetails'][$i]['pancard_no']);
                        if($requestData['PanDetails'][$i]['pancard_no'] == '' || substr($requestData['PanDetails'][$i]['pancard_no'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['PanDetails'][$i]['pancard_no']]]);  
                        } 
                        }  
                    }
                    if($requestData['PanDetails'][$i]['mobile_number']){
                        $requestData['PanDetails'][$i]['mobile_number'] = CommonFunctions::decryptRS($requestData['PanDetails'][$i]['mobile_number']);
                        if($requestData['PanDetails'][$i]['mobile_number'] == '' || substr($requestData['PanDetails'][$i]['mobile_number'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['PanDetails'][$i]['mobile_number']]]);  
                        } 
                    }
                    if($requestData['PanDetails'][$i]['email']){
                        $requestData['PanDetails'][$i]['email'] = CommonFunctions::decryptRS($requestData['PanDetails'][$i]['email']);
                        if($requestData['PanDetails'][$i]['email'] == '' || substr($requestData['PanDetails'][$i]['email'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['PanDetails'][$i]['email']]]);  
                        } 
                    }
                    if($requestData['PanDetails'][$i]['dob']){
                        $requestData['PanDetails'][$i]['dob'] = CommonFunctions::decryptRS($requestData['PanDetails'][$i]['dob']);
                        if($requestData['PanDetails'][$i]['dob'] == '' || substr($requestData['PanDetails'][$i]['dob'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['PanDetails'][$i]['dob']]]);  
                        } 
                    }

                    $domainId = explode("@",$requestData['PanDetails'][$i]['email']);
                    $getvaliddomain = DB::table('RESTRICTED_DOMAIN_ID')->select('DOMAIN_ID')
                        ->whereRaw('LOWER(DOMAIN_ID) = ?',[strtolower(trim($domainId[1]))])->get()->toArray();
    
                    if(count($getvaliddomain) > 0){
                        return json_encode(['status'=>'fail','msg'=>'Given Email Domian (Temporary) Name is not permitted for Applicant-'.$i,'data'=>[]]);
                }

                    $panExist=Session::get('ispanExists-'.$i);

                    if($panExist == 1 ){
                        return json_encode(['status'=>'fail','msg'=>'Customer (PAN Number) exists in Finacle for applicant-'.$i,'data'=>[]]);
                    }
                
                }

            }

            $requestData = Rules::preFlightSaveAccountDetails($requestData);
            
            //check rd flow not allowing ntb customer

            if(!isset($requestData['formId']))
            {
                $formId = Session::get('formId');
            }else{
                $formId = $requestData['formId'];
            }
    
            $EtbNtbStatus = CommonFunctions::getEtbNtbStatus($formId, $requestData['AccountDetails']['no_of_account_holders']);
            
            $getRdData = DB::table('TD_SCHEME_CODES')->where('ID',$requestData['AccountDetails']['scheme_code'])
                                                    ->where('TD_RD','RD')
                                                    ->count();

            if($getRdData > 0 && $EtbNtbStatus['account_type'] == 'ETB' && $this->roleId == 11){
                return json_encode(['status'=>'fail','msg'=>'Only ETB applicant permitted for selected scheme code','data'=>[]]);
            }
           
            $etbNtbComboAllow = true;

            if($requestData['AccountDetails']['delight_scheme'] == 5){
                if ($EtbNtbStatus['account_type'] == "ETB" && in_array('NTB', $EtbNtbStatus['customer_type'])) {
                    $etbNtbComboAllow = false;
                }
            }
            // if ($EtbNtbStatus['account_type'] == "ETB" && in_array('NTB', $EtbNtbStatus['customer_type'])) {
            //     $etbNtbComboAllow = false;
            // }elseif ($EtbNtbStatus['account_type'] == "NTB" && in_array('ETB', $EtbNtbStatus['customer_type'])) {
            //     $etbNtbComboAllow = false;
            // }

            if (!$etbNtbComboAllow) {
                return json_encode(['status'=>'fail','msg'=>'Combinations of ETB + NTB found. Currently not supported','data'=>[]]);
            }

            if (isset($requestData['AccountDetails']['delight_scheme'])) {
                if($requestData['AccountDetails']['account_type'] == 1 && $requestData['AccountDetails']['delight_scheme'] == 5){
                    $delightSavings = true;
                }else{
                    $delightSavings = false;
                }
                
                if ($delightSavings && Session::get('is_review') != 1) {
                    $schemeCodeDetails = CommonFunctions::getSchemeCodesBySchemeId($requestData['AccountDetails']['account_type'],$requestData['AccountDetails']['scheme_code']);
                    $schemeCodeDetails = current($schemeCodeDetails);
                    $delightKits = DB::table('DELIGHT_KIT')->where('SALES_USER_ID',Session::get('userId'))
                                                    ->where('SOL_ID',Session::get('branchId'))
                                                    ->where('SCHEME_CODE',$schemeCodeDetails->scheme_code)
                                                    ->where('CUSTOMER_ID','!=',null)
                                                    ->where('ACCOUNT_NUMBER','!=',null)
                                                    ->where('STATUS', 7)->get()->toArray();

                    if ($schemeCodeDetails->scheme_code == 'SB106' && $requestData['AccountDetails']['no_of_account_holders'] > 1) {
                        return json_encode(['status'=>'fail','msg'=>'Only one applicant permitted for selected scheme code','data'=>[]]);
                    }


                    if (count($delightKits) == 0) {
                        return json_encode(['status'=>'fail','msg'=>'No kits available for selected scheme code','data'=>[]]);
                    }

                    if(isset($requestData['no_of_account_holders']) && $requestData['no_of_account_holders'] > 2){
                        return json_encode(['status'=>'fail','msg'=>'More than two applicants not allowed for selected Account Type','data'=>[]]);
                    }
                }
            }

            //mobile number validation

            if($requestData['PanDetails'][1]['mobile_number'] == ""){
                return json_encode(['status'=>'fail','msg'=>'Please Insert Mobile Number','data'=>[]]);
            }
                        
           
            $primary_pf_type = $requestData['PanDetails'][1]['pf_type'];
            $account_type = $requestData['AccountDetails']['account_type'];

            if($primary_pf_type != 'pancard'){
                $validatePanCard = CommonFunctions::isPanMandatory($account_type,$requestData);
                if($validatePanCard){
                    return json_encode(['status'=>'fail','msg'=>'PAN Card Mandatory for this Scheme Code!','data'=>[]]);
                }
            }

            $is_uploaded = true;
            $url = 'addovddocuments';
            $dobArray = array();
            //Begins db transaction
            DB::beginTransaction();
            //store account type in session
            Session::put('accountType', $requestData['AccountDetails']['account_type']);
            Session::put('no_of_account_holders', $requestData['AccountDetails']['no_of_account_holders']);
            $requestData['AccountDetails']['scheme_code'] = implode(',', (array)$requestData['AccountDetails']['scheme_code']);
            // if($requestData['AccountDetails']['account_type'] == 2 && $requestData['AccountDetails']['scheme_code'] == 1){
            //     $requestData['AccountDetails']['scheme_code'] = 14;
            // }

            if(!isset($requestData['formId']))
            {
                $formId = Session::get('formId');
                if(($formId == '') || (Session::get('customer_type') == "ETB"))
                {
                    // $sequnceNumber = DB::select('select ACCOUNT_SEQUENCE.nextval from dual');
                    // $sequnceNumber = (array) current($sequnceNumber);
                    // $sequnceLength = strlen($sequnceNumber['nextval']);
                    // if($sequnceLength != 6){
                    //     $sequnceNumber = str_pad($sequnceNumber['nextval'], 6 , "0", STR_PAD_LEFT);
                    // }else{
                    //     $sequnceNumber = $sequnceNumber['nextval'];
                    // }
                    // $year = substr(Carbon::now()->year, 2);
                    $branchId = Session::get('branchId');
                    $aofnumber = CommonFunctions::genereateAofNumber($branchId);
                    if(strlen($aofnumber) <= 10 || $aofnumber == ''){
                        return json_encode(['status'=>'fail','msg'=>'Something Went Wrong, Please Try Again','data'=>[]]);
                    }
                    $requestData['AccountDetails']['aof_number'] = $aofnumber;
                    $requestData['AccountDetails']['branch_id'] = $branchId;
                    $requestData['AccountDetails']['created_by'] = Session::get('userId');
                    $requestData['AccountDetails']['screen'] = 1;
                    
                    if($formId == '')
                    {
                        //insert account details
                        $formId = DB::table("ACCOUNT_DETAILS")->insertGetId($requestData['AccountDetails']);
                    }else{
                        $update = DB::table("ACCOUNT_DETAILS")->whereId($formId)->update($requestData['AccountDetails']);
                    }
                }else{
                    $requestData['AccountDetails']['UPDATED_BY'] = Session::get('userId');
                    if(isset($requestData['AccountDetails']['aof_number']) && $requestData['AccountDetails']['aof_number'] == ''){
                        return json_encode(['status'=>'fail','msg'=>'Something Went Wrong, Please Try Again','data'=>[]]);
                    }
                    $update = DB::table("ACCOUNT_DETAILS")->whereId($formId)->update($requestData['AccountDetails']);
                }
            }else{
                $formId = $requestData['formId'];
                $update = DB::table("ACCOUNT_DETAILS")->whereId($formId)->update($requestData['AccountDetails']);
            }
            Session::put('formId',$formId);

            $updateFormId = DB::table('STATUS_LOG')->where('FORM_ID',Session::get('randomString'))
                                                    ->update(['FORM_ID'=>$formId]);
            if($updateFormId)
            {
                Session::forget('randomString');
            }

            DB::setDateFormat('DD-MM-YYYY');
            if(count($requestData['PanDetails']) > 0){
                $i = 1;
                foreach ($requestData['PanDetails'] as $key => $panImage) {
                    if(isset($panImage['pf_type_image'])){
                        //define old file path(temp folder)
                        $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$panImage['pf_type_image']);
                        //$folder = public_path('/uploads/attachments/'.$formId);
                        $folder = storage_path('/uploads/attachments/'.$formId);
                        // if(Session::get('is_review') == 1)
                        // {
                        //     $folder = public_path('/uploads/markedattachments/'.$formId);
                        // }
                        $filePath = $folder.'/_DONOTSIGN_'.$panImage['pf_type_image'];
                        // dd($oldFilePath);
                        if (!File::exists($folder)) {
                            File::makeDirectory($folder, 0775, true, true);
                        }
                        //checks file exists or not in temp folder
                        if(file_exists($oldFilePath)){
                            //move file from temp folder to upload folder
                            if (File::move($oldFilePath, $filePath)){

                            }else{
                                //make it false if any file didn't uploaded
                                $is_uploaded = false;
                            }
                        }
                    }
                    $i++;
                }
            }

            if(Session::get('customer_type') != "ETB"){
                if($is_uploaded){

                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'File is not uploaded','data'=>[]]);
                }
            }


           
            if(isset($requestData['AccountDetails']['account_type']) && (($requestData['AccountDetails']['account_type'] == 4) || ($requestData['AccountDetails']['account_type'] == 5)) && Session::get('customer_type') == "ETB"){
                return json_encode(['status'=>'fail','msg'=>'selected account type restricted for ETB','data'=>[]]);
            }

            // $i = 1;
            foreach ($requestData['PanDetails'] as $applicantSeq => $panDetails)
            {
                $accountDetails = (array) $panDetails;
                

                if($accountDetails['pf_type'] == 'form60'){

                     $accountDetails['pancard_no'] = '';
                }

              

                $accountDetails['applicant_sequence'] = $applicantSeq;

                Session::put('isCustExists_ID-'.$applicantSeq,1);
                Session::put('isCustExists_Address-'.$applicantSeq,1);

                if(isset($panDetails['applicantId']))
                {
                    $accountDetails['updated_by'] = Session::get('userId');
                    $applicantId = $accountDetails['applicantId'];
                    $accountDetails = Arr::except($accountDetails,['applicantId','is_update']);
                    $response = DB::table("CUSTOMER_OVD_DETAILS")->whereId($applicantId)->update($accountDetails);
                    $accountIds[$applicantSeq] = $applicantId;

                    if (!isset($aofNumber)) {
                        $accountDetailslforAof = DB::table('ACCOUNT_DETAILS')->select('aof_number')->where('ID',$formId)->get()->toArray();
                        $aofNumber = current($accountDetailslforAof)->aof_number;
                    }

                    if (isset($EtbNtbStatus['customer_type'][$accountDetails['applicant_sequence']]) && $EtbNtbStatus['customer_type'][$accountDetails['applicant_sequence']] == 'ETB') {
                        $etbCustDetails = Array(
                          "AOF_NUMBER" => $aofNumber,
                          "CUBE_EMAIL" => $accountDetails['email'],
                          "CUBE_MOB_NO" => $accountDetails['mobile_number'],
                          "UPDATED_BY"=> Session::get('userId'),
                          "UPDATED_AT"=> Carbon::now()
                        );

                        $updateEtbDetails = DB::table("ETB_CUST_DETAILS")
                                            ->where('FORM_ID', $formId)
                                            ->where('APPLICANT_SEQUENCE',$accountDetails['applicant_sequence'])
                                            ->update($etbCustDetails);
                    }
                }else{
                    $accountDetails['FORM_ID'] = $formId;
                    $accountDetails['created_by'] = Session::get('userId');
                    $response = DB::table("CUSTOMER_OVD_DETAILS")->insertGetId($accountDetails);
                    $accountIds[$applicantSeq] = $response;
                }
                array_push($dobArray, $accountDetails['dob']);
                // $i++;
            }
            //Array to hold user details
            $userArray = array();
            
            if(isset(Session::get('UserDetails')[$formId]['AccountDetails'])){
                $userArray[$formId]['AccountDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountDetails'],$requestData['AccountDetails']);
                $userArray[$formId]['customerOvdDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['customerOvdDetails'],$requestData['PanDetails']);
                $userArray[$formId]['AccountIds'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountIds'],$accountIds);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['AccountDetails'] = $requestData['AccountDetails'];
                $userArray[$formId]['customerOvdDetails'] = $requestData['PanDetails'];
                $userArray[$formId]['AccountIds'] = $accountIds;
                $userDetails = $userArray;
            }
            //store user details into sesion based on formId
            if(Session::get('customer_type') == "ETB"){
                // if($requestData['AccountDetails']['account_type'] == 3){
                //     $url = 'addovddocuments';
                // }else{
                //     $url = 'addriskclassification';
                // }
                    $url = 'addovddocuments';
            }

            
            if(Session::get('is_review') == 1){
                $url = $this->discrepencyresponse($formId,'step-1');
            }
           
			// foreach ($EtbNtbStatus['customer_type'] as $applicantSeq => $status) {
    			$markETB_NTB = CommonFunctions::markETB_NTB($formId, $EtbNtbStatus);
            // }
            if (!$markETB_NTB) {
                return json_encode(['status'=>'fail','msg'=>'Error! Data issue in fetching customer type','data'=>[]]);
            }

            $checkAOFnumber = DB::table('ACCOUNT_DETAILS')->select('aof_number')->where('ID',$formId)->get()->toArray();
            $checkAOFnumber = (array) current($checkAOFnumber); 
            
            if(isset($checkAOFnumber['aof_number']) &&  $checkAOFnumber['aof_number'] == '')
            {
                return json_encode(['status'=>'fail','msg'=>'Something Went Wrong, Please Try Again','data'=>[]]);
            }
            
            if($response){
                //commit database if response is true
                DB::commit();
                Session::put('UserDetails',$userDetails);
                Session::put('dobArray',$dobArray);
                
                $checkEtbNtb = DB::table('CUSTOMER_OVD_DETAILS')->select('IS_NEW_CUSTOMER')
                                                            ->where('FORM_ID',$formId)
                                                            ->get()
                                                            ->toArray();
                $checkEtb = 0;
                $checkNtb = 0;
                for($i=0;count($checkEtbNtb)>$i;$i++){
                    if($checkEtbNtb[$i]->is_new_customer == 0){
                        $checkEtb++;
                    }elseif($checkEtbNtb[$i]->is_new_customer == 1){
                        $checkNtb++;
                    }
                }

                $getAccData = DB::table('ACCOUNT_DETAILS')->select('SOURCE','DELIGHT_SCHEME','ACCOUNT_TYPE')
                                                          ->where('ID',$formId)
                                                          ->get()
                                                          ->toArray();
                $getAccData = (array) current($getAccData);
           
                $flowtype = '';
                if($getAccData['delight_scheme'] != '' && $getAccData['account_type'] == 1){
                    $flowtype = 'DELIGHT';
                }else if($getAccData['source'] == 'CC'){
                    $flowtype = 'ETB/CC';
                }else if(count($checkEtbNtb) == $checkEtb){
                    $flowtype = 'ETB';
                }else if(count($checkEtbNtb) == $checkNtb){
                    $flowtype = 'NTB';
                }else{
                    $flowtype = 'HYBRID';
                }
                  // spacial Character stop for mobile 
                  if (preg_match('/[-]/', $requestData['PanDetails'][$i]['mobile_number'])) {
                    return json_encode(['status' => 'fail', 'msg' => 'Special Character Detected. Please Enter a Valid Mobile Number', 'data' => []]);
                }
                $schemeCodeDetails = CommonFunctions::getSchemeCodesBySchemeId($requestData['AccountDetails']['account_type'],$requestData['AccountDetails']['scheme_code']);
                $schemeCodeDetails = current($schemeCodeDetails);
                
                if ($schemeCodeDetails->scheme_code == 'SB146' && $requestData['AccountDetails']['no_of_account_holders'] > 1) {
                    return json_encode(['status'=>'fail','msg'=>'Only one applicant permitted for selected scheme code','data'=>[]]);
                }
                
                $updateData = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->update(['FLOW_TYPE'=>$flowtype]);
               

                return json_encode(['status'=>'success','msg'=>'Response Saved Successfully','data'=>['formId'=>$formId,'accountIds'=>$accountIds,'url'=>$url]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: addovddocuments
    *  Created By : Sharanya T
    *  Created At : 24-02-2020
    *
    *  Description:
    *  Method to view addovd documents template
    *
    *  Params:
    *
    *  Output:
    *  Returns template.
    */
    public function addovddocuments(Request $request)
    {
        try{
            $tokenParams = Cookie::get('token');
            $encrypt_key = substr($tokenParams, -5); 
            $userDetails = array();
            $reviewDetails = array();
            $reviewSectionDetails = array();
			$ovdIDs = array(); 
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $formId = base64_decode($decodedString);
            }

            $formId = Session::get('formId');
            $scenario = CommonFunctions::getScenario($formId, 'ovd_details');
            $accountDetails = DB::table('ACCOUNT_DETAILS')
            ->where('ACCOUNT_DETAILS.ID',$formId)
            ->get()->toArray();
            $userDetails['AccountDetails'] = $accountDetails = (array) current($accountDetails);
            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }

            if($scenario == 'Update'){
                //$formId = Session::get('reviewId');
                // $formId = Session::get('formId');
                
                
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->orderBy('applicant_sequence', 'ASC')
                                            ->get()->toArray();
                array_unshift($customerOvdDetails, "phoney");
                unset($customerOvdDetails[0]);
                // $enc_fields = ['Aadhaar Photocopy','Passport','Voter ID','Driving Licence'];
                $enc_fields = [1,2,3,6,7];
                foreach ($customerOvdDetails as $key => $value) {
                    if(in_array($customerOvdDetails[$key]->proof_of_identity ,$enc_fields)){
                        $customerOvdDetails[$key]->id_proof_card_number = CommonFunctions::encrypt256($value->id_proof_card_number,$encrypt_key); 
                    }
                    if(in_array($customerOvdDetails[$key]->proof_of_address ,$enc_fields)){
                        $customerOvdDetails[$key]->add_proof_card_number = CommonFunctions::encrypt256($value->add_proof_card_number,$encrypt_key); 
                    }
                }
                $userDetails['customerOvdDetails'] = json_decode(json_encode($customerOvdDetails),true);
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                        ->orderBy('applicant_sequence', 'ASC')
                                                        ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
                $reviwerRole = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('role_id','DESC')
                                                        ->limit(1)->pluck('role_id')->toArray();
                $reviwerRole = current($reviwerRole);
                $reviewDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->pluck('comments','column_name')->toArray();
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->distinct()->pluck('id','section')->toArray();
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['customerOvdDetails'][1]['gender'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }else{
                       
						   $ovdIDs = DB::table('CUSTOMER_OVD_DETAILS')
												->where('FORM_ID',$formId)
                                                ->orderBy('applicant_sequence', 'ASC')
                                                ->pluck('id')->toArray();							
												
							array_unshift($ovdIDs, "phoney");
							unset($ovdIDs[0]);
				
					}
                }

                $userDetailsArray = CommonFunctions::getFormTableDetails($formId, 'ovd_details');
                if (isset($userDetailsArray['customerOvdDetails']) && count($userDetailsArray['customerOvdDetails']) > 0) {
                    $userDetails = $userDetailsArray;
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! ovd Details not found','data'=>[]]);
                }
            }
            $idProofOVDs = CommonFunctions::getOVDList('ID_PROOF',$formId);
            $addressProofOVDs = CommonFunctions::getOVDList('PER_ADDRESS_PROOF',$formId);
            $currentAddressProofOVDs = CommonFunctions::getOVDList('CUR_ADDRESS_PROOF');
           
            $huf_karta_curnt_add1 = CommonFunctions::huf_proof();

            $huf_karta_curnt_add1 = (array) $huf_karta_curnt_add1;

            $huf_karta_curnt_add=[];

            foreach ($huf_karta_curnt_add1 as $key => $value) {
                $huf_karta_curnt_add[$value->id] = $value->ovd;
            }

            $accountType = Session::get('accountType');
            // $modeOfOperations =  Rules::getValidateModeofOperations($formId,$accountType);
            $genderArray = config('constants.GENDER');

            // huf Mop start here
            // $hufflow = DB::table('ACCOUNT_DETAILS')->select('CONSTITUTION')->whereId($formId)->get()->toArray();
            // $hufflow = (array) current($hufflow);

            if ($accountDetails['constitution'] == 'NON_IND_HUF') {
                if (in_array($accountType, [1, 2, 3])) {
                    $columnhuf = 'Karta';
            }
                $modeOfOperations = DB::table('MODE_OF_OPERATIONS')->where('OPERATION_TYPE',$columnhuf)
                                                                   ->pluck('operation_type', 'id')
                                                                   ->toArray();
            }else{
                $modeOfOperations = Rules::getValidateModeofOperations($formId, $accountType);
               }
            // huf mop end here

            $countries = CommonFunctions::getCountry();
            $applicantsDOBs = [];
            if(count($userDetails) > 0)
            {
                foreach ($userDetails['customerOvdDetails'] as $userData) {
                    array_push($applicantsDOBs, $userData['dob']);
                }
            }else{
                if(!empty(Session::get('dobArray')))
                {
                    $applicantsDOBs = Session::get('dobArray');
                }
            }
            

            $accountHolders = Session::get('no_of_account_holders');

            $religions = config('constants.RELIGIONS');

            if ($accountDetails['constitution'] == 'NON_IND_HUF') {
                unset($religions[2]);
                unset($religions[3]);
                unset($religions[6]);
                unset($religions[8]);
            }
             
            $signatureArray = config('constants.SIGNATURE_TYPES');
            if(Session::get('is_review') != '1'){
                unset($signatureArray[2]);
            }

            $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();

            $accountDetails = (array) current($accountDetails);

            $returnTitle = Rules::getTitleDetails($formId);
            $titles = array();

            foreach($returnTitle as $rec){
                if($rec->id=="10" && $accountDetails["constitution"] != "NON_IND_HUF") continue;
                $titles[$rec->id] = $rec->description;
            }

            // $schemeCode  = DB::table('SCHEME_CODES')->where('SCHEME_CODE', 'CA229')->get()->toArray();
            // $schemeCode = (array) current($schemeCode);
            $entityDetails = [];
            $entityDetails_huf = [];
            if($accountDetails['account_type'] == '2'){
                $inserted = ['FORM_ID' =>$formId];
                $checkEntityData = DB::table("ENTITY_DETAILS")->where('FORM_ID',$formId)->get()->toArray();
                
                if(count($checkEntityData) > 0){
                    $updateEntity = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->update($inserted);
                }else{
                    $insertData = DB::table("ENTITY_DETAILS")->insert($inserted);
                }
                
                $entityDetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $entityDetails = (array) current($entityDetails);
                $application_status = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                                                                  ->get()->toArray();
                $application_status = current($application_status);

                // if($application_status->scheme_code == 14 && $application_status->account_type == 2){
                //     $application_status->scheme_code = 1;
                // }

                $getSchemeDetails = DB::table('CA_SCHEME_CODES')->select('SCHEME_CODE')
                                                            ->where('ID',$application_status->scheme_code)
                                                            ->where('ACCOUNT_TYPE',2)
                                                            ->get()->toArray();
                $getSchemeDetails = (array) current($getSchemeDetails);  
            }else{
                $getSchemeDetails['scheme_code'] = '';
            }

            // encription for email and mobile number
            if(isset($entityDetails["entity_mobile_number"]) != "")
            $entityDetails["entity_mobile_number"] = CommonFunctions::encrypt256($entityDetails["entity_mobile_number"],$encrypt_key);
            if(isset($entityDetails["entity_email_id"]) != "")
            $entityDetails["entity_email_id"] = CommonFunctions::encrypt256($entityDetails["entity_email_id"],$encrypt_key);

            if(isset($accountDetails['delight_scheme']) && $accountDetails['delight_scheme'] == 5){

                $signatureTypes = Arr::except($signatureArray,3);
            }else{

                $signatureTypes = $signatureArray;
            }

          
            $globaluser_dob_ms = DB::table('CUSTOMER_OVD_DETAILS')->select('MARITAL_STATUS','DOB','ID')
                                                                    ->where('FORM_ID',$formId)
                                                                    ->orderBy('applicant_sequence', 'ASC')
                                                                    ->get()->toArray();
            $application_status = DB::table('ACCOUNT_DETAILS')
                                                                    ->where('ID',$formId)
                                                                     ->get()->toArray();

            $application_status = current($application_status);
            $huf_cop_row=[];
            if($accountDetails["constitution"]=="NON_IND_HUF"){
                $huf_cop_row = DB::table("NON_IND_HUF")->where("FORM_ID",$formId)->where("DELETE_FLG","N")->orderBy("ID")->get()->toArray();
                $huf_cop_row = (array) $huf_cop_row;
            }

            $huf_relation1 = DB::table('RELATIONSHIP')->where('HUF_RELATION',"1")->get()->toArray();
            $huf_relation1 = (array) $huf_relation1;
            $huf_relation = [];
            foreach($huf_relation1 as $k => $val){
                $huf_relation[$val->id] = $val->display_description;
            }

            
			
            return view('bank.addovddocuments')->with('formId',$formId)
                                                ->with('titles',$titles)
                                                ->with('religions',$religions)
                                                ->with('countries',$countries)
                                                ->with('userDetails',$userDetails)
                                                ->with('idProofOVDs',$idProofOVDs)
                                                ->with('genderArray',$genderArray)
                                                ->with('accountType',$accountType)
                                                ->with('reviewDetails',$reviewDetails)
                                                ->with('applicantsDOBs',$applicantsDOBs)
                                                ->with('accountHolders',$accountHolders)
                                                ->with('signatureTypes',$signatureTypes)
                                                ->with('addressProofOVDs',$addressProofOVDs)
                                                ->with('modeOfOperations',$modeOfOperations)
                                                ->with('currentAddressProofOVDs',$currentAddressProofOVDs)
                                                ->with('reviewSectionDetails',$reviewSectionDetails)
                                                ->with('globaluser_dob_ms',$globaluser_dob_ms)
                                                ->with('returnTitle',$returnTitle)
                                                ->with('entityDetails', $entityDetails)
                                                ->with('accountDetails', $accountDetails)
                                                ->with('application_status',$application_status)
                                                ->with('ovdIDs', $ovdIDs)
												->with('getSchemeDetails',$getSchemeDetails)
                                                ->with('huf_karta_curnt_add',$huf_karta_curnt_add)
                                                ->with("huf_cop_row",$huf_cop_row)
                                                ->with("entityDetails_huf",$entityDetails_huf)
                                                ->with("huf_relation",$huf_relation)
												;
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: saveovddetails
    *  Created By : Sharanya T
    *  Created At : 26-02-2020
    *
    *  Description:
    *  Method to save ovd details
    *
    *  Params:
    *  $requestData
    *
    *  Output:
    *  Returns Json.
    */
    public function saveovddetails(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $is_review = Session::get('is_review');
                $requestData = Arr::except($request->get('data'),['functionName','formId','mode_of_operation', 'account_holders','signature_type']);
                $currentAddProofImageData = $requestData;
                $is_update = false;
                $url = 'addriskclassification';
                DB::setDateFormat('DD-MM-YYYY');
                if(isset($requestData['is_update'])){
                    $is_update = true;
                    $requestData = Arr::except($requestData,'is_update');
                }
                
                $is_uploaded = true;
                $formId = $request->get('data')['formId'];
                
                $mode_of_operation = $request->get('data')['mode_of_operation'];
                $signature_type = $request->get('data')['signature_type'];
                $account_holders = $request->get('data')['account_holders'];

            // new pan api check 18_03_2024
                $getPanDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('PF_TYPE','PANCARD_NO','DOB','APPLICANT_SEQUENCE')->where('IS_NEW_CUSTOMER','!=','0')->where('FORM_ID',$formId)->get()->toArray();

                if(count($getPanDetails)>0){
                $newpanArray = array();
                $counterpan = 0;
                for($seq=0;count($getPanDetails)>$seq;$seq++){
                    if($getPanDetails[$seq]->pf_type == 'pancard'){
                        $newpanArray[$counterpan]['panNo'] = $getPanDetails[$seq]->pancard_no;
                        $newpanArray[$counterpan]['dob'] = Carbon::parse($getPanDetails[$seq]->dob)->format('d/m/Y');
                        if (isset($requestData[$getPanDetails[$seq]->applicant_sequence]['middle_name']) && $requestData[$getPanDetails[$seq]->applicant_sequence]['middle_name'] != '') {
                            $newpanArray[$counterpan]['name'] = $requestData[$getPanDetails[$seq]->applicant_sequence]['first_name'].' '.$requestData[$getPanDetails[$seq]->applicant_sequence]['middle_name'].' '.$requestData[$getPanDetails[$seq]->applicant_sequence]['last_name'];
                        }else{
                            $newpanArray[$counterpan]['name'] = $requestData[$getPanDetails[$seq]->applicant_sequence]['first_name'].' '.($requestData[$getPanDetails[$seq]->applicant_sequence]['last_name'] ?? '');
                        }
                        $counterpan++;
                    }
                }

                //end api check
                $sehcme_code_desc = '';
               if(count($newpanArray)>0){
                $a_details = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','SCHEME_CODE')
                                                         ->where('ID',$formId)
                                                         ->get()
                                                        ->toArray();

                $a_details = (array) current($a_details);

                $accountType = $a_details['account_type'];
                $schemeCode = $a_details['scheme_code'];

                $schemeDetails = CommonFunctions::getSchemeCodesBySchemeId($accountType, $schemeCode);
                $schemeDetails = (array) current($schemeDetails);
                $sehcme_code_desc = $schemeDetails['scheme_code'];
                
                $statusCheck = Api::newPanIsValid($newpanArray,$formId);
                $currdate = Carbon::now()->format('d-m-Y');

                if($statusCheck['status'] == 'success'){
                    for($i=0;count($statusCheck['data'])>$i;$i++){
            
                        $insertpandetails = DB::table('PAN_DETAILS')->insert(['PANNO'=>$statusCheck['data'][$i]['panNo'],
                                                                              'EXIST'=>$statusCheck['data'][$i]['panStatus'],
                                                                              'AADHAARSEEDINGSTATUS'=>$statusCheck['data'][$i]['seedingStatus'],
                                                                              //'CREATED_AT'=>$currdate,
                                                                              'NAME_MATCH_FLAG'=>$statusCheck['data'][$i]['name'],
                                                                              'DOB_MATCH_FLAG'=>$statusCheck['data'][$i]['dob'],
                                                                              'FATHER_NAME_MATCH_FLAG'=>$statusCheck['data'][$i]['fatherName'],
                                                                              'FORM_ID' =>$formId]);                    

                        if($statusCheck['data'][$i]['panStatus'] != 'E'){

                            return json_encode(['status'=>'fail','msg'=>'Invalid pan '.$statusCheck['data'][$i]['panNo'],'data'=>[]]);
                        }

                        if(strtoupper($schemeDetails['scheme_code']) == 'SB146'){
                            if(!(in_array($statusCheck['data'][$i]['seedingStatus'],["Y","NA"]))){
                                return json_encode(['status'=>'fail','msg'=>'NSDL: Pancard not linked','data'=>[]]);
                            }
                        }
                    }
                }else{
                    return json_encode(['status'=>'fail','msg'=>$statusCheck['msg'],'data'=>[]]);
                }
            }
        }
                if(isset($requestData['OVDS'])){
                    $customerPhoto = $requestData['OVDS']['customers_photograph'];
                    $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$customerPhoto);
                    //$folder = public_path('/uploads/attachments/'.$formId);
                    // $folder = 'storage/uploads/attachments/'.$formId; commented during version upgrade
                    $folder = storage_path('/uploads/attachments/'.$formId);
                    //define new file path
                    if(substr($customerPhoto,0,11) == "_DONOTSIGN_"){
                        $filePath = $folder.'/'.$customerPhoto;
                    }else{
                        $filePath = $folder.'/_DONOTSIGN_'.$customerPhoto;
                    }
                    // $filePath = $folder.'/'.$customerPhoto;
                    if (!File::exists($folder)) {
                        File::makeDirectory($folder, 0775, true, true);
                    }

                    //checks file exists or not in temp folder
                    if(file_exists($oldFilePath)){
                        //move file from temp folder to upload folder
                        if (File::move($oldFilePath, $filePath)){

                        }else{
                            //make it false if any file didn't uploaded
                            $is_uploaded = false;
                        }
                    }
                    if(count($account_holders)>0)
                    {
                        $account_holders = implode(',', $account_holders);
                    }

                    $customerPhoto = (substr($customerPhoto,0,11) == "_DONOTSIGN_") ? substr($customerPhoto,11) : $customerPhoto;
                    $updateArray = ['CUSTOMERS_PHOTOGRAPH'=>$customerPhoto,
                                    'MODE_OF_OPERATION'=>$mode_of_operation,
                                    'SIGNATURE_TYPE'=>$signature_type,
                                    'ACCOUNT_HOLDERS'=>$account_holders];
                    if($mode_of_operation == 4)
                    {
                        $updateArray['DEBITCARD_REQUIRED'] = "N";
                    }

                    $updateCustomerPhoto = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                            ->update($updateArray);
                  
                    $accountDetails = array_merge($requestData['OVDS'],['mode_of_operation'=>$mode_of_operation,
                                        'signature_type'=>$signature_type,'account_holders'=>$account_holders]);
                    $requestData = Arr::except($requestData,'OVDS');
                }

                $userDetails = array();
                $accountCountries = array();
                $addressFields = ['per_address_line1','per_address_line2','per_landmark','current_address_line1','current_address_line2','current_landmark'];
                $fieldsToValidate = ['first_name','middle_name','last_name','short_name','mothers_maiden_name','mother_full_name','father_name','per_address_line1','per_address_line2','per_landmark','current_address_line1','current_address_line2','current_landmark'];

                $schemeDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
                $schemeDetails = (array) current($schemeDetails);

                $flowtag = $schemeDetails['constitution'];

                if(isset($schemeDetails['account_type']) && $schemeDetails['account_type'] == '2'){
                    if(isset($requestData['Entity'])){
                        $requestData['Entity']['entity_mop'] = $mode_of_operation;
                            $requestData['Entity']['entity_scheme_code'] = $schemeDetails['scheme_code'];
                            // if($requestData['Entity']['entity_scheme_code'] == '14'){
                            //     $requestData['Entity']['entity_scheme_code'] = 1;
                            // }

                            $entityDetails = EntityAccountController::saveEntityAccount($requestData,$formId);
    						$entityDetails1 = json_decode($entityDetails);
                            if(isset($entityDetails1->status)== 'fail'){
                                return $entityDetails;
                            }
                    }
                }

                if($flowtag=="NON_IND_HUF"){
                    if(isset($requestData['HUF'])){
                        $requestData['HUF']['huf_entity_mop'] = $mode_of_operation;
                            $requestData['HUF']['huf_entity_scheme_code'] = $schemeDetails['scheme_code'];
                            // if($requestData['Entity']['entity_scheme_code'] == '14'){
                            //     $requestData['Entity']['entity_scheme_code'] = 1;
                            // }

                            $entityDetails = EntityAccountController::saveEntityAccountHUF($requestData,$formId);
    						$entityDetails1 = json_decode($entityDetails);
                            if(isset($entityDetails1->status)== 'fail'){
                                return $entityDetails;
                            }
                    }
                    if(isset($requestData['HUF_COP'])){
                        $huf_cop_row = $requestData["HUF_COP"]["huf_coparcenar"];
                        $del_cop_id = [];
                        foreach($huf_cop_row as $key=> $val){
                            $name= $val["name"];
                            $dob = $val["dob"];
                            $rel = $val["rel"];
                            $cop_type = $val["cop_type"];
                            if(empty($name) || empty($dob) || empty($rel)|| empty($cop_type)){
                                return json_encode(['status'=>'fail','msg'=>'All coparcenors feilds are required','data'=>[]]);  
                            }
                            if(!empty($cop_type)){
                                if($cop_type == 'Member' && $rel !=11){
                                return json_encode(['status'=>'fail','msg'=>'Member should be spouse','data'=>[]]);  
                                }
                                elseif($cop_type =='Coparcenor' && $rel == 11){
                                    return json_encode(['status'=>'fail','msg'=>'Only member should be spouse','data'=>[]]);
                                }
                            }
                            $cop_data=[
                                "FORM_ID"=>$formId,
                                "COPARCENAR_NAME"=>"$name",
                                "HUF_RELATION"=>$rel,
                                "COPARCENER_TYPE"=>$cop_type,
                                "DOB"=> "$dob"
                            ];
                            if(isset($val["cop_id"])){
                                $cop_id = $val["cop_id"];
                                array_push($del_cop_id,$cop_id);
                                $cop_ins = DB::table("NON_IND_HUF")->where("ID",$cop_id)->update($cop_data);
                            }else{
                                $cop_ins = DB::table("NON_IND_HUF")->insertGetId($cop_data);
                                array_push($del_cop_id,$cop_ins);
                            }
                        }
                        $currDate  = Carbon::now(); 
                        DB::table("NON_IND_HUF")->where("FORM_ID",$formId)->whereNotIn("ID",$del_cop_id)->update(["DELETE_FLG"=>"Y","DELETED_AT"=>"$currDate"]);
                    }
                }

                $requestData = Arr::except($requestData,'Entity');
                $requestData = Arr::except($requestData,'HUF');
                $requestData = Arr::except($requestData,'HUF_COP');

                $i = 1;
                ///
                //$tokenParams = explode('.',Cookie::get('token'));
                //$tokenKey = $tokenParams[2];
                ///

                 $acc_details = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','SCHEME_CODE')
                                                            ->where('ID',$formId)
                                                            ->get()
                                                            ->toArray();

                $acc_details = (array) current($acc_details);

                $accountType = $acc_details['account_type'];
                $schemeCode = $acc_details['scheme_code'];

                $schemeDetails = CommonFunctions::getSchemeCodesBySchemeId($accountType, $schemeCode);
                $schemeDetails = (array) current($schemeDetails);
                $sehcme_code_desc = $schemeDetails['scheme_code'];
                
                foreach($requestData as $ovdDetails)
                {
                    if(strtoupper($sehcme_code_desc) == 'SB146'){

                        if($ovdDetails['proof_of_address'] == 9){
    
                            $ovdDetails['OVDS']['add_proof_image'] = '';
                            $ovdDetails['passport_driving_expire_permanent'] = '';
                            $ovdDetails['add_proof_image'] = '';
                        }
                    }

                $kycpresent = 'N';

                if($flowtag !="NON_IND_HUF"){
                
                    if($ovdDetails['proof_of_identity'] == '9' || $ovdDetails['proof_of_address'] == '9'){
                    $kycpresent = 'Y';
                }
            }
                   
                $isNewCustomer=DB::table('CUSTOMER_OVD_DETAILS')->select('IS_NEW_CUSTOMER')
                                                                    ->where('ID',$ovdDetails['applicantId'])
                                                                    ->get()->toArray();
                $isNewCustomer = current($isNewCustomer);
               
                if((isset($requestData[2]['address_per_flag']) !=1) && ($flowtag=="NON_IND_HUF") &&  ($isNewCustomer->is_new_customer =='1') && ($kycpresent != 'Y')){
                    if(isset($ovdDetails['OVDS']['id_proof_image']) == "" || isset($ovdDetails['OVDS']['add_proof_image']) == ""){
                        return json_encode(['status'=>'fail','msg'=>'Ovd Image is not Uploaded Please Upload all Images','data'=>[]]);
                    }
                }
        
                else if(($flowtag !="NON_IND_HUF") && ($isNewCustomer->is_new_customer =='1') && ($kycpresent != 'Y')){
                    if(isset($ovdDetails['OVDS']['id_proof_image']) == "" || isset($ovdDetails['OVDS']['add_proof_image']) == ""){
                        return json_encode(['status'=>'fail','msg'=>'Ovd Image is not Uploaded Please Upload all Images','data'=>[]]);
                    }
                }

                    $validationmessage = 'Please Select Mandatory Proof of Indentiy For Applicant : '.$i.'<br>'
                                    .'Driving Licence '
                                    .'/ Passport'
                                    .'/ Voter ID';
                
                    
                    if(isset($ovdDetails['proof_of_identity']) && $ovdDetails['proof_of_identity'] == '9'){
                        if(isset($ovdDetails['proof_of_address']) && $ovdDetails['proof_of_address'] != ''){
                            return json_encode(['status'=>'fail','msg'=>'Invalid combination of ID and Address proof noted. Please clear browser cache and try again for Applicant :'.$i,'data'=>[]]);  
                        }
                    }                    
                    
                    // 22May23 -- dec - aes id and add proof card nos.                    
                    // 25May23 -- dec RSA
                    if(isset($ovdDetails['id_proof_card_number']) && $ovdDetails['id_proof_card_number'] != ''){
                        $ovdDetails['id_proof_card_number'] = CommonFunctions::decryptRS($ovdDetails['id_proof_card_number']);
                        if($ovdDetails['id_proof_card_number'] == '' || substr($ovdDetails['id_proof_card_number'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['id_proof_card_number']]]);  
                        } 
                    } 
                    if(isset($ovdDetails['add_proof_card_number']) && $ovdDetails['add_proof_card_number'] != ''){
                        $ovdDetails['add_proof_card_number'] = CommonFunctions::decryptRS($ovdDetails['add_proof_card_number']);
                        if($ovdDetails['add_proof_card_number'] == '' || substr($ovdDetails['add_proof_card_number'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['add_proof_card_number']]]);  
                        } 
                    }
                   
                    
		                        if(isset($ovdDetails['per_address_line1']) && $ovdDetails['per_address_line1'] != ''){
                        $ovdDetails['per_address_line1'] = CommonFunctions::decryptRS($ovdDetails['per_address_line1']);
                        if($ovdDetails['per_address_line1'] == '' || substr($ovdDetails['per_address_line1'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_address_line1']]]);  
                    }                     
                    }

                    if(isset($ovdDetails['per_address_line2']) && $ovdDetails['per_address_line2'] != ''){
                        $ovdDetails['per_address_line2'] = CommonFunctions::decryptRS($ovdDetails['per_address_line2']);
                        if($ovdDetails['per_address_line2'] == '' || substr($ovdDetails['per_address_line2'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_address_line2']]]);  
                        } 
                    }
                    
                    if(isset($ovdDetails['per_country']) && $ovdDetails['per_country'] != ''){
                        $ovdDetails['per_country'] = CommonFunctions::decryptRS($ovdDetails['per_country']);
                        if($ovdDetails['per_country'] == '' || substr($ovdDetails['per_country'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_country']]]);  
                        } 
                    }

                    if(isset($ovdDetails['per_pincode']) && $ovdDetails['per_pincode'] != ''){
                        $ovdDetails['per_pincode'] = CommonFunctions::decryptRS($ovdDetails['per_pincode']);
                        if($ovdDetails['per_pincode'] == '' || substr($ovdDetails['per_pincode'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_pincode']]]);  
                        } 
                    }


                    if(isset($ovdDetails['per_state']) && $ovdDetails['per_state'] != ''){
                        $ovdDetails['per_state'] = CommonFunctions::decryptRS($ovdDetails['per_state']);
                        if($ovdDetails['per_state'] == '' || substr($ovdDetails['per_state'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_state']]]);  
                        } 
                    }

                    if(isset($ovdDetails['per_city']) && $ovdDetails['per_city'] != ''){
                        $ovdDetails['per_city'] = CommonFunctions::decryptRS($ovdDetails['per_city']);
                        if($ovdDetails['per_city'] == '' || substr($ovdDetails['per_city'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_city']]]);  
                        } 
                    }

                    if(isset($ovdDetails['per_landmark']) && $ovdDetails['per_landmark'] != ''){
                        $ovdDetails['per_landmark'] = CommonFunctions::decryptRS($ovdDetails['per_landmark']);
                        if($ovdDetails['per_landmark'] == '' || substr($ovdDetails['per_landmark'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['per_landmark']]]);  
                        } 
                    }
                

                    if(isset($ovdDetails['current_address_line1']) && $ovdDetails['current_address_line1'] != ''){
                        $ovdDetails['current_address_line1'] = CommonFunctions::decryptRS($ovdDetails['current_address_line1']);
                        if($ovdDetails['current_address_line1'] == '' || substr($ovdDetails['current_address_line1'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_address_line1']]]);  
                        } 
                    }

                    if(isset($ovdDetails['current_address_line2']) && $ovdDetails['current_address_line2'] != ''){
                        $ovdDetails['current_address_line2'] = CommonFunctions::decryptRS($ovdDetails['current_address_line2']);
                        if($ovdDetails['current_address_line2'] == '' || substr($ovdDetails['current_address_line2'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_address_line2']]]);  
                        } 
                    }

                    if(isset($ovdDetails['current_country']) && $ovdDetails['current_country'] != ''){
                        $ovdDetails['current_country'] = CommonFunctions::decryptRS($ovdDetails['current_country']);
                        if($ovdDetails['current_country'] == '' || substr($ovdDetails['current_country'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_country']]]);  
                        } 
                    }

                    if(isset($ovdDetails['current_pincode']) && $ovdDetails['current_pincode'] != ''){
                        $ovdDetails['current_pincode'] = CommonFunctions::decryptRS($ovdDetails['current_pincode']);
                        if($ovdDetails['current_pincode'] == '' || substr($ovdDetails['current_pincode'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_pincode']]]);  
                        } 
                    }


                    if(isset($ovdDetails['current_state']) && $ovdDetails['current_state'] != ''){
                        $ovdDetails['current_state'] = CommonFunctions::decryptRS($ovdDetails['current_state']);
                        if($ovdDetails['current_state'] == '' || substr($ovdDetails['current_state'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_state']]]);  
                        } 
                    }

                    if(isset($ovdDetails['current_city']) && $ovdDetails['current_city'] != ''){
                        $ovdDetails['current_city'] = CommonFunctions::decryptRS($ovdDetails['current_city']);
                        if($ovdDetails['current_city'] == '' || substr($ovdDetails['current_city'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_city']]]);  
                        } 
                    }

                    if(isset($ovdDetails['current_landmark']) && $ovdDetails['current_landmark'] != ''){
                        $ovdDetails['current_landmark'] = CommonFunctions::decryptRS($ovdDetails['current_landmark']);
                        if($ovdDetails['current_landmark'] == '' || substr($ovdDetails['current_landmark'],0,2) == '__1'){
                            return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$ovdDetails['current_landmark']]]);  
                        } 
                    }

                    
                    //24feb2022 required photoidefication we select form 60 doccument
                  
                    $pftype=DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where('id',$ovdDetails['applicantId'])
                                        ->pluck('pf_type')->toArray();
                    $pftype = current($pftype);
                                    
                    
                    if($pftype=='form60' && !in_array($ovdDetails['proof_of_identity'], [1,2,3,6,9])){ 

                    // return json_encode(['status'=>'fail','msg'=>$validationmessage,'data'=>[]]);
                    // validation removed as discussed with ravi on (07-04-2022) 
                    }

                    if($flowtag=="NON_IND_HUF"){
                        if ( (isset($ovdDetails['current_pincode']) && strlen($ovdDetails['current_pincode']) < 6) || ( isset($ovdDetails['per_pincode']) && strlen($ovdDetails['per_pincode']) < 6)) {
                        return json_encode(['status'=>'fail','msg'=>'Pincode validation failed for applicant-'.$i,'data'=>[]]);
                    }
                    }else if (strlen($ovdDetails['current_pincode']) < 6 || strlen($ovdDetails['per_pincode']) < 6) {
                        return json_encode(['status'=>'fail','msg'=>'Pincode validation failed for applicant-'.$i,'data'=>[]]);
                    }
                    
                    
                    $validationFailedField = CommonFunctions::apastropheValidations($ovdDetails,$fieldsToValidate);
                    if (isset($validationFailedField) && $validationFailedField != '') {
                        return json_encode(['status'=>'fail','msg'=>'field validation failed ( '.$validationFailedField.' ).','data'=>[]]);
                    }
                    
                    foreach ($addressFields as $addressField) {
                        
                        if(isset($ovdDetails[$addressField])){
                        if (strlen($ovdDetails[$addressField]) > 45) {
                                return json_encode(['status'=>'fail','msg'=>$addressField.' is more than 45 characters','data'=>[]]);
                                } 
                        }
                        // else if($flowtag !="NON_IND_HUF"){
                        //     if (strlen($ovdDetails[$addressField]) > 45) {
                        //         return json_encode(['status'=>'fail','msg'=>$addressField.' is more than 45 characters','data'=>[]]);
                        //     } 
                        // }
                    }  
                    // if ((isset($ovdDetails['proof_of_identity']) && $ovdDetails['proof_of_identity'] != 9) && (isset($ovdDetails['proof_of_current_address']) && $ovdDetails['proof_of_current_address'] == 29)) {
                    //     return json_encode(['status'=>'fail','msg'=>'Only EKyc applicant are permitted Others as current address proof','data'=>[]]);
                    // }                       
                   
                    // $isNewCustomer=DB::table('CUSTOMER_OVD_DETAILS')    
                    // ->select('IS_NEW_CUSTOMER')                                   
                    // ->where('id',$ovdDetails['applicantId'])
                    // ->get()->toArray();
                    // $isNewCustomer = current($isNewCustomer);

				  $checkekycboth = 'N';
                    if(isset($ovdDetails['proof_of_identity']) &&  $ovdDetails['proof_of_identity'] == 9){
                        $checkekycboth = 'Y';
                    }
                    if($ovdDetails['proof_of_address'] == 9){
                        $checkekycboth = 'Y';
                        if($ovdDetails['add_proof_aadhaar_ref_number'] == ''){
                            return json_encode(['status'=>'fail','msg'=>'Unable to fetch vault reference number.','data'=>[]]);
                        }
                    }

                    if($ovdDetails['proof_of_address'] == 1){
                        if($ovdDetails['add_proof_card_number'] == ''){
                            return json_encode(['status'=>'fail','msg'=>'Please enter valid Aadhaar number.','data'=>[]]);
                        }
                    }

                    if ($checkekycboth != 'Y' && $ovdDetails['proof_of_current_address'] == 29) {
                        return json_encode(['status'=>'fail','msg'=>'Only EKyc applicant are permitted Others as current address proof','data'=>[]]);
                    }

                    if ($flowtag !="NON_IND_HUF" && $isNewCustomer->is_new_customer =='1') {

                        $checkOvdFields = CommonFunctions::ETBNTBcheck($ovdDetails);
                    
                        if(isset($checkOvdFields) && $checkOvdFields['status'] == 'warning'){
                            return json_encode(['status'=>'fail','msg'=>'Customer already exists with given ID proof for applicant-'.$i,'data'=>[]]);
                        }    
                    }                   

                    
                    $checkOvdFields = CommonFunctions::checkOvdFields($ovdDetails);

                    if (isset($checkOvdFields[0]) && $flowtag !="NON_IND_HUF") {
                        return json_encode(['status'=>'fail','msg'=>'Failed! Unable to fetch field : '.$checkOvdFields[0],'data'=>[$checkOvdFields]]);
                    }
                    
                    $countriesList = array();
                    if(isset($ovdDetails['OVDS']))
                    {
                        foreach($ovdDetails['OVDS'] as $ovdType=>$ovd)
                        {
                            foreach($requestData as $key =>$data)
                            {  
                            $files = explode(',', $ovd);
                            foreach ($files as $file)
                            {
                                    if($file != ''){
                                $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$file);
                                //$folder = public_path('/uploads/attachments/'.$formId);
                                $folder = storage_path('/uploads/attachments/'.$formId);                                    
                                
                                //define new file path
                                $filePath = $folder.'/'.$file;
                                if (!File::exists($folder)) {
                                    File::makeDirectory($folder, 0775, true, true);
                                }
                                //checks file exists or not in temp folder
                                if(file_exists($oldFilePath)){
                                    //move file from temp folder to upload folder
                                    if (File::move($oldFilePath, $filePath)){
        
                                    }else{
                                        //make it false if any file didn't uploaded
                                        $is_uploaded = false;
                                    }
                                }
                            }
                        }
                    }
                }
                    }
                    
                    if($is_update){
                        $accountId = $ovdDetails['applicantId'];
                        $ovdDetails = (array) Arr:: except($ovdDetails,'applicantId');
                    }else{
                        if(Session::get('screen') >= 2)
                        {

                        }else{
                            $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>2]);
                        }
                        $accountId = Session::get('UserDetails')[$formId]['AccountIds'][$i];
                    }
                    
                    $ovdData = (array) Arr:: except($ovdDetails,'OVDS');
                    if(Session::get('UserDetails')[$formId]['AccountDetails'] != '')
                    {
                        if(Session::get('role') == "11"){

                        $userArray[$formId]['AccountDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountDetails']);
                        }else{
                            $userArray[$formId]['AccountDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountDetails'],$accountDetails);
                        }

                        $userArray[$formId]['customerOvdDetails'][$i] = array_replace_recursive(Session::get('UserDetails')[$formId]['customerOvdDetails'][$i],$ovdData);
                        $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
                    }
             
                    $ovdDetailsTable = DB::table("CUSTOMER_OVD_DETAILS")->whereId($accountId)
                                                    ->orderBy('applicant_sequence', 'ASC')
                                                    ->get()->toArray();
                    if($i == 2 && $hufflow = "NON_IND_HUF" && isset($currentAddProofImageData[$i]['address_per_flag']))   {

                        if ($ovdDetailsTable[0]->add_proof_image != '' && $currentAddProofImageData[$i]['address_per_flag'] == 1) {
                                $ovdData['add_proof_image'] = '';
                            }
                        }        
                                                  
                    if ($ovdDetailsTable[0]->current_add_proof_image != '' && $currentAddProofImageData[$i]['address_flag'] == 1) {
                        $ovdData['current_add_proof_image'] = '';
                    }
                   
                    $ovdData['updated_by'] = Session::get('userId');
                    $updateovd = true;
                    
                    
                    if(Session::get('is_review') != 1){
                        if($flowtag=="NON_IND_HUF"){
                            if(isset($ovdDetails['proof_of_identity'])){
                        $checkStatus = Rules::prechecksaveOvdDetails($ovdDetails);
                            }else{
                                $checkStatus = "success";
                            }
                        }else{
                            $checkStatus = Rules::prechecksaveOvdDetails($ovdDetails);
                        }
                  
                        if($checkStatus != 'success'){
                            return json_encode(['status'=>'fail','msg'=>'EKYC data mismatch for selected applicant '.$i,'data'=>[]]);
                        }
                    }

                    
                    if(isset($ovdData['datahash']) && $ovdData['datahash'] != ''){
                        unset($ovdData['datahash']);
                    }

                    $updateovd = DB::table("CUSTOMER_OVD_DETAILS")->whereId($accountId)->update($ovdData);
                        
                    if($flowtag=="NON_IND_HUF"){
                        if(isset($ovdData['per_country'])){
                    array_push($accountCountries, $ovdData['per_country']);
                    }

                    }else{
                    array_push($accountCountries, $ovdData['per_country']);

					                    $checkSchemeRule=Rules::checkSchemeSpecificRule($ovdDetails,$i,$formId);
                                        
                    	if(isset($checkSchemeRule['status']) && $checkSchemeRule['status']== 'fail'){
                        
                        return json_encode(['status'=>'fail','msg'=>$checkSchemeRule['msg'],'data'=>[]]);
                        
                    	}
                    }
                    
                    $entitysequecne = ['APPLICANT_SEQUENCE'=>$i];
                    $updateentitydetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)
                                                                     ->update($entitysequecne);

                    if($flowtag=="NON_IND_HUF"){
                        if(isset($ovdData['per_country'])){
                    array_push($accountCountries, $ovdData['per_country']);
                }
                    }else{
                        array_push($accountCountries, $ovdData['per_country']);
                    }
                    $i++;
                
                }
                //store user financial details into sesion based on formId
                Session::put('UserDetails',$userDetails);
                Session::put('accountCountries',$accountCountries);
                if(Session::get('is_review') == 1){
                    $url = $this->discrepencyresponse($formId,'step-2');
                }
                if((Session::get('in_progress') == 1) && (Session::get('screen') < 2))
                {
                    Session::put('screen',2);
                }

                $checkEtbNtb  = DB::table('CUSTOMER_OVD_DETAILS')->select('IS_NEW_CUSTOMER')->where('FORM_ID',$formId)
                                                                    ->get()
                                                                    ->toArray();

                $checkNtbEtbCtn = 0;

                for($i=0;count($checkEtbNtb)>$i;$i++){
                    if($checkEtbNtb[$i]->is_new_customer != '0'){
                        $checkNtbEtbCtn++;
                    }
                }
                
                

                // if ($userArray[$formId]['AccountDetails']['account_type'] == '3' && (Session::get('customer_type') == "ETB")) {
                if ($checkNtbEtbCtn == "0" && $userArray[$formId]['AccountDetails']['account_type'] == '3') {
                    $url = 'addfinancialinfo';
                }

                if($updateovd){
                    return json_encode(['status'=>'success','msg'=>'OVD Details Updated Successfully','data'=>['url'=>$url]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /* Method Name: addfinancialinfo
     * Created By : Ravali N
     * Created At : 25-02-2020
     * Modified By : Sharanya T
     * Modified At : 02-03-2020
     *
     * Modified: Fetch FormId and render to template
     *
     * Description:
     * Method to show addfinancialinfo template
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function addfinancialinfo(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            $reviewSectionDetails = array();
            $schemeDetails = array();
            $accountNumbers = array();
            $depositType = 1;
            $interest_payout = 1;
            $customer_name = '';
            $msg = '';
            //$accountType = Session::get('accountType');
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);

                $params = base64_decode($decodedString);
                $tokens = explode('_', $params);
                $formId = $tokens[0];
                if($formId == ""){

                    return json_encode(['status'=>'fail','msg'=>'Error! Form ID not found','data'=>[]]);
                }
                $cc_etb_data = substr($params,strlen($formId)+1);
                $cc_etb_details = json_decode($cc_etb_data, true);
            }else{

                return json_encode(['status'=>'fail','msg'=>'Error! Invalid invocation','data'=>[]]);
            }



            if(isset($cc_etb_details['etb_cc'])){

                $cc_etb_details = $cc_etb_details;
                $accountType  = $cc_etb_details['accountType'];
            }else{
                $account_type = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE')
                                                        ->where('ID',$formId)
                                                        ->get()->toArray();
                    $account_type =  (array) current($account_type);
                    $accountType = $account_type['account_type'];
            }

            DB::setDateFormat('DD-MM-YYYY');
            $formId = Session::get('formId');
            $scenario = CommonFunctions::getScenario($formId, 'initial_funding');

            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }

            if($scenario == 'Update'){
                //$formId = Session::get('reviewId');
                $formId = Session::get('formId');
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('initial_funding_type','initial_funding_date','amount',
                                'reference','bank_name','ifsc_code','account_number',
                                'account_name','cheque_image','funding_source',
                                'days','months','years','td_amount','auto_renew','self_thirdparty',
                                'others_type','emd','emd_name','maturity', 'relationship','maturity_flag','reason_for_account_change','maturity_account_name','maturity_bank_name','maturity_ifsc_code','maturity_account_number','cancel_cheque_image','is_new_customer','marital_status')
                            //->leftjoin('RELATIONSHIP','RELATIONSHIP.CODE', DB::raw("LPAD(CUSTOMER_OVD_DETAILS.RELATIONSHIP, 3, '0')"))
                            //->where('RELATIONSHIP.IS_CHEQUE_RELATION', 1)
                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                            ->orderBy('applicant_sequence', 'ASC')
                            ->get()->toArray();

                if (isset($customerOvdDetails[0]->relationship) && $customerOvdDetails[0]->relationship != '') {
                    $relationship = DB::table('RELATIONSHIP')->where('IS_CHEQUE_RELATION', 1)
                                                                ->where('id',$customerOvdDetails[0]->relationship)
                                                                ->get()->toArray();
                    
                    $relationship = current($relationship);
                    $customerOvdDetails[0]->relationship = $relationship->id;
                }
                
                $userDetails['FinancialDetails'] = (array) current($customerOvdDetails);

                if ($accountType != 2 && $accountType != 1 && $userDetails['FinancialDetails']['initial_funding_type'] == 5) {
                   $userDetails['FinancialDetails']['initial_funding_type'] = '';
                }
                $reviwerRole = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('role_id','DESC')
                                                        ->limit(1)->pluck('role_id')->toArray();
                $reviwerRole = current($reviwerRole);
                $reviewDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->pluck('comments','column_name')->toArray();
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->distinct()->pluck('id','section')->toArray();

                

            }else{
                // if(Session::get('formId') != ''){
                //     $formId = Session::get('formId');
                //     if(!empty(Session::get('UserDetails')[$formId]['FinancialDetails'])){
                //         $userDetails = Session::get('UserDetails')[$formId];
                //     }
                // }

                $userDetailsArray = CommonFunctions::getFormTableDetails($formId, 'initial_funding');
                if (isset($userDetailsArray['FinancialDetails']) && count($userDetailsArray['FinancialDetails']) > 0) {
                    $UserDetails = $userDetailsArray;
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! funding details not found','data'=>[]]);
                }
            }
            if((Session::get('customer_type') == "ETB") && ($accountType == 3))
            {
                if((Session::get('etb_cc') == "CC") && ($accountType == 3)){

                                $accountNumbers = Session::get('accountNumbers');

                }else{

                $customerId = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                ->orderBy('applicant_sequence','ASC')
                                                                ->pluck('customer_id')->toArray();
                $customerId = current($customerId);
                // $accountDetails = Api::accountNumbersWithBalance($customerId,$formId);
                // if (!isset($accountDetails['status']['isSuccess'])) {
                //      return json_encode(['status'=>'fail','msg'=>'Api Error! Please try again later','data'=>[]]);

                // }
                $accountDetails = CommonFunctions::getAccountDetailsBasedOnCustID($customerId);
            
                    if(count($accountDetails) > 0)
                {
                        //for remove minus balance from accounts
                        // foreach($accountDetails as $accountNumber)
                        // {
                        //     if (str_starts_with($accountNumber->clr_bal_amt, '-')) {
                        //         $startFromNegative = array_search($accountNumber, $accountDetails);
                        //         unset($accountDetails[$startFromNegative]);
                        //     }
                        // }

                        foreach($accountDetails as $accountNumber)
                        {
                            $balance = $accountNumber->clr_bal_amt;
                            if ($balance == '') {
                                $balance = '0.00';
                            }else{
                                $balance = floatval($balance);

                            }   

                            if ($accountNumber->schm_type == 'SBA') {
                               $accountNumbers[$accountNumber->foracid] = $accountNumber->foracid.' - (Rs. '.$accountNumber->clr_bal_amt.')';
                            }
                        }
                    
                }else{
                        $msg = 'Api Error! Please try again later';
                    }
                }
               }
           

$custmoerDetailschek = DB::table('CUSTOMER_OVD_DETAILS')->select('MARITAL_STATUS')
                                                                    ->where('FORM_ID',$formId)
                                                                    ->get()->toArray();
            $custmoerDetailschek = current($custmoerDetailschek);                                         
            //fetch relationship
            $relationship = Rules::setrelationship($formId,$custmoerDetailschek,'CHEQUE');
            // $relationship = DB::table('RELATIONSHIP')->where(['IS_CHEQUE_RELATION'=>1,'IS_ACTIVE'=>1])
                                                //                                          ->pluck('display_description','id')->toArray();
 
            $banksList = DB::table('BANK')->where('IS_ACTIVE',1)->pluck('bank_name','id')->toArray();

            $interestpayout = config('constants.INTREST_PAYOUT');
            $maturity = config('constants.MATURITY');
            $frequency = config('constants.FREQUENCY');
            $typeofAccount = config('constants.TYPE_OF_ACCOUNT');

            if(isset(Session::get('UserDetails')[$formId]['AccountDetails']))
            {
                $tdsavingSchemeCode = 'scheme_code';
                if($accountType == 4){
                    $tdsavingSchemeCode = 'td_scheme_code';
                }

                $scheme_code = Session::get('UserDetails')[$formId]['AccountDetails'][$tdsavingSchemeCode];
                $table = 'SCHEME_CODES';
                    $schemeDetails = CommonFunctions::getSchemeDetails($formId);

                if($accountType == 3 || $accountType == 4)
                {
                    $table = 'TD_SCHEME_CODES';
                    $schemeDetails = DB::table($table)->whereId($scheme_code)
                                                    ->get()->toArray();
                    $schemeDetails = (array) current($schemeDetails);
                    $depositType = config('constants.DEPOSIT_TYPES.'.$schemeDetails['td_rd']);
                    $interest_payout = config('constants.PAYOUT_TYPES.'.$schemeDetails['payout_type']);
                }
                if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails']['first_name']))
                {
                    $ovdDetails = Session::get('UserDetails')[$formId]['customerOvdDetails'];
                    $customer_name = $ovdDetails['first_name']. ' '.$ovdDetails['middle_name'].' '.$ovdDetails['last_name'];
                }
            }
            if ($customer_name == '') {
                $customerdetails = DB::table('CUSTOMER_OVD_DETAILS')->where('form_id',$formId)->get()->toArray();
                $customerDetails = current($customerdetails);
                $customer_name = $customerDetails->first_name.' '.$customerDetails->middle_name.' '.$customerDetails->last_name;
            }
            $is_minor = Rules::checkMinorDeclaration($userDetails);

            $min_ip_reqmt = array();

            $schemesDet = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $schemesDet = current($schemesDet);


            switch ($schemesDet->account_type) {
                case '1':  // SA
                    $sa = DB::table('SCHEME_CODES')->whereId($schemesDet->scheme_code)->pluck('min_ip_amount')->toArray();
                    $sa = current($sa);
                    $min_ip_reqmt['SA'] = $sa;
                    break;

                case '2':  // CA
                    $ca = DB::table('CA_SCHEME_CODES')->whereId($schemesDet->scheme_code)->pluck('min_ip_amount')->toArray();
                    $ca = current($ca);
                    $min_ip_reqmt['CA'] = $ca;
                    break;

                case '3':  // TD
                    $td = DB::table('TD_SCHEME_CODES')->whereId($schemesDet->scheme_code)->pluck('min_amount')->toArray();
                    $td = current($td);
                    $min_ip_reqmt['TD'] = $td;
                    break;

                case '4': // COMBO
                    $sa = DB::table('SCHEME_CODES')->whereId($schemesDet->scheme_code)->pluck('min_ip_amount')->toArray();
                    $sa = current($sa);
                    $min_ip_reqmt['SA'] = $sa;

                    $td = DB::table('TD_SCHEME_CODES')->whereId($schemesDet->td_scheme_code)->pluck('min_amount')->toArray();
                    $td = current($td);
                    $min_ip_reqmt['TD'] = $td;
                    break;

                case '5':  // DSA
                    $sa = DB::table('SCHEME_CODES')->whereId($schemesDet->scheme_code)->pluck('min_ip_amount')->toArray();
                    $sa = current($sa);
                    $min_ip_reqmt['SA'] = $sa;
                    break;

                default:
                    $min_ip_reqmt['SA'] = '99999999';
                    $min_ip_reqmt['TD'] = '99999999';
                    $min_ip_reqmt['CA'] = '99999999';
                    break;
            }

            $schemeDetails = json_decode(json_encode($schemeDetails), true);

            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();

            $accountDetails = (array) current($accountDetails);

            //returns to template
            return view('bank.addfinancialinfo')->with('formId',$formId)
                                                ->with('accountType',$accountType)
                                                ->with('depositType',$depositType)
                                                ->with('interest_payout',$interest_payout)
                                                ->with('interestpayout',$interestpayout)
                                                ->with('maturityList',$maturity)
                                                ->with('frequencyList',$frequency)
                                                ->with('relationships',$relationship)
                                                ->with('banksList',$banksList)
                                                ->with('typeofAccount',$typeofAccount)
                                                ->with('schemeDetails',$schemeDetails)
                                                ->with('userDetails',$userDetails)
                                                ->with('reviewDetails',$reviewDetails)
                                                ->with('customer_name',$customer_name)
                                                ->with('is_minor',$is_minor)
                                                ->with('msg',$msg)
                                                ->with('accountNumbers',$accountNumbers)
                                                ->with('reviewSectionDetails',$reviewSectionDetails)
                                                ->with('min_ip_reqmt',$min_ip_reqmt)
                                                ->with('cc_etb_details',$cc_etb_details)
                                                ->with("accountDetails",$accountDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function getifsccode(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch scheme data
                $ifscCode = DB::table('BANK')->whereId($requestData['id'])
                                                        ->get()->toArray();
                $ifscCode = (array) current($ifscCode);
                if($ifscCode){
                    return json_encode(['status'=>'success','msg'=>'Scheme Data Found.','data'=>$ifscCode]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: savefinancialinfo
    *  Created By : Ravali N
    *  Created At : 25-02-2020
    *  Modified By : Sharanya T
    *  Modified At : 02-03-2020
    *
    *  Modified: Fetched FormId and Update OVD Details Based on FormId
    *
    *  Description:
    *  Method to save financial info and profile details
    *
    *  Params:
    *  $profile details,$financial info
    *
    *  Output:
    *  saving in database.
    */
    public function savefinancialinfo(Request $request)
    {
        try {
            $chequeImages = array();
            //fetch get details from request
            $requestData = $request->get('data');
            $formId = $requestData['formId'];
            $requestData = Arr::except($requestData,['functionName','formId']);

            if (isset($requestData['financialDetails']['initial_funding_type']) && isset($requestData['financialDetails']['total_savings_funds']) && isset($requestData['financialDetails']['td_amount'])) { //FUNDING TYPE - CALLCENTER

                if ($requestData['financialDetails']['initial_funding_type'] == '3' ) {
                    if ($requestData['financialDetails']['total_savings_funds'] < $requestData['financialDetails']['td_amount']) {
                        $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AddAccountController','savefinancialinfo','Amount validation failed',$requestData['financialDetails']['total_savings_funds'],$requestData['financialDetails']['td_amount'],'1');

                        return json_encode(['status'=>'fail','msg'=>'Amount validation failed. Data tempering detected, event logged','data'=>[]]);
                    }else if($requestData['financialDetails']['min_td_amount'] > $requestData['financialDetails']['td_amount']){
                        $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AddAccountController','savefinancialinfo','Amount validation failed',$requestData['financialDetails']['total_savings_funds'],$requestData['financialDetails']['td_amount'],'1');

                        return json_encode(['status'=>'fail','msg'=>'Amount validation failed. Data tempering detected, event logged','data'=>[]]);
                    }
                }

            }

            // stop negative amount 21_09_2023 
            if(isset($requestData['financialDetails']['td_amount']) && $requestData['financialDetails']['td_amount'] !=""){
                if($requestData['financialDetails']['td_amount'] < 0){
                    return json_encode(['status'=>'fail','msg'=>'Please insert valid amount','data'=>[]]);
                }
            } 
            if(isset($requestData['financialDetails']['amount']) && $requestData['financialDetails']['amount'] !=""){
               
                if($requestData['financialDetails']['amount'] < 0){
                    return json_encode(['status'=>'fail','msg'=>'Please insert valid amount','data'=>[]]);
                }
            } 
            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
            $accountDetails = current($accountDetails);
            $checkFinancialFields = Self::checkFinancialFields($requestData['financialDetails'],$accountDetails->account_type,$accountDetails->is_new_customer);

            if (isset($checkFinancialFields[0])) {
                return json_encode(['status'=>'fail','msg'=>'Failed! Enable to fetch field : '.$checkFinancialFields[0],'data'=>[$checkFinancialFields]]);
            }

            
            $requestData['financialDetails'] = $checkFinancialFields;

            if (isset($requestData['financialDetails']['ifsc_code']) && $requestData['financialDetails']['ifsc_code'] != ' ') {
                $checkValidIfsc = Api::ifscCodeValidation($formId,$requestData['financialDetails']['ifsc_code']);
                if($checkValidIfsc != 'Success'){
                    return json_encode(['status'=>'fail','msg'=>'Ifsc Code Not Found in Finacle.','data'=>[]]);
                }
            }
            
            $global_cc_data_info = Session::get('global_cc_data');
            
            if (isset($requestData['financialDetails']['maturity_ifsc_code']) && $requestData['financialDetails']['maturity_ifsc_code'] != ' ') {
                $checkValidMaturityIfsc = Api::ifscCodeValidation($formId,$requestData['financialDetails']['maturity_ifsc_code']); 
                if($checkValidMaturityIfsc != 'Success'){
                    return json_encode(['status'=>'fail','msg'=>'Ifsc Code Not Found in Finacle.','data'=>[]]);
                }

                if(isset($global_cc_data_info['etb_cc']) &&  $global_cc_data_info['etb_cc'] == 'CC'){
                    if(substr($requestData['financialDetails']['maturity_ifsc_code'],0,4) != 'DCBL'){
                        return json_encode(['status'=>'fail','msg'=>'Ifsc Code Not Found in Finacle.','data'=>[]]);
                    }
                }

            }

            $ovdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID', $formId)->get()->toArray();
            $ovdDetails = current($ovdDetails);

            if (isset($requestData['financialDetails']['maturity_flag']) && $requestData['financialDetails']['maturity_flag'] == 0) {
                
                $requestData['financialDetails'] = Arr::except($requestData['financialDetails'],['maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name']);

                $ovdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID', $formId)->get()->toArray();
                $ovdDetails = current($ovdDetails);
                $maturity_account_name = $ovdDetails->first_name.' '.$ovdDetails->middle_name.' '.$ovdDetails->last_name;

                $requestData['financialDetails']['maturity_bank_name'] = 29;
                $requestData['financialDetails']['maturity_ifsc_code'] = 'DCBL0000018';
                $requestData['financialDetails']['maturity_account_number'] = '';
                $requestData['financialDetails']['maturity_account_name'] = $maturity_account_name;
            }
            
            if (isset($requestData['financialDetails']['others_type']) && $requestData['financialDetails']['others_type'] == 'zero') {
                $requestData['financialDetails']['funding_source'] = '';
                $requestData['financialDetails']['amount'] = '';
            }else{
                $requestData['financialDetails']['others_type'] = '';
            }

            if(isset($global_cc_data_info) && ($global_cc_data_info != '')){

                if($global_cc_data_info['account_number'] == $requestData['financialDetails']['account_number']){

                    if($requestData['financialDetails']['td_amount'] > $global_cc_data_info['account_balance']){
                        
                        $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AddAccountController','savefinancialinfo','Amount validation failed',$global_cc_data_info['account_balance'],$requestData['financialDetails']['td_amount'],'1');

                        return json_encode(['status'=>'fail','msg'=>'Amount validation failed. Data tempering detected, event logged','data'=>[]]);
                    }

                }else{
                    $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AddAccountController','savefinancialinfo','Account Number validation failed',$global_cc_data_info['account_number'],$requestData['financialDetails']['account_number'],'1');

                    return json_encode(['status'=>'fail','msg'=>'Account Number validation failed. Data tempering detected, event logged.','data'=>[]]);
                }
            }


            $formId = $request->get('data')['formId'];
            if(isset($requestData['financialDetails']['images']))
            {
                $chequeImages = $requestData['financialDetails']['images'];
                $requestData['financialDetails'] = Arr::except($requestData['financialDetails'],['images']);
            }


            if($requestData['financialDetails']['initial_funding_type'] == 2 && isset($requestData['financialDetails']['reference'])){

                $checkUTR = CommonFunctions::checkUniqueUtr($requestData['financialDetails']['reference'], $formId);
                if($checkUTR['status'] == 'true'){
                    return json_encode(['status'=>'fail','msg'=>$checkUTR['msg'],'data'=>[]]);
                }
            }

           

            //Begins db transaction
            DB::beginTransaction();
            $url = 'addnomineedetails';
            
            if(count($chequeImages) > 0)
            {
                foreach ($chequeImages as $image) {
                    //define old file path(temp folder)
                    $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$image);
                    //$folder = public_path('/uploads/attachments/'.$formId);
                    // $folder = 'storage/uploads/attachments/'.$formId; commented this line during version upgrade
                    $folder = storage_path('/uploads/attachments/'.$formId);
                    // if(Session::get('is_review') == 1)
                    // {
                    //     $folder = public_path('/uploads/markedattachments/'.$formId);
                    // }
                    //define new file path
                    // $filePath = $folder.'/'.$image;
                    if(substr($image,0,11) == "_DONOTSIGN_"){
                        $filePath = $folder.'/'.$image;
                    }else{
                        $filePath = $folder.'/_DONOTSIGN_'.$image;
                    }

                    if (!File::exists($folder)) {
                        File::makeDirectory($folder, 0775, true, true);
                    }
                    //checks file exists or not in temp folder
                    if(file_exists($oldFilePath)){
                        //move file from temp folder to upload folder
                        if (File::move($oldFilePath, $filePath)){

                        }else{
                            //make it false if any file didn't uploaded
                            $is_uploaded = false;
                        }
                    }
                }
            }

             //ETB  intial funding date  funding type 3 dcb
         
             $currDate  = Carbon::now();        
             $fundingDate  = Carbon::parse($currDate)->startOfDay()->format('d-m-Y');
             if((isset($ovdDetails->is_new_customer) && $ovdDetails->is_new_customer == '0') && $accountDetails->npc_review_by == "" && in_array($requestData['financialDetails']['initial_funding_type'],[3,5])){
                 $requestData['financialDetails']['initial_funding_date'] = $fundingDate;
             }

            if(isset($accountDetails->source) && $accountDetails->source == 'CC'){
                $requestData['financialDetails']['initial_funding_date'] = $fundingDate;
            }
            
            DB::setDateFormat('DD-MM-YYYY');
            $checkForm = DB::table("CUSTOMER_OVD_DETAILS")->where('FORM_ID',$formId)->get()->toArray();
            if(count($checkForm) > 0)
            {
                if ($checkForm[0]->initial_funding_type == 1 && $requestData['financialDetails']['initial_funding_type'] != 1) {
                    $requestData['financialDetails']['cheque_image'] = '';
                }
                //update financial  details into customer ovd details
                $requestData['financialDetails']['updated_by'] = Session::get('userId');
                $updateFinancialDetails = DB::table("CUSTOMER_OVD_DETAILS")->where('FORM_ID',$formId)
                                                                ->update($requestData['financialDetails']);
            }else{
                $requestData['financialDetails']['FORM_ID'] = $formId;
                $requestData['financialDetails']['updated_by'] = Session::get('userId');
                //insert financial  details into customer ovd details
                $updateFinancialDetails = DB::table("CUSTOMER_OVD_DETAILS")->insert($requestData['financialDetails']);
            }
            if(isset(Session::get('UserDetails')[$formId]['FinancialDetails'])){
                $userArray[$formId]['FinancialDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['FinancialDetails'], $requestData['financialDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                if(Session::get('screen') >= 4)
                {

                }else{
                    $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>4]);
                }
                $userArray[$formId]['FinancialDetails'] = $requestData['financialDetails'];
                if(Session::get('UserDetails') == '')
                {
                    $userDetails = $userArray;
                }else{
                    $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
                }
            }

            //store user financial details into sesion based on formId
            Session::put('UserDetails',$userDetails);
            if(Session::get('is_review') == 1){
                $url = $this->discrepencyresponse($formId,'step-4');
            }
            if((Session::get('in_progress') == 1) && (Session::get('screen') < 4))
            {
                Session::put('screen',4);
            }
            if($updateFinancialDetails){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Financial Details Updated Successfully','data'=>['url'=>$url]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkFinancialFields($fundingDetails,$accountType,$customerType){
        try{
            switch ($fundingDetails['initial_funding_type']) {
                case '1': //cheque
                    $requiredFields = ['initial_funding_type','images','cheque_image','initial_funding_date','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty'];

                    if ($accountType == '3' || $accountType == '4') { //TD
                        $requiredFields = ['initial_funding_type','images','initial_funding_date','cheque_image','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty','years','days','months','td_amount','auto_renew','interest_payout','emd','maturity','maturity_flag'];
                        
                        switch($fundingDetails['maturity_flag']){
                            case '0':

                            break;
                            case '1':
                                array_push($requiredFields, 'maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            case '2':
                                array_push($requiredFields, 'maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name','reason_for_Account_change','cancel_cheque_image','images');
                            break;
                            case '3':
                                array_push($requiredFields, 'maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            default:
                                return json_encode(['status'=>'fail','msg'=>'maturity flag not recognised','data'=>[]]);
                            break;
                        }

                    }

                    if ($fundingDetails['self_thirdparty'] == 'thirdparty') {
                        array_push($requiredFields, 'relationship');
                    }

                    break;
                case '2': //NEFT/RTGS
                    $requiredFields = ['initial_funding_type','initial_funding_date','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty'];

                    if ($accountType == '3' || $accountType == '4') { //TD
                        $requiredFields = ['initial_funding_type','initial_funding_date','reference','bank_name','ifsc_code','account_number','account_name','amount','self_thirdparty','years','days','months','td_amount','auto_renew','interest_payout','emd','maturity','maturity_flag'];
                        
                        switch($fundingDetails['maturity_flag']){
                            case '0':

                            break;
                            case '1':
                                array_push($requiredFields, 'maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            case '2':
                                array_push($requiredFields, 'maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name','reason_for_Account_change','cancel_cheque_image','images');
                            break;
                            case '3':
                                array_push($requiredFields, 'maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name');
                            break;
                            default:
                                return json_encode(['status'=>'fail','msg'=>'maturity flag not recognised','data'=>[]]);
                            break;
                        }

                    }

                    if ($fundingDetails['self_thirdparty'] == 'thirdparty') {
                        array_push($requiredFields, 'relationship');
                    }

                    break;
                case '5': //OTHERS
                    $requiredFields = ['initial_funding_type','others_type'];

                    if ($fundingDetails['others_type'] != 'zero') {
                        $requiredFields = ['initial_funding_type','funding_source','others_type','amount'];
                    }

                    break;
                case '3': //CALL CENTER
                    $requiredFields = ['initial_funding_type','maturity_flag','account_number','amount','years','days','months','td_amount','auto_renew','interest_payout','emd','maturity','maturity_bank_name','maturity_ifsc_code','maturity_account_number','maturity_account_name'];

                    if ($fundingDetails['maturity_flag'] == '2') {
                        array_push($requiredFields, 'reason_for_Account_change','cancel_cheque_image','images');
                    }

                    break;
                default:
                    return json_encode(['status'=>'fail','msg'=>'funding type not recognised','data'=>[]]);
                    break;
            }

            $fundingDetailsFields = [];
            foreach ($requiredFields as $value => $field) {
                if (!isset($fundingDetails[$field])) {
                    $fundingDetailsFields[0] = $field;
                }else{
                	$fundingDetailsFields[$field] = $fundingDetails[$field];
                }
            }

            return $fundingDetailsFields;

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addriskclassification(Request $request)
    {
        try{
            $source_of_funds = array();
            $userDetails = array();
            $reviewDetails = array();
            $reviewSectionDetails = array();
            $customerOvdDetails = array();
            if(!empty($request->all())){

                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $formId = base64_decode($decodedString);
                if($formId == ''){

                   return json_encode(['status'=>'fail','msg'=>'Form ID blank in token','data'=>[]]);
                }
            }else{
                if(Session::get('formId') != ''){

                    $formId = Session::get('formId');
                }else{

                   return json_encode(['status'=>'fail','msg'=>'Form ID errors','data'=>[]]);
                }
            }

            $formId = Session::get('formId');
            $scenario = CommonFunctions::getScenario($formId, 'risk_details');

            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }


            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();

            $accountDetails = (array) current($accountDetails);

            if($scenario == 'Update'){

                ////$formId = Session::get('reviewId');
                $formId = Session::get('formId');

                $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                                ->orderBy('applicant_sequence', 'ASC')
                                                                ->get()->toArray();
                array_unshift($riskDetails, "phoney");
                unset($riskDetails[0]);
                for($i=1;count($riskDetails)>=$i;$i++){
                    
                    // $userDetails['RiskDetails'][$riskDetails[$i]->applicant_sequence] = json_decode(json_encode($riskDetails),true);
                    $userDetails['RiskDetails'][$riskDetails[$i]->applicant_sequence] = json_decode(json_encode($riskDetails[$i]),true);
                    
                }
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                        ->orderBy('applicant_sequence', 'ASC')
                                                        ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
                $reviwerRole = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('role_id','DESC')
                                                        ->limit(1)->pluck('role_id')->toArray();
                $reviwerRole = current($reviwerRole);
                $reviewDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->pluck('comments','column_name')->toArray();
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->distinct()->pluck('id','section')->toArray();
            }else{
                // if(Session::get('formId') != ''){
                //     //$formId = Session::get('formId');
                //     if(!empty(Session::get('UserDetails')[$formId]['RiskDetails'])){
                //         $userDetails = Session::get('UserDetails')[$formId];
                //     }
                //     if(!empty(Session::get('UserDetails')[$formId]['customerOvdDetails'])){
                //         $customerOvdDetails = Session::get('UserDetails')[$formId]['customerOvdDetails'];
                //     }
                // }

                $userDetailsArray = CommonFunctions::getFormTableDetails($formId, 'risk_details');
                if (isset($userDetailsArray['risk_details']) && count($userDetailsArray['risk_details']) > 0) {
                    $userDetails = $userDetailsArray;
                }

                if (isset($userDetailsArray['customerOvdDetails'])) {
                    $customerOvdDetails = $userDetails['customerOvdDetails'];
                }
            }
            $customerOvdDetails = Session::get('UserDetails')[$formId]['customerOvdDetails'];

            $accountCountries = Session::get('accountCountries');
            //fetch Annual_turnover details
            $annual_turnover = config('constants.ANNUAL_TURNOVER');
            //fetch Annual_turnover details
            $expected_transactions = config('constants.EXPECTED_TRANSACTION');
            //fetch Annual_turnover details
            $approximate_value = config('constants.APROXIMATE_VALUE');
            //fetch basis categorisation details
            $basis_categorisation = CommonFunctions::getBasisCategorisation();
            // fetch basis of Source_of_funds
            // $source_of_funds = config('constants.SOURCE_OF_FUNDS');
            $requestData = $request->get('data');

            $schemeId = Session::get('UserDetails')[$formId]['AccountDetails']['scheme_code'];
            //$customerTypes = CommonFunctions::getCustomertype($schemeId);
            $countries = CommonFunctions::getCountry();
            $occupation = Rules::getValidateOccupation($formId);
            $customerTypeList = Rules::getValidateCustomerType($schemeId, $customerOvdDetails);
            // $occupation = CommonFunctions::getOccupation();
            $accountIds = Session::get('UserDetails')[$formId]['AccountIds'];
            $residenceList = CommonFunctions::getCountry();
            $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
            $educationList = config('constants.EDUCATION');
            $grossIncome = CommonFunctions::getgrossannualIncome();
            // $grossIncome = config('constants.GROSS_INCOME');
            //fetch networth income details
            $networth = config('constants.NETWORTH');
            $accountType = Session::get('accountType');

            $riskaccountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                                                              ->get()->toArray();
            $riskaccountDetails = (array) current($riskaccountDetails);
            $schemeDetails = CommonFunctions::getSchemeCodesBySchemeId($riskaccountDetails['account_type'],$riskaccountDetails['scheme_code']);
            $schemeDetails = (array) current($schemeDetails);

            $checkEtbNtbAllow = DB::table('CUSTOMER_OVD_DETAILS')->select('IS_NEW_CUSTOMER','APPLICANT_SEQUENCE','PF_TYPE')
                                                            ->where('FORM_ID',$formId)
                                                            ->get()
                                                            ->toArray();
            $checkEtbNtb = array();
            for($sq=0;count($checkEtbNtbAllow)>$sq;$sq++){
                $checkEtbNtb[$checkEtbNtbAllow[$sq]->applicant_sequence] = $checkEtbNtbAllow[$sq];
            }

            // fetch basis of Source_of_funds
            if($riskaccountDetails['constitution'] == "NON_IND_HUF"){
                $source_of_funds =  Rules::setsourceoffund($riskaccountDetails['account_type'],$riskaccountDetails['constitution'],$schemeDetails['scheme_code']);
            }else{
                $source_of_funds =  Rules::setsourceoffund($riskaccountDetails['account_type'],$riskaccountDetails['flow_tag_1'],$schemeDetails['scheme_code']);
            }
            // $source_of_funds = $source_of_funds1+$source_of_funds2;
            // $source_of_funds = config('constants.SOURCE_OF_FUNDS');
            if(isset($userDetails['RiskDetails']) && count($userDetails['RiskDetails']) >0){
             
                foreach($userDetails['RiskDetails'] as $key => $value){
                 
                    for($sq=0;count($checkEtbNtbAllow)>$sq;$sq++){
                        if($checkEtbNtbAllow[$sq]->applicant_sequence == $value['applicant_sequence']){
                            if($checkEtbNtbAllow[$sq]->is_new_customer == 0){
                                $getOccupationId = DB::table('OCCUPATION')->select('ID')->whereIn('ETB_NTB_BOTH',['BOTH','ETB'])->where('CODE',$value['occupation'])->get()->toArray();
                            }else{
                                $getOccupationId = DB::table('OCCUPATION')->select('ID')->whereIn('ETB_NTB_BOTH',['BOTH','NTB'])->where('CODE',$value['occupation'])->get()->toArray();
                            }
                        }
                    }
                    if(count($getOccupationId)>0){
                    $getOccupationId = (array) current($getOccupationId);
                    $userDetails['RiskDetails'][$key]['occupation'] = $getOccupationId['id'];
                }
            }
            }
            
            return view('bank.addriskclassification')->with('formId',$formId)
                                                    ->with('annualTurnover',$annual_turnover)
                                                    ->with('accountType',$accountType)
                                                    ->with('basisCategorisation',$basis_categorisation)
                                                    ->with('sourceOfFunds',$source_of_funds)
                                                    ->with('expected_transaction',$expected_transactions)
                                                    ->with('approximate_values',$approximate_value)
                                                    ->with('customerTypeList',$customerTypeList)
                                                    ->with('countries',$countries)
                                                    ->with('placeOfBirthList',$placeOfBirthList)
                                                    ->with('citizenshipList',$citizenshipList)
                                                    ->with('educationList',$educationList)
                                                    ->with('grossIncome',$grossIncome)
                                                    ->with('networthList',$networth)
                                                    ->with('occupationList',$occupation)
                                                    ->with('residenceList',$residenceList)
                                                    ->with('accountCountries',$accountCountries)
                                                    ->with('userDetails',$userDetails)
                                                    ->with('reviewDetails',$reviewDetails)
                                                    ->with('customerOvdDetails',$customerOvdDetails)
                                                    ->with('reviewSectionDetails',$reviewSectionDetails)
                                                    ->with('riskaccountDetails',$riskaccountDetails)
                                                    ->with('checkEtbNtb',$checkEtbNtb)
                                                    ->with('accountDetails',$accountDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function saveriskdetails(Request $request)
    {
        try{
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),'functionName');
            $formId = $request->get('data')['formId'];
            $url = 'addfinancialinfo';
            //Begins db transaction
            DB::beginTransaction();
            //build user Risk and Nominee deatils array with formId

            $formId = Session::get('formId');
            $scenario = CommonFunctions::getScenario($formId, 'risk_details');
            $accounttype = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','FLOW_TAG_1','constitution')->whereId($formId)->get()->toArray();
            $accounttype = (array) current($accounttype);
            $flowtag = $accounttype["constitution"];
            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }
            $occupationId = isset($requestData['riskclassificationDetails'][1]['occupation']) && $requestData['riskclassificationDetails'][1]['occupation'] != ''?$requestData['riskclassificationDetails'][1]['occupation']:'';
            $sourceOffunds = isset($requestData['riskclassificationDetails'][1]['source_of_funds']) && $requestData['riskclassificationDetails'][1]['source_of_funds'] != ''?$requestData['riskclassificationDetails'][1]['source_of_funds']:array();

            if($flowtag != "NON_IND_HUF"){
            $checksourcevalide = Rules::checkforoccupationbasedsourcefund($occupationId,$sourceOffunds,$accounttype['account_type']);
            }

            if(isset($checksourcevalide['status']) && $checksourcevalide['status'] == 'error'){
                return json_encode(['status'=>'fail','msg'=>$checksourcevalide['msg'].'.1','data'=>[]]);
            }
            for($i=1;count($requestData['riskclassificationDetails'])>=$i;$i++){
                $riskDetails = 0;
              
                if(isset($requestData['riskclassificationDetails'][$i]['applicantId']) && $requestData['riskclassificationDetails'][$i]['applicantId'] != ''){

                    $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('ACCOUNT_ID',$requestData['riskclassificationDetails'][$i]['applicantId'])->count();
                }
                // if($scenario == 'Update'){
                $riskclassificationDetails = $requestData['riskclassificationDetails'][$i];
                if($riskDetails > 0){
                //insert risk classification details into risk_classification_details table
                if(count($requestData['riskclassificationDetails']) > 0)
                {
                        // foreach ($requestData['riskclassificationDetails'] as $key => $riskclassificationDetails)
                        // {
                        
                    if (($riskclassificationDetails['is_new_customer'] != '0') && $riskclassificationDetails['risk_classification_rating'] == '') {
                        return json_encode(['status'=>'fail','msg'=>'Invalid risk classification for an applicant!','data'=>[]]);
                    }
                    
                    $appli_seqenc = $riskclassificationDetails['applicant_sequence'] = $i;
                        if ($riskclassificationDetails['residence'] != 1 || $riskclassificationDetails['citizenship'] != 1 || $riskclassificationDetails['country_name'] != 1) {
                        return json_encode(['status' => 'fail', 'msg' => 'Account opening is prohibited for restricted countries as Applicant-' . $appli_seqenc, 'data' => []]);
                    }
                
                    unset($riskclassificationDetails['is_new_customer']);
                        $riskclassificationDetails['applicant_sequence'] = $i;
    
                    if(isset($riskclassificationDetails['source_of_funds']))
                        {
                            if(count($riskclassificationDetails['source_of_funds']) > 0 ){
                                $riskclassificationDetails['source_of_funds'] = implode(',',$riskclassificationDetails['source_of_funds']);
                            }
                        }
                        if(isset($riskclassificationDetails['basis_categorisation']))
                        {
                            if(count($riskclassificationDetails['basis_categorisation']) > 0 ){
                                $riskclassificationDetails['basis_categorisation'] = implode(',',$riskclassificationDetails['basis_categorisation']);
                            }
                        }
                            $applicantId = isset($riskclassificationDetails['applicantId']) && $riskclassificationDetails['applicantId'] != ''?$riskclassificationDetails['applicantId']:'';
                        $riskclassificationDetails = (array) Arr::except($riskclassificationDetails,'applicantId');
                        $riskclassificationDetails['form_id'] = $formId;
                        //update risk classification details into risk_classification_details table
                        $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->where('ACCOUNT_ID',$applicantId)->update($riskclassificationDetails);
						
                    }
                    // }
            }else{
                if(count($requestData['riskclassificationDetails']) > 0)
                {   unset($riskclassificationDetails['is_new_customer']);
                        // $i = 1;
                      //  foreach ($requestData['riskclassificationDetails'] as $key => $riskclassificationDetails)
                    //   for($j=1;count($requestData['riskclassificationDetails'])>=$j;$j++)
                    //     {
                            // $riskclassificationDetails = $requestData['riskclassificationDetails'][$i];
                            // $riskclassificationDetails = (array) $riskclassificationDetails;
                            // $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('ACCOUNT_ID',$riskclassificationDetails['applicantId'])->count();
                            // if($riskDetails == '0'){
                                $riskclassificationDetails['applicant_sequence'] = $i;
        
                        if(isset($riskclassificationDetails['source_of_funds'])){
                            if(count($riskclassificationDetails['source_of_funds']) > 0 ){
                                $riskclassificationDetails['source_of_funds'] = implode(',',$riskclassificationDetails['source_of_funds']);
                            }
                        }
                        if(isset($riskclassificationDetails['basis_categorisation'])){
                            if(count($riskclassificationDetails['basis_categorisation']) > 0 ){
                                $riskclassificationDetails['basis_categorisation'] = implode(',',$riskclassificationDetails['basis_categorisation']);
                            }
                        }
                        $riskclassificationDetails['FORM_ID'] = $formId;
                        $riskclassificationDetails['ACCOUNT_ID'] = Session::get('UserDetails')[$formId]['AccountIds'][$i];
                        //insert risk classification details into risk_classification_details table
                                $applicatId = isset($riskclassificationDetails['applicantId']) && $riskclassificationDetails['applicantId'] != ''?$riskclassificationDetails['applicantId']:'';
                        // $riskclassificationDetails['ID'] = $applicatId;
                                unset($riskclassificationDetails['applicantId']);
                        $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->insert($riskclassificationDetails);
                                // $i++;
                            // }
                        // }
                    // $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>3]);
                    if(Session::get('screen') >= 3)
                    {

                    }else{
                        $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>3]);
                    }
                }
            }
            }

            if(isset(Session::get('UserDetails')[$formId]['RiskDetails'])){
                $userArray[$formId]['RiskDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['RiskDetails'], $requestData['riskclassificationDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['RiskDetails'] = $requestData['riskclassificationDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            Session::put('UserDetails',$userDetails);
            if(Session::get('is_review') == 1){
                $url = $this->discrepencyresponse($formId,'step-3');
            }
            if((Session::get('in_progress') == 1) && (Session::get('screen') < 3))
            {
                Session::put('screen',3);
            }
            if($saveRiskDetails){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Risk Classification Details Updated Successfully','data'=>['url'=>$url]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Data Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Exception Error! Please try again','data'=>[]]);
        }
    }

public function saveriskdetails_basedonSession(Request $request)
    {
        try{
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),'functionName');
            $formId = $request->get('data')['formId'];
            $url = 'addfinancialinfo';
            //Begins db transaction
            DB::beginTransaction();
            //build user Risk and Nominee deatils array with formId
            if((Session::get('is_review') == 1) || ((Session::get('in_progress') == 1 && Session::get('screen')>= 3) || (Session::get('customer_type') == 'ETB') && (Session::get('screen') >= 3)) || (isset(Session::get('UserDetails')[$formId]['RiskDetails']))){
                //insert risk classification details into risk_classification_details table
                if(count($requestData['riskclassificationDetails']) > 0)
                {
                    foreach ($requestData['riskclassificationDetails'] as $riskclassificationDetails)
                    {
                        if(isset($riskclassificationDetails['source_of_funds']))
                        {
                            if(count($riskclassificationDetails['source_of_funds']) > 0 ){
                                $riskclassificationDetails['source_of_funds'] = implode(',',$riskclassificationDetails['source_of_funds']);
                            }
                        }
                        if(isset($riskclassificationDetails['basis_categorisation']))
                        {
                            if(count($riskclassificationDetails['basis_categorisation']) > 0 ){
                                $riskclassificationDetails['basis_categorisation'] = implode(',',$riskclassificationDetails['basis_categorisation']);
                            }
                        }
                        $applicantId = $riskclassificationDetails['applicantId'];
                        $riskclassificationDetails = (array) Arr::except($riskclassificationDetails,'applicantId');
                        $riskclassificationDetails['form_id'] = $formId;
                        //update risk classification details into risk_classification_details table
                        $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->where('ACCOUNT_ID',$applicantId)->update($riskclassificationDetails);
                    }
                }
            }else{
                if(count($requestData['riskclassificationDetails']) > 0)
                {
                    $i = 1;
                    foreach ($requestData['riskclassificationDetails'] as $riskclassificationDetails)
                    {
                        $riskclassificationDetails = (array) $riskclassificationDetails;
                        if(isset($riskclassificationDetails['source_of_funds'])){
                            if(count($riskclassificationDetails['source_of_funds']) > 0 ){
                                $riskclassificationDetails['source_of_funds'] = implode(',',$riskclassificationDetails['source_of_funds']);
                            }
                        }
                        if(isset($riskclassificationDetails['basis_categorisation'])){
                            if(count($riskclassificationDetails['basis_categorisation']) > 0 ){
                                $riskclassificationDetails['basis_categorisation'] = implode(',',$riskclassificationDetails['basis_categorisation']);
                            }
                        }
                        $riskclassificationDetails['FORM_ID'] = $formId;
                        $riskclassificationDetails['ACCOUNT_ID'] = Session::get('UserDetails')[$formId]['AccountIds'][$i];
                        //insert risk classification details into risk_classification_details table
                        $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->insert($riskclassificationDetails);
                        $i++;
                    }
                    // $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>3]);
                    if(Session::get('screen') >= 3)
                    {

                    }else{
                        $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>3]);
                    }
                }
            }

            if(isset(Session::get('UserDetails')[$formId]['RiskDetails'])){
                $userArray[$formId]['RiskDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['RiskDetails'], $requestData['riskclassificationDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['RiskDetails'] = $requestData['riskclassificationDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            Session::put('UserDetails',$userDetails);
            if(Session::get('is_review') == 1){
                $url = $this->discrepencyresponse($formId,'step-3');
            }
            if((Session::get('in_progress') == 1) && (Session::get('screen') < 3))
            {
                Session::put('screen',3);
            }
            if($saveRiskDetails){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Risk Classification Details Updated Successfully','data'=>['url'=>$url]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function addnomineedetails(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            $reviewSectionDetails = array();
            $signature_type = '';
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $formId = base64_decode($decodedString);
            }
            
            $formId = Session::get('formId');
            $scenario = CommonFunctions::getScenario($formId, 'nominee_details');

            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }

            if($scenario == 'Update'){
                //$formId = Session::get('reviewId');
                $formId = Session::get('formId');
                $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)
                                                                        ->get()->toArray();
                array_unshift($nomineeDetails, "phoney");
                unset($nomineeDetails[0]);
                $userDetails['NomineeDetails'] = json_decode(json_encode($nomineeDetails),true);
                $reviwerRole = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('role_id','DESC')
                                                        ->limit(1)->pluck('role_id')->toArray();
                $reviwerRole = current($reviwerRole);
                $reviewDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->pluck('comments','column_name')->toArray();
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->distinct()->pluck('id','section')->toArray();
            }else{
                // if(Session::get('formId') != ''){
                //     $formId = Session::get('formId');
                //     if(!empty(Session::get('UserDetails')[$formId]['NomineeDetails'])){
                //         $userDetails = Session::get('UserDetails')[$formId];
                //     }
                // }
                // if(isset(Session::get('UserDetails')[$formId]['AccountDetails']['signature_type']))
                // {
                //     $signature_type = Session::get('UserDetails')[$formId]['AccountDetails']['signature_type'];
                // }

                $userDetailsArray = CommonFunctions::getFormTableDetails($formId, 'nominee_details');
                if (isset($userDetailsArray['NomineeDetails']) > 0) {
                    $userDetails = $userDetailsArray;
                }

                if (isset($userDetailsArray['ACCOUNT_DETAILS']['signature_type'])) {
                    $signature_type = $userDetailsArray['ACCOUNT_DETAILS']['signature_type'];
                }
            }

            $relations = DB::table('RELATIONSHIP')->where(['IS_NOMINEE_RELATION'=>1,'IS_ACTIVE'=>1])
                                                ->pluck('display_description','id')->toArray();

            $accountType = Session::get('accountType');

            $countries = CommonFunctions::getCountry();

            $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->where(['FORM_ID'=>$formId,'APPLICANT_SEQUENCE'=>1])
                                                                        ->get()->toArray();
            $customerDetails = current($customerDetails);

            $applicantDob = Carbon::parse($customerDetails->dob)->age;


            $nomineeIds = Array();
            $nomineeIds[] = DB::table("NOMINEE_DETAILS")->where('FORM_ID', $formId)->orderBy('id', 'ASC')->pluck('id')->toArray();
            $nomineeIds = current($nomineeIds);
            Session::put('nomineeIds',$nomineeIds);

            $schemeDetails = CommonFunctions::getSchemeDetails($formId);
            
            $globalCCData = Session::get('global_cc_data');

            $relations = Rules::setrelationship($formId,$customerDetails,'NOMINEE');

            $accountDetails = DB::table('ACCOUNT_DETAILS')
            ->where('ACCOUNT_DETAILS.ID',$formId)
            ->get()->toArray();

            $accountDetails = (array) current($accountDetails);
            return view('bank.addnomineedetails')->with('formId',$formId)
                                                    ->with('signature_type',$signature_type)
                                                    ->with('accountType',$accountType)
                                                    ->with('relations',$relations)
                                                    ->with('countries',$countries)
                                                    ->with('userDetails',$userDetails)
                                                    ->with('applicantDob',$applicantDob)
                                                    ->with('reviewDetails',$reviewDetails)
                                                    ->with('schemeDetails',$schemeDetails)
                                                    ->with('reviewSectionDetails',$reviewSectionDetails)
                                                    ->with('globalCCData', $globalCCData)
                                                    ->with("accountDetails",$accountDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function savenomineedetails(Request $request)
    {
        try{
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),['functionName','formId']);
            $is_update = false;
            if(isset($requestData['is_update'])){
                $is_update = true;
                $requestData = Arr::except($requestData,'is_update');
            }
            $is_uploaded = true;
            $formId = $request->get('data')['formId'];
            $role = Session::get('role');

            $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('FIRST_NAME','LAST_NAME','MIDDLE_NAME','APPLICANT_SEQUENCE','FATHER_NAME','FATHER_SPOUSE','GENDER')
                                                                ->where('FORM_ID',$formId)
                                                                ->orderBy('APPLICANT_SEQUENCE','ASC')
                                                                ->get()->toArray();

            $checkNominee =  Rules::checkapplicantnamenominee($customerDetails,$requestData);

            $customerDetails = (array) current($customerDetails);
            if(isset($checkNominee['status']) && $checkNominee['status'] == 'fail'){
                return json_encode(['status'=>'fail','msg'=>$checkNominee['msg'],'data'=>[]]);
            }

            $checkFather =  Rules::checknomineerelationship($customerDetails,$requestData);

            if($checkFather['status'] == 'fail'){
                return json_encode(['status'=>'fail','msg'=>$checkFather['msg'],'data'=>[]]);
            }

            $ccdetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                                                  ->get()->toArray();
            $ccdetails = (array) current($ccdetails);

            if(($ccdetails['account_type'] == 3) && ($ccdetails['source'] == 'CC')){

               $url = 'submission';
            }else{

               $url = 'declaration';
            }

            DB::setDateFormat('DD-MM-YYYY');
            if(count($requestData) > 0)
            {
                $witnessSignatures= array();
                foreach($requestData as $data)
                {
                    if (isset($data['nominee_country']) && $data['nominee_country'] != '') {
                        $finpcsDetails = CommonFunctions::getZipDetailsByZipCode($data['nominee_pincode'], $data['nominee_country']);
                    }
                    
                    if (isset($data['guardian_country']) && $data['guardian_country'] != '' && $data['nominee_age'] < 18) {
                        $finpcsDetails = CommonFunctions::getZipDetailsByZipCode($data['guardian_pincode'], $data['guardian_country']);
                    }
                    
                    if (isset($finpcsDetails['citycode']) && $finpcsDetails['citycode'] == '') {
                        return json_encode(['status'=>'fail','msg'=>'Error! Pincode and Country not found in registered database. Please contact NPC admin','data'=>[]]);
                    }

                    $fieldsToValidate = ['nominee_name','guardian_name','nominee_address_line1','nominee_address_line2','guardian_address_line1','guardian_address_line2'];

                    $validationFailedField = CommonFunctions::apastropheValidations($data, $fieldsToValidate);

                    if (isset($validationFailedField) && $validationFailedField != '') {
                        return json_encode(['status'=>'fail','msg'=>'field validation failed ( '.$validationFailedField.' ).','data'=>[]]);
                    }

                    if(isset($data['witnessSignatures']))
                    {
                        $witnessSignatures = $data['witnessSignatures'];
                        foreach ($witnessSignatures as $signature) {
                            $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$signature);
                            //$folder = public_path('/uploads/attachments/'.$formId);
                            // $folder = 'storage/uploads/attachments/'.$formId; commente line during version upgrade
                            $folder = storage_path('/uploads/attachments/'.$formId);
                            //define new file path
                            $filePath = $folder.'/'.$signature;
                            if (!File::exists($folder)) {
                                File::makeDirectory($folder, 0775, true, true);
                            }
                            //checks file exists or not in temp folder
                            if(file_exists($oldFilePath)){
                                //move file from temp folder to upload folder
                                if (File::move($oldFilePath, $filePath)){

                                }else{
                                    //make it false if any file didn't uploaded
                                    $is_uploaded = false;
                                }
                            }
                        }
                    }
                }
            }

            $formId = Session::get('formId');
            //nominee issue nominee details stored form id is blank
            if($formId == ''){
                return json_encode(['status'=>'fail','msg'=>'Form Id blank, Please try again later !','data'=>[]]);
            }

            $scenario = CommonFunctions::getScenario($formId, 'nominee_details');

            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }

            if($scenario == 'Update'){
                //update nominee details
                if(count($requestData) > 0)
                {
                    $addressFields = ['nominee_address_line1','nominee_address_line2','guardian_address_line1','guardian_address_line2'];
                    $i = 0;
                    foreach ($requestData as $nomineeDetails) {
                        foreach ($addressFields as $addressField) {
                            if (strlen($nomineeDetails[$addressField]) > 45) {
                                    return json_encode(['status'=>'fail','msg'=>$addressField.' is more than 45 characters','data'=>[]]);
                            }
                        }
                        
                        $nomineeDetails = Arr::except($nomineeDetails,['applicantId','witnessSignatures']);
                        if($nomineeDetails['nominee_dob'] != ''){
                            $nomineeDetails['nominee_dob'] = Carbon::parse($nomineeDetails['nominee_dob'])->format('d-m-Y');
                        }
                        $nomineeDetails['form_id'] = $formId;
                        if($is_update)
                        {
                            $j = $i + 1;
                            $nomineeId = $requestData[$j]['applicantId'];
                        }else{
                            $nomineeId = Session::get('nomineeIds')[$i];
                        }
                        // $nomineeId = '';
                        $saveNomineeDetails = DB::table("NOMINEE_DETAILS")->whereId($nomineeId)
                                                            ->update($nomineeDetails);
                        DB::commit();
                        $i++;
                    }
                }
            }else{
                //insert nominee details
                if(count($requestData) > 0)
                {
                    $nomineeIds = Array();

                    foreach ($requestData as $nomineeDetails) {
                        $nomineeDetails  = Arr::except($nomineeDetails , ['applicantId', 'witnessSignatures']);
                        $nomineeDetails['FORM_ID'] = $formId;
                        $saveNomineeDetails = $nomineeIds[] = DB::table("NOMINEE_DETAILS")->insertGetId($nomineeDetails);
                    }

                    if(Session::get('screen') >= 5)
                    {

                    }else{
                        $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>5]);
                    }
                    Session::put('nomineeIds',$nomineeIds);
                    DB::commit();
                }
            }

            if(isset(Session::get('UserDetails')[$formId]['NomineeDetails']))
            {
                $userArray[$formId]['NomineeDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['NomineeDetails'], $requestData);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['NomineeDetails'] = $requestData;
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }

            if(Session::get('is_review') == 1){
                $url = $this->discrepencyresponse($formId,'step-5');
            }
            if(isset(Session::get('UserDetails')[$formId]['FinancialDetails'])){
                $initial_funding_type = Session::get('UserDetails')[$formId]['FinancialDetails']['initial_funding_type'];
                if((Session::get('customer_type') == "ETB") && ($initial_funding_type == 3) && ($role == 11) )
                {
                    $url = 'submission';
                }
            }

            if($ccdetails['source'] == 'CC'){
                $is_cc_nri = DB::table('TD_SCHEME_CODES')->whereId($ccdetails['scheme_code'])->where('RI_NRI','NRI')->count();
                if($is_cc_nri == 1){
                    $url = 'declaration';
                }
            }

            // adding mandatory nominee 
            
            $adetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','SCHEME_CODE')
                                                    ->where('ID',$formId)
                                                    ->get()
                                                    ->toArray();
            
            $adetails = (array) current($adetails);

            $accountType = $adetails['account_type'];
            $schemeCode = $adetails['scheme_code'];
            
            $schemeDetails = CommonFunctions::getSchemeCodesBySchemeId($accountType, $schemeCode);
            $schemeDetails = (array) current($schemeDetails);

            if ($schemeDetails['scheme_code'] == 'SB146') {
                $requestData = (array) current($requestData);
                if (!isset($requestData['nominee_exists']) || $requestData['nominee_exists'] == 'no') {
                    return json_encode(['status' => 'fail', 'msg' => 'Nominee should be Mandatory for Selected scheme']);
                }
            }

            if((Session::get('in_progress') == 1) && (Session::get('screen') < 5))
            {
                Session::put('screen',5);
            }
            if($saveNomineeDetails){
                //commit database if response is true
                DB::commit();
                Session::put('UserDetails',$userDetails);
                return json_encode(['status'=>'success','msg'=>'Nomination Details Updated Successfully','data'=>['url'=>$url]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: declaration
    *  Created By : Ravali N
    *  Created At : 28-02-2020
    *
    *  Description:
    *  Method to show declararion template
    *
    *  Params:
    *  @params
    *
    *  Output:
    *  Returns template.
    */
    public function declaration(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            $reviewSectionDetails = array();
            $customerOvdDetails = array();
            $declarationExtraInfo = array();
            $delightDetails = array();
            $nominee_exists = '';
            $username = '';
            $nameOnPan = '';
            $nameMismatch = false;
            $pepApproval = false;
            $treasuryApproval = false;
            $annexureApproval = false;
            $vernacularApproval = false;
            $thirdpartyApproval = false;
            $cashApproval = false;
            $zeroApproval = false;
            $otherApproval = false;
            $is_minor = false;
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $formId = base64_decode($decodedString);
            }

            $formId = Session::get('formId');
            if ($formId == '') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to indentify form','data'=>[]]);
            }
            $scenario = CommonFunctions::getScenario($formId, 'declaration_details');

            $cust_Type = DB::table('ACCOUNT_DETAILS')->select('IS_NEW_CUSTOMER','ACCOUNT_TYPE','MODE_OF_OPERATION')->where('ID',$formId)->get()->toArray();

            $cust_Type = (array) current($cust_Type);
            $accountType = $cust_Type['account_type'];

            if($cust_Type['is_new_customer'] == 0){
               $customerType = 'ETB';
            }else{
               $customerType = 'NTB';
            }

            if ($scenario == 'Error') {
                return json_encode(['status'=>'fail','msg'=>'Error! Unable to recognise scenario','data'=>[]]);
            }

            if($scenario == 'Update'){
                $declarations = array();
                //$formId = Session::get('reviewId');
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->orderBy('applicant_sequence', 'ASC')
                                            ->get()->toArray();
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                            ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
                $userDetails['AccountDetails'] = (array) current($accountDetails);
                if($userDetails['AccountDetails']['account_type'] == 1 && $userDetails['AccountDetails']['delight_scheme'] == 5){
                    $delightSavings = true;
                }else{
                    $delightSavings = false;
                }

                if($delightSavings)
                {
                    $delightKitDetails = DB::table('DELIGHT_KIT')->whereId($userDetails['AccountDetails']['delight_kit_id'])
                                                                ->get()->toArray();
                    $userDetails['DelightDetails'] = (array) current($delightKitDetails);
                }

                $declarationDetails = DB::table('CUSTOMER_DECLARATIONS')
                                            ->where('FORM_ID',$formId)->get()->toArray();
                if(count($declarationDetails) > 0)
                {
                    foreach ($declarationDetails as $declaration)
                    {
                        $declaration = (array) $declaration;
                        $declarations[$declaration['declaration_type']] = 1;
                        if($declaration['applicant_sequence'] != '')
                        {
                            $declarations[$declaration['declaration_type'].'-'.$declaration['applicant_sequence'].'_proof'] = $declaration['attachment'];
                        }else{
                            $declarations[$declaration['declaration_type'].'_proof'] = $declaration['attachment'];
                        }
                    }
                }
                $userDetails['Declarations'] = $declarations;

                $reviwerRole = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('role_id','DESC')
                                                        ->limit(1)->pluck('role_id')->toArray();
                $reviwerRole = current($reviwerRole);
                $reviewDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->pluck('comments','column_name')->toArray();
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'ROLE_ID'=>$reviwerRole,'STATUS'=>0])
                                            ->distinct()->pluck('id','section')->toArray();
            }else{
                //$scenario = 'Insert';
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    // if(!empty(Session::get('UserDetails')[$formId]['Declarations'])){
                    //     $userDetails = Session::get('UserDetails')[$formId];
                    //     foreach ($userDetails['Declarations'] as $declaration => $value) {
                    //         if ($value == '0') {
                    //             unset($userDetails['Declarations'][$declaration]);
                    //         }
                    //     }
                    // }
                    $accountDetails = DB::table('ACCOUNT_DETAILS')
                                            ->where('ACCOUNT_DETAILS.ID',$formId)
                                            ->where('ACCOUNT_TYPE',1)->where('DELIGHT_SCHEME',5)
                                            ->get()->toArray();
                    if(count($accountDetails)>0){
                        $delightSavings = true;
                    }else{
                        $delightSavings = false;
                    }

                }

                $userDetailsArray = CommonFunctions::getFormTableDetails($formId,'declaration_details');
                $customerOvdDetails = $userDetailsArray['customerOvdDetails'];

                if (isset($userDetailsArray['Declarations']) && count($userDetailsArray['Declarations']) > 0) {
                    $userDetails = $userDetailsArray;
                    foreach ($userDetails['Declarations'] as $declaration => $value) {
                        if ($value == '0') {
                            unset($userDetails['Declarations'][$declaration]);
                        }
                    }
                }
            }
            $nom_exists = DB::table('NOMINEE_DETAILS')
                                    ->where('FORM_ID', $formId)
                                    ->where('NOMINEE_EXISTS', 'yes')
                                    ->get()->toArray();
            if(isset($nom_exists) && count($nom_exists) > 0){
                //$nominee_exists = Session::get('UserDetails')[$formId]['NomineeDetails'][1]['nominee_exists'];
                $nominee_exists = 'yes';
            }else{
                $nominee_exists = 'no';
            }

            if(count($customerOvdDetails) > 0)
            {

            }else{
                if(count(Session::get('UserDetails')[$formId]['customerOvdDetails']) > 0)
                {
                    $customerOvdDetails = Session::get('UserDetails')[$formId]['customerOvdDetails'];
                }
            }

            /*==============Below Code for Fetching Declaration details ==================*/
            $accountDetailsforScheme = DB::table('ACCOUNT_DETAILS')
                                                 ->where('ACCOUNT_DETAILS.ID',$formId)
                                                 ->get()->toArray();
            $accountDetailsforScheme = (array) current($accountDetailsforScheme);

            $sa_declarationIds = (array) null;
            $sa_excl_declarationIds = (array) null;
            $savingsDeclaration = false;

            $td_declarationIds = (array) null;
            $td_excl_declarationIds = (array) null;
            $TdDeclaration = false;
        
            if(isset($accountDetailsforScheme['account_type']) && ($accountDetailsforScheme['account_type']==1 || $accountDetailsforScheme['account_type']==4)){
                //SAVING OR COMBO
                $savingsDeclaration = true;
                $savingsScheme = DB::table('SCHEME_CODES')->where('ID',$accountDetailsforScheme['scheme_code'])->get()->toArray();
                $savingsScheme = (array) current($savingsScheme);

            }
            
			if(isset($accountDetailsforScheme['account_type']) && ($accountDetailsforScheme['account_type'] == 2 )){
                //FOR CURRENT ACCOUNT -- FETCHING SCHEME CODE
                // if($accountDetailsforScheme['scheme_code'] == 14){
                //     $accountDetailsforScheme['scheme_code'] = 1;
                // }

                $savingsDeclaration = true;
                $savingsScheme = DB::table('CA_SCHEME_CODES')->where('ID',$accountDetailsforScheme['scheme_code'])->get()->toArray();
                $savingsScheme = (array) current($savingsScheme);

            }

            if(isset($accountDetailsforScheme['account_type']) && ($accountDetailsforScheme['account_type']==3 || $accountDetailsforScheme['account_type']==4)){

                $TdDeclaration = true;
                if($accountDetailsforScheme['account_type'] == 4){

                $tdScheme = DB::table('TD_SCHEME_CODES')->where('ID',$accountDetailsforScheme['td_scheme_code'])->get()->toArray();

                }else{

                $tdScheme = DB::table('TD_SCHEME_CODES')->where('ID',$accountDetailsforScheme['scheme_code'])->get()->toArray();
                }
                $tdScheme = (array) current($tdScheme);
                
            }
           
            if($savingsDeclaration){
                $schemeDeclaration = DB::table('SCHEME_DECLARATION_MAPPING')
                                              ->where('SCHEME_CODE',$savingsScheme['scheme_code'])
                                              ->where(function ($query) {
                                                    $query->where('DECLARATION_IDS', '!=', null)
                                                            ->orWhere('EXCLUDED_DECLARATIONS', '!=', null);
                                                })
                                              ->get()->toArray();
                $schemeDeclaration = (array) current($schemeDeclaration);
                
                if (isset($schemeDeclaration['declaration_ids'])) {
                    $sa_declarationIds = explode(',', $schemeDeclaration['declaration_ids']);
                }else{
                    $sa_declarationIds = (array) null;
                }
                
                if (isset($schemeDeclaration['excluded_declarations'])) {
                    $sa_excl_declarationIds = explode(',', $schemeDeclaration['excluded_declarations']);
                }else{
                    $sa_excl_declarationIds = (array) null;
                }

                $getinitialfundingdetails = (array) current($customerOvdDetails);
                $amountval = $getinitialfundingdetails['amount'];
                if($getinitialfundingdetails['initial_funding_type'] != 5 && $accountDetailsforScheme['account_type'] == 1){
                    if($amountval >= $savingsScheme['min_ip_amount'] && $amountval < $savingsScheme['min_ip_desired_amount']){
                       array_push($sa_declarationIds,'156');
            }
                }
            }
           
          
            if(($accountDetailsforScheme['account_type'] == 2) && ($accountDetailsforScheme['flow_tag_1'] == 'INDI')){
                $ca_declartion = array();
                for($i=0;count($sa_declarationIds)>$i;$i++){
                    if(!in_array($sa_declarationIds[$i],[51,55,54,57,58])){
                        array_push($ca_declartion,$sa_declarationIds[$i]);
                    }
                }
                $sa_declarationIds = $ca_declartion;
            }
           
            if($TdDeclaration){
                $schemeDeclaration = DB::table('SCHEME_DECLARATION_MAPPING')
                                              ->where('SCHEME_CODE',$tdScheme['scheme_code'])
                                              ->where(function ($query) {
                                                $query->where('DECLARATION_IDS', '!=', null)
                                                        ->orWhere('EXCLUDED_DECLARATIONS', '!=', null);
                                                })
                                              ->get()->toArray();
                $schemeDeclaration = (array) current($schemeDeclaration);

                if (isset($schemeDeclaration['declaration_ids'])) {
                    $td_declarationIds = explode(',', $schemeDeclaration['declaration_ids']);
                }else{
                    $td_declarationIds = (array) null;
                }
                if (isset($schemeDeclaration['excluded_declarations'])) {
                    $td_excl_declarationIds = explode(',', $schemeDeclaration['excluded_declarations']);
                }else{
                    $td_excl_declarationIds = (array) null;
                }

            }

            $declarationIds = array_merge($sa_declarationIds,$td_declarationIds);
            $excl_declarationIds = array_merge($sa_excl_declarationIds,$td_excl_declarationIds);
            $ccSchemeCode = config('constants.CALL_CENTER_DECLARATION');
            $is_cc_nri = DB::table('TD_SCHEME_CODES')->whereId($accountDetailsforScheme['scheme_code'])->where('RI_NRI','NRI')->count();

            if($is_cc_nri == 1 && $accountDetailsforScheme['source'] == 'CC'){
                $declarationIds = ['53','142'];
            }
            
            $declarationMetaData = (array) null;
            $applicantsCount = $accountDetailsforScheme['no_of_account_holders'];

            if($accountDetailsforScheme['source'] != 'CC'){ 
                $aof_img = DB::table('DECLARATIONS')->where('blade_id','aof_back_img')->get()->toArray(); //Back aof image for non Call center
                $tmp = (array) current($aof_img);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]); 
            }

            for($decl=0; $decl < count($declarationIds); $decl++){
                $declType = DB::table('DECLARATIONS')->whereId($declarationIds[$decl])->get()->toArray();
                $declType_t = (array) current($declType);
                if($declType_t['type']=='SCHEME'){
                    //$declarationMetaData[$declType['blade_id']][1] = [];
                    $tmp = (array) current($declType);
                    $tmp['applicant'] = 1;
                    $tmp['data'] = [];
                    array_push($declarationMetaData, [(object) $tmp]);
                }else{
                    for($appl=1; $appl <= $applicantsCount; $appl++){
                        //$declarationMetaData[$declType['blade_id']][$appl] = [];
                        $tmp = (array) current($declType);
                        $tmp['applicant'] = $appl;
                        $tmp['data'] = [];
                        array_push($declarationMetaData, [(object) $tmp]);
                    } //EndFor
                }
            }

/*============================================End===========================================================*/

            if(count($customerOvdDetails) > 0)
            {
                $applicant = 0;

                // Processing name_mismatch and minor in same loop..
                $didInfo = DB::table('DECLARATIONS')->where('blade_id','name_mismatch')->get()->toArray();
                $minorInfo = DB::table('DECLARATIONS')->where('blade_id','minor')->get()->toArray();
                foreach ($customerOvdDetails as $customerData) {
                    $applicant++;
                    $customerData = (array) $customerData;
                    if(($customerData['pf_type'] == "pancard") && (isset($customerData['last_name'])||($accountDetailsforScheme['constitution']=="NON_IND_HUF" && isset($customerData['first_name']))) && $customerData['is_new_customer'] == '1') //name_mismtach allow only for NTB
                    {
                        $username = $customerData['first_name'].' '.$customerData['middle_name'].' '.$customerData['last_name'];
                        $namematchFlag = DB::table('PAN_DETAILS')
                                                                ->select('NAME_MATCH_FLAG')
                                                                ->where('FORM_ID',$formId)
                                                        ->where('PANNO',$customerData['pancard_no'])
                                                        ->orderBy('id','DESC')
                                                                ->pluck('name_match_flag')->toArray();
                        $namematchFlag = current($namematchFlag);
                        // if(str_replace(' ', '', strtoupper($username)) != str_replace(' ', '', strtoupper($nameOnPan)))
                        // if((str_replace(' ', '', strtoupper($username)) != str_replace(' ', '', strtoupper($nameOnPan))) && $nameOnPan != '')
                        if($namematchFlag == 'N')
                        {
                            $nameMismatch = true;
                            //array_push($declarationIds, 3);
                            if(($username == null) ||($username == false) ||($username == '')){
                                $username = '';
                            }
                            if(($nameOnPan == null) ||($nameOnPan == false) ||($nameOnPan == '')){
                                $nameOnPan = '';
                            }
                            $declarationExtraInfo[$applicant]['username'] = $username;
                            $declarationExtraInfo[$applicant]['nameOnPan'] = $nameOnPan;

                            $tmp = (array) current($didInfo);
                            $tmp['applicant'] = $applicant;
                            $tmp['data'] = ['username' => $username, 'nameOnPan' => $nameOnPan];
                            array_push($declarationMetaData, [(object) $tmp]);
                        }
                    }

                    if(($customerData['pf_type'] == "form60") && (isset($customerData['last_name'])) && $customerData['is_new_customer'] == '0'){
                        if (isset($customerData['is_new_customer']) && $customerData['pancard_no'] == '') {
                            $form60DidInfo = DB::table('DECLARATIONS')->where('blade_id','f60_ETB_wPAN')->get()->toArray();
                            $form60tmp = (array) current($form60DidInfo);
                            $form60tmp['applicant'] = $applicant;
                            $form60tmp['data'] = [];
                            array_push($declarationMetaData, [(object) $form60tmp]);
                        }
                    }


                    if(isset($customerData['dob']) && $customerData['dob']!='' && $customerData['dob'] != null){
                        $custAge = \Carbon\Carbon::parse($customerData['dob'])->age;
                        if(($custAge < 14) && (in_array($cust_Type['mode_of_operation'],[7,8]))){
                            $mnr = (array) current($minorInfo);
                            $mnr['applicant'] = $applicant;
                            $mnr['data'] = [];
                            array_push($declarationMetaData, [(object) $mnr]);
                        }
                    }

                } // EndFor
            }
           
        
            $checkPepApproval = DB::table('RISK_CLASSIFICATION_DETAILS')
                                    ->orderBy('applicant_sequence', 'ASC')
                                    ->where(['FORM_ID'=>$formId])
                                    ->get()->toArray();
            $didInfo = DB::table('DECLARATIONS')->where('blade_id','pep_approval')->get()->toArray();

            for($p=0; $p < count($checkPepApproval); $p++){

                if($customerData['is_new_customer'] == 0){
                    $url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
                    $ETBPepApproval = Api::customerdetails($url,'customerID',$customerData['customer_id']);
                    if($ETBPepApproval['data']['customerDetails']['FREE_TEXT_10'] == '4' || $ETBPepApproval['data']['customerDetails']['RISK_RATING'] == '4' ||$ETBPepApproval['data']['customerDetails']['OCCUPATION'] == 'POLITICIAN / PEPS'){
                        $tmp = (array) current($didInfo);
                        $tmp['applicant'] = $p+1;
                        $tmp['data'] = [];
                        array_push($declarationMetaData, [(object) $tmp]);
                    }
                }else{
                if($checkPepApproval[$p]->occupation==15 || $checkPepApproval[$p]->pep=='Yes'){
                    $tmp = (array) current($didInfo);
                    $tmp['applicant'] = $p+1;
                    $tmp['data'] = [];
                    array_push($declarationMetaData, [(object) $tmp]);

                }
            }
                
            }

            $checkVernacularApproval = DB::table('ACCOUNT_DETAILS')
                                        ->whereId($formId)
                                        ->where('SIGNATURE_TYPE','=',2)
                                        ->get()->toArray();
            $didInfo = DB::table('DECLARATIONS')->where('blade_id','vernacular')->get()->toArray();
            if(count($checkVernacularApproval) > 0)
            {
                $vernacularApproval = true;
                $tmp = (array) current($didInfo);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]);
            }
             // Commnented by Manikandan - 19/10/2022
            $checkLtiDeclaration = DB::table('ACCOUNT_DETAILS')
                                        ->whereId($formId)
                                        ->where('SIGNATURE_TYPE','=',3)
                                        ->get()->toArray();

            if(count($checkLtiDeclaration) > 0)
            {
                // $didInfo = DB::table('DECLARATIONS')->where('blade_id','lti_declaration')->get()->toArray();
                // $tmp = (array) current($didInfo);
                // $tmp['applicant'] = 1;
                // $tmp['data'] = [];
                // array_push($declarationMetaData, [(object) $tmp]);
                
                //if($nominee_exists == 'yes'){
                    $didInfo = DB::table('DECLARATIONS')->where('blade_id','lti_nom_wit')->get()->toArray();
                    $tmp = (array) current($didInfo);
                    $tmp['applicant'] = 1;
                    $tmp['data'] = [];
                    array_push($declarationMetaData, [(object) $tmp]);
                //}
             }

            $checkTreasuryApproval = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where('FORM_ID',$formId)
                                        ->where('TD_AMOUNT','>=','30000000')
                                        ->get()->toArray();

            if(count($checkTreasuryApproval) > 0)
            {
                $didInfo = DB::table('DECLARATIONS')->where('blade_id','treasury_approval')->get()->toArray();
                $tmp = (array) current($didInfo);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]);
            }

            $checkAnnexureApproval = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where(['FORM_ID'=>$formId,'EMD'=>1])
                                        ->get()->toArray();

            if(count($checkAnnexureApproval) > 0)
            {
                $didInfo = DB::table('DECLARATIONS')->where('blade_id','annexure_approval')->get()->toArray();
                $tmp = (array) current($didInfo);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]);
            }

            $checkThirdPartyApproval = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where(['FORM_ID'=>$formId,'SELF_THIRDPARTY'=>'thirdparty'])
                                        ->get()->toArray();

            if(count($checkThirdPartyApproval) > 0)
            {
                $didInfo = DB::table('DECLARATIONS')->where('blade_id','third_party_approval')->get()->toArray();
                $tmp = (array) current($didInfo);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]);
            }

            $checkCashApproval = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where(['FORM_ID'=>$formId,'OTHERS_TYPE'=>'cash'])
                                        ->get()->toArray();

            if(count($checkCashApproval) > 0)
            {
                $didInfo = DB::table('DECLARATIONS')->where('blade_id','cash_approval')->get()->toArray();
                $tmp = (array) current($didInfo);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]);
            }

            $checkZeroApproval = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where(['FORM_ID'=>$formId,'OTHERS_TYPE'=>'zero'])
                                        ->get()->toArray();

            $selectAccountType = $accountDetailsforScheme['account_type'];
            $selectSchemeCode = $accountDetailsforScheme['scheme_code'];
            $zeroApprovalRequired = true;

           
            
            if(count($checkZeroApproval) > 0 && $zeroApprovalRequired)
            {
                $didInfo = DB::table('DECLARATIONS')->where('blade_id','zero_approval')->get()->toArray();
                $tmp = (array) current($didInfo);
                $tmp['applicant'] = 1;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]);
            }
    

            // $checkCommunicationAddr = DB::table('CUSTOMER_OVD_DETAILS')
            //                             ->where(['FORM_ID'=>$formId])
            //                             ->get()->toArray();
            // $didInfo = DB::table('DECLARATIONS')->where('blade_id','commun_other_add')->get()->toArray();
            // for($c=0; $c < count($checkCommunicationAddr); $c++){
            //     if($checkCommunicationAddr[$c]->proof_of_current_address==29){
            //         $tmp = (array) current($didInfo);
            //         $tmp['applicant'] = $c+1;
            //         $tmp['data'] = [];
            //         array_push($declarationMetaData, [(object) $tmp]);
            //     }
            // }

            //$accountType = Session::get('accountType');
            $account_type = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','CONSTITUTION')
                                                        ->where('ID',$formId)
                                                        ->get()->toArray();
            $account_type =  (array) current($account_type);
            $accountType = $account_type['account_type'];


            if(isset($account_type["constitution"]) == "NON_IND_HUF"){ 
                $aof_img = DB::table('DECLARATIONS')->where('blade_id','huf_declaration_non_ind')->get()->toArray(); 
                $tmp = (array) current($aof_img);
                $tmp['applicant'] = 2;
                $tmp['data'] = [];
                array_push($declarationMetaData, [(object) $tmp]); 

                        
                $kartafull_name_fml = $customerOvdDetails[0]->first_name.' '. $customerOvdDetails[0]->middle_name.' '. $customerOvdDetails[0]->last_name;
                $huf_full_name_fml = $customerOvdDetails[1]->first_name;
                $kartafull_name_fml = preg_replace('/\s+/', ' ', $kartafull_name_fml);
                $kartafull_name_fml = trim($kartafull_name_fml);
                $huf_full_name_fml = preg_replace('/\s+/', ' ', $huf_full_name_fml);
                $huf_full_name_fml = trim($huf_full_name_fml);
                $huf_full_name_fml = trim(substr($huf_full_name_fml,0,-3));

                if(strtoupper($kartafull_name_fml) != strtoupper($huf_full_name_fml)){
                    $aof_img = DB::table('DECLARATIONS')->where('blade_id','huf_karta_namemismatch')->get()->toArray(); 
                    $tmp = (array) current($aof_img);
                    $tmp['applicant'] = 2;
                    $tmp['data'] = [];
                    array_push($declarationMetaData, [(object) $tmp]); 
            }
            }

            $gpaplan = CommonFunctions::getGpaPlan();
            $termautorenewal = config('constants.TERM_FOR_AUTO_RENEWAL');

            $customerOvd = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->get()->toArray();
            $customerOvd = (array) current($customerOvd);


            /* Before exclusion fix
            $checkdeclaration = array();

            foreach ($declarationIds as $declarationId) {
                $include = true;
                foreach($excl_declarationIds as $excludeId){
                    if($excludeId == $declarationId){
                        $include = false;
                    }
                }
                if($include){
                    $declarationIdDetail = DB::table('DECLARATIONS')->where('ID',$declarationId)->get()->toArray();
                    array_push($checkdeclaration, $declarationIdDetail);
                }
            }   */


            // If in_review/update mode fetch other declarations to show.
            if($scenario=='Update'){
                $otherDeclarationId = [10,13,14,15,16];
                $otherApprovals = DB::table('CUSTOMER_DECLARATIONS')
                                            ->where('FORM_ID',$formId)
                                            ->whereIn('DECLARATION_ID', $otherDeclarationId)
                                            ->get()->toArray();
                for($c=0; $c < count($otherApprovals); $c++){
                    $otherTemplate = DB::table('DECLARATIONS')
                        ->where('ID',$otherApprovals[$c]->declaration_id)
                        ->get()->toArray();
                    $tmp = (array) current($otherTemplate);
                    $tmp['applicant'] = 1;
                    $tmp['data'] = [];
                    $tmp['img'] = $otherApprovals[$c]->attachment;
                    array_push($declarationMetaData, [(object) $tmp]);
                }

            } // End Scenario Update

            // Get rid of excluded declarations
            if($selectAccountType == 1 || $selectAccountType == 4 || $selectAccountType == 2){
                /*if(in_array($selectSchemeCode,[3, 5, 8, 9, 13])){
                    $zeroApprovalRequired = false;
                }*/

                $cleanArray = array();
                for($d=0; $d<count($declarationMetaData); $d++) {
                    $currArray = current($declarationMetaData[$d]);
                    $include = true;
                    foreach($excl_declarationIds as $excludeId){
                        if($excludeId == $currArray->id){
                            $include = false;
                        }
                    }
                    if($include){
                        array_push($cleanArray, $declarationMetaData[$d]);
                    }
                }

                $declarationMetaData = $cleanArray;
            }

            $cardType = [];
            if ($accountDetailsforScheme['account_type'] != 3) { // not for TD account
                if($accountDetailsforScheme['delight_scheme'] == ''){
                $cardType = Rules::cardRuleBasedOnSchemeType($accountDetailsforScheme['account_type'],
                    $accountDetailsforScheme['scheme_code'],$accountDetailsforScheme['delight_scheme'] = false);
            }else{
                 $cardType = Rules::cardRuleBasedOnSchemeType($accountDetailsforScheme['account_type'],
                $accountDetailsforScheme['scheme_code'],$accountDetailsforScheme['delight_scheme'] = true);
            }
        }

            // Since default path includes FACTOR/bank, to remove bank .. added as prefix
            $imagePath = '..'.CommonFunctions::getImagePublicPath($formId);

            if($delightSavings)
            {   
                $accountDetailsInfo = DB::table('ACCOUNT_DETAILS') 
                                                        
                                                        ->where('ID',$formId)
                                                        ->get()->toArray();
                $accountDetailsInfo =  (array) current($accountDetailsInfo);

                $scheme_code_details = DB::table('SCHEME_CODES')
                                               ->where('ID',$accountDetailsInfo['scheme_code'])->get()->toArray();

                $scheme_code_details = (array) current($scheme_code_details);
                if(Session::get('is_review') == 1){
                    $delightKitStatus = 8;
                }else{
                    $delightKitStatus = 7;
                }
       
                if(isset($userDetails['DelightDetails']['sales_user_id']) && isset($userDetails['DelightDetails']['sales_user_id']) !=''){
                    $getuser_Id = $userDetails['DelightDetails']['sales_user_id'];
                }else{
                    $getuser_Id = Session::get('userId');

                }
              
                    $delightKits = DB::table('DELIGHT_KIT')
                                                    ->where('SALES_USER_ID',$getuser_Id)
                                                    ->where('SOL_ID',Session::get('branchId'))
                                                    ->where('SCHEME_CODE',$scheme_code_details['scheme_code'])
                                                    ->where('CUSTOMER_ID','!=',null)
                                                    ->where('ACCOUNT_NUMBER','!=',null)
                                                    ->where('STATUS', $delightKitStatus)
                                                    ->pluck('kit_number','id')->toArray();
                
                
                $delightDeclarationIds = [45, 46];
                foreach ($delightDeclarationIds as $delightDeclarationId) {
                $didInfo = DB::table('DECLARATIONS')->whereId($delightDeclarationId)->get()->toArray();
                        $tmp = (array) current($didInfo);
                        $tmp['applicant'] = 1;
                        $tmp['data'] = [];
                        array_push($declarationMetaData, [(object) $tmp]);
                }
            }else{
                $delightKits = array();
            }

            if (in_array($accountType, [1,2,4])) {
                $accountSchemeTable = 'SCHEME_CODES';
            }else{
                $accountSchemeTable = 'TD_SCHEME_CODES';
            }

            $schemeDetails = CommonFunctions::getSchemeDetails($formId);

            $sweeps_availability = 0;
            if (in_array($accountType, [1,2,4])) {
                $schemeDetailforSweeps = DB::table('SCHEME_CODES')->whereId($selectSchemeCode)->get()->toArray();
                $schemeDetailforSweeps = current($schemeDetailforSweeps);

                if ($schemeDetailforSweeps->sweeps_availability == 'Y') {
                    $sweeps_availability = 1;

                }
            }

            $addotherdeclarations = true;
            if($accountDetailsforScheme['source'] == 'CC'){
                if($is_cc_nri == 1){
                    $addotherdeclarations = false;
                }
            }

            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();

            $accountDetails = (array) current($accountDetails);

            return view('bank.declaration')->with('formId',$formId)
                                            ->with('accountType',$accountType)
                                            ->with('username',$username)
                                            ->with('nameOnPan',$nameOnPan)
                                            //->with('is_minor',$is_minor)
                                            ->with('nameMismatch',$nameMismatch)
                                            ->with('customerOvd',$customerOvd)
                                            ->with('cardType',$cardType)
                                            ->with('gpaplans',$gpaplan)
                                            ->with('nominee_exists',$nominee_exists)
                                            ->with('termautorenewals',$termautorenewal)
                                            ->with('userDetails',$userDetails)
                                            ->with('addotherdeclarations',$addotherdeclarations)
                                            ->with('reviewDetails',$reviewDetails)
                                            ->with('checkdeclaration',$declarationMetaData)
                                            ->with('declarationExtraInfo',$declarationExtraInfo)
                                            ->with('reviewSectionDetails',$reviewSectionDetails)
                                            ->with('declarationMetaData', $declarationMetaData)
                                            ->with('imagePath',$imagePath)
                                            ->with('delightKits',$delightKits)
                                            ->with('delightSavings', $delightSavings)
                                            ->with('accountDetailsforScheme', $accountDetailsforScheme)
                                             ->with('schemeDetails', $schemeDetails)
                                            ->with('sweeps_availability', $sweeps_availability)
                                            ->with('accountDetails', $accountDetails)
                                            ;

        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function markImages($formId)
    {
        try{
            //$folder = public_path('/uploads/attachments/'.$formId);
            $folder = storage_path('/uploads/attachments/'.$formId);
            // if(Session::get('is_review') == 1)
            // {
            //     $folder = public_path('/uploads/markedattachments/'.$formId);
            // }
            $accountType = Session::get('accountType');
            $isReview = Session::get('is_review');

            
            if(file_exists($folder)){
                $filesInFolder = \File::files($folder);
                foreach($filesInFolder as $path) {
                    $file = pathinfo($path);
                    $filename = $file['filename'].'.'.$file['extension'];
                    if($accountType == 2 && $isReview){
                        $checkClearance = DB::table('CLEARANCE')->where('FORM_ID',$formId)->where('CLEARANCE_IMG',$filename)->count();
                        if($checkClearance == 1){
                            continue;
                        }
                    }

                    if(strtolower($file['extension']) == 'pdf'){
                        $folder = storage_path('/uploads/markedattachments/'.$formId);
                        if (!File::exists($folder)) {
                            File::makeDirectory($folder, 0775, true, true);
                        }
                        $imageSaved = copy(storage_path('/uploads/attachments/'.$formId.'/'.$filename), storage_path('/uploads/markedattachments/'.$formId.'/'.$filename));
                        continue;
                    }

                    if(substr($filename,0,11) == "_DONOTSIGN_"){
                        $markImage = CommonFunctions::markImage($formId,$filename,"_STAMP_");
                    }else{
                        $markImage = CommonFunctions::markImage($formId,$filename,"_SIGN_");
                    }
                }
            }
           }
        catch(\Illuminate\Database\QueryException $e) {
              if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Bank/AddAccountController','markImages',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

  /*============= Below Code is for uploading and savings declarations =====*/
    public function applydigisign(Request $request)
    {
        try{
            $requestData = $request->get('data');

            $formId = $requestData['formId'];
            $delightDetails = array();
            $url = 'submission';
            $ok_without_declaration = false;
            $is_review = Session::get('is_review');

            // if(!isset($requestData['Declarations'])){
            //     $ok_without_declaration = true; //Rules::isNoDeclarationOK($formId);
            //     // Declaration is expected by array does not have value!
            //     if(!$ok_without_declaration){
            //         return json_encode(['status'=>'fail','msg'=>'Error! Declaration Missing','data'=>[]]);
            //     }
            //        else{
            //         // No declarations to save, directly mark available images
            //         Self::markImages($formId);
            //         DB::commit();
            //         return json_encode(['status'=>'success','msg'=>'Application details saved (No declarations available)','data'=>['url'=>$url]]);
            //     }

            // }

            // Regular routine if there are any Delcarations to be saved
            $updateProof= 1;
            if(!isset($requestData['Declarations']['Proofs'])){
                $requestData['Declarations']['Proofs'] = [];
                // return json_encode(['status'=>'fail','msg'=>'Error! Declaration Missing','data'=>[]]);
            }
            $proofs = $requestData['Declarations']['Proofs'];
            $declarations = Arr::except($requestData['Declarations'],['Proofs']);

            $accountType = Session::get('accountType');
            $getccaccountType = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','SOURCE','SCHEME_CODE')->whereId($requestData['formId'])->get()->toArray();
            $getccaccountType = (array) current($getccaccountType);

            if($accountType == ''){
                $accountType = $getccaccountType['account_type'];
            }

            if($accountType != 3){

                $services = $requestData['AccountDetails'];
            }else{
                $services = [
                    'gpa_required' => 0,
                    'two_way_sweep' => 0,
                    'card_type' => '',
                    'gpaplan' => '',
                    'auto_renew_gpa' => '',
                    'termautorenewal' => '',
                            ];
                $requestData['AccountDetails'] = $services;
            }

          //CARD REQUIRED PAN 
            $cardId = isset($services['card_type']) && $services['card_type'] != ''?$services['card_type']:'';
        
            if($cardId != ''){
                $getCardDetails = DB::table('CARD_RULES')->whereId($cardId)
                                                        ->where('IS_ACTIVE',1)
                                                        ->where('PAN_REQUIRED','Y')
                                                        ->count();
                         
                if($getCardDetails>0){
                    $getPanType = DB::table('CUSTOMER_OVD_DETAILS')->select('PF_TYPE')
                                                                  ->where('FORM_ID',$requestData['formId'])
                                                                  ->get()
                                                                  ->toArray();
                    // $getPanType = (array) current($getPanType);
                    for($custSeq = 0;count($getPanType)>$custSeq;$custSeq++){

                        if($getPanType[$custSeq]->pf_type != 'pancard'){
                        return json_encode(['status'=>'fail','msg'=>'Selected card type required PAN document.','data'=>[]]);
                    }
                }
            }
            }
            
            /*if(isset($requestData['dynaText'])){
                $dynaText = json_encode($requestData['dynaText']);
            }else{
                $dynaText = '';
            }*/
            //Begins db transaction
            DB::beginTransaction();

            if((isset($requestData['DelightDetails'])) && ($requestData['DelightDetails'] != ''))
            {
                $delightDetails = $requestData['DelightDetails'];
                $delightKitDetails = DB::table('DELIGHT_KIT')->whereId($delightDetails['kit_number'])
                                                        ->get()->toArray();
                $delightKitDetails =  current($delightKitDetails);
                if($delightKitDetails->account_number != $delightDetails['account_number']){
                     return json_encode(['status'=>'fail','msg'=>'Please validate account number.','data'=>[]]);
                }
                $updateKitId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->update(['DELIGHT_KIT_ID'=>$delightDetails['kit_number']]);
            }

            if(isset($services))
            {
                foreach ($services as $service=>$value)
                {
                    $updateServices = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                    ->update([$service=>$value]);
                }
            }

            if(isset($requestData['DelightDetails']))
            {
                $delightDetails = $requestData['DelightDetails'];
                $updateKitId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->update(['DELIGHT_KIT_ID'=>$delightDetails['kit_number']]);
            }
            if(isset($declarations)){
                foreach($declarations as $declaration => $value)
                {
                    $applicant= '';
                    $blade_id = explode('-',$declaration)[0];
                    if(isset(explode('-',$declaration)[1]))
                    {
                        $applicant = explode('-',$declaration)[1];
                    }
                    $insertArray = array();
                    
                    if(isset($requestData['dynaText'][$applicant])){
                        $dynaText = json_encode($requestData['dynaText'][$applicant]);
                    }else{
                        $dynaText = '';
                    }
                    // exit;
                    $nri_date = isset($requestData['nri_date']) && $requestData['nri_date'] != ''?'Y':'N';
                    if(Session::get('role') == 11 && $nri_date == 'Y'){
                        // validation for date check previous and features date restricted.
                        $selectedDate = Carbon::parse($requestData['nri_date']);
                        $getValidDate =  Carbon::now()->subDays(10);
                        $currDate =  Carbon::now();
                        
                        if(($getValidDate >= $selectedDate ) || ($currDate <= $selectedDate)){

                            return json_encode(['status'=>'fail','msg'=>'Please Selected Valid date.','data'=>[]]);
                    }

                        $dynaText = json_encode(array('nri_date'=>$requestData['nri_date']));
                    }

                    

                    
                    if($value != 0 )
                    {
                        $other = substr( $blade_id, 0, 5 );

                        $insertArray['FORM_ID'] = $formId;
                        $insertArray['DECLARATION_TYPE'] = $blade_id;
                        $insertArray['ATTACHMENT'] = $proofs[$declaration.'_proof'];
                        $insertArray['CREATED_BY'] = Session::get('userId');
                        $insertArray['DYNA_TEXT'] = $dynaText;
                        $insertArray['APPLICANT_SEQUENCE'] = $applicant;

                        $dId =  DB::table('DECLARATIONS')
                                          ->select('ID')
                                          ->where('BLADE_ID',$blade_id)
                                          ->get()->toArray();

                        $dId = (array) current($dId);

                        $insertArray['DECLARATION_ID'] = $dId['id'];

                        $existingId =  DB::table('CUSTOMER_DECLARATIONS')
                                          ->select('ID')
                                          ->where('FORM_ID',$formId)
                                          ->where('DECLARATION_TYPE',$blade_id)
                                          ->where('APPLICANT_SEQUENCE',$applicant)
                                          ->get()->toArray();

                        if (($other == 'other') && $is_review == 1) {
                            $existingDeclaration =  DB::table('REVIEW_TABLE')
                                          ->where('FORM_ID',$formId)
                                          ->where('COLUMN_NAME', $blade_id.'_proof-'.$applicant)
                                          ->orderBy('ID', 'DESC')
                                          ->get()->toArray();

                            $iterationArray =  DB::table('REVIEW_TABLE')
                                          ->where('FORM_ID',$formId)
                                          ->orderBy('ID', 'DESC')
                                          ->get()->toArray();

                            if (count($iterationArray) > 0) {
                                $iteration = current($iterationArray);
                                $roleId = $iteration->role_id;
                                $branchId = $iteration->branch_id;
                                $iteration = $iteration->iteration;
                            }else{
                                $iteration = 1;
                                $branchId = '';
                            }
                            if (count($existingDeclaration) == 0) {
                                $otherDeclaration = ['form_id'=> $formId,
                                                    'column_name'=> $blade_id.'_proof-'.$applicant,
                                                    'comments'=> 'Auto added: extra declaration by Branch',
                                                    'status'=> 0,
                                                    'created_by'=> Session::get('userId'),
                                                    'updated_by'=> Session::get('userId'),
                                                    'section'=> 'declarations',
                                                    'role_id'=> $roleId,
                                                    'iteration'=> $iteration,
                                                    'branch_id'=> $branchId];

                                $saveotherDeclarations = DB::table('REVIEW_TABLE')->insert($otherDeclaration);
                                if (!$saveotherDeclarations) {
                                    return json_encode(['status'=>'fail','msg'=>'Error! Unable to add other declaration','data'=>[]]);
                                }                    
                            }

                        }

                        if(count($existingId) > 0){

                            $saveDeclarations = DB::table('CUSTOMER_DECLARATIONS')->whereId($existingId[0]->id)->update($insertArray);
                        }else{
                            $saveDeclarations = DB::table('CUSTOMER_DECLARATIONS')->insert($insertArray);
                        }
                    }else{
                    	if($getccaccountType['account_type'] == 3){
                        	$table = 'TD_SCHEME_CODES';
                        }else{
                        	$table = 'SCHEME_CODES';
                        }

                        $getSchemeData = DB::table($table)->select('SCHEME_CODE')->whereId($getccaccountType['scheme_code'])->get()->toArray();
                        $getSchemeData = (array) current($getSchemeData);

                        $checkMandatory = DB::table('SCHEME_DECLARATION_MAPPING')->where('SCHEME_CODE',$getSchemeData['scheme_code'])->whereNull('IS_MANDATORY')->get()->toArray();

                    	if(count($checkMandatory) > 0){
	                		return json_encode(['status'=>'fail','msg'=>'Instance found of mandatory declaration not uploaded ('.$declaration.'). Please check and retry.','data'=>[]]);
                    	}
	                        
                    }
                }
            }

            // if(isset($declarations) && ($is_review == 1)){
            //     foreach($declarations as $declaration => $value)
            //     {

            //         $applicant= '';
            //         $blade_id = explode('-',$declaration)[0];
            //         $other = substr( $blade_id, 0, 5 );
            //         if ($other == 'other') {
                        

            //             if(isset(explode('-',$declaration)[1]))
            //             {
            //                 $applicant = explode('-',$declaration)[1];
            //             }
            //             $insertArray = array();
            //             if($value != 0)
            //             {
            //                 $insertArray['FORM_ID'] = $formId;
            //                 $insertArray['DECLARATION_TYPE'] = $blade_id;
            //                 $insertArray['ATTACHMENT'] = $proofs[$declaration.'_proof'];
            //                 $insertArray['CREATED_BY'] = Session::get('userId');
            //                 $insertArray['DYNA_TEXT'] = $dynaText;
            //                 $insertArray['APPLICANT_SEQUENCE'] = $applicant;

            //                 $dId =  DB::table('DECLARATIONS')
            //                                   ->select('ID')
            //                                   ->where('BLADE_ID',$blade_id)
            //                                   ->get()->toArray();

            //                 $dId = (array) current($dId);

            //                 $insertArray['DECLARATION_ID'] = $dId['id'];

            //                 $existingId =  DB::table('CUSTOMER_DECLARATIONS')
            //                                   ->select('ID')
            //                                   ->where('FORM_ID',$formId)
            //                                   ->where('DECLARATION_TYPE',$blade_id)
            //                                   ->where('APPLICANT_SEQUENCE',$applicant)
            //                                   ->get()->toArray();

                                              
            //                 if(count($existingId) > 0){

            //                     $saveDeclarations = DB::table('CUSTOMER_DECLARATIONS')->whereId($existingId[0]->id)->update($insertArray);
            //                 }else{
            //                     $saveDeclarations = DB::table('CUSTOMER_DECLARATIONS')->insert($insertArray);
            //                 }
            //             }else{
            //               return json_encode(['status'=>'fail','msg'=>'Instance found of mandatory declaration not uploaded. Please check and retry.','data'=>[]]);
            //             }
            //         }
            //     }
            // }

            if(Session::get('screen') >= 6)
            {

            }else{
                $updateScreen = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['SCREEN'=>6]);
            }

            $declarations = array_merge($declarations,$proofs);
            if(isset($proofs)){
                foreach($proofs as $proofType=>$proof)
                {
                    $proofs = explode(',', $proof);
                    // echo "<pre>";print_r($proofs);exit;
                    foreach($proofs as $image){
                        $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$image);
                        // echo "<pre>";print_r($oldFilePath);exit;
                        $folder = storage_path('/uploads/attachments/'.$formId);
                        //define new file path
                        $filePath = $folder.'/'.$image;
                        if (!File::exists($folder)) {
                            File::makeDirectory($folder, 0775, true, true);
                        }
                        //checks file exists or not in temp folder
                        if(file_exists($oldFilePath)){
                            //move file from temp folder to upload folder
                            if (File::move($oldFilePath, $filePath)){

                            }else{
                                //make it false if any file didn't uploaded
                                $is_uploaded = false;
                            }
                        }
                    }
                }
            }

// echo "test";exit;
            Self::markImages($formId);

            if(isset(Session::get('UserDetails')[$formId]['Declarations'])){
                $userArray[$formId]['Declarations'] = array_replace_recursive(Session::get('UserDetails')[$formId]['Declarations'], $declarations);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['Declarations'] = $declarations;
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            if(isset(Session::get('UserDetails')[$formId]['DelightDetails'])){
                $userArray[$formId]['DelightDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['DelightDetails'], $delightDetails);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['DelightDetails'] = $delightDetails;
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            if(isset(Session::get('UserDetails')[$formId]['AccountDetails'])){
                $userArray[$formId]['AccountDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountDetails'],$requestData['AccountDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['AccountDetails'] = $requestData['AccountDetails'];
                $userDetails = $userArray;
            }

            //store user financial details into session based on formId
            Session::put('UserDetails',$userDetails);
            if(Session::get('is_review') == 1){
                $url = $this->discrepencyresponse($formId,'step-6');
            }
            if((Session::get('in_progress') == 1) && (Session::get('screen') < 6))
            {
                Session::put('screen',6);
            }
            if($updateProof){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Application details saved','data'=>['url'=>$url]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Data Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
              if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
                $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Exception Error! Please try again','data'=>[]]);
        }
    }

    public function submission(Request $request)
    {
        try{
            $tokenParams = Cookie::get('token');
            $encrypt_key = substr($tokenParams, -5);
            $declarations = array();
            $reviewSectionDetails = array();
            $customer_type = '';
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $formId = base64_decode($decodedString);
            //get form details
            $formDetailsArray = CommonFunctions::getFormDetails($formId);
            $segmentList = CommonFunctions::getSegment();

            //fetch accounttype
            $accountType = Session::get('accountType');
            if((Session::get('is_review') == 1) || (Session::get('in_progress') == 1)){
                $reviewSectionDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->distinct()->pluck('id','section')->toArray();
                $declarationDetails = DB::table('ACCOUNT_DETAILS')->select('declarations')
                                            ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
                $declarations = (array) current($declarationDetails);
            }
            if($formDetailsArray['accountDetails']['is_new_customer'] == 0)
            {
                $customer_type = "ETB";
            }
            $no_of_account_holders = $formDetailsArray['accountDetails']['no_of_account_holders'];
            // if((Session::get('customer_type') == "ETB") && ($initial_funding_type == 3))

            $currUrlPath = parse_url(\Request::getRequestUri(), PHP_URL_PATH);
            $pathComponents = explode("/", trim($currUrlPath, "/"));
            $appName = $pathComponents[0];
            $imgPublicPath = '/'.$appName.CommonFunctions::getImagePublicPath($formId);
            $cifDeclarationDetails = DB::table('SUBMISSION_DECLARATION_FIELDS')->where('FORM_ID',$formId)->get()->toArray();
            $cifDeclarationDetails = current($cifDeclarationDetails);
            $schemeDetails = CommonFunctions::getSchemeDetails($formId);

            $sweeps_availability = 0;
            if (in_array($accountType, [1,4,2])) {
                $table = 'SCHEME_CODES';
                if($accountType == 2){
                    $table = 'CA_SCHEME_CODES';
                }
                $schemeDetailforSweeps = DB::table($table)->where('SCHEME_CODE',$formDetailsArray['accountDetails']['scheme_code'])->get()->toArray();
                $schemeDetailforSweeps = current($schemeDetailforSweeps);

                if ($schemeDetailforSweeps->sweeps_availability == 'Y') {
                    $sweeps_availability = 1;

                }
            }
 
            $l3DeclarationsImage = [];

            $callCenterEmailImage = DB::table('CUSTOMER_DECLARATIONS')->select('DYNA_TEXT')
                                                                    ->where('FORM_ID',$formId)
                                                                    //  ->where('DECLARATION_ID',144)
                                                                    ->whereIn('DECLARATION_ID',[53,142])
                                                                         ->get()->toArray();
            if(count($callCenterEmailImage) >0){

                $callCenterEmailImage  = (array) current($callCenterEmailImage);                   
            }

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

            //ekyc photo 12_08_2024 
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
            // $enc_fields = ['Aadhaar Photocopy','Passport','Voter ID','Driving Licence'];
            $enc_fields = [1,2,3,6,7];
            foreach ($formDetailsArray['customerOvdDetails'] as $key => $value) {
                $formDetailsArray['customerOvdDetails'][$key]->email = CommonFunctions::encrypt256($value->email,$encrypt_key);
                $formDetailsArray['customerOvdDetails'][$key]->mobile_number = CommonFunctions::encrypt256($value->mobile_number,$encrypt_key);
                if(isset($value->pf_type)=="pancard"){
                    $formDetailsArray['customerOvdDetails'][$key]->pancard_no = CommonFunctions::encrypt256($value->pancard_no,$encrypt_key);
                }
                if(in_array($value->proof_of_identity ,$enc_fields)){
                    $formDetailsArray['customerOvdDetails'][$key]->id_proof_card_number = CommonFunctions::encrypt256($value->id_proof_card_number,$encrypt_key); 
                }
                if(in_array($value->proof_of_address ,$enc_fields)){
                    $formDetailsArray['customerOvdDetails'][$key]->add_proof_card_number = CommonFunctions::encrypt256($value->add_proof_card_number,$encrypt_key); 
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

            

            return view('bank.submission')->with('formId',$formId)
                                            ->with('accountDetails',$formDetailsArray['accountDetails'])
                                            ->with('accountType',$accountType)
                                            ->with('callCenterDeclaration',$callCenterDeclaration)
                                            ->with('entityL1Images',$entityL1Images)
                                            ->with('declarations',$declarations)
                                            ->with('segmentList',$segmentList)
                                            ->with('declarationsList',$formDetailsArray['declarationsList'])
                                            ->with('nomineeDetails',$formDetailsArray['nomineeDetails'])
                                            ->with('delightDetails',$formDetailsArray['delightDetails'])
                                            ->with('customerOvdDetails',$formDetailsArray['customerOvdDetails'])
                                            ->with('entityDetails',$formDetailsArray['entityDetails'])
                                            ->with('userDetails',$formDetailsArray['userDetails'])
                                            ->with('files',$formDetailsArray['files'])
                                            ->with('no_of_account_holders',$no_of_account_holders)
                                            ->with('customer_type',$customer_type)
                                            ->with('is_aof_tracker',false)
                                            ->with('l3DeclarationsImage',$l3DeclarationsImage)
                                            ->with('username',Session::get('username'))
                                            ->with('reviewSectionDetails',$reviewSectionDetails)
                                            ->with('imgPublicPath',$imgPublicPath)
                                            ->with('cifDeclarationDetails', $cifDeclarationDetails)
                                            ->with('callCenterEmailImage', $callCenterEmailImage)
                                            ->with('schemeDetails', $schemeDetails)
                                            ->with('sweeps_availability', $sweeps_availability)
                                            ->with('tdSchemeCodecc',$tdSchemeCodecc)
                                            // ->with('entityDetails_huf',$formDetailsArray['entityDetails_huf'])
                                            ->with("huf_cop_row",$huf_cop_row)
                                            ;
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    /*
    *  Method Name: deleteimage
    *  Created By : Sharanya T
    *  Created At : 12-03-2020
    *
    *  Description:
    *  Method to delete iamge file in folder
    *
    *  Params:
    *  @$filename
    *
    *  Output:
    *  Returns Json.
    */
    public function deleteimage(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $deleteFlag = false;
                $responseString = '';
                $isReview = Session::get('is_review');
                $role = Session::get('role');
                $userId = Session::get('userId');
                $current_timestamp = Carbon::now()->format('d-m-Y_His');
                $folder = "temp";
                //$filename = public_path().'/uploads/temp/'.$requestData['imageName'];
                $filename = storage_path('/uploads/temp/'.$requestData['imageName']);
                if(file_exists($filename)){

                     $delete = \File::delete($filename);
                     $deleteFlag = true;
                     $responseString .= 'TEMP_Deleted,';
                }

                $formId = Session::get('formId');
                if(isset($requestData['form_id']) && $requestData['form_id'] != ''){
                   $formId = $requestData['form_id'];
                }
                
                // if((Session::get('in_progress') == 1) || (Session::get('is_review') == 1)){
                //     $formId = Session::get('reviewId');
                // }else{
                // }
                /*===============Deleting Un-Signed Attachment=====================*/
                //$folder = "attachments";
                //$filenamePath = public_path().'/uploads/attachments/'.$formId;
                $filenamePath = storage_path('/uploads/attachments/'.$formId);

                //$filename = public_path().'/uploads/attachments/'.$formId.'/'.$requestData['imageName'];
                $filename = storage_path('/uploads/attachments/'.$formId.'/'.$requestData['imageName']);
                if(file_exists($filename)){

                    $delete = \File::delete($filename);

                    $deleteFlag = true;
                    $responseString .= 'ATTACH_Renamed,';
                }
                /*===============Deleting Signed Attachment=====================*/
                //$filenamePath = public_path().'/uploads/markedattachments/'.$formId;
                $filenamePath = storage_path('/uploads/markedattachments/'.$formId);

                //$filename = public_path().'/uploads/markedattachments/'.$formId.'/'.$requestData['imageName'];
                $filename = storage_path('/uploads/markedattachments/'.$formId.'/'.$requestData['imageName']);


                if(file_exists($filename)){
                    if($isReview == 1){

                        if (!File::exists($filenamePath.'/deleted')) {
                            File::makeDirectory($filenamePath.'/deleted', 0775, true, true);
                        }

                        $delete = \File::move($filename, $filenamePath.'/deleted/'.$requestData['imageName'].'_'.$current_timestamp.substr($requestData['imageName'], -4));
                    }else{

                        $delete = \File::delete($filename);
                    }
                    $deleteFlag = true;
                    $responseString .= 'MARKED_Renamed';
                }

                $updateFlag = false;
                if($deleteFlag){
                        $imgData = CommonFunctions::getValidImageNames($formId);
                        $imgData = $imgData[2];
                        $imgDeleted = str_replace('_DONOTSIGN_', '', $requestData['imageName']);
                        foreach($imgData as $img => $location){
                            $img = str_replace('_DONOTSIGN_', '', $img);
                            if($img == $imgDeleted){
                                $updateFlag = Self::removeImageFromTable($img, $location, $formId);
                            }
                        }
                }
           
                if ($deleteFlag) {
                 $createUserLog = CommonFunctions::createImageDeleteLog($request,$userId,true,'',$requestData['imageName']);
                }else{
                 $createUserLog = CommonFunctions::createImageDeleteLog($request,$userId,false,'',$requestData['imageName']);
                }

                 if(substr($requestData['image_div'],-14) == '_clearance_div' || substr($requestData['image_div'],-8) == '_img_div'){
                    if(substr($requestData['image_div'],-14) == '_clearance_div'){
                        $blade_id = substr($requestData['image_div'], 0, -14);
                    }else{
                        $blade_id = substr($requestData['image_div'], 0, -8);
                    }
                       $formId = $requestData['form_id'];
                       $clearance_id = substr($requestData['image_div'], 0, -14);
                       $clearance_details = DB::table('CLEARANCE_MASTER')->where('BLADE_ID', $blade_id)
                                                                          ->get()->toArray();
                        $clearance_details = (array) current($clearance_details);
                        $update_clearance =  DB::table('CLEARANCE')
                                              ->where('FORM_ID',$formId)
                                              ->where('CLEARANCE_ID', $clearance_details['id'])
                                              ->update(["UPDATED_BY"=> Session::get('userId'),
                                                        "UPDATED_AT"=> Carbon::now(),
                                                        "ACTIVE" => 0,
                                                        "CLEARANCE_IMG" => NULL
                                                         ]);
                    }

                $messgaeNameType = 'Image';
                if(substr(strtolower($requestData['imageName']),-3) == 'pdf'){
                    $messgaeNameType = 'PDF';
                }
                if($deleteFlag){
                    if($updateFlag){
                        return json_encode(['status'=>'success','msg'=>$messgaeNameType.' deleted and records updated','data'=>['response'=>$responseString]]);
                    }else{
                        return json_encode(['status'=>'success','msg'=>$messgaeNameType.' deleted','data'=>['response'=>$responseString]]);
                    }
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

    public function removeImageFromTable($img, $location, $formId){     
        
        $tblFld = explode('@', $location);
       
        $tableName = $tblFld[0];
        $fieldName = $tblFld[1];
        
        $id_form = $tableName == 'account_details' ? 'ID' : 'FORM_ID';

        $recordExist = DB::table($tableName)->where($id_form, $formId)
                        ->whereRaw(''.$fieldName.' like (?)',$img)
                        ->get()->toArray();     
                
        if(count($recordExist)>0){
            $recordExist = current($recordExist);
        }else{
            return false;
        }
        
        $tableFieldString = $recordExist->$fieldName;
        $tableFieldId = $recordExist->id;
        
        $strToUpdate = str_replace('_DONOTSIGN_'.$img, '', $tableFieldString);
        $strToUpdate = str_replace($img, '', $tableFieldString);
        $strToUpdate = str_replace(',', '', $strToUpdate);

        $recordExist = DB::table($tableName)->where($id_form, $formId)->where('ID', $tableFieldId)
                            ->update([$fieldName => $strToUpdate]);
        DB::commit();
    
        return true;

    }

    public function getaddressdatabypincode(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $addressData = CommonFunctions::getAddressDataByPincode($requestData['pincode']);
                if(count($addressData) > 0){
                    $addressData = (array) current($addressData);
                    return json_encode(['status'=>'success','msg'=>'AddressData Details','data'=>$addressData]);
                }else{
                    $msg = 'Error! Pincode not found in registered database. Please contact NPC admin';
                    return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                }
            }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function submittonpc(Request $request)
    {
        try{
            if ($request->ajax()){
                $declarations = '';
                $isDeclarationsEmpty = 0;
                $msg = 'Form Submitted to NPC';
                $dedupe_check = env('DEDUPE_CHECK');
                $userId =  Session::get('userId');
                //fetch data from request
                $requestData = $request->get('data');               
                $branchId= isset($requestData['branch_id'])&&$requestData['branch_id']!=''?$requestData['branch_id']:'';             

                $precheckstatus = Rules::precheckbranchsubmission($requestData);

                if(isset($precheckstatus['status']) && $precheckstatus['status'] == 'fail'){
                    return json_encode(['status'=>$precheckstatus['status'],'msg'=>$precheckstatus['msg'],'data'=>[]]);
                }
           
                $checkbranchActive = CommonFunctions::checkbranchActive($branchId);
                if(isset($checkbranchActive)){
                    return json_encode(['status'=>$checkbranchActive['status'],'msg'=>$checkbranchActive['message'],'data'=>[]]);
                }
               
                // if(env('APP_SETUP') != 'DEV'){
                if((env('APP_SETUP') == 'UAT') || (env('APP_SETUP') == 'PRODUCTION')){
                    $authCheck = AuthenticationController::authenticate($requestData['password']);
                    if($authCheck == false){
                        return json_encode(['status'=>'fail','msg'=>'Authentication Failed! Please try again','data'=>[]]);
                    }
                }

                $updateArray = Arr::except($request->get('data'),['formId','functionName','user_id','password','reason_for_accnt_opening','lead_generated','dist_from_branch','meeting_date','customer_meeting_location']);

                DB::beginTransaction();
                $role = Session::get('role');
                $formId = $requestData['formId'];
                
                $userrole = Session::get('role');
                
                $cust_Type = DB::table('ACCOUNT_DETAILS')->select('IS_NEW_CUSTOMER','ACCOUNT_TYPE','SOURCE')->where('ID',$requestData['formId'])->get()->toArray();

                $cust_Type = (array) current($cust_Type);
                $accountType = $cust_Type['account_type'];

                if($cust_Type['is_new_customer'] == 0){
                   $customerType = 'ETB';
                }else{
                   $customerType = 'NTB';
                }

                if($userrole == 11){

                    $checkSolId = DB::table('USERS')
                                ->where('EMPSOL',$requestData['branch_id'])
                                ->get()->toArray();   
                    if(count($checkSolId) == 0){
                         return json_encode(['status'=>'fail','msg'=>'Please add valid sol Id','data'=>$checkSolId]);
                    }
                }

                if(isset($requestData['Declarations']))
                {
                    $declarations = implode(',',$requestData['Declarations']);
                }

                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                            ->whereId($requestData['formId'])
                                            ->pluck('aof_number')->toArray();
                $aof_number = current($accountDetails);
              
                
                if(Session::get('is_review') != 1){
                    $branchDetails =  DB::table('BRANCH')
                                            ->where('BRANCH_ID',$requestData['branch_id'])
                                            ->get()->toArray();
                    $globalCcdetails = Session::get('global_cc_data');

                    if(isset($globalCcdetails['etb_cc']) && $globalCcdetails['etb_cc'] == 'CC'){

                        $segmentCode = '';
                    }else{
                        $segmentCode = $requestData['segment_code'];
                    }

                    $updateBranchSegment = ['BRANCH_ID'=>$requestData['branch_id'],
                                            'SEGMENT_CODE'=>$segmentCode,
                                            ];

            
                    if(count($branchDetails) > 0){
                        $branchUpdate = DB::table('ACCOUNT_DETAILS')
                                            ->whereId($requestData['formId'])
                                            ->update($updateBranchSegment);
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'Please Enter Valid BRANCH ID','data'=>[]]);
                    }

                    $declarationFields = DB::table('SUBMISSION_DECLARATION_FIELDS')
                                            ->where('form_id', $requestData['formId'])
                                            ->get()->toArray();

                      

        
                    $etbCC = Session::get('global_cc_data');
                    if( $etbCC == '' && $accountType != 3 && isset($requestData['reason_for_accnt_opening'])){
                           $submissionDeclarationFields = ['FORM_ID' => $requestData['formId'],
                                            'ACCOUNT_OPEN_REASON' => $requestData['reason_for_accnt_opening'],
                                            'GENERATED_LEAD' => $requestData['lead_generated'],
                                            'DIST_FROM_BRANCH' => $requestData['dist_from_branch'],
                                            'MEETING_DATE' =>  Carbon::parse($requestData['meeting_date']),
                                            'CUSTOMER_LOCATION' => $requestData['customer_meeting_location'],
                                            'CREATED_BY' => $userId,
                                            ];
                    
                    }else{
                        $submissionDeclarationFields = array();
                    }

                    if (count($declarationFields) > 0) {

                    }else{
                        $submitDeclarationFields = DB::table('SUBMISSION_DECLARATION_FIELDS')->insert($submissionDeclarationFields);

                        DB::commit();
                    }

                }else{
                    $updateReviewStatus = DB::table('REVIEW_TABLE')->where(['FORM_ID'=> $formId, 'STATUS'=> '0'])
                                                                    ->update(['STATUS'=> '1' ]);
                }
                
                $formDetails = CommonFunctions::getFormDetails($formId);

                // if(Session::get('is_review') != 1){
                    //     if (($formDetails['accountDetails']['delight_scheme'] == 5) && ($formDetails['accountDetails']['account_type'] == "Savings")) {
                        //         $delightSavings = true;
                    //     }else{
                        //         $delightSavings = false;
                    //     }

                    //     if ($delightSavings) {
                        //         if ($formDetails['accountDetails']['delight_kit_id'] != '') {
                            //             $updateKitUtilized = DB::table('DELIGHT_KIT')->whereId($formDetails['accountDetails']['delight_kit_id'])
                                                                    //                                                     ->update(['status'=> 8]);
                        //         }
                //     }
                // }

                $formDetail = (array) current($formDetails['customerOvdDetails']);

                if($formDetail['initial_funding_type'] == 2){

                    $checkUTR = CommonFunctions::checkUniqueUtr($formDetail['reference'], $formId);
                    if($checkUTR['status'] == 'true'){
                        return json_encode(['status'=>'fail','msg'=>$checkUTR['msg'],'data'=>[]]);
                    }
                }

                
                //code create rajat 
                //modifer by nilesh
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                                                ->where('FORM_ID',$formId)
                                                                ->get()->toArray();
                // if(($customerOvdDetails[0]->proof_of_identity == 1) || ($customerOvdDetails[0]->proof_of_identity == 1)){
                //     if(count($customerOvdDetails) > 0){
                // //     foreach ($customerOvdDetails as $customerData){
                //         for($i=0;count($customerOvdDetails)>$i;$i++){
                            
                //             if($customerOvdDetails[$i]->is_new_customer == '1'){

                //                 $existingRecordDuplication=DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID','!=',$formId)
                //                                                                             ->where('CREATED_AT','>=',Carbon::now()->subDays(7))
                //                                                                             ->where('FIRST_NAME',$customerOvdDetails[$i]->first_name)
                //                                                                             ->where('LAST_NAME',$customerOvdDetails[$i]->last_name)
                //                                                                             ->where('EMAIL',$customerOvdDetails[$i]->email)
                //                                                                             ->where('MOBILE_NUMBER',$customerOvdDetails[$i]->mobile_number)
                //                                                                             ->count();
                            
                //                 $full_name=$customerOvdDetails[$i]->first_name.' '.$customerOvdDetails[$i]->last_name;
                                
                //                 if($existingRecordDuplication>=1){
                //                   //  return json_encode(['status'=>'success','msg'=>$full_name.' details exists in another form please check. ','data'=>[]] );      
                //                 }
                //             }
                //         }
                //     }

                if(count($customerOvdDetails) > 0)
                {
                    foreach ($customerOvdDetails as $customerData)
                    {
                        $customerData = (array) $customerData;
                        $id = $customerData['id'];
                        $customerId = '001189092';
                        $typeProof = '';
                        if($customerData['proof_of_identity'] == 1 || $customerData['proof_of_address'] == 1){
                            if($customerData['proof_of_identity'] == 1){
                                $aadharNumber = str_replace('-','',$customerData['id_proof_card_number']);
                                $typeProof = 'proofId';
                            }
                            if($customerData['proof_of_address'] == 1){
                                $aadharNumber = str_replace('-','',$customerData['add_proof_card_number']);
                                $typeProof = 'proofAdd';
                            }

                            if(($customerData['proof_of_identity'] == 1) && ($customerData['proof_of_address'] == 1)){
                                $typeProof = 'both';
                            }
                            
                            $aadharReferenceDetails = Self::vaultenumberupdate($customerId,$aadharNumber,$formId,$typeProof,$id);

                            if(!isset($aadharReferenceDetails['status']) || (isset($aadharReferenceDetails['status']) && $aadharReferenceDetails['status'] == 'fail')){
                                return json_encode(['status'=>'fail','msg'=>'Aadhaar Vault/Reference API failed','data'=>[$aadharReferenceDetails]] );                            
                            }
                        }                        
                    }
                }

                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$requestData['formId'])
                                            ->get()->toArray();
                if(count($customerOvdDetails) > 0)
                {
                    foreach($customerOvdDetails as $customerOvdData)
                    {
                        $customerOvdData = (array) $customerOvdData;
                        // $deDupeStatus = "No Match";
                        // if ($customerOvdData['is_new_customer'] == '1') {
                        if($cust_Type['source'] != 'CC'){
                            $dedupeStatusDetails = Api::checkdedupe($customerOvdData,$aof_number,$requestData['formId']);
                        }
                        $aofNumber = $formDetails['accountDetails']['aof_number'];
                        $fundingType = $customerOvdData['initial_funding_type'];
                        $amount = $customerOvdData['amount'];
                        $finconData = [
                            'FORM_ID' => $requestData['formId'],
                            'AOF_NUMBER' => $formDetails['accountDetails']['aof_number'],
                            'FUNDING_TYPE' => $customerOvdData['initial_funding_type'],
                            'REFERENCE_NUMBER' => $customerOvdData['reference'],
                            'AMOUNT' => $customerOvdData['amount'],
                            'FUNDING_STATUS' => 'N',
                            'FTR_STATUS' => 'N'
                        ];

                        $finconRecords = DB::table('FINCON')
                                            ->where('FORM_ID',$requestData['formId'])
                                            ->get()->toArray();
                        if(count($finconRecords) == 0){
                            $insertFinconData = DB::table('FINCON')->insert($finconData);
                        }else{
                            $insertFinconData = DB::table('FINCON')->where('FORM_ID',$requestData['formId'])
                                    ->update($finconData);
                        }

                        // if ($customerOvdData['is_new_customer'] == '1' && false) {
                        if($cust_Type['source'] != 'CC'){
                            $queryId = $dedupeStatusDetails['data'];

                            if($dedupeStatusDetails['status'] == "Success")
                            {
                                $queryId = $dedupeStatusDetails['data'];
                                $updateDeDupeStatus = DB::table("CUSTOMER_OVD_DETAILS")->whereId($customerOvdData['id'])
                                                                                    ->update(['QUERY_ID'=>$queryId,
                                                                                              'DEDUPE_STATUS'=>'']);
                            }else{
                                //rollback db transactions if any error occurs in query
                                DB::rollback();
                            }
                        }

                    }
                }

            // }
                $status = DB::table('ACCOUNT_DETAILS')->whereId($requestData['formId'])
                                                        ->pluck('application_status')->toArray();
                $status = current($status);
                if(in_array($status,[8,9,10,11,12]))
                {
                    $status = 9;                        // L2
                }else if($status == 16){                // QC - Hold
                    $status = 15;                       // QC
                }
                else if($status == 22){                 // QC - Discr
                    $status = 15;                       // QC
                }
                else if($status == 23){
                    $status = 18;                       // AU
                }
                else if($status == 19){
                    $status = 18;                       //  AU
                }else{
                    $status = 2;                        // Status L1
                }

                if($status == 9){
                    $nextRole = 4;  // Send to L2
                }else{
                    $nextRole = 3;  // Send to L1
                }

                $updateArray = Arr::except($request->get('data'),['formId','functionName','user_id','password','reason_for_accnt_opening','lead_generated','dist_from_branch','meeting_date','customer_meeting_location']);

                $updateArray['Declarations'] = $declarations;
                $updateArray['APPLICATION_STATUS'] = $status;
                $updateArray['SCREEN'] = 7;
                $updateArray['IS_ACTIVE'] = 1;
                $updateArray['NEXT_ROLE'] = $nextRole;
                $updateArray['CREATED_BY'] = Session::get('userId');
                //Add the update date 16feb2022 update to date to submit the from
                $updateArray['UPDATED_AT'] = CommonFunctions::getCurrentDBtime();

                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($requestData['formId'])
                                                            ->update($updateArray);
                $saveStatus = CommonFunctions::saveStatusDetails($requestData['formId'],2);
                $updateApplicationStatus = CommonFunctions::updateApplicationStatus('BRANCH_SUBMISSION',$formId);

                //chnages 20_10_2023 move code 
                if(Session::get('is_review') != 1){
                    if (($formDetails['accountDetails']['delight_scheme'] == 5) && ($formDetails['accountDetails']['account_type'] == "Savings")) {
                        $delightSavings = true;
                    }else{
                        $delightSavings = false;
                    }

                    if ($delightSavings) {
                        if ($formDetails['accountDetails']['delight_kit_id'] != '') {
                            $updateKitUtilized = DB::table('DELIGHT_KIT')->whereId($formDetails['accountDetails']['delight_kit_id'])
                                                                    ->update(['status'=> 8]);
                        }
                    }
                }

                if(($accountType == 3) && ($customerType == "ETB")){
                    $funding_type = Session::get('UserDetails')[$formId]['FinancialDetails']['initial_funding_type'];
                    if($funding_type == 3) // DCB Account
                    {
                        if($userrole == 11){

                            // Auto Approval
                            $updatel1approval = Rules::checkl1approval($requestData['formId']);
                            NotificationController::processNotification($requestData['formId'],'L1_CLEARED');
                            
                        }else{

                            // $updatel1approval = Rules::checkl1approval($requestData['formId']);
                            // $updatel2approval = Rules::checkl2approval($requestData['formId']);
                        }
                    }
                }
                
                $accountData = DB::table('ACCOUNT_DETAILS')->select('source')
                                                           ->where('ID',$formId)
                                                           ->get()->toArray();
                $accountData = (array) current($accountData);

                if(isset($accountData) && ($accountData['source'] == 'CC')){

                    $redirectURL = '/callcenter/dashboard';
                }else{

                    $redirectURL = '/bank/dashboard';
                }
                
                if($updateApplicationStatus){
                    CommonFunctions::moveAttactToMark($formId);
                    // NotificationController::processNotification($formId,'FORM_SUBMITTED');
                    CommonFunctions::clearSession();
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>$msg,'url'=>$redirectURL,'$data'=>[]]);
                }else{
                    DB::rollback();
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

    public function discrepencyresponse($formId,$step)
    {
        try{
            $discrepencyArray = array();
            $reviewData = array();
            if ($step == 'step-6') {
                 $url = 'submission';
            }else{
                $url = 'declaration';
            }
            $reviewedSectionColumns = DB::table('REVIEW_TABLE')
                                                ->select('section',DB::raw("LISTAGG(column_name, ', ') WITHIN GROUP (ORDER BY column_name) as columns"))
                                                ->where('FORM_ID',$formId)->groupBy('section')
                                                ->pluck('columns','section')->toArray();
            $userRoleId = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->pluck('role_id')->toArray();
            $steps = config('constants.STEPS');
            foreach ($steps as $section)
            {
                $discrepencyArray[$section]['section_name'] = $section;
                $discrepencyArray[$section]['discrepent'] = 'N';
                $discrepencyArray[$section]['coulmns'] = [];
                $discrepencyArray[$section]['url'] = config('constants.REDIRECT_URLS.'.$section);
                if(isset($reviewedSectionColumns[$section]))
                {
                    $discrepencyArray[$section]['discrepent'] = 'Y';
                    $columns = explode(',', $reviewedSectionColumns[$section]);
                    $columnArray = array();
                    if(count($columns) > 0)
                    {
                        foreach ($columns as $column)
                        {
                            $column = trim(explode('-', $column)[0]);
                            $columnArray[] = $column;
                            if(is_array(config('constants.REVIWED_COLUMNS.'.$section)))
                            {
                                if(in_array($column, config('constants.REVIWED_COLUMNS.'.$section)['columns']))
                                {
                                    $nextSection = config('constants.REVIWED_COLUMNS.'.$section)['nextsection'];
                                    if(count($nextSection) > 0)
                                    {
                                        $reviewData = array();
                                        foreach ($nextSection as $next_section => $column_name)
                                        {
                                            $reviewData['FORM_ID'] = $formId;
                                            $reviewData['COLUMN_NAME'] = $column_name;
                                            $reviewData['COMMENTS'] = 'Re-Confirm';
                                            $reviewData['SECTION'] = $next_section;
                                            $reviewData['ROLE_ID'] = current($userRoleId);
                                            $getReviewData = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$formId,'COLUMN_NAME'=>$column_name])
                                                                                        ->get()->toArray();
                                            if(count($getReviewData) > 0){

                                            }else{
                                                $insertReviewData = DB::table('REVIEW_TABLE')->insert($reviewData);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $discrepencyArray[$section]['coulmns'] = $columnArray;
                }
            }

            if(count($reviewData) > 0)
            {
                $discrepencyArray[$reviewData['SECTION']]['discrepent'] = 'Y';
                if(!in_array($reviewData['COLUMN_NAME'], $discrepencyArray[$reviewData['SECTION']]['coulmns']))
                {
                    array_push($discrepencyArray[$reviewData['SECTION']]['coulmns'], $reviewData['COLUMN_NAME']);
                }
            }
            $discrepencyArray = array_slice($discrepencyArray,explode('-', $step)[1]);
            foreach ($discrepencyArray as $discrepentData)
            {
                if($discrepentData['discrepent'] == 'Y')
                {
                    $url = $discrepentData['url'];
                    break;
                }
            }
            // force users to go to OVD section if coming from Basic page.
            if ($step == 'step-1') {
                $hasPan = false;
                foreach ($reviewedSectionColumns as $key => $value) {
                    if (stripos($value, 'pancard_no') !== false) {
                        $hasPan = true;
                        break;
                    }
                }
                if($hasPan){
                    $url = 'addovddocuments';
                }
            }
            return $url;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Bank/AddAccountController','discrepencyresponse',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function riskclassificationrating(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');

                $isPep = isset($requestData['pep']) && $requestData['pep'] !=""?$requestData['pep']:'';

                $riskRating = 'Low';
                $customerTypeRisk = DB::table('CUSTOMER_TYPE')->whereId($requestData['customer_risk_type'])
                                        ->pluck('risk_category','id')->toArray();
                $customerTypeRisk = current($customerTypeRisk);

                $occupationRisk = DB::table('OCCUPATION')->whereId($requestData['occupation'])
                                        ->pluck('risk_category','id')->toArray();
                $occupationRisk = current($occupationRisk);

                $countryRisk = DB::table('COUNTRIES')->whereId($requestData['country_risk'])
                                        ->pluck('risk_category','id')->toArray();
                $countryRisk = current($countryRisk);

                $perCountryRisk = DB::table('COUNTRIES')->whereId($requestData['country'])
                                        ->pluck('risk_category','id')->toArray();
                $perCountryRisk = current($perCountryRisk);
                $citizenshipRisk = DB::table('COUNTRIES')->whereId($requestData['citizenship'])
                                        ->pluck('risk_category','id')->toArray();
                $citizenshipRisk = current($citizenshipRisk);
                $countryOfBirthRisk = DB::table('COUNTRIES')->whereId($requestData['country_of_birth'])
                                        ->pluck('risk_category','id')->toArray();
                $countryOfBirthRisk = current($countryOfBirthRisk);

                $residenceForTaxPurpose = DB::table('COUNTRIES')->whereId($requestData['residence'])
                                        ->pluck('risk_category','id')->toArray();
                $residenceForTaxPurpose = current($residenceForTaxPurpose);

                if(($customerTypeRisk == 'H') || ($occupationRisk == 'H') || ($countryRisk == 'H') || ($perCountryRisk == 'H') || ($citizenshipRisk == 'H') || ($countryOfBirthRisk == 'H') || ($residenceForTaxPurpose == 'H'))
                {
                    $riskRating = 'High';
                }
                else if(($customerTypeRisk == 'M') || ($occupationRisk == 'M') || ($countryRisk == 'M') || ($perCountryRisk == 'M') || ($citizenshipRisk == 'M') || ($countryOfBirthRisk == 'M') || ($residenceForTaxPurpose == 'M'))
                {
                    $riskRating = 'Medium';
                }

                if($isPep == 'Yes'){
                   $riskRating = 'High';
                   $pepRisk = 'H';
                }else{
                   $pepRisk = 'L';
                }

                $responseData = array("customerTypeRisk"=> $customerTypeRisk, "occupationRisk"=>$occupationRisk, "countryRisk"=> $countryRisk, "perCountryRisk" => $perCountryRisk, "citizenshipRisk"=> $citizenshipRisk, "countryOfBirthRisk"=> $countryOfBirthRisk, "pepRisk"=>$pepRisk, "residenceForTaxRisk"=>$residenceForTaxPurpose);

                if($riskRating){
                    return json_encode(['status'=>'success','msg'=>'Risk Rating','data'=>['riskRating'=>$riskRating, 'individualRisk' => $responseData]]);
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

    public function categorisation(Request $request)
    {
        try{
            if ($request->ajax()){
                $response = array();
                //fetch data from request
                $requestData = $request->get('data');
                

                $isPep = isset($requestData['pep']) && $requestData['pep'] !=''?$requestData['pep']:'';

                $occupationRisk = DB::table('OCCUPATION')->whereId($requestData['occupation'])
                                        ->pluck('risk_category','id')->toArray();
                $occupationId = key($occupationRisk);
                $occupationRisk = current($occupationRisk);
                $countryRisk = DB::table('COUNTRIES')->whereId($requestData['country'])
                                        ->pluck('risk_category','id')->toArray();
                $countryRisk = current($countryRisk);
                $citizenshipRisk = DB::table('COUNTRIES')->whereId($requestData['citizenship'])
                                        ->pluck('risk_category','id')->toArray();
                $citizenshipRisk = current($citizenshipRisk);
                $countryOfBirthRisk = DB::table('COUNTRIES')->whereId($requestData['country_of_birth'])
                                        ->pluck('risk_category','id')->toArray();
                $countryOfBirthRisk = current($countryOfBirthRisk);
                if(in_array($occupationId,[7,8,15]))
                {
                    //if occupation politician (PEP,NOTARIES,SOLE PRACTITIONERS (LAW))
                    array_push($response, 7);
                }
                if($occupationRisk == 'H')
                {
                    //High Risk Proffesion
                    array_push($response, 5);
                }
                if(($countryRisk == 'H') || ($citizenshipRisk == 'H') || ($countryOfBirthRisk == 'H'))
                {
                    //Domiciled risk country
                    array_push($response, 2);
                }

                if($isPep == 'Yes')
                {
                    //PEP
                    array_push($response, 1);
                }

                //Comment to prevent categorization other's
                if(count($response) <= 0)
                {
                    //others
                    array_push($response, 8);
                }
                $pepdata = isset($requestData['pep']) && $requestData['pep'] !=''?$requestData['pep']:'';

                if($response){
                    return json_encode(['status'=>'success','msg'=>'Categorisation','data'=>['response'=>$response, 'pep'=>$pepdata]]);
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


    public function panisvalid(Request $request){
        $requestData = $request->get('data');
        if(isset($requestData['pancard_no']) && $requestData['pancard_no'] !=''){
            $requestData['pancard_no'] = CommonFunctions::decryptRS($requestData['pancard_no']);
            if($requestData['pancard_no'] == '' || substr($requestData['pancard_no'],0,2) == '__1'){
                return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['pancard_no']]]);  
            } 
        }
     
        $accountType = $requestData['account_type'];
        $scheme_code = explode('-',$requestData['scheme_code']);
        $newpanArray = array();
        $newpanArray[0]['panNo'] = $requestData['pancard_no'];
        $newpanArray[0]['name'] = $requestData['customer_full_name'];
        $newpanArray[0]['dob'] = Carbon::parse($requestData['dob'])->format('d/m/Y');
        $statusCheck = Api::newPanIsValid($newpanArray);
        $currdate = Carbon::now()->format('d-m-Y h:m:s');
        if($statusCheck['status'] == 'success'){
            for($i=0;count($statusCheck['data'])>$i;$i++){
    
                $insertpandetails = DB::table('PAN_DETAILS')->insert(['PANNO'=>$statusCheck['data'][$i]['panNo'],
                                                                      'EXIST'=>$statusCheck['data'][$i]['panStatus'],
                                                                    //   'CREATED_AT'=>$currdate,
                                                                      'AADHAARSEEDINGSTATUS'=>$statusCheck['data'][$i]['seedingStatus'],
                                                                      'NAME_MATCH_FLAG'=>$statusCheck['data'][$i]['name'],
                                                                      'DOB_MATCH_FLAG'=>$statusCheck['data'][$i]['dob'],
                                                                      'FATHER_NAME_MATCH_FLAG'=>$statusCheck['data'][$i]['fatherName'],
                                                                      'FORM_ID' =>NULL]);                    
                if($statusCheck['data'][$i]['panStatus'] != 'E'){
                    return json_encode(['status'=>'fail','msg'=>'Invalid pan '.$statusCheck['data'][$i]['panNo'],'data'=>[]]);
                }else{
                    
                    if($accountType == 1 && (isset($scheme_code[0]) &&  strtoupper($scheme_code[0]) == 'SB146')){
                        if(!(in_array($statusCheck['data'][$i]['seedingStatus'],["Y","NA"]))){
                           return json_encode(['status'=>'fail','msg'=>'NSDL: Pancard not linked','data'=>$pancard]);
                        }
                    }
                    return json_encode(['status'=>'success','msg'=>'PAN NSDL ok','data'=>[]]);
                }
            }
        }else{
            return json_encode(['status'=>'fail','msg'=>$statusCheck['msg'],'data'=>[]]);
        }
    }

    public function panisvalidold(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                if(isset($requestData['pancard_no']) && $requestData['pancard_no'] !=''){
                    $requestData['pancard_no'] = CommonFunctions::decryptRS($requestData['pancard_no']);
                    if($requestData['pancard_no'] == '' || substr($requestData['pancard_no'],0,2) == '__1'){
                        return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['pancard_no']]]);  
                    } 
                }
                
                //pancard exists in nsdl or not
                $pancard = Api::panIsValid($requestData);
                if($pancard['PANRes']['ErrorCode'] == "00"){
                    if($pancard['PANRes']['exist'] == "E"){ // If PAN Exist and is Valid!
                        // $bypassAadharCheck = true;
                        // if($pancard['PANRes']['aadhaarSeedingStatus'] == "Y"  || $pancard['PANRes']['aadhaarSeedingStatus'] == "NA"){
                           return json_encode(['status'=>'success','msg'=>'NSDL: Pancard OK','data'=>$pancard]);
                        // }else{
                        //    return json_encode(['status'=>'fail','msg'=>'NSDL: Pancard not linked','data'=>$pancard]);
                        // }
                        }else{
                        return json_encode(['status'=>'fail','msg'=>'NSDL: Invalid Pancard','data'=>[]]);
                    }
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

    public function savepandetails(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');
                //Begins db transaction
                DB::setDateFormat('DD-MM-YYYY');
                DB::beginTransaction();

                $fieldsCheckApastrophe = ['firstname','middlename','lastname','nameOnCard'];
                $requestData['panDetails'] = CommonFunctions::removeApastrophe($requestData['panDetails'],$fieldsCheckApastrophe);

                $requestData['panDetails'] = Arr::except($requestData['panDetails'],'id');
                $savePanDetails = DB::table('PAN_DETAILS')->insert($requestData['panDetails']);
                if($savePanDetails){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Pan Details Saved Successfully.','data'=>$requestData['panDetails']]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
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

    public function panexists(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                
                if(isset($requestData['pancard_no']) && $requestData['pancard_no'] !=''){
                    $requestData['pancard_no'] = CommonFunctions::decryptRS($requestData['pancard_no']);
                    if($requestData['pancard_no'] == '' || substr($requestData['pancard_no'],0,2) == '__1'){
                        return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['pancard_no']]]);  
                    } 
                }
                $pancard_no = $requestData['pancard_no'];

                if(env('APP_SETUP') == 'DEV'){
                    $schema = DB::table('CRM_ENTITYDOCUMENT');
                }else{
                    $schema = DB::connection('oracle2')->table('CRMUSER.ENTITYDOCUMENT');
                }

                $checkPanExist= $schema->where('DOCTYPECODE','PAN')                           
                               ->where('REFERENCENUMBER',$pancard_no)
                               ->take(20)
                               ->pluck('orgkey');
                                                         
                if(count($checkPanExist) > 0)
                {
                    $customerId = $checkPanExist->implode(',');
                    Session::put('ispanExists-'.$requestData['applicantId'],1);
                    return json_encode(['status'=>'warning','msg'=>'Pancard exists with customer Id-'.$customerId,'data'=>$customerId]);//show orgkey

                }else{
                    Session::put('ispanExists-'.$requestData['applicantId'],0);
                    return json_encode(['status'=>'success','msg'=>'FINACLE : Pancard does not exists','data'=>'']);
                }
               
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



    public function panexists_old(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                
                if(isset($requestData['pancard_no']) && $requestData['pancard_no'] !=''){
                    $requestData['pancard_no'] = CommonFunctions::decryptRS($requestData['pancard_no']);
                    if($requestData['pancard_no'] == '' || substr($requestData['pancard_no'],0,2) == '__1'){
                        return json_encode(['status'=>'fail','msg'=>'Decryption failed for applicant '.$i,'data'=>[$requestData['pancard_no']]]);  
                    } 
                }
                $pancard_no = $requestData['pancard_no'];
                //pancard exists in finacle or not
                $url = config('constants.APPLICATION_SETTINGS.PAN_DETAILS_URL');
                $current_timestamp = Carbon::now()->timestamp;
                $timestamp = Carbon::parse($current_timestamp)->format('d-m-Y');

                $panRequestData = array(
                            'reqHdr'=>array(
                                'consumerContext'=>array(
                                    'requesterId'=>'CUBE'
                                ),
                                'serviceContext'=>array(
                                    'serviceName'=>'panBasedDetails',
                                    'reqRefNum'=> $requestData['pancard_no'],
                                    'reqRefTimeStamp'=> $timestamp,
                                    'serviceVersionNo'=>'1.0',
                                )
                            ),
                            'reqBody'=>array(
                                'custDetails'=>array(
                                    'panNumber'=>$requestData['pancard_no']
                                )
                            )
                        );
                $panDetaills = Api::customerdetails($url,'panBasedDetailsReq',$panRequestData);
                if($panDetaills['status'] == "Success")
                {
                    $panDetaills = $panDetaills['data']['panBasedDetailsRes'];
                }else{
                    $msg = 'PAN Details API failed.'.$panDetaills['message'];
                    return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                }
                if(isset($panDetaills['serviceResponse']['resBody']['custDetails']['isExists']))
                {
                    if($panDetaills['serviceResponse']['resBody']['custDetails']['isExists'] == 'Y')
                    {
                        $customerId = $panDetaills['serviceResponse']['resBody']['custDetails']['custId'];
                        return json_encode(['status'=>'warning','msg'=>'Pancard exists','data'=>$customerId]);
                    }else{
                        return json_encode(['status'=>'success','msg'=>'FINACLE : Pancard does not exists','data'=>'']);
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

    public function saveetbaccount(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                
                $requestData = Arr::except($request->get('data'),'functionName');
                
                $customerDetaills = array();
                $customerData = array();
                if($requestData['customer_id'] != ''){
                    $url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
                    $customerDetaills = Api::customerdetails($url,'customerID',$requestData['customer_id']);
              
                    
                    if($requestData['account_type'] == 1 && $requestData['scheme_code'] == 3 && (isset($customerDetaills['data']['customerDetails']['STAFF_FLAG']) && $customerDetaills['data']['customerDetails']['STAFF_FLAG'] == 'N')){
                        $msg ="HRMS number not updated in Cust ID. Please update through Amendments";                        
                        return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                    }
                    if($customerDetaills['status'] != "Success")
                    {
                        if(isset($customerDetaills['data']['message'])){
                            $msg = 'Customer Details API: '.$customerDetaills['message'];
                        }else{
                            $msg = 'Customer Details API: No error response!';
                        }
                        return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                    }

                    if($customerDetaills['data']['status'] != "SUCCESS"){

                        $customerDetaills = $customerDetaills['data'];

                        if(isset($customerDetaills['message'])){
                            $msg = 'Customer Details API: '.$customerDetaills['message'];
                        }else{
                            $msg = 'Customer Details API: No error response!';
                    }
                        return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);

                    }

                    $checkStatus = Rules::precheckETBcustomer($customerDetaills,$requestData);
                    if(isset($checkStatus['status']) &&  $checkStatus['status'] != 'success'){
                        return json_encode(['status'=>$checkStatus['status'],'msg'=>$checkStatus['msg'],'data'=>[]]);
                    }
                    // if($customerDetaills['data']['customerDetails']['PAN_GIR_NUM'] == ''){
                    
                    //     if(isset($customerDetaills['data']['customerDetails']['PAN2_NUM']) && $customerDetaills['data']['customerDetails']['PAN2_NUM'] != ''){
                    //         return json_encode(['status'=>'fail','msg'=>'Unable to Proceed. Pancard not linked','data'=>[]]);
                    //     }
                    // }
                    
                    $applicantDob = Carbon::parse($customerDetaills['data']['customerDetails']['DATE_OF_BIRTH'])->age;

                    //Minor applicant not allow in Call Center commented on 12-10-2021
                    // if ($applicantDob < 18 && Session::get('role') == 11) {
                    //     return json_encode(['status'=>'fail','msg'=>'Applicant can not be Minor!','data'=>[]]);
                    // }

                    $customerDetaills = $customerDetaills['data']['customerDetails'];
                    $fieldsCheckApastrophe = ['CUST_NAME','CUST_COMU_ADDR1','CUST_COMU_ADDR2','CUST_PERM_ADDR1','CUST_PERM_ADDR2','EMAIL_ID'];
                    foreach ($fieldsCheckApastrophe as $fieldName) {
                        if ($customerDetaills[$fieldName] != '') {
                            $customerDetaills[$fieldName] = str_replace("'", "", strtoupper($customerDetaills[$fieldName]));
                        }
                    }

                    switch ($requestData['account_type']) {
                        case '1':
                            $table = 'SCHEME_CODES';
                            break;
                        case '2':
                            $table = 'CA_SCHEME_CODES';
                            break;
						case '3':
                            $table = 'TD_SCHEME_CODES';
                            break;
                        case '5':
                            $table = 'SCHEME_CODES';
                            break;
                        
                        default:
                            return json_encode(['status'=>'fail','msg'=>'Selected account type is restricted','data'=>[]]);
                            break;
                    }

                    
                    $schemeCodeDetails = DB::table($table)->where('ID',$requestData['scheme_code'])
                                                          ->get()->toArray();

                    $schemeCodeDetails = (array) current($schemeCodeDetails);

                    $checkETBcustomerId = ETBController::checkETBvalidation($customerDetaills,$schemeCodeDetails);
                    if ($checkETBcustomerId != '') {
                        return json_encode(['status'=>'fail','msg'=>$checkETBcustomerId,'data'=>[$customerDetaills,$schemeCodeDetails]]);
                    }

                }else if($requestData['pancard_no'] != ''){ // for ETB fetch on pan number 21-10-2021
                    // $url = config('constants.APPLICATION_SETTINGS.PAN_DETAILS_URL');
                    // $current_timestamp = Carbon::now()->timestamp;
                    // $timestamp = Carbon::parse($current_timestamp)->format('d-m-Y');
                    // $panRequestData = array(
                    //             'reqHdr'=>array(
                    //                 'consumerContext'=>array(
                    //                     'requesterId'=>'CUBE'
                    //                 ),
                    //                 'serviceContext'=>array(
                    //                     'serviceName'=>'panBasedDetails',
                    //                     'reqRefNum'=> $requestData['pancard_no'],
                    //                     'reqRefTimeStamp'=> $timestamp,
                    //                     'serviceVersionNo'=>'1.0',
                    //                 )
                    //             ),
                    //             'reqBody'=>array(
                    //                 'custDetails'=>array(
                    //                     'panNumber'=>$requestData['pancard_no']
                    //                 )
                    //             )
                    //         );
                    // $panDetaills = Api::customerdetails($url,'panBasedDetailsReq',$panRequestData);

                    // if($panDetaills['status'] == "Success")
                    // {
                    //     $panDetaills = $panDetaills['data']['panBasedDetailsRes'];

                    // }else{
                    //     $msg = 'PAN Details API Failed! [1]';
                    //     return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                    // }
                    // if((isset($panDetaills['serviceResponse']['resBody']['custDetails']['isExists'])) && ($panDetaills['serviceResponse']['resBody']['custDetails']['isExists'] == 'Y'))
                    // {
                    //     $customerId = $panDetaills['serviceResponse']['resBody']['custDetails']['custId'];
                    //     $url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
                    //     $customerDetaills = Api::customerdetails($url,'customerID',$customerId);
                    //     if($customerDetaills['status'] == "Success" && $customerDetaills['data']['status'] == "SUCCESS")
                    //     {
                    //         $customerDetaills = $customerDetaills['data']['customerDetails'];
                    //     }else{
                    //         if(isset($customerDetaills['data']['message'])){
                    //             $msg = 'Customer Details API: '.$customerDetaills['data']['message'];
                    //         }else{
                    //             $msg = 'Customer Details API Failed! [3] ';
                    //         }
                    //         return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                    //     }
                    // }else{
                    //     if(isset($panDetaills['serviceResponse']['esbErrorDesc'])){
                    //         $msg = 'PAN Details API: '.$panDetaills['serviceResponse']['esbErrorDesc'];
                    //     }else{
                    //         $msg = 'PAN Details API Failed! [2]';
                    //     }
                    //     return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                    // }
                }
                //Begins db transaction
                DB::beginTransaction();
                $userDetails['AccountDetails'] = array('customer_id'=>$customerDetaills['CUST_ID'],
                                                        'customer_full_name'=>$customerDetaills['CUST_NAME'],
                                                        'account_type'=>$requestData['account_type'],
                                                        'scheme_code'=>$requestData['scheme_code'],
                                                        'account_level_type'=>$requestData['account_level_type'],
                                                        'no_of_account_holders'=>$requestData['no_of_account_holders'],
                                                        'is_new_customer'=>0,
                                                        'screen'=>1);
                // if(!empty(Session::get('UserDetails')))
                // {
                //     $formId = Session::get('formId');
                // }else{
                //     $formId = DB::table('ACCOUNT_DETAILS')->insertGetId($userDetails['AccountDetails']);
                // }

                if(!empty(Session::get('formId')))
                {
                    $formId = Session::get('formId');
                    $accountUpdated = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->update($userDetails['AccountDetails']);
                    DB::commit();

                }else{

                    $formId = DB::table('ACCOUNT_DETAILS')->insertGetId($userDetails['AccountDetails']);
                    DB::commit();
                    Session::put('formId',$formId);

                }

                $customerName = explode(' ', $customerDetaills['CUST_NAME']);

                switch (count($customerName)) {
                       case '1':
                           $fName = '';
                           $mName = '';
                           $lName = $customerName[0];
                           break;

                        case '2':
                           $fName = $customerName[0];
                           $mName = '';
                           $lName = $customerName[1];
                           break;

                        case '3':
                           $fName = $customerName[0];
                           $mName = $customerName[1];
                           $lName = $customerName[2];
                           break;

                       default:
                            if(count($customerName) > 3){
                                $fName = $customerName[0];
                                $mName = $customerName[1];

                                $tmpName = '';
                                for ($i=2; $i < count($customerName) ; $i++) {

                                    $tmpName = $tmpName.' '.$customerName[$i];
                                }

                                $lName = $tmpName;

                            }else{

                                $fName = '';
                                $mName = '';
                                $lName = '';
                            }
                           break;
                   }

                $applicant_sequence = $requestData['applicantId'];
                // if(!empty(Session::get('UserDetails')[$formId]['customerOvdDetails']))
                // {
                //     $applicant_sequence = 0;
                // }
                $custMobile =  $customerDetaills['CUST_PAGER_NO'];

           		if (Session::get('role') == '11' && $custMobile == '') {
                	return json_encode(['status'=>'fail','msg'=>'Mobile number not available.','data'=>[]]);
           		}

                if(strlen($custMobile) > 10){
                    return json_encode(['status'=>'fail','msg'=>'Mobile number exceeds 10 digit limit.','data'=>[]]);
                } 

                // if($customerDetaills['CUST_COMU_PHONE_NUM_1'] != ''){
                //         $custMobile =  $customerDetaills['CUST_COMU_PHONE_NUM_1'];
                // }else{
                //    if($customerDetaills['CUST_COMU_PHONE_NUM_2'] != ''){
                //         $custMobile =  $customerDetaills['CUST_COMU_PHONE_NUM_2'];
                //    }else{
                //          if (Session::get('role') == '11') {
                //          return json_encode(['status'=>'fail','msg'=>'Mobile number not available.','data'=>[]]);
                //          }
                //    }
                // }

                $custEmail = $customerDetaills['EMAIL_ID'];

                $etbCustDetails = Array(
                    "FORM_ID" => $formId,
                    "APPLICANT_SEQUENCE"=> $applicant_sequence,
                    "CUSTOMER_ID"=> $customerDetaills['CUST_ID'],
                    "FINACLE_EMAIL" => $custEmail,
                    "CUBE_EMAIL" => "",
                    "FINACLE_MOB_NO" => $custMobile,
                    "CUBE_MOB_NO" => "",
                    "CREATED_AT"=> Carbon::now(),
                    "CREATED_BY"=> Session::get('userId'),
                    "AADHAR_NUM" => $customerDetaills["AADHAR_NUM"] ?? "",
                    "PASSPORT_NUM" => $customerDetaills["PASSPORT_NUM"] ?? "",
                    "VOTER_NUM" => $customerDetaills["VOTER_NUM"] ?? "",
                    "DRIVING_LIC_NUM" => $customerDetaills["DRIVINGLIC_NUM"] ?? "",
                );

                $ETBcustDetails = DB::table('ETB_CUST_DETAILS')->where('FORM_ID', $formId)
                                            ->where('APPLICANT_SEQUENCE',$applicant_sequence)
                                            ->get()->toArray();
                if (count($ETBcustDetails)>0) {
                    DB::table('ETB_CUST_DETAILS')->where('FORM_ID', $formId)
                                            ->where('APPLICANT_SEQUENCE',$applicant_sequence)
                                            ->update($etbCustDetails);
                }else{
                    DB::table('ETB_CUST_DETAILS')->insertGetId($etbCustDetails);
                }

                if(strlen($fName.' '.$lName) <= 24){
                    $short_name = $fName.' '.$lName;
                }else{
                    $short_name = substr($lName, 0,24);
                }
                $short_name = substr($short_name, 0,24);

                //28_03_2023 is not extra add beacuse check pan gir number and pan 2 num blank 

                $pf_type = 'pancard';
                
                if ($customerDetaills['PAN_GIR_NUM'] == '' && $customerDetaills['PAN2_NUM'] == '') {
                    $pf_type = 'form60';
                }

                $pancardNo = $customerDetaills['PAN_GIR_NUM'] != ''?$customerDetaills['PAN_GIR_NUM']:$customerDetaills['PAN2_NUM'];
                
                $commAddressDetails = CommonFunctions::getAddressDataByPincode($customerDetaills['CUST_COMU_PIN_CODE']);
                $commAddressDetails = current($commAddressDetails);
                $permAddressDetails = CommonFunctions::getAddressDataByPincode($customerDetaills['CUST_PERM_PIN_CODE']);
                $permAddressDetails = current($permAddressDetails);

                $customerOvdDetails[] = array('form_id'=> $formId,
                                                'pf_type'=> $pf_type,
                                                'pancard_no'=>$pancardNo,
                                                'applicant_sequence'=>$applicant_sequence,
                                                'first_name'=>$fName,
                                                'middle_name'=>$mName,
                                                'last_name'=>$lName,
                                                'short_name'=>strtoupper($short_name),
                                                'mobile_number'=>$custMobile,
                                                'email'=>$custEmail,
                                                'dob'=>$customerDetaills['DATE_OF_BIRTH'],
                                                // 'title'=>$customerDetaills['CUST_TITLE_CODE'],
                                                // 'title'=>12,
                                                'gender'=>$customerDetaills['CUST_SEX'],
                                                'title'=> ($customerDetaills['CUST_SEX'] == 'M' ? 12: 14),
                                                'current_address_line1'=>$customerDetaills['CUST_COMU_ADDR1'],
                                                'current_address_line2'=>$customerDetaills['CUST_COMU_ADDR2'],
                                                'current_state'=>$commAddressDetails->statedesc,
                                                'current_city'=>$commAddressDetails->citydesc,
                                                'current_pincode'=>$customerDetaills['CUST_COMU_PIN_CODE'],
                                                // 'current_country'=>$customerDetaills['CUST_COMU_CNTRY_CODE'],
                                                'current_country'=>1,
                                                'per_address_line1'=>$customerDetaills['CUST_PERM_ADDR1'],
                                                'per_address_line2'=>$customerDetaills['CUST_PERM_ADDR2'],
                                                'per_state'=>$permAddressDetails->statedesc,
                                                'per_city'=>$permAddressDetails->citydesc,
                                                'per_pincode'=>$customerDetaills['CUST_PERM_PIN_CODE'],
                                                // 'per_country'=>$customerDetaills['CUST_PERM_CNTRY_CODE'],
                                                'per_country'=>1,
                                                'customer_id'=>$customerDetaills['CUST_ID'],
                                                'customer_full_name'=>$customerDetaills['CUST_NAME'],
                                                'is_new_customer'=>0,
                                            );



                /*if(!empty(Session::get('UserDetails')[$formId]['customerOvdDetails']))
                {
                    // $customerOvdDetails[$requestData['applicantId']]['applicant_sequence'] = 0;
                    $customerOvdDetails = array_merge(Session::get('UserDetails')[$formId]['customerOvdDetails'],$customerOvdDetails);
                }
                array_unshift($customerOvdDetails, "phoney");
                unset($customerOvdDetails[0]);
                */

                $customerOvdDetails = (array) current($customerOvdDetails);
                $userDetails['customerOvdDetails'][$requestData['applicantId']] = $customerOvdDetails;
                
                $cod = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                        ->where('APPLICANT_SEQUENCE',$requestData['applicantId'])
                                                        ->get()->toArray();

                if(count($cod) == 0){
                    $customerOvdDetails['created_by'] = Session::get('userId');
                  $accountId = DB::table('CUSTOMER_OVD_DETAILS')->insertGetId($customerOvdDetails);
                }else{
                    $customerOvdDetails['updated_by'] = Session::get('userId');
                  $accountId = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                               ->where('APPLICANT_SEQUENCE',$requestData['applicantId'])
                                                               ->update($customerOvdDetails);
                  $accountId = $cod[0]->id;
                  DB::commit();
                }


                // if(!empty(Session::get('UserDetails')[$formId]['AccountIds']))
                // {
                //     $accountIds = Session::get('UserDetails')[$formId]['AccountIds'];
                //     array_push($accountIds, $accountId);
                // }else{
                //     $accountIds = [$accountId];
                // }

                $applicantIds = DB::table('CUSTOMER_OVD_DETAILS')->select('id')
                                                        ->where('FORM_ID',$formId)
                                                        ->orderBy('APPLICANT_SEQUENCE','ASC')
                                                        ->get()->toArray();
                $accountIds = [];
                foreach ($applicantIds as $applicant) {
                    $accountIds[$requestData['applicantId']] = $applicant->id;
                    // array_push($accountIds, $applicant->id);
                }

            //occupation change code check and store id for only 0007 and 0021 04_07_2024
                //remove 23_07_2024 approve manikandan
                            
            // if($customerDetaills['CUST_OCCP_CODE'] == '0006'){
            //     $customerDetaills['CUST_OCCP_CODE'] = '6';
            // }elseif($customerDetaills['CUST_OCCP_CODE'] == '0007'){
            //     $customerDetaills['CUST_OCCP_CODE'] = '14';
            // }elseif($customerDetaills['CUST_OCCP_CODE'] == '0010'){
            //     $customerDetaills['CUST_OCCP_CODE'] = '19';
            // }elseif($customerDetaills['CUST_OCCP_CODE'] == '0021'){
            //     $customerDetaills['CUST_OCCP_CODE'] = '28';
            // }elseif($customerDetaills['CUST_OCCP_CODE'] == '0026'){
            //     $customerDetaills['CUST_OCCP_CODE'] = '33';
            // }

                $userDetails['AccountIds'] = $accountIds;
                $riskDetails = array('form_id'=> $formId,
                                    'account_id'=>$accountId,
                                    'applicant_sequence'=> $applicant_sequence,
                                    'occupation'=>$customerDetaills['CUST_OCCP_CODE']);
                
                $userDetails['RiskDetails'] = $riskDetails;

                $riskRecord = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                        ->where('applicant_sequence', $applicant_sequence)
                                                        ->where('ACCOUNT_ID',$accountId)
                                                        ->get()->toArray();

                if(count($riskRecord) == 0){
                   $saveRiskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->insert($riskDetails);

                }else{
                 $saveRiskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                                            ->where('ACCOUNT_ID',$accountId)
                                                                            ->where('applicant_sequence', $applicant_sequence)
                                                                           ->update($riskDetails);
                }


                $customerData[$formId] = $userDetails;
                Session::put('UserDetails',$customerData);
                Session::put('customer_type',"ETB");
                Session::put('formId',$formId);
                $requestData = $request->get('data');
                //fetch residential status
                $residentialStatus = CommonFunctions::getResidentialStatus();
                //fetch education details
                $educationList = config('constants.EDUCATION');
                //fetch gross income details
                $grossIncome = CommonFunctions::getgrossannualIncome();
                // $grossIncome = config('constants.GROSS_INCOME');
                $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
                //fetch cusotmer account typess
                $customerAccountTypes = CommonFunctions::getCustomerAccountTypes();
                //fetch marital status
                $maritalStatus = CommonFunctions::getMaritalStatus();

                $allowETB = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE')->where('FIELD_NAME', 'ALLOW_ETB')->get()->toArray();
                $allowETB = current($allowETB)->field_value;

                DB::commit();
                //returns to template
                return view('bank.addaccountapplicant')->with('residentialStatus',$residentialStatus)
                                                ->with('educationList',$educationList)
                                                ->with('grossIncome',$grossIncome)
                                                ->with('placeOfBirthList',$placeOfBirthList)
                                                ->with('citizenshipList',$citizenshipList)
                                                ->with('maritalStatus',$maritalStatus)
                                                ->with('customerAccountTypes',$customerAccountTypes)
                                                ->with('userDetails',$userDetails)
                                                ->with('customerOvdDetails',$userDetails['customerOvdDetails'])
                                                ->with('AccountIds',$userDetails['AccountIds'])
                                                ->with('accountHoldersCount',$requestData['no_of_account_holders'])
                                                ->with('i',$requestData['applicantId'])
                                                ->with('allowETB',$allowETB)
                                                ;
            }
        }catch(\Illuminate\Database\QueryException $e) {
            DB::rollback();
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function saveetbcc(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');
                $accountNo = $requestData['account_no'];
                Session::put('accountNo',$accountNo);


                $accountNumbers[$requestData['account_no']] = $requestData['account_no'].' - (Rs. '.$requestData['balance'].')';
              

                $cust_details = $requestData['cust_details'];
                $cust_details = json_decode($cust_details, true);

                $url = 'addfinancialinfo';


                if(isset($cust_details['customer1'])){

                    $noofcust = 1;
                    $no_of_account_holders = 1;
                }

                if(isset($cust_details['customer2'])){

                    $noofcust = 2;
                    $no_of_account_holders = 2;
                }

                $formId = Session::get('formId');

                $cod = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                        ->get()->toArray();

                $branchId = Session::get('branchId');
                $aofnumber = CommonFunctions::genereateAofNumber($branchId);
                if(strlen($aofnumber) <= 10 || $aofnumber == ''){
                    return json_encode(['status'=>'fail','msg'=>'Something Went Wrong, Please Try Again','data'=>[]]);
                }

                $customerDetaills = array();
                $customerData = array();

                $mod_type = strtolower($requestData['mode_of_operation']);

                $mod = DB::table('MODE_OF_OPERATIONS')->select('ID')
                                                     ->whereRaw('LOWER(operation_type) like (?)',$mod_type)
                                                     ->get()->toArray();
                $mod = (array) current($mod);
                $mode_of_operation = $mod['id'];



             for($custid = 1; $custid <= $noofcust; $custid++){

                $current_cust_details = $cust_details['customer'.$custid];

                //Begins db transaction
                DB::beginTransaction();
                $userDetails['AccountDetails'] = array('customer_id'=>$current_cust_details['CUST_ID'],
                                                        'customer_full_name'=>$current_cust_details['CUST_NAME'],
                                                        'account_type'=>$requestData['account_type'],
                                                        'scheme_code'=>$requestData['scheme_code'],
                                                        'account_level_type'=>$requestData['account_level_type'],
                                                        'no_of_account_holders'=>$no_of_account_holders,
                                                        'is_new_customer'=>0,
                                                        'mode_of_operation'=> $mode_of_operation,
                                                        'aof_number'=>$aofnumber,
                                                        'branch_id'=>$branchId,
                                                        'created_by'=> Session::get('userId'),
                                                        'source'=> 'CC',
                                                        'screen'=>1);

                if(!empty(Session::get('formId')))
                {
                    $formId = Session::get('formId');
                    $accountUpdated = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->update($userDetails['AccountDetails']);
                    DB::commit();

                }else{

                    $formId = DB::table('ACCOUNT_DETAILS')->insertGetId($userDetails['AccountDetails']);
                    DB::commit();
                    Session::put('formId',$formId);

                }

                $checkAOFnumber = DB::table('ACCOUNT_DETAILS')->select('aof_number')->where('ID',$formId)->get()->toArray();
                $checkAOFnumber = (array) current($checkAOFnumber); 
                
                if(isset($checkAOFnumber['aof_number']) &&  $checkAOFnumber['aof_number'] == '')
                {
                    return json_encode(['status'=>'fail','msg'=>'Something Went Wrong, Please Try Again','data'=>[]]);
                }

                $customerName = explode(' ', $current_cust_details['CUST_NAME']);

                switch (count($customerName)) {
                       case '1':
                           $fName = '';
                           $mName = '';
                           $lName = $customerName[0];
                           break;

                        case '2':
                           $fName = $customerName[0];
                           $mName = '';
                           $lName = $customerName[1];
                           break;

                        case '3':
                           $fName = $customerName[0];
                           $mName = $customerName[1];
                           $lName = $customerName[2];
                           break;

                       default:
                            if(count($customerName) > 3){
                                $fName = $customerName[0];
                                $mName = $customerName[1];

                                $tmpName = '';
                                for ($i=2; $i < count($customerName) ; $i++) {

                                    $tmpName = $tmpName.' '.$customerName[$i];
                                }

                                $lName = $tmpName;

                            }else{

                                $fName = '';
                                $mName = '';
                                $lName = '';
                            }
                           break;
                   }


                $applicant_sequence = $custid;

                 // if($current_cust_details['CUST_COMU_PHONE_NUM_1'] != ''){
                    // $custMobile =  $current_cust_details['CUST_PAGER_NO'];
                // }else{
                //    if($current_cust_details['CUST_COMU_PHONE_NUM_2'] != ''){
                //         $custMobile =  $current_cust_details['CUST_COMU_PHONE_NUM_2'];
                //    }else{
                //         return json_encode(['status'=>'fail','msg'=>'Mobile number not available for initiating DeDupe API','data'=>[]]);
                //    }
                // }
                
                $custMobile =  $current_cust_details['CUST_PAGER_NO'];
                
                if($userDetails['AccountDetails']['source'] == 'CC'){
                    $nriFlag = DB::table('TD_SCHEME_CODES')->select('RI_NRI')
                                                           ->where('ID',$userDetails['AccountDetails']['scheme_code'])
                                                           ->get()
                                                           ->toArray();
                    $nriFlag = (array) current($nriFlag);

                if($custMobile == ''){
                        if($nriFlag['ri_nri'] != 'NRI'){ //NRI Ignore the validation
                    return json_encode(['status'=>'fail','msg'=>'Mobile number not available for initiating DeDupe API','data'=>[]]);
                }
                    }
                }else{
                    if($custMobile == ''){
                        return json_encode(['status'=>'fail','msg'=>'Mobile number not available for initiating DeDupe API','data'=>[]]);
                    }
                }

                //check pincode valid or not in finacle data

                $chkValidPincode = DB::table('FIN_PCS_DESC')->where('PINCODE',$current_cust_details['CUST_COMU_PIN_CODE'])
                                                            ->count();

                if($chkValidPincode == '0'){
                    return json_encode(['status'=>'fail','msg'=>'Pincode '.$current_cust_details['CUST_COMU_PIN_CODE'].' is not available in CUBE database. Please contact server administrator.','data'=>[]]);
                }

                $pancardNo = $current_cust_details['PAN_GIR_NUM'] != ''? $current_cust_details['PAN_GIR_NUM']:$current_cust_details['PAN2_NUM'];
                
                $customerOvdDetails = array('form_id'=> $formId,
                                                'pf_type'=>'pancard',
                                                'pancard_no'=>$pancardNo,
                                                'applicant_sequence'=>$applicant_sequence,
                                                'first_name'=>$fName,
                                                'middle_name'=>$mName,
                                                'last_name'=>$lName,
                                                'mobile_number'=>$custMobile,
                                                'email'=>isset($current_cust_details['EMAIL_ID']) && $current_cust_details['EMAIL_ID'] != ''?$current_cust_details['EMAIL_ID']:'',
                                                'dob'=>$current_cust_details['DATE_OF_BIRTH'],
                                                // 'title'=>$customerDetaills['CUST_TITLE_CODE'],
                                                'gender'=>$current_cust_details['CUST_SEX'],
                                                'title'=> ($current_cust_details['CUST_SEX'] == 'M' ? 12: 14),
                                                'current_address_line1'=>$current_cust_details['CUST_COMU_ADDR1'],
                                                'current_address_line2'=>$current_cust_details['CUST_COMU_ADDR2'],
                                                'current_state'=>$current_cust_details['CUST_COMU_STATE_CODE'],
                                                'current_city'=>$current_cust_details['CUST_COMU_CITY_CODE'],
                                                'current_pincode'=>$current_cust_details['CUST_COMU_PIN_CODE'],
                                                // 'current_country'=>$customerDetaills['CUST_COMU_CNTRY_CODE'],
                                                'current_country'=>1,
                                                'per_address_line1'=>$current_cust_details['CUST_PERM_ADDR1'],
                                                'per_address_line2'=>$current_cust_details['CUST_PERM_ADDR2'],
                                                'per_state'=>$current_cust_details['CUST_PERM_STATE_CODE'],
                                                'per_city'=>$current_cust_details['CUST_PERM_CITY_CODE'],
                                                'per_pincode'=>$current_cust_details['CUST_PERM_PIN_CODE'],
                                                // 'per_country'=>$customerDetaills['CUST_PERM_CNTRY_CODE'],
                                                'per_country'=>1,
                                                'etb_ntb'=>0,
                                                'customer_id'=>$current_cust_details['CUST_ID'],
                                                'customer_full_name'=>$current_cust_details['CUST_NAME'],
                                            );

                $userDetails['customerOvdDetails'][$custid] = $customerOvdDetails;


                if(count($cod) == 0){
                    $customerOvdDetails['created_by'] = Session::get('userId');
                  $accountId = DB::table("CUSTOMER_OVD_DETAILS")->insertGetId($customerOvdDetails);
                  DB::commit();

                }else{
                    $customerOvdDetails['updated_by'] = Session::get('userId');
                  $accountId = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                               ->where('APPLICANT_SEQUENCE',$custid)
                                                               ->update($customerOvdDetails);
                  $accountId = $cod[0]->id;
                  DB::commit();
                }


                if(!empty(Session::get('UserDetails')[$formId]['AccountIds']))
                {
                    $accountIds = Session::get('UserDetails')[$formId]['AccountIds'];
                    array_push($accountIds, $accountId);
                }else{
                    $accountIds = [$accountId];
                }
                array_unshift($accountIds, "phoney");
                unset($accountIds[0]);
                $userDetails['AccountIds'] = $accountIds;
                $riskDetails[] = array('form_id'=> $formId,
                                    'account_id'=>$accountId,
                                    'occupation'=>$current_cust_details['CUST_OCCP_CODE']);
                if(!empty(Session::get('UserDetails')[$formId]['RiskDetails']))
                {
                    $riskDetails = array_merge(Session::get('UserDetails')[$formId]['RiskDetails'],$riskDetails);
                }
                array_unshift($riskDetails, "phoney");
                unset($riskDetails[0]);
                $userDetails['RiskDetails'] = $riskDetails;


                $riskRecord = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                        ->orderBy('applicant_sequence', 'ASC')
                                                        ->where('ACCOUNT_ID',$accountId)
                                                        ->get()->toArray();

                if(count($riskRecord) == 0){

                   $saveRiskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->insert($riskDetails[$custid]);

                }else{

                 $saveRiskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                                            ->where('ACCOUNT_ID',$accountId)
                                                                           ->update($riskDetails[$custid]);
                }


                $customerData[$formId] = $userDetails;
                 Session::put('UserDetails',$customerData);
                // Session::put('accountType',$requestData['account_type']);
                // Session::put('accountNumbers',$accountNumbers);
                Session::put('customer_type',"ETB");
                // Session::put('etb_cc',"CC");
                // Session::put('formId',$formId);

                $global_cc_data = [ 'UserDetails' =>$customerData,
                                     'accountType' =>$requestData['account_type'],
                                     'account_number' =>$requestData['account_no'],
                                     'account_balance' =>$requestData['balance'],
                                     'customer_type' =>'ETB',
                                     'etb_cc' =>'CC',
                                     'formId' =>$formId,
                ];

                Session::put('global_cc_data',$global_cc_data);

                $updateFormId = DB::table('STATUS_LOG')->where('FORM_ID',Session::get('randomString'))
                                                    ->update(['FORM_ID'=>$formId]);

                if($updateFormId)
                {
                    Session::forget('randomString');
                }


                //$requestData = $request->get('data');
                //fetch residential status
                $residentialStatus = CommonFunctions::getResidentialStatus();
                //fetch education details
                $educationList = config('constants.EDUCATION');
                //fetch gross income details
                $grossIncome = CommonFunctions::getgrossannualIncome();
                // $grossIncome = config('constants.GROSS_INCOME');
                $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
                //fetch cusotmer account typess
                $customerAccountTypes = CommonFunctions::getCustomerAccountTypes();
                //fetch marital status
                $maritalStatus = CommonFunctions::getMaritalStatus();
                DB::commit();


                //returns to template
            }
                //return json_encode(['status'=>'success','msg'=>'Data Inserted','data'=>[]]);

                 return json_encode(['status'=>'success','msg'=>'Response Saved Successfully','data'=>['formId'=>$formId,'accountIds'=>$accountIds,'global_cc_data'=>$global_cc_data,'url'=>$url]]);

            }
        }catch(\Illuminate\Database\QueryException $e) {
            DB::rollback();
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getaddress(Request $request)
    {
        try{
            if ($request->ajax()){
                $customerDetails = array();
                $address = array();
                //fetch data from request
                $requestData = $request->get('data');
                $formId = $requestData['formId'];
                if($requestData['address_type'] == "permanent")
                {
                    $address = DB::table('CUSTOMER_OVD_DETAILS')->select('PER_ADDRESS_LINE1','PER_ADDRESS_LINE2','PER_STATE',
                                                                                    'PER_CITY','PER_PINCODE','C.NAME as PER_COUNTRY')
                                    ->leftjoin('COUNTRIES as C','C.ID' ,'=','CUSTOMER_OVD_DETAILS.PER_COUNTRY')
                                    ->where('FORM_ID',$formId)->get()->toArray();
                }else{
                    $address = DB::table('CUSTOMER_OVD_DETAILS')->select('CURRENT_ADDRESS_LINE1','CURRENT_ADDRESS_LINE2','CURRENT_STATE',
                                                                                    'CURRENT_CITY','CURRENT_PINCODE','C.NAME as CURRENT_COUNTRY')
                                    ->leftjoin('COUNTRIES as C','C.ID' ,'=','CUSTOMER_OVD_DETAILS.CURRENT_COUNTRY')
                                    ->where('FORM_ID',$formId)->get()->toArray();
                }
                $address = (array) current($address);
                if($address){
                    return json_encode(['status'=>'success','msg'=>'Address Details.','data'=>$address]);
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

    /*public function getaccountdetails(Request $request)
    {
        try{
            if ($request->ajax()){
                $customerDetails = array();
                $accountData = array();
                //fetch data from request
                $requestData = $request->get('data');
                $requestData['customerID'] = '010443984';
                $requestData['accountNumber'] = '01025100001091';
                $accountData = Api::accountBalanceEnquiry($requestData['customerID'],$requestData['accountNumber']);
                if($accountData){
                    return json_encode(['status'=>'success','msg'=>'Account Balance Details.','data'=>$accountData]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }*/

    public function ekycdetails(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $eKYCRequestData = array();
                $kycdataArray = array();
                $Error = false;
                //fetch data from request
                $eKYCRequestData['referenceNumber'] = $requestData['kyc_reference_no'];
                $eKYCRequestData['txnId'] = 'UKC:0002439120200615165105070';
                // $eKYCRequestData['timeStamp'] = '6/8/2020 5:56:20 PM';
                $eKYCRequestData['timeStamp'] = Carbon::now()->format('d/m/Y H:i:s a');
                $form_id = Session::get('formId');
                $eKYCData = Api::ekycDetails($eKYCRequestData,Session::get('formId'),1);
                $address1=strlen($eKYCData['data']['response']['userAddress']['locality']) - substr_count($eKYCData['data']['response']['userAddress']['locality'], ' ');
                $address2= strlen($eKYCData['data']['response']['userAddress']['village']) - substr_count($eKYCData['data']['response']['userAddress']['village'], ' ');
               $landmark=strlen($eKYCData['data']['response']['userAddress']['landmark']) - substr_count($eKYCData['data']['response']['userAddress']['landmark'], ' ');
               $datacount=$address1+$address2+$landmark;
		
		if (isset($eKYCData['data']['response']['refno']) && $eKYCData['data']['response']['refno'] == '') {
                    return json_encode(['status'=>'fail','msg'=>'Aadhaar vault reference number not available.','data'=>[]]);
                }  

                if (isset($eKYCData['data']['response']['userDetails']['gender']) && $eKYCData['data']['response']['userDetails']['gender'] != 'M' && $eKYCData['data']['response']['userDetails']['gender'] != 'F') {
                    return json_encode(['status'=>'fail','msg'=>'Error! Unable to find gender/ title.','data'=>[]]);
                }  

                if (isset($eKYCData['data']['response']['userDetails']['gender'])) {
                    $dob = DB::table('CUSTOMER_OVD_DETAILS')->select('dob')
                                                            ->where('form_id',$form_id)
                                                            ->where('APPLICANT_SEQUENCE',$requestData['id'])
                                                            ->get()->toArray();     
                    $dob = Carbon::parse(current($dob)->dob)->age;
                    
                    $title = CommonFunctions::getTitleForEkyc($dob,$eKYCData['data']['response']['userDetails']['gender']);

                    if ($title < 0) {
                        return json_encode(['status'=>'fail','msg'=>'Error! Unable to find gender/ title.','data'=>[]]);
                    }
                    
                    $eKYCData['data']['response']['userDetails']['title'] = $title;              
                }

                if(isset($eKYCData['data']['response']['userDetails']['name'])){
                    $eKYCData['data']['response']['userDetails']['name'] = str_replace("'", "", strtoupper($eKYCData['data']['response']['userDetails']['name']));

                    foreach ($eKYCData['data']['response']['userAddress'] as $fieldName => $fieldValue) {
                        if ($eKYCData['data']['response']['userAddress'][$fieldName] != '') {
                            $eKYCData['data']['response']['userAddress'][$fieldName] = str_replace("'", "", strtoupper($eKYCData['data']['response']['userAddress'][$fieldName]));
                        }
                    }

                   $fullName  = $eKYCData['data']['response']['userDetails']['name'];
                   $tokens  = explode(' ', $fullName);

                   switch (count($tokens)) {
                       case '1':
                           $eKYCData['data']['response']['userDetails']['first_name'] = '';
                           $eKYCData['data']['response']['userDetails']['middle_name'] = '';
                           $eKYCData['data']['response']['userDetails']['last_name'] = $tokens[0];
                           break;

                        case '2':
                           $eKYCData['data']['response']['userDetails']['first_name'] = $tokens[0];
                           $eKYCData['data']['response']['userDetails']['middle_name'] = '';
                           $eKYCData['data']['response']['userDetails']['last_name'] = $tokens[1];
                           break;

                        case '3':
                           $eKYCData['data']['response']['userDetails']['first_name'] = $tokens[0];
                           $eKYCData['data']['response']['userDetails']['middle_name'] = $tokens[1];
                           $eKYCData['data']['response']['userDetails']['last_name'] = $tokens[2];
                           break;

                       default:
                            if(count($tokens) > 3){
                                $eKYCData['data']['response']['userDetails']['first_name'] = $tokens[0];
                                $eKYCData['data']['response']['userDetails']['middle_name'] = $tokens[1];

                                $lName = '';
                                for ($i=2; $i < count($tokens) ; $i++) {

                                    $lName = $lName.' '.$tokens[$i];
                                }

                                $eKYCData['data']['response']['userDetails']['last_name'] = $lName;

                            }else{

                                $Error = true;
                            }
                           break;
                   }

                }else{

                    $Error = true;
                }
            
                $ekyc_photo = '';
                $ekycapiresponse = '';

                if(($eKYCData['status'] == "Success") && (!$Error)){
                  
                    $eKYCDetails = $eKYCData['data']['response'];
                    $ekyc_photo =  isset($eKYCDetails['userPhoto']) && $eKYCDetails['userPhoto'] != ''?$eKYCDetails['userPhoto']:'';

                    unset($eKYCDetails['userPhoto']);
                    $ekycapiresponse =  json_encode($eKYCDetails);

                    $kycdataArray['form_id'] = $form_id;
                    $kycdataArray['applicant_sequence'] = $requestData['id'];
                    $kycdataArray['ekyc_no'] = $eKYCDetails['rrn'];
                    $kycdataArray['ekyc_reference_no'] = $eKYCDetails['refno'];
                    $kycdataArray['name'] =  $eKYCDetails['userDetails']['name'];
                    $kycdataArray['dob'] = Carbon::parse($eKYCDetails['userDetails']['dob'])->format('Y-m-d');
                    $kycdataArray['gender'] = $eKYCDetails['userDetails']['gender'];
                    $kycdataArray['title'] = $eKYCDetails['userDetails']['title'];
                    $kycdataArray['first_name'] = $eKYCDetails['userDetails']['first_name'];
                    $kycdataArray['middle_name'] = $eKYCDetails['userDetails']['middle_name'];
                    $kycdataArray['last_name'] = $eKYCDetails['userDetails']['last_name'];
                    $shortName = substr($eKYCDetails['userDetails']['name'].' '.$eKYCDetails['userDetails']['last_name'],0,10);
                    $kycdataArray['short_name'] = $shortName;
                 
                    $getvalidAddress = CommonFunctions::ekycaddsmartsplit($eKYCDetails['userAddress']);
       
                    $kycdataArray['address_line_1'] = $getvalidAddress['address_line_1'];
                    $kycdataArray['address_line_2'] = $getvalidAddress['address_line_2'];
                    $kycdataArray['country']  = $eKYCDetails['userAddress']['country'];
                    $kycdataArray['pincode']  = $eKYCDetails['userAddress']['pincode'];
                    $kycdataArray['state']  = $eKYCDetails['userAddress']['state'];
                    $kycdataArray['city']  = $eKYCDetails['userAddress']['district'];
                    $kycdataArray['landmark']  = $getvalidAddress['landmark'];

                    //adding below code to prevent ekyc state and city in compare
     
                    if($eKYCDetails['userAddress']['pincode'] != ''){
                        $getfinpcsdata = DB::table('FIN_PCS_DESC')->select('CITYDESC','STATEDESC')->where('PINCODE',$eKYCDetails['userAddress']['pincode'])->get()->toArray();
                        if(count($getfinpcsdata)>0){
                            $getfinpcsdata = (array) current($getfinpcsdata);
                            $kycdataArray['state']  = $getfinpcsdata['statedesc'];
                            $kycdataArray['city']  = $getfinpcsdata['citydesc'];
                        }
                    }
                    
                    $getCountryCode = DB::table('COUNTRIES')->select('ID')->where(DB::raw('upper(NAME)'),strtoupper($kycdataArray['country']))->get()->toArray();
                    
                    $kycdataArray['api_response'] = $ekycapiresponse;

                    if(count($getCountryCode)>0){
                        $getCountryCode = (array)current($getCountryCode);
                        $getCountryCode = $getCountryCode['id'];
                    }
                    $kycdataArray['country'] = $getCountryCode;
                
                    $ekycdatainsert = DB::table('EKYC_DETAILS')->insertLob($kycdataArray,
                                                                            ['EKYC_PHOTO'=>base64_encode(json_encode(
                                                                                array(
                                                                                    'ekyc_photo_details'=>$ekyc_photo
                                                                                ))
                                                                            )]);
                  
                    if(!$ekycdatainsert){
                        return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                    }

                    $checksumekycData = strtoupper($eKYCDetails['userDetails']['name'].$eKYCDetails['userDetails']['gender'].$eKYCDetails['userDetails']['title'].$eKYCDetails['userDetails']['first_name'].$eKYCDetails['userDetails']['middle_name'].$eKYCDetails['userDetails']['last_name'].$kycdataArray['address_line_1'].$kycdataArray['address_line_2'].$kycdataArray['landmark'].$eKYCDetails['userAddress']['pincode'].$kycdataArray['state'].$kycdataArray['city']);

                    $checksumekycData = str_replace(' ','',$checksumekycData);
           
                    $hash1 = hash('sha256', $checksumekycData);
                    return json_encode(['status'=>'success','msg'=>'E-KYC Details.','data'=>$kycdataArray,'datacount'=>$datacount,'datahash'=>$hash1]);
                }else{
                    $msg = 'E-KYC API failed.'.$eKYCData['code'].': '.$eKYCData['message'].': '.$eKYCData['moreInformation'];
                    return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function saveform(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                CommonFunctions::clearSession();
                return json_encode(['status'=>'success','msg'=>'Session Cleared.','data'=>[]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkdedupestatus($callFrom = 'control')
    {
        $updateDeDupeStatus = '';
        $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->select('DEDUPE_STATUS','FORM_ID','QUERY_ID','ID')
                                            ->whereNotNull('QUERY_ID')
                                            ->whereNotIn('DEDUPE_STATUS',['No Match','Match'])
                                            ->where('CREATED_AT','>=',Carbon::now()->subMonths(1))
                                            ->orderBy('ID','DESC')  
                                            ->take(100)->get()->toArray();


        //if funding type is cheque and it is cleared or not cleared
        if(count($customerOvdDetails) > 0)
        {
            foreach($customerOvdDetails as $customerOvdData)
            {
                
                $customerOvdData = (array) $customerOvdData;
                $dedupeStatusDetails = Api::checklivystatus($customerOvdData['query_id'],$customerOvdData['form_id']);
                if(isset($dedupeStatusDetails['response']['data'][0]['decisionFlag'])){

                   $deDupeStatus = $dedupeStatusDetails['response']['data'][0]['decisionFlag'];
                   $deDupeReference = substr($dedupeStatusDetails['response']['data'][0]['remarks'],0,100);
                   $deDupeStatus = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeStatus);
                   $deDupeReference = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeReference);

                   $updateDeDupeStatus = DB::table("CUSTOMER_OVD_DETAILS")->whereId($customerOvdData['id'])
                                                                ->where('QUERY_ID',$customerOvdData['query_id'])
                                                                ->update(['DEDUPE_STATUS'=>$deDupeStatus,'DEDUPE_REFERENCE'=>$deDupeReference]);
                }
                else{
                    if ($callFrom == 'control') {
                         return json_encode(['status'=>'fail','msg'=>'Unable to Update dedupe response.','data'=>[]]);
                    }
                }
            }
        }else{
            return json_encode(['status'=>'success','msg'=>'No records found','data'=>[]]);
        }

        if($updateDeDupeStatus)
        {
            return json_encode(['status'=>'success','msg'=>'Updated dedupe response.','data'=>[]]);
        }else{
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function getbatchnumber()
    {
        $finacleQueryInstance = DB::connection('oracle2');
        // $batchNoDetails = $finacleQueryInstance->select('select CUSTOM.DCB_CMUPL_BATCH_NUM.nextVal from dual');
        $batchNoDetails = $finacleQueryInstance->select('select '.env('FINACLE_BATCH_SEQUNCE').'.nextVal from dual');
        $batchNoDetails = (array) current($batchNoDetails);
        $batch_no = $batchNoDetails['nextval'];
        DB::disconnect('oracle2');
        return $batch_no;
    }

    public  function registerScreenFlow(Request $request){

        try{
            if($request->ajax()){

            $requestData = $request->get('data');

            $curr_max_screen = Session::get('max_screen');
            $curr_last_screen = Session::get('last_screen');

           if($curr_max_screen == ''){

             Session::put('max_screen',1);
           }

            if($curr_last_screen == ''){

             Session::put('last_screen',1);
           }

           Session::put('last_screen', $requestData['from']);

           if( $requestData['to'] > $curr_max_screen){

             Session::put('max_screen', $requestData['to']);
           }

        $curr_max_screen = Session::get('max_screen');
        $curr_last_screen = Session::get('last_screen');

           return json_encode(['status'=>'success','msg'=>'Screen Updated.','data'=>['max'=>$curr_max_screen,'last'=>$curr_last_screen]]);
        }
    }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }


   /*
    * Check ETB / Call Center Customer Type;
    * Created At : 14-06-2021
    */

   public function checkEtbCustomerType(Request $request){
      try{
            if($request->ajax()){

            $role = Session::get('role');


            if($role == '11'){
                $requestData = Arr::except($request->get('data'),'functionName');

                $schemeCodeDetails = DB::table('TD_SCHEME_CODES')->where('ID',$requestData['scheme_code'])
                                                      ->get()->toArray();
                $schemeCodeDetails = (array) current($schemeCodeDetails);

                $gridDataDetails = CommonFunctions::ccGrid($requestData['customer_id'],$schemeCodeDetails);

            //Checking Scheme is valid for Senior Citizen or Not----------
                if((isset($gridDataDetails['CustomerDetails'])) && ($gridDataDetails['CustomerDetails']['status'] == 'FAILED')){

                 return json_encode(['status'=>'fail','msg'=>$gridDataDetails['CustomerDetails']['message'],'data'=>[]]);
                }
                if(!is_array($gridDataDetails) && substr($gridDataDetails,0, 6) == 'Error!'){
                 return json_encode(['status'=>'fail','msg'=>'Error fetching customer details or no records found.','data'=>[]]);
                }
                return view('bank.ccgrid')->with('gridDataDetails',$gridDataDetails);
            }else{

              return Self::saveetbaccount($request);
            }
        }
    }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
   }


   public function vaultenumberupdate($customerId,$aadharNumber,$formId,$type,$id){
    //one doute of the two in flow beacuse chanage adddress proof 
        $aadharReferenceDetails = Api::aadharValutSvc($customerId,$aadharNumber,$formId);
        
        $aadhaarCode =  isset($aadharReferenceDetails['code']) && $aadharReferenceDetails['code'] != '' ? $aadharReferenceDetails['code'] : '!';
        $aadhaarMsg =  isset($aadharReferenceDetails['message']) && $aadharReferenceDetails['message'] != '' ? $aadharReferenceDetails['message'] : '!';

        $updateReferenceArray = array();
        if(isset($aadharReferenceDetails['status']) && $aadharReferenceDetails['status'] == "Success"){

            $referenceKey = isset($aadharReferenceDetails['data']['response']['referenceKey']) && $aadharReferenceDetails['data']['response']['referenceKey'] != ''? $aadharReferenceDetails['data']['response']['referenceKey'] : '';

            if($referenceKey == ''){
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                            ->update(['NEXT_ROLE' => 2]);
                $msg = 'Aadhaar Vault API failed.'.$aadhaarCode.': '.$aadhaarMsg;
                return ['status'=>'fail','msg'=>$msg,'data'=>[]];
            }

            switch($type){
                case 'proofId':
                    $updateReferenceArray['ID_PROOF_AADHAAR_REF_NUMBER'] = $referenceKey;
                break;
                case 'proofAdd':
                    $updateReferenceArray['ADD_PROOF_AADHAAR_REF_NUMBER'] = $referenceKey;
                break;
                case 'both':
                    $updateReferenceArray = ['ADD_PROOF_AADHAAR_REF_NUMBER' => $referenceKey,
                                            'ID_PROOF_AADHAAR_REF_NUMBER' => $referenceKey];
                break;
                default:
                     return ['status'=>'fail','msg'=>'Incorrect Type !!','data'=>[]];
                break;
            }

            $updateReferenceArray['UPDATED_BY'] = Session::get('userId');
            $updateReference = DB::table('CUSTOMER_OVD_DETAILS')->whereId($id)
                                            ->update($updateReferenceArray);

            return ['status'=>'success','msg'=>'Reference details updated','data'=>[]];
        }else{
            $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->update(['NEXT_ROLE' => 2]);

            $msg = 'Aadhaar Vault API failed.'.$aadhaarCode.' : '.$aadhaarMsg;
            return ['status'=>'fail','msg'=>$msg,'data'=>[]];
        }

        // Old Code for Ref! -- delete after Jan.23
        // $aadharReferenceDetails = Api::aadharValutSvc($customerId,$aadharNumber,$formId);
                        // if($aadharReferenceDetails['status'] == "Success")
                        // {
                        //     $referenceKey = $aadharReferenceDetails['data']['response']['referenceKey'];
                        //     if($customerData['proof_of_identity'] == 1)
                        //     {
                        //         $updateReferenceArray['ID_PROOF_AADHAAR_REF_NUMBER'] = $referenceKey;
                        //         $customerData = (array) $customerData;
                        //         $id = $customerData['id'];
                        //         $customerId = '001189092';
                        //         if(($customerData['proof_of_identity'] == 1) || ($customerData['proof_of_address'] == 1)){

                        //             if($customerData['proof_of_identity'] == 1)
                        //             {
                        //                 $aadharNumber = str_replace('-','',$customerData['id_proof_card_number']);
                        //             }
                        //             if($customerData['proof_of_address'] == 1)
                        //             {
                        //                 $aadharNumber = str_replace('-','',$customerData['add_proof_card_number']);
                        //             }
                        //             $aadharReferenceDetails = Api::aadharValutSvc($customerId,$aadharNumber,$formId);
                        //             if($aadharReferenceDetails['status'] == "Success")
                        //             {
                        //                 $referenceKey = $aadharReferenceDetails['data']['response']['referenceKey'];
                        //                 if($customerData['proof_of_identity'] == 1)
                        //                 {
                        //                     $updateReferenceArray['ID_PROOF_AADHAAR_REF_NUMBER'] = $referenceKey;
                        //                     //$updateReferenceArray['ID_PROOF_CARD_NUMBER'] = '9999-9999-'.substr(trim($aadharNumber), -4 );
                        //                 }
                        //                 if($customerData['proof_of_address'] == 1)
                        //                 {
                        //                     $updateReferenceArray['ADD_PROOF_AADHAAR_REF_NUMBER'] = $referenceKey;
                        //                     //$updateReferenceArray['ADD_PROOF_CARD_NUMBER'] = '9999-9999-'.substr(trim($aadharNumber), -4 );

                        //                 }
                        //                 $updateReference = DB::table('CUSTOMER_OVD_DETAILS')->whereId($id)
                        //                     ->update($updateReferenceArray);
                        //             }else{
                        //                 $msg = 'Aadhar Vault API failed.'.$aadharReferenceDetails['code'].': '.$aadharReferenceDetails['message'];
                        //                 //return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                        //             }
                        //         }
                        //     }
                        //     if($customerData['proof_of_address'] == 1)
                        //     {
                        //         $updateReferenceArray['ADD_PROOF_AADHAAR_REF_NUMBER'] = $referenceKey;
                        //     }

                        //     $updateReference = DB::table('CUSTOMER_OVD_DETAILS')->whereId($id)
                        //                                                     ->update($updateReferenceArray);
                        // }else{
                        //     $msg = 'Aadhar Vault API failed.'.$aadharReferenceDetails['code'].': '.$aadharReferenceDetails['message'];
                        //     return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                        // }



   }

   public function getnomineedetailsbyAccid(Request $request){  
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $accountNo = Session::get('accountNo');
                
                $getNomineeAcctDetails = AmendController::SBAccountInq($accountNo);
                
                if(count($getNomineeAcctDetails) > 0){
                $getNomineeAcctDetails = (array) current($getNomineeAcctDetails);

                // get nominee age
                $currentyear = date("Y");
                    
                $nomineeBirthYear = explode('-',$getNomineeAcctDetails['nomineeBirthDt'])[2];
                $nomineeAge = ($currentyear - $nomineeBirthYear)- 1;

                //get nominee relationship type
                $nomineeRelType = DB::table('RELATIONSHIP')->select('ID','DESCRIPTION')
                                                            ->where('CODE',$getNomineeAcctDetails['relType'])
                                                            ->get()->toArray();
                
                $nomineeRelType = (array) current($nomineeRelType);

                $getNomineeAcctDetails['nomineeAge'] = $nomineeAge;
                $getNomineeAcctDetails['nomineeRelTypeDesc'] = $nomineeRelType['description'];
                $getNomineeAcctDetails['nomineeRelTypeID'] = $nomineeRelType['id'];

                    return json_encode(['status'=>'success','msg'=>'NomineeData Details','data'=>$getNomineeAcctDetails]);
                }else{
                    $msg = 'Error! Account Number not found in registered database. Please contact NPC admin';
                    return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                }
            }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }

    public static function checkAmendEtbRequestPending(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $cust_id = $data["data"]["cust_id"];
            $res = Rules::custAmendRequestIsPending($cust_id);
            return $res;
        }
    }


    public function ProofSelectedValidation(Request $request)
    {
        if ($request->ajax()) {
            $requestData = $request->get('data');
            $getList = Rules::validatedproofList($requestData);
            return json_encode(['status' => 'success', 'msg' => 'Fetch data successfully', 'data' => $getList, 'requestData' => $requestData]);
        }
    }

}
?>
