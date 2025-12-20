<?php
namespace App\Helpers;

use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;
use File;
use Carbon\Carbon;
use Crypt,Cache,Session;
use Illuminate\Support\Str;
use DB;
use DateTime;
use GuzzleHttp\Psr7\Request;
// use GuzzleHttp\Client;

class Api {

    public static function hrapiconnection($hrmsNo)
    {
        $employeeApiDetails = array();
        $client = new \GuzzleHttp\Client();
        $url = CommonFunctions::getapplicationSettingsDetails('url');
        $client_id = CommonFunctions::getapplicationSettingsDetails('client_id');
        $client_key = CommonFunctions::getapplicationSettingsDetails('client_key');
        $encrypt_key = CommonFunctions::getapplicationSettingsDetails('encrypt_key');
        try{
			$guzzleClient = $client->request('POST',$url,['auth' =>[$client_id,$client_key],
                                    'json'=>['hrmsNo'=>EncryptDecrypt::AES128Encryption($hrmsNo,$encrypt_key)]]);
		}catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }
        $response = json_decode($guzzleClient->getBody(),true);
        $employeeApiDetails = json_decode(EncryptDecrypt::AES128Decryption($response['employeeDetails'],$encrypt_key));
        return $employeeApiDetails;
    }

    public static function customerdetails($url,$type,$clientId)
    {
        try{
        $customerDetails = '';
        
        $status = 'Success';
        $serviceName = 'FINACLE_CUSTOMER_DETAILS';
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $payload = json_encode([$type=>$clientId]);
        $requestID = EncryptDecrypt::JWEEncryption($payload);
        $client = new \GuzzleHttp\Client();
		$requestTime = Carbon::now();
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>[$requestID],
                                                'exceptions'=>false
                                            ]);
        		
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        $response = json_encode(json_decode($guzzleClient->getBody()));
        $customerDetails = EncryptDecrypt::JWEDecryption($response);
        $serviceName = 'FINACLE_PAN_DETAILS';
        if($type == "panBasedDetailsReq")
        {
            if(!isset($customerDetails['panBasedDetailsRes']))
            {
                $status = "Error";
            }
        }else{
            if(!isset($customerDetails['status']))
            {
                $status = "Error";
            }
        }

        if($customerDetails == ''){
            return ['status'=>'fail','msg'=>'Api is not Working. please try again later.','data'=>[]];
        }
		 
        $saveService = CommonFunctions::saveApiRequest($serviceName,$url,$requestID,$response,$payload,$customerDetails,'',$responseTime);

        if($status == "Success")
        {
            return ['status' => "Success",'data' => $customerDetails];
        }else{
            return ['status' => "Error",'code' => $response['httpCode'] ,'message' => $response['message']
                                                                    ,'moreInformation' => $response['moreInformation']];
        }

        return $customerDetails;

            }catch(\Throwable $e){
                if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
                $eMessage = $e->getMessage();
                CommonFunctions::addLogicExceptionLog('Helpers/Api','customerdetails',$eMessage,'','');
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
        }

    public static function accountNumbersWithBalance($customerId,$formId)
    {
        $accountDetails = array();
        $url = config('constants.APPLICATION_SETTINGS.ACCOUNT_ENQUIRY_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $RequestUUID = CommonFunctions::getRandomValue(9);
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');

        $data = Array(
                        'header' => Array
                            (
                                'apiVersion' => '1.0',
                                'appVersion' => "1.0.0.0",
                                'channelId' => 'CUBE',
                                'isEnc' => 'Y',
                                'cifId' => $customerId,
                                'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
                                'languageId' => 1,
                                'os' => 'iOS',
                                'model' => 'iPhone X',
                                'osVersion' => '11.2',
                                'osVersion' => '11.2',
                                'requestUUID' => strval($customerId.'_'.'ANWB'),
                                'serReqId' => 'ESBGeneralAccountEnquiry',
                                'sessionId' => '5932216656835406787',
                                'timeStamp' => $timestamp,
                            ),
                        'request' => Array
                            (
                                'cif' => $customerId,
                                'schmCode' => 'ALL',
                                'pageNo' => '01'
                            )
                    );
        $payload = json_encode(['gatewayRequest'=>$data]);
        $requestID = EncryptDecrypt::JWEEncryption($payload);
        $client = new \GuzzleHttp\Client();
		
		$requestTime = Carbon::now();	
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>[$requestID],
                                                'exceptions'=>false
                                            ]);
        
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        $response = json_encode(json_decode($guzzleClient->getBody()));
        $responseValidate = (json_decode($response));

        // Need to Modified if fail  ------------
        if(isset($responseValidate->httpCode) && $responseValidate->httpCode != '200'){
          
            if (isset($responseValidate->httpCode) && isset($responseValidate->httpMessage)) {
                $msg = $responseValidate->httpMessage;
            }else{
                $msg = '';
            }

            $saveService = CommonFunctions::saveApiRequest('ACCOUNT_ENQUIRY',$url,$requestID,$response,$payload,$msg.$response,$formId, $responseTime);
            return 'Error! '.$msg;
        }
        try{
            $accountDetails = EncryptDecrypt::JWEDecryption($response);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return ['status'=>'Error','data'=>'','errorCode'=>'','message'=>'API Error: '];
        }

        $saveService = CommonFunctions::saveApiRequest('ACCOUNT_ENQUIRY',$url,$requestID,$response,$payload,$accountDetails,$formId, $responseTime);
        

        return $accountDetails['gatewayResponse'];
    }

     public static function accountNumberDetails($accountNo, $formId = '')
    {
        try{
        $accountDetails = array();
        $url = config('constants.APPLICATION_SETTINGS.CUSTACCT_DETAILS_ENQUIRY_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $RequestUUID = CommonFunctions::getRandomValue(9);
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');

        $data = Array (
                      'accountNumber' => $accountNo
                      );                            
        $requestID = EncryptDecrypt::JWEEncryption(json_encode($data)); 
        $client = new \GuzzleHttp\Client(); 
	
		$requestTime = Carbon::now();
	
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>[$requestID],
                                                'exceptions'=>false
                                            ]);
                		
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
		
		$response = json_encode(json_decode($guzzleClient->getBody()));
        $accountDetails = EncryptDecrypt::JWEDecryption($response);
                
        $saveService = CommonFunctions::saveApiRequest('ACCOUNT_DETAILS',$url,$requestID,$response,$requestID,
                                                                                            $accountDetails,$formId, $responseTime);        
        return $accountDetails;
        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','accountNumberDetails',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }
	
	
    //------------------------------Function will remove after check 19-FEB-2021------------------//

    // public static function accountBalanceEnquiry($customerId,$accountNumber)
    // {
    //     $accountDetails = array();
    //     $url = config('constants.APPLICATION_SETTINGS.ACCOUNT_BALANCE_ENQUIRY_URL');
    //     $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
    //     $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
    //     $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
    //     $data = Array(
    //                     'header' => Array
    //                         (
    //                             'apiVersion' => '1.0',
    //                             'appVersion' => "1.0.0.0",
    //                             'channelId' => 'CUBE',
    //                             'isEnc' => 'Y',
    //                             'cifId' => '100933779',
    //                             'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
    //                             'languageId' => 1,
    //                             'os' => 'iOS',
    //                             'model' => 'iPhone X',
    //                             'osVersion' => '11.2',
    //                             'osVersion' => '11.2',
    //                             'requestUUID' => '895547861',
    //                             'serReqId' => 'ESBAccountBalanceEnquiry',
    //                             'sessionId' => '5932216656835406787',
    //                             'timeStamp' => '1519731538269'
    //                         ),
    //                     'request' => Array
    //                         (
    //                             'customerID' => $customerId,
    //                             'accountNumber' => $accountNumber
    //                         )
    //                 );
    //     $payload = json_encode(['gatewayRequest'=>$data]);
    //     $requestID = EncryptDecrypt::JWEEncryption($payload);
    //     $client = new \GuzzleHttp\Client();
    //     $guzzleClient = $client->request('POST',$url,
    //                                         [   'headers' =>[
    //                                                 'Content-Type'=>'application/json',
    //                                                 'X-IBM-Client-secret'=>$client_key,
    //                                                 'X-IBM-Client-Id'=>$client_id,
    //                                                 'authorization'=>$authorization
    //                                             ],
    //                                             'json'=>[$requestID],
    //                                             'exceptions'=>false
    //                                         ]);
    //     //fetching response from server
    //     $response = json_encode(json_decode($guzzleClient->getBody()));
    //     $accountDetails = EncryptDecrypt::JWEDecryption($response);
    //     $saveService = CommonFunctions::saveApiRequest('ACCOUNT_BALANCE_ENQUIRY',$url,$requestID,$response,
    //                                                                                         $payload,$accountDetails);
    //     return $accountDetails;
    // }

    public static function panIsValid($requestData)
    {
        $client = new \GuzzleHttp\Client();
        $url = config('constants.APPLICATION_SETTINGS.NSDL_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $payload = json_encode(['pannumber'=>$requestData['pancard_no']]);
        $pancard_no = EncryptDecrypt::JWEEncryption($payload);
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        
		$requestTime = Carbon::now();
	
		$guzzleClient = $client->request('POST',$url,
                                            ['headers' =>['Content-Type'=>'application/json',
                                            'X-IBM-Client-secret'=>$client_key,
                                            'X-IBM-Client-Id'=>$client_id,
                                            'authorization'=>$authorization],
                                            'json'=>[$pancard_no],
                                            'exceptions'=>false
                                            ]);
    
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
	
        $response = json_encode(json_decode($guzzleClient->getBody()));
        $pancard = EncryptDecrypt::JWEDecryption($response);
        $saveService = CommonFunctions::saveApiRequest('NSDL_API',$url,$pancard_no,$response,
                                                                                        $payload,$pancard, '', $responseTime);
        return $pancard;
    }

    public static function checkdedupe($customerOvdData,$aof_number,$formId)
    {
        try{
            
            $url = config('constants.APPLICATION_SETTINGS.LIVY_GENERATE_QID_URL');
            $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');
            
            $LIVYGenerateQIDResponse = '';
            $referenceNumber = '';
            $passportNumber = '';
            $drivingLicence = '';
            $voterId = '';
            $queryId = '';
            $errorCode = '';
            
            //etb cutomer 
            $freeText1 = '';
            $freeText2 = '';

            $global_Details = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
        
            $global_Details = current($global_Details);
        
            if($global_Details->constitution == "NON_IND_HUF" && $customerOvdData["applicant_sequence"] == "2"){
           
                $gender_ = "O";
            }else{
                $gender_ = $customerOvdData['gender'];
            }

            if($customerOvdData['is_new_customer'] == '0'){
                // $url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
                // $customerDetaills = Self::customerdetails($url,'customerID',$customerOvdData['customer_id']);
                $ETB_Detaills = DB::table("ETB_CUST_DETAILS")->where([["FORM_ID",$formId],["CUSTOMER_ID",$customerOvdData['customer_id']]])->get()->toArray();
                $freeText1 = 'YES';
                $freeText2 = $customerOvdData['customer_id'];
                if(!empty($ETB_Detaills))
                {
                    $ETB_Detaills = (array) current($ETB_Detaills);
                    $referenceNumber = $ETB_Detaills['aadhar_num'];
                    $passportNumber = $ETB_Detaills['passport_num'];
                    $voterId = $ETB_Detaills['voter_num'];
                    $drivingLicence = $ETB_Detaills['driving_lic_num'];
                }
            }else{
            if($customerOvdData['proof_of_identity'] == 9 || $customerOvdData['proof_of_address'] == 9){	// eKYC cases
                $referenceNumber = $customerOvdData['add_proof_aadhaar_ref_number'];
            }
            if($customerOvdData['proof_of_address'] == 1)
            {
                        $referenceNumber =  $customerOvdData['add_proof_aadhaar_ref_number'];
            }
            if($customerOvdData['proof_of_address'] == 2)
            {
                        $passportNumber = $customerOvdData['add_proof_card_number'];
            }
            if($customerOvdData['proof_of_address'] == 3)
            {
                        $drivingLicence =  $customerOvdData['add_proof_card_number'];
            }
            if($customerOvdData['proof_of_address'] == 6)
            {
                    $voterId = $customerOvdData['add_proof_card_number'];
                } 
            }
            
            
            $current_timestamp = Carbon::now()->timestamp;
            $timestamp = substr($current_timestamp, 1);
            
            if(env('APP_SETUP') == 'PRODUCTION'){
                
                $inquiryAgencyId = '1';
            }else{
                
                $inquiryAgencyId = '1';
            }
            
            $RequestUUID = CommonFunctions::getRandomValue(4);
            
            $data = Array(
                'header' => Array
                (
                    'apiVersion' => '1.0',
                    'appVersion' => "1.0.0.0",
                    'channelId' => 'CUBE',
                    'isEnc' => 'Y',
                    'cifId' => '100933779',
                    'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
                    'languageId' => 1,
                    'requestUUID' => strval($aof_number.'_'.'CHKDEDUPE'),
                    'serReqId' => 'LIVYGenerateQID',
                    'sessionId' => '5932216656835406787',
                    //'timeStamp' => '1519731538269'
                    'timeStamp' => $current_timestamp,
                    
                ),
                'request' => Array
                (
                    "custName" => $customerOvdData['first_name'].' '.$customerOvdData['middle_name'].' '.$customerOvdData['last_name'],
                    "gender" => $gender_,
                    "dateOfBirth" => Carbon::parse($customerOvdData['dob'])->format('d-m-Y'),
                    "panNumber" => $customerOvdData['pancard_no'],
                    "referenceNumber" => $referenceNumber,
                    "passportNumber" => $passportNumber,
                    "mobileNumber" => $customerOvdData['mobile_number'],
                    "drivingLicence" => $drivingLicence,
                    "voterId" => $voterId,
                    "emailId" => $customerOvdData['email'],
                    "address" => $customerOvdData['per_address_line1'],
                    "cityName" => $customerOvdData['per_city'],
                    "pinCode" => $customerOvdData['per_pincode'],								
                    "inRequester" => "",
                    "productType" => "",
                    //"entity" => "",
                    "freeText1" => $freeText1,
                    "freeText2" => $freeText2,
                    "freeText3" => "",
                    "sourceType"=>"Cube",
                    "entity"=>"Individual",
                    "sourceAppNum"=>$aof_number,
                    "inquiryAgencyId"=> $inquiryAgencyId,
                    )
                );
                $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
                
                $payload = json_encode(['gatewayRequest'=>$data]);
                $client = new \GuzzleHttp\Client();
                
                $requestTime = Carbon::now();	
                
                $guzzleClient = $client->request('POST',$url,
                [   'headers' =>[
                    'Content-Type'=>'application/json',
                    'X-IBM-Client-secret'=>$client_key,
                    'X-IBM-Client-Id'=>$client_id,
                    'authorization'=>$authorization
                ],
                'json'=>['gatewayRequest'=>$data],
                // 'json'=>[$payload],
                'exceptions'=>false
            ]);
            
            $responseTime = Carbon::now()->diffInSeconds($requestTime); 
            
            $response = $guzzleClient->getBody();
            $response = json_decode($response,true);
            if(isset($response['gatewayResponse'])){
                $response = $response['gatewayResponse'];
                if($response['status']['isSuccess'] == "true"){
                    $encryptedResponse = json_encode($response['response']);
                    $LIVYGenerateQIDResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                    // echo "<pre>";print_r($LIVYGenerateQIDResponse);exit;
                    $queryId = $LIVYGenerateQIDResponse['data']['queryId'];
                    $status = 'Success';
                    $data = $queryId;
                    $message = $response['status']['message'];
                }else{
                    $encryptedResponse = json_encode($response);
                    $status = 'Error';
                    $errorCode = $response['status']['statusCode'];
                    $message = $response['status']['message'];
                }
                $saveService = CommonFunctions::saveApiRequest('DEDUPE_QID',$url,$payload,$encryptedResponse,
                json_encode($data),$response,$formId, $responseTime);
                return ['status'=>$status,'data'=>$queryId,'errorCode'=>$errorCode,'message'=>$message];
            }
            else{
                $saveService = CommonFunctions::saveApiRequest('DEDUPE_QID',$url,$payload,$response,
                json_encode($data),$response,$formId, $responseTime);
                return ['status'=>'Error','data'=>'','errorCode'=>'','message'=>'API Error: '.http_build_query($response,'',', ')];
            }
            
        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','checkdedupe',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
    


    public static function createcustomerid($customerData)
    {  
        try{
            $is_huf = false;
        $customerID = '';
        $url = config('constants.APPLICATION_SETTINGS.CUSTOMER_ID_CREATION_URL');
        // $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        // $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $client_id = config('constants.APPLICATION_SETTINGS.CRM_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CRM_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');
        // $current_state_code = CommonFunctions::getStateCodeByZipCode($customerData['current_pincode']);
        // $permanent_state_code = CommonFunctions::getStateCodeByZipCode($customerData['per_pincode']);
        $uuid = CommonFunctions::getRandomValue(20);

        //$current_timestamp = Carbon::now()->subDays(50)->timestamp;
        //$current_timestamp_t = Carbon::now()->subDays(50)->format('Y-m-d\TH:i:s.v');
        $current_timestamp_t = Carbon::now()->format('Y-m-d\TH:i:s.v');

        if(env('APP_SETUP') == 'PRODUCTION'){
            
            $current_timestamp_t_c = Carbon::now()->format('Y-m-d\TH:i:s.v');
        }else{
            $current_timestamp_t_c = '2021-02-02T13:13:25.258';
        }


        $isStaff = $customerData['customer_account_type'] == 3 ? 'Y' : 'N';
        if($isStaff == 'Y'){
            $StaffEmpID = $customerData['empno'];
        }else{

            $StaffEmpID = '';
        }
        $globalaccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($customerData['form_id'])->get()->toArray();
        $globalaccountDetails = current($globalaccountDetails);
        if($globalaccountDetails->constitution == "NON_IND_HUF" && $customerData["applicant_sequence"] == "2"){
            $is_huf = true;
        }
        if($is_huf){
            $primary_acc = DB::table("CUSTOMER_OVD_DETAILS")->where([["FORM_ID",$customerData['form_id']],["applicant_sequence","1"]])->get()->toArray();
            $primary_acc = (array) current($primary_acc);
        
        $proofOfIdentity = DB::table('OVD_TYPES')
            ->where('ID',$primary_acc['proof_of_identity'])
            ->get()->toArray();
            $proofOfIdentity = current($proofOfIdentity); 
        }else{   
            $proofOfIdentity = DB::table('OVD_TYPES')
                                    ->where('ID',$customerData['proof_of_identity'])
                                    ->get()->toArray();
        $proofOfIdentity = current($proofOfIdentity); 
        }

                            
        
        if($customerData['passport_driving_expire'] != ''){

             $expDt = \Carbon\Carbon::parse($customerData['passport_driving_expire'])->format('Y-m-d\TH:i:s.v');
        }else{
            $expDt = '';
        }
        if($customerData['id_psprt_dri_issue'] != ''){

            $psprtissueDate = \Carbon\Carbon::parse($customerData['id_psprt_dri_issue'])->format('Y-m-d\TH:i:s.v');
       }else{
            $psprtissueDate = '';
       }

        $issueDt = $current_timestamp_t;
        

        $maritalStatus = DB::table('MARITAL_STATUS')->whereId($customerData['marital_status'])->get()->toArray();
        $maritalStatus = current($maritalStatus);

        $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$customerData['form_id'])
        ->where('ACCOUNT_ID',$customerData['id'])
        ->get()->toArray();

        $riskDetails = current($riskDetails);
        switch(trim($riskDetails->risk_classification_rating)){
            case 'Low':
                  $riskText = '1';
                break;
            case 'Medium':
                $riskText = '2';
                break;
            default:
                $riskText = '3';
                break;
        }
		
		if(isset($riskDetails->pep) && trim($riskDetails->pep)!='No'){
			$riskText = '4';
		}
				

        //$fatcaResidence = $riskDetails->residence;       

        $riskCountries = DB::table('COUNTRIES')
                                    ->where('ID',$riskDetails->country_of_birth)
                                    ->get()->toArray();
        $riskCountries = current($riskCountries);

        $fatcaResidence = DB::table('COUNTRIES')
                                    ->where('ID',$riskDetails->residence)
                                    ->get()->toArray();
        $fatcaResidence = (array) current($fatcaResidence);

        $fatcaNationality = DB::table('COUNTRIES')
                                    ->where('ID',$riskDetails->citizenship)
                                    ->get()->toArray();
        $fatcaNationality = current($fatcaNationality);

        $fatcaNationality=isset($fatcaNationality->country_code) && $fatcaNationality->country_code !=''? $fatcaNationality->country_code:"";
        //  echo "<pre>";print_r($fatcaNationality);exit;
       

        $occupation = DB::table('OCCUPATION')->where('ID',$riskDetails->occupation)->get()->toArray();
        $occupation = current($occupation);

        $empStatus = Rules::getEmploymentStatus($riskDetails->occupation);

        $dateStr = $customerData['dob'];
        $dateArray = date_parse_from_format('Y-m-d', $dateStr);


        $sol_id = DB::table('ACCOUNT_DETAILS')->whereId($customerData['form_id'])->pluck('branch_id')->toArray();
        $sol_id = current($sol_id);

        $globalaccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($customerData['form_id'])->get()->toArray();
        $globalaccountDetails = current($globalaccountDetails);

        $grossIncomeValue = DB::table('GROSS_INCOME')->select('VALUE_TO_BE_PASSED')->whereId($riskDetails->gross_income)->get()->toArray();
        $grossValue = '';
        if(count($grossIncomeValue) > 0){
            $grossIncomeValue = (array) current($grossIncomeValue);
            $grossValue = $grossIncomeValue['value_to_be_passed'];
        }

        $curr_pcsDetails = CommonFunctions::getZipDetailsByZipCode($customerData['current_pincode'], $customerData['current_country']);
        if(count($curr_pcsDetails) < 1){
           $curr_pcsDetails['citycode'] = '';
           $curr_pcsDetails['statecode'] = '';
        }

        $per_pcsDetails = CommonFunctions::getZipDetailsByZipCode($customerData['per_pincode'], $customerData['per_country']);
        if(count($per_pcsDetails) < 1){
           $per_pcsDetails['citycode'] = '';
           $per_pcsDetails['statecode'] = '';
        }

        // echo "<pre>";print_r($curr_pcsDetails);exit;
        $pfDocCodeValue = '';
        $aadharLinkStatus = DB::table('PAN_DETAILS')->select('AADHAARSEEDINGSTATUS')
                                                    ->where('PANNO',$customerData['pancard_no'])
                                                    ->orderBy('id','DESC')
                                                    ->get()->toArray();

        if(count($aadharLinkStatus)>0){

            $aadharLinkStatus = (array) current($aadharLinkStatus);

            if(isset($aadharLinkStatus['aadhaarseedingstatus']) && ($aadharLinkStatus['aadhaarseedingstatus'] == "NA" || $aadharLinkStatus['aadhaarseedingstatus'] == "Y")){
                $pfDocCodeValue = 'RPAN'; 
            }
            elseif($is_huf){
                $pfDocCodeValue = 'RPAN';
            }else{
                $applicantAge = Carbon::parse($customerData['dob'])->age;
    			if($applicantAge < 80){
                $pfDocCodeValue = 'INPAN';

    			}else{
    				$pfDocCodeValue = 'RPAN';
                }
            }
        }

        $tdsSlab = CommonFunctions::getTDSSlab($customerData,$pfDocCodeValue);
        // echo "<pre>";print_r($tdsSlab);exit;
        // if($customerData['father_spouse'] == 01){
        //     $fatherName = $customerData['father_name'];
        // }else{
        //     $fatherName = '';
        // }

        $getMotherName = CommonFunctions::getFML_Name($customerData['mother_full_name']);
        $getFatherSpouseName = CommonFunctions::getFML_Name($customerData['father_name']);

        $segmentDetails = db::table('SEGMENT')
                                    ->where('SEGMENT_CODE',$globalaccountDetails->segment_code)
                                    ->get()->toArray();

        $segmentDetails = current($segmentDetails);        
        
        $title = DB::table('TITLE')->whereId($customerData['title'])->get()->toArray();
        $title = (array) current($title);

        $years = \Carbon\Carbon::parse($customerData['dob'])->age;

        if($years < 18){           
           $is_minor = 'Y';
           $custom_accountType = '03';
        }else{
            $is_minor = 'N';
            $custom_accountType = '01';
        }
         
        
        $custom_POA = Rules::PadZeros($customerData['proof_of_identity'],2);
        //$custom_accountType = Rules::PadZeros($globalaccountDetails->account_type,2);

        $current_addres_proof = Rules::PadZeros($customerData['proof_of_address'],2);
        $minorTomajor = \Carbon\Carbon::parse($customerData['dob'])->addYears(18);        
        $minorTomajorstr = $minorTomajor->format('Y-m-d\TH:i:s.v');
        $RequestUUID = CommonFunctions::getRandomValue(13);
        $header_timestamp = Carbon::now()->subDays(50)->format('dmY');
        $identityRefNum = $customerData['id_proof_card_number'];

        // If Identity is Aadhar share the valut no.        
        if($customerData['proof_of_identity'] == 1){ //
            $identityRefNum = $customerData['id_proof_aadhaar_ref_number'];
        }
        if($is_huf){
            // if($customerData['id_proof_card_number']==""){
            //     $identityRefNum = $primary_acc['id_proof_card_number'] ?? "";
            // }
            if($customerData['pancard_no'] != ""){
                $identityRefNum = $customerData['pancard_no'] ?? "";
            }else{
                $identityRefNum = "";
            }
            $IdentificationType = "PAN";
            $DocCode = "RPAN";
            $TypeCode = "PAN";
        }else{
            $IdentificationType=$proofOfIdentity->identification_type ?? '';
            $DocCode=$proofOfIdentity->id_proof_code ?? "";
            $TypeCode=$proofOfIdentity->doc_type ?? "";
        }
		
		// 001 for Individual 095 for Delight;		
		if($globalaccountDetails->delight_scheme == 5){
			$custTypeCode = '095'; 
		}else{
			$custTypeCode = '001';
		}	
        if($is_huf){
            $custTypeCode = '601';
        }
		
        // $aadharLinkStatus = DB::table('PAN_DETAILS')->select('AADHAARSEEDINGSTATUS')
        //                             ->where('PANNO',$customerData['pancard_no'])
        //                             ->orderBy('id','DESC')
        //                             ->get()->toArray();
        // $aadharLinkStatus = (array) current($aadharLinkStatus);

        // if($aadharLinkStatus['aadhaarseedingstatus'] == "NA" || $aadharLinkStatus['aadhaarseedingstatus'] == "Y"){
        //     $pfDocCodeValue = 'RPAN'; 
        // }else{
        //     $pfDocCodeValue = 'INPAN';
        // }

        if($customerData['pf_type'] == 'pancard'){
            $pfIdType = 'PAN';
            $pfDocCode = $pfDocCodeValue;
            $pfRefNum = $customerData['pancard_no'];
            $typeCode = 'PAN';
			$freeText6 = '000';
        }else{
            $pfIdType = 'Unique Identification Number';
            $pfDocCode = 'FOM60';
            $pfRefNum = 'FORM60'; 
            $typeCode = 'FORM60';
			$freeText6 = '001';
        }
        $ChildCustId = "M00500004";
        if($is_huf){
            $ChildCustId = "M00500003";   
        }
        if ($is_minor == 'Y') {
            $guardInfo = array (
                    "ChildEntity" =>"CONTACT",
                    "ChildEntityType" => "Retail",
                    "ChildCustId" => "$ChildCustId",
                    "Relationship" => "Guardian",
                    "RelationshipCategory" => "Social",
                    "GuardCode" => "D",
                    "ParentEntity" => "CUSTOMER",
                    "ParentEntityType" => "Retail"
                );
        }else{
            $guardInfo = array();
        } 

        $userDetails = CommonFunctions::getUserDetails($globalaccountDetails->created_by);
        if (isset($userDetails['rm_code']) && $userDetails['rm_code'] != '') {
            $rm_code = $userDetails['rm_code'];
        }else{
            $rm_code = 'EOD0009';
        }

        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $empHRMSnumber = $userDetails['hrmsno'];
        }else{
            $empHRMSnumber = '';
        }

        $identityArray = [ 
                        //Below Block for Identification Details
                        "IdentificationType" => $IdentificationType,
                        "CountryOfIssue" => "IN",
                        "DocCode" => $DocCode,
                        "IssueDt" => $issueDt,
                        "TypeCode" => $TypeCode,
                        "PlaceOfIssue" => "I005",
                        //"ReferenceNum" => $customerData['id_proof_card_number'],
                        "ReferenceNum" => $identityRefNum,
                        "preferredUniqueId" => "N",
                        "IDIssuedOrganisation" => "",
                        ];

        if($is_huf){
            $fatcaIdType =  "C";
        }else{
        $fatcaIdType =  $proofOfIdentity->id_type ?? "";
        }
        $fatcIdNum =  $identityRefNum;
        $checkPassIssuedate = '';
        if( isset($proofOfIdentity->id) && ($proofOfIdentity->id == 2 || $proofOfIdentity->id == 3)){ // Only for Passport and Driving License
			if($expDt != ''){            
				$identityArray["ExpDt"] = $expDt;
			}
            
        if($is_huf){
                if($expDt == '')	{
                    $identityArray["ExpDt"] ="2099-01-20T00:00:00.000";
            }
        }

            if($psprtissueDate != ''){            
				$identityArray["IssueDt"] = $psprtissueDate;
                // $psprtissueDate=
                $checkPassIssuedate = ["PassportIssueDt" =>$psprtissueDate];
			}	
		}
        
        $EntityDoctData = Array();

        $panDocData =  [
            //Below Block for PAN/FOMR60 Details
            "IdentificationType" => $pfIdType,
            "CountryOfIssue" => "IN",
            "DocCode" => $pfDocCode,
            "IssueDt" => $current_timestamp_t,
            "TypeCode" => $typeCode,
            "PlaceOfIssue" => "I005",
            "ReferenceNum" =>  $pfRefNum,
            "preferredUniqueId" => "Y",
            "IDIssuedOrganisation" => "",
        ];

       
        if($customerData['proof_of_identity'] == 9 || $customerData['proof_of_address'] == 9){
            $getAadharDetails = CommonFunctions::getaadhardocumentForKyc($customerData,$issueDt);
            // echo "<pre>";print_r($getAadharDetails);exit;
            $getAadharDetails['preferredUniqueId'] = 'N';
            $identityArray['preferredUniqueId'] = 'N';
            if(!$is_huf){
            array_push($EntityDoctData,$identityArray);
            }
            array_push($EntityDoctData,$panDocData);
            array_push($EntityDoctData,$getAadharDetails);
        }else{
            if(!$is_huf){
            array_push($EntityDoctData,$identityArray);
            }
            array_push($EntityDoctData,$panDocData);
        }

        //sb146 add ekyc address proof of document --07012025

        $getschmdec = DB::table('SCHEME_CODES')->select('SCHEME_CODE')->whereId($globalaccountDetails->{'scheme_code'})->get()->toArray();

        if(!empty($getschmdec)){
            $getschmcode = (array) current($getschmdec);

            if($getschmcode['scheme_code'] == 'SB146' && $customerData['proof_of_address'] == 9){
                $proofOfAddress = DB::table('OVD_TYPES')
                                    ->where('ID',$customerData['proof_of_address'])
                                    ->get()->toArray();
                $proofOfAddress = current($proofOfAddress); 

                $addproofaddressArray = [ 
                    "IdentificationType" => $proofOfAddress->identification_type,
                    "CountryOfIssue" => "IN",
                    "DocCode" => $proofOfAddress->id_proof_code,
                    "IssueDt" => $issueDt,
                    "TypeCode" => $proofOfAddress->doc_type,
                    "PlaceOfIssue" => "I005",
                    "ReferenceNum" => $customerData['add_proof_card_number'],
                    "preferredUniqueId" => "N",
                    "IDIssuedOrganisation" => "",
                    ];
                array_push($EntityDoctData,$addproofaddressArray);
            }
        }
        
        $prefName = substr($customerData['first_name'].' '.$customerData['last_name'],0,50);
       
        if($is_huf){
            $firstNm = '';
            $customerData['last_name']=$customerData['first_name'];
            $customerData['first_name'] = "";
            $customerData['gender'] = "O";
            // $maritalStatus->code = "SINGL";
            $custConsCode = "003";
        }else{
            $custConsCode = "001";
            $firstNm = $customerData['first_name'];
        }



        $custName = $customerData['first_name'].' '.$customerData['middle_name'].' '.$customerData['last_name'];
        $custName = CommonFunctions::getspacehandlingName($custName);
    
        $data = Array(
                    "header" => Array(
                                    "isEnc" => "N",
                                    "apiVersion" => "1.0",
                                    "cifId" => "100212262",
                                    "languageId" => "1",
                                    "channelId" => "CUBE",
                                    "requestUUID" => strval($uuid) ,
                                    "sVersion" => "20",
                                    "serReqId"  => "CRMCustomerCreation",
                                    "timeStamp" => $header_timestamp
                                ),

                    "request" => Array(
                             "FIXML" => Array(
                                        "Header" => Array(
                                               "RequestHeader" => Array(
                                                        
                                                            "MessageKey" => Array(
                                                                "RequestUUID" => "REQ_".$RequestUUID,
                                                                "ServiceRequestId" => "RetCustAdd",
                                                                "ServiceRequestVersion" => "10.2",
                                                                "ChannelId" => "COR"
                                                            ),
                                                            "RequestMessageInfo" => Array(
                                                                "BankId"=>"01",
                                                                "MessageDateTime"=>$current_timestamp_t
                                                            ),
                                                            "Security" => Array( 

                                                                     "Token" =>Array(

                                                                         "PasswordToken" => (object)[]
                                                                     ), 
                                                            )
                                                        

                                                ),
                                               /*---RequestHeader End------*/
                                        ),
                                        /*---Header End------*/
                            "Body" => Array(
                                "RetCustAddRequest" => Array(
                                    "RetCustAddRq" => Array(
                                        "CustDtls" => Array(
                                            "CustData" => Array(
                                        "Pan" => $pfRefNum,
                                        "Salutation" => $title['title'],
                                        "FirstName" =>  $firstNm,
                                        "LastName" => $customerData['last_name'],
                                        "MiddleName" => $customerData['middle_name'],
                                        "Name" => $custName,
										"CustType" => $custTypeCode,  
										"MaidenName" => $customerData['mothers_maiden_name'] != ''?$customerData['mothers_maiden_name']:'.',
                                        "ShortName" => substr($customerData['short_name'],0,10),
                                        "Occupation" => $occupation->code,
                                        "Email" => $customerData['email']!=null ? $customerData['email'] : '',
                                        "Gender" => $customerData['gender'],
                                        "BirthDt" => $dateArray['day'],
                                        "BirthMonth" => $dateArray['month'],
                                        "BirthYear" => $dateArray['year'],
                                        "DateOfBirth" => Carbon::parse($customerData['dob'])->format('Y-m-d\TH:i:s.v'),
                                        "CountryOfBirth" => $riskCountries->country_code,
                                        "PlaceOfBirth" => $riskDetails->place_of_birth,
                                        "IsMinor" => $is_minor,
                                        "IsCustNRE" => "N",
                                        "CustStatusChangeDt" => $current_timestamp_t_c,
                                        "MaidenNameOfMother" => $customerData['mothers_maiden_name'] != ''?$customerData['mothers_maiden_name']:'.',
                                        "AddrDtls" => Array(
                                                   [
                                                      "AddrLine1" => $customerData['per_address_line1'],
                                                      "AddrLine2" => $customerData['per_address_line2'],
                                                      "AddrLine3" => $customerData['per_landmark'],
                                                      "AddrCategory" => "Home",
                                                      "City" => $per_pcsDetails['citycode'],
                                                      "Country" => $per_pcsDetails['countrycode'],
                                                      "FreeTextLabel" => $customerData['first_name'].' '.$customerData['last_name'],
                                                      "PrefAddr" => "N",
                                                      "PrefFormat" => "FREE_TEXT_FORMAT",
                                                      "StartDt" => $current_timestamp_t_c,
                                                      "State" => $per_pcsDetails['statecode'],
                                                      "PostalCode" => $per_pcsDetails['pincode'],
                                                      "HoldMailFlag" => "N",
                                                    ],
                                                    [
                                                      "AddrLine1" => $customerData['current_address_line1'],
                                                      "AddrLine2" => $customerData['current_address_line2'],
                                                      "AddrLine3" => $customerData['current_landmark'],
                                                      "AddrCategory" => "Mailing",
                                                      "City" => $curr_pcsDetails['citycode'],
                                                      "Country" => $curr_pcsDetails['countrycode'],
                                                      "FreeTextLabel" => $customerData['first_name'].' '.$customerData['last_name'],
                                                      "PrefAddr" => "Y",
                                                      "PrefFormat" => "FREE_TEXT_FORMAT",
                                                      "StartDt" => $current_timestamp_t_c,
                                                      "State" => $curr_pcsDetails['statecode'],
                                                      "PostalCode" => $curr_pcsDetails['pincode'],
                                                      "HoldMailFlag" => "N",
                                                      ]
                                                  ),
                                        "ChargeLevelCode" => "001",
                                        "CreatedBySystemId" => "FIVUSR",
                                        "Language" => "INFENG",
                                        "DefaultAddrType" => "Mailing",
                                        "Manager" => $rm_code, //RM_CODE (default: SAC1273)(EOD0009)
                                        "MinorToMajorDt" => $minorTomajorstr,
                                        "NativeLanguageCode" => "INFENG",
                                        // "PrefName" => $customerData['first_name'].' '.$customerData['last_name'],
                                        "PrefName" => $prefName,
                                        "PrimarySolId" => $sol_id,
                                        "Region" => "OTHERS",
                                        "RelationshipOpeningDt" => $current_timestamp_t_c,
                                        "SegmentationClass" => $segmentDetails->segment_code,
                                        "StaffFlag" => $isStaff,
                                        "FatcaRemarks" => "01",
                                        "StaffEmployeeId" => $StaffEmpID,
                                        "SubSegment" => $segmentDetails->segment_value,
                                        "TaxDeductionTable" => $tdsSlab,
                                        "IsEbankingEnabled" => "N",
                                        "TradeFinFlag" => "N",
                                        "PhoneEmailDtls" => Array(
                                                            [
                                                            "Email" => $customerData['email']!=null ? $customerData['email'] : '',
                                                            "PhoneEmailType" => "COMMEML",
                                                            "PhoneOrEmail" => "EMAIL",
                                                            "PrefFlag" => "Y"
                                                        ],
                                                        [
                                                            'Email' => '',
                                                            'PhoneEmailType' => 'CELLPH',
                                                            'PhoneOrEmail' => 'PHONE',
                                                            'PhoneNum' => $customerData['mobile_number'],
                                                            'PhoneNumCountryCode' => '91',
                                                            'PhoneNumLocalCode' => $customerData['mobile_number'],
                                                            'PrefFlag' => 'Y'
                                                        ]
                                                        ),
                                                // "PassportIssueDt" => $psprtissueDate, 
                                                $checkPassIssuedate
                                            )
                                        ),
										/*-----CustDtls End here-------*/
                                    "RelatedDtls" => Array(
                                                "DemographicData" => [
                                                                "EmploymentStatus" => $empStatus,
                                                                "MaritalStatus" => $maritalStatus->code,
                                                                "Nationality" => "IN",
                                                                "ResidenceCountry" => "IN",
                                                                // "AnnualSalaryIncome" => 'INR',
                                                                "AnnualSalaryIncome"=> $grossValue,
                                                                "cuAnnualSalaryIncome"=> "INR"
                                                ],

                                                "EntityDoctData"=>$EntityDoctData,
                                                // "EntityDoctData" =>Array(
                                                //                $identityArray,
                                                //                 [
                                                //                 //Below Block for PAN/FOMR60 Details
                                                //                 "IdentificationType" => $pfIdType,
                                                //                 "CountryOfIssue" => "IN",
                                                //                 "DocCode" => $pfDocCode,
                                                //                 "IssueDt" => $current_timestamp_t,
                                                //                 "TypeCode" => $typeCode,
                                                //                 "PlaceOfIssue" => "MUM",
                                                //                 "ReferenceNum" =>  $pfRefNum,
                                                //                 "preferredUniqueId" => "N",
                                                //                 "IDIssuedOrganisation" => "",
                                                //                 ]
                                                //                 // $getAadharDetails
                                                //             ),
                                                                
                                                "PsychographicData" => [
                                                                "CustCurrCode" => "INR",
                                                                "PsychographMiscData"  => [
                                                                        "StrText10" => "INR",
                                                                        "Type" => "CURRENCY",
                                                                        "DTDt1" => "2099-12-31T00:00:00.000"
                                                                    ],
                                                                "preferred_Locale" => "en_US"
                                                                ],
                                                "RelationshipDtls" => [$guardInfo],
                                                "CoreInterfaceInfo"=>[
                                                                "FreeText1" => $empHRMSnumber,
                                                                "FreeText3" => "",
                                                                "FreeText5" => "",
                                                                "FreeText6" => $freeText6,
                                                                "FreeText7" => "",
                                                                "FreeText8" => $riskText,
                                                                "FreeText10" => $riskText,
                                                                "FREECODE1" => "",
                                                                "FREECODE3" => "",
                                                                "FREECODE6" => "",
                                                                "FREECODE7" => ""
                                                                ]
                                    ),/*-----RelatedDtls End here-------*/
                                    "RetCustAdd_CustomData" => Array(
                                        "CKYCTeam" => "01",
                                        "FS_FirstName"=> $getFatherSpouseName['first_name'],
                                        "FS_MiddleName"=> $getFatherSpouseName['middle_name'],
                                        "FS_LastName"=> $getFatherSpouseName['last_name'],
                                        "MotherFirstName" => $getMotherName['first_name'],
                                        "MotherMiddleName" => $getMotherName['middle_name'],
                                        "MotherLastName" => $getMotherName['last_name'],
                                        "ResidentialStatus" => "",
                                        "FatherOrSpouse" => $customerData['father_spouse'],
                                        "AddressType" => "01",
                                        "ProofOfAddress" => $custom_POA,
                                        "AccountType" =>  $custom_accountType,
                                        "FatherName" => ($customerData['father_spouse'] == '01' ? $customerData['father_name'] : ''),       
                                        "SpouseName" => ($customerData['father_spouse'] == '02' ? $customerData['father_name'] : ''),  
                                        "FatcaIdType" => $fatcaIdType,
                                        "FatcIdNum" => $fatcIdNum,
                                        "Attestation" => "",
                                        "CurrentAddrProof" => $current_addres_proof,
                                        "FatcaNationality" => $fatcaNationality,
                                        "FatcaResidenceCountry" => $fatcaResidence['country_code'],
                                        "AgriculturalIncome" => "0.000000",
                                        "NonAgriculturalIncome" => "0.000000",
                                        "custCons" => $custConsCode,
                                        "custConsCode" => $custConsCode,
                                        //"grossIncome" => $grossValue,
                                        "OtherLimits" => "0.000000",
                                        "TotalNonFundBase" => "0.000000",
                                        "TotalFundBase" => "0.000000",
                                        "CustNetWorth" => "0.000000",
                                        "CustomerType" => $custTypeCode,
                                        "CustomerTypeCode" => $custTypeCode
                                    ),/*-----RetCustAdd_CustomData End here-------*/
                                )
                            )
                        )

                    )
                        /*---FIXML End------*/
                )
                    
            );
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
        // echo "<pre>";print_r((['gatewayRequest'=>$data]));
        //$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);
		
		$requestTime = Carbon::now();
	
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);

        
		$responseTime = Carbon::now()->diffInSeconds($requestTime);     

		$saveService = CommonFunctions::saveApiRequest('CUSTOMER_ID',$url,$payload,json_encode($response),json_encode($data),$response,$customerData['form_id'], $responseTime);
		

        // If all ok and we get custID. Update and return
		if(isset($response['gatewayResponse']['status']['isSuccess']) && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ER000')
        {
            $encryptedResponse = json_encode($response['gatewayResponse']['response']['data']);
            //$customerIdResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
															   
            $customerIdResponse = json_decode($encryptedResponse);						
	
            $customerID = $customerIdResponse->CustId;			
			if($customerID){			
				$updateCustomerId = DB::table('CUSTOMER_OVD_DETAILS')->whereId($customerData['id'])->update(['customer_id'=>$customerID]); 
                Rules::postCustomerIdApiQueue($customerData['form_id'],$customerID);
				DB::commit();
				return ['status'=>'Success','data'=>$customerID];
			}			
        } 
		
		// Else something wrong! return with error message or generic.
        if(isset($response['gatewayResponse']['status']['message'])){
            return ['status'=>'Error','data'=>$response['gatewayResponse']['status']['statusCode'],
                                                        'message'=>$response['gatewayResponse']['status']['message']];			 
         }else{
            return ['status'=>'Error','data'=>'API', 'message'=>json_encode($response)];			 
            }

        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            $formId = isset($customerData['form_id']) && $customerData['form_id'] !=''?$customerData['form_id']:'';
            CommonFunctions::addLogicExceptionLog('Helpers/Api','createcustomerid',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function createaccountid($formId,$customerID)
    {
        try{
            $is_huf = false;
        $updateAccountId = '';
        $nomineeDetails = DB::table('NOMINEE_DETAILS')
                                    ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT')
                                // ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT',
                                //     'RELATIONSHIP.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                                // ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $nomineeDetails = (array) current($nomineeDetails);


        //add extra
        $gaurdianDetails = DB::table('NOMINEE_DETAILS')->select('REL.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $gaurdianDetails = (array) current($gaurdianDetails);
        // echo "<pre>";print_r($nomineeDetails);exit;
        $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
        $accountDetails = (array) current($accountDetails);

        $is_huf = false;
        if($accountDetails['constitution'] == "NON_IND_HUF"){
            $is_huf = true;
        }
        if($is_huf){

            //for huf applicant sequence should reverse account to be created with huf cust id and joint applicant should be karta hence desending
        $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
            ->orderBy('APPLICANT_SEQUENCE','DESC')
            ->get()->toArray();
            $customerOvdData = (array) current($customerOvdDetails);
            //only for huf senario
           
            $customerID =  $customerOvdDetails[0]->customer_id;
        }
        else{
            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
								->orderBy('APPLICANT_SEQUENCE','ASC')
								->get()->toArray();
        $customerOvdData = (array) current($customerOvdDetails);

        }

        // $reviewTable=DB::table('REVIEW_TABLE')->where('FORM_ID',$requestData['form_id'])->get()->toArray();
        $reviewTable=DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->get()->toArray();
        $reviewTable=(array)current($reviewTable);

        // echo "<pre>";print_r($accountDetails);exit;
        //only CA229 for selected data
        if($accountDetails['account_type'] == 2){
            if($accountDetails['scheme_code'] == 1){
                $accountDetails['scheme_code'] = 14;
            }
        }
        $sbchemeCodeDetails = DB::table('SCHEME_CODES')->whereId($accountDetails['scheme_code'])->get()->toArray();
        $sbchemeCodeDetails = (array) current($sbchemeCodeDetails);

        $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)->where('APPLICANT_SEQUENCE',1)->get()->toArray();
        $riskDetails = current($riskDetails);

        $annual_turnover = config('constants.ANNUAL_TURNOVER')[$riskDetails->annual_turnover];
        $annual_turnover = str_replace(' Lakh', '00000', $annual_turnover);
        $annual_turnover = str_replace(' Crore', '0000000', $annual_turnover);  
        $annual_turnover = str_replace('Upto ', '', $annual_turnover);

        //special case for CA229
        if($sbchemeCodeDetails['scheme_code'] == 'CA229'){
            $sbchemeCodeDetails['scheme_code']='SB129';
        }

        $url = config('constants.APPLICATION_SETTINGS.ACCOUNT_ID_CREATION_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');


        if(isset($nomineeDetails['nominee_exists']) && $nomineeDetails['nominee_exists'] == 'yes'){
            $nominee_pcsDetails = CommonFunctions::getZipDetailsByZipCode($nomineeDetails['nominee_pincode'], $nomineeDetails['nominee_country']);
            $guardian_pcsDetails = CommonFunctions::getZipDetailsByZipCode($nomineeDetails['guardian_pincode'], $nomineeDetails['guardian_country']);
            
            $nomineeYears = \Carbon\Carbon::parse($nomineeDetails['nominee_dob'])->age;

            if($nomineeYears < 18){           
               $nominee_is_minor = 'Y';
            }else{
                $nominee_is_minor = 'N';
            }

            $nomineeFlag = 'Y';
        }else{
           $nomineeFlag = 'N';
        }

        //$uuid = CommonFunctions::getRandomValue(10);
		$uuid = uniqid().uniqid();

        $current_timestamp = Carbon::now()->timestamp;

        $sol_id = DB::table('ACCOUNT_DETAILS')->whereId($formId)->pluck('branch_id')->toArray();
        $sol_id = current($sol_id);

        $branch_name = DB::table('BRANCH')->where('BRANCH_ID',$sol_id)->pluck('branch_name')->toArray();
        $branch_name = current($branch_name);

        
        $shortName = trim($customerOvdData['short_name']);

        if($shortName == ''){
            $shortName = substr($customerOvdData['last_name'],0,10);
        }else{
            $shortName =  substr($customerOvdData['short_name'],0,10);
        }

        $GenLedgerSubHeadCode = CommonFunctions::getGenLedgerSubHeadCode($formId);
        $DespatchMode = CommonFunctions::getDespatchMode($formId);
        // echo "<pre>";print_r($nomineeDetails);exit;
        $gaurdianCode = $gaurdianDetails['relatinship_applicant_guardian'];

        if($nomineeFlag == 'Y'){
          $nomineeDetails = Self::getNomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdianCode);
       }else{          
          $nomineeDetails = Array();
       }

        //echo "<pre>";print_r($GenLedgerSubHeadCode);exit;

       $mop = DB::table('MODE_OF_OPERATIONS')->select('CODE')
                                             ->where('ID',$accountDetails['mode_of_operation'])
                                             ->get()->toArray();
       $mop = current($mop);
       $mop = $mop->code;
       $mop = Rules::checkAndRevertMopChange($mop,'SA');

		$userDetails = CommonFunctions::getUserDetails($accountDetails['created_by']);
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $empHRMSnumber = $userDetails['hrmsno'];
        }else{
            $empHRMSnumber = '';
        }
				
        $labelCode = Array(
                        Array(   
                        "isMultiRec" => "Y",
                            "srlNum" => "0",
                            "acctLable" => $customerOvdData['label_code'],
                            "delFlg" => "N",
                        )
                    );
	 
       $scaccountdata = Array(
                          "AOFNUMBER" => $accountDetails['aof_number'],
                          "INTCRFLG" => "S",
                          "INTDRFLG" => "S",
                          "MODEOFOPER" => $mop,
						  "SOURCECODE" => $empHRMSnumber,
                          "FREETEXT5" => $annual_turnover,
                        //   "lable" => $labelCode,
                    );
        
        $custName = $customerOvdData['first_name'].' '.$customerOvdData['middle_name'].' '.$customerOvdData['last_name'];
        $accountName = CommonFunctions::getspacehandlingName($custName);
        $accountName = substr($accountName,0,80);

       if($is_huf){
        $shortName = $accountName;
    }
        

        $data = Array(
                    'header' => Array(
                                    // 'apiVersion' => '1.0',
                                    // 'channelId' => 'CUBE',
                                    // 'isEnc' => 'Y',
                                    // 'cifId' => $customerID,
                                    // 'requestUUID' => $uuid,
                                    // 'serReqId' => 'ESBAcctNumGen',
                                    // 'timeStamp' => $current_timestamp
                        
                         /*=============NEW API================*/

                                    'apiVersion' => '1.0.0.0',
                                    'appVersion' => '1.0.0.0',
                                    'channelId' => 'CUBE',
                                    'cifId' => $customerID,
                                    'deviceId' => 'af8f1289-bac1-3063-bd2f-d865a99a5d6e',
                                    'modelName' => 'Motorola XT1022',
                                    'languageId' => '1',
                                    'os' => 'Android',
                                    'osVersion' => '5.1',
                                    'requestUUID' => strval($uuid),
                                    'serReqId' => 'SBACCTADD_V1',
                                    'sVersion' => '13',
                                    'timeStamp' => strval($current_timestamp),
                                    'isEnc' => 'N'
                                ),
                    'request' => Array(
                        /*=============OLD API================*/
                                    // 'identifier' => 'SBO',
                                    // 'customerId' => $customerID,
                                    // 'schemeCode' => $sbchemeCodeDetails['scheme_code'],
                                    // 'currencyCode' => 'INR',
                                    // 'solid' => $sol_id,
                                    // 'glSubHeadCode' => '14001',
                                    // 'statementMode' => 'S',
                                    // 'accountName' => $customerOvdData['first_name'].' '.$customerOvdData['middle_name'].' '.$customerOvdData['last_name'],
                                    // 'accountShortName' => $shortName,

                        /*=============NEW API================*/
                                    'custId' => $customerID,
                                    'solId' => $sol_id,
                                    'currency' => 'INR',
                                    'schmCode' => $sbchemeCodeDetails['scheme_code'],
                                    'schmType' => 'SBA',
                                    'GenLedgerSubHeadCode' => $GenLedgerSubHeadCode,
                                    'acctName' => $accountName,
                                    'acctShortName'=>substr($shortName, 0,9),
                                    'despatchMode' => $DespatchMode,
                                    'bankId' => '01',
                                    'bankName' => 'DCB',
                                    'branchName' => $branch_name,
                                    'drIntMethodInd' => 'E',
                                    'crIntMethodInd' => 'E',
                                    'drIntMethodCode' => 'SBINT',
                                    'crIntMethodCode' => 'SBINT',
                                    'code' => 'SBINT',
                                    'curCode' => 'INR',
                                    'acctStmtMode' => 'N',
                                    'cal' => '00',
                                    'type' => 'D',
                                    
									//'NomineeDetails' => Array($nomineeDetails) -- commented after Prasad's comment									
									'NomineeDetails' => $nomineeDetails
                                      

                                )
                );

		 
            if(count($customerOvdDetails) >= 2){
                $relationArray = array();
                $joinArray = array();
                $i = 1;
            for($i=1; $i < count($customerOvdDetails) ; $i++){
                $relationParty =    
                                    Array(
                                            "RelPartyType" => "J",
                                            "RelPartyTypeDesc"=> "",
                                            "RelPartyCode"=> "",
                                            "RelPartyCodeDesc"=> "",
                                            "custId"=> $customerOvdDetails[$i]->customer_id,
                                            "PersonName"=> Array(
                                                "LastName"=> "",
                                                "FirstName"=> "",
                                                "MiddleName"=> "",
                                                "Name"=> "",
                                                "TitlePrefix"=> "",
                                                "CustName"=> ""
                                            ),
                                            "RelPartyContactInfo"=> Array(
                                                "PhoneNum"=> Array(
                                                    "TelephoneNum"=> "",
                                                    "FaxNum"=> "",
                                                    "TelexNum"=> ""
                                                ),
                                                "EmailAddr"=> "",
                                                "PostAddr"=> Array(
                                                    "Addr1"=> "",
                                                    "Addr2"=> "",
                                                    "Addr3"=> "",
                                                    "City"=> "",
                                                    "StateProv"=> "",
                                                    "PostalCode"=> "",
                                                    "Country"=> "",
                                                    "AddrType"=> ""
                                                ),
                                                "RecDelFlg"=> ""
                                            ),
                                        );
            

              $JointApplicantData = [
                  "isMultiRec"=> "Y",
                  "SRLNUM"=> "0",
                  "CIF_ID" => $customerOvdDetails[$i]->customer_id,
                  "PASSSHEETFLG" => "N",
                  "STANDINGINSTRUCTION"=> "N",
                  "DEPOSITNOTICEFLG"=> "N",
                  "LOANOVRDUNOTICEFLG"=> "N",
                  "XCLUDEFORCOMBSTMTFL"=> "N"
              ];
                  array_push($relationArray, $relationParty);
                  array_push($joinArray, $JointApplicantData);
              }
              $data['request']['RelPartyRec'] = $relationArray;
              $data['request']['SBAcctAdd_CustomData'] = $scaccountdata;
              $data['request']['SBAcctAdd_CustomData']['JOINT'] = $joinArray;
       
          
            }else{
              $data['request']['SBAcctAdd_CustomData'] = $scaccountdata;
            }
                  
          
          
        
      //  echo "<pre>";print_r(json_encode(['gatewayRequest'=>$data]));exit;
        //$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
		
        $payload = json_encode(['gatewayRequest'=>$data]);
        $client = new \GuzzleHttp\Client();
		
		$requestTime = Carbon::now();	
		
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                // 'json'=>[$payload],
                                                'exceptions'=>false
                                            ]);
        
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        // $response = json_decode($guzzleClient->getBody(),true);
        
		$response = json_decode($guzzleClient->getBody(),true);
                  


            $saveService = CommonFunctions::saveApiRequest('ACCOUNT_ID',$url,$payload,json_encode($response),
                                                                                json_encode($data),$response,$formId, $responseTime);
        //echo "<pre>";print_r(json_encode($response['gatewayResponse']));exit;   
		
        if($response['gatewayResponse']['status']['isSuccess'] && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ER000')    
        {
            $encryptedResponse = json_encode($response['gatewayResponse']['response']);
            //$accountIdResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
            //$accountNumber = $accountIdResponse['accountNumber'];
            if(isset($response['gatewayResponse']['response']['data']['XMLNSC']['FIXML']['Body']['Error']['FISystemException']['ErrorDetail']['ErrorDesc'])){                 
                 return ['status'=>'Error','data'=>$response['gatewayResponse']['response']['data'],
                                                    'message'=>$response['gatewayResponse']['response']['data']['XMLNSC']['FIXML']['Body']['Error']['FISystemException']['ErrorDetail']['ErrorDesc']];
            }

            //echo "<pre>";print_r($response['gatewayResponse']['response']['data']['ErrorDesc']);exit;
            if(isset($response['gatewayResponse']['response']['data']['ErrorDesc'])){               
               return ['status'=>'Error','data'=>$response['gatewayResponse']['response']['data'],
                                                    'message'=>$response['gatewayResponse']['response']['data']['ErrorDesc']];

            }else{
                $accountIdResponse = json_decode($encryptedResponse);
                $accountIdResponse = (array) current($accountIdResponse);
                $accountNumber = $accountIdResponse['SBAcctAddResponse']->SBAcctAddRs->SBAcctId->AcctId;
                if(empty($accountNumber)){
            		return (['status'=>'fail','message'=>'Error! Please try again','data'=>[]]);
                }
            }   
                $updateAccountId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                ->update([
                                                                            'ACCOUNT_NO'=>$accountNumber,
                                                                            'APPLICATION_STATUS'=>14
                                                                        ]);
                if($updateAccountId){
                    $updateApplicationStatus = CommonFunctions::updateApplicationStatus('ACCOUNT_OPENED',$formId);
                    
                    if($accountDetails['account_type'] == 1 || $accountDetails['account_type'] == 4){
                        Rules::postAccountIdApiQueue($formId,1);
                    }

                    return ['status'=>'Success','data'=>$accountNumber];
                }
              //echo "<pre>";print_r($accountNumber);exit;
                 return $accountNumber;

        }else{
            if(isset($response['gatewayResponse']['status']['message'])){
				$returnMsg = $response['gatewayResponse']['status']['message'];
				if(isset($response['gatewayResponse']['response']['data']['ErrorDesc'])){
					$returnMsg .= ' <br> '.$response['gatewayResponse']['response']['data']['ErrorDesc'];
				}
            return ['status'=>'Error','data'=>$response['gatewayResponse']['status']['statusCode'],
                                                    'message'=>$returnMsg];
            }else{
                return ['status'=>'Error','data'=>$response['gatewayResponse']['status']['statusCode'],
                                                    'message'=>'API Server returned Blank Error Message'];
            }
        }

        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','createaccountid',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function checklivystatus($queryId,$formId)
    {
        try{
        $url = config('constants.APPLICATION_SETTINGS.LIVY_CHECK_STATUS_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

        $RequestUUID = CommonFunctions::getRandomValue(4);

        $current_timestamp = Carbon::now()->timestamp;

        $data = Array(
                        'header' => Array
                            (
                                'apiVersion' => '1.0',
                                'appVersion' => "1.0.0.0",
                                'channelId' => 'CUBE',
                                'isEnc' => 'Y',
                                'cifId' => '100933779',
                                'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
                                'languageId' => 1,
                                //'requestUUID' => '895547861',
                                'requestUUID' => strval($queryId.'_'.'CHECKLIVY'),
                                "sVersion" => "20",
                                "serReqId" => "LIVYCheckStatus",
                                'sessionId' => '5932216656835406787',
                                'timeStamp' => strval($current_timestamp),
                            ),
                        'request' => Array
                            (
                                "queryId" => $queryId
                            )
                    );

        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);
        $client = new \GuzzleHttp\Client();
		
		$requestTime = Carbon::now();	
		
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        
		$responseTime = Carbon::now()->diffInSeconds($requestTime);
        
        if (!isset(json_decode($guzzleClient->getBody(),true)['gatewayResponse'])){
            return  []; 
        }

        $response = json_decode($guzzleClient->getBody(),true)['gatewayResponse'];
       
        if(isset($response['status']['isSuccess']) && $response['status']['isSuccess'] != '')
        {
            $encryptedResponse = json_encode($response['response']);
            $LIVYStatusResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
			
			if(isset($LIVYStatusResponse['data'][0]['decisionFlag'])){
					switch(strtoupper($LIVYStatusResponse['data'][0]['decisionFlag'])){
					case 'NO MATCH':
						$LIVYStatusResponse['data'][0]['decisionFlag'] = 'No Match';
						break;
					case 'PENDING':
						$LIVYStatusResponse['data'][0]['decisionFlag'] = 'Pending';
						break;
					case 'MATCH':
						$LIVYStatusResponse['data'][0]['decisionFlag'] = 'Match';
						break;
					case 'DEDUPE API HAS FAILED':
						$LIVYStatusResponse['data'][0]['decisionFlag'] = 'Dedupe Api has Failed';
						break;
					default:
						$LIVYStatusResponse['data'][0]['decisionFlag'] = $LIVYStatusResponse['data'][0]['decisionFlag'];
				}
			}
            $response['response'] = $LIVYStatusResponse;            
        }else{
			$response = $response; 
            // echo "<pre>";print_r($response);exit;
		}
  
        $saveService = CommonFunctions::saveApiRequest('DEDUPE_STATUS',$url,$payload,json_encode($response),
                                                                                json_encode($data),$response,$formId, $responseTime);
        return $response;

        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','checklivystatus',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function fundtransfer($formId,$accountNumber,$fundingDetails,$aofNumber)
    {        

        $acctDetails = DB::table('ACCOUNT_DETAILS')
                                                 ->where('AOF_NUMBER',$aofNumber)
                                                 ->where('FUND_TRANSFER_STATUS',1)
                                                 ->get()->toArray();

        $fincon = DB::table('FINCON')
                                 ->where('FORM_ID',$formId)
                                 ->where('FTR_STATUS','Y')
                                 ->get()->toArray();                              


        if((count($acctDetails) > 0) || (count($fincon) > 0)){
    
            return  Array
                    (
                        'Body' => Array
                            (
                                'transferResponse' => Array
                                    (
                                        'transactionStatus' => 'FAILED',
                                        'message' => 'Duplicate attempt detected'
                                    )
                            )
                    );

        }
        
        //as confirm by IT Oauth to be bypass (08-02-2022)
        /*$authTokenDetails = Self::generateoauthtoken();
        
        if(!isset($authTokenDetails['token_type']) || !isset($authTokenDetails['access_token']) || !isset($authTokenDetails['scope']) || $authTokenDetails['access_token'] == '' || $authTokenDetails['scope'] != 'payments'){
            $saveService = CommonFunctions::saveApiRequest('OAUTH',
                                                            config('constants.APPLICATION_SETTINGS.AUTH_TOKEN_URL'),
                                                            'Request', $authTokenDetails,
                                                            'Request', $authTokenDetails,
                                                            $formId, 1);
            return -1;
        }*/

        $url = config('constants.APPLICATION_SETTINGS.PAYMENTS_URL');
        $custovdCheck = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
        $custovdCheck=(array)current($custovdCheck);
        $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('neft_account_number');
        if($fundingDetails['initial_funding_type'] == 1)
        {
            $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('cheque_account_number');
        }

        if(($fundingDetails['td_amount'] == null) || ($fundingDetails['td_amount'] == '')){

            $tdamount = 0;
        }else{
            $tdamount = intval($fundingDetails['td_amount']);
        }

        $amounttotransfer = intval($fundingDetails['amount'])-$tdamount;
        
        $narration = 'CUBE FTR '.$aofNumber;

        if($fundingDetails['initial_funding_type'] == 1){
            $narration = 'CUBE '.$aofNumber.' CHQ '.$custovdCheck['reference'];
        }elseif($fundingDetails['initial_funding_type'] == 2){
            $narration = 'CUBE '.$aofNumber.' UTR '.$custovdCheck['reference'];
        }

        $message = '<soapenv:Envelope xmlns:pay="http://payments" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                       <soapenv:Header/>
                       <soapenv:Body>
                          <pay:transfer>
                             <pay:version>1.0</pay:version>
                             <pay:uniqueRequestNo>'.$aofNumber.'</pay:uniqueRequestNo>
                             <pay:appID>CUBEDCB</pay:appID>
                             <pay:customerID>'.env('BANK_CUSTOMER_ID').'</pay:customerID>
                             <pay:debitAccountNo>'.$bankAccountNumber.'</pay:debitAccountNo>
                             <pay:beneficiary>
                                <pay:beneficiaryDetail>
                                   <pay:beneficiaryName>
                                      <pay:fullName>CUBE</pay:fullName>
                                   </pay:beneficiaryName>
                                   <pay:beneficiaryAddress>
                                      <pay:address1>Address</pay:address1>
                                   </pay:beneficiaryAddress>
                                   <pay:beneficiaryContact>
                                      <pay:mobileNo>'.$fundingDetails['mobile_number'].'</pay:mobileNo>
                                      <pay:emailID>'.$fundingDetails['email'].'</pay:emailID>
                                   </pay:beneficiaryContact>
                                   <pay:beneficiaryAccountNo>'.$accountNumber.'</pay:beneficiaryAccountNo>
                                   <pay:beneficiaryIFSC>DCBL0000018</pay:beneficiaryIFSC>
                                </pay:beneficiaryDetail>
                             </pay:beneficiary>
                             <pay:transferType>RFT</pay:transferType>
                             <pay:transferAmount>'.$amounttotransfer.'</pay:transferAmount>
                             <pay:narration>'.$narration.'</pay:narration>
                          </pay:transfer>
                       </soapenv:Body>
                    </soapenv:Envelope>';

                    
        file_put_contents(storage_path('uploads/Api/form_'.$formId.'.xml'), $message);
        $encryptedData = EncryptDecrypt::Encryption($formId);
		
		$requestTime = Carbon::now();	
        $paymentDetails = Self::payments('fixed_token',$encryptedData);        
		// $paymentDetails = Self::payments($authTokenDetails['access_token'],$encryptedData);        
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
	
		file_put_contents(storage_path('uploads/Api/form_response_'.$formId.'.txt'), $paymentDetails);
        $decryptedData = EncryptDecrypt::Decryption($formId);
		try{
			$saveService = CommonFunctions::saveApiRequest('FTR',$url,$encryptedData[0],$paymentDetails,
                                                                        htmlentities($message),$decryptedData,$formId, $responseTime);
		}catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }

        return $decryptedData;
    }

    public static function generateoauthtoken()
    {
        try{
            $url = config('constants.APPLICATION_SETTINGS.AUTH_TOKEN_URL');
            $authString = config('constants.APPLICATION_SETTINGS.AUTH_TOKEN_STRING');
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$authString);

            // Receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);

            curl_close ($ch);

            //echo '<pre>'; var_dump($url, $server_output); exit;
            // "{ "token_type":"Bearer", "access_token":"AAIkMzZmM2QxMjktZTdmNy00MjBhLTg1NDAtY2VjNzcxMDQ3YWM4lepDBxPM8_i_oksYXueXjp2xUxMCPimW-a1Kc1i9TnGtemwm9b_iTLgBLirg6fLyOKXF68r-ZEAeecmkqn0z9Q", "expires_in":3600, "consented_on":1635572432, "scope":"payments" }"
            return json_decode($server_output,true);
        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','generateoauthtoken',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function payments($token,$encryptedData)
    {
        if(!isset($encryptedData[0])){
            return json_encode(['status'=>'fail','msg'=>'Error! Encrypted Data in API Incorrect ','data'=>[]]);
        }
        $accountDetails = array();
        $url = config('constants.APPLICATION_SETTINGS.PAYMENTS_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($encryptedData[0]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(($encryptedData[0])),
            'X-IBM-Client-secret: '.$client_key,
            'X-IBM-Client-Id: '.$client_id,
            'X-IBM-Client-auth: '.$authorization,
            'Authorization: Bearer '.$token,
        ));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
        curl_close($ch);
        return $result;
    }

    public static function ekycDetails($data,$formId,$withPhoto=0)
    {
        $url = config('constants.APPLICATION_SETTINGS.EKYC_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

        $RequestUUID = CommonFunctions::getRandomValue(9);
	    $getRandnumber = CommonFunctions::getRandomValue(5);
        $current_timestamp = Carbon::now()->timestamp;

        $requestData = Array(
                        'header' => Array
                            (
                                'apiVersion' => '1.0',
                                'appVersion' => "1.0.0.0",
                                'channelId' => 'CUBE',
                                'isEnc' => 'Y',
                                'cifId' => '100933779',
                                'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
                                'languageId' => 1,
                                //'requestUUID' => '895547861',
                                'requestUUID' => strval($data['referenceNumber'].'_'.'EKYC'.'_'.$getRandnumber),
                                "sVersion" => "20",
                                "serReqId" => "ESBFETCHRRN",
                               'sessionId' => '5932216656835406787',
                                'timeStamp' => strval($current_timestamp),
                            ),
                        'request' => Array
                            (
                                "referenceNumber" => $data['referenceNumber'],
                                "txnId" => $data['txnId'],
                                "timeStamp" => $data['timeStamp']
                            )
                    );

        // echo "<pre>";print_r($requestData);
        $requestData['request'] = EncryptDecrypt::AES256Encryption(json_encode($requestData['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$requestData]);
        // echo "<pre>";print_r($payload);
        $client = new \GuzzleHttp\Client();
		
		$requestTime = Carbon::now();
	
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$requestData],
                                                'exceptions'=>false
                                            ]);
    
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        
		$response = json_decode($guzzleClient->getBody(),true);        
		// echo "<pre>";print_r($response);exit;
		try{
			$saveService = CommonFunctions::saveApiRequest('E_KYC_DETAILS',$url,$payload,json_encode($response),
                                                                                    json_encode($data),$response,$formId, $responseTime);
		}catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }

        if(isset($response['gatewayResponse']))
        {
            if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'success'){ 
                $response = $response['gatewayResponse'];
                $encryptedResponse = json_encode($response['response']);
                $ekycDetailsResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);	
				if(!isset($ekycDetailsResponse['userDetails']) || !isset($ekycDetailsResponse['userAddress'])){
					return ['status'=>'Error','code'=>'','message'=>'eKYC Error!',
                                                                        'moreInformation'=>'Unable to process API data'];
				}
				foreach($ekycDetailsResponse['userDetails'] as $key => &$value){
					$ekycDetailsResponse['userDetails'][$key] = strtoupper($ekycDetailsResponse['userDetails'][$key]);
				}   	
				foreach($ekycDetailsResponse['userAddress'] as $key => &$value){
					$ekycDetailsResponse['userAddress'][$key] = strtoupper($ekycDetailsResponse['userAddress'][$key]);
				}
				if(isset($ekycDetailsResponse['userPhoto'])){
					if($withPhoto==0){
                        unset($ekycDetailsResponse['userPhoto']);
                    }
				}	
                $response['response'] = $ekycDetailsResponse;
                return ['status'=>'Success','data'=>$response];
            }else{
                if(isset($response['gatewayResponse']['status']['message']) && isset($response['gatewayResponse']['status']['statusCode'])){
                return ['status'=>'Error','code'=>$response['gatewayResponse']['status']['statusCode'],'message'=>$response['gatewayResponse']['status']['message'],
                                                                        'moreInformation'=>'eKYC Error'];
                }else{
                    return ['status'=>'Error','code'=>'','message'=>'eKYC Error!',
                                                                        'moreInformation'=>'No additional information available'];
                }
            }
        }else{
            return ['status'=>'Error','code'=>$response['httpCode'],'message'=>$response['httpMessage'],
                                                                        'moreInformation'=>$response['moreInformation']];
        }
    }

    public static function aadharValutSvc($customerId,$aadharNumber,$formId)
    {
        $url = config('constants.APPLICATION_SETTINGS.AADHAR_VAULT_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

        $RequestUUID = CommonFunctions::getRandomValue(5).$formId;
 
        $current_timestamp = Carbon::now()->timestamp;

        $requestData = Array(
            'header' => Array
            (
                'apiVersion' => '1.0',
                'appVersion' => "1.0.0.0",
                'channelId' => 'CUBE',
                'isEnc' => 'Y',
                'cifId' => $customerId,
                'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
                'languageId' => 1,
                //'requestUUID' => '45421154',
                'requestUUID' => strval($RequestUUID.'_'.'AADHAARV'),
                "sVersion" => "20",
                "serReqId" => "AVLGENERATEREFNUMBER",
                'sessionId' => '5932216656835406787',
                'timeStamp' => strval($current_timestamp),
            ),
            'request' => Array
            (
                "aadharNo" => $aadharNumber
            )
        );
		                    //   echo"<pre>"; print_r($requestData);

        $requestData['request'] = EncryptDecrypt::AES256Encryption(json_encode($requestData['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$requestData]);
        $client = new \GuzzleHttp\Client();
	
		$requestTime = Carbon::now();
	
        $guzzleClient = $client->request('POST',$url,
            [
                'headers' =>[
                'Content-Type'=>'application/json',
                'X-IBM-Client-secret'=>$client_key,
                'X-IBM-Client-Id'=>$client_id,
                'authorization'=>$authorization
            ],
                'json'=>['gatewayRequest'=>$requestData],
                'exceptions'=>false
            ]);
        
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
	
        $response = json_decode($guzzleClient->getBody(),true);
		
		$saveService = CommonFunctions::saveApiRequest('AADHAR_VAULT',$url,$payload,json_encode($response),   
															$payload,$response,$formId, $responseTime);
					
		                    //   echo"<pre>"; print_r($response); exit; 
			
        if(isset($response['gatewayResponse']))
        {
            if(isset($response['gatewayResponse']['response'])){
                $response = $response['gatewayResponse'];
                $encryptedResponse = json_encode($response['response']);
                $ekycDetailsResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                $response['response'] = $ekycDetailsResponse;                
                return ['status'=>'Success','data'=>$response];
            }else{
                if(isset($response['gatewayResponse']['status']['message']) && isset($response['gatewayResponse']['status']['statusCode'])){
                    return ['status'=>'Error','code'=>$response['gatewayResponse']['status']['statusCode'],'message'=>$response['gatewayResponse']['status']['message'],
                        'moreInformation'=>'Aadhar Vault Error!'];
                }else{
                    return ['status'=>'Error','code'=>'','message'=>'Aadhar Vault Error!',
                        'moreInformation'=>'No additional information available'];
                }
            }
        }else{
            return ['status'=>'Error','code'=>$response['httpCode'],'message'=>$response['httpMessage'],
                'moreInformation'=>$response['moreInformation']];
        }
    }

    public static function getNomineeDetaisTD($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdCode)
    {   
        $gaurdianCode = CommonFunctions::getGaurdianCode($gaurdCode);
        // echo "<pre>";print_r($gaurdianCode);exit;
        if($nominee_is_minor == 'Y'){ 
             $guardianInfo =    Array(
                   "GuardianCode" => $gaurdianCode,
                   "GuardianContactInfo" => Array(
                    "EmailAddr" => "",
                    "PostAddr" => Array(
                        "Addr3" => $nomineeDetails['guardian_address_line1'],
                        "Addr1" => $nomineeDetails['guardian_address_line1'],
                        "Addr2" => $nomineeDetails['guardian_address_line2'],
                        "AddrType" => "",
                        "StateProv" => $guardian_pcsDetails['statecode'],
                        "PostalCode" => $nomineeDetails['guardian_pincode'],
                        "Country" => $guardian_pcsDetails['countrycode'],
                        "City" => $guardian_pcsDetails['citycode']
                      ),
                      "PhoneNum" => Array(
                        "TelephoneNum" => "",
                        "TelexNum" => "",
                        "FaxNum" => ""
                      )
                   ),
                   "GuardianName" => $nomineeDetails['guardian_name']
            );
        }else{
            $guardianInfo = array();
        }

        $returnArray = Array(
                "NomineeInfoRec" => Array(
                                Array(
                "NomineeContactInfo" => Array(
                   "EmailAddr" =>"",
                   "PostAddr" => Array(
                      "Addr3" =>"",
                      "Addr1" => $nomineeDetails['nominee_address_line1'],
                      "Addr2" => $nomineeDetails['nominee_address_line2'],
                      "AddrType" => "Home",
                      "StateProv" => $nominee_pcsDetails['statecode'],
                      "PostalCode" => $nominee_pcsDetails['pincode'],
                      "Country" => $nominee_pcsDetails['countrycode'],
                      "City"=> $nominee_pcsDetails['citycode']
                   ),
                   "PhoneNum" => Array(
                      "TelephoneNum" => "",
                      "TelexNum"=>"",
                      "FaxNum"=>""
                   )
                ),
            "RecDelFlg" => "",
            "RelType" => $nomineeDetails['relatinship_applicant'],
            "NomineeBirthDt" => Carbon::parse($nomineeDetails['nominee_dob'])->format('Y-m-d\TH:i:s.v'),
            "NomineeMinorFlg" => $nominee_is_minor,
            "NomineeName" => $nomineeDetails['nominee_name'],
            "GuardianInfo" => $guardianInfo,
            "RegNum" => "90149",
            "NomineePercent" => Array(
               "value" => "100"
            ),
        )
        )
    );
            return $returnArray;
    }       



    public static function getNomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdCode)
    {    
        $gaurdianCode = CommonFunctions::getGaurdianCode($gaurdCode);
        // echo "<pre>";print_r($gaurdianCode);exit;
        if ($nominee_is_minor == 'Y') { //nominee is minor
            $gurdianInfo = Array(
                             "GuardianCode" => $gaurdianCode,
                             "GuardianName" => $nomineeDetails['guardian_name'],
                             "GuardianContactInfo" => Array(
                                "PhoneNum" => Array(
                                   "TelephoneNum" => "",
                                   "FaxNum" => "",
                                   "TelexNum" => "",
                                   "EmailAddr" => ""
                                ),
                                "PostAddr" => Array(
                                   "Addr1" => $nomineeDetails['guardian_address_line1'],
                                   "Addr2" => $nomineeDetails['guardian_address_line2'],
                                   "Addr3" => "",
                                   "City" => $guardian_pcsDetails['citycode'],
                                   "StateProv" => $guardian_pcsDetails['statecode'],
                                   "PostalCode" => $nomineeDetails['guardian_pincode'],
                                   "Country" => $guardian_pcsDetails['countrycode'],
                                   "AddrType" => ""
                                )
                             )
                          );
        }else{
            $gurdianInfo = array();
        }
        $returnArray = Array(
            'NomineeInfoRec' =>[
                         Array(
                        "RegNum" => "001",
                        "NomineeName" => $nomineeDetails['nominee_name'],
                        "RelType" => $nomineeDetails['relatinship_applicant'],
                        "NomineeContactInfo"=> Array(
                            "PhoneNum" => Array(
                            "TelephoneNum" => "",
                            "FaxNum" => "",
                            "TelexNum" => ""
                          ),
                        "EmailAddr" => "",
                        "PostAddr" => Array(
                            "Addr1" => $nomineeDetails['nominee_address_line1'],
                            "Addr2" => $nomineeDetails['nominee_address_line2'],
                            "Addr3" => "",
                            "City" => $nominee_pcsDetails['citycode'],
                            "StateProv" => $nominee_pcsDetails['statecode'],
                            "PostalCode" => $nomineeDetails['nominee_pincode'],
                            "Country" => $nominee_pcsDetails['countrycode'],
                            "AddrType" => "Mailing"
                           )
                        ),
                        "NomineeMinorFlg" => $nominee_is_minor,
                        "NomineeBirthDt" => Carbon::parse($nomineeDetails['nominee_dob'])->format('Y-m-d\TH:i:s.v'),
                        "NomineePercent" => Array(
                          "value" => 100
                        ),
                        "GuardianInfo" => $gurdianInfo,
                      )
                    ]

         );

        return  $returnArray;
    }

    public static function createTdaccountid($formId,$customerID){

        try{

            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $accountDetails = (array) current($accountDetails);

            if($accountDetails['account_type'] == 4){

               $schemeCodeDetails = DB::table('TD_SCHEME_CODES')->whereId($accountDetails['td_scheme_code'])->get()->toArray();
            }else{

               $schemeCodeDetails = DB::table('TD_SCHEME_CODES')->whereId($accountDetails['scheme_code'])->get()->toArray();
            }

            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                ->where('APPLICANT_SEQUENCE',1)
                                                                ->get()->toArray();
            $customerOvdData = (array) current($customerOvdDetails);

            $url = config('constants.APPLICATION_SETTINGS.TD_ACCOUNT_ID_CREATION_URL');
            $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

            $data = Array(
                     "header" => Array(
                                  "apiVersion" =>  "1.0.0.0",
                                  "appVersion" =>  "1.0.0.0",
                                  "channelId" =>  "WBB",
                                  "cifId" =>  "100341795",
                                  "deviceId" =>  "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
                                  "modelName" =>  "Motorola XT1022",
                                  "languageId" =>  "1",
                                  "os" =>  "Android",
                                  "osVersion" =>  "5.1",
                                  "requestUUID" =>  "1000000013",
                                  "serReqId" =>  "TDACCTADD_V1",
                                  "sVersion" =>  "13",
                                  "timeStamp" =>  "1544434754893",
                                  "isEnc" =>  "N"
                     ),//Header End
                     "request" => Array(
                                  "custId" =>  $customerID,
                                  "schmCode" => $chemeCodeDetails['scheme_code'],
                                  "currency" =>  "INR",
                                  "solId" =>  $accountDetails['branch_id'],

                                  "tdAcctGenInfo" => Array(
                                                      "genLedgerSubHeadCode" => "17001",
                                                      "curCode" => "INR"
                                  ),//tdAcctGenInfo End
                                  "acctName" =>  $customerOvdData['first_name'],
                                  "acctShortName" =>  $customerOvdData['short_name'],
                                  "acctStmtMode" =>  "N",
                                  "despatchMode" =>  "N",
                                  "intialDeposit" =>  Array(
                                                        "amountValue" => "10000",
                                                        "currencyCode" => "INR"
                                  ),//intialDeposit End
                                  "depositTerm" => Array(
                                                    "days" => "0",
                                                    "months" => "12"
                                  ),//depositTerm End
                                  "renewalDtls" => Array(
                                                    "autoCloseOnMaturityFlg" =>  "N",
                                                    "autoRenewalflg" =>  "U",
                                                    "renewalTerm" => Array(
                                                                        "days" => "0",
                                                                        "months" => "12"
                                                    ),//renewalTerm End
                                                    "renewalOption" => "P"
                                  ),
                                  "trnDtls" => Array(
                                                "trnType" => "T",
                                                "trnSubType" => "CI",
                                                "debitAcctId" => Array(
                                                                  "acctId" => "01812200000790",
                                                                  "acctType" => Array(
                                                                                 "schmCode" =>"SB122",
                                                                                 "schmType" =>"SBA"
                                                                  )
                                                ),
                                                "acctCurr" => "INR"
                                  ),//trnDtls End
                                  "relPartyRec" => Array(
                                                    "relPartyType" => "M",
                                                    "custId" => "100615791"
                                  )
                     )//request End
            );

        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
       //echo "<pre>";print_r(json_encode(['gatewayRequest'=>$data]));exit;
        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);

        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);

        // echo "<pre>";print_r($response);exit;

        if(isset($response['gatewayResponse']['status']['isSuccess']) && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ER000')
        {
            $encryptedResponse = json_encode($response['gatewayResponse']['response']['data']);
            //$customerIdResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                                                               
            $tdAccountResponse = json_decode($encryptedResponse);                       
    
            $tdAccountResponse = $delightKitDetails['customer_id'];
                    
            return $tdAccountResponse;
        }else{
             
            $amendmentResponse = '';

            return  $amendmentResponse;
        }
  

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/Api','createTdaccountid',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }

    public static function DepositValidation(){

        try{

            $url = config('constants.APPLICATION_SETTINGS.DEPOSIT_VALIDATION_URL');
            $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

            $data = Array(
                    "header" => Array(
                                  "apiVersion" =>  "1.0",
                                  "channelId" =>  "MB",
                                  "cifId" =>  "PVRET",
                                  "languageId" =>  1,
                                  "requestUUID" =>  "545611484515484",
                                  "isEnc" =>  "N",
                                  "sVersion" =>  "20",
                                  "serReqId" =>  "ESBDEPOSITVALIDATION",
                                  "timeStamp" =>  "15197315382"
                    ),//header End
                    "request" => Array(
                                      "deposit" =>  "FD",
                                      "cif" =>  "100212262",
                                      "pannum" =>  "99999999",
                                      "amount" =>  "15000",
                                      "depositDurationYears" =>  "1",
                                      "depositDurationMonths" =>  "11",
                                      "depositDurationDays" =>  "23",
                                      "accountNo" =>  "07111300001045",
                                      "depositType" =>  "104",
                                      "principalMaturityFlag" =>  "Redeem",
                                      "reddemInterestDropDown" =>  "RIC"
                    )//request End
            );


            $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
       //echo "<pre>";print_r(json_encode(['gatewayRequest'=>$data]));exit;
        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);

        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);

        // echo "<pre>";print_r($response);exit;

        if(isset($response['gatewayResponse']['status']['isSuccess']) && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ER000')
        {
            $encryptedResponse = json_encode($response['gatewayResponse']['response']['data']);
            //$customerIdResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                                                               
            $tdAccountResponse = json_decode($encryptedResponse);                       
    
            $tdAccountResponse = $delightKitDetails['customer_id'];
                    
            return $tdAccountResponse;
        }else{
             
            $amendmentResponse = '';

            return  $amendmentResponse;
        }


        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/Api','DepositValidation',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


	public static function uploadSignature($formId, $account_id)
    {
        try{
        $accountDetails = array();
        $url = config('constants.APPLICATION_SETTINGS.SIGNATURE_UPLOAD');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $RequestUUID = '25dff7d7-4d52-524b-99d6-60b500e23011'; //CommonFunctions::getRandomValue(9);
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1'); //'casa@2018';
        // $encrypt_key = "june2020";
        $hash1 = hash('sha256', $timestamp);
        $hash2 = hash('md5', $timestamp);

        $uuid = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);

         $getAccountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->select('ACCOUNT_DETAILS.ID','ACCOUNT_DETAILS.CUSTOMERS_PHOTOGRAPH','MODE_OF_OPERATIONS.OPERATION_TYPE',
                                        'ACCOUNT_DETAILS.ACCOUNT_TYPE', 'ACCOUNT_DETAILS.ACCOUNT_NO', 'ACCOUNT_DETAILS.TD_ACCOUNT_NO')
                                    ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
        $getAccountDetails = (array) current($getAccountDetails);

        // if($account_id == ''){
        //     $account_id = $getAccountDetails['account_no'];
        //     if($getAccountDetails['account_type'] == '3'){
        //         $account_id = $getAccountDetails['td_account_no'];
        //     }
        // }
        switch ($getAccountDetails['account_type']) {
            case '1':
                $account_id = $getAccountDetails['account_no'];
            break;

            case '2':
                $account_id = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->value('ENTITY_ACCOUNT_NO');
            break;

            case '3':
                $account_id = $getAccountDetails['td_account_no'];
            break;

            default:
                $account_id = $account_id;
            break;
        }


        
        if(strlen($getAccountDetails['customers_photograph'])==0 || $account_id == ''){
            return ['status'=>'Error','data'=>'', 'message'=>'Signature not in DB or account number not found!'];
        }

        $customerSignature = '_DONOTSIGN_'.$getAccountDetails['customers_photograph'];
        $filePath = storage_path('uploads/markedattachments/'.$getAccountDetails['id'].'/'.$customerSignature);
        $signatureDate = Carbon::now()->format('Y-m-d\TH:i:s.v');

        if (File::exists($filePath)) {
            $imageData = file_get_contents($filePath);
            $encodedData = base64_encode($imageData);
        }else{            
            $customerSignature = $getAccountDetails['customers_photograph'];
            $filePath = storage_path('uploads/markedattachments/'.$getAccountDetails['id'].'/'.$customerSignature);
            if (File::exists($filePath)) {
                $imageData = file_get_contents($filePath);
                $encodedData = base64_encode($imageData);
            }else{    
                return ['status'=>'Error','data'=>'', 'message'=>'Signature file not found'];
            }
        }

        $ekycphotoCheck = 0;
        $applicantSeq = '';
        $getCustomerDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('PROOF_OF_IDENTITY','PROOF_OF_ADDRESS','APPLICANT_SEQUENCE')
                                                                ->where('FORM_ID',$getAccountDetails['id'])
                                                                ->get()
                                                                ->toArray();

        if(count($getCustomerDetails)>0){
            for($i=0;count($getCustomerDetails)>$i;$i++){
                if($getCustomerDetails[$i]->{'proof_of_identity'} == '9' || $getCustomerDetails[$i]->{'proof_of_address'} == '9'){
                   $ekycphotoCheck++; 
                   $applicantSeq .=$getCustomerDetails[$i]->{'applicant_sequence'};
                }
            }
        }

        $getekycphoto = array();
        $getekycimage = '';
        $ekycPhotoeffdate = '';
        $ekycPhotoexpdate = '';

        if($ekycphotoCheck > 0){
            $getekycphoto = CommonFunctions::getuploadekycphoto($getAccountDetails['id'],$applicantSeq);
        }

        if(count($getekycphoto)>0){
            if($getekycphoto['status'] == 'success'){
                $getekycimage = $getekycphoto['data'];
                $ekycPhotoeffdate = $signatureDate;
                $ekycPhotoexpdate = '2099-01-20T00:00:00.000';
            }
        }

        // echo "<pre>";print_r($getekycimage);exit;

        $data = Array(
                        'header' => Array
                            (
                                'apiVersion' => '1.0.0.0',
                                'appVersion' => "1.0.0.0",
                                'channelId' => 'CUBE',
                                'isEnc' => 'Y',
                                'cifId' => '102873457',
                                'deviceId' => 'E1A31A83-D4DC-421E-8338-1FBFE7C573B7',
                                'languageId' => 1,
                                'os' => 'iOS',
                                'model' => 'iPhone X',
                                'osVersion' => '11.2',
                                'osVersion' => '11.2',
                                'requestUUID' => $uuid, //strval('SU'.$formId.'_'.$customerId),
                                'serReqId' => 'SIGNATUREADD',
								'sVersion' => 13,
                                'sessionId' => '5932216656835406787',
                                'timeStamp' => $timestamp,
                            ),
                        'request' => Array (
						
									'signatureAddRequest' => Array
													(
														'signatureAddRq' => Array (
																'acctId' => $account_id,
																'bankId' => '01',
																'bankCode' => '01',
																'acctCode' => 'N',
																'sigPowerNum' => 1,
																'imageAccessCode' => 'DE',
                                                                'pictureEffDt'=>$ekycPhotoeffdate,
                                                                'pictureExpDt'=>$ekycPhotoexpdate,
                                                                'pictureFile'=>$getekycimage,
																'signEffDt' => $signatureDate, // Current Date 
																'signExpDt' => '2099-01-20T00:00:00.000', // Current Date 
																'sigFile' => $encodedData,
                                                                'Remarks' => $getAccountDetails['operation_type'],
																)
																
													)
									)				 
                    );

        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);
        $client = new \GuzzleHttp\Client();
		$requestTime = Carbon::now();	
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
    
        $response = $guzzleClient->getBody();

        $response = json_decode($response,true);
        //print_r($response); exit;
                
        if(isset($response['gatewayResponse'])){
            $response = $response['gatewayResponse'];
            if($response['status']['isSuccess'] == 1){
                $encryptedResponse = json_encode($response['response']);
                $decResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                 $saveService = CommonFunctions::saveApiRequest('UPLOAD_SIGNATURE',$url,$payload,$encryptedResponse,
                                                                            json_encode($data),$response,$formId, $responseTime);
                $signResponse = $decResponse['data']['signatureAddResponse']['signatureAddRs']['sigAddStatusCode'];
                return ['status'=>'Success','data'=>$signResponse, 'message'=>'Signature uploaded successfully!'];

            }else{
                $saveService = CommonFunctions::saveApiRequest('UPLOAD_SIGNATURE',$url,$payload,json_encode($response),
                                                                            json_encode($data),$response,$formId, $responseTime);
                return ['status'=>'Error','data'=>$response, 'message'=>'API Response not successfull!'];
            }
        }else{
                $saveService = CommonFunctions::saveApiRequest('UPLOAD_SIGNATURE',$url,$payload,json_encode($response),
                                                                            json_encode($data),$response,$formId, $responseTime);  
                return ['status'=>'Error','data'=>$response, 'message'=>'Error in API Response!'];
        }

        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','uploadSignature',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }

        public static function submitCardDetails($formId){

        try{
            
            // Same URL used for KYC Update and Card update - internal script name changes!
            $url =  config('constants.APPLICATION_SETTINGS.KYC_UPDATE');
            $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');
            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID', $formId)->get()->toArray();

            $accountDetails = DB::table('ACCOUNT_DETAILS')
                            ->select('ACCOUNT_NO','MODE_OF_OPERATION', 'CARD_RULES.FINACLE_CODE','ACCOUNT_TYPE','DELIGHT_SCHEME','CONSTITUTION')
                            ->leftjoin('CARD_RULES','CARD_RULES.ID' ,'=','ACCOUNT_DETAILS.CARD_TYPE')
                            ->where('ACCOUNT_DETAILS.ID',$formId)
                            ->get()->toArray();

            $accountDetails = current($accountDetails);
            $accountNumber = $accountDetails->account_no;

            
            // echo"<pre>";print_r($accountDetails);
            $is_huf = false;
            if($accountDetails->constitution == "NON_IND_HUF"){
                $is_huf = true;
            }

            if($accountDetails->account_type == 2){
                $accountNumber = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->value('ENTITY_ACCOUNT_NO');
            }

            $customerArrayData = Array(
                "foracid"=> $accountNumber,
            );

            $counter=0;
            $ctn = 0;
            foreach ($customerOvdDetails as $key => $customerData) {
                if(isset($customerData->customer_id)){
                    $checkPass = true;
                    if(($is_huf) && ($customerData->applicant_sequence == 2)){
                        $checkPass = false;
                    }
                    if($checkPass){
                    $customerAge = Carbon::parse($customerData->dob)->age;

                        if($accountDetails->delight_scheme != ''){
    
                        if ($customerData->applicant_sequence == 2 && $customerAge >= 14 && in_array($accountDetails->mode_of_operation, [2])) {
                            $customerArrayData['cardDetails'.$ctn] = $accountDetails->finacle_code;
                            $customerArrayData['cifId'.$ctn] = $customerData->customer_id;
                            $customerArrayData['embossName'.$ctn] = substr($customerData->short_name, 0 , 24);
                            $counter++;
                        }
                    }else{
                        
                    if ($customerData->applicant_sequence == 1 && $customerAge >= 14 && in_array($accountDetails->mode_of_operation, [1,2,3,6,22,23])) {
                            $customerArrayData['cardDetails'.$ctn] = $accountDetails->finacle_code;
                    }elseif ($customerData->applicant_sequence == 1 && $customerAge >= 14) {
                        continue;
                            $customerArrayData['cardDetails'.$ctn] = "";
                    }elseif ($customerData->applicant_sequence > 1 && $customerAge >= 18 && in_array($accountDetails->mode_of_operation, [2,6,7,23])){
                            $customerArrayData['cardDetails'.$ctn] =  $accountDetails->finacle_code;
                    }elseif ($is_huf && $customerData->applicant_sequence > 1 && $customerAge < 14 && in_array($accountDetails->mode_of_operation, [23])){
                        $customerArrayData['cardDetails'.$ctn] =  $accountDetails->finacle_code;
                    }else{
                        continue;
                            $customerArrayData['cardDetails'.$ctn] = "";
                    }
                        $customerArrayData['cifId'.$ctn] = $customerData->customer_id;
                        $customerArrayData['embossName'.$ctn] = substr($customerData->short_name, 0 , 24);
                        $counter++;
                        $ctn++;
                }
                }
            }
            }

            if($counter == 0){
                 $saveService = CommonFunctions::saveApiRequest('SUBMIT_CARD_DETAILS',$url,'','','','Rule: No cards to be issued.',$formId,'');
                 return 'Success';
            }
        
            // echo "<pre>";print_r($customerArrayData);exit;

            $current_timestamp = Carbon::now()->timestamp;
            $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
        
            $data = Array(
                        
                    "header" => Array(
                                "apiVersion"=> "1.0",
                                "appVersion"=> "1.0.0.0",
                                "channelId"=> "CUBE",
                                "cifId"=> $customerOvdDetails[0]->customer_id,
                                "deviceId"=> "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
                                "modelName"=> "Motorola XT1022",
                                "languageId"=> "1",
                                "os"=> "Android",
                                "osVersion"=> "5.1",
                                "requestUUID"=> strval($formId.$customerOvdDetails[0]->customer_id.'_'.'CARD'),
                                "serReqId"=> "KYC_FINACLE_SCRIPT",
                                "sVersion"=> "13",
                                "timeStamp"=> $timestamp,
                                "isEnc"=> "N"

                    ),//header End
                    "request" => Array(
                                    "executeFinacleScriptRequest"=> Array(
                                        "executeFinacleScriptInputVO"=> Array(
                                            "requestId"=> "DCB_CARD_DATAILS_INSERT.scr",
                                        ),
                                        "executeFinacleScriptCustomData"=> $customerArrayData,
                                    )
                                )//request End
            );

        //echo "<pre>";print_r($data);

            // echo "<pre>";print_r($data);exit;
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
       //echo "<pre>";print_r(json_encode(['gatewayRequest'=>$data]));exit;
        // $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);
        $requestTime = Carbon::now();   

        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);

        $responseTime = Carbon::now()->diffInSeconds($requestTime); 
        // echo "<pre>";print_r($response);
        $saveService = CommonFunctions::saveApiRequest('SUBMIT_CARD_DETAILS',$url,$data,$response,$payload,$response,$formId, $responseTime);

       if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] != ''){
            return true;
        }else{
            if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] != ''){
                return $response['gatewayResponse']['status']['message'];
            }else{
                $response = 'Api Error! Please Try Again Later';
                return $response;
            }
        }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/Api','submitCardDetails',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public static function kycUpdate($formId,$customerId,$kycType='PHYSICAL'){

        $amendFlag = $kycType;
        
        if($kycType == 'amend'){
            $kycType = 'PHYSICAL';
        }
        
        $url = config('constants.APPLICATION_SETTINGS.KYC_UPDATE'); 
		$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
		$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $RequestUUID = CommonFunctions::getRandomValue(5);

        $request = Array(
            "header" => Array(
            "apiVersion" => "1.0",
            "appVersion" => "1.0.0.0",
            "channelId" => "CUBE",
            "cifId" => "102873457",
            "deviceId" => "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
            "modelName" => "Motorola XT1022",
            "languageId" => "1",
            "os" => "Android",
            "osVersion" => "5.1",
            "requestUUID" => 'CUBE_'.$current_timestamp.$RequestUUID,
            "serReqId" => "KYC_FINACLE_SCRIPT",
            "sVersion" => "13",
            "timeStamp" => $current_timestamp,
            "isEnc" => "N"
        ),
        "request" => Array(
            "executeFinacleScriptRequest" => Array(
                "executeFinacleScriptInputVO" => Array(
                    "requestId" => "DCB_ISA_KYCUPDATE_API.scr"
                ),
                "executeFinacleScriptCustomData" => Array(
                    "kycStatus" => "F",
                    "referenceNo" => "",
                    "custId" => $customerId,
                    "userId" => "CUBE",
                    "kycType" => $kycType
                )
            )
        )
    );
        

        $payload = json_encode(['gatewayRequest'=>$request]);
        $client = new \GuzzleHttp\Client();
		$guzzleClient = $client->request('POST',$url,
                                            ['headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$request],
                                                'exceptions'=>false
                                            ]);


        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime); 
        $saveService = CommonFunctions::saveApiRequest('KYC_FINACLE_UPDATE',$url,$request,$response,$payload,$response,$formId, $responseTime);
        
        if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] != ''){

            if($amendFlag == 'amend'){
			    return json_encode(['status'=>'success','msg'=>'Customer Data Update Successfully','data'=>[$response]]);
            }else{
            return 'Success';
            }
        }else{
        	if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] != ''){
        		return $response['gatewayResponse']['status']['message'];
        	}else{
        		$response = 'Api Error! Please Try Again Later';
        		return $response;
        	}
        }
    }

    public static function internetBankRegister($customerId,$formId){
        $url = config('constants.APPLICATION_SETTINGS.INTERNET_BANKING'); 
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); //Auth confirmed by Sreesa 12.07.22
        $encrypt_key = config('constants.APPLICATION_SETTINGS.IB_ENCRYPT_KEY');
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $RequestUUID = CommonFunctions::getRandomValue(17);

        $request = Array(
            "header" =>  Array(
            "isEnc"=> "N",
            "apiVersion"=> "10.1",
            "cifId"=> "100212262",
            "languageId"=> "1",
            "channelId"=> "CUBE",
            "requestUUID"=> 'CUBE_'.$current_timestamp,
            "serReqId"=> "ESB_IBREGISTRATION_SCRIPTS_FIS",
            "timeStamp"=> 'CUBE_'.$current_timestamp,
        ),
        "request"=> Array(
            "executeFinacleScriptRequest"=> Array(
                "executeFinacleScriptInputVO"=> Array(
                    "requestId"=> "DCB_INTERNET_REGISTRATION.scr"
                ),
                "executeFinacleScriptCustomData"=> Array(
                    "cifId"=> $customerId
                )
            )
        )
        );

        $payload = json_encode(['gatewayRequest'=>$request]);
        $client = new \GuzzleHttp\Client();
        $guzzleClient = $client->request('POST',$url,
                                            ['headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$request],
                                                'exceptions'=>false
                                            ]);


        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime); 

        $saveService = CommonFunctions::saveApiRequest('INTERNET_BANKING',$url,$request,$response,$payload,$response,$formId, $responseTime);
        
        if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] != ''){
            return true;
        }else{
        	if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] != ''){
        		return $response['gatewayResponse']['status']['message'];
        	}else{
        		$response = 'Api Error! Please Try Again Later';
        		return $response;
        	}
        }


    }

    public static function createaccountnumber_tdapi($formId){
        try{
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022');
        $url = config('constants.APPLICATION_SETTINGS.TD_ACCOUNT_ID');
        $current_timestamp = Carbon::now()->timestamp;
            

        $global_Details = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
        $global_Details = current($global_Details);
        
        $is_huf = false;
        if($global_Details->constitution == "NON_IND_HUF"){
            $is_huf = true;
        }
        if($is_huf){
        //for huf applicant sequence should reverse account to be created with huf cust id and joint applicant should be karta hence desending
        $accountDetails = DB::table('ACCOUNT_DETAILS')->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
            ->leftjoin('FINCON','FINCON.FORM_ID','CUSTOMER_OVD_DETAILS.FORM_ID')
            ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
            ->leftjoin('TITLE','TITLE.ID','CUSTOMER_OVD_DETAILS.TITLE')
            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','DESC')->get()->toArray();
            $customerDetails = (array) current($accountDetails);
        }else{
            $accountDetails = DB::table('ACCOUNT_DETAILS')->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
            ->leftjoin('FINCON','FINCON.FORM_ID','CUSTOMER_OVD_DETAILS.FORM_ID')
            ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
            ->leftjoin('TITLE','TITLE.ID','CUSTOMER_OVD_DETAILS.TITLE')
            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','ASC')->get()->toArray();
        $customerDetails = (array) current($accountDetails);
        }

        // echo "<pre>";print_r($customerDetails);exit;
        $accountNumber = '';
        if($customerDetails['initial_funding_type'] == '3'){
            $accountNumber = $customerDetails['account_number'];
        }
        $renewalOption  = 'M';
        switch ($customerDetails['maturity']) {
            case '1':
                $renewalOption = 'M';
                break;
            case '2':
                $renewalOption = 'P';
                break;
        }

        $debitAccountNumber = CommonFunctions::getAccountNumberBasedonFunding($customerDetails['initial_funding_type'],$accountNumber);

        $nomineeDetails = DB::table('NOMINEE_DETAILS')
                                // ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT',
                                //     'RELATIONSHIP.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT')
                                ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                                // ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $nomineeDetails = (array) current($nomineeDetails);

        //add extra
        $gaurdianDetails = DB::table('NOMINEE_DETAILS')->select('REL.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                    ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                    ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $gaurdianDetails = (array) current($gaurdianDetails);
        // echo "<pre>";print_r($nomineeDetails);exit;

        if(isset($nomineeDetails['nominee_exists']) && $nomineeDetails['nominee_exists'] == 'yes'){
            $nominee_pcsDetails = CommonFunctions::getZipDetailsByZipCode($nomineeDetails['nominee_pincode'], $nomineeDetails['nominee_country']);
            $guardian_pcsDetails = CommonFunctions::getZipDetailsByZipCode($nomineeDetails['guardian_pincode'], $nomineeDetails['guardian_country']);
            
            $nomineeYears = \Carbon\Carbon::parse($nomineeDetails['nominee_dob'])->age;

            if($nomineeYears < 18){           
               $nominee_is_minor = 'Y';
            }else{
                $nominee_is_minor = 'N';
            }

            $nomineeFlag = 'Y';
        }else{
           $nomineeFlag = 'N';
        }

        $gaurdianCode = $gaurdianDetails['relatinship_applicant_guardian'];
       

        if($nomineeFlag == 'Y'){
          $nomineeDetails = Self::getNomineeDetaisTD($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdianCode);
        }else{          
          $nomineeDetails = Array();
        }



        if($customerDetails['account_type'] == 3){
            $schemeCode = $customerDetails['scheme_code'];
        }else{
            $schemeCode = $customerDetails['td_scheme_code'];
        }

        if($customerDetails['auto_renew'] == 'Y'){
            $autoRenewalflg = 'U';
            $autoCloseOnMaturityFlg = 'N';
        }else{
            $autoRenewalflg = 'N';
            $autoCloseOnMaturityFlg = 'Y';

        }

        $schemeCode = DB::table('TD_SCHEME_CODES')->whereId($schemeCode)->get()->toArray();
        $schemeCode = (array) current($schemeCode);


        $subGenCode = "";

        switch ($schemeCode['nri_type']) {
            case 'NRE':
                $subGenCode = '17101';
                break;

            case 'NRO':
                $subGenCode = '17151';
                break;
            
            default:
                $subGenCode = '17001';
                break;
        }

        $branchDetails = DB::table('BRANCH')->where('BRANCH_ID',$customerDetails['branch_id'])->get()->toArray();
        $branchDetails = (array) current($branchDetails);

        // $bankDetails = DB::table('BANK')->whereId($customerDetails['bank_name'])->get()->toArray();
        if($customerDetails['initial_funding_type'] == 3){
            $bankKey = 'maturity_bank_name';
        }else{
            $bankKey = 'bank_name';
        }

        $bankDetails = DB::table('BANK')->whereId($customerDetails[$bankKey])->get()->toArray();
        $bankDetails = (array) current($bankDetails);


        if(isset($bankDetails['bank_name']) && $bankDetails['bank_name'] == ''){
            return['status' => 'Error','message'=>'Bank Id not present in DB.Please Try Again Later','data' => ''];
        }
        $RequestUUID = Carbon::now()->format('dmyhhmmss');
        $RequestUUID = $customerDetails['aof_number'].substr($RequestUUID, 0,17);


       if(env('APP_SETUP') == 'DEV' || env('APP_SETUP') == 'UAT'){
            $fundingDate  = Carbon::parse($customerDetails['funding_date'])->subYears(1)->startOfDay()->format('Y-m-d\TH:i:s.v');
        }else{
            $fundingDate  = Carbon::parse($customerDetails['funding_date'])->startOfDay()->format('Y-m-d\TH:i:s.v');
        }


        $customerDetails['created_by'] = DB::table('ACCOUNT_DETAILS')->whereId($formId)->value('created_by');
        $userDetails = CommonFunctions::getUserDetails($customerDetails['created_by']);
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $empHRMSnumber = $userDetails['hrmsno'];
        }else{
            $empHRMSnumber = '';
        }
        
        $per_pcsDetails = CommonFunctions::getZipDetailsByZipCode($customerDetails['per_pincode'], $customerDetails['per_country']);
        if(count($per_pcsDetails) < 1){
           $per_pcsDetails['citycode'] = '';
           $per_pcsDetails['statecode'] = '';
        }

        $curr_pcsDetails = CommonFunctions::getZipDetailsByZipCode($customerDetails['current_pincode'], $customerDetails['current_country']);
        if(count($curr_pcsDetails) < 1){
           $curr_pcsDetails['citycode'] = '';
           $curr_pcsDetails['statecode'] = '';
        }

        //check call center 
        
        if($customerDetails['source'] == 'CC'){
            $fundingDate = Self::getCallcenterDate($formId);
            $fundingDate =  Carbon::parse($fundingDate)->startOfDay()->format('Y-m-d\TH:i:s.v');
        }


        $custName = $customerDetails['first_name'].' '.$customerDetails['middle_name'].' '.$customerDetails['last_name'];
        $accountName = CommonFunctions::getspacehandlingName($custName);
        $accountName = substr($accountName,0,80);

		if($is_huf){
         	$shortName = substr($customerDetails['last_name'], 0,9);
        }else{
        	$shortName = substr($customerDetails['short_name'], 0,9);
        }

        $tenure_months = strval(($customerDetails['years'] * 12) + $customerDetails['months']);
        $data = Array(
                     "header"=> Array(
                          "sVersion"=> "13",
                            "serReqId"=> "TDACCTADD_V1",
                            "apiVersion"=> "1.0.0.0",
                            "requestUUID"=> $RequestUUID,
                            "languageId"=> "1",
                            "isEnc"=> "Y",
                            "channelId"=> "CUBE",
                            "cifId"=> "100341795",
                            "timeStamp"=> $current_timestamp,
                     ),
                 "request"=> Array(
                        "AcctStmtNxtPrintDt"=>"",
                        "renewalDtls"=>Array(
                            "renewalTerm"=>Array(
                                 "months"=>$tenure_months,
                                 "days"=>$customerDetails['days']
                            ),
                            "autoRenewalflg"=>$autoRenewalflg,
                            "renewalOption"=>$renewalOption,
                           "RenewalSchm"=> Array(
                                 "SchmCode"=>$schemeCode['scheme_code'],
                                 "SchmType"=>$schemeCode['scheme_desc'],
                            ),
                            "autoCloseOnMaturityFlg"=> $autoCloseOnMaturityFlg,
                            "IntTblCode"=>"",
                            "RenewalAmt"=>Array(
                                "amountValue"=>"",
                                "currencyCode"=>""
                            ),
                            "MaxNumOfRenewalAllwd"=>"",
                            "SrcAcctId"=>Array(
                                "AcctId"=>"",
                                "AcctType"=>Array(
                                    "SchmCode"=>"",
                                    "SchmType"=>""
                                ),
                                "AcctCurr"=>"",
                                "BankInfo"=>Array(
                                    "PostAddr"=>Array(
                                       "Addr3"=>"",
                                       "Addr1"=>"",
                                       "Addr2"=>"",
                                       "AddrType"=>"",
                                       "StateProv"=>"",
                                       "PostalCode"=>"",
                                       "Country"=>"",
                                       "City"=>""
                                    ),
                                    "BranchId"=>"",
                                    "BranchName"=>"",
                                    "BankId"=>"",
                                    "Name"=>""
                                )
                            ),
                            "RenewalAddnlAmt"=>Array(
                                "amountValue"=>"",
                                "currencyCode"=>""
                            ),
                            "GenLedgerSubHead"=>Array(
                                "CurCode"=>"",
                                "GenLedgerSubHeadCode"=>""
                            ),
                              "RenewalCurCode"=>"INR"
                        ),
                        "solId"=>$customerDetails['branch_id'],
                        "intialDeposit"=>Array(
                            "amountValue"=>$customerDetails['td_amount'],
                            "currencyCode"=>"INR"
                        ),
                       "bankName"=>$bankDetails['bank_name'], 
                       // "bankName"=>'072',
                       "acctShortName"=>$shortName,
                       "TDAcctAdd_CustomData"=>Array(
                             ),
                        "custId"=>$customerDetails['customer_id'],
                        "OperAcctId"=>Array(
                            "AcctType"=>Array(
                                "SchmCode"=>"",
                                "SchmType"=>""
                            ),
                            "AcctCurr"=>""
                       ),
                       "currency"=>"INR",
                       "BranchName"=>$branchDetails['branch_name'],
                       "NomineeDetails"=> $nomineeDetails,
                        "PostAddr"=>Array(
                            "Addr3"=>$customerDetails['current_landmark'],
                            "Addr1"=>$customerDetails['current_address_line1'],
                            "Addr2"=>$customerDetails['current_address_line2'],
                            "AddrType"=>"C",
                            "StateProv"=>$curr_pcsDetails['statecode'],
                            "PostalCode"=>$customerDetails['current_pincode'],
                            "Country"=>"IN",
                            "City"=>$curr_pcsDetails['citycode']
                        ),
                        "PersonName"=>Array(
                            "TitlePrefix"=>$customerDetails['title'],
                            "FirstName"=>$customerDetails['first_name'],
                            "LastName"=>$customerDetails['last_name'],
                            "MiddleName"=>$customerDetails['middle_name'],
                            "Name"=>$shortName,
                        ),
                        "AcctId"=>"",
                        "tdAcctGenInfo"=>Array(
                            "curCode"=>"INR",
                            "genLedgerSubHeadCode"=>$subGenCode
                        ),
                        "SchmType"=>$schemeCode['scheme_desc'],
                        "DrIntMethodInd"=>"N",
                        "schmCode"=>$schemeCode['scheme_code'],
                        "AcctExemptFlg"=>"N",
                        "acctName"=>$accountName,
                        "depositTerm"=>Array(
                            "months"=>$tenure_months,
                            "days"=>$customerDetails['days']
                        ),
                        "MaturityDt"=>"",
                        "trnDtls"=>Array(
                            "trnSubType"=>"CI",
                            "TrnCreateMode"=>"",
                            "debitAcctId"=>Array(
                               "acctId"=> $debitAccountNumber
                            ),
                            "acctCurr"=>"INR",
                            "TrnAmt"=>Array(
                                "amountValue"=>"",
                                "currencyCode"=>""
                            ),
                            "TreaRate"=>Array(
                                "value"=>""
                            ),
                            "trnType"=>"T",
                            "PmtType"=>"",
                            "TreaRefNum"=>""
                        ),
                        "relPartyRec"=> Array(
                            Array(
                                "RecDelFlg"=>"",
                                "RelPartyCodeDesc"=>"",
                                "PersonName"=>Array(
                                        "TitlePrefix"=>$customerDetails['title'],
                                        "CustName"=>$customerDetails['first_name'].' '.$customerDetails['middle_name'].' '.$customerDetails['last_name'],
                                        "FirstName"=>$customerDetails['first_name'],
                                        "LastName"=>$customerDetails['last_name'],
                                        "MiddleName"=>$customerDetails['middle_name'],
                                        "Name"=>$shortName,
                                ),
                                "RelPartyContactInfo"=>Array(
                                    "EmailAddr"=>$customerDetails['email'],
                                    "PostAddr"=>Array(
                                        "Addr3"=>$customerDetails['per_landmark'],
                                        "Addr1"=>$customerDetails['per_address_line1'],
                                        "Addr2"=>$customerDetails['per_address_line2'],
                                        "AddrType"=>"",
                                        "StateProv"=>$per_pcsDetails['statecode'],
                                        "PostalCode"=>$customerDetails['per_pincode'],
                                        "Country"=>"IN",
                                        "City"=>$per_pcsDetails['citycode'],
                                    ),
                                    "PhoneNum"=>Array(
                                        "TelephoneNum"=>$customerDetails['mobile_number'],
                                        "TelexNum"=>"",
                                        "FaxNum"=>""
                                    )
                                ),
                                "relPartyType"=>"M",
                                "custId"=>$customerDetails['customer_id'],
                                "RelPartyCode"=>"",
                                "RelPartyTypeDesc"=>""
                            ),
                        ),
                       "despatchMode"=>"N",
                       "acctStmtMode"=>"R",
                       "BankId"=>$bankDetails['id'],
                       "AcctStmtFreq"=>Array(
                          "WeekDay"=>"",
                          "StartDt"=>"",
                          "Type"=>"",
                          "WeekNum"=>"",
                          "HolStat"=>"",
                          "Cal"=>""
                       ),
                       "EotEnabled"=>"N",
                       "RepayAcctId"=>Array(
                            // "AcctId"=>$accountNumber,
                            // "AcctType"=>Array(
                            //     "SchmCode"=>"",
                            //     "SchmType"=>""
                            // ),
                            // "AcctCurr"=>"",
                            // "BankInfo"=>Array(
                            //     "PostAddr"=>Array(
                            //         "Addr3"=>"",
                            //         "Addr1"=>"",
                            //         "Addr2"=>"",
                            //         "AddrType"=>"",
                            //         "StateProv"=>"",
                            //         "PostalCode"=>"",
                            //         "Country"=>"",
                            //         "City"=>""
                            //     ),
                            // "BranchId"=>$branchDetails['branch_id'],
                            // "BranchName"=>$branchDetails['branch_name'],
                            // "BankId"=>$bankDetails['id'],
                            // "Name"=>$bankDetails['bank_name']
                        // )
                    )
                )
            );



    // $jointApplicant =        Array(
    //                             "relPartyType" => "J",
    //                             "custId"=> "103786970"
    //                         );

    $tdAccountData = Array(  
                          "NOTES"=>"",  // tbd
                          "TRANMODE"=>"O",
                          "OPENEFFDATE"=>$fundingDate,
                          "MODEOFOPER"=>$customerDetails['code'],
                          "SOURCECODE"=>$empHRMSnumber,
                          "INTADJUSTFLAG"=>"N",
                          "TRFRIND"=>"O",
                          "AOFNUMBER"=>$customerDetails['aof_number'],
                          "MODEOFOPERCODE"=>$customerDetails['operation_type']
                     );

    if($customerDetails['no_of_account_holders'] >= 2){
        $jointApplicantArray = array();
        // $relationPartyArray = array();
        $i = 1;
        for($i=1; $i < count($accountDetails) ; $i++){
            $relationPartyData = Array("relPartyType" => "J","custId"=> $accountDetails[$i]->customer_id);
            $jointApplicantData =  Array(
                    "isMultiRec"=> "Y",
                    "SRLNUM"=> "1",
                    "CIF_ID"=> $accountDetails[$i]->customer_id,
                    "PASSSHEETFLG"=> "N",
                    "STANDINGINSTRUCTIONFLG"=> "N",
                    "DEPOSITNOTICEFLG"=> "N",
                    "LOANOVRDUNOTICEFLG"=> "N",
                    "XCLUDEFORCOMBSTMTFLG"=> "N"
                );
              array_push($jointApplicantArray,$jointApplicantData);
        $data['request']['relPartyRec'][$i] = $relationPartyData;
        }

        $data['request']['TDAcctAdd_CustomData'] = $tdAccountData;
        $data['request']['TDAcctAdd_CustomData']['JOINT'] = $jointApplicantArray;
    }else{
        $data['request']['TDAcctAdd_CustomData'] = $tdAccountData;
    }



       
    $payload = json_encode(['gatewayRequest'=>$data]);
    $client = new \GuzzleHttp\Client();
    $requestTime = Carbon::now();
    
    $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
    $guzzleClient = $client->request('POST',$url,
                                            ['headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);



        $response = json_decode($guzzleClient->getBody(),true);

        $responseTime = Carbon::now()->diffInSeconds($requestTime);

        $saveService = CommonFunctions::saveApiRequest('TD_ACCOUNT_ID',$url,$payload,json_encode($response),json_encode($data),$response,$formId, $responseTime);
        
        if(isset($response['gatewayResponse'])){
            $responsed = $response['gatewayResponse'];
            if(isset($responsed['status']['isSuccess'])  && $responsed['status']['isSuccess'] == 1){
                $encryptedResponse = json_encode($responsed['response']);
                $LIVCurrentNameResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                if(isset($LIVCurrentNameResponse['data']['TDAcctAddResponse']['TDAcctAddRs']['TDAcctId']['AcctId']) && $LIVCurrentNameResponse['data']['TDAcctAddResponse']['TDAcctAddRs']['TDAcctId']['AcctId'] != ''){
                    $tdAccountId = $LIVCurrentNameResponse['data']['TDAcctAddResponse']['TDAcctAddRs']['TDAcctId']['AcctId'];
                return['status' => 'success','message'=>'Td Account Number Successfully Created','data' => $tdAccountId];
            }else{
                return['status' => 'Error','message'=>'Error Please Try Again Later','data' => ''];
            }
        }else{
                return['status' => 'Error','message'=>'Error Please Try Again Later','data' => ''];

        }
        }else{
            return['status' => 'Error','message'=>'Error Please Try Again Later','data' => ''];
    }

        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','createaccountnumber_tdapi',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }
     
    public static function repaymentTDApi($accountNumber,$benfAccountNumber,$benefIfsc,$formId){
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022');
        $url = config('constants.APPLICATION_SETTINGS.KYC_UPDATE');
        $current_timestamp = Carbon::now()->timestamp;
        $RequestUUID = CommonFunctions::getRandomValue(17);
       
        $data = Array(
            "header" =>Array(
            "apiVersion"=> "1.0.0.0",
            "os"=> "Android",
            "sVersion"=> "13",
            "osVersion"=> "5.1",
            "serReqId"=> "KYC_FINACLE_SCRIPT",
            "timeStamp"=> $current_timestamp,
            "isEnc"=> "Y",
            "channelId"=> "CUBE",
            "languageId"=> "1",
            "requestUUID"=> $RequestUUID,
            "modelName"=> "Motorola XT1022",
            "appVersion"=> "1.0.0.0",
            "cifId"=> "100896637",
            "deviceId"=> "af8f1289-bac1-3063-bd2f-d865a99a5d6e"
        ),
        "request"=> Array(
            "executeFinacleScriptRequest"=> Array(
                "executeFinacleScriptCustomData"=> Array(
                    "foracid"=> $accountNumber,
                    "neftacctType"=> "CUBE",
                    "benAct"=> $benfAccountNumber,
                    "ifscC"=> $benefIfsc
                ),
                "executeFinacleScriptInputVO"=> Array(
                    "requestId"=> "DCB_REPAYMENT_FLOW_API.scr"
                )
            )
        )
    );

    $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
       
    $payload = json_encode(['gatewayRequest'=>$data]);
    $client = new \GuzzleHttp\Client();
    $requestTime = Carbon::now();

    $guzzleClient = $client->request('POST',$url,
                                            ['headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);




        $response = json_decode($guzzleClient->getBody(),true);
    $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $saveService = CommonFunctions::saveApiRequest('TD_ACCOUNT_REPAYMENT',$url,$payload,json_encode($response),json_encode($data),json_encode($response),$formId, $responseTime);


        if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'Success'){
            return 'Success';
        }else{
            if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] != ''){
                return $response['gatewayResponse']['status']['message'];
            }else{
                return  'Api Error! Please Try Again Later';
            }
        }
    }

    public static function ifscCodeValidation($formId,$ifscCode){
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        // $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');

        $current_timestamp = Carbon::now()->timestamp;
        $RequestUUID = CommonFunctions::getRandomValue(15);
        $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_OTHER');  // to be change
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given
        

        $data = Array(
                "header"=> Array(
                    "isEnc"=> "Y",
                    "apiVersion"=> "1.0",
                    "cifId"=> "100212262",
                    "languageId"=> "1",
                    "channelId"=> "CUBE",
                    "requestUUID"=> $RequestUUID,
                    "serReqId"=> "ESB_IFSCCODE_SCRIPTS_FIS",
                    "timeStamp"=> $current_timestamp
                ),
                "request" => Array(
                    "ifscCodeRequest"=> Array(
                        "ifscCode"=> $ifscCode
                    )
                )
            );


            $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);

            $payload = json_encode(['gatewayRequest'=>$data]);
            $client = new \GuzzleHttp\Client();
            $requestTime = Carbon::now();
    
            $guzzleClient = $client->request('POST',$url,
                                                ['headers' =>[
                                                        'Content-Type'=>'application/json',
                                                        'X-IBM-Client-secret'=>$client_key,
                                                        'X-IBM-Client-Id'=>$client_id,
                                                        'authorization'=>$authorization
                                                    ],
                                                    'json'=>['gatewayRequest'=>$data],
                                                    'exceptions'=>false
                                                ]);
    
            $response = json_decode($guzzleClient->getBody(),true);
            $responseTime = Carbon::now()->diffInSeconds($requestTime);
            $saveService = CommonFunctions::saveApiRequest('IFSC_CODE_VALIDATION',$url,$payload,json_encode($response),json_encode($data),json_encode($response),$formId, $responseTime);

            if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'SUCCESS'){
                return 'Success';
            }else{
                if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] != ''){
                    return $response['gatewayResponse']['status']['message'];
                }else{
                    return  'Api Error! Please Try Again Later';
                }
            }

    }

    public static function createaccountnumber_rdapi($formId,$schemeCodeId){
        try{
        $url = config('constants.APPLICATION_SETTINGS.RD_ACCT_ID');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); //Auth confirmed by Sreesa 12.07.22
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022');
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $RequestUUID = CommonFunctions::getRandomValue(17);

        $customerDetails = DB::table('ACCOUNT_DETAILS')->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
                                                      ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                                       ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
        
        $customerDetails = (array) current($customerDetails);

        $userDetails = CommonFunctions::getUserDetails($customerDetails['created_by']);
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $empHRMSnumber = $userDetails['hrmsno'];
        }else{
            $empHRMSnumber = '';
        }
        $nomineeDetails = DB::table('NOMINEE_DETAILS')
                                // ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT',
                                //     'RELATIONSHIP.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT')
                                ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                                // ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $nomineeDetails = (array) current($nomineeDetails);

        // add extra
        $gaurdianDetails = DB::table('NOMINEE_DETAILS')->select('REL.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                    ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                    ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $gaurdianDetails = (array) current($gaurdianDetails);

        if(isset($nomineeDetails['nominee_exists']) && $nomineeDetails['nominee_exists'] == 'yes'){
            $nominee_pcsDetails = CommonFunctions::getZipDetailsByZipCode($nomineeDetails['nominee_pincode'], $nomineeDetails['nominee_country']);
            $guardian_pcsDetails = CommonFunctions::getZipDetailsByZipCode($nomineeDetails['guardian_pincode'], $nomineeDetails['guardian_country']);
            
            $nomineeYears = \Carbon\Carbon::parse($nomineeDetails['nominee_dob'])->age;

            if($nomineeYears < 18){           
               $nominee_is_minor = 'Y';
            }else{
                $nominee_is_minor = 'N';
            }

            $nomineeFlag = 'Y';
        }else{
           $nomineeFlag = 'N';
        }

        $gaurdianCode = $gaurdianDetails['relatinship_applicant_guardian'];

        if($nomineeFlag == 'Y'){
          $nomineeDetails = Self::getNomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdianCode);
          $nomineeDetails = current($nomineeDetails['NomineeInfoRec']);
        }else{          
          $nomineeDetails = Array();
        }

        // $schemeCode = DB::table('TD_SCHEME_CODES')->whereId($schemeCodeId)->value('scheme_code');
        $schemeCode = DB::table('TD_SCHEME_CODES')->whereId($schemeCodeId)->get()->toArray();
        $schemeCode = (array) current($schemeCode);
       
        $subGenCode = "";

        switch ($schemeCode['nri_type']) {
            case 'NRE':
                $subGenCode = '17101';
                break;

            case 'NRO':
                $subGenCode = '17151';
                break;
            
            default:
                $subGenCode = '17001';
                break;
        }
       

        // echo "<pre>";print_r($customerDetails);exit;
        $months = strval(($customerDetails['years'] * 12) + $customerDetails['months']);

        $custName = $customerDetails['first_name'].' '.$customerDetails['middle_name'].' '.$customerDetails['last_name'];
        $accountName = CommonFunctions::getspacehandlingName($custName);
        $accountName = substr($accountName,0,80);

        $request = Array(

             "header"=> Array(
            "isEnc"=> "Y",
            "apiVersion"=> "10.1",
            "cifId"=> "100212262",
            "languageId"=> "1",
            "channelId"=> "CUBE",
            "requestUUID"=> $RequestUUID,
            "serReqId"=> "ESB_RDACCTADD_FIS",
            "timeStamp"=> $current_timestamp
        ),
        "request"=> Array(
            "FIXML"=> Array(
                "Body"=> Array(
                    "RDAcctAddRequest"=> Array(
                        "RDAcctAddRq"=> Array(
                            "CustId"=> Array(
                                "CustId"=> $customerDetails['customer_id']
                            ),
                            "RDAcctId"=> Array(
                                "AcctType"=> Array(
                                    "SchmCode"=> $schemeCode['scheme_code']
                                ),
                                "AcctCurr"=> "INR",
                                "BankInfo"=> Array(
                                    "BranchId"=> $customerDetails['branch_id']
                                )
                            ),
                            "RDAcctGenInfo"=> Array(
                                "GenLedgerSubHead"=> Array(
                                    "GenLedgerSubHeadCode"=> $subGenCode,
                                    "CurCode"=> "INR"
                                ),
                                "AcctName"=> $accountName,
                                "AcctShortName"=> substr($customerDetails['short_name'],0,10) ,
                                "AcctStmtMode"=> "N",
                                "DespatchMode"=> "N",
                                "SolId"=> $customerDetails['branch_id']
                            ),
                            "InitialDeposit"=> Array(
                                "amountValue"=> $customerDetails['amount'],
                                "currencyCode"=> "INR"
                            ),
                            "DepositTerm"=> Array(
                                "Days"=> "0",
                                "Months"=> $months
                            ),
                            "RepayAcctId"=> Array(
                                "AcctId"=> $customerDetails['account_number'],
                                "AcctCurr"=> "INR",
                                "BankInfo"=> ""
                            ),
                            "RenewalDtls"=> Array(
                                "AutoCloseOnMaturityFlg"=> "Y",
                                "AutoRenewalflg"=> "N",
                                "RenewalTerm"=> Array(
                                    "Days"=> "0",
                                    "Months"=> $months
                                ),
                                "RenewalCrncy"=> "INR",
                                "RenewalOption"=> "P"
                            ),
                            "NomineeInfoRec"=> $nomineeDetails,
                            "TrnDtls"=> Array(
                                "TrnType"=> "T",
                                "TrnSubType"=> "CI",
                                "DebitAcctId"=> Array(
                                    "AcctId"=> $customerDetails['account_number'],
                                    "AcctType"=> Array(
                                        "SchmCode"=> "SB118",
                                        "SchmType"=> "SBA"
                                    ),
                                    "AcctCurr"=> "INR"
                                )
                            ),
                            "RelPartyRec"=> Array(
                                "RelPartyType"=> "M",
                                "CustId"=> Array(
                                    "CustId"=> $customerDetails['customer_id']
                                )
                            )
                        ),
                        "RDAcctAdd_CustomData"=> Array(
                            "RANMODE"=> "O",
                            "TRFRIND"=> "O",
                            "AOFNUMBER"=> $customerDetails['aof_number'],
                            "MODEOFOPER"=> $customerDetails['code'],
                            "SOURCECODE"=> $empHRMSnumber
                        )
                    )
                )
            )
        )
    );
        $request['request'] = EncryptDecrypt::AES256Encryption(json_encode($request['request']),$encrypt_key);
     $payload = json_encode(['gatewayRequest'=>$request]);

        $client = new \GuzzleHttp\Client();
        $guzzleClient = $client->request('POST',$url,
                                            ['headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$request],
                                                'exceptions'=>false
                                            ]);


        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);


        $saveService = CommonFunctions::saveApiRequest('RD_ACCOUNT_ID',$url,$payload,json_encode($response),json_encode($request),$response,$formId, $responseTime);

        if(isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ER000'){
            $rdResponse = json_decode(EncryptDecrypt::AES256Decryption(json_encode($response['gatewayResponse']['response']),$encrypt_key),true);
            if(isset($rdResponse['data']['RDAcctAddResponse']['RDAcctAddRs']['TDAcctId']['AcctId']) && $rdResponse['data']['RDAcctAddResponse']['RDAcctAddRs']['TDAcctId']['AcctId'] != ''){
                $accountNumber = $rdResponse['data']['RDAcctAddResponse']['RDAcctAddRs']['TDAcctId']['AcctId'];

                return['status' => 'success','message'=>'RD Account Number Successfully Created','data' => $accountNumber];
            }else{
                return['status' => 'Error','message'=>'Error Please Try Again Later','data' => ''];
            }
        }else{
            return['status' => 'Error','message'=>'Error Please Try Again Later','data' => ''];
        }

        }catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','createaccountnumber_rdapi',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getCallcenterDate($formId){
        try{
        
        $fundingDate = '';

        $getTdDetails = DB::table('TD_DETAILS')->select('CREATION_DATE','REVIEW_DATE','CUSTOMER_DATE','SELECTED_VALUE_TYPE')
                                                ->where('FORM_ID',$formId)
                                                ->get()
                                                ->toArray();
                                            
        $getTdDetails = (array) current($getTdDetails);
        
        switch ($getTdDetails['selected_value_type']) {
            case 'C':
                    $fundingDate = $getTdDetails['creation_date'];
                break;
            case 'R':
                    $fundingDate = $getTdDetails['review_date'];
                break;
            case 'E':
                    $fundingDate = $getTdDetails['customer_date'];
                break;
        }

        if($fundingDate == ''){
            $fundingDate = Carbon::now();
        }

        return $fundingDate;
            
        }catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','getCallcenterDate',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function createeliteaccountid($formId)
    {
        try{
        $client = new \GuzzleHttp\Client();
        $url = config('constants.APPLICATION_SETTINGS.SB124ACCOUNTCREATION');  // to be change
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        if(env('APP_SETUP')=='PRODUCTION'){
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        }else{
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        }
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
//        $requestUUID = '566e1e75-2e34-7ba5-d237-a4a9c58707bs';
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');

        $hash1 = hash('sha256', $timestamp);
        $hash2 = hash('md5', $timestamp);
        $uuid = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);

        $customerData = DB::table('ACCOUNT_DETAILS')->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
                                            ->leftjoin('FINCON','FINCON.FORM_ID','ACCOUNT_DETAILS.ID')
                                            ->leftjoin('RISK_CLASSIFICATION_DETAILS','RISK_CLASSIFICATION_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
                                            ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                            ->leftjoin('NOMINEE_DETAILS','NOMINEE_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId);
        
        $firstApplicantData = $customerData->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','ASC')->get()->toArray();
        $firstApplicantData = (array) current($firstApplicantData);
        
        $getTitle = DB::table('TITLE')->select('TITLE')->where('is_active',1)
                                                        ->where('ID',$firstApplicantData['title'])
                                                        ->get()->toArray();
        $getTitle = (array) current($getTitle);
        
        $getaddressData = Self::getAddressData($firstApplicantData['per_pincode']);

        //accoutnnumber

        $genAccountNumber = $firstApplicantData['branch_id'].'124'.$firstApplicantData['elite_account_number'];

        $annual_turnover = config('constants.ANNUAL_TURNOVER')[$firstApplicantData['annual_turnover']];
        $annual_turnover = str_replace(' Lakh', '00000', $annual_turnover);
        $annual_turnover = str_replace(' Crore', '0000000', $annual_turnover);  
        $annual_turnover = str_replace('Upto ', '', $annual_turnover);

        $eliteAccountNo = $firstApplicantData['branch_id'].'124'.$firstApplicantData['elite_account_number'];
        
        
        //check nominee minor flag 
        $getNomineeAge = Carbon::parse($firstApplicantData['nominee_dob'])->age;

        if($getNomineeAge < 18){
            $nomineeMinor = 'Y';
        }else{
            $nomineeMinor = 'N';
        }
        
        $getAccDetailsData = DB::table('ACCOUNT_DETAILS')->select('CREATED_BY')
                                                         ->whereId($formId)
                                                         ->get()
                                                         ->toArray();

        $getAccDetailsData = (array) current($getAccDetailsData);                                      
        
        $userDetails = CommonFunctions::getUserDetails($getAccDetailsData['created_by']);
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $empHRMSnumber = $userDetails['hrmsno'];
        }else{
            $empHRMSnumber = '';
        }

        $getOccupationCode = DB::table('OCCUPATION')->select('CODE')
                                                    ->where('ID',$firstApplicantData['occupation'])
                                                    ->get()
                                                    ->toArray();
        $getOccupationCode = (array) current($getOccupationCode);

        $nomineeRelationshipCode = Self::getRelationship($firstApplicantData['relatinship_applicant']);
        //Get Nominmee and gaudiean city,state and country code
        
        $getaddressNomineeData = Self::getAddressData($firstApplicantData['nominee_pincode']);

        $getaddressGaineeData = Self::getAddressData($firstApplicantData['guardian_pincode']);
        
        $gaRelationshipCode = Self::getRelationship($firstApplicantData['relatinship_applicant_guardian']);
        
        if(isset($gaRelationshipCode['code']) && $gaRelationshipCode['code'] != ''){
            $gaurdianCode = CommonFunctions::getGaurdianCode($gaRelationshipCode['code']);
        }else{
            $gaurdianCode = 'O';
        }
    //    echo "<pre>";print_r($gaurdianCode);exit;
        // joint application holder flow 
        $getJointapplicantData = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                    ->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','ASC')
                                                                    ->get()->toArray();
        

        $jointName = '';
        $joinCustid = '';
        $getJoinTitle = '';

        $jschmCode = '';
        $jrelationtype = '';
        $jaddressType = '';
        
        $jaddress1 = '';
        $jaddress2 = '';
        $jcityCode = '';
        $jcityDesc = '';
        $jstateCode = '';
        $jstateDesc = '';
        $jcntryCode = '';
        $jcntryDesc = '';
        $jpinCode = '';
        
        if(count($getJointapplicantData)>1){
            for($seq=1;count($getJointapplicantData)>$seq;$seq++){
                
                $jointName .= $getJointapplicantData[$seq]->first_name.' '.$getJointapplicantData[$seq]->middle_name.' '.$getJointapplicantData[$seq]->last_name.'|';
                
                $joinCustid .= $getJointapplicantData[$seq]->customer_id.'|';
    
            $getJTitle = DB::table('TITLE')->select('TITLE')->where('is_active',1)
                                            ->where('ID',$getJointapplicantData[$seq]->title)
                                        ->get()->toArray();
            $getJTitle = (array) current($getJTitle);
           
                $getJoinTitle .= $getJTitle['title'].'|';
                $jpinCode .= $getJointapplicantData[$seq]->per_pincode.'|';
                $getaddressJoineeData = Self::getAddressData($getJointapplicantData[$seq]->per_pincode);
            
                $jaddress1 .=  $getJointapplicantData[$seq]->per_address_line1.'|';
                
                $jaddress2 .=  $getJointapplicantData[$seq]->per_address_line2.'|';
    
                $jcityCode .=  $getaddressJoineeData['citycode'].'|';
                $jcityDesc .=  $getJointapplicantData[$seq]->per_city.'|';
    
                $jstateCode .=  $getaddressJoineeData['statecode'].'|';
                $jstateDesc .=  $getJointapplicantData[$seq]->per_state.'|';
    
                $jcntryCode .= $getaddressJoineeData['countrycode'].'|';
                $jcntryDesc .= $getaddressJoineeData['countrydesc'].'|';

                $jrelationtype .= 'J'.'|';
                $jschmCode .= 'SB124'.'|';
                $jaddressType .= 'Home'.'|';
            }

        } 
        // exit;
        $nomineePerc = '';
        if($firstApplicantData['nominee_name'] != ''){
            $nomineePerc = '100';
        }

        $custName = $firstApplicantData['first_name'].' '.$firstApplicantData['middle_name'].' '.$firstApplicantData['last_name'];
        $accountName = CommonFunctions::getspacehandlingName($custName);
        $accountName = substr($accountName,0,80);

         $request = Array(
                        'header' => Array
                        (
                                "apiVersion"=> "1.0",
                                "appVersion"=> "1.0.94.0",
                                "channelId"=> "CUBE",
                                "cifId"=> $firstApplicantData['customer_id'],
                                "deviceId"=> "652F1351-A479-4544-B0C2-949602425720",
                                "modelName"=> "iPhone X",
                                "languageId"=> "1",
                                "os"=> "iOS",
                                "osVersion"=> "11",
                                "requestUUID"=> $uuid,
                                "serReqId"=> "ESB_SBACCTOPEN_SCRIPTS_FIS",
                                "sVersion"=> "20",
                                "timeStamp"=> $timestamp,
                                "isEnc"=> "Y"
                            ),
                            "request" =>  Array(
                                    "executeFinacleScriptRequest" => Array(
                                        "executeFinacleScript_CustomData" => Array( 
                                            "cifId"=> $firstApplicantData['customer_id'],
                                            "pbPsFlg"=> "B",
                                            "annualTurnOver"=> $annual_turnover,
                                            "schmCode"=> "SB124",
                                            "crncyCode"=> "INR",
                                            "solId"=> $firstApplicantData['branch_id'],
                                            "glSubHeadCode"=> "14001",
                                            "schmType"=> "SBA",
                                            "acctOpenDate"=> Carbon::now()->format('Y-m-d\TH:i:s.v'),
                                            "acctName"=> $accountName,
                                            "shortName"=> $firstApplicantData['short_name'],
                                            "aofNumber"=> $firstApplicantData['aof_number'],
                                            "modeOfOper"=> $firstApplicantData['code'],
                                            "intcrCode"=> "S",
                                            "intdrCode"=> "S",

                                            // "jointRelationCode"=> "509",
                                            "regValue"=> "001",
                                            "nomFlag"=> $firstApplicantData['nominee_exists'] == 'yes'? 'Y':'N',
                                            "sourceCode"=> $empHRMSnumber,
                                            "acctOccpCode"=> isset($getOccupationCode['code']) && $getOccupationCode['code'] != ''? $getOccupationCode['code']:'',
                                            "nomSrlNum"=> "001",
                                            "nomCifId"=> "",
                                            "nomName"=> $firstApplicantData['nominee_name'],
                                            "relation"=> isset($nomineeRelationshipCode['code']) && $nomineeRelationshipCode['code'] !=''?$nomineeRelationshipCode['code']:'',
                                            "nomAddrLine1"=> $firstApplicantData['nominee_address_line1'],
                                            "nomCityCode"=> isset($getaddressNomineeData['citycode']) && $getaddressNomineeData['citycode'] !=''?$getaddressNomineeData['citycode']:'',
                                            "nomCntryCode"=> isset($getaddressNomineeData['countrycode']) && $getaddressNomineeData['countrycode'] !=''?$getaddressNomineeData['countrycode']:'',
                                            "nomStateCode"=> isset($getaddressNomineeData['statecode']) && $getaddressNomineeData['statecode'] !=''?$getaddressNomineeData['statecode']:'',
                                            "nomPostalCode"=> $firstApplicantData['nominee_pincode'],
                                            // "dtOfBirth"=>  Carbon::now()->subDays(2000)->format('Y-m-d\TH:i:s.v'),
                                            "dtOfBirth"=>  $firstApplicantData['nominee_dob'],
                                            "nomMinorFlg"=> $nomineeMinor,
                                            "nomPcnt" => $nomineePerc,
                                            "grdnName"=> $firstApplicantData['guardian_name'],
                                            // "grdnCode"=> "F",
                                            "grdnCode"=> $gaurdianCode,
                                            "nomGuardAddr1"=> $firstApplicantData['guardian_address_line1'],
                                            "nomGuardAddr2"=> $firstApplicantData['guardian_address_line2'],
                                            "nomGuardCityCode"=> isset($getaddressGaineeData['citycode']) && $getaddressGaineeData['citycode'] !=''?$getaddressGaineeData['citycode']:'',
                                            "nomGuardStateCode"=> isset($getaddressGaineeData['statecode']) && $getaddressGaineeData['statecode'] !=''?$getaddressGaineeData['statecode']:'',
                                            "nomGuardCntryCode"=> isset($getaddressGaineeData['countrycode']) && $getaddressGaineeData['countrycode'] !=''?$getaddressGaineeData['countrycode']:'',
                                            "nomGuardPinCode"=> $firstApplicantData['guardian_pincode'],

                                            "relationType"=> "M",
                                            "custTitle"=> $getTitle['title'],
                                            "addressType"=> "Home",
                                            "address1"=> $firstApplicantData['per_address_line1'],
                                            "address2"=> $firstApplicantData['per_address_line2'],
                                            "cityCode"=> $getaddressData['citycode'],
                                            "cityDesc"=> $firstApplicantData['per_city'],
                                            "stateCode"=> $getaddressData['statecode'],
                                            "stateDesc"=> $firstApplicantData['per_state'],
                                            "cntryCode"=> $getaddressData['countrycode'],
                                            "cntryDesc"=> $getaddressData['countrydesc'],
                                            "pinCode"=> $firstApplicantData['per_pincode'],

                                            "jcustName"=> isset($jointName) && $jointName !=''?$jointName:'',
                                            "jcifId"=> isset($joinCustid) && $joinCustid != ''?$joinCustid:'',
                                            "jschmCode"=> isset($jschmCode) && $jschmCode != ''?$jschmCode:'',
                                            "jrelationType"=> isset($jrelationtype) && $jrelationtype != ''?$jrelationtype:'',
                                            "jcustTitle"=> isset($getJoinTitle) && $getJoinTitle !=''?$getJoinTitle:'',
                                            "jaddressType"=> isset($jaddressType) && $jaddressType !=''?$jaddressType:'',
                                            "jaddress1"=> isset($jaddress1) && $jaddress1 != ''?$jaddress1:'',
                                            "jaddress2"=> isset($jaddress2) && $jaddress2 != ''?$jaddress2:'',
                                            "jcityCode"=> isset($jcityCode) && $jcityCode != ''?$jcityCode:'',
                                            "jcityDesc"=> isset($jcityDesc) && $jcityDesc != ''?$jcityDesc:'',
                                            "jstateCode"=>  isset($jstateCode) && $jstateCode != ''?$jstateCode:'',
                                            "jstateDesc"=>  isset($jstateDesc) && $jstateDesc != ''?$jstateDesc:'',
                                            "jcntryCode"=>  isset($jcntryCode) && $jcntryCode != ''?$jcntryCode:'',
                                            "jcntryDesc"=> isset($jcntryDesc) && $jcntryDesc != ''?$jcntryDesc:'',
                                            "jpinCode"=> isset($jpinCode) && $jpinCode != ''?$jpinCode:'',

                                            "perAcct"=> $genAccountNumber
                                        ),
                                ),
                            ),
        );
        // echo "<pre>";print_r($request);
         $request['request'] = EncryptDecrypt::AES256Encryption(json_encode($request['request']),$encrypt_key);
            $guzzleClient = $client->request('POST',$url,
                                            ['headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$request],
                                                'exceptions'=>false
                                            ]);
            $response = json_decode($guzzleClient->getBody(),true);
            $responseTime = Carbon::now()->diffInSeconds($requestTime);
            $saveService = CommonFunctions::saveApiRequest('ACCOUNT_ID',$url,'',json_encode($response),
                                                                                json_encode($request['request']),$response,$formId, $responseTime);

        if(isset($response['gatewayResponse'])){
            
            $response = $response['gatewayResponse'];
                                                                                       
                if(isset($response['response']) && $response['status']['isSuccess'] == '1'){

                    $encryptedResponse = json_encode($response['response']);
                    $ekycDetailsResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                    // echo "<pre>";print_r($ekycDetailsResponse);
                    $accountNumber = '';
                    if($ekycDetailsResponse['data']['executeFinacleScriptResponse']['executeFinacleScript_CustomData']['PerAcct'] != ''){
                        $accountNumber = $ekycDetailsResponse['data']['executeFinacleScriptResponse']['executeFinacleScript_CustomData']['PerAcct'];
                        $accountStatus = $ekycDetailsResponse['data']['executeFinacleScriptResponse']['executeFinacleScript_CustomData']['AccountStatus'];

                        $updateAccountId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                ->update([
                                                                            'ACCOUNT_NO'=>$accountNumber,
                                                                            'APPLICATION_STATUS'=>14
                                                                        ]);
                        if($updateAccountId){
                            $updateApplicationStatus = CommonFunctions::updateApplicationStatus('ACCOUNT_OPENED',$formId);
                            if($firstApplicantData['account_type'] == 1){
                                Rules::postAccountIdApiQueue($formId,1);
                            }

                            return ['status'=>'Success','data'=>$accountNumber];
                        }
                         return $accountNumber;
                    }else{
            		    return (['status'=>'fail','message'=> $response['status']['message'],'data'=>[]]);
                    }

                }else{
                    return['status' => 'Error','message'=>$response['status']['message'],'data' => ''];
                }
            }else{
                return['status' => 'Error','message'=>$response['status']['message'],'data' => ''];
            }
        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','createeliteaccountid',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getAddressData($pincode){
        try{
        $getaddressData = DB::table('FIN_PCS_DESC')->select('STATECODE','CITYCODE','COUNTRYCODE','COUNTRYDESC')
                                                   ->where('PINCODE',$pincode)
                                                   ->get()
                                                   ->toArray();
        $getaddressData = (array) current($getaddressData);

        return $getaddressData;
        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','getAddressData',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getRelationship($relationId){
        try{
        $relationshipCode = DB::table('RELATIONSHIP')->select('CODE')
                                                    ->where('ID',$relationId)
                                                    ->get()
                                                    ->toArray();

        $relationshipCode =  (array) current($relationshipCode);

        return $relationshipCode;

        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/Api','getRelationship',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }


    public static function esbSicreation($formId){
        try{
        $formId = (array) current($formId);
        // echo "<pre>";print_r($formId);exit;
        
      
        $url = config('constants.APPLICATION_SETTINGS.SICREATION');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); 
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');


        $custDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('TD_AMOUNT','INITIAL_FUNDING_DATE','CUSTOMER_ID',      
                                                                'ACCOUNT_NUMBER','YEARS','MONTHS')
                                                       ->where('FORM_ID',$formId[0])
                                                       ->get()
                                                       ->toArray();
        $custDetails = (array) current($custDetails);

        $accountDetails = DB::table('ACCOUNT_DETAILS')->select('BRANCH_ID','TD_ACCOUNT_NO','AOF_NUMBER')
                                                      ->where('ID',$formId[0])
                                                      ->get()
                                                      ->toArray();

        $accountDetails = (array) current($accountDetails);

        //get date for selected date
        $firstDate = Carbon::parse($custDetails['initial_funding_date'])->format('d');

        //get current date to next month date
        $getNextMonth = Carbon::parse($custDetails['initial_funding_date'])->addMonth()->format('d-m-Y');

        //1 month beacuse siendaterequired
        $getTotalMonth = $custDetails['years']*12+$custDetails['months']-1;

        $getSienDate = Carbon::parse($custDetails['initial_funding_date'])->addMonth($getTotalMonth)->format('d-m-Y');
        // echo "<pre>";print_r($getSienDate);exit;
       
        $data =  Array(
                "header"=> Array(
                    "isEnc"=> "Y",
                    "apiVersion"=> "10.1",
                    "cifId"=> "100212262",
                    "languageId"=> "1",
                    "channelId"=> "CUBE",
                    "requestUUID"=> "SI".$accountDetails['aof_number'],
                    "serReqId"=> "ESB_SICREATION_SCRIPTS_FIS",
                    "timeStamp"=> $timestamp
                ),
                "request"=> Array(
                    "executeFinacleScriptRequest"=> Array(
                        "executeFinacleScriptInputVO"=> Array(
                            "requestId"=> "dcb_SICreation.scr"
                        ),
                        "executeFinacleScriptCustomData"=> Array(
                            "CUSTID"=> $custDetails['customer_id'],
                            "SOLID"=> $accountDetails['branch_id'],
                            "SISTARTDATE"=> $firstDate,
                            "NEXTEXECDATE"=> $getNextMonth,
                            "SIENDDATE"=> $getSienDate,
                            "DRACCTNUM"=> $custDetails['account_number'],
                            "CRACCTNUM"=> $accountDetails['td_account_no'],
                            "AMT"=> $custDetails['td_amount'],
                            "FIXEDAMT"=> $custDetails['td_amount']
                        )
                )
            )
        );
        
        $plain_req = $data['request'];
        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest' => $data]);
        $client = new \GuzzleHttp\Client();
			
       
        $guzzleClient = $client->request('POST',$url,
                                        ['headers' =>[
                                                'Content-Type'=>'application/json',
                                                'X-IBM-Client-secret'=>$client_key,
                                                'X-IBM-Client-Id'=>$client_id,
                                                'authorization'=>$authorization
                                            ],
                                            'json'=>['gatewayRequest'=>$data],
                                            'exceptions'=>false
                                        ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $saveService = CommonFunctions::saveApiRequest('SI_CREATION',$url,'','',json_encode($data),$response,$formId[0],$responseTime); 
        
        if((isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == "Success") && (isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == '1')){
            return ['status'=>'success','message'=>'SI Creation Successfully','data'=>$response];
        }else{
            return ['status'=>'Error','message'=>'SI Creation Failed!','data'=>$response];
        }
       
            }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
                CommonFunctions::addLogicExceptionLog('Helpers/Api','ESBSicreation',$eMessage,'',$formId);
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function newPanIsValid($pandetails,$formId = ''){
        $url = config('constants.APPLICATION_SETTINGS.NEW_PAN_API');
        $client_id = config('constants.APPLICATION_SETTINGS.PAN_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.PAN_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.PAN_ENCRYPT_KEY_2022'); 
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
        $RequestUUID = CommonFunctions::getRandomValue(11);
        $data = Array(
            "header"=> Array(
                "isEnc"=> "Y",
                "apiVersion"=> "1.0",
                "cifId"=> "100212262",
                "languageId"=> "1",
                "channelId"=> "CUBE",
                "requestUUID"=> $RequestUUID,
                "serReqId"=> "ESB_VALIDATEPAN_NSDL",
                "timeStamp"=> $timestamp
            ),
            "request"=> Array(
                "panDetails"=>$pandetails
                    // Array(
                    //     "panNo"=> "AFQPG1785B",
                    //     "name"=> "SAMRUDDHI",
                    //     "dob"=> "05/11/1966"
                    // ),
            )
        );
        // echo "<pre>";print_r($data);
        $plain_req = $data['request'];
        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest' => $data]);
        $client = new \GuzzleHttp\Client();
        $guzzleClient = $client->request('POST',$url,
                                        ['headers' =>[
                                                'Content-Type'=>'application/json',
                                                'X-IBM-Client-secret'=>$client_key,
                                                'X-IBM-Client-Id'=>$client_id,
                                                'authorization'=>$authorization
                                            ],
                                            'json'=>['gatewayRequest'=>$data],
                                            'exceptions'=>false
                                        ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime); 

        $saveService = CommonFunctions::saveApiRequest('NSDL_API',$url,'','',json_encode($data),$response,$formId, $responseTime);

        if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == 1){
            // echo "<pre>";print_r($response['gatewayResponse']);exit;
            $response = json_encode($response['gatewayResponse']['response']);
            $responseEnc = json_decode(EncryptDecrypt::AES256Decryption($response,$encrypt_key),true);
            //  echo "<pre>";print_r($response1);exit;
            return ['status'=>'success','msg'=>'','data'=>$responseEnc['data']['panDetails']];
        }else{
            return ['status'=>'fail','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        }   
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
    }



    public static function accountOpeningNiyoCallback($form_id='',$accountopeningDate){ 

        try{
            
            if($form_id == ""){
                return ['status'=>'fail','msg'=>'Error! Form id is blank','data'=>[]];
            }
        
        $custData = [];
        $accountDetails = DB::table("ACCOUNT_DETAILS")
                              ->select('ACCOUNT_NO','BRANCH_ID')
                              ->where("ID",$form_id)
                              ->get()->toArray();
                             
        $accountDetails = (array) current($accountDetails);

        $ovdDetails = DB::table("CUSTOMER_OVD_DETAILS")
                             ->select('MOBILE_NUMBER','EMAIL','PANCARD_NO','CUSTOMER_ID')
                             ->where("FORM_ID",$form_id)
                             ->get()->toArray();                             
        $ovdDetails = (array) ($ovdDetails);

        $accountNumber = $accountDetails["account_no"];
        $sole_id = $accountDetails["branch_id"];
        $serviceName='NIYO_UPDATE';

        foreach ($ovdDetails as $k => $val) {
            $v = (array) $val;
            $tcust_data = [
                "mobile" => $v['mobile_number'],
                "email" => $v['email'],
                "pan" => $v['pancard_no'],
                "customerId" => $v['customer_id'],
                "accountId" => $accountNumber,
                "channelId" => "CUBE",
                "solId" => $sole_id,
                "accountOpeningDate" => $accountopeningDate
            ];
            array_push($custData,$tcust_data);
        }        
        $client_id = config('constants.APPLICATION_SETTINGS.PAN_CLIENT_ID'); 
        $client_key = config('constants.APPLICATION_SETTINGS.PAN_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.PAN_ENCRYPT_KEY_2022'); 
        $url = config('constants.APPLICATION_SETTINGS.ACCOUNTOPEN_NIYO'); 
        
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
        $RequestUUID = CommonFunctions::getRandomValue(11);        

        $data = Array(
            "header"=> Array(
                "isEnc"=> "Y",
                "apiVersion"=> "1.0",
                "cifId"=> "100212262",
                "languageId"=> "1",
                "channelId"=> "CUBE",
                "requestUUID"=> $RequestUUID,
                "serReqId"=> "ESB_BRANCHKYCCALLBACK_NIYO",
                "timeStamp"=> $timestamp
            ),
            "request"=> Array(
                "data" => $custData
                        
            )
        );
        $plain_req = $data['request'];
        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest' => $data]);
        $client = new \GuzzleHttp\Client();
        $guzzleClient = $client->request('POST',$url,
                                            [                                             
                                                'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                'exceptions'=>false
                                            ]);
        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime); 
        $saveService = CommonFunctions::saveApiRequest($serviceName,$url,'','',json_encode($data),$response,$form_id, $responseTime);
        
        if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == 1){
            
            $response = json_encode($response['gatewayResponse']['response']);
            $responseEnc = json_decode(EncryptDecrypt::AES256Decryption($response,$encrypt_key),true);
            
            return ['status'=>'success','msg'=>$responseEnc['message'],'data'=>$responseEnc['statusCode']];

        }
        else{
            return ['status'=>'fail','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        }

    }catch(\Throwable $e){
        if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        $eMessage = $e->getMessage();
        CommonFunctions::addLogicExceptionLog('Helpers/Api','Niyoupdate',$eMessage,'',$form_id);
        return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }

    }
}
?>
