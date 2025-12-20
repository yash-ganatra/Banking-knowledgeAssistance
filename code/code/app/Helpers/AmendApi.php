<?php
namespace App\Helpers;

use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;
use App\Helpers\AmendCommonFunctions;
use App\Helpers\ApiCommonFunctions;
use App\Helpers\AmendRules;
use Carbon\Carbon;
use Crypt,Cache,Session;
use DB;
use File;
use App\Helpers\Api;

class AmendApi {

   public static function retcustmod(){
		// For Debug
	   $formId = 12486;
	   $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('FORM_ID',$formId)
                                            ->get()->toArray();
		//	echo '<pre>'; print_r($customerDetails); 
											
		foreach($customerDetails as $customerData){
             $customerData = (array) $customerData;
			 $amendCustDetails = Self::AmendCustApi($customerData,$formId);			 
		}		
   }

   public static function AmendCustApi($customerData, $formId){

   	try{
        
        $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
        $accountDetails = (array)  current($accountDetails);

        $delightKitDetails = DB::table('DELIGHT_KIT')->where('ID',$accountDetails['delight_kit_id'])
                                                     ->get()->toArray();
        $delightKitDetails = (array)  current($delightKitDetails); 		//$delightKitDetails['customer_id'] = '103015725';
		
		$existingIDs = Self::retcustinq($delightKitDetails['customer_id'], $formId);
		//echo '<pre>'; print_r($existingIDs);  exit;

        $timestamp = Carbon::now()->timestamp;
        $current_timestamp = Carbon::parse($timestamp)->format('Y-m-d\TH:i:s.v');

        $curr_pcsDetails = DB::table('FIN_PCS_DESC')->where('pincode',$customerData['current_pincode'])->get()->toArray();
        $curr_pcsDetails = (array) current($curr_pcsDetails);
		
		$per_pcsDetails = DB::table('FIN_PCS_DESC')->where('pincode',$customerData['per_pincode'])->get()->toArray();
        $per_pcsDetails = (array) current($per_pcsDetails);

        $countries = DB::table('COUNTRIES')->where('ID',$customerData['per_country'])->get()->toArray();
        $countries = (array) current($countries);
		
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

		// echo "<pre>";print_r($riskDetails);exit;
        $occupation = DB::table('OCCUPATION')->where('ID',$riskDetails->occupation)->get()->toArray();
        $occupation = (array) current($occupation);
	
		$demographicMiscModData = Array (				
					"strText2" => $occupation['code'],
					"Type" => "CURRENT_EMPLOYMENT",					
				);
		if($existingIDs['OCCUPATION'] != ''){
			$demographicMiscModData["MiscellaneousID"] = $existingIDs['OCCUPATION'];
		}

        $isStaff = $customerData['customer_account_type'] == 3 ? 'Y' : 'N';
		$staffEmpNo = $isStaff == 'Y' ? $customerData['empno'] : '';

        $title = DB::table('TITLE')->whereId($customerData['title'])->get()->toArray();
        $title = (array) current($title);

        $maritalStatus = DB::table('MARITAL_STATUS')->whereId($customerData['marital_status'])->get()->toArray();
        $maritalStatus = (array) current($maritalStatus);

        $empStatus = Rules::getEmploymentStatus($riskDetails->occupation);

		$pfDocCodeValue = '';
		$aadharLinkStatus = DB::table('PAN_DETAILS')->select('AADHAARSEEDINGSTATUS')
													->where('PANNO',$customerData['pancard_no'])
													->orderBy('id','DESC')
													->get()->toArray();
		if(count($aadharLinkStatus)>0){											
			$aadharLinkStatus = (array) current($aadharLinkStatus);
			
			if(isset($aadharLinkStatus['aadhaarseedingstatus']) && ($aadharLinkStatus['aadhaarseedingstatus'] == "NA" || $aadharLinkStatus['aadhaarseedingstatus'] == "Y")){
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

        $proofOfIdentity = DB::table('OVD_TYPES')
                                    ->where('ID',$customerData['proof_of_identity'])
                                    ->get()->toArray();
        $proofOfIdentity = (array) current($proofOfIdentity); 

        $fatcaIdType =  $proofOfIdentity['id_type'];

        $FatcIdNum = $customerData['id_proof_card_number'];

        // If Identity is Aadhar share the valut no.        
        if($customerData['proof_of_identity'] == 1){ //
            $FatcIdNum = $customerData['id_proof_aadhaar_ref_number'];
        }

        $fatcaNationality = DB::table('COUNTRIES')
                                    ->where('ID',$riskDetails->citizenship)
                                    ->get()->toArray();
        $fatcaNationality = (array) current($fatcaNationality);

        $fatcaResidence = DB::table('COUNTRIES')
                                    ->where('ID',$riskDetails->residence)
                                    ->get()->toArray();
        $fatcaResidence = (array) current($fatcaResidence);

        if($accountDetails['delight_scheme'] == 5){
			$custTypeCode = '095'; 
		}else{
			$custTypeCode = '001';
		}
						
		$panForm60docArray = [
			"RetIdentificationType" => $pfIdType,
			"CountryOfIssue" => "IN",
			"DocCode" => $pfDocCode,
			"IssueDt" => $current_timestamp,
			"TypeCode" => $typeCode,
			"PlaceOfIssue" => "I005",
			"ReferenceNum" => $pfRefNum,
			"IDIssuedOrganisation" => "Ministry of External Affairs",
			"preferredUniqueId" => "Y",
			//"preferredUniqueId" => ($accountDetails['delight_scheme'] == 5) ? 'N' : 'Y',
		];
		
		if($existingIDs['RPAN'] != ''){
			$panForm60docArray["EntityDocumentID"] = $existingIDs['RPAN'];
		}
		// INPAN 23_03_2023

		if($existingIDs['INPAN'] != ''){
			$panForm60docArray["EntityDocumentID"] = $existingIDs['INPAN'];
		}
		
		$commEmailArray = [	"Email" => $customerData['email']!=null ? $customerData['email'] : '',
							"PhoneOrEmail" => "EMAIL",
							"PrefFlag" => "Y",
							"PhoneEmailtType" => "COMMEML",					
						];
						
		if($existingIDs['COMMEML'] != ''){
			$commEmailArray["PhoneEmailID"] = $existingIDs['COMMEML'];
		}	
		
		$delPassportEntry = [
			"RetIdentificationType" => "Passport Number",
			"CountryOfIssue" => "IN",
			"DocCode" => "PASPR",
			"deleteFlag" => "Y", 			
			];

		if($existingIDs['PASPR'] != ''){
			$delPassportEntry["EntityDocumentID"] = $existingIDs['PASPR'];
		}				
		// echo "<pre>";print_r($customerData);exit;
		// If Passport or DL -- with expDt
		if($customerData['proof_of_identity'] == 2 || $customerData['proof_of_identity'] == 3){
			// echo "<pre>"
			$idArray = Self::genEntityDocArray($customerData['proof_of_identity'], $customerData['id_proof_card_number'], $customerData['passport_driving_expire'],$customerData['id_psprt_dri_issue']);
		}else{
			if($customerData['proof_of_identity'] == 1){ // Aadhar
				$idArray = Self::genEntityDocArray($customerData['proof_of_identity'], $customerData['id_proof_aadhaar_ref_number']);
			}else{
				$idArray = Self::genEntityDocArray($customerData['proof_of_identity'], $customerData['id_proof_card_number']);
			}	
		}
		
		$idArray["preferredUniqueId"] = 'N';
		
		if($existingIDs['ADHAR'] != ''){
			$idArray["EntityDocumentID"] = $existingIDs['ADHAR'];
		}	

		if($customerData['proof_of_identity'] == 2 && $existingIDs['PASPR'] != ''){ 
			$idArray["EntityDocumentID"] = $existingIDs['PASPR'];
		}	
																
		if($customerData['proof_of_identity'] == 9){
			$idArray['preferredUniqueId'] = 'N';
			$getAadhaarData = CommonFunctions::getaadhardocumentForKyc($customerData,$current_timestamp);
			if($existingIDs['ADHAR'] != ''){
				$getAadhaarData["EntityDocumentID"] = $existingIDs['ADHAR'];
			}	

			if($existingIDs['EKYC'] != ''){
				$idArray["EntityDocumentID"] = $existingIDs['EKYC'];
			}	
			$getAadhaarData['IDIssuedOrganisation'] = "Ministry of External Affairs";
			$getAadhaarData['preferredUniqueId'] = 'N';
			$entityArrayForAPI = Array($panForm60docArray,$idArray,$getAadhaarData);
		}else{
		$entityArrayForAPI = Array($panForm60docArray, $idArray);
		}
		// echo "<pre>";print_r($entityArrayForAPI);exit;
		
		// If ID proof is not Passport, delete dummyPassport data from custID 
		// And also Passport ID should exist!
		if($customerData['proof_of_identity'] != 2 && $existingIDs['PASPR'] != ''){ 
			array_push($entityArrayForAPI, $delPassportEntry); 
		}  
		
		//echo '<pre>'; print_r($entityArrayForAPI); exit;
		
		$userDetails = CommonFunctions::getUserDetails($accountDetails['created_by']);
        if (isset($userDetails['rm_code']) && $userDetails['rm_code'] != '') {
            $rm_code = $userDetails['rm_code'];
        }else{
            $rm_code = 'EOD0009';
        }

        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $sourcecode = $userDetails['hrmsno'];
        }else{
            $sourcecode = '';
        }

	    $years = \Carbon\Carbon::parse($customerData['dob'])->age;

        if($years < 18){           
           $is_minor = 'Y';
           $custom_accountType = '03';
        }else{
            $is_minor = 'N';
            $custom_accountType = '01';
        }

		
        $grossIncomeValue = DB::table('GROSS_INCOME')->select('VALUE_TO_BE_PASSED')->whereId($riskDetails->gross_income)->get()->toArray();
        $grossValue = '';
        if(count($grossIncomeValue) > 0){
            $grossIncomeValue = (array) current($grossIncomeValue);
            $grossValue = $grossIncomeValue['value_to_be_passed'];
        }

        $motherFML = CommonFunctions::getFML_Name($customerData['mother_full_name']);
		
		$tdsSlab = CommonFunctions::getTDSSlab($customerData,$pfDocCodeValue);

		$prefName = substr($customerData['first_name'].' '.$customerData['last_name'],0,50);
			
        $RequestUUID = 'C'.CommonFunctions::getRandomValue(14);

        $header_timestamp = Carbon::now()->subDays(50)->format('dmY');

   		$url = config('constants.APPLICATION_SETTINGS.AMEND_CUSTID');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');		

        $data = Array(

        	    "header" => Array(
        	    				"isEnc" => "N",
								"apiVersion" => "10.1",
								"cifId" => "100212262",
								"languageId" => "1",
                                "channelId" => "CUBE",
								"requestUUID" => $RequestUUID,
								"sVersion" => "20",
								"serReqId" => "RetCustMod",
                                "timeStamp" => $timestamp
        	                ),
        	    "request" => Array(
        	    		  "FIXML" => Array(
        	    				  "Header" => Array(
        	    				  		   "RequestHeader" => Array(
        	    				  		   				   "MessageKey" => Array(

        	    				  		   				   			"RequestUUID" => $RequestUUID,
																	"ServiceRequestId" => "RetCustMod",
																	"ServiceRequestVersion" => "10.2",
																	"ChannelId" => "COR",
																	"LanguageId" => ""

															),//End of MessageKey
        	    				  		   				   "RequestMessageInfo" => Array(
        	    				  		   				   						"BankId" => "01",
																				"TimeZone" => "",
																				"EntityId" => "",
																				"EntityType" => "",
																				"ArmCorrelationId" => "",
																				"MessageDateTime" => $current_timestamp,
        	    				  		   				   ),//End of RequestMessageInfo
        	    				  		   				   "Security" => Array(
        	    				  		   				   		"Token" => Array(
        	    				  		   				   			"PasswordToken" => Array(
        	    				  		   				   						        "UserId" => "",
                           																"Password" => ""
        	    				  		   				   			)//End of PasswordToken

        	    				  		   				   		),//End of Token
        	    				  		   				   		"FICertToken" => "",
																"RealUserLoginSessionId" => "",
																"RealUser" => "",
																"RealUserPwd" => "",
																"SSOTransferToken" => ""

        	    				  		   				   )//End of Security

        	    				  		   )//End of RequestHeader

        	    				    ),//End of Header

        	    				    "Body" => Array(
        	    				    	"RetCustModRequest" => Array(
        	    				    		"RetCustModRq" => Array(
        	    				    			"RetCustModMainData" => Array(
        	    				    				"CustModData" => Array(
        	    				    								"CustId" => $delightKitDetails['customer_id'],
																	"IsSuspended" =>  "N",
																	"Gender" => $customerData['gender'],
																	"Name" => $customerData['first_name'].' '.$customerData['middle_name'].' '.$customerData['last_name'],
																	"MaidenNameOfMother" => $customerData['mothers_maiden_name'],
																	"TaxDeductionTable" => $tdsSlab,
																	"RelationshipOpeningDt" => $current_timestamp,
																	"RetAddrModDtls" => Array([							
																					  "AddrLine1" => $customerData['per_address_line1'],
														                              "AddrLine2" => $customerData['per_address_line2'],
																					  "AddrLine3" => $customerData['per_landmark'],
																					  "PagerNum" => $customerData['mobile_number'],
														                              "CellNum" => $customerData['mobile_number'],
														                              "City" => $per_pcsDetails['citycode'],
														                              "Country" => $countries['country_code'],
														                              "State" => $per_pcsDetails['statecode'],
														                              "PostalCode" => $per_pcsDetails['pincode'],
																					  "AddrCategory" => "Home",
																					  //"StartDt" => "2021-12-01T00:00:00.000",
														                              "addressID" => $existingIDs['HOME']
																	],[							
																					  "AddrLine1" => $customerData['current_address_line1'],
														                              "AddrLine2" => $customerData['current_address_line2'],
														                              "AddrLine3" => $customerData['current_landmark'],
																					  "PagerNum" => $customerData['mobile_number'],
														                              "CellNum" => $customerData['mobile_number'],
														                              "City" => $curr_pcsDetails['citycode'],
														                              "Country" => $countries['country_code'],
														                              "State" => $curr_pcsDetails['statecode'],
														                              "PostalCode" => $curr_pcsDetails['pincode'],
																					  "AddrCategory" => "Mailing",
																					  //"StartDt" => "2021-12-01T00:00:00.000",
														                              "addressID" => $existingIDs['MAILING']
																	]),//End of RetAddrModDtls
																   "DateOfBirth" => Carbon::parse($customerData['dob'])->format('Y-m-d\TH:i:s.v'),
										                           "CustStatusChangeDt" => $current_timestamp,
										                           "Manager" => $rm_code,
										                           "Occupation" => $occupation['code'],
										                           "PAN" => $customerData['pancard_no'],
										                           "ShortName" => substr($customerData['short_name'],0,10),
										                           "FirstName" => $customerData['first_name'] == null ? "null": $customerData['first_name'],
										                           "LastName" => $customerData['last_name'] == null ? "null": $customerData['last_name'],
                                        						"MiddleName" => $customerData['middle_name'] == null ? "null": $customerData['middle_name'],
										                        //    "PrefName" => $customerData['first_name'].' '.$customerData['last_name'],
																"PrefName" => $prefName,
										                           "StaffFlag" => $isStaff,
																   "StaffEmployeeId" => $staffEmpNo,
										                           "CountryOfBirth" => $countries['country_code'],
										                           "PlaceOfBirth" => $riskDetails->place_of_birth,
										                           "FatcaRemarks" => "2",
										                           "Salutation" => $title['title'],
										                           "CustStatus" => "999",
										                           "AdhaarNumber" => $customerData['id_proof_aadhaar_ref_number'],
										                           "PhoneEmailModData" => Array(
																		$commEmailArray
																		,[
																			"PhoneNum" =>  $customerData['mobile_number'],
																			"PhoneNumCityCode" => "",
																			"PhoneNumCountryCode" => "91",
																			"PhoneNumLocalCode" => $customerData['mobile_number'],
																			"PhoneOrEmail" => "PHONE",
																			"PhoneEmailtType" => "CELLPH",
																			"PhoneEmailID" => $existingIDs['CELLPH']

										                           		]  
										                           ),//End of PhoneEmailModData

        	    				    				)//End of CustModData

        	    				    			),//End of RetCustModMainData
        	    				    			"RetailCustModRelatedData" => Array(
        	    				    				"EntityDocModData" => $entityArrayForAPI,
													"DemographicModData" => Array (
																/*"DemographicID": 2870207,*/
																"MaritalStatus" => $maritalStatus['code'],
																"EmploymentStatus" => $empStatus,
																"DemographicMiscModData" => $demographicMiscModData,
																"annualSalaryIncome" => $grossValue
															),
													
        	    				    				"coreInterfaceInfo" => Array(
        	    				    									        "FreeText1" => $sourcecode,
        	    				    									        "FreeText2" => substr($customerData['mothers_maiden_name'], 0,30),
																				"FreeText6" => $freeText6,
																				"FreeText10" => $riskText,
																				"FREECODE3" => "000",
																				"FREECODE4" => "01",
																				"FREECODE5" => "02",
																				"FREECODE7" => "01"
        	    				    				)


        	    				    			)//End of RetailCustModRelatedData

        	    				    		),//End of RetCustModRq
                                            "RetCustMod_CustomData" => Array(

													"AccountType" => $custom_accountType,
													"FatherOrSpouse" => $customerData['father_spouse'],
													"Father_Name" => ($customerData['father_spouse'] == '01' ? $customerData['father_name'] : ''),		 
													"Spouse_Name" => ($customerData['father_spouse'] == '02' ? $customerData['father_name'] : ''),	
													"MotherFirstName" => ($motherFML['first_name'] == null ? "null": $motherFML['first_name']),
				                                	"MotherMiddleName" => ($motherFML['middle_name'] == null ? "null": $motherFML['middle_name']),
				                                	"MotherLastName" => ($motherFML['last_name'] == null ? "null": $motherFML['last_name']),	 
													"TinIssueCountry" => "IN",
													"FatcaIdType" => $fatcaIdType,
													"FatcIdNum" => $FatcIdNum,
													"FatcaNationality" => $fatcaNationality['country_code'],
													"FatcaResidenceCountry" => $fatcaResidence['country_code'],
													"AgriculturalIncome" => "0",
													"NonAgriculturalIncome" => "0",
													"consCode" => "001",
													"custConsCode" => "001",
													// "grossIncome" => $grossValue,
													"CustType" => $custTypeCode,
													"CustTypeCode" => $custTypeCode

                                                // "TinIssueCountry" => "IN",
                                                // "FatcaIdType" => "D",
                                                // "FatcIdNum" => "3ST4H36A8T9YQOAW",
                                                // "FatcaNationality" => "IN",
                                                // "FatcaResidenceCountry" => "IN",
                                                // "AgriculturalIncome" => "0",
                                                // "NonAgriculturalIncome" => "0",
                                                // "consCode" => "001",
                                                // "custConsCode" => "001",
                                                // "grossIncome" => "150000",
                                                // "CustType" => "095",
                                                // "CustTypeCode" => "095"

                                            )//End of RetCustMod_CustomData

        	    				    	)//End of RetCustModRequest

        	    				    )//End of Body


        	    		    )//End of FIXML
				)//End of request
        );

		//echo '<pre>'; print_r($data); 

		$client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']); //, 'debug' => true]);
        $requestTime = Carbon::now();
       //echo "<pre>";print_r(json_encode(['gatewayRequest'=>$data]));exit;
        //$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);
		
		//echo '<pre>R'; print_r($payload); exit;		

        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                //'json'=>$payload,
												'exceptions'=>false
                                            ]);
        
		//$res->getBody()->getContents();
		
		//echo '<pre>Req'; print_r($data); print_r($guzzleClient->getBody()); exit;

		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);
	
		
        $saveService = CommonFunctions::saveApiRequest('CUSTOMER_ID_AMENDMENT',$url,$payload,json_encode($response),json_encode($data),$response,$customerData['form_id'], $responseTime);


