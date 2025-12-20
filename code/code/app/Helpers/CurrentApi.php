<?php
namespace App\Helpers;

use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;
use Carbon\Carbon;
use Crypt,Cache,Session;
use DB;

class CurrentApi {
        public static function CurrentAccountApi($formId)
        {

            $global_Details = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $global_Details = current($global_Details);

            $getschemeDesc = CommonFunctions::getSchemeCodesDesc('Current',$global_Details->scheme_code);

            // echo "<pre>";print_r($getschemeDesc->scheme_code);exit;

            $is_huf = false;
            $jointholderdata = [];
            if($global_Details->constitution == "NON_IND_HUF"){
                $is_huf = true;
            }
            if($is_huf){
            //for huf applicant sequence should reverse account to be created with huf cust id and joint applicant should be karta hence desending
                $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('form_Id',$formId)
                ->orderBy('APPLICANT_SEQUENCE','DESC')->get()->toArray();
                $jointholderdata = $customerDetails;
                $customerDetails = (array) current($customerDetails);
            }else{
            $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('form_Id',$formId)->get()->toArray();
            $customerDetails = (array)current($customerDetails);
            } 
               
            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
            $accountDetails = (array)current($accountDetails);

            $accountname = $customerDetails['first_name'].' '.$customerDetails['middle_name'].' '.$customerDetails['last_name'];
            $formId = $customerDetails['form_id'];

            $entityDetails = DB::table('ENTITY_DETAILS')->leftjoin('FIN_PCS_DESC','FIN_PCS_DESC.PINCODE','ENTITY_DETAILS.ENTITY_PINCODE')
                ->leftjoin('CA_SCHEME_CODES','CA_SCHEME_CODES.ID','ENTITY_DETAILS.ENTITY_SCHEME_CODE')
                ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ENTITY_DETAILS.ENTITY_MOP')
                                                        ->where('ENTITY_DETAILS.FORM_ID',$formId)
                                                        ->get()->toArray();
            $entityDetails = (array) current($entityDetails);
            $shortName = substr($customerDetails['short_name'], 0,10);

            $userDetails = CommonFunctions::getUserDetails($accountDetails['created_by']);
            if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
                $empHRMSnumber = $userDetails['hrmsno'];
            }else{
                $empHRMSnumber = '';
            }


