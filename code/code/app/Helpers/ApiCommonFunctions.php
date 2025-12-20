<?php
namespace App\Helpers;

use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Helpers\CommonFunctions;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NPC\ReviewController;
use Session;
use Route;
use App\Helpers\Api;
use App\Helpers\AmendApi;
use App\Helpers\CurrentApi;
use App\Helpers\Rules;
use DB;
use File;


class ApiCommonFunctions {

	public static function insertIntoApiQueue($form_id,$class,$function,$module,$sequence,$applicant_no,$parameter,$next_run){
		$apiQueueData = DB::table('API_QUEUE')->where('API',$function)->where('FORM_ID',$form_id)->where('APPLICANT_NO',$applicant_no)->where('SEQUENCE',$sequence);
		
		if($function == 'signatureAccountWrapper'){
			$apiQueueData->where('API','signatureAccountWrapper');
		}

		if($apiQueueData->count() == 0){
			$insertApiQueue = [];
			$insertApiQueue['form_id'] = $form_id;
			$insertApiQueue['class'] = $class;
			$insertApiQueue['api'] = $function;
			$insertApiQueue['module'] = $module;
			$insertApiQueue['sequence'] = $sequence;
			$insertApiQueue['applicant_no'] = $applicant_no;
			$insertApiQueue['parameter'] = json_encode($parameter);
			$insertApiQueue['created_at'] = Carbon::now();
			$insertApiQueue['created_by'] = Session::get('userId');
			$insertApiQueue['next_run'] = $next_run;
			$insertApiQueue['retry'] = 0;
			$apiQueue = DB::table('API_QUEUE')->insert($insertApiQueue);
            DB::commit();
		}
	}

		// public static function excuteApiQueue(){
		// 	$getApiData = DB::table('API_QUEUE')->whereNull('STATUS')->get()->toArray();
		// 	$i = 0;
		// 	while($i <= count($getApiData)){
		// 		if(count($getApiData) >= 1){
		// 			$apiQueueData = $getApiData[$i];
		// 			$apiQueueData = (array) ($apiQueueData);
		// 			echo "<pre>";print_r($apiQueueData);
		// 			$className = app("App\\Helpers\\".$apiQueueData['class']);
		// 			$function = $apiQueueData['api'];
		// 			$parameter = json_decode($apiQueueData['parameter']);
		// 			array_push($parameter, $apiQueueData['id']);
		// 			array_push($parameter, $apiQueueData['form_id']);
		// 			$callFunction = call_user_func(array($className,$function), array($parameter));
		// 			if($callFunction['status'] == 'success'){
		// 				DB::table('API_QUEUE')->where('ID',$apiQueueData['id'])->where('FORM_ID',$apiQueueData['form_id'])->update(['STATUS' => 'Y','UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction)]);
		// 			}
					
		// 			if($i === array_key_last($getApiData)){
		// 				$i = 0;
		// 			}else{
		// 				$i++;
		// 			}

		// 		}
		// 	}
		// }