        // If all ok. Update and return
		if(isset($response['gatewayResponse']['status']['isSuccess']) && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ER000')
        {
            $encryptedResponse = json_encode($response['gatewayResponse']['response']['data']);
            //$customerIdResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
															   
            $amendmentResponse = json_decode($encryptedResponse);						
		
            /* $msg = $amendmentResponse->RetCustModRs->Desc;
            $amendmentResponse = $amendmentResponse->RetCustModRs->CustId;
			*/

            //if($amendmentResponse){            
               //$updateCustomerId = DB::table('CUSTOMER_OVD_DETAILS')->whereId($customerData['id'])->update(['customer_id'=>$amendmentResponse]);

			   
			   $updateCustomerId = DB::table('CUSTOMER_OVD_DETAILS')->whereId($customerData['id'])->update(['customer_id'=>$delightKitDetails['customer_id']]);

			   Rules::postCustomerIdApiQueue($formId,$delightKitDetails['customer_id']);
			   
			   DB::commit();
			   return ['status'=>'Success','data'=>$delightKitDetails['customer_id']];
            //    return array('success', $delightKitDetails['customer_id']);
            //}
					
        }else{   

			$amendmentResponse = 'Error!';
			if(isset($response['gatewayResponse']['response']['data']['Error']['FIBusinessException']['ErrorDetail']['ErrorDesc']))
			{
				$amendmentResponse = $response['gatewayResponse']['response']['data']['Error']['FIBusinessException']['ErrorDetail']['ErrorDesc'];
            }
			return ['status'=>'Error','data'=>'API', 'message'=>json_encode($amendmentResponse)];
            // return  array('fail', $amendmentResponse);
        }
       

   	}catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','AmendCustApi',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
   }


   public static function AmendAccountApi($formId, $customerID){

        try{

        $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                                                      ->get()->toArray();
        $accountDetails = (array) current($accountDetails);

        $primaryDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->where('APPLICANT_SEQUENCE',1)
                                                      ->get()->toArray();
		$primaryDetails = (array) current($primaryDetails);

		$riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)->where('APPLICANT_SEQUENCE',1)->get()->toArray();
		$riskDetails = current($riskDetails);
		
		$acctName = substr($primaryDetails['first_name'].' '.$primaryDetails['middle_name'].' '.$primaryDetails['last_name'],0,80);		
		
		$mop = DB::table('MODE_OF_OPERATIONS')->select('CODE')->where('ID',$accountDetails['mode_of_operation'])		
                                                      ->get()->toArray();				
        $mop = current($mop);																	
		
		$aof = $accountDetails['aof_number'];
		
		$userDetails = CommonFunctions::getUserDetails($accountDetails['created_by']);
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $sourcecode = $userDetails['hrmsno'];
        }else{
            $sourcecode = '';
        }

        $delight_kit_details = DB::table('DELIGHT_KIT')->where('ID',$accountDetails['delight_kit_id'])->get()->toArray();

        $delight_kit_details = (array) current($delight_kit_details);

        if (!isset($delight_kit_details['account_number'])) {
			return ['status'=>'fail','msg'=> 'Data error! Kit details not found','data'=>[]];
        }


        $nomineeDetails = DB::table('NOMINEE_DETAILS')->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT')
                                // ->select('NOMINEE_DETAILS.*','RELATIONSHIP.CODE as RELATINSHIP_APPLICANT',
                                //     'RELATIONSHIP.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                              //  ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        $nomineeDetails = (array) current($nomineeDetails);

		$gaurdianDetails = DB::table('NOMINEE_DETAILS')->select('REL.CODE as RELATINSHIP_APPLICANT_GUARDIAN')
												->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
												->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
		$gaurdianDetails = (array) current($gaurdianDetails);
        
        $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
        $accountDetails = (array) current($accountDetails);
        
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

        // echo "<pre>";print_r($nomineeDetails);exit;
        if(isset($nominee_pcsDetails) && count($nominee_pcsDetails) < 1){
           $nominee_pcsDetails['citycode'] = '';
           $nominee_pcsDetails['statecode'] = '';
        }

        $current_timestamp = Carbon::now()->timestamp;
        $gaurdianCode = $gaurdianDetails['relatinship_applicant_guardian'];
		
       if($nomineeFlag == 'Y'){
            $getnomineeDetails = Self::getNomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdianCode);
       }else{          
          $getnomineeDetails = Array();
       }
  
            $url = config('constants.APPLICATION_SETTINGS.AMEND_ACCOUNT_ID');
            $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
			$encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');
			$current_timestamp = Carbon::now()->timestamp;
			$timestamp_string = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
			$account_open_date = Carbon::parse($current_timestamp)->format('d-m-Y');

			$annual_turnover = config('constants.ANNUAL_TURNOVER')[$riskDetails->annual_turnover];
			$annual_turnover = str_replace(' Lakh', '00000', $annual_turnover);
		    $annual_turnover = str_replace(' Crore', '0000000', $annual_turnover);  
		    $annual_turnover = str_replace('Upto ', '', $annual_turnover);
	
            // $data = Array(
            //          "header" => Array(
            //                         "apiVersion" =>  "1.0.0.0",
            //                         "appVersion" =>  "1.0.0.0",
            //                         "channelId" =>  "CUBE",
            //                         "cifId" =>  "102873457",
            //                         "deviceId" =>  "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
            //                         "modelName" =>  "Motorola XT1022",
            //                         "languageId" =>  "1",
            //                         "os" =>  "Android",
            //                         "osVersion" =>  "5.1",
            //                         "requestUUID" => substr("CUBEDELAA".$accountDetails['aof_number'].uniqid(),0,35),
            //                         "serReqId" =>  "SBACCTMOD_V1",
            //                         "sVersion" =>  "13",
            //                         "timeStamp" =>  $current_timestamp,
            //                         "isEnc" =>  "N"
            //          ),//Header End
            //          "request" => Array(
            //             "sbAcctModRequest" => Array(
            //                 "sbAcctModRq" => Array(
            //                     "sbAcctId" => Array(
            //                         "acctId" => $delight_kit_details['account_number'],
            //                         //"acctId" => "01812500013767",
            //                         /*"acctType" => Array(
            //                                      "schmCode" => "",
            //                                      "schmType" => ""
            //                         ),//accType
            //                         "acctCurr" => "",*/
            //                         "bankInfo" => Array(
            //                                         "bankId" =>  "",
            //                                         "name" =>  "",
            //                                         "branchId" =>  "",
            //                                         "branchName" =>  "",
            //                                         "postAddr" => Array(
            //                                             "addr1" => "",
            //                                             "addr2" => "",
            //                                             "addr3" => "",
            //                                             "city" => "",
            //                                             "stateProv" => "",
            //                                             "postalCode" =>"",
            //                                             "country" => "",
            //                                             "addrType" => ""
            //                                         ) //postAddr
            //                         )//bankInfo

            //                     ),//sbAcctId End

            //                     "acctStmtMode" => "N",  		// Statement
            //                     "acctStmtFreq" => Array(
            //                                         // "cal" => "",
            //                                         "type" => "M",			// Monthly
            //                                         "startDt" => "",
            //                                         "weekDay" => "1",
            //                                         "weekNum" => "1",
            //                                         "holStat" => "N"
            //                     ),//acctStmtFreq End
            //                     "AcctStmtNxtPrintDt" => $timestamp_string,
            //                     "despatchMode" => "N",     		// No-Dispatch
			// 					"nomineeInfoRec" => [
            //                            $getnomineeDetails

            //                     ],//nomineeInfoRec  End
			// 				),

            //                     "drIntMethodInd" => "",
            //                     "eotEnabled" => "",
            //                     "acctExemptFlg" => "",
            //                     "acctLabelLlmodInput" => Array(
            //                         "acctLabelValue" => "RM030",
            //                         "delFlg" => "N",
            //                         "acctLabel" => "",
            //                         "acctLabelDesc" => "",
            //                         "key" => Array(
            //                                     "serialNum" => "01"
            //                         ) //key end
            //                     ), //acctLabelLlmodInput End
			// 					"sbAcctModCustomData" => Array (					
			// 								"acctName"  => $acctName,
			// 								"aofNumber" => $aof,
			// 								"modeOfOper"=> $mop->code,
			// 								"sourceCode"=> $sourcecode,
			// 								"freeText5" => $annual_turnover,
			// 								"ACCTOPNDATE" => $account_open_date,
			// 								"ASTATUS" => 'A'
			// 					),


            //                 ),//sbAcctModRq  End

            //             )//sbAcctModRequest End

            //          );//request End 
            
            $data = Array(
                     "header" => Array(
                                    "apiVersion" =>  "1.0.0.0",
                                    "appVersion" =>  "1.0.0.0",
                                    "channelId" =>  "CUBE",
                                    "cifId" =>  "102873457",
                                    "deviceId" =>  "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
                                    "modelName" =>  "Motorola XT1022",
                                    "languageId" =>  "1",
                                    "os" =>  "Android",
                                    "osVersion" =>  "5.1",
                                    "requestUUID" => substr("CUBEDELAA".$accountDetails['aof_number'].uniqid(),0,35),
                                    "serReqId" =>  "SBACCTMOD_V1",
                                    "sVersion" =>  "13",
                                    "timeStamp" =>  $current_timestamp,
                                    "isEnc" =>  "N"
                     ),//Header End
                     "request" => Array(
                        "sbAcctModRequest" => Array(
                            "sbAcctModRq" => Array(
                                "sbAcctId" => Array(
                                    "acctId" => $delight_kit_details['account_number'],
                                    "acctType" => Array(
                                                 "schmCode" => "",
                                                 "schmType" => ""
                                    ),//accType
                                    "acctCurr" => "",
                                    "bankInfo" => Array(
                                                    "bankId" =>  "",
                                                    "name" =>  "",
                                                    "branchId" =>  "",
                                                    "branchName" =>  "",
                                                    "postAddr" => Array(
                                                        "addr1" => "",
                                                        "addr2" => "",
                                                        "addr3" => "",
                                                        "city" => "",
                                                        "stateProv" => "",
                                                        "postalCode" =>"",
                                                        "country" => "",
                                                        "addrType" => ""
                                                    ) //postAddr
                                    )//bankInfo

                                ),//sbAcctId End

                                "acctStmtMode" => "N",  		// Statement
                                "acctStmtFreq" => Array(
                                                    // "cal" => "",
                                                    "type" => "M",			// Monthly
                                                    "startDt" => "",
                                                    "weekDay" => "1",
                                                    "weekNum" => "1",
                                                    "holStat" => "N"
                                ),//acctStmtFreq End
                                "AcctStmtNxtPrintDt" => $timestamp_string,
                                "despatchMode" => "N",     		// No-Dispatch
								"nomineeInfoRec" => [
                                       $getnomineeDetails

                                ],//nomineeInfoRec  End
							),

                                								


                            ),//sbAcctModRq  End

                        )//sbAcctModRequest End

                     );//request End 
            
		$customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
								->orderBy('APPLICANT_SEQUENCE','ASC')
								->get()->toArray();

		$drIntMethodInd = Array(
								"drIntMethodInd" => "",
                                "eotEnabled" => "",
                                "acctExemptFlg" => "",
                                "acctLabelLlmodInput" => Array(
                                	Array(
                                    "acctLabelValue" => "",
                                    "delFlg" => "N",
                                    "acctLabel" => $primaryDetails['label_code'],
                                    "acctLabelDesc" => "",
                                    "key" => Array(
                                        "serialNum" => "01"
                                    ) //key end
                                )
                                )
                             ); 

        $sbAcctId = Array(					
							"acctName"  => $acctName,
							"aofNumber" => $aof,
							"modeOfOper"=> $mop->code,
							"sourceCode"=> $sourcecode,
							"freeText5" => $annual_turnover,
							"ACCTOPNDATE" => $account_open_date,
							"ASTATUS" => 'A'
						);

		if(count($customerOvdDetails) >= 2){
                $relationArray = array();
                $joinArray = array();
            $relationParty = Array(
                        "relPartyType"=> "J",
                        "relPartyTypeDesc"=> "",
                        "relPartyCode"=> "",
                        "relPartyCodeDesc"=> "",
                        "recDelFlg"=> "N",
                        "custId"=> Array(
                            "custId"=> $customerOvdDetails[1]->customer_id,
                            "personName"=> Array(
                                "lastName"=> "",
                                "firstName"=> "",
                                "middleName"=> "",
                                "name"=> "",
                                "titlePrefix"=> "",
                                "custName"=> ""
                            )
                        ),
                        "relPartyContactInfo"=> Array(
                            "phoneNum"=> Array(
                                "telephoneNum"=> "",
                                "faxNum"=> "",
                                "telexNum"=> ""
                            ),
                            "emailAddr"=> "",
                            "postAddr"=> Array(
                                "addr1"=> "",
                                "addr2"=> "",
                                "addr3"=> "",
                                "city"=> "",
                                "stateProv"=> "",
                                "postalCode"=> "",
                                "country"=> "",
                                "addrType"=> ""
                            )
                        )
                    );
            $JointApplicantData = 
            [
                  "isMultiRec"=> "Y",
                  "srlNum"=> "0",
                  "delFlag" => "N",
                  "cifId" => $customerOvdDetails[1]->customer_id,
                  "passSheetFlag" => "N",
                  "standingInstruction"=> "N",
                  "depositNoticeFlag"=> "N",
                  "loanOverDueNoticeFlag"=> "N",
                  "xcludeForCombStmtFlag"=> "N"
              ];
          	array_push($relationArray, $relationParty);
          	array_push($joinArray, $JointApplicantData);

	        $data['request']['sbAcctModRequest']['sbAcctModRq']['relPartyRec'] = $relationArray;
	        $data['request']['sbAcctModRequest']['sbAcctModRq'] =  array_merge($data['request']['sbAcctModRequest']['sbAcctModRq'],$drIntMethodInd);
	        $joinArray['joint'] = $joinArray;
	        unset($joinArray[0]);
	        $data['request']['sbAcctModRequest']['sbAcctModCustomData'] =  array_merge($joinArray,$sbAcctId);
        }else{
	        $data['request']['sbAcctModRequest'] =  array_merge($data['request']['sbAcctModRequest'],$drIntMethodInd);
	        $data['request']['sbAcctModRequest']['sbAcctModCustomData'] = $sbAcctId;
        }

                    //  );//request End 
            

        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
        $requestTime = Carbon::now();
    
        //$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest'=>$data]);
		
		// echo "<pre>";print_r($payload);

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
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);
        // echo "<pre>";print_r($response);exit;

        $saveService = CommonFunctions::saveApiRequest('ACCOUNT_ID_AMENDMENT',$url,$payload,json_encode($response),json_encode($data),$response,$formId, $responseTime);


        if(isset($response['gatewayResponse']['status']['isSuccess']) && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ERO00')
        {   
            $encryptedResponse = json_encode($response['gatewayResponse']['response']['data']);
            //$customerIdResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
                                                               
            //$tdAccountResponse = json_decode($encryptedResponse); 
            //$DelightaccountNumber = $tdAccountResponse->sbAcctModResponse->sbAcctModRs->sbAcctId->acctId ;                  
			
			Rules::postAccountIdApiQueue($formId,1);

			$DelightaccountNumber = $delight_kit_details['account_number'];
    
             $updateAccountId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->update(['ACCOUNT_NO'=>$DelightaccountNumber,
                                                                    'APPLICATION_STATUS'=>14]);
                if($updateAccountId){
                    $updateApplicationStatus = CommonFunctions::updateApplicationStatus('ACCOUNT_OPENED',$formId);
                    Rules::postAccountIdApiQueue($formId,5);
                    return ['status'=>'Success','data'=>$DelightaccountNumber];
                 //return $DelightaccountNumber;
                }
                 
        }else{

            if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == '' && $response['gatewayResponse']['status']['message']){

                $apiResponseMessage = $response['gatewayResponse']['status']['message'];

                return ['status'=>'Error','msg'=> 'API Error: '.$apiResponseMessage,'data'=>[]];
            }

            if (isset($response['httpCode']) && isset($response['httpMessage']) && $response['httpMessage'] != '') {
                $apiResponseMessage = $response['httpMessage'];

                return ['status'=>'Error','msg'=> 'HTTP Error: '.$apiResponseMessage,'data'=>[]];
            }else{
				return ['status'=>'Error','msg'=> 'API Error: Unable to process API response','data'=>[]];
			}

            // $DelightaccountNumber = '';

            // return  $DelightaccountNumber;
        }
  

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','AmendAccountApi',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }

    public static function getNomineeDetais($nomineeDetails,$nominee_pcsDetails,$guardian_pcsDetails,$nominee_is_minor,$gaurdCode)
    {
		$gaurdianCode = CommonFunctions::getGaurdianCode($gaurdCode);
		
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

        $returnArray = 
             Array(
                "regNum" => "001",
                "nomineeName" => $nomineeDetails['nominee_name'],
                "relType" => $nomineeDetails['relatinship_applicant'],
                // "recDelFlg" => "" // want clearification
                "nomineeContactInfo" => Array(
                    "phoneNum" => Array(
                                    "telephoneNum" => "",
                                    "faxNum" => "",
                                    "telexNum" => ""
                    ), //phoneNum End
                     "emailAddr" => "",
                     "postAddr" => Array(
                                    "addr1" =>  $nomineeDetails['nominee_address_line1'],
                                    "addr2" =>  $nomineeDetails['nominee_address_line2'],
                                    "addr3" => "",
                                    "city" => $nominee_pcsDetails['citycode'],
                                    "stateProv" => $nominee_pcsDetails['statecode'],
                                    "postalCode" => $nomineeDetails['nominee_pincode'],
                                    "country" => "IN",
                                    "addrType" => "Mailing"
                     )//postAddr End
                ),//nomineeContactInfo End
                "nomineeMinorFlg" => $nominee_is_minor,
                "nomineeBirthDt" => Carbon::parse($nomineeDetails['nominee_dob'])->format('Y-m-d\TH:i:s.v'),
                "nomineePercent" => Array(
                                        "value" => "100"
                ), //nomineePercent End
                "guardianInfo" => $gurdianInfo,
            );							
        // echo "<pre>";print_r($returnArray);exit;

        return  $returnArray;
    }



   public static function retcustinq($custId = '', $formId = ''){
	   
	   // FormID is required for logging purpose!
	
		$accountDetails = array();
        $url = config('constants.APPLICATION_SETTINGS.RET_CUST_INQ');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $requestUUID = CommonFunctions::getRandomValue(10);
		$RequestUUID = 'SS'.CommonFunctions::getRandomValue(13);
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp_string = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
		// encrypt_key_1 to be used, when required!
		
		// $data = Array(
		// 			'header' => Array (							
		// 					"timeStamp" => $current_timestamp,
		// 					"sVersion" => "20",
		// 					"serReqId" => "RetCustInq",
		// 					"apiVersion" => "10.1",
		// 					"requestUUID" => $requestUUID,
		// 					"languageId" => "1",
		// 					"isEnc" => "N",
		// 					"channelId" => "CUBE",
		// 					"cifId" => "101146469"
		// 				),	
		// 			"request" => Array(
  //       	    		  "FIXML" => Array(
  //       	    				  "Header" => Array(
  //       	    				  		   "RequestHeader" => Array(
  //       	    				  		   				   "MessageKey" => Array(
		// 															"ServiceRequestVersion" => "10.2",
  //       	    				  		   				   			"RequestUUID" => $RequestUUID,
		// 															"ChannelId" => "CRM",
		// 															"LanguageId" => "",
		// 															"ServiceRequestId" => "RetCustInq"
  //       	    				  		   				   ),//End of MessageKey
  //       	    				  		   				   "RequestMessageInfo" => Array(
		// 																		"EntityId" => "",
		// 																		"EntityType" => "",
		// 																		"MessageDateTime" => $timestamp_string,
		// 																		"TimeZone" => "",
		// 																		"ArmCorrelationId" => "",
  //       	    				  		   				   						"BankId" => "01"
  //       	    				  		   				   ),//End of RequestMessageInfo
  //       	    				  		   )//End of RequestHeader
  //       	    				    ),//End of Header
  //       	    				    "Body" => Array(
  //       	    				    	"RetCustInqRequest" => Array(
		// 								"getAccountsForCustomer_CustomData" => "",
		// 								"RetCustInqRq" => Array (
		// 										"CustId" => $custId
		// 										)
		// 								)
		// 							) // End of Body
		// 			) // End of FIXML
		// 		) // End of Main Request
		// 	);// End of data Array
		$data = Array(
        "header" => Array(
							"timeStamp" => $current_timestamp,
							"sVersion" => "20",
							"serReqId" => "RetCustInq",
							"apiVersion" => "10.1",
							"requestUUID" => $requestUUID,
							"languageId" => "1",
							"isEnc" => "N",
							"channelId" => "CUBE",
							"cifId" => "101146469"
        ),
					"request" => Array(
        	    		  "FIXML" => Array(
        	    				  "Header" => Array(
        	    				  		   "RequestHeader" => Array(
        	    				  		   				   "MessageKey" => Array(
																	"ServiceRequestVersion" => "10.2",
        	    				  		   				   			"RequestUUID" => $RequestUUID,
																	"ChannelId" => "CRM",
																	"LanguageId" => "",
																	"ServiceRequestId" => "RetCustInq"
                        ),
        	    				  		   				   "RequestMessageInfo" => Array(
																				"EntityId" => "",
																				"EntityType" => "",
																				"MessageDateTime" => $timestamp_string,
																				"TimeZone" => "",
																				"ArmCorrelationId" => "",
        	    				  		   				   						"BankId" => "01"
                        )
                    )
                ),
        	    				    "Body" => Array(
        	    				    	"RetCustInqRequest" => Array(
										"getAccountsForCustomer_CustomData" => "",
                        "RetCustInqRq" => Array(
												"CustId" => $custId
												)
										)
                )
            )
        )
   );	
        //$payload = json_encode(['gatewayRequest'=>$data]);		
		//$payload = array('gatewayRequest'=>$data);
        //$encPayload = EncryptDecrypt::JWEEncryption($payload);

		// echo "<pre>";print_r(json_encode($data));exit;

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
												//'json'=>$encPayload,												
                                                'exceptions'=>false
                                            ]);
        
		
		
		$responseTime = Carbon::now()->diffInSeconds($requestTime);         
		//$response = $guzzleClient->getBody();
        $response = json_decode($guzzleClient->getBody());
				
		try{
			$saveService = CommonFunctions::saveApiRequest('RET_CUST_INQ',$url,'','',json_encode($data),json_encode($response),$formId, $responseTime);
		}catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }
		
		

		//$responseValidate = (json_decode($response));
										
		$returnIDs = Array( 
						'HOME' => '',
						'MAILING' => '',
						'COMMEML' => '',
						'CELLPH' => '',
						'PASPR' => '',
						'RPAN' => '',
						'ADHAR' => '',
						'OCCUPATION' => '',
						'EKYC' => '',
						'NREGA' => '',
						'VOTID' => '',
						'NPR' => '',
						'UTLBL' => '',
						'DRVLC' => '',
						'INPAN'=>'',
						'WORKPH1'=>'',

					);
					
		if(isset($response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->retCustDtls->phoneEmailInfo)){
			$phomeEmailArray = $response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->retCustDtls->phoneEmailInfo;
			//print_r($phomeEmailArray);
			foreach($phomeEmailArray as $pe){
				if($pe->phoneEmailType == 'COMMEML'){
					//print_r('COMMEML ID: '.$pe->phoneEmailID);
					$returnIDs['COMMEML'] = $pe->phoneEmailID;
				}	
				if($pe->phoneEmailType == 'CELLPH'){
					//print_r('COMMEML ID: '.$pe->phoneEmailID);
					$returnIDs['CELLPH'] = $pe->phoneEmailID;
				}

				if($pe->phoneEmailType == 'WORKPH1'){
					//print_r('COMMEML ID: '.$pe->phoneEmailID);
					$returnIDs['WORKPH1'] = $pe->phoneEmailID;
				}
			}			
		}
		if(isset($response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->retCustDtls->retCustAddrInfo)){
			$addArray = $response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->retCustDtls->retCustAddrInfo;
			//print_r($addArray);
			foreach($addArray as $add){
				if($add->addrCategory == 'Home'){
					//print_r('Home ID: '.$add->addressID);
					$returnIDs['HOME'] = $add->addressID;
				}	
				if($add->addrCategory == 'Mailing'){
					//print_r('Mailing ID: '.$add->addressID);
					$returnIDs['MAILING'] = $add->addressID;
				}
			}
		}			
		if(isset($response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->entityDocDtls)){
			$entArray = $response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->entityDocDtls;
			foreach($entArray as $ent){
				if($ent->docCode == 'RPAN'){
					$returnIDs['RPAN'] = $ent->entityDocumentID;
				}	
				if($ent->docCode == 'ADHAR'){
					$returnIDs['ADHAR'] = $ent->entityDocumentID;
				}
				if($ent->docCode == 'PASPR'){
					$returnIDs['PASPR'] = $ent->entityDocumentID;
				}
				if($ent->docCode == 'EKYC'){
					$returnIDs['EKYC'] = $ent->entityDocumentID;
				}
				//AMEND FLOW

				//NREGA
				if($ent->docCode == 'NREGA'){
					$returnIDs['NREGA'] = $ent->entityDocumentID;
				}
				//VoterID
				if($ent->docCode == 'VOTID'){
					$returnIDs['VOTID'] = $ent->entityDocumentID;
				}
				//NPR 
				if($ent->docCode == 'NPR'){
					$returnIDs['NPR'] = $ent->entityDocumentID;
				}

				if($ent->docCode == 'UTLBL'){
					$returnIDs['UTLBL'] = $ent->entityDocumentID;
				}

				if($ent->docCode == 'DRVLC'){
					$returnIDs['DRVLC'] = $ent->entityDocumentID;
				}

				if($ent->docCode == 'INPAN'){
					$returnIDs['INPAN'] = $ent->entityDocumentID;
			}			
		}			
		}			
		
		if(isset($response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->demographicDtls->demographicMiscInfo)){
			$demogArray = $response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->demographicDtls->demographicMiscInfo;			
			if($demogArray->type == 'CURRENT_EMPLOYMENT'){
				$returnIDs['OCCUPATION'] = $demogArray->miscellaneousID;
			}				
		}			

		//echo '<hr><pre>'; 		
		//print_r($response->gatewayResponse->response->data->retCustInqResponse->retCustInqRs->demographicDtls->demographicMiscInfo);
		
		return $returnIDs;
		

   }
   
   public static function genEntityDocArray($idCode, $refNum, $expDate='',$issuesDate=''){
	   
	    $identityRec = DB::table('OVD_TYPES')
                                    ->where('ID',$idCode)
                                    ->get()->toArray();
        $identityRec = (array) current($identityRec); 
	
		$timestamp = Carbon::now()->timestamp;
        $current_timestamp = Carbon::parse($timestamp)->format('Y-m-d\TH:i:s.v');	
	   
	   $docTemplate = [
			"RetIdentificationType" => $identityRec['identification_type'],
			"CountryOfIssue" => "IN",
			"DocCode" => $identityRec['id_proof_code'],
			//"docTypeDesc" => "PROOF OF IDENTITY OR ADDRESS",
			"IssueDt" => $current_timestamp,
			"TypeCode" => $identityRec['doc_type'],
			"PlaceOfIssue" => "I005",
			"ReferenceNum" => $refNum,
			"IDIssuedOrganisation" => 'Ministry of External Affairs',
			//"preferredUniqueId" => "Y",
			//"EntityDocumentID" => ""			
			];
										
		if($idCode == 2 || $idCode == 3){  // Passport or DL
			$docTemplate["ExpDt"] = Carbon::parse($expDate)->format('Y-m-d\TH:i:s.v');
			$docTemplate["IssueDt"] = Carbon::parse($issuesDate)->format('Y-m-d\TH:i:s.v');
		}
		
		return $docTemplate;			
   
   }


   public static function amendforEkycData($crf_id, $customerID,$crf_number){
		try{
   		$formId = $crf_id;
		$timestamp = Carbon::now()->timestamp;
		$current_timestamp = Carbon::parse($timestamp)->format('Y-m-d\TH:i:s.v');
		$RequestUUID = 'C'.CommonFunctions::getRandomValue(14);
		$header_timestamp = Carbon::now()->subDays(50)->format('dmY');
   		$getDynaData = AmendCommonFunctions::apiStructure($crf_id,$crf_number,$customerID);

		$url = config('constants.APPLICATION_SETTINGS.AMEND_CUSTID');
		$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
		$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
		$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
		$encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');		

		if($getDynaData != 'fail'){
			$data = Array(
			    "header" => Array(
			                    "isEnc" => "N",
			                    "apiVersion" => "10.1",
			                    "cifId" => "100212262",
			                    "languageId" => "1",
			                    "channelId" => "CUBE",
			                    "requestUUID" => $RequestUUID,
			                    "sVersion" => "20",
			                    "serReqId" => "RetCustMod",
			                    "timeStamp" => $timestamp
			                ),
			    "request" => Array(
			              "FIXML" => Array(
			                      "Header" => Array(
			                                 "RequestHeader" => Array(
			                                                    "MessageKey" => Array(

			                                                        "RequestUUID" => $RequestUUID,
			                                                        "ServiceRequestId" => "RetCustMod",
			                                                        "ServiceRequestVersion" => "10.2",
			                                                        "ChannelId" => "COR",
			                                                        "LanguageId" => ""

			                                                ),//End of MessageKey
			                                                    "RequestMessageInfo" => Array(
			                                                                            "BankId" => "01",
			                                                                    "TimeZone" => "",
			                                                                    "EntityId" => "",
			                                                                    "EntityType" => "",
			                                                                    "ArmCorrelationId" => "",
			                                                                    "MessageDateTime" => $current_timestamp,
			                                                    ),//End of RequestMessageInfo
			                                                    "Security" => Array(
			                                                            "Token" => Array(
			                                                                "PasswordToken" => Array(
			                                                                                    "UserId" => "",
			                                                                               "Password" => ""
			                                                                )//End of PasswordToken

			                                                            ),//End of Token
			                                                            "FICertToken" => "",
			                                                    "RealUserLoginSessionId" => "",
			                                                    "RealUser" => "",
			                                                    "RealUserPwd" => "",
			                                                    "SSOTransferToken" => ""

			                                                    )//End of Security

			                                 )//End of RequestHeader

			                        ),//End of Header

			                        "Body" => $getDynaData

			                )//End of FIXML
			    )//End of request
			);
		}else{
			return json_encode(['status'=>'fail','msg'=>'Api Failed!','data'=>[]]);
		}

		$client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']); //, 'debug' => true]);
        $requestTime = Carbon::now();
        $payload = json_encode(['gatewayRequest'=>$data]);
        $guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
                                                //'json'=>$payload,
												'exceptions'=>false
                                            ]);
        
		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        //fetching response from server   
        $response = json_decode($guzzleClient->getBody(),true);
        $saveService = CommonFunctions::saveApiRequest('AMEND_KYC',$url,$payload,json_encode($response),json_encode($data),$response,$formId, $responseTime);
		if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess']){
			return json_encode(['status'=>'success','msg'=>'Customer Modification Successfully','data'=>[$response]]);
		}else{
			return json_encode(['status'=>'fail','msg'=>'Customer Modification Failed!','data'=>[$response]]);
		}
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','amendforEkycData',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
   }
   }
   public static function amendforAcmData($accountNo,$crf_number){

        try{
        	$callToDynaArr = AmendCommonFunctions::apiAcmStructure($accountNo,$crf_number);
        	$current_timestamp = Carbon::now()->timestamp;
            $url = config('constants.APPLICATION_SETTINGS.AMEND_ACCOUNT_ID');
            $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
            $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
            $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
			$encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');
			$current_timestamp = Carbon::now()->timestamp;
			$timestamp_string = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
			$account_open_date = Carbon::parse($current_timestamp)->format('d-m-Y');
	
            $data = Array(
                     "header" => Array(
                                    "apiVersion" =>  "1.0.0.0",
                                    "appVersion" =>  "1.0.0.0",
                                    "channelId" =>  "CUBE",
                                    "cifId" =>  "102873457",
                                    "deviceId" =>  "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
                                    "modelName" =>  "Motorola XT1022",
                                    "languageId" =>  "1",
                                    "os" =>  "Android",
                                    "osVersion" =>  "5.1",
                                    "requestUUID" => substr("CUBEAMEND".$crf_number.uniqid(),0,40),
                                    "serReqId" =>  "SBACCTMOD_V1",
                                    "sVersion" =>  "13",
                                    "timeStamp" =>  $current_timestamp,
                                    "isEnc" =>  "N"
                     ),//Header End
                     	"request" => $callToDynaArr
                     );//request End 
            
	        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
	        $requestTime = Carbon::now();
	    
	        //$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
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

	        $responseTime = Carbon::now()->diffInSeconds($requestTime);
	        //fetching response from server   
	        $response = json_decode($guzzleClient->getBody(),true);
	        // echo "<pre>";print_r($response);exit;
	        $saveService = CommonFunctions::saveApiRequest('ACCOUNT_ID_AMENDMENT',$url,$payload,json_encode($response),json_encode($data),$response,$crf_number, $responseTime);

	        if(isset($response['gatewayResponse']['status']['isSuccess']) && isset($response['gatewayResponse']['status']['statusCode']) && $response['gatewayResponse']['status']['statusCode'] == 'ERO00')
	        {   
	            $encryptedResponse = json_encode($response['gatewayResponse']['response']['data']);

	                return json_encode(['status'=>'success','msg'=>'Successfully data updated.','data'=>[]]);
	                 
	        }else{

	            if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == '' && $response['gatewayResponse']['status']['message']){

	                $apiResponseMessage = $response['gatewayResponse']['status']['message'];

	                // return ['status'=>'fail','msg'=> 'API Error: '.$apiResponseMessage,'data'=>$accountNo];
	                return json_encode(['status'=>'fail','msg'=>'API Error: '.$apiResponseMessage,'data'=>$accountNo]);

	            }
	            if (isset($response['httpCode']) && isset($response['httpMessage']) && $response['httpMessage'] != '') {
	                $apiResponseMessage = $response['httpMessage'];

	                // return ['status'=>'fail','msg'=> 'HTTP Error: '.$apiResponseMessage,'data'=>[]];
	                return json_encode(['status'=>'fail','msg'=>'HTTP Error: '.$apiResponseMessage,'data'=>[]]);

	            }else{
					// return ['status'=>'fail','msg'=> 'API Error: Unable to process API response','data'=>[]];
	                return json_encode(['status'=>'fail','msg'=>'API Error: Unable to process API response','data'=>[]]);

				}
	        }
        }catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','amendforAcmData',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
	
    public static function accountClouser($accountNo){
		try{
        $url = config('constants.APPLICATION_SETTINGS.ACCOUNT_CLOUSER'); 
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');
        $current_timestamp =  Carbon::now()->timestamp;
        $requestTime = Carbon::now();

        $hash1 = hash('sha256', $current_timestamp);
        $hash2 = hash('md5',$current_timestamp);
        $RequestUUID = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);

        $data  = Array(
                            "header" =>  Array(
                                "isEnc" =>  "Y",
                                "apiVersion" =>  "10.1",
                                "cifId" =>  "100212262",
                                "languageId" =>  "1",
                                "channelId" =>  "CUBE",
                                // "requestUUID" =>  "254k621c-9aiL-87p-02kb-1cb8898r8lk1",
                                "requestUUID" =>  $RequestUUID,
                                "serReqId" =>  "ESB_SBACCTCLOSE_SCRIPTS_FIS",
                                // "serReqId" =>  "CUBE_SBACCTCLOSE_SCRIPTS_FIS",
                                "timeStamp" =>  $current_timestamp
                            ),
                            "request" =>  Array(

                                "executeFinacleScriptRequest" =>  Array(

                                    "executeFinacleScriptInputVO" =>  Array(

                                        "requestId" =>  "SBAcctclose.scr"

                                    ),
                                    
                                    "executeFinacleScriptCustomData" =>  Array(

                                        "AccountNumber" => $accountNo
                                    )
                                )
                            )
                        );

        // echo "<pre>";print_r(($data));
        $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload = json_encode(['gatewayRequest' => $data]);
        $client = new \GuzzleHttp\Client();

        $guzzleClient = $client->request('POST',$url,
                                            [ 'headers' => [
                                                'Content-Type' => 'application/json',
                                                'X-IBM-Client-secret' => $client_key,
                                                'X-IBM-Client-Id' => $client_id,
                                                'authorization' => $authorization
                                                ],

                                                'json'=>['gatewayRequest' => $data],
                                                'exceptions' => false
                                            ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $saveService = CommonFunctions::saveApiRequest('ACCOUNT_CLOSURE',$url,$data,$response,$payload,$response,$accountNo,$responseTime);
        // echo "<pre>";print_r(json_encode($response));exit;
        // echo "<pre>";print_r($response['gatewayResponse']);exit;
        if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == 1){
			return json_encode(['status'=>'success','msg'=>'Successfully Closing this account.','data'=>[]]);
		}else{
			return json_encode(['status'=>'fail','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]]);
        }
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','accountClouser',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function nomineeModification($accountNo,$crf_number){
		try{
        $url = config('constants.APPLICATION_SETTINGS.NOMINEE_MODI'); 
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        // $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); //Auth confirmed by Sreesa 12.07.22
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

        $current_timestamp =  Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $callToDynaArr =  AmendCommonFunctions::apiAcmStructure($accountNo,$crf_number);
        $hash1 = hash('sha256', $current_timestamp);
        $hash2 = hash('md5',$current_timestamp);
        $RequestUUID = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);
        $crfs = substr($crf_number,0,11);

              $data = Array( 
                "header"=> Array( 
                    "apiVersion" => "1.0.0.0",
                    "appVersion" => "1.0.0.0",
                    "channelId" => "CUBE",
                    "cifId" => "102873457",
                    "deviceId" => "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
                    "modelName" => "Motorola XT1022",
                    "languageId"=> "1",
                    "os" => "Android",
                    "osVersion" => "5.1",
                    // "requestUUID" => "26314877-9clj-3955-F-f63r4624f207",	
                    "requestUUID" => $RequestUUID,	
                    // "requestUUID" => substr("CUBEDELAA".$crfs.uniqid(),0,35),
                    "serReqId" => "SBACCTMOD_V1",
                    "sVersion" => "13",
                    "timeStamp" => $current_timestamp,
                    "isEnc" => "N"
                ),
                "request" => $callToDynaArr
 			);
        // $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $payload =  json_encode(['gatewayRequest'=>$data]);
        $client = new \GuzzleHttp\Client;

        $guzzleClient = $client->request('POST',$url,
                                            [ 'headers' => [
                                                'Content-Type' => 'application/json',
                                                'X-IBM-Client-secret' => $client_key,
                                                'X-IBM-Client-Id' => $client_id,
                                                'authorization' => $authorization
                                                ],

                                                // 'json'=>[$requestId],
                                                'json' => ['gatewayRequest' => $data],
                                                'exceptions' => false
                                            ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $saveService = CommonFunctions::saveApiRequest('NOMINEE_MODIFICATION',$url,$data,$response,$payload,$response,$crf_number,$responseTime);
	
		if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == 1){
				return json_encode(['status'=>'success','msg'=>'Successfully ACM data updated.','data'=>[]]);
		}else{
				return json_encode(['status'=>'fail','msg'=>'Api failed, Please try again later.','data'=>[]]);
    }
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','nomineeModification',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function SBAcctInq($accountNo){
		try{
    	$url = config('constants.APPLICATION_SETTINGS.SBA_ACCOUNT_INQ');
    	$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); //Auth confirmed by Sreesa 12.07.22
        $current_timestamp =  Carbon::now()->timestamp;
        $requestTime = Carbon::now();
        $RequestUUID = Carbon::now()->format('dmYYYYHms');
     
    	$data = Array(
				     // "gatewayRequest" =>  Array(
				     "header" =>  Array(
				     "apiVersion" =>  "1.0.0.0",
				     "appVersion" =>  "1.0.0.0",
				     // "channelId" =>  "MB",
				     "channelId" =>  "CUBE",
				     "cifId" =>  "102873457",
				     "deviceId" =>  "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
				     "modelName" =>  "Motorola XT1022",
				     "languageId" =>  "1",
				     "os" =>  "Android",
				     "osVersion" =>  "5.1",
				     // "requestUUID" =>  '29042021124652128',
				     "requestUUID" =>  $RequestUUID,
				     "serReqId" =>  "SBACCTINQ_V1",
				     "sVersion" =>  "13",
				     "timeStamp" =>  $current_timestamp,
				     "isEnc" =>  "N"
				     ),

				     "request" =>  Array(
				             "sbAcctInqRequest" =>  Array(
				             "sbAcctInqRq" =>  Array(
				             "sbAcctId" =>  Array(
				             "acctId" =>  $accountNo,
				                 "acctType" =>  Array(
				                 "schmCode" =>  "",
				                 "schmType" =>  ""
				                ),
				             "acctCurr" =>  "",
				             "bankInfo" =>  Array(
				             "bankId" =>  "",
				             "name" =>  "",
				             "branchId" =>  "",
				             "branchName" =>  "",
				             "postAddr" =>  Array(
				             "addr1" =>  "",
				             "addr2" =>  "",
				             "addr3" =>  "",
				             "city" =>  "",
				             "stateProv" =>  "",
				             "postalCode" =>  "",
				             "country" =>  "",
				             "addrType" =>  ""
				                        )
				                    )
				                )
				            ),
				            "sbAcctInqCustomData" =>  ""
				            )
				        )
				    // )
				);

    	$payload = json_encode(['gatewayRequest' => $data]);
    	$client = new \GuzzleHttp\Client;

		$guzzleClient = $client->request('POST',$url,
	                                        [ 'headers' => [
	                                            'Content-Type' => 'application/json',
	                                            'X-IBM-Client-secret' => $client_key,
	                                            'X-IBM-Client-Id' => $client_id,
	                                            'authorization' => $authorization
	                                            ],

	                                            // 'json'=>[$requestId],
	                                            'json' => ['gatewayRequest' => $data],
	                                            'exceptions' => false
	                                        ]);

        $response = json_decode($guzzleClient->getBody(),true);
        // echo "<pre>";print_r($response);exit;
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $saveService = CommonFunctions::saveApiRequest('SBA_ACCOUNT_INQ',$url,$data,$response,$payload,$response,$accountNo,$responseTime);
        if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'Success'){

        	$response = $response['gatewayResponse']['response']['data'];
        	
        	return $response;
        }else{
        	return ['status'=>'fail','msg'=>'Api Failed!.Please try later','data'=>[]];
        }
        // echo "\nResponse\n";print_r($response);exit;
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','SBAcctInq',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function mergeCustId($customerId,$accountNo,$crf_number){
		try{
    	$url = config('constants.APPLICATION_SETTINGS.MERGE_CUST_ID'); 
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); //Auth confirmed by Sreesa 12.07.22
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

        $current_timestamp =  Carbon::now()->timestamp;
        $requestTime = Carbon::now();

    	$hash1 = hash('sha256', $current_timestamp);
        $hash2 = hash('md5',$current_timestamp);
    	$RequestUUID = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);
    	
    	$newgetCustId = AmendCommonFunctions::getDynamicvalue($crf_number,'Merging Of Customer Id','NEW_VALUE'); 
    	$data = Array(
				        "header" => Array(
				            "isEnc" => "Y",
				            "apiVersion" => "10.1",
				            "cifId" => "100212262",
				            "languageId" => "1",
				            "channelId" => "CUBE",
				            "requestUUID" => $RequestUUID,
				            "serReqId" => "ESB_CUSTIDMOD_SCRIPTS_FIS",
				            "timeStamp" => $current_timestamp,
				        ),
				        "request" => Array(
				            "executeFinacleScriptRequest" => Array(
				                "executeFinacleScriptInputVO" => Array(
				                    "requestId" => "custIdModification.scr"
				                ),
				                "executeFinacleScriptCustomData" => Array(
				                    "OldCustId" => $customerId,
				                    "AccountNumber" => $accountNo,
				                    "NewCustId" => $newgetCustId['_MERGE_CUST_ID'],
				                )	
				            )
				        )
				    );

    	// echo "<pre>";print_r(json_encode($data));
    	$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
    	$payload = json_encode(['gatewayRequest'=>$data]);
    	$client =  new \GuzzleHttp\Client;

    	$guzzleClient = $client->request('POST',$url,
	                                        [ 'headers' => [
	                                            'Content-Type' => 'application/json',
	                                            'X-IBM-Client-secret' => $client_key,
	                                            'X-IBM-Client-Id' => $client_id,
	                                            'authorization' => $authorization
	                                            ],

	                                            'json' => ['gatewayRequest' => $data],
	                                            'exceptions' => false
	                                        ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $saveService = CommonFunctions::saveApiRequest('MERGE_CUST_ID',$url,$data,$response,$payload,$response,$crf_number,$responseTime);
        // $response = isset($response['gatewayResponse']['response']) && $response['gatewayResponse']['response'] !=''?$response['gatewayResponse']['response']:'';

        $response =  isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'Success' ?$response['gatewayResponse']['status']['message'] : '';

        if(isset($response['gatewayResponse']['status']['message']) && $response['gatewayResponse']['status']['message'] == 'Success'){
        	return json_encode(['status'=>'success','msg'=>$response,'data'=>[$response]]);
        }else{
        	return json_encode(['status'=>'fail','msg'=>$response,'data'=>[$response]]);
        }
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','mergeCustId',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }	

    public static function gpaCancelation($accountNo,$crf_number){
    	try{
    	$url = config('constants.APPLICATION_SETTINGS.GPA_CANCEL');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); //Auth confirmed by Sreesa 12.07.22
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');
	
        $current_timestamp =  Carbon::now()->timestamp;
        $requestTime = Carbon::now();
    	$getpolicyNo = AmendCommonFunctions::getDynamicvalue($crf_number,'Cancellation Of GPA','NEW_VALUE'); 
    	
    	$requestUUID = CommonFunctions::getRandomValue(12);

    	$data = Array(	"header" => Array(
						        "isEnc" => "Y",
						        "apiVersion" => "10.1",
						        "cifId" => "100212262",
						        "languageId" => "1",
						        "channelId" => "CUBE",
						        "requestUUID" => $requestUUID,
						        "serReqId" => "ESB_GPACANCELATION_SCRIPTS_FIS",
						        "timeStamp" => $current_timestamp
						    ),
						    "request" => Array(
						        "executeFinacleScriptRequest" => Array(
						            "executeFinacleScriptInputVO" => Array(
						                "requestId" => "GPA_CANCELATION.scr"
						            ),
						            "executeFinacleScriptCustomData" => Array(
						                "funcCode" => "D",
						                "policyNum" => $getpolicyNo['_POLICY_NO'],
						                "acctNum" => "02911100080149",
							    		"custId" => "100847179",
						                "vLogRefId" => ""
						            )
						        )
						    )
						
					);


    	$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
    	$payload = json_encode(['gatewayRequest'=>$data]);
    	$client =  new \GuzzleHttp\Client;
    	// echo "Request<pre>\n";print_r(json_encode($data));

    	$guzzleClient = $client->request('POST',$url,
	                                        [ 'headers' => [
	                                            'Content-Type' => 'application/json',
	                                            'X-IBM-Client-secret' => $client_key,
	                                            'X-IBM-Client-Id' => $client_id,
	                                            'authorization' => $authorization
	                                            ],

	                                            'json' => ['gatewayRequest' => $data],
	                                            'exceptions' => false
	                                        ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);
       	$saveService = CommonFunctions::saveApiRequest('GPA_CANCELATION',$url,$data,$response,$payload,$response,$crf_number,$responseTime);

        $response = isset($response['gatewayResponse']['response']['data']) && $response['gatewayResponse']['response']['data']!=''?$response['gatewayResponse']['response']['data']:'';

        if($response['gatewayResponse']['status']['message'] == 'Success'){
        	
        	// return ['status'=>'Success','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        	return json_encode(['status'=>'success','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]]);

        }else{
        	// return ['status'=>'fail','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        	return json_encode(['status'=>'fail','msg'=>'Api failed, Please try again later.','data'=>[$response]]);
        }
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','gpaCancelation',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function schemeConversion($accountNo,$crf_number){
		try{
    	$url = config('constants.APPLICATION_SETTINGS.SCHEME_CONV');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION'); 
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');
        $current_timestamp =  Carbon::now()->timestamp;
	    $timestamp = Carbon::parse($current_timestamp)->format('dmY');
        $requestTime = Carbon::now();
    	$requestUUID = CommonFunctions::getRandomValue(12);
    	$schemConversion = AmendCommonFunctions::getDynamicvalue($crf_number,'Scheme Conversion','NEW_VALUE'); 

        $data = Array(	"header" => Array(
			            "isEnc" => "Y",
			            "apiVersion" => "10.1",
			            "cifId" => "100212262",
			            "languageId" => "1",
			            "channelId" => "CUBE",
			            "requestUUID" => $requestUUID,
			            "serReqId" => "ESB_SCHEMECODECONV_SCRIPTS_FIS",
			            "timeStamp" => $timestamp
				        ),
				        "request" => Array(
				            "executeFinacleScriptRequest" => Array(
				                "executeFinacleScriptInputVO" => Array(
				                    "requestId" => "SchemeCodeCoversion.scr"
				                ),
        	
				                "executeFinacleScriptCustomData" => Array(
				                    "CurrencyCode" => "INR",
				                    "NewSchemeCode" => $schemConversion['SCHM_CODE_NEW'],
				                    "OldSchemeCode" => $schemConversion['SCHM_CODE'],
						    		"GlSubHeadCode" => $schemConversion['GL_SUB_HEAD_CODE'],
				                    "AccountNumber" => $accountNo
				                )
				            )
				        )
					);

        // echo "<pre>";print_r(($data));exit;

    	$data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
    	$payload = json_encode(['gatewayRequest'=>$data]);
    	$client =  new \GuzzleHttp\Client;

    	$guzzleClient = $client->request('POST',$url,
	                                        [ 'headers' => [
	                                            'Content-Type' => 'application/json',
	                                            'X-IBM-Client-secret' => $client_key,
	                                            'X-IBM-Client-Id' => $client_id,
	                                            'authorization' => $authorization
	                                            ],

	                                            'json' => ['gatewayRequest' => $data],
	                                            'exceptions' => false
	                                        ]);

        $response = json_decode($guzzleClient->getBody(),true);
        $responseTime = Carbon::now()->diffInSeconds($requestTime);

        $saveService = CommonFunctions::saveApiRequest('SCHEME_CONVERSION',$url,$data,$response,$payload,$response,$crf_number,$responseTime);
        // echo "<pre>";print_r(json_encode($response));exit;
        if($response['gatewayResponse']['status']['message'] == 'success'){
        	// return ['status'=>'Success','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        	return json_encode(['status'=>'success','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]]);
        }else{
        	// return ['status'=>'fail','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        	return json_encode(['status'=>'fail','msg'=>$response['gatewayResponse']['status']['message'],'data'=>[]]);
        }
		}catch(\Throwable $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','schemeConversion',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function pinManagement(){
		try{
        $url = config('constants.APPLICATION_SETTINGS.PIN_MANAGEMENT');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $requestUUID = 'PINGEN'.CommonFunctions::getRandomValue(13);
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp_string = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');


        $data = Array(
						"header" => Array(
							"apiVersion" => "1.0.0.0",
							"appVersion" => "1.0.0.0",
							"channelId" => "CUBE",
							"cifId" => "100948522",
							"deviceId" => "",
							"modelName" => "",
							"languageId" => "1",
							"os" => "",
							"osVersion" => "",
							"requestUUID" => $requestUUID,
							"serReqId" => "ESBPINMANAGEMENT",
							"sVersion" => "13",
							"timeStamp" => $current_timestamp,
							"isEnc" => "N"
							),
							"request" => Array(
									"inAuditDs" => Array(
									"application" => "CMS",
									"userId" => "DCBPIBUSR",
									"organization" => "FDC",
									"serviceId" => "PINSRSMNGM",
									"sequence" => "1602236923064"
									),
									"inPartId" => "FDC",
									"inCard" => "5044391000021246",
									"inMbr" => "1",
									"inFunction" => "*PINCGWOTH",
									"inOffsetFlag" => "P",
									"inAcceptorID" => "100948522",
									"inOldOffset" => "2405",
									"inOldPvv" => "553",
									"inNewPin" => "ED1375D79D82AEA1",
									"inNewOffset" => ""
							)
					);
        // echo "<pre>";print_r(json_encode($data));


       	$client =  new \GuzzleHttp\Client;
       	$requestTime = Carbon::now();	        
		$guzzleClient = $client->request('POST',$url,
                                            [   'headers' =>[
                                                    'Content-Type'=>'application/json',
                                                    'X-IBM-Client-secret'=>$client_key,
                                                    'X-IBM-Client-Id'=>$client_id,
                                                    'authorization'=>$authorization
                                                ],
                                                'json'=>['gatewayRequest'=>$data],
												//'json'=>$encPayload,												
                                                'exceptions'=>false
                                            ]);
        
		
		
		$responseTime = Carbon::now()->diffInSeconds($requestTime);         
        $response = json_decode($guzzleClient->getBody());

        echo "\npinManagement<pre>";print_r($response);exit;
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			//CommonFunctions::addExceptionLog($eMessage, $request);
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','pinManagement',$eMessage,'','');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function amenduploadSignature($account_id,$crf_number)
    {
        try{
    		
        	$getEvidenceData = AmendCommonFunctions::getEvidenceList($crf_number,10);
        	$amendPath = storage_path('uploads/amend/'.$getEvidenceData);
        	$encodeImage = '';
        	
        	if(File::exists($amendPath)){
        		$imageFile = file_get_contents($amendPath);
        		$encodeImage = base64_encode($imageFile);
        	}

        	$getmopContentType = AmendCommonFunctions::getDynamicvalue($crf_number,'Mode Of Operation','NEW_VALUE_DISPLAY');
       
        	$signatureDate = Carbon::now()->format('Y-m-d\TH:i:s.v');
	        $url = config('constants.APPLICATION_SETTINGS.SIGNATURE_UPLOAD');
	        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
	        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
	        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
	        $RequestUUID = '25dff7d7-4d52-524b-99d6-60b500e23011'; //CommonFunctions::getRandomValue(9);
	        $current_timestamp = Carbon::now()->timestamp;
	        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
			$encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1'); //'casa@2018';
	        // $encrypt_key = 'june2020'; 

	        $hash1 = hash('sha256', $timestamp);
	        $hash2 = hash('md5', $timestamp);

	        $uuid = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);

			$getAccountDetails = Api::accountNumberDetails($account_id);
			if(!isset($getAccountDetails['accountDetails']['status']) || strtoupper($getAccountDetails['accountDetails']['status']) != 'SUCCESS'){
					return json_encode(['status'=>'fail','msg'=>'try again','data'=>[]]);
			}
			// echo "<pre>";print_r($getAccountDetails['accountDetails']['SCHM_CODE']);exit;
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
																	'signEffDt' => $signatureDate, // Current Date 
																	'signExpDt' => '2099-01-20T00:00:00.000', // Current Date 
																	'sigFile' => $encodeImage,
	                                                                // 'Remarks' => $getmopContentType['MODE_OF_OPERATION'],
																	'Remarks' => $getAccountDetails['accountDetails']['MODE_OF_OPERATION'],
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
			// echo "<pre>";print_r($response);exit;
	        if(isset($response['gatewayResponse'])){
	            $response = $response['gatewayResponse'];
	            if($response['status']['isSuccess'] == 1){
	                $encryptedResponse = json_encode($response['response']);
	                $decResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
	                 $saveService = CommonFunctions::saveApiRequest('UPLOAD_SIGNATURE',$url,$payload,$encryptedResponse,
	                                                                            json_encode($data),$response,$crf_number, $responseTime);
	                $signResponse = $decResponse['data']['signatureAddResponse']['signatureAddRs']['sigAddStatusCode'];
	                // return ['status'=>'success','data'=>$signResponse, 'message'=>'Signature uploaded successfully!'];
					return json_encode(['status'=>'success','data'=>$signResponse, 'msg'=>'Signature uploaded successfully!']);

	            }else{
	                $saveService = CommonFunctions::saveApiRequest('UPLOAD_SIGNATURE',$url,$payload,json_encode($response),
	                                                                            json_encode($data),$response,$crf_number, $responseTime);
	                return json_encode(['status'=>'fail','data'=>$response, 'msg'=>'API Response not successfull!']);
	            }

		        }else{
		                $saveService = CommonFunctions::saveApiRequest('UPLOAD_SIGNATURE',$url,$payload,json_encode($response),
		                                                                            json_encode($data),$response,$crf_number, $responseTime);  
		                return json_encode(['status'=>'fail','data'=>$response, 'msg'=>'Error in API Response!']);
		        }

		}catch(\Throwable $e){            
	        if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','amenduploadSignature',$eMessage,'','');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    	}
    }

	public static function checkBookIssuence($accountNo,$crf_number){
		try{
		$url = config('constants.APPLICATION_SETTINGS.CHECKBOOK_ISSUENCE');
		$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
		$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
		$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
		$current_timestamp = Carbon::now()->timestamp;
		$timestamp = Carbon::parse($current_timestamp)->format('dmY');


		$checkData = Self::chequeInquiry($accountNo,$crf_number); 
		echo "<pre>";print_r($checkData);exit;

		$data = Array(
				"header"=>Array(
						"isEnc"=>"N",
						"apiVersion"=>"1.0",
						"cifId"=>"100212262",
						"languageId"=>"1",
						"channelId"=>"ESB",
						"requestUUID"=>"4edda7735697111",
						"serReqId"=>"ESB_CHEQUEBOOKADD_FIS",
						// "timeStamp"=>"14122022"
						"timeStamp" => $timestamp
					),
					"request"=>Array(
						"chkbkAddRequest"=>Array(
							"chkbkAddRq"=>Array(
								"leavesPerBk"=>20,
								"ackObtainedFlg"=>"N",
								"chkbkAckDetailInfoRec"=>Array(
									"numOfLeaves"=>20,
									"beginChkAlpha"=>"CHQ",
									"beginChkNum"=>"21"
								),
								"acctId"=>"18211100001540",
								"invtReqd"=>"N"
							)
						)
					)
				);

			$client =  new \GuzzleHttp\Client;
			$requestTime = Carbon::now();	        
			$guzzleClient = $client->request('POST',$url,
												 [   'headers' =>[
														 'Content-Type'=>'application/json',
														 'X-IBM-Client-secret'=>$client_key,
														 'X-IBM-Client-Id'=>$client_id,
														 'authorization'=>$authorization
													 ],
													 'json'=>['gatewayRequest'=>$data],
													 //'json'=>$encPayload,												
													 'exceptions'=>false
												 ]);
			 
			 
			 
			$responseTime = Carbon::now()->diffInSeconds($requestTime);         
			$response = json_decode($guzzleClient->getBody());
			
			echo "<pre>";print_r($data);
			echo "<pre>";print_r($response);exit;
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			//CommonFunctions::addExceptionLog($eMessage, $request);
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','checkBookIssuence',$eMessage,$crf_number,'');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}


	public static function customerRetCustInq($custId = '', $formId = ''){
		$accountDetails = array();
        $url = config('constants.APPLICATION_SETTINGS.RET_CUST_INQ');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $requestUUID = CommonFunctions::getRandomValue(10);
		$RequestUUID = 'SS'.CommonFunctions::getRandomValue(13);
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp_string = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
		// encrypt_key_1 to be used, when required!
		$data = Array(
        "header" => Array(
							"timeStamp" => $current_timestamp,
							"sVersion" => "20",
							"serReqId" => "RetCustInq",
							"apiVersion" => "10.1",
							"requestUUID" => $requestUUID,
							"languageId" => "1",
							"isEnc" => "N",
							"channelId" => "CUBE",
							"cifId" => "101146469"
        ),
					"request" => Array(
        	    		  "FIXML" => Array(
        	    				  "Header" => Array(
        	    				  		   "RequestHeader" => Array(
        	    				  		   				   "MessageKey" => Array(
																	"ServiceRequestVersion" => "10.2",
        	    				  		   				   			"RequestUUID" => $RequestUUID,
																	"ChannelId" => "CRM",
																	"LanguageId" => "",
																	"ServiceRequestId" => "RetCustInq"
                        ),
        	    				  		   				   "RequestMessageInfo" => Array(
																				"EntityId" => "",
																				"EntityType" => "",
																				"MessageDateTime" => $timestamp_string,
																				"TimeZone" => "",
																				"ArmCorrelationId" => "",
        	    				  		   				   						"BankId" => "01"
                        )
                    )
                ),
        	    				    "Body" => Array(
        	    				    	"RetCustInqRequest" => Array(
										"getAccountsForCustomer_CustomData" => "",
                        "RetCustInqRq" => Array(
												"CustId" => $custId
												)
										)
                )
            )
        )
   );	
    
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
												//'json'=>$encPayload,												
                                                'exceptions'=>false
                                            ]);
        
		
		
		$responseTime = Carbon::now()->diffInSeconds($requestTime);         
		//$response = $guzzleClient->getBody();
        $response = json_decode($guzzleClient->getBody());
		return $response;
		// echo "<pre>";print_r($response);exit;
				
		try{
			$saveService = CommonFunctions::saveApiRequest('RET_CUST_INQ',$url,'','',json_encode($data),json_encode($response),$formId, $responseTime);
		}catch(\Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }
	}

	public static function cadormantAccActiveation($accountNo,$crf_number){
		try{
			$url = config('constants.APPLICATION_SETTINGS.CA_DORMANT_ACT');
		$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
		$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
		$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
		$current_timestamp = Carbon::now()->timestamp;
		$timestamp = Carbon::parse($current_timestamp)->format('dmY');
        
			$data = Array(
							"header"=> Array(
								"isEnc"=> "N",
								"apiVersion"=> "1.0",
								"cifId"=> "100212262",
								"languageId"=> "1",
								"channelId"=> "ESB",
								"requestUUID"=> "CUBETE654dedas1222342214",
								"serReqId"=> "ESB_CAACCTMOD_FIS",
								"timeStamp"=> $timestamp
							),
							"request"=> Array(
								"caAcctModRequest"=> Array(
									"caAcctModRq"=> Array(
										"caAcctId"=> Array(
											"acctId"=> $accountNo
										)
									),
									"caAcctModCustomData"=> Array(
										"astatus"=> "A"
									)
								)
							)
						
					);
					echo "<pre>";print_r(json_encode($data));

					$client =  new \GuzzleHttp\Client;
					$requestTime = Carbon::now();	        
					$guzzleClient = $client->request('POST',$url,
															[   'headers' =>[
																	'Content-Type'=>'application/json',
																	'X-IBM-Client-secret'=>$client_key,
																	'X-IBM-Client-Id'=>$client_id,
																	'authorization'=>$authorization
																],
																'json'=>['gatewayRequest'=>$data],
																//'json'=>$encPayload,												
																'exceptions'=>false
															]);
						
					$responseTime = Carbon::now()->diffInSeconds($requestTime);         
					$response = json_decode($guzzleClient->getBody());
					echo "<pre>";print_r(json_encode($response));exit;
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			//CommonFunctions::addExceptionLog($eMessage, $request);
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','cadormantAccActiveation',$eMessage,$crf_number,'');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

    public static function chequeInquiry($account_No,$crf_number){
		try{
        $url = config('constants.APPLICATION_SETTINGS.CHQ_INQ');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();
//        $requestUUID = '566e1e75-2e34-7ba5-d237-a4a9c58707bs';
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');

        $hash1 = hash('sha256', $timestamp);
        $hash2 = hash('md5', $timestamp);
        $uuid = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);


      $data =  Array(
                "header"=> Array(
                    "isEnc"=> "N",
                    "apiVersion"=> "1.0",
                    "cifId"=> "100212262",
                    "languageId"=> "1",
                    "channelId"=> "CUBE",
                    "requestUUID"=> $uuid,
                    "serReqId"=> "ESB_CHEQUEBOOKINQ_FIS",
                    "timeStamp"=> $timestamp
                ),
                "request"=> Array(
                    "doChqBookDtlInqRequest"=> Array(
                        "chqBookDtlInqInputVO"=> Array(
                            "criteria"=> Array(
                                "acct"=> Array(
                                    "forACID"=> "01014200000073"
                                )
                            )
                        )
                    )
                )
        );

        $client =  new \GuzzleHttp\Client;
        $requestTime = Carbon::now();
        $guzzleClient = $client->request('POST',$url,
            [   'headers' =>[
                'Content-Type'=>'application/json',
                'X-IBM-Client-secret'=>$client_key,
                'X-IBM-Client-Id'=>$client_id,
                'authorization'=>$authorization
            ],
                'json'=>['gatewayRequest'=>$data],
                //'json'=>$encPayload,
                'exceptions'=>false
            ]);

        $responseTime = Carbon::now()->diffInSeconds($requestTime);
        $response = json_decode($guzzleClient->getBody());
        echo "<pre>";print_r($response);exit;
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			//CommonFunctions::addExceptionLog($eMessage, $request);
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','chequeInquiry',$eMessage,$crf_number,'');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

	public static function aadharLinkAdd($customerId,$accountNo,$aadhaarRef,$crf_number){
		try{
        $url = config('constants.APPLICATION_SETTINGS.AADHAR_LINK_ADD');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
        $current_timestamp = Carbon::now()->timestamp;
        $timestamp = Carbon::parse($current_timestamp)->format('Y-m-d\TH:i:s.v');
        $data = Array(
					"header" => Array(
						"isEnc" => "N",
						"apiVersion" => "10.1",
						"cifId" => "100212262",
						"languageId" => "1",
						"channelId" => "CUBE",
						// "requestUUID" => "781f93c466356819",
						"requestUUID" => CommonFunctions::getRandomValue(16),
						"serReqId" => "ESB_AADHARLINKADD_SCRIPTS_FIS",
						"timeStamp" => $timestamp
					),
					"request" => Array(
						"aadharLinkAddRequest" => Array(
								"customerId" => $customerId,
								"acctNum" => $accountNo,
								"aadharNumber" => $aadhaarRef
						)
					)
				);
        $client =  new \GuzzleHttp\Client;
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
        $response = json_decode($guzzleClient->getBody());
			$saveService = CommonFunctions::saveApiRequest('AADHAR_LINK_UPDATE',$url,$data,$response,json_encode($data),$response,$crf_number,$responseTime);
			$response = json_encode($response);
			$gatewayResponse = json_decode($response,true);
			
			if(isset($gatewayResponse['gatewayResponse']['status']['isSuccess']) && $gatewayResponse['gatewayResponse']['status']['isSuccess'] == 1){
				$msg = isset($gatewayResponse['gatewayResponse']['response']['data']['resultMsg']) && $gatewayResponse['gatewayResponse']['response']['data']['resultMsg'] != ''?$gatewayResponse['gatewayResponse']['response']['data']['resultMsg']:'';
				return json_encode(['status'=>'success','msg'=>$msg,'data'=>[$gatewayResponse]]);
			}else{
				$msg = isset($gatewayResponse['gatewayResponse']['status']['message']) && $gatewayResponse['gatewayResponse']['status']['message'] != ''?$gatewayResponse['gatewayResponse']['status']['message']:'';
				return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[$gatewayResponse]]);
    }
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			//CommonFunctions::addExceptionLog($eMessage, $request);
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','aadharLinkAdd',$eMessage,$crf_number,'');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
    }

	public static function EtbMobileEmailAdding($formId,$customerId){
		try{
			$timestamp = Carbon::now()->timestamp;
			$RequestUUID = 'C'.CommonFunctions::getRandomValue(14);
			$current_timestamp = Carbon::parse($timestamp)->format('Y-m-d\TH:i:s.v');
			
			$url = config('constants.APPLICATION_SETTINGS.AMEND_CUSTID');
			$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
			$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
			$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
			$encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');	
			
			
			$getEtbCustDetails =  DB::table('ETB_CUST_DETAILS')->select('FINACLE_EMAIL',
																		'FINACLE_MOB_NO','CUBE_EMAIL','CUBE_MOB_NO')
																		->where('FORM_ID',$formId)
																		->where('CUSTOMER_ID',$customerId)
																		->get()
																		->toArray();
			$getEtbCustDetails = (array) current($getEtbCustDetails);

			$getOVDDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('MOBILE_NUMBER','EMAIL')
															 ->where('FORM_ID',$formId)
															 ->where('CUSTOMER_ID',$customerId)
															 ->get()
															 ->toArray();
			$getOVDDetails  = (array) current($getOVDDetails);
			// echo "<prew>";print_r($getEtbCustDetails);exit;
			$existingIDs = AmendApi::retcustinq($customerId, $formId);
			$callDynamically = array();
			
			if($getEtbCustDetails['finacle_mob_no'] == '' || ($getEtbCustDetails['finacle_mob_no'] != $getOVDDetails['mobile_number'])){
				$PhoneNum =  [ "PhoneNum" => $getOVDDetails['mobile_number'],
								"PhoneNumCityCode" => "",
								"PhoneNumCountryCode" => "91",
								"PhoneNumLocalCode" => $getOVDDetails['mobile_number'],
								"PhoneOrEmail" => "PHONE",
								"PhoneEmailtType" => "CELLPH",
								];
								
				if($existingIDs['CELLPH'] != ''){
					$PhoneNum["PhoneEmailID"] = $existingIDs['CELLPH'];
				}
				array_push($callDynamically,$PhoneNum);

			}
			if($getEtbCustDetails['finacle_email'] == '' || ($getEtbCustDetails['finacle_email'] != $getOVDDetails['email'])){
			
				
				$commEmailArray = [	"Email" => $getOVDDetails['email'],
									"PhoneOrEmail" => "EMAIL",
									"PrefFlag" => "Y",
									"PhoneEmailtType" => "COMMEML",					
								];
			
				if($existingIDs['COMMEML'] != ''){
						$commEmailArray["PhoneEmailID"] = $existingIDs['COMMEML'];
				}
				array_push($callDynamically,$commEmailArray);
			}

		if(count($callDynamically) == 0){
			return ['status'=>'success','msg'=>'No changes required.','data'=>[]];
		}
		if(count($callDynamically)>0){

			$data = Array(

				"header" => Array(
								"isEnc" => "N",
								"apiVersion" => "10.1",
								"cifId" => "100212262",
								"languageId" => "1",
								"channelId" => "CUBE",
								"requestUUID" => $RequestUUID,
								"sVersion" => "20",
								"serReqId" => "RetCustMod",
								"timeStamp" => $timestamp
							),
				"request" => Array(
						"FIXML" => Array(
								"Header" => Array(
											"RequestHeader" => Array(
																"MessageKey" => Array(
																	"RequestUUID" => $RequestUUID,
																	"ServiceRequestId" => "RetCustMod",
																	"ServiceRequestVersion" => "10.2",
																	"ChannelId" => "COR",
																	"LanguageId" => ""

																),//End of MessageKey
																"RequestMessageInfo" => Array(
																						"BankId" => "01",
																				"TimeZone" => "",
																				"EntityId" => "",
																				"EntityType" => "",
																				"ArmCorrelationId" => "",
																				"MessageDateTime" => $current_timestamp,
																),//End of RequestMessageInfo
																"Security" => Array(
																		"Token" => Array(
																			"PasswordToken" => Array(
																								"UserId" => "",
																						"Password" => ""
																			)//End of PasswordToken

																		),//End of Token
																		"FICertToken" => "",
																"RealUserLoginSessionId" => "",
																"RealUser" => "",
																"RealUserPwd" => "",
																"SSOTransferToken" => ""

																)//End of Security

											)//End of RequestHeader

									),//End of Header

									"Body" => Array(
										"RetCustModRequest" => Array(
											"RetCustModRq" => Array(
												"RetCustModMainData" => Array(
													"CustModData" => Array(
														"CustId" => $customerId,
														"PhoneEmailModData" => $callDynamically
														
													)
												),
											)
										)
									)
							)
						)
				);

				$client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']);
				$requestTime = Carbon::now();
				$payload = json_encode(['gatewayRequest'=>$data]);
				
				$guzzleClient = $client->request('POST',$url,
													[   'headers' =>[
															'Content-Type'=>'application/json',
															'X-IBM-Client-secret'=>$client_key,
															'X-IBM-Client-Id'=>$client_id,
															'authorization'=>$authorization
														],
														'json'=>['gatewayRequest'=>$data],
														//'json'=>$payload,
														'exceptions'=>false
													]);
		
				$responseTime = Carbon::now()->diffInSeconds($requestTime); 
				$response = json_decode($guzzleClient->getBody(),true);
			
				$saveService = CommonFunctions::saveApiRequest('ETB_CUSTOMER_ID_UPDATE',$url,$payload,json_encode($response),json_encode($data),$response,$formId, $responseTime);
				if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess']){
					return ['status'=>'success','msg'=>'Customer Modification Successfully','data'=>[$response]];
				}else{
					return ['status'=>'fail','msg'=>'Customer Modification Failed!!!','data'=>[$response]];
				}
			}
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			//CommonFunctions::addExceptionLog($eMessage, $request);
			CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','EtbMobileEmailAdding',$eMessage,$crf_number,'');
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
    }

	public static function nomineeDeletionforModification($accountNo,$crf_number){
		try{
			$url = config('constants.APPLICATION_SETTINGS.NOMINEE_MODI'); 
			$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
			$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
			$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
			$encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');
	
			$current_timestamp =  Carbon::now()->timestamp;
			$requestTime = Carbon::now();
			$hash1 = hash('sha256', $current_timestamp);
			$hash2 = hash('md5',$current_timestamp);
			$RequestUUID = substr($hash1,0,8).'-'.substr($hash1,-4).'-'.substr($hash1, 10, 4).'-'.substr($hash2, 0, 4).'-'.substr($hash2, -12);
			$crfs = substr($crf_number,0,11);
			$getData =  DB::table('AMEND_QUEUE')->select('OLD_VALUE','FIELD_NAME')
												->where('CRF',$crf_number)
												->where('AMEND_ITEM','Nomination Modification')
												->pluck('old_value','field_name')
												->toArray();
			// echo "<pre>";print_r($getData);exit;
			$nomineeDob = \Carbon\Carbon::parse($getData['nomineeBirthDt'])->age;

		 	$relationCode =  DB::table('RELATIONSHIP')->select('CODE')
													->where('DISPLAY_DESCRIPTION',$getData['relType'])
													->get()
													->toArray();
			$relationCode =  (array) current($relationCode);

			$checkValidPin = AmendCommonFunctions::getpincodeData($getData['postalCode']);

			if(isset($getData['g_postalCode']) && $getData['g_postalCode'] != ''){

				$getGaurdianPin = AmendCommonFunctions::getpincodeData($getData['g_postalCode']);
			}

			if($nomineeDob < 18){
				$nomineeMinorFlag = "Y";
			}else{
				$nomineeMinorFlag = "N";
			}
			$nomineDob = Carbon::parse($getData['nomineeBirthDt'])->format('Y-m-d\TH:i:s.v');
				  $data = Array( 
					"header"=> Array( 
						"apiVersion" => "1.0.0.0",
						"appVersion" => "1.0.0.0",
						"channelId" => "CUBE",
						"cifId" => "102873457",
						"deviceId" => "af8f1289-bac1-3063-bd2f-d865a99a5d6e",
						"modelName" => "Motorola XT1022",
						"languageId"=> "1",
						"os" => "Android",
						"osVersion" => "5.1",
						"requestUUID" => $RequestUUID,	
						"serReqId" => "SBACCTMOD_V1",
						"sVersion" => "13",
						"timeStamp" => $current_timestamp,
						"isEnc" => "N"
					),
					"request" => Array
					(
						"sbAcctModRequest" => Array
							(
								"sbAcctModRq" => Array
									(
										"nomineeInfoRec" => Array
											(
												Array
													(
														"regNum" => $getData['regNum'],
														"nomineeName" => $getData['nomineeName'],
														"nomineeBirthDt" => $nomineDob,
														"relType" => $relationCode['code'],
														"nomineeContactInfo" => Array
															(
																"postAddr" => Array
																	(
																		"addr1" => $getData['addr1'],
																		"addr2" => $getData['addr2'],
																		"postalCode" => $getData['postalCode'],
																		"city" => $checkValidPin['citycode'],
																		"stateProv" => $checkValidPin['statecode'],
																		"country" => $checkValidPin['countrycode'],
																		"addrType" => 'Mailing'
																	)
	
															),
															"guardianInfo" => Array
															(
																"guardianName" => isset($getData['guardianName']) && $getData['guardianName'] != ''?$getData['guardianName']:'',
																"guardianContactInfo" => Array
																	(
																		"postAddr" => Array
																			(
																				"addr1" => isset($getData['g_addr1']) && $getData['g_addr1'] != ''?$getData['g_addr1']:'',
																				"addr2" => isset($getData['g_addr2']) && $getData['g_addr2'] != ''?$getData['g_addr2']:'',
																				"postalCode" => isset($getData['g_postalCode']) && $getData['g_postalCode'] != ''?$getData['g_postalCode']:'',
																				"city" => isset($getGaurdianPin['citycode']) && $getGaurdianPin['citycode'] != ''?$getGaurdianPin['citycode']:'',
																				"stateProv" => isset($getGaurdianPin['statecode']) && $getGaurdianPin['statecode'] != ''?$getGaurdianPin['statecode']:'',
																				"country" => isset($getGaurdianPin['countrycode']) && $getGaurdianPin['countrycode'] != ''?$getGaurdianPin['countrycode']:'',
																				"addrType" => 'Mailing',
																			)
																	)
																),
	
														"recDelFlg" => 'Y',
														"nomineePercent" => Array
															(
																"value" => '100.0',
															),
	
														"nomineeMinorFlg" => $nomineeMinorFlag,
													)
	
										),
	
										"sbAcctId" => Array
											(
												"acctId" => $accountNo,
											)
	
											),
	
								"sbAcctModCustomData" => Array
									(
										"nominee" => Array
											(
												 Array
													(
														"isMultiRec" => 'N',
														"delFlag" => 'Y',
													)
	
											)
	
									)
							)
					)
				 );
			$payload =  json_encode(['gatewayRequest'=>$data]);
			$client = new \GuzzleHttp\Client;
	
			$guzzleClient = $client->request('POST',$url,
												[ 'headers' => [
													'Content-Type' => 'application/json',
													'X-IBM-Client-secret' => $client_key,
													'X-IBM-Client-Id' => $client_id,
													'authorization' => $authorization
													],
	
													'json' => ['gatewayRequest' => $data],
													'exceptions' => false
												]);
	
			$response = json_decode($guzzleClient->getBody(),true);
			$responseTime = Carbon::now()->diffInSeconds($requestTime);
			$saveService = CommonFunctions::saveApiRequest('NOMINEE_MODIFICATION_DEL',$url,$data,$response,$payload,$response,$crf_number,$responseTime);
		
			if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess'] == 1){
					return json_encode(['status'=>'success','msg'=>'Successfully ACM data updated.','data'=>[]]);
			}else{
					return json_encode(['status'=>'fail','msg'=>'Api failed, Please try again later.','data'=>[]]);
		}
			}catch(\Throwable $e){
				if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
				$eMessage = $e->getMessage();
				CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','nomineeModification',$eMessage,$crf_number,'');
				return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}

	}

	public static function amendforPanDocDelete($crf_id, $customerID,$crf_number){
		try{
			$formId = $crf_id;
		 	$timestamp = Carbon::now()->timestamp;
		 	$current_timestamp = Carbon::parse($timestamp)->format('Y-m-d\TH:i:s.v');
		 	$RequestUUID = 'C'.CommonFunctions::getRandomValue(14);
		 	$header_timestamp = Carbon::now()->subDays(50)->format('dmY');

			$url = config('constants.APPLICATION_SETTINGS.AMEND_CUSTID');
			$client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
			$client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
			$authorization = config('constants.APPLICATION_SETTINGS.NEW_AUTHORIZATION');
			$encrypt_key = config('constants.APPLICATION_SETTINGS.ENCRYPT_KEY_1');		


			$getPandata = DB::table('AMEND_QUEUE')->select('OLD_VALUE')
												  ->where('CRF',$crf_number)
												  ->where('AMEND_ITEM','PAN Number Updation')
												  ->get()
												  ->toArray();
			$getPandata = (array) current($getPandata);
			$existingIDs = Self::retcustinq($customerID, $formId);
			$idArray = AmendApi::genEntityDocArray(7,$getPandata['old_value'],'');
			// echo "<pre>";print_r($idArray);exit;

			$aadharLinkStatus = DB::table('PAN_DETAILS')->select('AADHAARSEEDINGSTATUS')
                                                    ->where('PANNO',$getPandata['old_value'])
                                                    ->orderBy('id','DESC')
                                                    ->get()->toArray();
			$aadharLinkStatus = (array) current($aadharLinkStatus);
			if((isset($aadharLinkStatus['aadhaarseedingstatus']) && $aadharLinkStatus['aadhaarseedingstatus'] == "NA") || (isset($aadharLinkStatus['aadhaarseedingstatus']) && $aadharLinkStatus['aadhaarseedingstatus'] == "Y")){
				$pfDocCodeValue = 'RPAN'; 
			}else{
					$pfDocCodeValue = 'INPAN';
					}
					
					$idArray['DocCode'] = $pfDocCodeValue;
					
					// echo "<pre>";print_r($existingIDs);exit;
			if(isset($existingIDs['RPAN']) &&  $existingIDs['RPAN'] != ''){
				$idArray["EntityDocumentID"] = $existingIDs['RPAN'];
			}

			if(isset($existingIDs['INPAN']) &&  $existingIDs['INPAN'] != ''){
				$idArray["EntityDocumentID"] = $existingIDs['INPAN'];
			}
			$idArray['deleteFlag'] = 'Y';
			// echo "<pre>";print_r($idArray);exit;
				$data = Array(
					"header" => Array(
									"isEnc" => "N",
									"apiVersion" => "10.1",
									"cifId" => "100212262",
									"languageId" => "1",
									"channelId" => "CUBE",
									"requestUUID" => $RequestUUID,
									"sVersion" => "20",
									"serReqId" => "RetCustMod",
									"timeStamp" => $timestamp
								),
					"request" => Array(
							"FIXML" => Array(
									"Header" => Array(
												"RequestHeader" => Array(
																	"MessageKey" => Array(
	
																		"RequestUUID" => $RequestUUID,
																		"ServiceRequestId" => "RetCustMod",
																		"ServiceRequestVersion" => "10.2",
																		"ChannelId" => "COR",
																		"LanguageId" => ""
	
																),//End of MessageKey
																	"RequestMessageInfo" => Array(
																							"BankId" => "01",
																					"TimeZone" => "",
																					"EntityId" => "",
																					"EntityType" => "",
																					"ArmCorrelationId" => "",
																					"MessageDateTime" => $current_timestamp,
																	),//End of RequestMessageInfo
																	"Security" => Array(
																			"Token" => Array(
																				"PasswordToken" => Array(
																									"UserId" => "",
																								"Password" => ""
																				)//End of PasswordToken
	
																			),//End of Token
																			"FICertToken" => "",
																	"RealUserLoginSessionId" => "",
																	"RealUser" => "",
																	"RealUserPwd" => "",
																	"SSOTransferToken" => ""
	
																	)//End of Security
	
												)//End of RequestHeader
	
										),//End of Header
	
						"Body" => Array
							(
							"RetCustModRequest" => Array
								(
									"RetCustModRq" => Array
										(
											"RetCustModMainData" => Array
												(
													"CustModData" => Array
														(
															"CustId" => $customerID,
															"PAN" => $getPandata['old_value']
														)

												),

											"RetailCustModRelatedData" => Array
												(
													"EntityDocModData" =>
													 Array($idArray)

												)

										)

								)

						)
	
								)//End of FIXML
					)//End of request
				);
 
	 	// echo "<pre>";print_r(json_encode($data));
			$client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost']); //, 'debug' => true]);
			$requestTime = Carbon::now();
			$payload = json_encode(['gatewayRequest'=>$data]);
			$guzzleClient = $client->request('POST',$url,
												[   'headers' =>[
														'Content-Type'=>'application/json',
														'X-IBM-Client-secret'=>$client_key,
														'X-IBM-Client-Id'=>$client_id,
														'authorization'=>$authorization
													],
													'json'=>['gatewayRequest'=>$data],
													//'json'=>$payload,
													'exceptions'=>false
												]);
		 
		 $responseTime = Carbon::now()->diffInSeconds($requestTime); 
		 $response = json_decode($guzzleClient->getBody(),true);
		//  echo "<pre>";print_r(json_encode($response));exit;
		 $saveService = CommonFunctions::saveApiRequest('AMEND_KYC',$url,$payload,json_encode($response),json_encode($data),$response,$formId, $responseTime);
		 if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess']){
			 return json_encode(['status'=>'success','msg'=>'Customer Modification Successfully','data'=>[$response]]);
		 }else{
			 return json_encode(['status'=>'fail','msg'=>'Customer Modification Failed!!','data'=>[$response]]);
		 }
		 }catch(\Throwable $e){
			 if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			 $eMessage = $e->getMessage();
			 CommonFunctions::addLogicExceptionLog('Helpers/AmendApi','amendforPanDocDelete',$eMessage,$crf_number,'');
			 return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

}

?>
