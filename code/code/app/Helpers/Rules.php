<?php
namespace App\Helpers;

use App\Helpers\CommonFunctions;
use App\Helpers\ApiCommonFunctions;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Arr;
use Session;
use Route;
use App\Helpers\Api;
use Carbon\Carbon;
use DB;

class Rules {

    public static function getValidateOccupation_old($formId)
    {
        $occupationList = array();
       
        $session_exists = 0;
        $apply_scheme_occupation = false;
        $accountType = Session::get('accountType');
        if($accountType == 1){
            $accountDetails = DB::table('ACCOUNT_DETAILS')
                                        ->where('ACCOUNT_DETAILS.ID',$formId)
                                        ->get()->toArray();
            $accountDetails = (array) current($accountDetails);

            $schemeCodes = DB::table('SCHEME_CODES')
                                ->where('ACCOUNT_TYPE',$accountType)
                                ->where('ID',$accountDetails['scheme_code'])
                                ->where('IS_ACTIVE',1)
                                ->get()->toArray();
            $schemeCodes = (array) current($schemeCodes);

            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->where('FORM_ID',$formId)
                                        ->get()->toArray();
            $customerOvdDetails = (array) current($customerOvdDetails);

          if($schemeCodes['primary_applicant_occupation'] != 'All'){
             $apply_scheme_occupation = true;
             $scheme_occupation = $schemeCodes['primary_applicant_occupation'];
          }
        }

        if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails']))
        {
            $applicantIds = Session::get('UserDetails')[$formId]['AccountIds'];

        if(count($applicantIds) > 0)
            {
                // foreach($applicantIds as $key => $applicantId)
                for($key=1;count($applicantIds)>=$key;$key++)
                {
                    if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['gender']))
                    {
                        $occupations = CommonFunctions::getOccupation(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['is_new_customer']);
                        $session_exists = 1;
                        $gender = Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['gender'];
                        $applicantDob = Carbon::parse(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['dob'])->age;
                        $femaleAccountHolder = Session::get('UserDetails')[$formId]['AccountDetails']['no_of_account_holders'];

                        if(($apply_scheme_occupation) &&  ($customerOvdDetails['applicant_sequence'] == 1)){

                             $occupationList[$key] = Arr::only($occupations,$scheme_occupation);

                        }else{
                            if($gender == "M" && $applicantDob >= 18){
                                $occupationList[$key] = Arr::except($occupations,2);
                            }elseif($gender == "M" || $gender == "F" && $applicantDob < 18){
                                
                                $occupationList[$key] = Arr::only($occupations,3);
                            }elseif($gender == "F" && $femaleAccountHolder == 1){
                               $occupationList[$key] = $occupations;
                            }
                            else{
                                $occupationList[$key] = $occupations;
                            }
                        }
                    }else{
                        $occupationList[$key] = $occupations;
                    }
                }
            }
        }
    	return $occupationList;
    }

    public static function getValidateOccupation($formId)
    {
        $occupationList = array();
       
        $session_exists = 0;
        $apply_scheme_occupation = false;
        $accountType = Session::get('accountType');

        $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();

        $accountDetails = (array) current($accountDetails);

        $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('DOB','IS_NEW_CUSTOMER','GENDER','APPLICANT_SEQUENCE')
                                                                ->where('FORM_ID',$formId)
                                                                ->get()->toArray();

        if($accountType == 1){

            $schemeCodes = DB::table('SCHEME_CODES')
                                ->where('ACCOUNT_TYPE',$accountType)
                                ->where('ID',$accountDetails['scheme_code'])
                                ->where('IS_ACTIVE',1)
                                ->get()->toArray();
            $schemeCodes = (array) current($schemeCodes);

         

            if($schemeCodes['primary_applicant_occupation'] != 'All'){
                $apply_scheme_occupation = true;
                $scheme_occupation = $schemeCodes['primary_applicant_occupation'];
            }
        }

        if(count($customerOvdDetails) > 0){

            for($seq=0;count($customerOvdDetails)>$seq;$seq++){

                $occupations = CommonFunctions::getOccupation($customerOvdDetails[$seq]->{'is_new_customer'});
                $gender = $customerOvdDetails[$seq]->{'gender'};
                $applicantDob = Carbon::parse($customerOvdDetails[$seq]->{'dob'})->age;
                $femaleAccountHolder = $accountDetails['no_of_account_holders'];
                $key = $customerOvdDetails[$seq]->{'applicant_sequence'};

                if(($apply_scheme_occupation) &&  ($customerOvdDetails[$seq]->{'applicant_sequence'} == 1)){

                        $occupationList[$key] = Arr::only($occupations,$scheme_occupation);
                }else{
                    if($gender == "M" && $applicantDob >= 18){

                        $occupationList[$key] = Arr::except($occupations,2);

                    }elseif($gender == "M" || $gender == "F" && $applicantDob < 18){
                        
                        $occupationList[$key] = Arr::only($occupations,3);

                    }elseif($gender == "F" && $femaleAccountHolder == 1){

                        $occupationList[$key] = $occupations;
                    }
                    else{
                        $occupationList[$key] = $occupations;
                    }
                }
            }
        }
    	return $occupationList;
    }

    public static function getValidateModeofOperations($formId,$account_type){
    try{
        $session_exists = 0;
        $NoOfAccountHolder = '';    
        $filterType = 'I';
        $code = '';
        if($account_type == 2){
            $filterType = 'P';
            $getFlowType = DB::table('ACCOUNT_DETAILS')->select('FLOW_TAG_1')
                                                        ->whereId($formId)
                                                        ->get()
                                                        ->toArray();

            $getFlowType = (array) current($getFlowType);   

            if($getFlowType['flow_tag_1'] == 'INDI'){
                $code = '001';
                $filterType = 'I';
        }
        }

        $applicantDob = array();
        if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails'])){
            $applicantIds = Session::get('UserDetails')[$formId]['AccountIds'];
            if(count($applicantIds) > 0){
                for($key=1;count($applicantIds) >= $key;$key++){
                    if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['dob'])){
                        $session_exists = 1;
                        $ageapp = Carbon::parse(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['dob'])->age;
                        // echo "<pre>";print_r($ageapp);exit;
                        array_push($applicantDob,$ageapp);
                        $NoOfAccountHolder = Session::get('UserDetails')[$formId]['AccountDetails']['no_of_account_holders'];
                    }
                }
            }
        }else{
            return $applicantDob;
        }
      
        $minapplicatage = min($applicantDob);

        $getmodofoperationdetails = DB::table('MODE_OF_OPERATIONS')->select('ID','OPERATION_TYPE')
                                                                    ->where('FILTER',$filterType);
        // echo "<pre>";print_r($code);exit;

        if($code != ''){
                $getmodofoperationdetails =  $getmodofoperationdetails->where('CODE',$code);
        }

        $getmodofoperationdetails= $getmodofoperationdetails->where('MIN_APPLICANTS','<=',$NoOfAccountHolder)
                                                                    ->where('MAX_APPLICANTS','>=',$NoOfAccountHolder)
                                                                    ->where('YOUNGEST_START_AGE','<=',$minapplicatage)
                                                                    ->where('YOUNGEST_END_AGE','>=',$minapplicatage)
                                                                    ->pluck('operation_type','id')
                                                                    ->toArray();
        
        return $getmodofoperationdetails;
    
        }catch(\Illuminate\Database\QueryException $e) {
        if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        $eMessage = $e->getMessage();
        CommonFunctions::addExceptionLog($eMessage, $request);
        }
}

    public static function old_getValidateModeofOperations($formId,$accountType)
    {
        $modeofoperationList = array();
        $mod = CommonFunctions::getModeOfOperations($accountType,$formId);
        if($accountType == 2){  //current account propiretpr
            return $mod;
        }

        $session_exists = 0;
        if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails']))
        {
            $applicantIds = Session::get('UserDetails')[$formId]['AccountIds'];

        if(count($applicantIds) > 0)
            {
                // foreach($applicantIds as $key => $applicantId)
                for($key=1;count($applicantIds) >= $key;$key++)
                {
                    if(isset(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['dob']))
                    {
                        $session_exists = 1;

                        $applicantDob = Carbon::parse(Session::get('UserDetails')[$formId]['customerOvdDetails'][$key]['dob'])->age;
                        $NoOfAccountHolder = Session::get('UserDetails')[$formId]['AccountDetails']['no_of_account_holders'];

                       //echo "<pre>";print_r($NoOfAccountHolder);exit;
                        if($NoOfAccountHolder == 1){
                               if($applicantDob >= 14){
                                 $modeofoperationList[$key] =   Arr::only($mod,[1]);
                                }else{

                                 $modeofoperationList[$key] = $mod;
                                }
                        }elseif($NoOfAccountHolder == 2){

                             if($applicantDob < 14){
                                 $modeofoperationList[$key] =   Arr::only($mod,[7]);
                             }elseif(($applicantDob >=14) && ($applicantDob < 18)){
                                 $modeofoperationList[$key] =   Arr::only($mod,[2,3,4,7]);
                             }elseif($applicantDob >= 18){
                                 $modeofoperationList[$key] =   Arr::only($mod,[2,3,4]);
                             }else{
                                $modeofoperationList[$key] = $mod;
                              }
                        }elseif($NoOfAccountHolder == 3){

                             if($applicantDob < 14){
                                 $modeofoperationList[$key] =   Arr::only($mod,[8]);
                             }elseif(($applicantDob >=14) && ($applicantDob < 18)){
                                 $modeofoperationList[$key] =   Arr::only($mod,[4,6,8]);
                             }elseif($applicantDob >= 18){
                                 $modeofoperationList[$key] =   Arr::only($mod,[4,6]);
                             }else{
                                 $modeofoperationList[$key] = $mod;
                             }
                        }elseif($NoOfAccountHolder == 4){

                            if($applicantDob >= 14){
                                 $modeofoperationList[$key] =   Arr::only($mod,[4,6]);
                             }else{
                                 $modeofoperationList[$key] = $mod;
                             }
                        }
                        else{
                            $modeofoperationList[$key] = $mod;
                        }
                    }else{
                        $modeofoperationList[$key] = $mod;
                    }
                }
            }
        }
         $modeofoperationList = (array) current($modeofoperationList);
        return $modeofoperationList;
    }

    public static function checkl1approval($formId)
    {
        $updateApplicationStatus = 0;

        if(Session::get('role') == 11){

              $updateAccountId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                    ->update(['APPLICATION_STATUS'=>9,'UPDATED_BY'=>Session::get('userId'),'NEXT_ROLE'=>4,]);

        }else{


        $updateAccountId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                    ->update(['APPLICATION_STATUS'=>3,'UPDATED_BY'=>Session::get('userId')]);
        }
        $saveStatus = CommonFunctions::saveStatusDetails($formId,'10','auto_approval');
        $updateApplicationStatus = CommonFunctions::updateApplicationStatus('L1',$formId);
        return $updateApplicationStatus;
    }

    public static function checkl2approval($formId)
    {
        $updateApplicationStatus = 0;
        $accountNumber = rand();
        $updateAccountId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                        ->update(['TD_ACCOUNT_NO'=>$accountNumber,'APPLICATION_STATUS'=>14]);
        $saveStatus = CommonFunctions::saveStatusDetails($formId,'21','auto_approval');
        $saveStatus = CommonFunctions::saveStatusDetails($formId,'24','auto_approval');
        $updateApplicationStatus = CommonFunctions::updateApplicationStatus('ACCOUNT_OPENED',$formId);
        return $updateApplicationStatus;
    }

    public static function declarationEmptyCheck($formId)
    {
        $vernacular = Session::get('UserDetails')[$formId]['Declarations']['vernacular'];
        $name_mismatch = Session::get('UserDetails')[$formId]['Declarations']['name_mismatch'];
        $other = Session::get('UserDetails')[$formId]['Declarations']['other'];
        if(($vernacular == 0) && ($name_mismatch == 0) && ($other == 0))
        {
            return true;
        }else{
            return false;
        }
    }

    public static function checkMinorDeclaration($userDetails)
    {
        $is_minor = false;
        $applicantsDOBs = [];
        if(count($userDetails) > 0)
        {
            if(isset($userDetails['customerOvdDetails']))
            {
                foreach ($userDetails['customerOvdDetails'] as $userData){
                    array_push($applicantsDOBs, $userData['dob']);
                }
            }
        }else{
            if(!empty(Session::get('dobArray')))
            {
                $applicantsDOBs = Session::get('dobArray');
            }
        }

        if(count($applicantsDOBs) > 0)
        {
            foreach($applicantsDOBs as $applicantsDOB)
            {
                $years = \Carbon\Carbon::parse($applicantsDOB)->age;
                if($years < 18){
                    $is_minor = true;
                    break;
                }
            }
        }
        return $is_minor;
    }

    public static function getValidateCustomerType($schemeId, $customerOvdDetails)
    {
        $customerTypeList = array();
        $customerTypes = CommonFunctions::getCustomertype($schemeId);

            for ($i=1; $i <= count($customerOvdDetails); $i++) {

                $years = \Carbon\Carbon::parse($customerOvdDetails[$i]['dob'])->age;
                if($years < 18){
                    //if the applicant is minor customer cannot be deplomat
                    $customerTypeList[$i] = Arr::except($customerTypes, 20);
                }else{
                    $customerTypeList[$i] = $customerTypes;
                }
            }

        return $customerTypeList;
    }


    public static function getTitleDetails($formId)
        {
           $titles = DB::table('TITLE')->where('is_active',1)
                                                         ->whereIn('CONSTITUTION',['I','C'])
                                                         ->orderBy('SERIAL')
                                                         ->get()->toArray();
             return $titles;
        }

   public static function getEmploymentStatus($occupationId)
        {


           switch ($occupationId) {
                    //MIGR, Unemployed, Self employed, Employed,
                    case 1:
                        return 'Salaried';
                        break;
                    case 2:
                        return 'Housewife';
                        break;
                    case 4:
                        return 'Retired';
                        break;

                    default:
                         return 'Other';
                }

        }


        public static function PadZeros($input, $maxWidth)
        {

            $fieldLength = strlen($input);

            if($fieldLength > $maxWidth){

                return $input;
            }else{

                $zerosRequired = $maxWidth - $fieldLength;
                $response = '';
                for ($i=0; $i < $zerosRequired; $i++) {
                    $response .= '0';
                }
                return $response.$input;
            }

        }

        public static function preFlightSaveAccountDetails($requestData)
        {
            //To ensure if account type is combo then td_scheme_code should be empty

            if((isset($requestData['AccountDetails'])) && (isset($requestData['AccountDetails']['account_type']))){

                 if($requestData['AccountDetails']['account_type'] != 4){
                    $requestData['AccountDetails']['td_scheme_code'] = '';
                }
            }else{

                if(Session::get('formId')!= ''){
                    $formId = Session::get('formId');
                }else{
                    $formId= '';
                }
                
                $logicExceptionLog = CommonFunctions::addLogicExceptionLog('AddAccountController', 'saveaccountdetails','AccountDetails & account_type not found', '', $formId);
            }

            return $requestData;


        }

        public static function cardRuleBasedOnSchemeType($account_type, $scheme_code,$delightkit)
        {
            if($account_type == 4){
                $account_type = 1;
            }
            
            $schemeCodeDetails =  CommonFunctions::getSchemeCodesBySchemeId($account_type, $scheme_code,$delightkit);
            $scheme_code = current($schemeCodeDetails)->scheme_code;

            if($delightkit){
                $card_types = DB::table('CARD_RULES')->select('description','id')->where('scheme_code',$scheme_code)
                                                                                ->where('for_delight', '=','Y')
                                                                                ->where('is_active',1)
                                                                                ->pluck('description','id')
                                                                                ->toArray();
            }else{
            $card_types = DB::table('CARD_RULES')->select('description','id')->where('scheme_code',$scheme_code)
                                                                             ->where('is_active',1)
                                                                             ->pluck('description','id')
                                                                             ->toArray();
            }

            return $card_types;
        }


        public static function checkAndRevertMopChange($modeofopertaions,$type){
            $mop = $modeofopertaions;
            switch ($type) {
                case 'CA':
                    break;
                case 'TD':
                    break;
                case 'SA':
                default:
                if($mop == '011'){ // if Proprietor make it 000
                    $mop = '000';
                }
                    break;
            }
            return $mop;
        } 

        // public static function CurrentAccountSchemeCode($schemeCode){
        //     $ca_scheme_code = '';
        //     switch ($schemeCode) {
        //         case '1':
        //             $ca_scheme_code = '108';
        //             break;
        //         default:
        //             break;
        //     }
        //     return $ca_scheme_code;
        // }

        public static function preCustomerId($formId){
            $isFormValidToContinue = CommonFunctions::isFormValidToContinue($formId);

            if (!$isFormValidToContinue) {
                return json_encode(['status'=>'fail','msg'=>'Invalid data for customer ID creation ','data'=>[]]);
            }

            $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('ID','APPLICANT_SEQUENCE','CUSTOMER_ID','IS_NEW_CUSTOMER','INITIAL_FUNDING_TYPE')->where('FORM_ID',$formId)
                                                               // ->where('CUSTOMER_ID', null)
                                                                ->get()->toArray();

            $checkCC = DB::table('ACCOUNT_DETAILS')->select('SOURCE')->where('ID',$formId)
                                                                    ->get()->toArray();
            $checkCC = (array) current($checkCC);

            for($i=0;count($customerDetails)>$i;$i++){

                if($customerDetails[$i]->is_new_customer == '1' && $customerDetails[$i]->customer_id == ''){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','createCustomerIdWrapper','Customer_Id',null,$customerDetails[$i]->applicant_sequence,Array($customerDetails[$i]->id),Carbon::now()->addMinutes(2));
                }

                if($customerDetails[$i]->is_new_customer == '0' && $checkCC['source'] != 'CC'){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','updateEtbCustomerId','Common',null,$customerDetails[$i]->applicant_sequence,Array($formId,$customerDetails[$i]->customer_id),Carbon::now()->addMinutes(2));
                    $notif = NotificationController::processNotification($formId,'CUSTID_CREATED');
                    $customNotif = CommonFunctions::processCustomerNotification($formId,'CUSTID_EMAIL',$customerDetails[$i]->applicant_sequence);
            }
            }
          
            ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','prefundingCallWrapper','FundingCall',null,null,Array($formId),Carbon::now()->addMinutes(2));
            DB::commit();
            return true;
        }



        public static function postCustomerIdApiQueue($formId,$customerId)
        {
            DB::commit();

            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $accountDetails = current ($accountDetails);

            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_ID',$customerId)->get()->toArray();
            $customerOvdDetails = (array) current($customerOvdDetails);

            ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','internetBankingWrapper','Common',null,$customerOvdDetails['applicant_sequence'],Array($customerId,$formId),Carbon::now()->addMinutes(2));
            // echo "<pre>";print_r($customerOvdDetails);exit;
            if($customerOvdDetails['is_new_customer'] == 1 && $accountDetails->source != 'CC'){
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','kycUpdateWrapper','Common',null,$customerOvdDetails['applicant_sequence'],Array($formId,$customerId,'PHYSICAL'),Carbon::now()->addMinutes(2));
            }

            
            DB::commit();
            return true;
        }

    public static function postFundingCreateAccount($formId){
            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $accountDetails = current ($accountDetails);
            $checkCustomerIdNull = DB::table('CUSTOMER_OVD_DETAILS')->whereNull('CUSTOMER_ID')->where('FORM_ID',$formId)->count();
            if($checkCustomerIdNull == 0){
                $customerIdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                ->where('APPLICANT_SEQUENCE',1)
                                                                ->pluck('customer_id')->toArray();
                $customerID = current($customerIdDetails);

                //For saving account
                if($accountDetails->account_type == 1 ){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','savingAccountWrapper','Saving',null,null,Array($formId,$customerID),Carbon::now()->addMinutes(2));
                }

                // echo '<pre>';print_r($accountDetails);exit;
                //Only for current account (DBSA)
                if($accountDetails->account_type == 2 && $accountDetails->scheme_code == 1){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','savingAccountWrapper','Saving',null,null,Array($formId,$customerID),Carbon::now()->addMinutes(2));
                }

                //For normal current account
                if($accountDetails->account_type == 2){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','currentAccountWrapper','Current',null,null,Array($formId),Carbon::now()->addMinutes(2));
                }

                if($accountDetails->account_type == 3){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','termdepositeAccountWrapper','TermDeposite',null,null,Array($formId,$customerID),Carbon::now()->addMinutes(2));
                }
                if($accountDetails->account_type == 4 ){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','savingAccountWrapper','Saving',null,null,Array($formId,$customerID),Carbon::now()->addMinutes(2));
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','termdepositeAccountWrapper','TermDeposite',null,null,Array($formId,$customerID),Carbon::now()->addMinutes(2));

                }

                DB::commit();
                return true;
            }

        }


        public static function postAccountIdApiQueue($formId,$accountType){

            $accountDetails = DB::table('ACCOUNT_DETAILS')->select('SOURCE','SCHEME_CODE','FLOW_TAG_1','ACCOUNT_TYPE')->whereId($formId)->get()->toArray();
            $accountDetails = (array) current($accountDetails);
            $scheme_details = CommonFunctions::getSchemeCodesBySchemeId($accountDetails['account_type'],$accountDetails['scheme_code']);
            $scheme_code = '';
            
            if(!empty($scheme_details)){
                $scheme_details = (array) current($scheme_details);
                $scheme_code = $scheme_details['scheme_code'];
            }

            $reviewTable=DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->get()->toArray();
            $reviewTable=(array)current($reviewTable);
            $Date = Carbon::now();
            $accountopeningDate = Carbon::parse($Date)->format('d/m/Y');	
            if(($accountDetails['source'] == null || $accountDetails['source'] == '') && $scheme_code == 'SB146'){
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','NiyoAccountWrapper','Common',null,null,Array($formId,$accountopeningDate),Carbon::now()->addMinutes(2));
            }         
                  
if($accountDetails['source'] == null || $accountDetails['source'] == '' ){
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','signatureAccountWrapper','Common',null,null,Array($formId,$accountType),Carbon::now()->addMinutes(2));
            }

            $fundDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('INITIAL_FUNDING_TYPE','OTHERS_TYPE')->where('FORM_ID',$formId)->get()->toArray();

            $fundDetails = (array) current($fundDetails);

            // if($accountType == '1' && $fundDetails['initial_funding_type'] == '5' && $fundDetails['others_type'] == 'zero'){
            //     ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','ftrStatusWrapper','FTR',null,null,Array($formId),Carbon::now()->addMinutes(2));
            // }

            if($accountType == '1'){
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','ftrStatusWrapper','FTR',null,null,Array($formId),Carbon::now()->addMinutes(2));
                $accountDetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_NO','MODE_OF_OPERATION', 'CARD_RULES.FINACLE_CODE','ACCOUNT_TYPE')->leftjoin('CARD_RULES','CARD_RULES.ID' ,'=','ACCOUNT_DETAILS.CARD_TYPE')->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
                if(count($accountDetails) != 0){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','cardDetailsWrappper','Common',null,null,Array($formId),Carbon::now()->addMinutes(2));
                }
            }

            if($accountType == 2){
                $entityDetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $entityDetails = (array) current($entityDetails);

                if($accountDetails['flow_tag_1'] != 'INDI'){

                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','currentAccountNameWrapper','Current',null,null,Array($entityDetails['entity_account_no'],$entityDetails['entity_name'],$formId),Carbon::now()->addMinutes(2));
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','currentAccountAddressWrapper','Current',null,null,Array($formId,$entityDetails['entity_account_no']),Carbon::now()->addMinutes(2));
                }


                if($entityDetails['proof_of_entity_address'] == '5' && $accountDetails['flow_tag_1'] != 'INDI'){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','currentGstValidation','Current',null,null,Array($formId,$entityDetails['entity_account_no'],$entityDetails['entity_add_proof_card_number']),Carbon::now()->addMinutes(2));
                }
                if($accountDetails['scheme_code'] == 1){
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','currentPreSweepWrapper','Current',null,null,Array($formId),Carbon::now()->addMinutes(2));
                }
                
                $accountDetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_NO','MODE_OF_OPERATION', 'CARD_RULES.FINACLE_CODE','ACCOUNT_TYPE')->leftjoin('CARD_RULES','CARD_RULES.ID' ,'=','ACCOUNT_DETAILS.CARD_TYPE')->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();

                if(count($accountDetails) != 0){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','cardDetailsWrappper','Common',null,null,Array($formId),Carbon::now()->addMinutes(2));
                }
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','ftrStatusWrapper','FTR',null,null,Array($formId),Carbon::now()->addMinutes(2));


            }


            if($accountType == 3){
                $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('CUSTOMER_OVD_DETAILS.ACCOUNT_NUMBER','CUSTOMER_OVD_DETAILS.IFSC_CODE','ACCOUNT_DETAILS.TD_ACCOUNT_NO','CUSTOMER_OVD_DETAILS.MATURITY_IFSC_CODE','CUSTOMER_OVD_DETAILS.INITIAL_FUNDING_TYPE')
                                                                    ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                                                                        ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                                                      ->get()->toArray();

                $customerDetails = (array) current($customerDetails);
                if($customerDetails['initial_funding_type'] == 3){
                    $ifscCode = $customerDetails['maturity_ifsc_code'];
                }else{
                    $ifscCode = $customerDetails['ifsc_code'];
                }

                $tdcheck = DB::table('TD_SCHEME_CODES')->select('TD_RD')->where('ID',$accountDetails['scheme_code'])
                                                                        ->get()
                                                                        ->toArray();
                $tdcheck = (array) current($tdcheck);

                if($tdcheck['td_rd'] == 'RD'){
                    ApiCommonFunctions::insertIntoApiQueue($formId,'Api','esbSicreation','RD',null,null,Array($formId),Carbon::now()->addMinutes(2));
                }
                ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','repaymentTDAccountWrapper','Term Deposit',null,null,Array($customerDetails['td_account_no'],$customerDetails['account_number'],$ifscCode,$formId),Carbon::now()->addMinutes(2));
            }

            DB::commit();
            return true;

        }

        public static function postDSACustomerIdApiQueue($formId,$customerId)
        {
            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $accountDetails = current ($accountDetails);

            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_ID',$customerId)->get()->toArray();
            $customerOvdDetails = (array) current($customerOvdDetails);

            ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','internetBankingWrapper','Common',null,$customerOvdDetails['applicant_sequence'],Array($customerId,$formId),Carbon::now()->addMinutes(2));

            ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','kycUpdateWrapper','Common',null,$customerOvdDetails['applicant_sequence'],Array($formId,$customerId,'VIDEO'),Carbon::now()->addMinutes(2));

            
            DB::commit();
            return true;
        }

    public static function setsourceoffund($accountType,$flowType,$scheme_code){
        $filedName = '';
        switch ($accountType) {
            case 1:
                    $filedName = 'SAVING_AND_DELIGHT';
                break;
            case 2:
                    if($flowType == 'INDI'){
                        $filedName = 'CA_INDI';
            }else{
                        $filedName = 'CA_PROP';
            }
                break;
            case 3:
                   $filedName = 'TD';
                break;
            case 4: 
                  $filedName = 'TD';
                break;
            default:
                   $filedName = 'SAVING_AND_DELIGHT';
            break;
        }

        if($flowType == 'NON_IND_HUF'){
            $filedName = 'CA_HUF';
        }

        $source_of_funds = DB::table('SOURCE_OF_FUNDS')->select('ID','SOURCE_OF_FUND')
                                                       ->where($filedName,'Y')
                                                       ->orderBy('ID','ASC');
                                                       
        if(in_array(strtoupper($scheme_code),['SB106','SB118'])){
            $source_of_funds = $source_of_funds->whereId('4');
        }
        
        $source_of_funds = $source_of_funds->pluck('source_of_fund','id')
                                                        ->toArray();

        return $source_of_funds;
    }

    public static function checkforoccupationbasedsourcefund($occupationId,$sourceoffundlist,$account_type){

        $getoccpsourceList = DB::table('OCCUPATION')->select('SOURCE_FUNDS_ID')->whereId($occupationId)->get()->toArray();
        $getsourceList = DB::table('SOURCE_OF_FUNDS')->select('ID','SOURCE_OF_FUND')
                                                        ->pluck('source_of_fund','id')
                                                        ->toArray();
        $checkcount = 0;
        if($account_type != 3){
            if(count($getoccpsourceList)>0 && count($sourceoffundlist)>0){
            $getoccpsourceList = (array)current($getoccpsourceList);
            $getoccpsourceList = explode(',',$getoccpsourceList['source_funds_id']);
            
            for($i=0;count($sourceoffundlist)>$i;$i++){
                if(in_array($sourceoffundlist[$i],$getoccpsourceList)){
                    $checkcount++;
                }else{
                    $getSourceDesc = isset($getsourceList[$sourceoffundlist[$i]]) && $getsourceList[$sourceoffundlist[$i]] != ''?$getsourceList[$sourceoffundlist[$i]] : '';
                    return ['status'=>'error','msg'=>''.$getSourceDesc.' not valid for selected occupation','data'=>[]];
                }
            }
        }else{
            return ['status'=>'error','msg'=>'Selected occupation not valid please recheck?','data'=>[]];
        }
        } 
    }

    public static function setrelationship($formId,$customerDetails,$relation=''){

        if($relation == 'CHEQUE'){
            $field = 'IS_CHEQUE_RELATION';
        }else{
            $field = 'IS_NOMINEE_RELATION';
        }
        
        $acc_const = DB::table('ACCOUNT_DETAILS')->where('id', $formId)->value('CONSTITUTION');

        if($acc_const ==''){
        $relations = DB::table('RELATIONSHIP')->where([$field=>1,'IS_ACTIVE'=>1])
                                              ->whereNotIn('ID',[45,47,48])
                                                ->pluck('display_description','id')->toArray();
        if($customerDetails->marital_status == 1){
            unset($relations[11]);
            unset($relations[12]);
            unset($relations[13]);
            unset($relations[3]);
            unset($relations[5]);
        }   
    }  
    
        if($acc_const == 'NON_IND_HUF'){
            $relations = DB::table('RELATIONSHIP')->where([$field=>1,'IS_ACTIVE'=>1])
                                                  ->whereIn('ID',[48])
                                                  ->pluck('display_description','id')->toArray();
            unset($relations[1]);
            unset($relations[2]);
            unset($relations[44]);
                                        
            }

        return $relations;
    }

    
    public static function checkapplicantnamenominee($customerDetails,$requestData){
        $allow = ['status'=>'success','msg'=>'','data'=>[]];
        $requestData = current($requestData);
        $nomineeName = $requestData['nominee_name'];
        $nomineeAge =  $requestData['nominee_age'];
        $ctn = 0;
        for($i=0;count($customerDetails)>$i;$i++){

            if($customerDetails[$i]->middle_name == ''){
                $username = $customerDetails[$i]->first_name.' '.$customerDetails[$i]->last_name;
            }else{
                $username = $customerDetails[$i]->first_name.' '.$customerDetails[$i]->middle_name.' '.$customerDetails[$i]->last_name;
            }
            if($username == $nomineeName){
                $ctn++;
            }
        }
        if($ctn != 0){
            $allow = ['status'=>'fail','msg'=>'Nominee Name not should be match applicant name.','data'=>[]];
            return $allow;
        }
        if($nomineeAge >= 120){
            $allow = ['status'=>'fail','msg'=>'Nominee DOB should not be more than 120 years.','data'=>[]];
        return $allow;
    }

        return $allow;
    }

    public static function checknomineerelationship($customerDetails,$requestData){
        $data = ['status'=>'success','msg'=>''];
        $requestData = current($requestData);
        $nomineeName = $requestData['nominee_name'];
        $fathername = $customerDetails['father_name'];
        $nomineeRelation = $requestData['relatinship_applicant'];
        $gaurdianRelation = $requestData['relatinship_applicant_guardian'];
        $guardianName = $requestData['guardian_name'];
        // echo "<pre>";print_r($requestData);exit;
        if($nomineeRelation == '1' && $customerDetails['father_spouse'] == '01'){
            if($nomineeName != $customerDetails['father_name']){
                $data = ['status'=>'fail','msg'=>'Nominee father name does not match with applicant father name'.'-'.$fathername];
            }
        }

        if($nomineeRelation == '11' && $customerDetails['father_spouse'] == '02'){
            if($nomineeName != $customerDetails['father_name']){
                $data = ['status'=>'fail','msg'=>'Nominee Spouse name does not match with applicant Spouse name'.'-'.$fathername];
            }
        }
        
        if($customerDetails['gender'] == 'M' && $customerDetails['father_spouse'] == '02'){ //male applicant
            if($nomineeRelation == '5' || $nomineeRelation == '3'){
                if($requestData['nominee_age'] < 18){ //only minor
                    if($gaurdianRelation == '2'){ //mother
                        if($guardianName != $customerDetails['father_name']){
                            $data = ['status'=>'fail','msg'=>'Guardian name does not match with Applicant spouse name'];
                        }
                    }
                }
            }
        }

        if($customerDetails['gender'] == 'F' && $customerDetails['father_spouse'] == '02'){ //female applicant
            if($nomineeRelation == '5' || $nomineeRelation == '3'){
                if($requestData['nominee_age'] < 18){ //only minor 
                    if($gaurdianRelation == '1'){ //father
                        if($guardianName != $customerDetails['father_name']){
                            $data = ['status'=>'fail','msg'=>'Guardian name does not match with Applicant spouse name'];
                        }
                    }
                }
            }
        }


        return $data;
    }

    public static function prechecksaveOvdDetails($ovdDetails){
        $status = 'success';
        if($ovdDetails['proof_of_identity'] == 9){
            $fullName = '';
            if($ovdDetails['middle_name'] != ''){
                $fullName = $ovdDetails['first_name'].' '.$ovdDetails['middle_name'].' '.$ovdDetails['last_name'];
            }else{
                $fullName = $ovdDetails['first_name'].' '.$ovdDetails['last_name'];
            }
            // echo "<pre>";print_r($ovdDetails);exit;
            $checksumekycData = strtoupper($fullName.$ovdDetails['gender'].$ovdDetails['title'].$ovdDetails['first_name'].$ovdDetails['middle_name'].$ovdDetails['last_name'].$ovdDetails['per_address_line1'].$ovdDetails['per_address_line2'].$ovdDetails['per_landmark'].$ovdDetails['per_pincode'].$ovdDetails['per_state'].$ovdDetails['per_city']);
            $checksumekycData = str_replace(' ','',$checksumekycData);
            // echo "<pre>";print_r($checksumekycData);exit;
            $hash1 = hash('sha256',$checksumekycData);
            if($hash1 != $ovdDetails['datahash']){
                $status = 'fail';
            }
        }
        // echo "<pre>";print_r($ovdDetails);exit;
        return $status;
    }

 public static function precheckETBcustomer($customerdetails,$requestData){
        try{
     
            $schmeId = isset($requestData['scheme_code']) && $requestData['scheme_code'] != ''?$requestData['scheme_code']:'';
            $custId = isset($requestData['customer_id']) && $requestData['customer_id'] != ''?$requestData['customer_id']:'';
            $customerData = $customerdetails['data']['customerDetails'];
            $getSchemeCode = DB::table('SCHEME_CODES')->select('SCHEME_CODE')->whereId($schmeId)->get()->toArray();

            $status = ['status'=>'success','msg'=>'','data'=>[]];

            if(count($getSchemeCode) >0){

                $schemeCode = (array) current($getSchemeCode);

                if($schemeCode['scheme_code'] == 'SB146'){
                    $constitution = $customerdetails['data']['customerDetails']['CUST_CONST'];

                    if($constitution != '001'){
                        return ['status'=>'fail','msg'=>'Constitution should be individual.','data'=>[]];
                    }

                    $nationalityflag = $customerdetails['data']['customerDetails']['FATCA_NATIONALITY'];

                    if($nationalityflag != 'IN'){
                        return ['status'=>'fail','msg'=>'Nationality should be IN.','data'=>[]];
                    }

                    $occupation = $customerdetails['data']['customerDetails']['OCCUPATION'];
                    $occupationCode = $customerdetails['data']['customerDetails']['CUST_OCCP_CODE'];

                    if($occupation == '' || $occupationCode == ''){
                        return ['status'=>'fail','msg'=>'Occupation can not be blank.','data'=>[]];
                    }

                    $fatcactnresidence = $customerdetails['data']['customerDetails']['FATCA_CNTRY_OF_RESIDENCE'];
                    $fatcaplacebirth = $customerdetails['data']['customerDetails']['FATCA_PLACEOFBIRTH'];
                    $fatcabirthctn = $customerdetails['data']['customerDetails']['FATCA_BIRTHCOUNTRY'];

                    if(in_array($nationalityflag,['','null'])){
                        return ['status'=>'fail','msg'=>'FATCA Nationality can not be blank.','data'=>[]];
                    }

                    if(in_array($fatcactnresidence,['','null'])){
                        return ['status'=>'fail','msg'=>'FATCA Residence can not be blank.','data'=>[]];
                    }

                    if(in_array($fatcaplacebirth,['','null'])){
                        return ['status'=>'fail','msg'=>'FATCA place of birth can not be blank.','data'=>[]];
                    }

                    if(in_array($fatcabirthctn,['','null'])){
                        return ['status'=>'fail','msg'=>'FATCA country of birth can not be blank.','data'=>[]];
                    }
                    
                    if(env('APP_SETUP') != 'DEV'){
                       
          
                        $getcountexstingcheck = DB::connection('oracle2')->table("TBAADM.GAM")->select('FORACID')
                                                                        ->where('CIF_ID',$custId)
                                                                        ->where('SCHM_TYPE','SBA')
                                                                        ->where('SCHM_CODE','SB146')
                                                                        ->where('ACCT_CLS_FLG','N')
                                                                        ->where('DEL_FLG','N')
                                                                        ->where('ENTITY_CRE_FLG','Y')
                                                                        ->where('BANK_ID','01')
                                                                        ->get()
                                                                        ->count();
                        if($getcountexstingcheck > 0){
                            return ['status'=>'fail','msg'=>'Allow only one account under SB146.','data'=>[]];
                        }

                        $checkaccountlist = DB::connection('oracle2')->table("TBAADM.GAM")->select('FORACID')
                                                                                        ->where('CIF_ID',$custId)
                                                                                        ->where('SCHM_TYPE','SBA')
                                                                                        ->where('ACCT_CLS_FLG','N')
                                                                                        ->where('DEL_FLG','N')
                                                                                        ->where('ENTITY_CRE_FLG','Y')
                                                                                        ->where('BANK_ID','01')
                                                                                        ->get()
                                                                                        ->count();
                        if($checkaccountlist>=6){
                            return ['status'=>'fail','msg'=>'Only 6 CASA accounts allowed for the customer.','data'=>[]];
                        }
                    }
                }
               return $status;
            }

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    public static function precheckdiscrepentColumn($formId,$column,$applicantId){
        try{
            $column = $column;
            if(in_array($column,['id_proof_image','proof_of_identity','proof_of_address','add_proof_image'])){

        
                $getcustomerDetails =  DB::table('CUSTOMER_OVD_DETAILS')->select('ID_PROOF_IMAGE','PROOF_OF_IDENTITY','PROOF_OF_ADDRESS','ADD_PROOF_IMAGE')->where('FORM_ID',$formId)
                                                                    ->where('APPLICANT_SEQUENCE',$applicantId)
                                                                    ->get()->toArray();

                $getcustomerDetails = (array) current($getcustomerDetails);                                                    

                if($getcustomerDetails['proof_of_identity'] == $getcustomerDetails['proof_of_address']){
                    $column = 'ALL';
                }

                if(in_array(9,[$getcustomerDetails['proof_of_identity'],$getcustomerDetails['proof_of_address']])){
                    $column = 'ALL'; 
            }
            }
            
            return $column;

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public static function precheckbranchsubmission($requestData){
        $checkStatus = ['status'=>'success','msg'=>'','data'=>[]];
        $formId = $requestData['formId'];
        $tochecklogic = 'NO';
        $getcustDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('IS_NEW_CUSTOMER','PROOF_OF_IDENTITY')->where('FORM_ID',$formId)
                                                           ->where('APPLICANT_SEQUENCE',1)
                                                           ->get()->toArray();
        $proofofId = '';
        $is_newcustomer = '';
        if(!empty($getcustDetails)){
            $getcustDetails = (array) current($getcustDetails);
            $proofofId  = $getcustDetails['proof_of_identity'];
            $is_newcustomer = $getcustDetails['is_new_customer'];
        }
        $getaccountDetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','FLOW_TAG_1','AOF_NUMBER','CONSTITUTION')->whereId($formId)
                                                                                ->get()
                                                                                ->toArray();
        if(count($getaccountDetails)>0){
            $getaccountDetails = (array) current($getaccountDetails);

            //code modified done only allow sa, sa+td , ca-ind, ca-prop, delight, hybrid firstapplicant NTB 
            if(in_array($getaccountDetails['account_type'],[1,2,4]) && $getaccountDetails['constitution'] != 'NON_IND_HUF'){
                $tochecklogic = 'YES';
            }

            if($tochecklogic == 'YES'){
                    if(isset($requestData['lead_generated']) && strtoupper($requestData['lead_generated']) == 'W'){
                    if($proofofId != 9 && $is_newcustomer != 0){
                        $checkStatus = ['status'=>'fail','msg'=>'Selected E-KYC proof of indentity when walk-in should be mandatory for primary Applicant','data'=>[]];
                    }
                }
            }
            
            $getaof_number = isset($getaccountDetails['aof_number']) && $getaccountDetails['aof_number'] !=''?$getaccountDetails['aof_number']:'';

            if($getaof_number == ''){
                $checkStatus = ['status'=>'fail','msg'=>'Blank Aof number, please try gain later.','data'=>[]];
        }
        }
        return $checkStatus;        
    }

    public static function precheckshowingciddInformation($riskDetails,$reviewDetails,$formId){
        try{
            $getdisplayfield = array();
            $checkvisible = 'N';
            $counter = 0;
            $checkpftypecounter = '0';
            $account_type = '';
            $flowTag = '';
            $flowType = '';
            $hufFlow = '';

            $getacctDetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','FLOW_TAG_1','SCHEME_CODE','FLOW_TYPE','CONSTITUTION')->whereId($formId)->get()->toArray();
            if(count($getacctDetails) > 0){
                $getacctDetails = (array) current($getacctDetails);
                $account_type = $getacctDetails['account_type'];
                $flowTag = strtoupper($getacctDetails['flow_tag_1']);
                $flowType = strtoupper($getacctDetails['flow_type']);
                $hufFlow = strtoupper($getacctDetails['constitution']);
            }
        
            for($i=0;count($riskDetails)>$i;$i++){

                if(strtoupper($getacctDetails['flow_type']) == 'ETB'){
                    $checkvisible = 'N';
                }
                
                if($account_type == 2 && $flowTag == 'INDI'){
                    $checkvisible = 'Y';
                    $getdisplayfield[$riskDetails[$i]->applicant_sequence]['sourceoffund'] = 'Y';
                }
                
                if($account_type == 2 && $flowType != 'ETB'){
                    $checkvisible = 'Y';
                    $getdisplayfield[$riskDetails[$i]->applicant_sequence]['otheroccupation'] = 'Y';
                }
            
                $seqId = $i+1;
             
                // if(isset($riskDetails[$i]->occupation) && ($riskDetails[$i]->occupation == 'OTHER - SELF EMPLOYED PROFESSIONAL' || $riskDetails[$i]->occupation == 'OTHER - SELF EMPLOYED - NON-PROFESSIONAL'  || $riskDetails[$i]->occupation == 'OTHER - TRADING' || $riskDetails[$i]->occupation == 'OTHERS') || (isset($reviewDetails['occupation-'.$seqId]) && $reviewDetails['occupation-'.$seqId] != '') || (isset($reviewDetails['other_occupation-'.$seqId]) && $reviewDetails['other_occupation-'.$seqId] != '')){
                    $getdisplayfield[$riskDetails[$i]->applicant_sequence]['otheroccupation'] = 'Y';
                    $counter++;
                // }
                // echo "test<pre>";print_r($riskDetails[$i-1]->occupation);
                
                if(isset($riskDetails[$i]->occupation) && ($riskDetails[$i]->occupation == 'OTHER - SELF EMPLOYED PROFESSIONAL' || $riskDetails[$i]->occupation == 'OTHER - SELF EMPLOYED - NON-PROFESSIONAL' || $riskDetails[$i]->occupation == 'OTHER - TRADING' || $riskDetails[$i]->occupation == 'OTHERS')){
              
                    $getdisplayfield[$riskDetails[$i]->applicant_sequence]['otheroccupationcomment'] = 'Y';
                }

                if( $hufFlow == 'NON_IND_HUF' && $riskDetails[$i]->pf_type != 'pancard'){
                    $getdisplayfield[$riskDetails[$i]->applicant_sequence]['grossincome'] = 'Y';
                    $checkpftypecounter++;
                }
               
                if($hufFlow != 'NON_IND_HUF'){
                if(in_array(Session::get('role'),[3,4])){
                    $getdisplayfield[$riskDetails[$i]->applicant_sequence]['grossincome'] = 'Y';
                    $checkpftypecounter++;
                }
            }

                // if($hufFlow == 'NON_IND_HUF' && (session("role")== 3 || session("role")== 4)){
                //     $getdisplayfield[$riskDetails[$i]->applicant_sequence]['grossincome'] = 'Y';
                //     $getdisplayfield[$riskDetails[$i]->applicant_sequence]['networth'] = 'Y';
                //     $checkpftypecounter++;
                // }
                
            }
            // if($counter > 0){
                $checkvisible = 'Y';
            // }
            if($checkpftypecounter >0){
                $checkvisible = 'Y';
            }

            return ['status'=>$checkvisible,'data'=>$getdisplayfield];
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public static function custAmendRequestIsPending($cust_id = ''){
        $result = false;
        $data = [];
        if(session("role")==11){
            return ["status"=>$result,"data"=>$data];
        }
        $days_db = DB::table("APPLICATION_SETTINGS")->select("FIELD_VALUE")->where("FIELD_NAME","AMEND_REQUEST_DAYS")->get()->toArray();
        $days = 7;
        if(count($days_db)>0){
            $days_db=(array) current($days_db);
            if(count($days_db)>0){
                if(isset($days_db["field_value"]) && !empty($days_db["field_value"])){
                    $days = $days_db["field_value"];
                }
            }
        }
        $status = [20,22,23,24,30,32,35,39,40,42,45,49,50];
        $currentDate = date('Y-m-d');
        $amend_table=[];
        $date = Carbon::parse($currentDate)->subDays($days);
        $past_date = $date->format('Y-m-d');
        $amend_table = DB::table("AMEND_MASTER")->select("AMEND_MASTER.CRF_NUMBER","AMEND_MASTER.CUSTOMER_ID")
        ->leftjoin("AMEND_QUEUE","AMEND_QUEUE.CRF","=","AMEND_MASTER.CRF_NUMBER")
        ->where("AMEND_MASTER.CUSTOMER_ID",$cust_id)
        ->whereRaw("AMEND_MASTER.CREATED_AT > TO_DATE('$past_date','YYYY-MM-DD')")
        ->whereIn("AMEND_MASTER.CRF_STATUS",$status)
        ->whereIn("AMEND_QUEUE.AMEND_ITEM",["Address as per OVD (Primary)","Communication Address"])
        ->orderBy("AMEND_MASTER.CREATED_AT","DESC")->get()->toArray();
        if(count($amend_table)>0){
            $result = true;
            $data = (array) current($amend_table);
        }
        return ["status"=>$result,"data"=>$data];
    }

    public static function checkSchemeSpecificRule($ovdDetails,$i,$formId){

        $getSessionData= Session::get('UserDetails')[$formId]['AccountDetails'];
        $schemeCodeDetails = CommonFunctions::getSchemeCodesBySchemeId($getSessionData['account_type'],$getSessionData['scheme_code']);
        $schemeCodeDetails = current($schemeCodeDetails); 
           
        $custData=DB::table('CUSTOMER_OVD_DETAILS')->select('APPLICANT_SEQUENCE','CUSTOMER_ID')->where('FORM_ID',$formId)->orderby('APPLICANT_SEQUENCE','ASC')->get()->toArray();
        $gender=isset($ovdDetails['gender'])&& $ovdDetails['gender'] !=''?$ovdDetails['gender']:''; 
        $custData= $custData[$i-1];      

        $scheme_code= isset($schemeCodeDetails->scheme_code) && $schemeCodeDetails->scheme_code !='' ? $schemeCodeDetails->scheme_code:'';
       
        if($scheme_code == 'SB150' && $gender !='F' && $custData->applicant_sequence == 1){ // check for primary account
         return ['status'=>'fail','msg'=>'Primary applicant should be female'];

        }
        
        if($scheme_code == 'SB151' && $custData->customer_id == '' && $custData->applicant_sequence != 1){
                        
            return ['status'=>'fail','msg'=>'Secondary applicant should be ETB'];
        }

        if($scheme_code == 'SB151' && $gender =='M' && $custData->applicant_sequence == 2){
            return ['status'=>'fail','msg'=>'Secondary applicant should be female'];
        }
    

        if(isset($custData->customer_id) && $scheme_code == 'SB151' && $custData->applicant_sequence  == 2){
            $gridDataDetails = CommonFunctions::getAccountDetailsBasedOnCustID($custData->customer_id);      
                $sb150found = false;         
                foreach($gridDataDetails as $checkSchemeCode){
                    if(isset($checkSchemeCode->schm_code) && $checkSchemeCode->schm_code == 'SB150'){ // check for secondary account
                           $sb150found = true;             
                        	
                    }
                }
            
            if(!$sb150found){
                return ['status'=>'fail','msg'=>'Secondary applicant SB150 account not found.'];
            }
        }
    }

    public static function precheckforautodiscrepentColumn($formId,$column,$applicantId){
        
        $selected_cols = [];
        // if($applicantId!="1"){
        //     return $selected_cols;
        // }
        $column = explode("-",$column)[0];
        $checkcolumnsforauto=["proof_of_address","proof_of_current_address","add_proof_image","current_add_proof_image"];
        $per_communication_arr = ["proof_of_address"=>"proof_of_current_address",'add_proof_image'=>"current_add_proof_image",'add_proof_card_number'=>"current_add_proof_card_number",'per_address_line1'=>"current_address_line1",'per_address_line2'=>"current_address_line2",'per_pincode'=>"current_pincode",'per_country'=>"current_country",'per_state'=>"current_state",'per_city'=>"current_city",'per_landmark'=>"current_landmark"];

        $per_add_arr = ["proof_of_address",'add_proof_image','add_proof_card_number','per_address_line1','per_address_line2','per_pincode','per_country','per_state','per_city','per_landmark','passport_driving_expire_permanent'];

        $current_add_arr = ["proof_of_current_address",'current_add_proof_image','current_add_proof_card_number','current_address_line1','current_address_line2','current_pincode','current_country','current_state','current_city','current_landmark'];

        $req_cols = ["ADDRESS_FLAG","ADDRESS_PER_FLAG"];
        $ovdDetails = DB::table("CUSTOMER_OVD_DETAILS")->select($req_cols)->where("FORM_ID",$formId)->orderBy("ID","ASC")->get()->toArray();
        $ovdDetails = (array) $ovdDetails;
       
        foreach ($ovdDetails as $key => $value) {
            $v = (array) $value;
            $acc_id_num = $key + 1;
            $selected_cols[$acc_id_num]=[];
            $address_per_flag = false;
            if($applicantId==2){
                if($v["address_per_flag"]!=1){
                    if(in_array($column,["proof_of_address","add_proof_image"])){
                        if($acc_id_num==2){
                            array_push($selected_cols[$acc_id_num],$current_add_arr);
                        }
                    }else if($acc_id_num==2 && in_array($column,$per_add_arr)){
                        array_push($selected_cols[$acc_id_num],[$per_communication_arr[$column]??""]);
                    }
                }
            }else{
                if($v["address_per_flag"]==1){
                    $address_per_flag = true;
                    if(in_array($column,$checkcolumnsforauto)){
                        if($acc_id_num==2 && in_array($column,["proof_of_address","add_proof_image"])){
                            array_push($selected_cols[$acc_id_num],$per_add_arr);
                        }
                    }else{
                        if(in_array($column,["proof_of_address","add_proof_image"])){
                            array_push($selected_cols[$acc_id_num],$per_add_arr);
                        }else if(in_array($column,$per_add_arr)){
                            array_push($selected_cols[$acc_id_num],[$column]);
                        }
                    }
                }
                if($v["address_flag"]==1){
                    if(in_array($column,$checkcolumnsforauto)){
                        if($acc_id_num==2 && !($v["address_per_flag"]==1 && $v["address_flag"]==1) || in_array($column,["proof_of_current_address","current_add_proof_image"])){
                            array_push($selected_cols[$acc_id_num],$current_add_arr);
                        }elseif($acc_id_num==1){
                            array_push($selected_cols[$acc_id_num],$current_add_arr);
                        }elseif($v["address_per_flag"]==1 && in_array($column,["proof_of_address","add_proof_image"]) && ($v["address_flag"]!=1 || ($v["address_flag"]==1 && $ovdDetails[0]->address_flag==1))){
                            array_push($selected_cols[$acc_id_num],$current_add_arr);
                        }
                        
                    }else if($ovdDetails[0]->address_flag==1){
                        array_push($selected_cols[$acc_id_num],[$per_communication_arr[$column]??""]);
                    }else if(in_array($column,$current_add_arr)){
                        array_push($selected_cols[$acc_id_num],[$column]);
                    }
                }else if($acc_id_num==2 && $ovdDetails[1]->address_per_flag == 1){
                    if(in_array($column,["proof_of_current_address","current_add_proof_image"])){
                        array_push($selected_cols[$acc_id_num],$current_add_arr);
                    }else if(in_array($column,$current_add_arr)){
                        array_push($selected_cols[$acc_id_num],[$column]);
                    }
                }
            }
        }
        return $selected_cols;
    }

    public static function specialCharValidations($proofcard_Number){
       
        if(preg_match('/[^a-z_\-0-9]/i',$proofcard_Number)){
        $msg="Please enter valid card number";
        $status="fail";
        return["status" => $status, "msg" => $msg];
        }
                 
    }


    public static function validatedproofList($requestData){
        
        $proofList = array();
        $add_type = explode('-',$requestData['add_type']);
    
      
           if(isset($add_type[0]) && ($add_type[0] == 'address_per_flag')){

               if($requestData['proof_value'] == 1){
                   $proofList = CommonFunctions::normal_proof('PER_ADDRESS_PROOF');
               }else{
                   $proofList = CommonFunctions::huf_proof('HUF_PROOF');
               }
           }
        return $proofList;
    }
}

?>