		public function createCustomerIdWrapper($param){
			$param = $param[0];
			$ovdId = $param[0];
			$checkStatus  = DB::table('CUSTOMER_OVD_DETAILS')->whereId($ovdId)->get()->toArray();
			$customerData = (array) current($checkStatus);
			if($customerData['customer_id'] != ''){
				return Self::buildwrapperArray('success','Customer Id Already Generated',$param);
			}

			$dilightCheck = DB::table('ACCOUNT_DETAILS')->whereId($customerData['form_id'])->where('DELIGHT_SCHEME','!=',null)
																						   ->where('DELIGHT_KIT_ID','!=',null)
																						   ->count();
			if($dilightCheck > 0){
				if($customerData['applicant_sequence'] == 1){
				$customerIdDetails = AmendApi::AmendCustApi($customerData,$customerData['form_id']);
			}else{
            $customerIdDetails = Api::createcustomerid($customerData);
			}
			}else{
            	$customerIdDetails = Api::createcustomerid($customerData);
			}
            if($customerIdDetails['status'] == "Success"){
                $customerID = $customerIdDetails['data'];
                $saveStatus = CommonFunctions::saveStatusDetails($customerData['form_id'],'22');
                $notif = NotificationController::processNotification($customerData['form_id'],'CUSTID_CREATED');

                $customNotif = CommonFunctions::processCustomerNotification($customerData['form_id'],'CUSTID_EMAIL',$customerData['applicant_sequence']);
                $msg = 'Customer Id Created Successfully';
				// $checkCustomerIdClear = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$customerData['form_id'])->whereNull('CUSTOMER_ID')->count();                
				// if($checkCustomerIdClear == 0){
				// 	$carbonTime = Carbon::now()->addMinutes(2);
				// 	if($customerData['initial_funding_type'] == '1'){ //cheque
				// 		$checkHour = Carbon::now()->format('H');
				// 		if($checkHour < 20){
				// 			$carbonTime = Carbon::parse('today 8pm');
				// 		}
				// 	}
                // 	Self::insertIntoApiQueue($customerData['form_id'],'ApiCommonFunctions','fundingStatusWrapper','Common',null,null,Array($customerData['form_id'],$customerData['initial_funding_type']),$carbonTime);
                // }

				return Self::buildwrapperArray('success',$msg,$customerIdDetails);
            }else{
            	$msg = 'Customer ID creation API failed.'.$customerIdDetails['data'].': '.$customerIdDetails['message'];
				return Self::buildwrapperArray('error',$msg,$customerIdDetails);
            }
		}

		public function internetBankingWrapper($param)
		{
			$param = $param[0];
			$checkStatus  = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_ID',$param[0])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			if($checkStatus['internet_banking'] == 'Y'){
				return Self::buildwrapperArray('success','Interent Banking Already Successfully Registered',$param);
			}