            $currenteliteAccount = '';
            if($customerDetails['elite_account_number'] != ''){
                $currenteliteAccount = $accountDetails['branch_id'].'224'.$customerDetails['elite_account_number'];
            }
            
if(!$is_huf){
            $nomineeDetails = DB::table('NOMINEE_DETAILS')
                                // ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT',
                                //     'RELATIONSHIP.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT')
                                ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                                //->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
            $nomineeDetails = (array) current($nomineeDetails);

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
              $nomineeDetails = Self::getCANomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdianCode);
           }else{          
              $nomineeDetails = Array();
           }
        }

            $authusername = 'esbtest';
            if(env('APP_SETUP') == 'PRODUCTION'){
                $authusername = 'cube';
            }

            $getCityStateCode = DB::table('FIN_PCS_DESC')->select('CITYCODE','STATECODE')
                                                         ->where('PINCODE',$customerDetails['per_pincode'])
                                                         ->get()
                                                         ->toArray();
            $getCityStateCode = (array) current($getCityStateCode);
          
            $annualturnover = DB::table('RISK_CLASSIFICATION_DETAILS')->select('ANNUAL_TURNOVER')
                                                                      ->where('FORM_ID',$formId)
                                                                      ->get()
                                                                      ->toArray();
            $annualturnover = (array) current($annualturnover);

            $modeOperationCode = DB::table('MODE_OF_OPERATIONS')->select('CODE')
                                                                ->whereId($accountDetails['mode_of_operation'])
                                                                ->get()
                                                                ->toArray();
            $modeOperationCode = (array) current($modeOperationCode);

            $getSchemeCode = DB::table('CA_SCHEME_CODES')->select('SCHEME_CODE')
                                                        ->whereId($accountDetails['scheme_code'])
                                                        ->get()
                                                        ->toArray();
            $getSchemeCode = (array) current($getSchemeCode);

            $annual_turnover = config('constants.ANNUAL_TURNOVER')[$annualturnover['annual_turnover']];
            $annual_turnover = str_replace(' Lakh', '00000', $annual_turnover);
            $annual_turnover = str_replace(' Crore', '0000000', $annual_turnover);  
            $annual_turnover = str_replace('Upto ', '', $annual_turnover);

            $relationparty = '';
            $jointApplicantData = '';
            if(count($jointholderdata) > 1){
            for($i=1; $i<count($jointholderdata);$i++){

                $relationparty = 
            
                Array(
                           "RelPartyType"=> "J",
                           "relPartyCode"=> "001",
                           "CustId"=> $jointholderdata[$i]->customer_id,
                );
                       
                $jointApplicantData =  Array(
                    "isMultiRec"=> "Y",
                    "SRLNUM"=> "1",
                    "CIF_ID"=> $jointholderdata[$i]->customer_id,
                    "PASSSHEETFLG"=> "N",
                    "STANDINGINSTRUCTIONFLG"=> "N",
                    "DEPOSITNOTICEFLG"=> "N",
                    "LOANOVRDUNOTICEFLG"=> "N",
                    "XCLUDEFORCOMBSTMTFLG"=> "N"
                );
            }
        }

        if($is_huf){
            $nomineeDetails = Array(
                 "regNum" => "001",
                 "nomineeName" => "",
                 "relType" => "",
                 "nomineeContactInfo"=> Array(
                     "phoneNum" => Array(
                     "telephoneNum" => "",
                   ),
                 "postAddr" => Array(
                     "addr1" => "",
                     "addr2" => "",
                     "addr3" => "",
                     "city" => "",
                     "stateProv" =>"",
                     "postalCode" => "",
                     "country" => "",
                     "addrType" => "Mailing"
                    )
                 ),
                 "nomineeMinorFlg" => "N",
                 "nomineeBirthDt" => "",
                 "nomineePercent" => Array(
                   "value" => 100
                 ),
                 "guardianInfo" => "",
             );
         }
        
            $client = new \GuzzleHttp\Client();
            $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_ID');  // to be change
            $client_id = config('constants.APPLICATION_SETTINGS.CA_CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.CA_CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_ENCRYPT_KEY');
            $current_timestamp = Carbon::now()->timestamp;
            $requestTime = Carbon::now();
            $requestUUID = CommonFunctions::getRandomValue(13);

             $data = Array(
                         "header"=> Array(
                        "isEnc"=> "Y",
                        "apiVersion"=> "1.0",
                        "cifId" => "103354927",
                        "languageId"=> "1",
                        "channelId"=> "CUBE",
                        "requestUUID" => strval($requestUUID),
                        "sVersion" => "20",
                        "serReqId" => "ESB_ACCOUNTCREATION_CAA_FIS",
                        "timeStamp"=> strval($current_timestamp),
                    ),
                    'request' => Array(
                         "FIXML" => Array(
                            "Body" => Array(
                                "CAAcctAddRequest" => Array(
                                    "CAAcctAddRq" => Array(
                                        "CustId"=> Array(
                                            "CustId"=> $customerDetails['customer_id'],
                                        ),
                                        "CAAcctId"=> Array(
                                            "AcctType"=> Array(
                                                "SchmCode"=> $getSchemeCode['scheme_code'],
                                            ),
                                            "AcctCurr"=> "INR",
                                            "BankInfo"=> Array(
                                                "BranchId"=> $accountDetails['branch_id'],
                                        ),
                                            // "CAAcctId"=>$currenteliteAccount,
                                        ),
                                        "CAAcctGenInfo"=> Array(
                                            "GenLedgerSubHead"=> Array(
                                                "GenLedgerSubHeadCode"=> "12001",
                                                "CurCode"=> "INR"
                                            ),
                                            "AcctName"=> $accountname,
                                            "AcctShortName"=> $shortName,
                                            "AcctStmtMode"=> "N",
                                            "DespatchMode"=> "N"
                                        ),
                                    'nomineeInfoRec' => $nomineeDetails,
                                    ),

                                    "CAAcctAdd_CustomData" => Array(
                                        "ADD1" => $customerDetails['per_address_line1'],
                                        "ADD2" => $customerDetails['per_address_line2'],
                                        "CITY" => $getCityStateCode['citycode'],
                                        "STATE" => $getCityStateCode['statecode'],
                                        "PINCODE"=> $customerDetails['per_pincode'],
                                        "CNTRYCODE" => "IN",
                                        "PHONENO" => $customerDetails['mobile_number'],
                                        "MOBNO" => $customerDetails['mobile_number'],
                                        "EMAILID" => $customerDetails['email'],
                                        "INTCRFLG"=> "S",
                                        "INTDRFLG"=> "S",
                                        "AOFNUMBER"=> 'CA'.$accountDetails['aof_number'],
                                        'ACCTID'=>$currenteliteAccount,
                                        "MODEOFOPER"=> $modeOperationCode['code'],
                                        "SOURCECODE"=> $empHRMSnumber,
                                        "FREECODE3"=> "001",
                                        "freeText5"=> $annual_turnover,
                                        
                                    ),
                                    )
                                )
                             )
                        )
                );
                
                
                $data["request"]["FIXML"]["Body"]["CAAcctAddRequest"]['CAAcctAddRq']["nomineeInfoRec"] =  $nomineeDetails;
                if($accountDetails['scheme_code'] !=4){
                    if($is_huf){
                        $data["request"]["FIXML"]["Body"]["CAAcctAddRequest"]['CAAcctAddRq']["nomineeInfoRec"] =  $nomineeDetails;
                        $data["request"]["FIXML"]["Body"]["CAAcctAddRequest"]["relPartyRec"] =  $relationparty;
                        $data["request"]["FIXML"]["Body"]["CAAcctAdd_CustomData"]["JOINT"] =  $jointApplicantData;
                    }
                }

                // echo"<pre>";print_r($data);
            $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
            $payload = json_encode(['gatewayRequest'=>$data]);
         
             $guzzleClient = $client->request('POST',$url,
                                    ['headers' =>[
                                     'Content-Type'=>'application/json',
                                     'X-IBM-Client-secret'=>$client_key,
                                     'X-IBM-Client-Id'=>$client_id,
                                        'authorization'=>$authorization
                                    ],
                                    //  'auth' => [$authusername, 'dcbl@123'],
                                    'json'=>['gatewayRequest'=>$data],
                                    'exceptions'=>false
                                    ]);
            
                                    // echo "<pre>";print_r($guzzleClient);exit;
            $response = json_decode($guzzleClient->getBody(),true);
            //  echo "<pre>";print_r($response);exit;
            $responseTime = Carbon::now()->diffInSeconds($requestTime);
            
            $saveService = CommonFunctions::saveApiRequest('CURRENT_API',$url,$payload,json_encode($response),
                                                                                    json_encode($data),$response,$formId, $responseTime);
            
            if(isset($response['gatewayResponse'])){
                $response = $response['gatewayResponse'];
                if($response['status']['message'] == 'success'){
                    $encryptedResponse = json_encode($response['response']);
                    $LIVCurrentNameResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                    //CA224 api doest return valid account number elite 
                    if($customerDetails['elite_account_number'] != ''){
                        return ['status'=>'Success','data'=>$currenteliteAccount, 'message'=>''];   
                    }
                    // echo  "<pre>";print_r($LIVCurrentNameResponse);exit;
                    if(strtoupper($getschemeDesc->scheme_code) == 'CA224'){
                    if(isset($LIVCurrentNameResponse['data']['caAcctAddResponse']['CAAcctAdd_CustomData']['ACCTID']) && $LIVCurrentNameResponse['data']['caAcctAddResponse']['CAAcctAdd_CustomData']['ACCTID'] != ''){
                        $currentaccountId = $LIVCurrentNameResponse['data']['caAcctAddResponse']['CAAcctAdd_CustomData']['ACCTID'];
                        return ['status'=>'Success','data'=>$currentaccountId, 'message'=>''];             
                    }else{
                        return ['status'=>'Error','data'=>'','message'=>json_encode($response)];
                    }
                }else{
                        if(isset($LIVCurrentNameResponse['data']['caAcctAddResponse']['caAcctAddRs']['caAcctId']['acctId']) && $LIVCurrentNameResponse['data']['caAcctAddResponse']['caAcctAddRs']['caAcctId']['acctId']!=''){
                        $currentaccountId = $LIVCurrentNameResponse['data']['caAcctAddResponse']['caAcctAddRs']['caAcctId']['acctId'];
                        return ['status'=>'Success','data'=>$currentaccountId, 'message'=>''];             
                    }else{
                        return ['status'=>'Error','data'=>'','message'=>json_encode($response)];
                    }
                    }
                }else{
                    return ['status'=>'Error','data'=>'','message'=>json_encode($response)];

                }
            }else{
                    return ['status'=>'Error','data'=>'','message'=>json_encode($response)];
                }
                                            
        }

    public static function getCANomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdCode)
    {    
        $gaurdianCode = CommonFunctions::getGaurdianCode($gaurdCode);
        // echo "<pre>";print_r($gaurdianCode);exit;
        if ($nominee_is_minor == 'Y') { //nominee is minor
            $gurdianInfo = Array(
                             "guardianCode" => $gaurdianCode,
                             "guardianName" => $nomineeDetails['guardian_name'],
                             "guardianContactInfo" => Array(
                                "phoneNum" => Array(
                                   "telephoneNum" => "",
                                   "faxNum" => "",
                                   "telexNum" => "",
                                ),
                                "emailAddr" => "",
                                "postAddr" => Array(
                                   "addr1" => $nomineeDetails['guardian_address_line1'],
                                   "addr2" => $nomineeDetails['guardian_address_line2'],
                                   "addr3" => "",
                                   "city" => $guardian_pcsDetails['citycode'],
                                   "stateProv" => $guardian_pcsDetails['statecode'],
                                   "postalCode" => $nomineeDetails['guardian_pincode'],
                                   "country" => $guardian_pcsDetails['countrycode'],
                                   "addrType" => ""
                                )
                             )
                          );
        }else{
            $gurdianInfo = array();
        }
        $returnArray = Array(
                         Array(
                        "regNum" => "001",
                        "nomineeName" => $nomineeDetails['nominee_name'],
                        "relType" => $nomineeDetails['relatinship_applicant'],
                        "nomineeContactInfo"=> Array(
                            "phoneNum" => Array(
                            "telephoneNum" => "",
                          ),
                        "postAddr" => Array(
                            "addr1" => $nomineeDetails['nominee_address_line1'],
                            "addr2" => $nomineeDetails['nominee_address_line2'],
                            "addr3" => "",
                            "city" => $nominee_pcsDetails['citycode'],
                            "stateProv" => $nominee_pcsDetails['statecode'],
                            "postalCode" => $nomineeDetails['nominee_pincode'],
                            "country" => $nominee_pcsDetails['countrycode'],
                            "addrType" => "Mailing"
                           )
                        ),
                        "nomineeMinorFlg" => $nominee_is_minor,
                        "nomineeBirthDt" => Carbon::parse($nomineeDetails['nominee_dob'])->format('Y-m-d\TH:i:s.v'),
                        "nomineePercent" => Array(
                          "value" => 100
                        ),
                        "guardianInfo" => $gurdianInfo,
                      )

         );

        return  $returnArray;
    }


        public static function currentAccountNameApi($accountNumber,$accountname,$formId)
        {
            $client = new \GuzzleHttp\Client();
            $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_OTHER');  // to be change
            $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
            //$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given
            $current_timestamp = Carbon::now()->timestamp;
            $requestTime = Carbon::now();
            $requestUUID = CommonFunctions::getRandomValue(13);
            $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');

            $hash1 = hash('sha256', $timestamp);
            $hash2 = hash('md5', $timestamp);

            $uuid = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);



            $request = Array(
                        'header' => Array
                            (
                                "isEnc"=> "Y",
                                "apiVersion"=> "10.1",
                                "cifId"=> "103783107",
                                "languageId"=> "1",
                                "channelId"=> "CUBE",
                                "requestUUID"=> $uuid,
                                "serReqId"=> "ESB_NAMEUPDATE_SCRIPTS_FIS",
                                "timeStamp"=> $timestamp
                            ),
                            "request"=> Array(
                                "executeFinacleScriptRequest" => Array(
                                    "executeFinacleScriptInputVO"=> Array(
                                        "requestId"=> "NAMEUPDATE.scr"
                                    ),
                                    "executeFinacleScriptCustomData"=>  Array(
                                        "acctName"=> $accountname,
                                        "foracid"=> $accountNumber,
                                        "ReasonCode"=>"PAC"
                                    ),
                                ),
                            )
                        );

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
            $encryptedResponse = json_encode($response['gatewayResponse']['response']);
            $payload = json_encode(['gatewayRequest'=>$request]);
            $responseTime = Carbon::now()->diffInSeconds($requestTime);

            $saveService = CommonFunctions::saveApiRequest('CURRENT_NAME_API',$url,$payload,json_encode($response),
                                                                                    json_encode($request),$response,$formId, $responseTime);
            if(isset($response['gatewayResponse'])){
                $response = $response['gatewayResponse'];
                if($response['status']['message'] == 'success'){
                    $encryptedResponse = json_encode($response['response']);
                    $LIVCurrentNameResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                    $response['response'] = $LIVCurrentNameResponse;  
                 return ['status'=>'Success','data'=>json_encode($response), 'message'=>''];             
                }else{
                    return ['status'=>'Error','data'=>json_encode($response['response']), 'message'=>''];             
                }
            }          

        }

          public static function currentAccountAddressApi($formId,$currentaccountId)
        {
            $client = new \GuzzleHttp\Client();
            $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_OTHER');  // to be change
            $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
           // $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
            $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
            $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given

            $current_timestamp = Carbon::now()->timestamp;
            $requestTime = Carbon::now();
            $requestUUID = CommonFunctions::getRandomValue(13);
            $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');

            $hash1 = hash('sha256', $timestamp);
            $hash2 = hash('md5', $timestamp);

            $uuid = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);

            $entityDetails = DB::table('ENTITY_DETAILS')->leftjoin('FIN_PCS_DESC','FIN_PCS_DESC.PINCODE','ENTITY_DETAILS.ENTITY_PINCODE')->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ENTITY_DETAILS.FORM_ID')->where('ENTITY_DETAILS.FORM_ID',$formId)->get()->toArray();
            $entityDetails = (array) current($entityDetails);

            $request =  Array(
                        'header' => Array
                            (
                                "isEnc"=> "Y",
                                "apiVersion"=> "10.1",
                                "cifId"=> "103783107",
                                "languageId"=> "1",
                                "channelId"=> "CUBE",
                                "requestUUID"=> $uuid,
                                "serReqId"=> "ESB_ADDRESSUPDATE_SCRIPTS_FIS",
                                "timeStamp"=> $timestamp
                            ),
                            "request"=> Array(
                                "addressUpdateRequest"=> Array(
                                    "funcCode"=> "A",
                                    "customerId"=> $entityDetails['customer_id'],
                                    "accountNumber"=> $currentaccountId,
                                    "cAddress1"=> $entityDetails['entity_address_line1'],
                                    "cAddress2"=> $entityDetails['entity_address_line2'],
                                    "cityCode"=> $entityDetails['citycode'],
                                    "stateCode"=> $entityDetails['statecode'],
                                    "postalCode"=> $entityDetails['pincode'],
                                    "countryCode"=> $entityDetails['countrycode'],
                                    "phNum"=> "",
                                    "mobNum"=> $entityDetails['entity_mobile_number'],
                                    "emailId"=> $entityDetails['entity_email_id']
                                ),
                            )
                        );

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
            $payload = json_encode(['gatewayRequest'=>$request]);
            $responseTime = Carbon::now()->diffInSeconds($requestTime);
            $saveService = CommonFunctions::saveApiRequest('CURRENT_ADDRESS_API',$url,$payload,json_encode($response),
                                                                                    json_encode($request),$response,$formId, $responseTime);
            if(isset($response['gatewayResponse'])){
                $response = $response['gatewayResponse'];
                if($response['status']['message'] == 'success'){
                    $encryptedResponse = json_encode($response['response']);
                    $LIVCurrentNameResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                    $response['response'] = $LIVCurrentNameResponse;  
                    return ['status'=>'Success','data'=>$response, 'message'=>''];             
                }else{
                    return ['status'=>'Error','data'=>$response['response'], 'message'=>''];             
                }
            }          
        }

    public static function dbsaSweepAccount($formId,$currentAccountId,$savingAccountId){
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given
        $current_timestamp = Carbon::now()->timestamp;
        $RequestUUID = CommonFunctions::getRandomValue(15);
        $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_OTHER');

        $data = Array(
            "header"=> Array(
                "isEnc"=> "Y",
                "apiVersion"=> "1.0",
                "cifId"=> "100212262",
                "languageId"=> "1",
                "channelId"=> "CUBE",
                "requestUUID"=> $RequestUUID,
                "serReqId"=> "ESB_HSWEEP_SCRIPTS_FIS",
                "timeStamp"=> $current_timestamp
            ),
            "request"=> Array(
                "hsweepRequest"=> Array(
                    "forACID"=> $currentAccountId,
                    "sbaForACID"=> $savingAccountId
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
        $saveService = CommonFunctions::saveApiRequest('CA_SA_SWEEP',$url,$payload,json_encode($response),json_encode($data),json_encode($response),$formId, $responseTime);


        if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'success'){
            return 'Success';
        }else{
            return ['status'=>'error','message'=>'Sweep Updation Failed','data'=>$response];
        }
    }

    public static function dbsa_gstValidation($formId,$entityAcctNo,$gstNumber){
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        //$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given
        
        $current_timestamp = Carbon::now()->timestamp;
        $RequestUUID = CommonFunctions::getRandomValue(15);
        $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_OTHER');
        
        $stateValue = DB::table('GST_STATE_LIST')->where('STATE_CODE',substr($gstNumber,0,2))->where('IS_ACTIVE',1)->value('STATE_NAME');
        if($stateValue == ''){
            return ['status'=>'error','message'=>'No Data Found in Gst State List','data'=>null];
        }

        $data = Array(
                "header"=> Array(
                    "isEnc"=> "Y",
                    "apiVersion"=> "1.0",
                    "cifId"=> "100212262",
                    "languageId"=> "1",
                    "channelId"=> "CUBE",
                    "requestUUID"=> $RequestUUID,
                    "serReqId"=> "ESB_CGSTN_SCRIPTS_FIS",
                    "timeStamp"=> $current_timestamp
                ),
                "request"=> Array(
                    "cgstnRequest"=> Array(
                        "forACID"=> $entityAcctNo,
                        "stateName"=> $stateValue,
                        "state"=>substr($gstNumber,0,2),
                        "gstRegNo"=> $gstNumber
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

            $saveService = CommonFunctions::saveApiRequest('CA_GST_VALIDATION',$url,$payload,json_encode($response),json_encode($data),json_encode($response),$formId, $responseTime);

            if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'success'){
                return 'Current Gst Updated Successfully';
            }else{
                return ['status'=>'error','message'=>'Current Gst Failed','data'=>$response];
            }

    }    

    public static function preSweepApi($formId,$customerId){
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_2022'); // to be updated in config once prod detials given
        
        $current_timestamp = Carbon::now()->timestamp;
        $RequestUUID = CommonFunctions::getRandomValue(17);
        $url = config('constants.APPLICATION_SETTINGS.CA_ACCOUNT_OTHER');

        $data = Array(
            "header"=> Array(
                "isEnc"=> "N",
                "apiVersion"=> "1.0",
                "cifId"=> "100212262",
                "languageId"=> "1",
                "channelId"=> "CUBE",
                "requestUUID"=> $RequestUUID,
                "serReqId"=> "ESB_HCCFMMODIFY_SCRIPTS_FIS",
                "timeStamp"=> $current_timestamp
            ),
            "request"=> Array(
                "hccfmModifyRequest"=> Array(
                    "custId"=> $customerId,
                    "sweepFlg"=> "Y"
                )
            )
        );


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

            $saveService = CommonFunctions::saveApiRequest('CA_PRE_SWEEP',$url,$payload,json_encode($response),json_encode($data),json_encode($response),$formId, $responseTime);

            if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'success'){
                return 'Success';
            }else{
                return ['status'=>'error','message'=>'Current Account Pre Sweep Api Failed','data'=>$response];
            }
            


        
    }
}
