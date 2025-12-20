<?php

namespace App\Http\Controllers\Amend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Helpers\CommonFunctions;
use App\Helpers\AmendCommonFunctions;
use App\Helpers\EncryptDecrypt;
use App\Helpers\AmendRules;
use App\Helpers\Api;
use App\Helpers\AmendApi;
use App\Helpers\CurrentApi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use File;
use DB;
use PDF;
use URL;
use Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
class AmendController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function checkamendcustomer(Request $request){
    	try{
	 		Session::put('currCustomerNo');
	    	return view('amend.amend');

	    }catch(\Illuminate\Database\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

    public function fetchdataforid(Request $request){

    	try{
	    	if($request->ajax()){

	    		$requestData = $request->get('data');
	    		$cust_acctNo = $requestData['cust_acctNo'];

				if($cust_acctNo == ''){
					return json_encode(['status'=>'fail','msg'=>'Invalid input. Please retry!','data'=>[]]);
				}
				//------------------amend session clear-------------------\\

				Self::clearAmendSession();
	    		$url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
		  		$getCustomerDetails = array();
		  		$getAccountDetails = array();
		  		$getAccountNo = '';
		  		Session::put('currAccountNo','');				
				Session::put('currAccountDetails',$getAccountDetails);
				Session::put('currCustomerDetails',$getCustomerDetails);
		  			
		  		if(strlen($cust_acctNo) > 9){
		  			$getAccountNo = $cust_acctNo;
		  			Session::put('currAccountNo',$getAccountNo);
		  			$getAccountDetails = Api::accountNumberDetails($cust_acctNo);
	           		if(!isset($getAccountDetails['accountDetails']['status']) || strtoupper($getAccountDetails['accountDetails']['status']) != 'SUCCESS')	 
	            	{
	  					return json_encode(['status'=>'fail','msg'=>'API Error! Please try again','data'=>[]]);

	            	}else{
		
						Session::put('currAccountDetails',$getAccountDetails);
					}			
		  			$cust_acctNo = $getAccountDetails['accountDetails']['CUSTOMER_ID'];
		  		}
		  		Session::put('currCustomerNo',$cust_acctNo);
					  		
		  		$getCustomerDetails = Api::customerdetails($url,'customerID',$cust_acctNo);
		  		// echo "<pre>";print_r(($getCustomerDetails));exit;
				if(isset($getCustomerDetails['data']['customerDetails']['CUST_NRE_FLG']) && $getCustomerDetails['data']['customerDetails']['CUST_NRE_FLG'] != 'N'){
					return json_encode(['status'=>'fail','msg'=>'Only resident Indian IDs permitted','data'=>[]]);
				}
				if(isset($getCustomerDetails['data']['customerDetails']['CUST_CONST']) && $getCustomerDetails['data']['customerDetails']['CUST_CONST'] != '001'){
					return json_encode(['status'=>'fail','msg'=>'Only individual (Constitution) IDs permitted','data'=>[]]);
				}
					
				if(isset($getCustomerDetails['data']['status']) && $getCustomerDetails['data']['status'] == 'fail'){
						return json_encode(['status'=>'fail','msg'=>'Api Error. Please try again later.','data'=>[]]);
				}
		 		
		 		if(isset($getCustomerDetails['data']['status']) && $getCustomerDetails['data']['status'] == 'FAILED'){

		 			$is_suspended = AmendCommonFunctions::isCustidSuspended($cust_acctNo);
					
					if($is_suspended == ''){
		 				return json_encode(['status'=>'fail','msg'=>'Please Enter valid Customer ID.','data'=>[]]);
		 			}
		 		}

		  		$getPincode = isset($getCustomerDetails['data']['customerDetails']['CUST_PERM_PIN_CODE']) && $getCustomerDetails['data']['customerDetails']['CUST_PERM_PIN_CODE'] != ''? $getCustomerDetails['data']['customerDetails']['CUST_PERM_PIN_CODE']:'';

		  		if($getPincode != ''){

			  		$getPincdetails = CommonFunctions::getAddressDataByPincode($getPincode);
			  		$getPincdetails = (array) current($getPincdetails);
			  		$getCustomerDetails['data']['customerDetails']['CUST_PERM_CITY_CODE'] = isset($getPincdetails['citydesc']) && $getPincdetails['citydesc']!=''?$getPincdetails['citydesc']:'';
	          		$getCustomerDetails['data']['customerDetails']['CUST_PERM_STATE_CODE'] = isset($getPincdetails['statedesc']) && $getPincdetails['statedesc'] !=''?$getPincdetails['statedesc']:'';
	                $getCustomerDetails['data']['customerDetails']['CUST_PERM_CNTRY_CODE'] = isset($getPincdetails['countrydesc']) && $getPincdetails['countrydesc'] !=''?$getPincdetails['countrydesc']:'';

		  		}

				$getPincode = isset($getCustomerDetails['data']['customerDetails']['CUST_COMU_PIN_CODE']) && $getCustomerDetails['data']['customerDetails']['CUST_COMU_PIN_CODE'] != ''? $getCustomerDetails['data']['customerDetails']['CUST_COMU_PIN_CODE']:'';

				if($getPincode != ''){

					$getPincdetails = CommonFunctions::getAddressDataByPincode($getPincode);
					$getPincdetails = (array) current($getPincdetails);
					$getCustomerDetails['data']['customerDetails']['CUST_COMU_CITY_CODE'] = isset($getPincdetails['citydesc']) && $getPincdetails['citydesc']!=''?$getPincdetails['citydesc']:'';
					$getCustomerDetails['data']['customerDetails']['CUST_COMU_STATE_CODE'] = isset($getPincdetails['statedesc']) && $getPincdetails['statedesc'] !=''?$getPincdetails['statedesc']:'';
				  $getCustomerDetails['data']['customerDetails']['CUST_COMU_CNTRY_CODE'] = isset($getPincdetails['countrydesc']) && $getPincdetails['countrydesc'] !=''?$getPincdetails['countrydesc']:'';

			  }
					Session::put('currCustomerDetails',$getCustomerDetails);
				$pagerNumber = isset($getCustomerDetails['data']['customerDetails']['CUST_PAGER_NO']) && $getCustomerDetails['data']['customerDetails']				['CUST_PAGER_NO'] != ''? $getCustomerDetails['data']['customerDetails']['CUST_PAGER_NO']:'';

				$chkblankMailId = isset($getCustomerDetails['data']['customerDetails']['EMAIL_ID']) && $getCustomerDetails['data']['customerDetails']				['EMAIL_ID'] != ''? $getCustomerDetails['data']['customerDetails']['EMAIL_ID']:'';

				return json_encode(['status'=>'success','msg'=>'Please wait while proccessing the data','data'=>['pagerNo'=>$pagerNumber,'chkblankMailId'=>$chkblankMailId]]);
				
	    	}
	    }catch(Illuminate\Exception\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

    public function getcustomeraccountdetails(Request $request){
    	try{

    	 	$tokenParams = explode('.',Cookie::get('token'));
	    	$decodeString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
			$getCustomerDetails = Session::get('currCustomerDetails');
			$getCustomerDetails = isset($getCustomerDetails['data']['customerDetails']) && $getCustomerDetails['data']['customerDetails'] !='' && $getCustomerDetails['data']['customerDetails'] != 'NULL'? $getCustomerDetails['data']['customerDetails'] : array();
			// echo "<pre>";print_r($getCustomerDetails);exit;
			$getAccountDetails = array();
			$getMinorData = '';
			$getEycData = '';
			$getCustRefnumber = '';
			if(count($getCustomerDetails)>0){

				$getAccountDetails = Session::get('currAccountDetails');

				if(isset($getAccountDetails['accountDetails']) && count($getAccountDetails['accountDetails'])>0){
					$getAccountDetails = $getAccountDetails['accountDetails'];
				}
				
		    	//minor to major check
		  		$minortomajor = $getCustomerDetails['CUST_MINOR_FLG'];
		  		$custDob = $getCustomerDetails['DATE_OF_BIRTH'];

		  		//ekyc check data 
		  		$ekycStatus = $getCustomerDetails['ISA_STATUS'];
		  		$ekycDate = $getCustomerDetails['ISA_UPDATE_DATE'];
		  		$ekycRisk = $getCustomerDetails['RISK_RATING'];

		  		//check minortomajor logic
		  		$getMinorData = AmendRules::checkMinorToMajor($minortomajor,$custDob);

		  		//ekyc check status logic
		  		$getEycData = AmendRules::checkekycStatus($ekycStatus,$ekycDate,$ekycRisk);

		  		//check aadhar reference number
		  		$getCustRefnumber = $getCustomerDetails['NAT_ID_CARD_NUM'];
			}

			return view('amend.amend')->with('getCustomerDetails',$getCustomerDetails)
					  ->with('getAccountDetails',$getAccountDetails)
					  // ->with('cust_acctNo',$cust_acctNo)
					  ->with('getMinorData',$getMinorData)
					  ->with('getEycData',$getEycData)
					  ->with('getCustRefnumber',$getCustRefnumber);

		}catch(Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);

		}

    }

    public function checkcustdataprocessing(Request $request){
    	try{
    		if($request->ajax()){

    			$requestData = $request->get('data');
    			$cust_acctNo = $requestData['cust_acctNo'];
    			$getekycStatus = $requestData['getekycStatus'];
    			$getminorStatus = $requestData['getminorStatus'];
	  			$getacctStatus = isset($requestData['accountStatus']) && $requestData['accountStatus'] !=''?$requestData['accountStatus']:'';
				$getCustomerDetails = Session::get('currCustomerDetails');
  				$ekyc_no = '';
		    	$eKYCData = '';
		    	$voltNoMatch = '';

    			if(isset($requestData['ekyc_number']) && $requestData['ekyc_number'] != ''){
    				$ekyc_no = $requestData['ekyc_number'];
    			}	
					

				$userEkycDetails = array(); 
				$getekycReference = '';
				
					
			    if($ekyc_no != ''){

					if(count($getCustomerDetails) <= 0){
						return json_encode(['status'=>'fail','msg'=>'Unable to proceed due to inadequate Customer details in Finacle.','data'=>[]]);
					}

					$voltNoMatch = 'N';
			    	$ekycData = array();
			    	$ekycData['referenceNumber'] = $ekyc_no;
			    	$ekycData['txnId'] = 'UKC:0002439120200615165105070';
			    	$ekycData['timeStamp'] = Carbon::now()->format('d/m/Y H:i:s a');
		            $eKYCData = Api::ekycDetails($ekycData,'');
		            if(isset($eKYCData['status']) && $eKYCData['status'] == 'Error'){
		            	return json_encode(['status'=>'fail','msg'=>$eKYCData['message'],'data'=>[]]);
		            }
					
				    $ekycDetails = $eKYCData['data']['response'];
			   		$getekycReference = $ekycDetails['refno'];

				    //userdetails
				    $userEkycDetails['CUST_NAME'] = $eKYCData['data']['response']['userDetails']['name'];
				    $userEkycDetails['DATE_OF_BIRTH'] = $eKYCData['data']['response']['userDetails']['dob'];
				    $userEkycDetails['CUST_SEX'] = $eKYCData['data']['response']['userDetails']['gender'];

				    //addresss data	
					$getvalidAddress = CommonFunctions::ekycaddsmartsplit($eKYCData['data']['response']['userAddress']);

					$userEkycDetails['CUST_PERM_ADDR1'] = $getvalidAddress['address_line_1'];
					$userEkycDetails['CUST_PERM_ADDR2'] = $getvalidAddress['address_line_2'];
					$userEkycDetails['CUST_PERM_ADDR3'] = $getvalidAddress['landmark'];
					// $userEkycDetails['CUST_PERM_ADDR1'] = $eKYCData['data']['response']['userAddress']['house'].' '.$eKYCData['data']['response']['userAddress']['locality'];
					// $userEkycDetails['CUST_PERM_ADDR1'] = trim($userEkycDetails['CUST_PERM_ADDR1']) != '' ? $userEkycDetails['CUST_PERM_ADDR1'] : '.';
					// $userEkycDetails['CUST_PERM_ADDR1'] = substr($userEkycDetails['CUST_PERM_ADDR1'], 0, 45);
				  
					// $userEkycDetails['CUST_PERM_ADDR2'] = $eKYCData['data']['response']['userAddress']['street'].' '.$eKYCData['data']['response']['userAddress']['village'];
					// $userEkycDetails['CUST_PERM_ADDR2'] = trim($userEkycDetails['CUST_PERM_ADDR2']) != '' ? $userEkycDetails['CUST_PERM_ADDR2'] : '.';
					// $userEkycDetails['CUST_PERM_ADDR2'] = substr($userEkycDetails['CUST_PERM_ADDR2'], 0, 45);

				    // $userEkycDetails['CUST_PERM_ADDR3'] = $eKYCData['data']['response']['userAddress']['landmark'];
					// $userEkycDetails['CUST_PERM_ADDR3'] = substr($userEkycDetails['CUST_PERM_ADDR3'], 0, 45);
					
					//27_02_2023 check kyc pincode with finacle pincode and upated state and city
					if($eKYCData['data']['response']['userAddress']['pincode'] != ''){
						$checkValidPin = DB::table('FIN_PCS_DESC')->select('CITYDESC','STATEDESC')
																  ->where('PINCODE',$eKYCData['data']['response']['userAddress']['pincode'])
																  ->get()
																  ->toArray();
						$checkValidPin = (array) current($checkValidPin);
						if(count($checkValidPin)>0){
							$userEkycDetails['CUST_PERM_CITY_CODE'] = $checkValidPin['citydesc'];
							$userEkycDetails['CUST_PERM_STATE_CODE'] = $checkValidPin['statedesc'];
						}else{
							return json_encode(['status'=>'fail','msg'=>'Pincode validation failed. Please try later.','data'=>[]]);
						}
					}else{
						return json_encode(['status'=>'fail','msg'=>'Pincode validation failed. Please try later.','data'=>[]]);
					}
				    $userEkycDetails['CUST_PERM_PIN_CODE'] = $eKYCData['data']['response']['userAddress']['pincode'];
				    $userEkycDetails['CUST_PERM_CNTRY_CODE'] = $eKYCData['data']['response']['userAddress']['country'];
					
				    if($eKYCData['data']['response']['userDetails']['gender'] == 'M'){
						$userEkycDetails['CUST_TITLE_CODE'] = '12';  //MR.
				    }else{
						$userEkycDetails['CUST_TITLE_CODE'] = '14';	//MS.
				    }


				    $referenceNumber = '';

	    			if(isset($requestData['getReferenceNo']) && $requestData['getReferenceNo'] != ''){
	    				$referenceNumber = $requestData['getReferenceNo'];
	    			}else{
	    				 $tmp = Session::get('currCustomerDetails');
	    				 if(isset($tmp['data']['customerDetails']['NAT_ID_CARD_NUM'])){
	    				 	$referenceNumber =  $tmp['data']['customerDetails']['NAT_ID_CARD_NUM'];
	    				 }
	    			}

				    if($referenceNumber == $getekycReference){
				    	$voltNoMatch = 'Y';
				    }else{
					 	// return json_encode(['status'=>'fail','msg'=>'Reference data mismatch with ekyc details','data'=>[]]);
				    }
			    }

			    Session::put('referenceNumber',$ekyc_no);
				Session::put('voltMatch',$voltNoMatch);			    	
			 	Session::put('userEkycDetails',$userEkycDetails);
			 	Session::put('getekycStatus',$getekycStatus);
			 	Session::put('getminorStatus',$getminorStatus);
			 	Session::put('getacctStatus',$getacctStatus);
				Session::put('getekycReference',$getekycReference);
				
				switch($voltNoMatch){

					case 'Y': $msg = 'Processing request..';
					break;

					case 'N': $msg = 'Aadhaar not updated in Finacle. Request will be submitted to NPC for processing..';
					break;

					default : $msg = 'Processing data..';
					break;
				}		 	
				
				if($voltNoMatch =='N' && $referenceNumber != ''){
					return json_encode(['status'=>'fail','msg'=>'Aadhaar Vault number mismatch','data'=>[]]);
				}else{
			 		return json_encode(['status'=>'success','msg'=>$msg,'data'=>['cust_acctNo'=>$cust_acctNo, 'getekycStatus'=>$getekycStatus,'getminorStatus'=>$getminorStatus,'match'=>$voltNoMatch,'getacctStatus'=>$getacctStatus]]);
				}
				
    		}

    	}catch(Illuminate\Exception\QueryException $e){
    		if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);

    	}
    }

    public function fetchdataperselectedid(Request $request){
    	try{
    		$tokenParams = explode('.',Cookie::get('token'));
	    	$decodeString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
	    	$getdataDetails = base64_decode($decodeString);
	    	$getdataDetails = json_decode($getdataDetails,true);
	    	$cust_acctNo = Session::get('currCustomerNo');
	    	$getAccountNo = Session::get('currAccountNo');
	    	$voltMatch = Session::get('voltMatch');
			$currCustomerDetails = Session::get('currCustomerDetails');
			$currAccountDetails = Session::get('currAccountDetails');
			$getcustName = isset($currCustomerDetails['data']['customerDetails']['CUST_NAME']) && $currCustomerDetails['data']['customerDetails']['CUST_NAME'] != '' ?
							$currCustomerDetails['data']['customerDetails']['CUST_NAME'] :'';

			$ekycStatus =  isset($currCustomerDetails['data']['customerDetails']['ISA_STATUS']) && $currCustomerDetails['data']['customerDetails']['ISA_STATUS'] != ''? $currCustomerDetails['data']['customerDetails']['ISA_STATUS'] : '';
			$ekycDate =  isset($currCustomerDetails['data']['customerDetails']['ISA_UPDATE_DATE']) && $currCustomerDetails['data']['customerDetails']['ISA_UPDATE_DATE'] != ''? $currCustomerDetails['data']['customerDetails']['ISA_UPDATE_DATE'] : '';
			$ekycRisk =  isset($currCustomerDetails['data']['customerDetails']['RISK_RATING']) && $currCustomerDetails['data']['customerDetails']['RISK_RATING'] != ''? $currCustomerDetails['data']['customerDetails']['RISK_RATING'] : '';

			//ekyc check status logic
			$getEycData = AmendRules::checkekycStatus($ekycStatus,$ekycDate,$ekycRisk);
			// echo "<pre>";print_r($getVkycStatus);exit;
			$getValue =  AmendRules::getValidAmendItem($currCustomerDetails,$currAccountDetails);

	    	return view('amend.amendcustaccdata')->with('getValue',$getValue)
	    										 ->with('cust_acctNo',$cust_acctNo)
	    										 ->with('voltMatch',$voltMatch)
	    										 ->with('getVkycStatus',$ekycStatus)
	    										 ->with('getAccountNo',$getAccountNo)
												 ->with('currCustomerDetails',$currCustomerDetails)
												 ->with('getcustName',$getcustName)
												 ->with('getEycData',$getEycData);
	    								

	    	}catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
    
    public function ameditemselected(Request $request){
	    try{
	    	if($request->ajax())
	    	{
	  			$requestData = $request->get('data');
		  		$cust_acctNo = $requestData['getcust_acctNo'];
		  		$accountNo = isset($requestData['accountNumber']) && $requestData['accountNumber'] !=''?$requestData['accountNumber']:'';

		  		// echo "<pre>";print_r($requestData['selectedItem']);exit;
	  			$url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
	  			$responseArray = array();
				$getSelectedData = [];
				if(isset($requestData['selectedItem']) && $requestData['selectedItem'] != '')
				{
					$getSelectedData = DB::table('AMENDITEMS')->select('ID','DESCRIPTION','FINACLE_FIELD','TYPE1','TYPE2','EVIDENCE_ID','AMEND_API_FIELD','CLASSES')
															  ->whereIn('ID',$requestData['selectedItem'])
															  ->orderBy('SEQUENCE','ASC')
															  ->get()
															  ->toArray();
				}

				$currCustomerDetails = Session::get('currCustomerDetails');
	
				if(count($getSelectedData)>0){

					$getcustomersDetails = array();
					$getaccountsDetails = array();
					for($type = 0;$type < count($getSelectedData);$type++){
						$amednId = $getSelectedData[$type]->id;

						$evidenceId = explode(',',$getSelectedData[$type]->evidence_id);
						$amendEvidence = DB::table('AMEND_EVIDENCE')->select('ID', 'EVIDENCE','MANDATORY')
																	->where(function($query) use ($evidenceId){
																		$query->whereIn('ID',$evidenceId)
																		->orWhere('EVIDENCE','like','%'.'Other Documents'.'%');
																	})
																	->where('ACTIVE_STATUS','Y')
																	->get()
																	->toArray();
						$evidenceData[$amednId] = $amendEvidence;

						$finacleField = $getSelectedData[$type]->finacle_field;
						$amendApiField = $getSelectedData[$type]->amend_api_field;
						
						$amendId = $getSelectedData[$type]->id;

						if($getSelectedData[$type]->type2 == 'CRM')
						{
							$getcustomersDetails[$amednId] = self::customerDetails($url,$cust_acctNo,$finacleField,$amednId,$amendApiField);
							$getSelectedData[$type]->custData = $getcustomersDetails[$amednId];
								
								if($getcustomersDetails[$amednId] == ''){
									return json_encode(['status'=>'fail','msg'=>'Unable to fetch data','data'=>[]]);
								}
							}else{
								
							$getaccountsDetails[$amednId] = self::accountDetails($cust_acctNo,$finacleField,$amendApiField,$amendId,$accountNo);
							$getSelectedData[$type]->acctData = $getaccountsDetails[$amednId];

							if($getaccountsDetails[$amednId]  == ''){
								return json_encode(['status'=>'fail','msg'=>'Unable to fetch data','data'=>[]]);
							}
						}

					}
					
					$status = AmendRules::amendValidationItems($getSelectedData);
					
					if($status['status'] == 'false'){
						return json_encode(['status'=>'fail','msg'=>$status['msg'],'data'=>[]]);
					}
					
					$uniqEvidence = array();
					
					foreach($evidenceData as $key => $value){
						foreach($value as $item => $data){
						
							$uniqEvidence[$data->id] = ['evidence' => $data->evidence ,
														'mandatory' =>$data->mandatory];
						}
					}
					ksort($uniqEvidence);
					// echo "<pre>";print_r($uniqEvidence);exit; 27_03_2023
					$currentAcc = true;
					if($currentAcc){
						unset($uniqEvidence['38']);
					}
					Session::put('uniqEvidence',$uniqEvidence);
					Session::put('currAllData', $getSelectedData);
					return json_encode(['status'=>'success','msg'=>'Item(s) selected successfully','data'=>[]]);
				}else{
					return json_encode(['status'=>'fail','msg'=>'Select one list of Item','data'=>[]]);
				}
	  		}
	  	}catch(\Illuminate\Exception\QueryException $e){
	  		if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
	  		return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	  	}
    }

    public function amendinput(Request $request){
    	try{
    		$tokenParams = explode('.',Cookie::get('token'));
	    	$decodeString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);								
	    	$ovd_check = '';
			$crfNumber = Session::get('crfNumber');
			$getEvidenceData = array();
			$scenario = 'manual';
			$crfId = '';
			$getCurrentyear = '';
			$additionalData = '';
			$customerReqId = '';
			$checkTag = Session::get('voltMatch');

			if($checkTag != ''){
				$scenario = 'ekyc';
			}

	    	if($crfNumber == ''){

				$getCustomerDetailsArray = Session::get('currAllData');		
				$eKYCDetails = Session::get('userEkycDetails');		
				$evidenceUniqueList = Session::get('uniqEvidence');		

			}else{
				$masterData = DB::table('AMEND_MASTER')->where('CRF_NUMBER', $crfNumber)
														->get()
														->toArray();

				$additionalData = json_decode($masterData[0]->additional_data);

				if($masterData[0]->volt_match_flag != ''){
					$scenario = 'ekyc';
				}

				$crfId = substr($masterData[0]->id,-1);
				$customerReqId = $masterData[0]->id;
				$getCurrentyear = Carbon::now()->year;
				$masterData = json_decode(base64_decode($masterData[0]->cache_data), true);				
				$ovd_check = $masterData['ovdCheck'];
				$savedData =  $masterData['cleanArray']; 
				$eKYCDetails = array();		
				$evidenceUniqueList = $masterData['uniqEvidence']; 
				$getEvidenceData = $masterData['imageData']; 
				if($getEvidenceData == ''){
					$getEvidenceData = array();
				}
			}	
	    		    
	    	$idProofList = CommonFunctions::getOVDList('ID_PROOF');
	    	unset($idProofList[9]);

	    	$addComuProofList = CommonFunctions::getOVDList('CUR_ADDRESS_PROOF');
			$voltMatch = Session::get('voltMatch');
			if($voltMatch == ''){
				unset($addComuProofList[29]);
			}


	    	$validationData = config('amendvalidation');
			$cleanArray = array();
			$counter = 0;
		
	    	if($crfNumber == ''){

				for($ctr=0; $ctr<count($getCustomerDetailsArray); $ctr++){
					$currData = (object) $getCustomerDetailsArray[$ctr];					
					
					if($currData->type2 == 'CRM' && isset($currData->custData)){
						$arrayToProcess = $currData->custData;
					}else{
						$arrayToProcess = $currData->acctData;
					}	
					foreach($arrayToProcess as $key => $value){	
						// echo "<pre>";print_r($value);
						$cleanArray[$counter]['id'] = $currData->id;
						$cleanArray[$counter]['counter'] = $counter;
						$cleanArray[$counter]['type2'] = $currData->type2;
						$cleanArray[$counter]['type1'] = $currData->type1;
						$cleanArray[$counter]['evidence_id'] = $currData->evidence_id;
						$cleanArray[$counter]['description'] = $currData->description;	
						$cleanArray[$counter]['accNo'] = $currData->type2 == 'CRM' ? '': $value['accountNo'];
						$cleanArray[$counter]['fieldName'] = isset($value['finacleField']) && $value['finacleField']!=''?$value['finacleField']:'';

						$cleanArray[$counter]['apiCall'] = isset($currData->classes) && $currData->classes != ''? $currData->classes : '';
						// $cleanArray[$counter]['fieldName'] = $value['finacleField'];
						//--------------amendField check------------\\
						$cleanArray[$counter]['amendField'] = isset($value['amendField'])&& $value['amendField'] != ''? $value['amendField']:'';
						$validations = isset($validationData[$value['finacleField']]) ? $validationData[$value['finacleField']] : '';
						if($validations!=''){
							$cleanArray[$counter]['fieldClass'] = isset($validations['class']) && $validations['class'] !=''?$validations['class'] :'';
							$cleanArray[$counter]['fieldDiv'] = isset($validations['div']) && $validations['div'] != '' ? $validations['div'] : '';
							$cleanArray[$counter]['fieldFunction'] = isset($validations['function']) ? $validations['function'] : '';
							$cleanArray[$counter]['display'] = isset($validations['display']) ? $validations['display'] : '';
							$cleanArray[$counter]['input_type'] = isset($validations['input_type']) ? $validations['input_type'] : '';
							$cleanArray[$counter]['placeholder'] = isset($validations['placeholder']) ? $validations['placeholder'] : '';
							$cleanArray[$counter]['required'] = isset($validations['required']) ? $validations['required'] : 'Y';
							$cleanArray[$counter]['saveBtn'] = isset($validations['savebtn']) ? $validations['savebtn'] : 'Y';
							$cleanArray[$counter]['visibility'] = isset($validations['visibility']) && $validations['visibility'] != ''? $validations['visibility'] : '';
							

						}else{
							$cleanArray[$counter]['fieldClass'] = '';
							$cleanArray[$counter]['fieldDiv'] = '';
							$cleanArray[$counter]['fieldFunction'] = '';
							$cleanArray[$counter]['display'] = '';
							$cleanArray[$counter]['input_type'] = '';
							$cleanArray[$counter]['placeholder'] = '';
							$cleanArray[$counter]['required'] = 'Y';
							$cleanArray[$counter]['saveBtn'] = '';	
							$cleanArray[$counter]['visibility'] = '';

						}						
						
						$cleanArray[$counter]['ekycdata'] = isset($eKYCDetails[$value['finacleField']]) ? $eKYCDetails[$value['finacleField']] : '';
				
						if($scenario == 'ekyc' && $cleanArray[$counter]['ekycdata'] == '' && in_array($cleanArray[$counter]['fieldName'],['CUST_PERM_ADDR3','CUST_PERM_ADDR2','CUST_COMU_ADDR2','CUST_COMU_ADDR3'])){
							$cleanArray[$counter]['required'] = 'N';
						}

						if(in_array($currData->id,[29,30,26,33,50])){
							$cleanArray[$counter]['newValue'] = isset($value['newValue']) && $value['newValue'] != ''?$value['newValue']:'';
						}
						
						if($currData->id == 6){
							$cleanArray[$counter++]['oldValue'] = date('d-m-Y',strtotime(substr($value['oldValue'],0,10)));

						}else{

							$cleanArray[$counter++]['oldValue'] = $value['oldValue'];
						}
					}	
				}
				
			}else{
				$cleanArray = $savedData;
				
				//---------------ACM level checkbox----------------\\

				
				//-----------------Account number check preselected checkbox------------------\\


				for($clnSeq=0;count($cleanArray)>$clnSeq;$clnSeq++){

					$amemdQueue = DB::table('AMEND_QUEUE')->select('ACCOUNT_NO')
														  ->where('ACCOUNT_NO',$cleanArray[$clnSeq]['accNo'])
														  ->where('CRF',$crfNumber)
														  ->where('SOFT_DEL','N')
														  ->get()
														  ->toArray();

					if(count($amemdQueue)>0){
						$cleanArray[$clnSeq]['accNoChecked'] = 'Y';
					}else{
						unset($cleanArray[$clnSeq]['accNoChecked']);
					}
				}
			}


			foreach($evidenceUniqueList as $key => $value){

				if(isset($getEvidenceData[$key]) && $getEvidenceData[$key] != ''){

					$namePath = $getCurrentyear.'/'.$crfId.'/'.$customerReqId.'/'.$getEvidenceData[$key];
					$storagePath = storage_path('/uploads/amend/'.$namePath);
					if(File::exists($storagePath)){
						$evidenceUniqueList[$key]['storageExist'] = 'Y';
					}else{
						$evidenceUniqueList[$key]['storageExist'] = 'N';

					}
				}
				$otherDoc = substr($value['evidence'],0,5);

				if(strtolower($otherDoc) == 'other'){

					$evidenceUniqueList[$key]['other'] = 'Y';
				}else{

					$evidenceUniqueList[$key]['other'] = 'N';
				}
			}

			// $dropdownData = array();
			$currAccountDetails = Session::get('currAccountDetails');
			$currCustomerDetails = Session::get('currCustomerDetails');
			$dropdownData = AmendRules::setDropdownData($cleanArray,$currAccountDetails,$currCustomerDetails);
			$cleanArray =  AmendRules::setcodeName($cleanArray);

			
			Session::put('cleanArray',$cleanArray);

			//--------------hide and show field name--------------\\

			switch(env('APP_SETUP')){
				case 'DEV':
					$html = 'hide';
				break;
				case 'UAT':
					$html = 'hide';
				break;
				case 'PROD':
					$html = 'hide';
				break;
				default:
					$html = 'hide';
				break;
			}

			$currentDate = Carbon::now()->format('d-m-Y');

			return view('amend.amenduploaddocument_v2')->with('cleanArray',$cleanArray)
													->with('eKYCDetails',$eKYCDetails)
													->with('evidenceUniqueList',$evidenceUniqueList)
													->with('html',$html)
													->with('validation',$validationData)
													->with('dropdownData',$dropdownData)
													->with('idProofList',$idProofList)
													->with('additionalData',$additionalData)
													->with('getEvidenceData',$getEvidenceData)
													->with('scenario', $scenario)
													->with('getCurrentyear',$getCurrentyear)
													->with('crfId',$crfId)
													->with('ovd_check',$ovd_check)
													->with('addComuProofList',$addComuProofList)
													->with('customerReqId',$customerReqId)
													->with('currentDate',$currentDate);
											
		}catch(Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
    }

									  

	public function getdetailspincodeselected(Request $request){
		try{

			$requestData = $request->get('data');

			$pinId = $requestData['id'];
			$pincode = $requestData['pincodeData'];

			$descriptionPinDetails = array();

			$getPinDetails = DB::table('FIN_PCS_DESC')->select('CITYDESC','STATEDESC','COUNTRYDESC')
													  ->where('PINCODE',$pincode)
													  ->get()->toArray();

			if(count($getPinDetails) <= 0){
				return json_encode(['status'=>'fail','msg'=>'Data not found !!','data'=>[$pinId]]);
			}

			$getPinDetails = (array) current($getPinDetails);

			return json_encode(['status'=>'success','msg'=>'Successfully selected data.','data'=>[$pinId,$getPinDetails]]);

		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){ dd($e->getMessage()); }
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
			
		}		
	}

	public function accountDetails($custID,$finacleFieldData,$amendApiField,$amendId,$accountNo = ''){
		try{
			if($accountNo == ''){
				return false;
			}	
			$getList = array();

			if($finacleFieldData != ''){

				$finacleField = explode('|',$finacleFieldData);
				$amendApiField = explode('|',$amendApiField);
				
				for($i=0;count($finacleField)>$i;$i++){

						if($amendId == 30){
							$getNomineeDetails = self::SBAccountInq($accountNo);
							$getAccountDetails = $getNomineeDetails;
							if(count($getAccountDetails) > 0){
							
								$getList[$i]['newValue'] = (isset($getAccountDetails['accountDetails'][$finacleField[$i]]) && $getAccountDetails['accountDetails'][$finacleField[$i]] != '') ? $getAccountDetails['accountDetails'][$finacleField[$i]] : '';
							}else{
								return false;
							}
						}

						if($amendId == 29){
							$getNomineeDetails = self::SBAccountInq($accountNo);
							$getAccountDetails = $getNomineeDetails;
							if(count($getAccountDetails) > 0){
								
								$getList[$i]['newValue'] = (isset($getAccountDetails['accountDetails'][$finacleField[$i]]) && $getAccountDetails['accountDetails'][$finacleField[$i]] != '') ? $getAccountDetails['accountDetails'][$finacleField[$i]] : '';
							}else{
								return false;
							}
						}
						

						if($amendId == 28){
							$getNomineeDetails = self::SBAccountInq($accountNo);
							$getAccountDetails = $getNomineeDetails;
							if(count($getAccountDetails) != 0){
								return false;
							}
						}

						if(!in_array($amendId,[29,30])){
							$getAccountDetails = Api::accountNumberDetails($accountNo);

				  			if($getAccountDetails['accountDetails']['JOINTHOLDERS_CUSTID'] != '-'){

				  				$getcustid = explode('|',$getAccountDetails['accountDetails']['JOINTHOLDERS_CUSTID']);

				  				for($seqId=0;count($getcustid)>$seqId;$seqId++){
				  					if($getcustid[$seqId] != ''){
				  						$getAccountDetails['accountDetails']['JOINTHOLDERS_CUSTID'] = [$getcustid[$seqId]];
									}
				  				}
				  			}
						}
						// dormant account activation
						if($amendId == 23){

							$schemeData = $getAccountDetails['accountDetails']['SCHM_CODE'];
							$schemeType = substr($schemeData,0,2);
							
							if($schemeType == 'SB' || $schemeType == 'CA'){
							}else{

								return false;
							}
							
						}
						$oldValue = (isset($getAccountDetails['accountDetails'][$finacleField[$i]]) && $getAccountDetails['accountDetails'][$finacleField[$i]] != '') ? $getAccountDetails['accountDetails'][$finacleField[$i]]: '';
				
						if($amendId == 50){
							if($finacleField[$i] == 'JOINTHOLDERS_CUSTID'){
								$getjoinHolder = $getAccountDetails['accountDetails'][$finacleField[$i]];

								if(is_array($getjoinHolder)){

									for($seqj=0;count($getjoinHolder)>$seqj;$seqj++){

										$oldValue = $getjoinHolder[$seqj];
										$getList[$i]['newValue'] = $oldValue;
									}
								}
							}
						}

						$amendField = isset($amendApiField[$i]) && $amendApiField[$i] ? $amendApiField[$i] : '';

						if(in_array($amendId,[26])){
							$getList[$i]['newValue'] = $oldValue;
						}

						$getList[$i]['accountNo'] = $accountNo;
						$getList[$i]['oldValue'] = $oldValue;
						$getList[$i]['finacleField'] = $finacleField[$i];
						$getList[$i]['amendField'] = $amendField;
					}
					// echo "<pre>";print_r($getList);exit;
				return $getList;
			}else{
				$getList[]['accountNo'] = $accountNo;
				$getList[]['oldValue'] = '';
				$getList[]['finacleField'] = '';
				$getList[]['amendField'] = '';
				$getList[]['newValue'] = '';

				return $getList;
			}
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public function customerDetails($url,$cust_acctNo,$finacleField,$amednId,$amendApiField){
		try{
			$custDetails = Session::get('currCustomerDetails');
			// echo "<pre>";print_r($custDetails);exit;
			$custlist = array();
			if(count($custDetails)>0){
				if(isset($custDetails['data']['customerDetails'])){

					if($finacleField != ""){
						$finacleField = explode('|',$finacleField);
						$amendApiField = explode('|',$amendApiField);

						foreach($finacleField as $key => $value){

							if($amednId == 33){
								$custlist[$key]['newValue'] = isset($custDetails['data']['customerDetails'][$value]) ? $custDetails['data']['customerDetails'][$value] : '';
							}
							
							$custlist[$key]['oldValue'] = isset($custDetails['data']['customerDetails'][$value]) ? $custDetails['data']['customerDetails'][$value] : '';
							$custlist[$key]['finacleField'] = $value;
						}

						foreach($amendApiField as $key2 => $value2){

							$custlist[$key2]['amendField'] = $value2;
						}
					}else{
						$custlist[$amednId]['oldValue'] = "";
						$custlist[$amednId]['finacleField'] = "" ;
						$custlist[$amednId]['amendField'] = "";
					}
					return $custlist;
				}else{
					
					return false;
				}
			}else{
				
				// return false;
				$custlist[$amednId]['oldValue'] = "";
				$custlist[$amednId]['finacleField'] = $finacleField ;
				$custlist[$amednId]['amendField'] = $amendApiField;

				return $custlist;
			}
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}
	
	public function insertnewdata(Request $request){
		try{
			if($request->ajax()){
				$requestData = $request->get('data');
				$tokenParams = explode('.',Cookie::get('token'));
				$tokenKey = $tokenParams[2];
				if(isset($requestData['ekycAddData']['id_code']) && $requestData['ekycAddData']['id_code'] != ''){

					$requestData['ekycAddData']['id_code'] = CommonFunctions::decryptRS($requestData['ekycAddData']['id_code'],$tokenKey);
				}
				$ovdCheck = '';	

				if(isset($requestData['ovdCheck']) && $requestData['ovdCheck'] != ''){

					$ovdCheck = $requestData['ovdCheck'];
				}

				$amendCrfData = Session::get('cleanArray');

				if(!AmendRules::isMOPokay($requestData)){
					return json_encode(['status'=>'fail','msg'=>'Incorrect MOP. Please check','data'=>[]]);
				}

				
				$crf_numberData = Session::get('crfNumber');
				$amendData  = isset($requestData['amendNewData']) && $requestData['amendNewData'] != ''?$requestData['amendNewData']:'';

				$inputFieldCheck = (isset($requestData['inputFieldCheck']) && $requestData['inputFieldCheck'] != '') ? $requestData['inputFieldCheck'] : '';

				for($amdi=0;$amdi<count($amendCrfData);$amdi++){

					if(isset($requestData['inputFieldCheck'][$amendCrfData[$amdi]['id']]) && $requestData['inputFieldCheck'][$amendCrfData[$amdi]['id']] == 'Y'){

						$searchKey = 'input_'.$amendCrfData[$amdi]['id'].'_'.$amendCrfData[$amdi]['counter'];
						$amednId  = explode('_',$searchKey)[1];
						if($amednId == 32){
							$requestData['amendNewData'][$searchKey] = CommonFunctions::decryptRS($requestData['amendNewData'][$searchKey],$tokenKey);
						}
					}
				}
				$getValidData = AmendRules::checkFieldValidation($amendData,$amendCrfData,$inputFieldCheck,$tokenKey);
				// echo "<pre>";print_r($getValidData);exit;

				if(isset($getValidData['status']) && $getValidData['status'] == 'fail'){
					return json_encode(['status'=>$getValidData['status'],'msg'=>$getValidData['message'],'data'=>[]]);
				}

				if($amendData == ''){
					return json_encode(['status'=>'fail','msg'=>'Please insert all data','data'=>[]]);
				}

				ksort($amendData);
				
				// $amendCrfData = Session::get('cleanArray');
				$voltFlagCheck = Session::get('voltMatch');

				$imageData = array();
				$ekycAddData = '';
				$additionalData = '';
				$validationCheck = 'Y';

				//--------------Process input data--------------\\

				// $inputFieldCheck = (isset($requestData['inputFieldCheck']) && $requestData['inputFieldCheck'] != '') ? $requestData['inputFieldCheck'] : '';


				for($amdi=0;$amdi<count($amendCrfData);$amdi++){

					if(isset($requestData['inputFieldCheck'][$amendCrfData[$amdi]['id']]) && $requestData['inputFieldCheck'][$amendCrfData[$amdi]['id']] == 'Y'){

						$searchKey = 'input_'.$amendCrfData[$amdi]['id'].'_'.$amendCrfData[$amdi]['counter'];

					}else{

						$searchKey = 'amend_toggle_'.$amendCrfData[$amdi]['id'].'_'.$amendCrfData[$amdi]['counter'];
					}
					if(isset($searchKey) && $searchKey != ''){

						 if(isset($amendData[$searchKey]) && $amendData[$searchKey] != '')
						 {
							$amendCrfData[$amdi]['newValue'] = $amendData[$searchKey];

						 	unset($amendCrfData[$amdi]['insertNo']);
							 
						 }else{

							 if($amendCrfData[$amdi]['type2'] != 'ACM'){

						 		$amendCrfData[$amdi]['newValue'] = '';
						 	}else{

						 		$amendCrfData[$amdi]['insertNo'] = 'Y';
							}
						 } 
						 
						 if(isset($requestData['newDisplayData'][$searchKey])){
							$amendCrfData[$amdi]['newDisplayValue'] = $requestData['newDisplayData'][$searchKey];
						}else{
							$amendCrfData[$amdi]['newDisplayValue'] = isset($amendData[$searchKey]) && $amendData[$searchKey] != ''? $amendData[$searchKey] : ''; 
						}
					}else{

						if($amendCrfData[$amdi]['required'] == 'N'){

							$amendCrfData[$amdi]['newValue'] = '';
						}else{
							return json_encode(['status'=>'fail','msg'=>'Please insert all data','data'=>[]]);
						}
					}

			//--------------address field validation check max 45--------------------\\

				if(in_array($amendCrfData[$amdi]['fieldName'], ['CUST_PERM_ADDR3','CUST_PERM_ADDR2','CUST_PERM_ADDR1'])){

					if(!(strlen($amendCrfData[$amdi]['newValue']) <= 45)){
						return json_encode(['status'=>'fail','msg'=>'Validation failed address field.','data'=>[]]);
					}
				}

				if(in_array($amendCrfData[$amdi]['id'],[28,29,30])){
					if($amendCrfData[$amdi]['fieldName'] == 'nomineeBirthDt'){
						
						$getNomineeAge =  \Carbon\Carbon::parse($amendCrfData[$amdi]['newValue'])->age;
						
						if($amendCrfData[$amdi]['newValue'] != '' && $getNomineeAge < 18){
							$validationCheck = 'Y';
						}else{
							$validationCheck = 'N';
						}
					}
					
				}
				
				if($validationCheck == 'Y'){
					
					if(($amendCrfData[$amdi]['newValue'] == '') && ($amendCrfData[$amdi]['required'] != 'N')){
						return json_encode(['status'=>'fail','msg'=>'Please insert all data','data'=>[]]);
					}
				}

				if($amendCrfData[$amdi]['id'] == '11'){
					
					if($voltFlagCheck != ''){
					}else{
						if(isset($requestData['commuAddData']['addproof_id']) && $requestData['commuAddData']['addproof_id'] == ''){
							return json_encode(['status'=>'fail','msg'=>'Please Select address proof of id.','data'=>[]]);
			}

						if(isset($requestData['commuAddData']['addproof_no']) && $requestData['commuAddData']['addproof_no'] == ''){
							return json_encode(['status'=>'fail','msg'=>'Please Enter address proof number.','data'=>[]]);
						}
					}

				}
			}

				//-------------Process image---------------\\
				
				$getEvidence = Session::get('uniqEvidence');
				
				if(isset($requestData['imageData']) && $requestData['imageData'] != ''){
					$imageData = $requestData['imageData'];
				}

				//------------If images are found and this is not a ekyc case--------------\\
				if(count($imageData) > 0 && $voltFlagCheck == ''){
					$found = false;
					foreach($getEvidence as $evdId => $data){
						if($data['mandatory'] == 'Y'){
							foreach($imageData as $key => $value){						
								if($evdId == $key){
									$found = true;
								}
							}
							if(!$found){
									return json_encode(['status'=>'fail','msg'=>'Mandatory document not found','data'=>[]]);					
							}
						}
					}
				}

			$approval = '';

			if($voltFlagCheck != ''){
				$addData = (isset($requestData['ekycAddData']) && $requestData['ekycAddData'] != '') ? $requestData['ekycAddData'] : '';
				$comuAddProofData = isset($requestData['commuAddData']) && $requestData['commuAddData'] !=''? $requestData['commuAddData'] :'';
				// echo "<pre>";print_r($comuAddProofData);exit;	
				$additionalData = json_encode(['ekyc_rrn' => Session::get('referenceNumber'),'proofIdData'=>$addData,'comuproofAddData'=>$comuAddProofData]);
				$approval = 'auto';
			}else{		

				$addData = (isset($requestData['ekycAddData']) && $requestData['ekycAddData'] != '') ? $requestData['ekycAddData'] : '';	
				$comuAddProofData = isset($requestData['commuAddData']) && $requestData['commuAddData'] !=''? $requestData['commuAddData'] : '';

				$additionalData = json_encode(['proofIdData'=>$addData,'comuproofAddData'=>$comuAddProofData]);
				$approval = 'offline';
			} 

			$created_at = Carbon::now();
			$branchId = Session::get('branchId');
	
			if($crf_numberData == ''){

					$getCustData = Session::get('currCustomerDetails');
					$getCustName = '';
					$emailId = '';
					$mobileNumber = '';

					if(isset($getCustData['data']['customerDetails']['CUST_NAME']) && $getCustData['data']['customerDetails']['CUST_NAME'] != ''){
						$getCustName = $getCustData['data']['customerDetails']['CUST_NAME']; 
					}

					if(isset($getCustData['data']['customerDetails']['EMAIL_ID']) && $getCustData['data']['customerDetails']['EMAIL_ID'] != ''){

						$emailId = $getCustData['data']['customerDetails']['EMAIL_ID'];
					}
					if(isset($getCustData['data']['customerDetails']['CUST_PAGER_NO']) && $getCustData['data']['customerDetails']['CUST_PAGER_NO'] != ''){

						$mobileNumber = $getCustData['data']['customerDetails']['CUST_PAGER_NO'];
					}

					$amendUniqueNumber = CommonFunctions::amendUniqueNumber($branchId);
					Session::put('crfNumber',$amendUniqueNumber);
					
					//--------------------Any error for below statement rollback data---------------------\\
				

					$amendMaster =  DB::table('AMEND_MASTER')->insert(['CRF_NUMBER'=>$amendUniqueNumber,
																		'CREATED_AT'=>$created_at,
																		'CREATED_BY' => Session::get('userId'),
																		'CUSTOMER_ID'=>Session::get('currCustomerNo'),
																		'ACCOUNT_NO'=>Session::get('currAccountNo'),
																		'VOLT_MATCH_FLAG'=>Session::get('voltMatch'),
																		'SOL_ID'=>$branchId,
																		'REFERENCE_NO'=>Session::get('referenceNumber'),
																		'CUSTOMER_NAME'=>$getCustName,
																		'CRF_NEXT_ROLE'=> 2, 		//defaulr branch role   
																		'APPROVAL' => $approval,
																		'CRF_STATUS' => 22,
																		'ADDITIONAL_DATA'=>	$additionalData,
																		'MOBILE_NUMBER' => $mobileNumber,
																		'EMAIL_ID' => $emailId,
																		'L1'=>'A',
																		'L2'=>'R'					
																		]);
																		
					$amendcrfId = DB::table('AMEND_MASTER')->select('ID')
													->where('CRF_NUMBER',$amendUniqueNumber)
													->get()
													->toArray(); 

					$amendcrfId = current($amendcrfId);
					$amendcrfId = $amendcrfId->id;

					$cacheUpdt = DB::table('AMEND_MASTER')
									->where('ID',$amendcrfId)
									->updateLob(	
										['UPDATED_AT'=>$created_at,'UPDATED_BY' => Session::get('userId')],					
										['CACHE_DATA' => base64_encode(
																json_encode(
																			array(
																				//'currAllData' => Session::get('currAllData'),
																				'cleanArray' => $amendCrfData,
																				'imageData' => $imageData,						
																				'uniqEvidence' => Session::get('uniqEvidence'),
																				'ovdCheck' => $ovdCheck,
																				'aadharRefNumber' => Session::get('getekycReference'),
																				'accountDetails'=> Session::get('currAccountDetails')
																				))
																	)]
									);					
									



							$amendDataQueue = self::amendInsertUpdateData($amendUniqueNumber,$amendcrfId,$amendCrfData,$additionalData,$tokenKey);

							// $amendDataQueue = DB::table('AMEND_QUEUE')->insert(['CRF' => $amendUniqueNumber,
							// 													'AMEND_ITEM' => $amendCrfData[$crfSeq]['description'],
							// 													'FIELD_NAME' => $amendCrfData[$crfSeq]['fieldName'],
							// 													'ACCOUNT_NO' => $amendCrfData[$crfSeq]['accNo'],
							// 													'OLD_VALUE' => $amendCrfData[$crfSeq]['oldValue'],
							// 													'NEW_VALUE' => $amendCrfData[$crfSeq]['newValue'],
							// 													'AMEND_FIELD' => $amendCrfData[$crfSeq]['amendField'],
							// 													'CRF_ID' => $amendcrfId->id,
							// 													'NEW_VALUE_DISPLAY' => $amendCrfData[$crfSeq]['newDisplayValue'],
							// 													'CREATED_AT' => $created_at,
							// 													'CREATED_BY' => Session::get('userId'),
							// 													'ADDITION_DATA_EKYC' => json_encode($additionalData),
							// 													'TAG' => $amendCrfData[$crfSeq]['type2'],
							// 													'SOFT_DEL' => $amendCrfData[$crfSeq]['sofDel'],	
							// 													]);
	

				// if($amendMaster && $cacheUpdt && $amendDataQueue){
				// 	DB::commit();
				// }else{
				// 	DB::rollback();
				// 	return json_encode(['status'=>'fail','msg'=>'Server error. Please try later','data'=>[]]);
				// }

				$comment = '';
				CommonFunctions::saveAmendStatusLog($amendUniqueNumber,'CRF Generated',2,$comment);
				
				return json_encode(['status'=>'success','msg'=>'CRF generated','data'=>$amendUniqueNumber]);
			}else{

				// $existingAmendData = DB::table('AMEND_QUEUE')->select('NEW_VALUE','ACCOUNT_NO')
				// 												 ->where('CRF',$crf_numberData)
				// 												 ->get()
				// 												 ->toArray();
				// echo "<pre>";print_r($existingAmendData);exit;
				$cacheUpdt = DB::table('AMEND_MASTER')
									->where('CRF_NUMBER',$crf_numberData)
									->updateLob(	
										['UPDATED_AT'=>$created_at,'ADDITIONAL_DATA' => $additionalData,'UPDATED_BY' => Session::get('userId')],					
										['CACHE_DATA' => base64_encode(
																json_encode(
																			array(
																				'cleanArray' => $amendCrfData,
																				'imageData' => $imageData,								
																				'uniqEvidence' => Session::get('uniqEvidence'),
																				'ovdCheck' => $ovdCheck,
																				'aadharRefNumber' => Session::get('getekycReference'),
																				'accountDetails'=> Session::get('currAccountDetails')
																				))
																	)]
									);	

				// echo "<pre>";print_r($amendCrfData);exit;
		// 		for($crfSeq=0;$crfSeq<count($amendCrfData);$crfSeq++){

		// 			//---check exsiting or new value are same or not value same not update and value differnt are updated---\\
		// 			if($amendCrfData[$crfSeq]['sofDel'] != 'Y'){


  // // updt all crf rec as N

		// 				for($exSeq = 0;count($existingAmendData)>$exSeq;$exSeq++){

		// 					// if($existingAmendData[$exSeq]->account_no == $amendCrfData[$crfSeq]['accNo']){

		// 				// chk if crf   = custid + act exist
		// 						$amendDataQueue = DB::table('AMEND_QUEUE')->where('CRF',$crf_numberData)
		// 																  ->where('FIELD_NAME',$amendCrfData[$crfSeq]['fieldName'])
		// 																  ->update(['NEW_VALUE' => $amendCrfData[$crfSeq]['newValue'],
		// 																			'NEW_VALUE_DISPLAY' => $amendCrfData[$crfSeq]['newDisplayValue'],
		// 																			'ACCOUNT_NO' => $amendCrfData[$crfSeq]['accNo'],
		// 																			'UPDATED_AT'=>$created_at,
		// 																			'UPDATED_BY' => Session::get('userId'),
		// 																			'ADDITION_DATA_EKYC' => $additionalData,
		// 																			'TAG' => $amendCrfData[$crfSeq]['type2'],
		// 																			'SOFT_DEL' => $amendCrfData[$crfSeq]['sofDel']]);								   

		// 					// }
		// 					// else{

		// 					// 	//-------------new account data insert becuase is overdide account number---------------\\
		// 					// 	$amendDataQueue = DB::table('AMEND_QUEUE')->where('ACCOUNT_NO',$existingAmendData[$exSeq]->account_no)
		// 					// 											  ->delete();								
		// 					// }
		// 				}
		// 			}
		// 		}

				$amendcrfId = '';
				$amendDataQueue = self::amendInsertUpdateData($crf_numberData,$amendcrfId,$amendCrfData,$additionalData);
									
				return json_encode(['status'=>'success','msg'=>'CRF updated successfully!','data'=>$crf_numberData]);

			}
		}
			
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}
	public function amendform(Request $request){
		try{
			$tokenParams = explode('.',Cookie::get('token'));
	    	$decodeString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
	    	$getCrfData = base64_decode($decodeString);
	    	$getCrfData = json_decode($getCrfData,true);

			if(isset($getCrfData['crfNumber']) && $getCrfData['crfNumber'] != ''){
				$getCrfNUmber = $getCrfData['crfNumber'];
				$breadcrumhide = $getCrfData['breadCrumBack'];
			}else{
				$breadcrumhide = '';
	    		$getCrfNUmber = $getCrfData;
			}	    

			$imagecacheCheck = '';
			$addData = '';									  
	    	$getAmendData = DB::table('AMEND_QUEUE')->select('CRF','AMEND_ITEM','FIELD_NAME','OLD_VALUE','NEW_VALUE_DISPLAY','ACCOUNT_NO','CREATED_AT','ADDITION_DATA_EKYC','SOFT_DEL')
														->where('CRF',$getCrfNUmber)
														// ->where('SOFT_DEL','!=','Y')
														->get()
														->toArray();

			$getAmendMasterData = DB::table('AMEND_MASTER')->select('ID','CUSTOMER_ID','ACCOUNT_NO','CRF_STATUS','APPROVAL','VOLT_MATCH_FLAG','CUSTOMER_NAME','CACHE_DATA','ADDITIONAL_DATA')
															->where('CRF_NUMBER',$getCrfNUmber)
															->get()->toArray();
			$getAmendMasterData = (array )current($getAmendMasterData);


			//-----------to get amend proof of documene table----------------\\

			$imageData = DB::table('AMEND_PROOF_DOCUMENT')->select('AMEND_PROOF_IMAGE','CRF_ID','OSV','EVIDENCE_ID','CREATED_AT')
														  ->where('CRF_NUMBER',$getCrfNUmber)
														  ->get()
														  ->toArray();

	
			//-------------First time fill the form fetch data from cache data--------------\\
			$custReqFormId = $getAmendMasterData['id'];

				if(count($imageData)<=0){

					$cacheDetails = json_decode(base64_decode($getAmendMasterData['cache_data']),true);
					$getImageData = $cacheDetails['imageData'];
					// $getImageData = (isset($cacheDetails['imageData']) && $cacheDetails['imageData'] != '') ? $cacheDetails['imageData'] : '';
					$ovd_check = (isset($cacheDetails['ovdCheck']) && $cacheDetails['ovdCheck'] != '') ?  $cacheDetails['ovdCheck'] : '';
					$imagecacheCheck = 'Y';
				}		

			//----------end chache image-------------//

	
			//-------------Second time form in review---------------------\\

				if(count($imageData)>0){
					$getImageData = [];

					for($imageSeq=0;count($imageData)>$imageSeq;$imageSeq++){

						$getImageData[$imageData[$imageSeq]->evidence_id] = $imageData[$imageSeq]->amend_proof_image; 
						$ovd_check[$imageData[$imageSeq]->evidence_id] = $imageData[$imageSeq]->osv;
					}
					$imagecacheCheck = 'N';
				}

			//-------------end second time get image data------------------//	 

			$getCustomerName = $getAmendMasterData['customer_name'];
			$voltNoMatch = $getAmendMasterData['volt_match_flag'];
			$additionalData = $getAmendMasterData['additional_data'];

			if($voltNoMatch == ''){
				$addData = json_decode($additionalData,true);

				if($addData != ''){
					
					$proof_Id = isset($addData['proofIdData']['proof_id']) && $addData['proofIdData']['proof_id'] != ''?$addData['proofIdData']['proof_id']:'';
					if($proof_Id == 1){
						$addData['proofIdData']['id_code'] = 'XXXX-XXXX-'.substr($addData['proofIdData']['id_code'],10);
					}

					$idDescription = AmendCommonFunctions::getIdDescription($proof_Id);
					// echo "<pre>";print_r($proof_Id);exit;
					if(count($idDescription)>0 && $proof_Id != ''){
						$addData['proofIdData']['proof_id'] = $idDescription['ovd'];
					}
					// echo "<pre>";print_r($addData);exit;
					$addcomuProofData = isset($addData['comuproofAddData']['addproof_id']) && $addData['comuproofAddData']['addproof_id'] !=''?$addData['comuproofAddData']['addproof_id']:'';
					if($addcomuProofData != ''){

						$idAddDescription = AmendCommonFunctions::getIdDescription($addcomuProofData);

						if(count($idAddDescription)>0){
							$addData['comuproofAddData']['addproof_id'] = $idAddDescription['ovd'];
						}
					}
				}
			}

			$getUserId = Session::get('userId');
			$getBranchOfficalName = CommonFunctions::getUserName($getUserId);
		
			//created new storage path code unique
			$crfId = '';
			$getCurrentyear = '';
			$evidenceData = array();
			if(count($getImageData)>0){
				$created_At = Carbon::now();
				$getCurrentyear = $created_At->year;
				$storagePath = storage_path('uploads/amend');
				$crfId = substr($getAmendMasterData['id'],-1);
				$storagePath = $storagePath.'/'.$getCurrentyear.'/'.$crfId.'/'.$getAmendMasterData['id'];
				$imageMove = false;
				// dd($storagePath);
				foreach($getImageData as $key => $imageName){

						$tempstoragePath = storage_path('uploads/temp').'/'.$imageName;

						if(File::exists($tempstoragePath)){
							
							if(!File::exists($storagePath)){
								File::makeDirectory($storagePath,0775, true,true);
							}
								File::move($tempstoragePath,$storagePath.'/'.$imageName);
								$imageMove = true;
						}
						if(isset($ovd_check[$key]) && $ovd_check[$key] == 'Y'){

							if($imageMove){
								$typeImage = 'ovd_section';
								$markamendImage = CommonFunctions::markAmendImage($getCurrentyear,$crfId,$imageName,$typeImage,$custReqFormId);
							}

							$osvCheck = 'Y';
						}else{
							$osvCheck = 'N';
						}
						$getEvidncename = DB::table('AMEND_EVIDENCE')->select('EVIDENCE')
																	 ->where('ID',$key)
																	 ->get()
																	 ->toArray();
						$getEvidncename = (array)current($getEvidncename);
						
						$evidenceData[$key]['evidenceName'] = $getEvidncename['evidence'];
						$evidenceData[$key]['imageName'] = $imageName;
						$evidenceData[$key]['osv_check'] = $osvCheck;
				}
			}

			if(count($getAmendData)>0){
				for($i=0;count($getAmendData)>$i;$i++){

					if($getAmendData[$i]->soft_del == 'Y'){
						unset($getAmendData[$i]);
					}
					sort($getAmendData);

					if(in_array($getAmendData[$i]->field_name,['FATCA_NATIONALITY','FATCA_CNTRY_OF_RESIDENCE','FATCA_BIRTHCOUNTRY'])){
						$getAmendData[$i]->old_value = AmendCommonFunctions::getFatacaCountryDesc($getAmendData[$i]->old_value);
					}
				}
			}

			if($voltNoMatch != ''){
				$counter = 0;
				for($getSeq=0;count($getAmendData)>$getSeq;$getSeq++){
	
					if(in_array($getAmendData[$getSeq]->field_name,['CUST_PERM_ADDR1','CUST_NAME','DATE_OF_BIRTH','CUST_SEX','CUST_TITLE_CODE','ISA_STATUS'])){
	
					}else{
						$counter++;
					}
				}

				if($counter > 6){
					$getAmendMasterData['approval'] = 'offline';
				}
			}
			
			$kycNumber = isset($addData['ekyc_rrn']) && $addData['ekyc_rrn'] != ''?$addData['ekyc_rrn']:'';
	    	return view('amend.amendsubmissionform')->with('getAmendData',$getAmendData)
	    											->with('getImageData',$evidenceData)
	    											->with('getAmendMasterData',$getAmendMasterData)
	    											->with('additionalData',$addData)
	    											->with('getBranchOfficalName',$getBranchOfficalName)
	    											->with('getCustomerName',$getCustomerName)
	    											->with('voltNoMatch',$voltNoMatch)
	    											->with('crfId',$crfId)
	    											->with('getCurrentyear',$getCurrentyear)
	    											->with('breadcrumhide',$breadcrumhide)
	    											->with('imagecacheCheck',$imagecacheCheck)
													->with('custReqFormId',$custReqFormId)
													->with('kycNumber',$kycNumber);
	    											
	  	}catch(\Illuminate\Exception\QueryException $e){
	  		if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
	  		return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	  	}
	}

	//pdf template password protection  
	public function printrequestform(Request $request){
		try{
			$requestData =  $request->get('data');
			$getCrfNUmber = $requestData['crf_number'];
			$printCall = $requestData['printCall'];
			$amendUrl = '';
			return Self::genCrf_View_Pdf($getCrfNUmber,'view',$amendUrl,$printCall);

		}catch(Exception\Illuminate\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
	  		return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public function genCrf_View_Pdf($getCrfNUmber, $mode = 'view',$amendUrl = '',$printCall =''){
		try{
			
			if(isset($requestData['printCall']) && $requestData['printCall'] != ''){
				$printCall = $requestData['printCall'];
			}

			$getAmendData = DB::table('AMEND_QUEUE')->select('CRF','AMEND_ITEM','FIELD_NAME','OLD_VALUE','NEW_VALUE_DISPLAY','ACCOUNT_NO','CREATED_AT','ADDITION_DATA_EKYC')
														->where('CRF',$getCrfNUmber)
														->get()
														->toArray();


			$imageData = DB::table('AMEND_PROOF_DOCUMENT')->select('AMEND_PROOF_IMAGE','OSV','EVIDENCE_ID','CREATED_AT','UPDATED_AT')
														 ->where('CRF_NUMBER',$getCrfNUmber)
														 ->get()
														 ->toArray();

			$getAmendMasterData = DB::table('AMEND_MASTER')->select('ID','CUSTOMER_ID','ACCOUNT_NO','CRF_STATUS','APPROVAL','VOLT_MATCH_FLAG','CUSTOMER_NAME','CREATED_AT', 'CREATED_BY','ADDITIONAL_DATA','UPDATED_AT','UPDATED_BY','CACHE_DATA')
															->where('CRF_NUMBER',$getCrfNUmber)
															->get()->toArray();
			$getAmendMasterData = (array)current($getAmendMasterData);

			$getReview = DB::table('AMEND_STATUS_LOG')->select()
														->where('CRF_NUMBER',$getCrfNUmber)
														->get()
														->toArray();

			//hold and reject message for 
			$comment = '';
			$message = '';
			for($statusSeq=0;count($getReview)>$statusSeq;$statusSeq++){

				switch($getReview[$statusSeq]->status){

					case '35':
						$comment = $getReview[$statusSeq]->comments;
						$message = 'AmendL1 Hold';
					break;

					case '38':
						$comment = $getReview[$statusSeq]->comments;
						$message = 'AmendL1 Reject';
					break;

					case '45':
						$comment = $getReview[$statusSeq]->comments;
						$message = 'AMENDL2 Hold';
					break;

					case '48':
						$comment = $getReview[$statusSeq]->comments;
						$message = 'AMENDL2 Reject';
					break;

					default:
					break;
				}
			}

			$getImageData = [];
			$evidenceNameList = [];
			$imageCheckCache = '';
			$osv_check = array();
			$additionalData = '';
			$getCustomerName = $getAmendMasterData['customer_name'];
			$voltNoMatch = $getAmendMasterData['volt_match_flag'];
			$currentYear = Carbon::parse($getAmendMasterData['created_at'])->format('Y');
			$customerReqId =  $getAmendMasterData['id'];
			$crfId = substr($getAmendMasterData['id'],-1);
			$customer_Id = $getAmendMasterData['customer_id'];
		
		//--------process form to npc check  for data amend proof document table-----------\\

			if(count($imageData) > 0){
				foreach($imageData as $key => $value){
					$getImageData[$value->evidence_id] = $value->amend_proof_image;
					$evidenceNameList[$value->evidence_id] = DB::table('AMEND_EVIDENCE')->select('EVIDENCE')
																   ->where('ID',$value->evidence_id)
																   ->get()
																   ->toArray();
					$evidenceNameList[$value->evidence_id] = current($evidenceNameList[$value->evidence_id]);
				}
					$imageCheckCache = 'N';
			}
		//-------first flow check the image data in amend master table--------------\\
			
			if(count($getImageData) <= 0){
				$masterData = json_decode(base64_decode($getAmendMasterData['cache_data']));
				$osv_check  = (array) $masterData->ovdCheck;
				$getImageData = (array) $masterData->imageData;
				$evidenceNameList = (array) $masterData->uniqEvidence;
				$imageCheckCache = 'Y';
			}
			$additionalData = json_decode($getAmendMasterData['additional_data'],true);
		
			if($voltNoMatch == ''){

				$additionalData = json_decode($getAmendMasterData['additional_data'],true);

				if($additionalData != ''){

					$additionalId = '';
					if(isset($additionalData['proofIdData']['proof_id']) && $additionalData['proofIdData']['proof_id'] != ''){
						$additionalId = $additionalData['proofIdData']['proof_id'];

						if($additionalId == 1){

							$additionalData['proofIdData']['id_code'] = 'XXXX-XXXX-'.substr($additionalData['proofIdData']['id_code'],10);
						}
					}
					$idDescription = AmendCommonFunctions::getIdDescription($additionalId);
					if($additionalId != ''){

						$additionalData['proofIdData']['proof_id'] = (isset($idDescription['ovd']) && $idDescription['ovd'] != '') ? $idDescription['ovd'] : '';
				}

					$addcomuProofData = isset($additionalData['comuproofAddData']['addproof_id']) && $additionalData['comuproofAddData']['addproof_id'] !=''?$additionalData['comuproofAddData']['addproof_id']:'';

					if($addcomuProofData != ''){

						$idAddDescription = AmendCommonFunctions::getIdDescription($addcomuProofData);

						if(count($idAddDescription)>0){
							$additionalData['comuproofAddData']['addproof_id'] = $idAddDescription['ovd'];
			}
					}
				}
			}
			$getUserId = Session::get('userId');

			if(isset($getAmendMasterData['created_by']) && $getAmendMasterData['created_by'] != null){
				$getUserId = $getAmendMasterData['created_by'];
			}
			$getBranchOfficalName = CommonFunctions::getUserName($getUserId);
				
			$linkNotShow = '';
			
			$getaddData = $additionalData;
			$kycNumber = isset($getaddData['ekyc_rrn']) && $getaddData['ekyc_rrn'] != ''?$getaddData['ekyc_rrn']:'';
			if($mode == 'view'){

				$linkNotShow = 'Y';

		    	return view('amend.requestform')->with('getAmendData',$getAmendData)
												->with('getImageData',$getImageData)
												->with('getAmendMasterData',$getAmendMasterData)
												->with('additionalData',$additionalData)
												->with('getBranchOfficalName',$getBranchOfficalName)
												->with('getCustomerName',$getCustomerName)
												->with('currentYear',$currentYear)
												->with('crfId',$crfId)
												->with('imageCheckCache',$imageCheckCache)
												->with('evidenceNameList',$evidenceNameList)
												->with('osv_check',$osv_check)
												->with('message',$message)
												->with('comment',$comment)
												->with('printCall',$printCall)
												->with('voltNoMatch',$voltNoMatch)
												->with('linkNotShow',$linkNotShow)
												->with('customerReqId',$customerReqId)
												->with('kycNumber',$kycNumber);
				}else{
					//---------------password protection pdf documment-----------\\

					$linkNotShow = $amendUrl;
					$customerDetails = Session::get('currCustomerDetails');

					$getDate = '';

					if(isset($customerDetails['data']['customerDetails']['DATE_OF_BIRTH']) && $customerDetails['data']['customerDetails']['DATE_OF_BIRTH'] != ''){

						$getDate =  $customerDetails['data']['customerDetails']['DATE_OF_BIRTH'];
					}

					$pdfPass = $customer_Id;
					view()->share([	'getAmendData'=>$getAmendData,
									'getImageData'=>$getImageData,
									'getAmendMasterData'=>$getAmendMasterData,
									'additionalData'=>$additionalData,
									'getBranchOfficalName'=>$getBranchOfficalName,
									'getCustomerName'=>$getCustomerName,
									'currentYear'=>$currentYear,
									'crfId'=>$crfId,
									'imageCheckCache'=>$imageCheckCache,
									'evidenceNameList'=>$evidenceNameList,
									'osv_check'=>$osv_check,
									'message'=>$message,
									'comment'=>$comment,
									'printCall'=>$printCall,
									'voltNoMatch'=>$voltNoMatch,
									'linkNotShow'=>$linkNotShow,
									'customerReqId' => $customerReqId,
									'kycNumber'=>$kycNumber]);

				
					$pdf = PDF::loadView('amend.requestform');
					$pdf->setEncryption($pdfPass);
					$checkFile = base_path('/conf_data/crf/DCB-CUBE-'.$getCrfNUmber.'.pdf');
					// dd($checkFile);
					if(!file_exists($checkFile)){
						$pdf->save($checkFile);
						// Storage::disk('conf_data')->put('DCB-CUBE-'.$getCrfNUmber.'.pdf',$pdf);
				     	NotificationController::processNotification($getCrfNUmber,'CRF_APPROVED','CRF_SEND_EMAIL','amend',$checkFile);

						$amendMaster = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$getCrfNUmber)	 
																->update(['CRF_PDF_DOCUMENT_PATH' => $checkFile,
																		  'CREATED_AT' => Carbon::now(),
																		  'CREATED_BY' => Session::get('userId')]);

						return json_encode(['status'=>'success','msg'=>'CRF form (pdf) generated and saved','data'=>[]]);
				    }
			}
		         	    	
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){ dd($e->getMessage()); }
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
		    											
	}

	//--------------crf document save into table---------------\\

	public function savecrfdocument(Request $request){
		try{
			$requestData = $request->get('data');

			if((env('APP_SETUP') == 'UAT') || (env('APP_SETUP') == 'PRODUCTION')){
				$authCheck = AuthenticationController::authenticate($requestData['password']);
				if($authCheck == false){
					return json_encode(['status'=>'fail','msg'=>'Authentication Failed! Please try again','data'=>[]]);
				}
			}
			$created_At = Carbon::now();
			$crf_document = '';
			$sharedLink = '';

			if(isset($requestData['imageName']) && $requestData['imageName'] != ''){

				$crf_document = $requestData['imageName'];
			}

			$crf_number = $requestData['crf_number'];

			$crfMasterData = DB::table('AMEND_MASTER')->select('ID','CUSTOMER_ID','APPROVAL','VOLT_MATCH_FLAG','REFERENCE_NO','CREATED_AT','CACHE_DATA','EMAIL_ID','MOBILE_NUMBER')
													 ->where('CRF_NUMBER',$crf_number)
													 ->get()
													 ->toArray();

			$crfMasterData = (array) current($crfMasterData);
			$crfId = substr($crfMasterData['id'],-1);
			$imageYear = Carbon::parse($crfMasterData['created_at'])->format('Y');
		
			//image data insert into table

			$getCacheData = json_decode(base64_decode($crfMasterData['cache_data']));

			$voltNoMatch = $crfMasterData['volt_match_flag'];
			$crf_id = $crfMasterData['id'];
			$crf_customer_id = $crfMasterData['customer_id'];
			$referenceNumber = $crfMasterData['reference_no'];
			$comment = '';

			switch($voltNoMatch){
				case 'Y':
					$custApproval = 'auto';
					$l1 = 'A';	 // Auto
					$l2 = 'A';
					$nextRole = '21'; // L2
					$crf_status = '60';
					break;
				case 'N':
					$custApproval = 'auto';
					$l1 = 'A';
					$l2 = 'R';	// Required
					$nextRole = '20'; // L2
					$crf_status = '40';
					break;
				default:
					$custApproval = 'offline';
					$l1 = 'A';
					$l2 = 'R';	// Required
					$nextRole = '2'; // branch 
					$crf_status = '22';
					break;
				}						

				$getChecKData = DB::table('AMEND_QUEUE')->select('FIELD_NAME')
														->where('CRF',$crf_number)
														->get()
														->toArray();
			if($voltNoMatch != ''){

				$counter = 0;
				for($getSeq=0;count($getChecKData)>$getSeq;$getSeq++){

					if(in_array($getChecKData[$getSeq]->field_name,['CUST_PERM_ADDR1','CUST_NAME','DATE_OF_BIRTH','CUST_SEX','CUST_TITLE_CODE','ISA_STATUS'])){

					}else{
						$counter++;
					}
				}
				
				if($counter > 6){
					$custApproval = 'offline';
				}
			}
			// echo "<pre>";print_r($custApproval);exit;
			if($custApproval == 'offline'){
				if($crf_document == ''){
					$crf_status = '23';
					$nextRole = '2';
					$url = '/bank/amendform';
					$breadCrumBack = 'Y';
					if(isset($requestData['imageosvData']) && $requestData['imageosvData'] != ''){
						$imageData = $requestData['imageosvData'];

						foreach($imageData as $evid => $imageName){
							//dynamically update osv flag in table 
							$nameImage = $imageName;

							// echo "<pre>";print_r($nameImage);exit;
							$osvFlag = (isset($getCacheData->ovdCheck->$evid) && $getCacheData->ovdCheck->$evid !='') ? $getCacheData->ovdCheck->$evid : 'N';

							$evidenceCount = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crf_number)
																				->where('CRF_ID',$crfMasterData['id'])
																				->where('EVIDENCE_ID',$evid)
																				->count();	  
							if($evidenceCount == 0){

								$insertImage = DB::table('AMEND_PROOF_DOCUMENT')->insert(['CRF_NUMBER' => $crf_number,
																						  'AMEND_PROOF_IMAGE' => $imageYear.'/'.$crfId.'/'.$crf_id.'/'.$nameImage,
																						  'CRF_ID' => $crfMasterData['id'],
																						  'EVIDENCE_ID' => $evid,
																						  'OSV' => $osvFlag,
																						  'CREATED_AT' => $created_At,
																						  'CREATED_BY' => Session::get('userId'),
																							]);
							}else{

								$insertImage = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crf_number)
																				->where('CRF_ID',$crfMasterData['id'])
																				->where('EVIDENCE_ID',$evid)
																				->update([
																						  'AMEND_PROOF_IMAGE' => $imageYear.'/'.$crfId.'/'.$crf_id.'/'.$nameImage,
																						  'OSV' => $osvFlag,
																						  'UPDATED_AT' => $created_At,
																						  'UPDATED_BY' => Session::get('userId'),
																						]);
							}
						}
					}

					// $getecrfProcessingform = DB::table('AMENDITEMS')->where();
					$sharedLink = AmendRules::amendCrfApprovalOnline($getChecKData);
					$amendUrl = '';
					if($sharedLink == 'true'){

					$getMobileNumber = $crfMasterData['mobile_number'];
					// echo "<pre>";print_r($getMobileNumber);exit;
					$hash_capture_link =  hash('sha256','CUBE'.$crf_number.'AIS');
					$created_date = Carbon::parse($crfMasterData['created_at'])->format('Y-m-d');
	
		//------------Combine three value of crf number ,mobile number or created_date--------\\
	
					$encryptedString =  EncryptDecrypt::AES256Encryption($crf_number.'|'.$getMobileNumber.'|'.$created_date,'amend-4');
	
		//---------concat hash string and encrypted string ------------------\\
					
					$gettoCrc = $hash_capture_link.$encryptedString;
	
		//--------------set crc to concat string-----------------------\\
	
					$getcheckDigit = crc32($gettoCrc);
	
		//-----------then again concat hast string and encrypted string and sub string give 2 digit substr of firt decoded----------\\
	
					$hashToPass = base64_encode($hash_capture_link.substr($getcheckDigit,0,2).$encryptedString);
	
		//----------- Replace the current url to new set hashing url---------------------\\
					// dd('test');
							
						$amendUrl = config('constants.APPLICATION_SETTINGS.AMEND_WEB_URL');	
						// echo "<pre>";print_r($amendUrl);exit;	
						$amendUrl = $amendUrl.$hashToPass;
						Cache::put('amendUrl'.$crf_number,$amendUrl);
					}

					CommonFunctions::saveAmendStatusLog($crf_number,'Waiting Customer Aprroval',2,'Document Upload Pending');
					$updateRole = self::amendRoleUpdate($crf_number,$nextRole);
					$updateStatus = self::amendCRFStatusUpdate($crf_number,$crf_status);

				try{
					Self::emailCRF($crf_number,$amendUrl);		
				}catch(\Throwable $e){
					if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
				}
					
					return json_encode(['status'=>'success','msg'=>'Please wait for customer approval..','data'=>['crfNumber'=>$crf_number,'breadCrumBack'=>$breadCrumBack],'url'=>$url]);
				}				
			}
			$failure = false;
			$failureMsg = '';

			if($custApproval == 'auto'){
			
				$url = '/bank/amenddashboard';
				$statusStr = 'CRF Approved';
				
				$updateCrfFlag = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crf_number)
															  ->update([
															  			'UPDATED_BY' => Session::get('userId'),
																		'UPDATED_AT' => Carbon::now(),
																		'APPROVAL'=>$custApproval,
																		'L1'=>$l1,
																		'L2'=>$l2]);
				$updateRole = self::amendRoleUpdate($crf_number,$nextRole);
				$updateStatus = self::amendCRFStatusUpdate($crf_number,$crf_status);
				CommonFunctions::saveAmendStatusLog($crf_number,$statusStr,2,$comment);

				if($l1 == 'A' && $l2 == 'A'){ // Special Case
					
	                // $refernceNumber = $crfMasterData['reference_no'];
	                // $proofId = '9';
	                // $expDate = '';
		            $amendResponse = AmendApi::amendforEkycData($crfMasterData['id'],$crfMasterData['customer_id'],$crf_number);
					$amendResponse = json_decode($amendResponse,true);
					if(isset($amendResponse['status']) && $amendResponse['status'] == 'success'){
						Api::kycUpdate($crf_id,$crf_customer_id);
						$updateRole = self::amendRoleUpdate($crf_number,$nextRole);
						$updateStatus = self::amendCRFStatusUpdate($crf_number,$crf_status);						
						CommonFunctions::saveAmendStatusLog($crf_number,'Moved To Amend-L1',19,'Auto Approval');
						CommonFunctions::saveAmendStatusLog($crf_number,'Moved To Amend-L2',20,'Auto Approval');
						CommonFunctions::saveAmendStatusLog($crf_number,'Moved To Amend-QC',$nextRole,$comment);
						return json_encode(['status'=>'success','msg'=>'Form processed successfully','data'=>$crf_number,'url'=>$url]);
					}else{
						$failure = true;
						$failureMsg = 'Amendment API failed!';			
					}		
				}
				CommonFunctions::saveAmendStatusLog($crf_number,'Moved To Amend-L1',19,'Auto Approval');
				CommonFunctions::saveAmendStatusLog($crf_number,'Moved To Amend-L2',20,'');

			}

			Cache::forget('amendUrl'.$crf_number); // temp code
			if($failure){
				return json_encode(['status'=>'fail','msg'=>$failureMsg,'data'=>'','url'=>$url]);
			}else{		
				return json_encode(['status'=>'success','msg'=>'CRF submitted to NPC','data'=>$crf_number,'url'=>$url]);
			}
		
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){ dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}

	}

	public function clearAmendSession(){

		Session::put('currAccountNo','');				
		Session::put('currAccountDetails','');
		Session::put('currCustomerDetails','');
		Session::put('userEkycDetails','');
		Session::put('uniqEvidence','');
		Session::put('currAllData','');
		Session::put('cleanArray','');
		Session::put('crfNumber','');
		Session::put('getekycStatus','');
		Session::put('getminorStatus','');
		Session::put('additionalData','');
		Session::put('imageData','');
		Session::put('voltMatch','');		
		Session::put('getekycReference','');

		Session::forget('currAccountNo');				
		Session::forget('currAccountDetails');
		Session::forget('currCustomerDetails');
		Session::forget('userEkycDetails');
		Session::forget('uniqEvidence');
		Session::forget('currAllData');
		Session::forget('cleanArray');
		Session::forget('crfNumber');
		Session::forget('getekycStatus');
		Session::forget('getminorStatus');
		Session::forget('additionalData');
		Session::forget('imageData');
		Session::forget('voltMatch');			    	
		Session::forget('getekycReference');

	}

	public function amenddeleteimage(Request $request){
		try{
			if($request->ajax()){
				$requestData = $request->get('data');
				$imageName = $requestData['imageName'];
				$evidence_id = $requestData['evidenceId'];
				$deleteFlag = false;		

				if(isset($requestData['crfNumber']) && $requestData['crfNumber'] != ''){
					$amendMasterData = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$requestData['crfNumber'])->get()->toArray();
					$amendMasterData = (array) current($amendMasterData);

					$imageName = $requestData['imageName'];
					$crfId = substr($amendMasterData['id'],-1);
					$custReqId= $amendMasterData['id'];
					$imageYear = Carbon::parse($amendMasterData['created_at'])->format('Y');

					$file_amendPath = $imageYear.'/'.$crfId;
					$filename = storage_path('/uploads/amend/'.$file_amendPath.'/'.$imageName);
					if(file_exists($filename)){
						$delete = File::delete($filename);
						$deleteFlag = true;
					}

					$filename = storage_path('/uploads/amend/'.$file_amendPath.'/OSV_DONE_'.$imageName);
					if(file_exists($filename)){
						$delete = File::delete($filename);
						$deleteFlag = true;
					}
                	$filename = storage_path('/uploads/temp/'.$imageName);
                	if(file_exists($filename)){
						$delete = File::delete($filename);
						$deleteFlag = true;
					}

					$filename = storage_path('/uploads/amend/'.$imageName);
					if(file_exists($filename)){
						$delete = File::delete($filename);
						$deleteFlag = true;
					}

                }else{
                	$filename = storage_path('/uploads/temp/'.$imageName);
                	if(file_exists($filename)){
	                     $delete = File::delete($filename);
	                     $deleteFlag = true;
	                }
                }

                if($deleteFlag){
                    return json_encode(['status'=>'success','msg'=>'Image deleted','data'=>['image_div'=>$requestData['image_div']]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
			}
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){ dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public function amendRoleUpdate($crf_number,$nextrole){
		try{

			$updateCrfFlag = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crf_number)
													  ->update(['CRF_NEXT_ROLE'=>$nextrole,'UPDATED_BY' => Session::get('userId'),
																'UPDATED_AT' => Carbon::now(),]);
			
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public function amendCRFStatusUpdate($crf_number,$amend_status){
		try{

			$updateCrfFlag = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crf_number)
													  ->update(['CRF_STATUS'=>$amend_status,'UPDATED_BY' => Session::get('userId'),
																'UPDATED_AT' => Carbon::now(),]);
			
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public function uploadcrfapproval(Request $request){
		try{
			if($request->ajax()){
				
				$requestData = $request->get('data');

			
				if((env('APP_SETUP') == 'UAT') || (env('APP_SETUP') == 'PRODUCTION')){

					$authCheck = AuthenticationController::authenticate($requestData['password']);

					if($authCheck == false){
						return json_encode(['status'=>'fail','msg'=>'Authentication Failed! Please try again','data'=>[]]);
					}
				}

				$crfUploadFlag = 'Offline Approval';
				$crfNumber = $requestData['crfNumber'];
				$crfDocument = $requestData['crfDocument'];
				$userName = $requestData['userName'];
				$approvalType = $requestData['approvalType'];
				
				$amendMasterData = DB::table('AMEND_MASTER')->select('ID')
															->where('CRF_NUMBER',$crfNumber)
															->get()
															->toArray();
				$amendMasterData = current($amendMasterData);
				$custReqFormId = $amendMasterData->id;
				$crfId = substr($amendMasterData->id,-1);
				$currentYear = Carbon::now()->year;

				$storagePath = storage_path('uploads/amend');
				$storageDoc = $storagePath.'/'.$currentYear.'/'.$crfId.'/'.$custReqFormId;
				$tempstoragePath = storage_path('uploads/temp').'/'.$crfDocument;

				if(File::exists($tempstoragePath)){

					if(!File::exists($storageDoc)){
						File::makeDirectory($storageDoc,0775,true,true);
					}
					File::move($tempstoragePath,$storageDoc.'/'.$crfDocument);
				}

				// $checkExtension = explode('.',$crfDocument)[1];
				$checkExtension = strtolower(substr($crfDocument,-3));

				if($checkExtension != 'pdf'){

					$typeImage = 'crf_document';
					$markamendImage = CommonFunctions::markAmendImage($currentYear,$crfId,$crfDocument,$typeImage,$custReqFormId);
	 				$nameImage = 'OSV_DONE_'.$crfDocument;
	 				$osvFlag = 'Y';
	 				$crfuploadType = 'SIGN';
	 				$crfStatus = 24;
	 				$nextRole = 20;
				}else{
					$osvFlag = 'Y';
					$nameImage = $crfDocument;
					$crfuploadType = 'SIGN';
	 				$crfStatus = 24;
	 				$nextRole = 20;
				}

 				//-------------crf upload document insert into upload document-------------\\
            	// DB::beginTransaction();
				$crfDocumentCheck = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crfNumber)
																	->where('CRF_ID',$amendMasterData->id)
																	->where('EVIDENCE_ID',1)
																	->count();
				if($crfDocumentCheck == 0){

					$crfUploadDoc = DB::table('AMEND_PROOF_DOCUMENT')->insert(['CRF_NUMBER' => $crfNumber,
																				'EVIDENCE_ID' => 1,
																				'OSV' => $osvFlag,
																				'CRF_ID' => $amendMasterData->id,
																				'AMEND_PROOF_IMAGE' => $currentYear.'/'.$crfId.'/'.$custReqFormId.'/'.$nameImage,
																				'CREATED_AT' => Carbon::now(), 
																				'CREATED_BY' => Session::get('userId'),
																				]);
				}else{
					$crfUploadDoc = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crfNumber)
																	->where('CRF_ID',$amendMasterData->id)
																	->where('EVIDENCE_ID',1)
																	->update([	'OSV' => $osvFlag,
																				'AMEND_PROOF_IMAGE' => $currentYear.'/'.$crfId.'/'.$custReqFormId.'/'.$nameImage,
																				'UPDATED_AT' => Carbon::now(), 
																				'UPDATED_BY' => Session::get('userId'),
																			]);
				}

 				if($crfUploadDoc){

 					$updateData = ['CRF_STATUS' => $crfStatus,
 								   'CRF_NEXT_ROLE' => $nextRole,
 								   'UPLOAD_CRF_FLAG' => $crfuploadType,
 								   'L1' => 'A',
 								   'L2' => 'R',
 								   'APPROVAL' => $approvalType,  
 								   'UPDATED_AT' => Carbon::now(),
 								   'UPDATED_BY' => Session::get('userId')];

 					$updateMaster = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)
 															 ->update($updateData);

 					$emailComment = 'Form is submitted to NPC';					 
 					Self::emailCRF($crfNumber,$emailComment);
					CommonFunctions::saveAmendStatusLog($crfNumber,'CRF Approved',$nextRole,'Upload CRF document');
					CommonFunctions::saveAmendStatusLog($crfNumber,'Moved To Amend-L1',19,'Auto Approval');
					CommonFunctions::saveAmendStatusLog($crfNumber,'Moved To Amend-L2',20,'');
					$updateRole = self::amendRoleUpdate($crfNumber,$nextRole);
					$updateStatus = self::amendCRFStatusUpdate($crfNumber,$crfStatus);
					DB::commit();
 					return json_encode(['status'=>'success','msg'=>'Form processing to NPC.','data'=>[]]);
 				}else{

 					// DB::rollback();
 					return json_encode(['status'=>'fail','msg'=>'Server error. Please try again later!','data'=>[]]);
 				}

			}
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data' =>[]]);
		}

	}

	public function amendInsertUpdateData($amendUniqueNumber,$amendcrfId = '',$amendCrfData,$additionalData, $tokenKey = ''){

		try{
		
			$proofData = json_decode($additionalData,true);
			$commuProofData = isset($proofData['comuproofAddData']) && $proofData['comuproofAddData'] != ''?$proofData['comuproofAddData']:'';
			$proofIdData = isset($proofData['proofIdData']) && $proofData['proofIdData'] !=''?$proofData['proofIdData']:'';
			$getaddProfComu = json_encode($commuProofData);
			$getIdProfData = json_encode($proofIdData);
			// echo "<pre>";print_r($getaddProfComu);exit;
			$created_at = Carbon::now();
			$amdupdate  = DB::table('AMEND_QUEUE')->where('CRF',$amendUniqueNumber)
												  ->update(['SOFT_DEL' => 'Y']);

			$amendDataQueue = true;

			for($crfSeq=0;count($amendCrfData)>$crfSeq;$crfSeq++){

				if($amendCrfData[$crfSeq]['accNo'] == ''){

					$amendCrfData[$crfSeq]['accNo'] = null;
				}


				$amdQueExtData = DB::table('AMEND_QUEUE')->select('FIELD_NAME','SOFT_DEL','TAG','ACCOUNT_NO','CRF_ID','NEW_VALUE')
														 ->where('CRF',$amendUniqueNumber)
														 ->where('FIELD_NAME',$amendCrfData[$crfSeq]['fieldName'])
														 ->where('ACCOUNT_NO',$amendCrfData[$crfSeq]['accNo'])
														 ->get()
														 ->toArray();

				if(count($amdQueExtData)>0){

					$amendcrfId = $amdQueExtData[0]->crf_id;

					if(isset($amendCrfData[$crfSeq]['insertNo']) && $amendCrfData[$crfSeq]['insertNo'] == 'Y'){
						$softDel = 'Y';
					}else{
						$softDel = 'N';
					}
					
					$amendDataQueue = DB::table('AMEND_QUEUE')->where('CRF',$amendUniqueNumber)
															  ->where('FIELD_NAME',$amendCrfData[$crfSeq]['fieldName'])
															  ->where('ACCOUNT_NO',$amendCrfData[$crfSeq]['accNo'])
															  ->update(['NEW_VALUE' => $amendCrfData[$crfSeq]['newValue'],
																		'NEW_VALUE_DISPLAY' => $amendCrfData[$crfSeq]['newDisplayValue'],
																		'UPDATED_AT'=>$created_at,
																		'UPDATED_BY' => Session::get('userId'),
																		'ADDITION_DATA_EKYC' => $amendCrfData[$crfSeq]['id'] == 11?$getaddProfComu : $getIdProfData,
																		'TAG' => $amendCrfData[$crfSeq]['type2'],
																		'SOFT_DEL' => $softDel]);
					}else{

						if(isset($amendCrfData[$crfSeq]['insertNo']) && $amendCrfData[$crfSeq]['insertNo'] == 'Y'){

						}else{

							// echo '<pre>';print_r($amendCrfData);exit;
							if(isset($amendCrfData[$crfSeq]['fieldName']) && $amendCrfData[$crfSeq]['fieldName'] == '_AADHAR_NUMBER'){
								$amendCrfData[$crfSeq]['newValue'] = CommonFunctions::decryptRS($amendCrfData[$crfSeq]['newValue'],$tokenKey);
								$amendCrfData[$crfSeq]['newDisplayValue'] = CommonFunctions::decryptRS($amendCrfData[$crfSeq]['newDisplayValue'],$tokenKey);

							}

						$amendDataQueue = DB::table('AMEND_QUEUE')->insert(['CRF' => $amendUniqueNumber,
																			'AMEND_ITEM' => $amendCrfData[$crfSeq]['description'],
																			'FIELD_NAME' => $amendCrfData[$crfSeq]['fieldName'],
																			'ACCOUNT_NO' => $amendCrfData[$crfSeq]['accNo'],
																			'OLD_VALUE' => $amendCrfData[$crfSeq]['oldValue'],
																			'NEW_VALUE' => $amendCrfData[$crfSeq]['newValue'],
																			'AMEND_FIELD' => $amendCrfData[$crfSeq]['amendField'],
																			'CRF_ID' =>  $amendcrfId,
																			'NEW_VALUE_DISPLAY' => $amendCrfData[$crfSeq]['newDisplayValue'],
																			'CREATED_AT' => $created_at,
																			'CREATED_BY' => Session::get('userId'),
																			'ADDITION_DATA_EKYC' => $amendCrfData[$crfSeq]['id'] == 11?$getaddProfComu : $getIdProfData,
																			'TAG' => $amendCrfData[$crfSeq]['type2'],
																			'SOFT_DEL' => 'N',
																			'API_CALL' => $amendCrfData[$crfSeq]['apiCall']]);
						}
					}
			}
			
		return $amendDataQueue;

		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public function dowloadshowDoc($pdfDocName){

		$tempPath = storage_path('uploads/temp');
		$getFile =  $tempPath.'/'.$pdfDocName;

		if(File::exists($getFile)){

			return response()->file($getFile);
		}
	}

	public function emailCRF($crfnumber,$amendUrl = ''){

		try{

			$getcrfData = DB::table('AMEND_MASTER')->select('CRF_STATUS')
												   ->where('CRF_NUMBER',$crfnumber)
												   ->get()
												   ->toArray();
			$getcrfData = (array) current($getcrfData);
		
			if(count($getcrfData)>0){

				$checkStatus = $getcrfData['crf_status'];
				$pdfPath = base_path('/conf_data/crf/'.'DCB-CUBE-'.$crfnumber);	
				$pdfexists =  File::exists($pdfPath);
				$printCall = '';
				if(!$pdfexists){
					$genPDf = Self::genCrf_View_Pdf($crfnumber,'pdf',$amendUrl,$printCall);
				}

				if($pdfexists && $checkStatus == '23'){
					NotificationController::processNotification($crfnumber,'CRF_APPROVED','CRF_SEND_EMAIL','amend',$pdfPath);
				}
			}
		}catch(\Illuminate\Exception\QueryException $e){
			if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}	

	// The below code is shifted to amend web server 20-03-2023 comment

// 	public function amendonline(Request $request,$gethashlink){

// 		$ipAddresses = $request->ip();
// 		$attempt = true;
// 		$message = 'Unauthorized Attempt Detected';

// 		if(Cache::has($ipAddresses)){
// 			$countIpAttemt = Session::get($ipAddresses);  	
// 			Cache::put($ipAddresses,$countIpAttemt+1,now()->addHours(48));

// 		}else{
// 			Cache::put($ipAddresses,1,now()->addHours(48));
// 		}

// 		$amendInstance =  DB::connection('amend');
// 		$getdecodehashlink = base64_decode($gethashlink);

// 		//----------------attempt success can't check verification again--------------\\

// 		$checkCustomeramend =  $amendInstance->table('CUSTOMER_AMEND')->where('URL',$getdecodehashlink)
// 																	  // ->whereNotNull('STATUS')
// 																	  ->where('STATUS','Y')
// 																	  ->get()
// 																	  ->toArray();
		
// 		if(count($checkCustomeramend) > 0){
// 			$attempt = false;
// 			$message = 'Record Already Processed';
// 		}

// 		$capture_link =  substr($getdecodehashlink,0,64);
// 		$decodeString = substr($getdecodehashlink,66);
// 		$check =  substr($getdecodehashlink,62,2);

// 		if(substr(crc32($capture_link.$decodeString),0,2) != $check){
// 			$attempt = false;
// 		}

// 		if($attempt == false){
// 			echo "<div class='container RefreshRestrictMsg' style='
//                     width: 63%;
//                     margin: 0 auto;
//                     height: 4em;
//                     padding: 2em;
//                     text-align: center;
//                     border-radius: 10px red;
//                     border-radius: 6px;
//                     margin-top: 12em;
//                     background-color: #fff0d3;
//                     font-family:Arial;
//                     line-height: 35px;'>

//                     <p style='margin-top: 0%;
//                     font-size: 1.375rem;
//                     font-weight: 500;'>".$message."</p>
                 
//                   </div>";
//             // CommonFunctions::saveUserLog(Session::get('userId'),$hashlinkId,basename(url()->current()));
//             return false;
// 		}else{

// 			$decryptStringArray =  EncryptDecrypt::AES256Decryption($decodeString,'amend-4');
// 			$decryptStringArray =  explode('|',$decryptStringArray);
// 			$amendInstance =  DB::connection('amend');
// 			$otpNumber = str_pad(mt_rand(0,999999),6,'0',STR_PAD_LEFT);
// 			Cache::put($decryptStringArray[1],$otpNumber,now()->addMinutes(10));

// 			$url = URL::to('/');
// 			echo "
// 	        	 <html>
// 	        	     <meta charset='utf-8' name='base_url' content=".$url.">
// 	    				<meta name='cookie' content='{{Cookie::get('token')}}'>
// 	    				<input type='hidden' id='hashlinkid' value=".$gethashlink.">
// 	        	 	<div>
// 	                 <img class='img-fluid' src='http://165.232.188.115/FACTOR_26_09_2022/public/assets/images/dcb-logo.svg' alt='DCB Logo' style='margin-top:60px; margin-left:80px;'>
// 	                 <h5 style='display:inline-block; margin-left:963px; font-size:19px;'>CUSTOMER APPROVAL FORM </h5>
// 	            	</div>
// 	        	 <div class='container RefreshRestrictMsg' style='
// 	                    width: 63%;
// 	                    margin: 0 auto;
// 	                    height: 4em;
// 	                    padding: 8em;
// 	                    text-align: center;
// 	                    border-radius: 10px red;
// 	                    border-radius: 6px;
// 	                    margin-top: 4em;
// 	                    background-color: #fff0d3;
// 	                    font-family:Arial;
// 	                    line-height: 35px;'>

// 	                    <p style='margin-top: -40px; font-size: 1.375rem;
// 	                    font-weight: 500;'> Dear Customer,<br> A verification token has been sent to your Registered Mobile Number. Please provide below to approve your request.</p>
// 	                    <input type='password' id='otpValue' maxlength='6'> 
// 	                    <br>
// 	                    <button type='submit' style=' height: 28px;width: 95px; margin-top: 18px;' onclick='submit()'>Submit</button>
// 	                  </div>

// 	                  <script src='http://165.232.188.115/FACTOR_26_09_2022/public/components/jquery/js/jquery.min.js
// 	                  '></script>
// 	                  <script src='http://165.232.188.115/FACTOR_26_09_2022/public/custom/js/app.js'></script>
// 	                 <script>
// 	                 function submit(){
// 						var amendOtp = [];
// 						amendOtp.data = {};
// 						amendOtp.url = '/bank/saveAmendOTP';
// 						amendOtp.data['otp_value'] = $('#otpValue').val();
// 						amendOtp.data['hash_link'] = $('#hashlinkid').val();
// 						amendOtp.data['functionName'] = 'amendOtpCallBack'; 
// 						crudAjaxCall(amendOtp);
// 						return false;
// 					}
// 	                 </script></html>";
//         }
//     }

// //---- code to review and  logic --\\

//     public function saveAmendOTP(Request $request){

//     	$requestData = $request->get('data');
//     	$getOtp =  $requestData['otp_value'];
//     	$hash_capture_link = $requestData['hash_link'];

//     	$gethaslinkData =  base64_decode($hash_capture_link);
//     	$capture_link =  substr($gethaslinkData,0,64);
//     	$check = substr($gethaslinkData,64,2);
//     	$decodeString = substr($gethaslinkData,66);

//     	if($decodeString == '' || $check == '' || $capture_link == ''){
//             return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
//         }

//         if(substr(crc32($capture_link.$decodeString),0,2) != $check){
//             return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
//         }

//         $amendInstance = DB::connection('amend');

//         $decodeStringData = EncryptDecrypt::AES256Decryption($decodeString,'amend-4');
//         $decodeStringData = explode('|',$decodeStringData);
//         $checkCustomeramend = $amendInstance->table('CUSTOMER_AMEND')->where('URL',$decodeStringData[1])->get()->toArray();

//         if(count($checkCustomeramend)>0){
//             return json_encode(['status'=>'fail','msg'=>'Record Already Updated','data'=>[]]);
//         }

//         $checkOtp = '999999';

//         if($checkOtp != $getOtp){
//             return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);

//         }else{

//         	$encryptCache = EncryptDecrypt::AES256Encryption($decodeStringData[0].'|'.$getOtp,$decodeStringData[0]);
//         	$amendDBConnection = $amendInstance->table('CUSTOMER_AMEND')->insert(['URL' => $gethaslinkData,'ENCRYPT_CACHE'=>$encryptCache,'STATUS' => 'Y','CREATED_AT' => Carbon::now()]);

// 			if($amendDBConnection){
//             	return json_encode(['status'=>'success','msg'=>'CRF VERIFIED SUCCESSFULLY','data'=>[]]);
// 			}
//         }
//     }

    //------------------domainname black list------------------------\\

    public function checkvaliddomain(Request $request){

    	try{
	    	if($request->ajax()){

	    		$requestData = $request->get('data');
	    		$getEmail =  strtolower($requestData['email_Id']);
	    		$getdomainName = explode('@',$getEmail);
	    		$checkDomainId = isset($getdomainName[1]) && $getdomainName[1] != "" ? $getdomainName[1] : '';
	    		
	    		if($checkDomainId == ''){
	    			return json_encode(['status'=>'fail','msg'=>'Please Enter Valid Mail_Id.','data'=>[]]);
	    		}

	    		$domainListCount =  DB::table('RESTRICTED_DOMAIN_ID')->where(DB::raw('LOWER(DOMAIN_ID)'),$checkDomainId)
	    															 ->count();
	    		if($domainListCount == '0'){
	    			return json_encode(['status'=>'success','msg'=>'Email Validated.','data'=>[]]);
	    		}else{
	    			return json_encode(['status'=>'fail','msg'=>'Please Enter Valid Mail_Id.','data'=>[]]);
	    		}

	    	}
	    }catch(\Illuminate\Exception\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
			return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

    //------------------end domainname---------------------------------\\

    //-----------------------sbaccountinq get information-----------------\\

    public static function SBAccountInq($accountNo){

    	$getSbAccountInq = AmendApi::SBAcctInq($accountNo);

    	// echo "<pre>";print_r($getSbAccountInq);exit;
    	$accountArray = array();
    	
    	if(isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']) && count($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']) >0){

	    	$accountArray['accountDetails']['regNum'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['regNum']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['regNum']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['regNum'] :'';

	    	$accountArray['accountDetails']['nomineeName'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeName']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeName']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeName']:'';

	    	$accountArray['accountDetails']['nomineeBirthDt'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeBirthDt']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeBirthDt']) == 'string'? date('d-m-Y',strtotime(substr($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeBirthDt'],0,10))):'';

	    	$accountArray['accountDetails']['relType'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['relType']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['relType']) =='string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['relType'] :'';

	    	$accountArray['accountDetails']['addr1'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['addr1']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['addr1']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['addr1'] :'';

	    	$accountArray['accountDetails']['addr2'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['addr2']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['addr2']) =='string'? $getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['addr2'] : '';

	    	$accountArray['accountDetails']['postalCode'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['postalCode']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['postalCode']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['postalCode'] :'';

			if($accountArray['accountDetails']['postalCode'] != ''){

				$getpinData = AmendRules::getPincodeDetails($accountArray['accountDetails']['postalCode']);
			}
	    	// $accountArray['accountDetails']['city'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['city']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['city']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['city'] : '';
			$accountArray['accountDetails']['city'] = $getpinData['citydesc'];
	    	// $accountArray['accountDetails']['stateProv'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['stateProv']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['stateProv']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['stateProv'] :'';
			$accountArray['accountDetails']['stateProv'] = $getpinData['statedesc'];
	    	// $accountArray['accountDetails']['country'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['country']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['country']) == 'string' ? $getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineeContactInfo']['postAddr']['country'] : '';
			$accountArray['accountDetails']['country'] = $getpinData['countrydesc'];
	    	// $accountArray['accountDetails']['modeOfOper'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['modeOfOper']) &&
	    	// 	gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['modeOfOper']) == 'string' ? $getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['modeOfOper'] : '';
	    	// $accountArray['accountDetails']['nomineePercent'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineePercent']['value']) &&
	    	// 	gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineePercent']['value']) =='string' ? $getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['nomineePercent']['value'] : '';

	    }
	    
	    	if(isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianCode']) && $getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianCode'] != ''){

		    	$accountArray['accountDetails']['guardianCode'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianCode']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianCode']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianCode']:'';

		    	$accountArray['accountDetails']['guardianName'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianName']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianName']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianName']:'';

		    	$accountArray['accountDetails']['g_addr1'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['addr1']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['addr1']) == 'string' ?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['addr1'] :'';

		    	$accountArray['accountDetails']['g_addr2'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['addr2']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['addr2']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['addr2'] :'';
		    	$accountArray['accountDetails']['g_postalCode'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['postalCode']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['postalCode']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['postalCode'] :'';
				
				if($accountArray['accountDetails']['g_postalCode'] != ''){

					$getpinData = AmendRules::getPincodeDetails($accountArray['accountDetails']['g_postalCode']);
				}

		    	// $accountArray['accountDetails']['g_city'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['city']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['city']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['city'] :'';
				$accountArray['accountDetails']['g_city'] = $getpinData['citydesc'];

		    	// $accountArray['accountDetails']['g_stateProv'] = isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['stateProv']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['stateProv']) == 'string' ?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['stateProv'] :'';
				$accountArray['accountDetails']['g_stateProv'] = $getpinData['statedesc'];

				$accountArray['accountDetails']['g_country'] = $getpinData['countrydesc'];
				
		    	// $accountArray['accountDetails']['g_country'] =isset($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['country']) && gettype($getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['country']) == 'string'?$getSbAccountInq['sbAcctInqResponse']['sbAcctInqRs']['nomineeInfoRec']['0']['guardianInfo']['guardianContactInfo']['postAddr']['country'] :'';
	    	}
	    // echo "<pre>";print_r($accountArray);exit;
	   
    	return $accountArray;
    }

    //----------------------end sbaccountinq------------------------------\\

    public function amendpanisvalid(Request $request){
    	try{
	    	if($request->ajax()){
	    		$requestData = $request->get('data');
				$currCustomerDetails = Session::get('currCustomerDetails');
				$panDetails[0]['panNo'] = $requestData['pancard_no'];
				$panDetails[0]['name'] = strtoupper($currCustomerDetails['data']['customerDetails']['CUST_NAME']);
				$panDetails[0]['dob'] = Carbon::parse($currCustomerDetails['data']['customerDetails']['DATE_OF_BIRTH'])->format('d/m/Y');

	    		$pancard = Api::newPanIsValid($panDetails);
				// echo "<pre>";print_r($pancard);exit;
	    		 if(isset($pancard['status']) && $pancard['status']== "success"){
	                if($pancard['data'][0]['panStatus'] == "E"){ // If PAN Exist and is Valid!
	                    $bypassAadharCheck = true;
                        if($pancard['data'][0]['seedingStatus'] == "Y"  || $pancard['data'][0]['seedingStatus'] == "NA"){
	                       return json_encode(['status'=>'success','msg'=>'NSDL: Pancard OK','data'=>$pancard]);
	                    }else{
	                       return json_encode(['status'=>'fail','msg'=>'NSDL: Pancard not linked','data'=>$pancard]);
	                    }
	                }else{
	                    return json_encode(['status'=>'fail','msg'=>'NSDL: Invalid Pancard','data'=>[]]);
	                }
	            }else{
	                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	            }
	    	}
	    }catch(\Illuminate\Database\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

    public function checkvalidcustid(Request $request){
    	try{
	    	if($request->ajax()){
	    		$requestData = $request->get('data');
	    		$cutomerId = $requestData['customer_id'];
	    		$url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
		  		$getCustomerDetails = Api::customerdetails($url,'customerID',$cutomerId);

		 		if(!isset($getCustomerDetails['data']['status']) || strtoupper($getCustomerDetails['data']['status']) != 'SUCCESS'){
	  				return json_encode(['status'=>'fail','msg'=>$getCustomerDetails['data']['message'],'data'=>[]]);
	  			}
				
				if($requestData['checkExist'] == 'Y'){
					$getJoinCustId = Session::get('currAccountDetails')['accountDetails']['JOINTHOLDERS_CUSTID'];
					$getJoinCustId = explode('|',$getJoinCustId);
					
					if(!in_array($cutomerId,$getJoinCustId)){
						return json_encode(['status'=>'fail','msg'=>'Please Enter Valid Customer ID.','data'=>[]]);
					}else{
	    		return json_encode(['status'=>'success','msg'=>'Valid Customer ID.','data'=>[]]);
	    	}

				}
	    		return json_encode(['status'=>'success','msg'=>'Valid Customer ID.','data'=>[]]);
	    	}
	    }catch(\Illuminate\Database\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

    public function chkrestcountry(Request $request){
    	try{
    		if($request->ajax()){
				$requestData = $request->get('data');
				$countryId = $requestData['county_id'];

				$getRisk = DB::table('COUNTRIES')->select('RISK_CATEGORY')
												->where('ID',$countryId)
												->get()
												->toArray();
				$getRisk = (array)current($getRisk);

				if($getRisk['risk_category'] != 'H'){

					return json_encode(['status'=>'success','msg'=>'Successfully selected country.','data'=>[]]);
				}else{
					return json_encode(['status'=>'fail','msg'=>'select restricted countries.','data'=>[]]);
				}

    		}
    	 }catch(\Illuminate\Database\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

	public function checkvalidacctno(Request $request){
		try{
			$requestData = $request->get('data'); 
			$cust_acctNo = $requestData['account_no'];
			$getAccountDetails = Api::accountNumberDetails($cust_acctNo);

			if(!isset($getAccountDetails['accountDetails']['status']) || strtoupper($getAccountDetails['accountDetails']['status']) != 'SUCCESS')	 
				{
				return json_encode(['status'=>'fail','msg'=>'API Error! Please try again','data'=>[]]);
			}
			
			$getCustomerID = Session::get('currCustomerDetails')['data']['customerDetails']['CUST_ID'];
			
			if($getAccountDetails['accountDetails']['CUSTOMER_ID'] == $getCustomerID){
				return json_encode(['status'=>'success','msg'=>'Successfully prcoess the account number.','data'=>[]]);
			}

			$joinCustomerId = explode('|',$getAccountDetails['accountDetails']['JOINTHOLDERS_CUSTID']);
			
			if(in_array($getCustomerID,$joinCustomerId)){
				return json_encode(['status'=>'success','msg'=>'Successfully prcoess the account number.','data'=>[]]);
			}
			return json_encode(['status'=>'fail','msg'=>'Please enter valid account number.','data'=>[]]);


		}catch(\Illuminate\Database\QueryException $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}
	
	public function checkvalidaadhaarNo(Request $request){
		$requestData = $request->get('data'); 
		$aadharNo = $requestData['aadhaar_no'];
		echo "<pre>";print_r($aadharNo);exit;

		
	}
}

?>