			$checkResponse = Api::internetBankRegister($param[0],$param[1]);
			// Self::ApiResponse('INTERNET_BANKING',$param[0],$param[1]);
			if($checkResponse == true){
				$updateStatus = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_ID',$param[0])->update(['INTERNET_BANKING' => 'Y']);
				return  Self::buildwrapperArray('success','Interent Banking Successfully Registered',$param);
			}else{
				return Self::buildwrapperArray('error',$checkResponse,$param);
			}
		}

		public function kycUpdateWrapper($param){
			$param = $param[0];
			$checkStatus  = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_ID',$param[1])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			// echo "<pre>";print_r($checkStatus);exit;
			if(isset($checkStatus['kyc_update']) &&  $checkStatus['kyc_update'] == 'Y'){
				return Self::buildwrapperArray('success','KYC Already Successfully Updated',$param);
			}

			$checkResponse = Api::kycUpdate($param[0],$param[1],$param[2]);
			if($checkResponse == 'Success'){
				$updateStatus = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_ID',$param[1])->update(['KYC_UPDATE' => 'Y']);
				 return Self::buildwrapperArray('success','KYC UPDATE Successfully Updated',$checkResponse);
			}else{
				return Self::buildwrapperArray('error',$checkResponse,$param);
			}

		}

		public function fundingStatusWrapper($param){
			$param = $param[0];
			$formId = $param[0];
			$initialFundingType = $param[1];
			// $reviewController = new ReviewController(); 		
        	$checkStatus = ReviewController::checkfundingstatus_func($formId,$initialFundingType);
	
        	if($checkStatus != ''){
        		$checkStatus = json_decode($checkStatus,true);
	        	if($checkStatus['status'] == 'success'){
                	Rules::postFundingCreateAccount($formId);
					 return Self::buildwrapperArray('success','Funding Status Successfully Updated',$checkStatus);
	        	}else{
					return Self::buildwrapperArray('error',$checkStatus['msg'],$param);
	        	}
	        }

		}

		public function signatureAccountWrapper($param){
			$param = $param[0];
			$checkStatus = DB::table('ACCOUNT_DETAILS')->where('ID',$param[0])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			$table = 'ACCOUNT_DETAILS';
			$column = 'SIGNATURE_FLAG';
			$where = 'ID';
			if($checkStatus['account_type'] == '3'){
				if($checkStatus['td_account_no'] == ''){
					return Self::buildwrapperArray('error','TD Account Id Not Yet Generated',$param);
				}
				$accountNo = $checkStatus['td_account_no'];
			}

			if($checkStatus['account_type'] == '1'){
				if($checkStatus['account_no'] == ''){
					return Self::buildwrapperArray('error','Account Id Not Yet Generated',$param);
				}
				$accountNo = $checkStatus['account_no'];
			}

			if($checkStatus['account_type'] == '2'){
				$entityDetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[0])->get()->toArray();
				$entityDetails = (array) current($entityDetails);
				if($entityDetails['entity_account_no'] == ''){
					return Self::buildwrapperArray('error','Account Id Not Yet Generated',$param);
				}
				$table = 'ENTITY_DETAILS';
				$column = 'ENTITY_SIGNATURE_FLAG';
				$where = 'FORM_ID';
				$accountNo = $entityDetails['entity_account_no'];
 			}


 			if($checkStatus['account_type'] == '4'){
 				if($checkStatus['account_no'] == '' && $checkStatus['td_account_no'] == ''){
					return Self::buildwrapperArray('error','Account Id Not Yet Generated',$param);
 				}

 				if($checkStatus['account_no'] != ''){
 					$accountNo = $checkStatus['account_no'];
 				}else{
 					$accountNo = $checkStatus['td_account_no'];
 				}
 			}

			$checkResponse = Api::uploadSignature($param[0],$accountNo);
			if($checkResponse['status'] == 'Success'){
				DB::table($table)->where($where,$param[0])->update([$column => 'Y']);
				return Self::buildwrapperArray('success','Signature uploaded successfully!',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Signature Api Failed',$checkResponse);
			}
		}

		public function NiyoAccountWrapper($param){

			$param = $param[0];			
			$accountopeningDate=$param[1];
		    $checkResponse = Api::accountOpeningNiyoCallback($param[0],$accountopeningDate);			
		
			if($checkResponse['status'] == 'success'){
				
				return Self::buildwrapperArray('success','Niyo Details Updated successfully!',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Niyo Api Failed',$checkResponse);
			}


		}
		
		public function savingAccountWrapper($param){
			$param = $param[0];
			$checkStatus = DB::table('ACCOUNT_DETAILS')->where('ID',$param[0])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			if($checkStatus['account_no'] != ''){
				return Self::buildwrapperArray('success','Saving Account Number Already Generated',$param);
			}
			if($checkStatus['scheme_code'] == '11'){
				$checkResponse = Api::createeliteaccountid($param[0]);
			}elseif($checkStatus['delight_kit_id'] != '' && $checkStatus['delight_scheme'] != ''){
				$checkResponse = AmendApi::AmendAccountApi($param[0],$param[1]);
			}else{
			$checkResponse = Api::createaccountid($param[0],$param[1]);
			}
			if(isset($checkResponse['status'])  && $checkResponse['status'] == 'Success'){
				if($checkStatus['account_type'] == 1 || $checkStatus['account_type'] == 4){
				 	$saveStatus = CommonFunctions::saveStatusDetails($param[0],'24','done by cron');
                    NotificationController::processNotification($param[0],'ACCOUNTNO_CREATED');
				}
				if($checkStatus['delight_kit_id'] != '' && $checkStatus['delight_scheme'] != ''){
					Self::insertIntoApiQueue($param[0],'freezeUnfreezeApi','unfreezeApi','Common',null,null,Array($checkResponse['data'],$param[0]),Carbon::now()->addMinutes(5));
				}
				return Self::buildwrapperArray('success','Saving Account Number Generated!',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Saving Account Number Api Failed',$checkResponse);
			}
		}

		public function currentAccountWrapper($param){
			$param = $param[0];

			$checkStatus = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[0])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			if($checkStatus['entity_account_no'] != ''){
				return Self::buildwrapperArray('success','Current Account Number Already Generated',$param);
			}

			$currentApiResponse = CurrentApi::CurrentAccountApi($param[0]);

			if(isset($currentApiResponse['status']) && $currentApiResponse['status']=='Success'){
                $currentApiId = $currentApiResponse['data'];
                $entitydetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[0])
                                                            ->update(['ENTITY_ACCOUNT_NO' => $currentApiId]);
				//for qcdashboard showing current account
				$savacctoaccountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$param[0])
																	->where('ACCOUNT_NO',null)
																	->update(['ACCOUNT_NO' => $currentApiId]);

                    NotificationController::processNotification($param[0],'ACCOUNTNO_CREATED');
                       $saveStatus = CommonFunctions::saveStatusDetails($param[0],'24', 'done by api queue');

                    Rules::postAccountIdApiQueue($param[0],2);
                    DB::commit();
					return Self::buildwrapperArray('success','Current Account Number Generated!',$currentApiResponse);
            }else{
				return Self::buildwrapperArray('Error','Current Account Number Api Failed',$currentApiResponse);
			}
		}

		// create term deposite account creator 

		public function termdepositeAccountWrapper($param){
			$param = (array) current($param);
			// echo "<pre>";print_r($param);exit;
			$formId = $param[0];
			$customerId = $param[1];

			$accountNumberCheck = DB::table('ACCOUNT_DETAILS')->select('TD_ACCOUNT_NO')
															  ->whereId($formId)
															  ->get()
															  ->toArray();

			$accountNumberCheck = (array) current($accountNumberCheck);
			
			if($accountNumberCheck['td_account_no'] != ''){
				return Self::buildwrapperArray('success','Term Deposite Account Number Already Generated',$param);
			}else{
				$tdAccountResponse = Api::createaccountnumber_tdapi($formId);
				if($tdAccountResponse['status'] != 'Error'){
					$updateAccountDetails = [
						'TD_ACCOUNT_NO'=>$tdAccountResponse['data'],
						'APPLICATION_STATUS'=> '14'
					   ];

					$saveComments = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update($updateAccountDetails); 
					
					NotificationController::processNotification($param[0],'ACCOUNTNO_CREATED');
					$saveStatus = CommonFunctions::saveStatusDetails($param[0],'24', 'done by api queue');
                    
                    Rules::postAccountIdApiQueue($formId,3);
					DB::commit();

					return Self::buildwrapperArray('success','TD Account Number Generated!',$tdAccountResponse);

				}else{
					return Self::buildwrapperArray('Error','TD Account Number Api Failed',$tdAccountResponse);
				}
			}
		}

		public function currentAccountNameWrapper($param){
			$param = $param[0];
			$checkStatus = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[2])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			if($checkStatus['entity_account_no'] == ''){
				return Self::buildwrapperArray('error','Current Account Number Not Yet Generated',$param);
			}

			$checkResponse = CurrentApi::currentAccountNameApi($param[0],$param[1],$param[2]);
            if (isset($checkResponse['status']) && $checkResponse['status'] == 'Success') {
				return Self::buildwrapperArray('success','Current Account Name Successfully Updated!',json_encode($checkResponse));
			}else{
				return Self::buildwrapperArray('Error','Current Account Number Api Failed',json_encode($checkResponse));
			}
		}

		

		public function currentAccountAddressWrapper($param){
			$param = $param[0];
			$checkStatus = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[0])->get()->toArray();
			$checkStatus = (array) current($checkStatus);
			if($checkStatus['entity_account_no'] == ''){
				return Self::buildwrapperArray('error','Current Account Number Not Yet Generated',$param);
			}

			$checkResponse = CurrentApi::currentAccountAddressApi($param[0],$param[1]);
            if (isset($checkResponse['status']) && $checkResponse['status'] == 'Success') {
				return Self::buildwrapperArray('success','Current Account Address Successfully Updated!',json_encode($checkResponse));
			}else{
				return Self::buildwrapperArray('Error','Current Account Address Api Failed',json_encode($checkResponse));
			}
		}


		public function repaymentTDAccountWrapper($param){
			$param = $param[0];
			$checkResponse = Api::repaymentTdApi($param[0],$param[1],$param[2],$param[3]);
			if($checkResponse ==  'Success'){
				return Self::buildwrapperArray('success','Repayment Success!',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Repayment Failed',$checkResponse);
			}
		}

		public function currentPreSweepWrapper($param){
			$param = $param[0];
			$customerId = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$param[0])->value('customer_id');
			$accountDetails = DB::table('ACCOUNT_DETAILS')->select('CONSTITUTION')->whereId($param[0])->get()->toArray();
			
			//huf flow only pass huf customer id 
			if(!empty($accountDetails)){
				$accountDetails = (array) current($accountDetails);
				if(strtoupper($accountDetails['constitution']) == 'NON_IND_HUF'){
					$customerId = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$param[0])->where('APPLICANT_SEQUENCE',2)->value('customer_id');
				}
			}

			$checkResponse = CurrentApi::preSweepApi($param[0],$customerId);

			if($checkResponse ==  'Success'){	
				$entityAccountNumber = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[0])->value('entity_account_no');
                Self::insertIntoApiQueue($param[0],'ApiCommonFunctions','currentSweepWrapper','Current',null,null,Array($param[0],$entityAccountNumber),Carbon::now()->addMinutes(10));
				return Self::buildwrapperArray('success','Current Account Pre Sweep Flag Updated',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Current Account Pre Sweep Api Failed',$checkResponse);
			}
			
		}

		public function currentSweepWrapper($param){
			$param = $param[0];
			$checkSAAccount = DB::table('ACCOUNT_DETAILS')->whereId($param[0])->value('ACCOUNT_NO');
			if($checkSAAccount == '' || $checkSAAccount == null){
				return Self::buildwrapperArray('Error','Saving Account Number not Created',null);
			}

			$checkResponse = CurrentApi::dbsaSweepAccount($param[0],$param[1],$checkSAAccount);
			if($checkResponse ==  'Success'){
				return Self::buildwrapperArray('success','Current Sweep Success!',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Current Sweep Failed',$checkResponse);
			}
		}

		public function currentGstValidation($param){
			$param = $param[0];
			$checkStatus = DB::table('ENTITY_DETAILS')->where('FORM_ID',$param[0])->value('ENTITY_ACCOUNT_NO');
			if($checkStatus == ''){
				return Self::buildwrapperArray('error','Current Account Number Not Yet Generated',$param);
		}

			$checkResponse = CurrentApi::dbsa_gstValidation($param[0],$param[1],$param[2]);
			if($checkResponse ==  'Current Gst Updated Successfully'){
				return Self::buildwrapperArray('success','Current Gst Added Successfully!',$checkResponse);
			}else{
				return Self::buildwrapperArray('Error','Current Gst Failed',$checkResponse);
			}
		}


		public function ftrStatusWrapper($param){
			$param = $param[0];
			$formId = $param[0];

			$checkFtrDone = self::preftrstatuscheck($formId);

			if($checkFtrDone == 'false'){
				$checkFtrCall = self::precheckftrbusinessCall($formId);
				if($checkFtrCall == 'false'){
					return Self::buildwrapperArray('error','Business flow not completed','');
				}else{
        			$checkStatus = ReviewController::checkFundTransfer($formId);
				$msg = 'FTR Successfully Updated';
				}
			}else{
				$checkStatus = ['status'=>'success','message'=>'FTR Already done','data'=>[]];	
				$msg = 'FTR Already done';		
			}

    		if($checkStatus['status'] == 'success'){
                Self::insertIntoApiQueue($formId,'ApiCommonFunctions','markFormForQcWrapper','Common',null,null,Array($formId),Carbon::now()->addMinutes(5));
				return Self::buildwrapperArray('success',$msg,$checkStatus);

    		}else{
				return Self::buildwrapperArray('error','FTR Failed',$checkStatus);
    		}
		}

		public function cardDetailsWrappper($param){
			$param = $param[0];
			$formId = $param[0];

			$cardApiResponse = Api::submitCardDetails($formId);
			if($cardApiResponse == 'Success'){
				$updateCardFlag = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['CARD_FLAG' => 'Y']);
				DB::commit();
				return Self::buildwrapperArray('success','Card Details Successfully Updated',$cardApiResponse);
			}else{
				return Self::buildwrapperArray('error','Card Details Failed',$cardApiResponse);
			}
		}

		public function markFormForQcWrapper($param){
			$param = $param[0]; $formId = $param[0];
			$accountDetails = DB::table('API_QUEUE')->where('FORM_ID',$formId)->whereNull('STATUS')->where('API','<>','markFormForQcWrapper')->where('API','!=','fundingStatusWrapper')->count();
			if($accountDetails == 0){
				// $reviewController = new ReviewController(); 		
	        	$checkStatus = ReviewController::markFormForQCInternal($formId);
	        	if(isset($checkStatus['status']) && $checkStatus['status'] == 'success'){
					return Self::buildwrapperArray('success','Form Marked for QC Successfully',null);
	            }else{
					return Self::buildwrapperArray('error','Form Marked for  QC Failed',null);
	            }
			}else{
				return Self::buildwrapperArray('error','Marking QC is not permitted at the moment',null);
			}
		}
		
		public function updateEtbCustomerId($param){
			$customerData = (array) current($param);
			$formId = $customerData[0];
			$customerId = $customerData[1];
			$checkstatus = AmendApi::EtbMobileEmailAdding($formId,$customerId);
			// echo "<pre>";print_r($checkstatus);exit;
			if($checkstatus['status'] == 'success'){
		
				return Self::buildwrapperArray('success',$checkstatus['msg'],$checkstatus);
			}else{
				return Self::buildwrapperArray('error','Customer modification failed.',null);
			}

		}

		public function prefundingCallWrapper($param){
			$formId = (array) current($param);
			$formId = $formId[0];

			if(env('APP_SETUP') == 'DEV'){
                $schema = 'NPC';
            }else{
                $schema = 'CUBE';
            }   
		
			$checktoCall = DB::select("SELECT COUNT(ID) AS count FROM {$schema}.API_QUEUE  WHERE STATUS IS NULL AND FORM_ID = '".$formId."' AND API != 'prefundingCallWrapper' AND API !='markFormForQcWrapper'");	
			// echo "<pre>";print_r($checktoCall);exit;	
			$checktoCall = (array) current($checktoCall);	
			if($checktoCall['count'] == 0){
				$checkStatus  = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
				$customerData = (array) current($checkStatus);
				$checkCustomerIdClear = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->whereNull('CUSTOMER_ID')->count();                
				if($checkCustomerIdClear == 0){
					$carbonTime = Carbon::now()->addMinutes(2);
					if($customerData['initial_funding_type'] == '1'){ //cheque
						$checkHour = Carbon::now()->format('H');
						if($checkHour < 20){
							$carbonTime = Carbon::parse('today 8pm');
						}
					}
					Self::insertIntoApiQueue($formId,'ApiCommonFunctions','fundingStatusWrapper','Common',null,null,Array($formId,$customerData['initial_funding_type']),$carbonTime);
					return Self::buildwrapperArray('success','Customer Id realted tasks completed.',null);
				}
			}else{
				return Self::buildwrapperArray('error','Customer Id realted tasks not completed.',null);
			}
		}

		public function ovdmaskCallWrapper($param){
		
			$requestData = (array)current($param);
			$formId = $requestData[0];
			$imageName = $requestData[1];
			$applicant_no = $requestData[2];
			$ovdType = $requestData[3];
			$folder = storage_path('/uploads/markedattachments/'.$formId);
			$imageHost = storage_path(config('constants.APPLICATION_SETTINGS.IMAGE_HOST'));
				try{                            
						$filepath=$folder.'/'.$imageName;
						$ch = curl_init($imageHost.'/maskpii');
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_AUTOREFERER, true);
						$cfile = new \CURLFile($filepath, mime_content_type($filepath), basename($filepath));
						$post_data = array('file' => $cfile);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
						$response = curl_exec($ch);

						if (curl_errno($ch)) {
						$response = curl_error($ch); 
					}
					
					curl_close($ch);
					$response = json_decode($response);
					if($response->pattern_found>0){
						$filepath1=$folder.'/'.$imageName.'.enc';
						File::move($filepath,$filepath1);
						$maskimagepath = $imageHost.'/'.$response->path;
						File::put($filepath,file_get_contents($maskimagepath));
						return Self::buildwrapperArray('success','Image mask successfully.',null);
					}else{
						return Self::buildwrapperArray('error','Image are not mask. Please Upload Valid Image.',null);
					}
				}catch(\Throwable $e){
					if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
				}catch(\Guzzle\Http\Exception\ConnectException $e){
					if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
				}
		}
		
		public function ovdNumbermaskCallWrapper($param){
			$requestData = (array) current($param);
			$formId = $requestData[0];
			$applicant_no = $requestData[1];

			$checkMaskingDone = DB::table('API_QUEUE')->where('STATUS',NULL)
													  ->where('FORM_ID',$formId)
													  ->where('API','ovdmaskCallWrapper')
													  ->where('APPLICANT_NO',$applicant_no)
													  ->get()->count();
		
			// echo "<pre>";print_r($checkMaskingDone);exit;
			if($checkMaskingDone == 0){
				// ID PROOF NUMBER 
				
				DB::table('CUSTOMER_OVD_DETAILS ')
									->where('FORM_ID', $formId)
									->where('PROOF_OF_IDENTITY', '1')
									->where('APPLICANT_SEQUENCE', $applicant_no)
									->update(['ID_PROOF_CARD_NUMBER' => DB::raw("CONCAT('XXXX-XXXX-',SUBSTR(ID_PROOF_CARD_NUMBER,-4))")]);
				//ADD PROOF NUMBER
				DB::table('CUSTOMER_OVD_DETAILS ')
									->where('FORM_ID', $formId)
									->where('PROOF_OF_ADDRESS', '1')
									->where('APPLICANT_SEQUENCE', $applicant_no)
									->update(['ADD_PROOF_CARD_NUMBER' => DB::raw("CONCAT('XXXX-XXXX-',SUBSTR(ADD_PROOF_CARD_NUMBER,-4))")]);

				return Self::buildwrapperArray('success','Number mask successfully.',null);
			}else{
				return Self::buildwrapperArray('error','Image mask are pending',null);
			}
		}

		public function preftrstatuscheck($formId){
			$ftrDone = 'false';
			$ftrCheck  = DB::table('FINCON')->where('FORM_ID',$formId)->where('FTR_STATUS','Y')->count();

			if($ftrCheck > 0){
				$ftrDone = 'true';
			}
			return $ftrDone;
		}

		public function buildwrapperArray($status,$message,$data){
			return ['status' => $status,'message' => $message,'data' => $data];
		}

		public function precheckftrbusinessCall($formId){
			$checkCall = 'true';
			$apiQueueChekc = DB::table('API_QUEUE')->where('FORM_ID',$formId)->where('API','unfreezeApi')->where('STATUS','Y')->get()->count();
			$checkDelight = DB::table('ACCOUNT_DETAILS')->whereId($formId)->where('DELIGHT_SCHEME','!=',null)->where('DELIGHT_KIT_ID','!=',null)->get()->count();
			if($apiQueueChekc == 0 && $checkDelight > 0){
				$checkCall = 'false';
			}
			return $checkCall;

		}
}


?>