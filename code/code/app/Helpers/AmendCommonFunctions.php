<?php
namespace App\Helpers;

use Illuminate\Support\Arr;
use Session;
use Carbon\Carbon;
use DB;
use App\Helpers\AmendApi;
use Route;
use Cache;
use File;

	
class AmendCommonFunctions {

	public static function apiStructure($crf_id,$crf_number,$customerID){
		try{
   		$amendDataCall = array();

   		$amendData = DB::table('AMEND_QUEUE')->select('FIELD_NAME','NEW_VALUE','AMEND_FIELD','OLD_VALUE')
   											 ->where('CRF',$crf_number)
   											 ->where('TAG','CRM')
   											 ->orderBy('id')
   											 ->get()
   											 ->toArray();

   	
   		$amendItemMatser = DB::table('AMENDITEMS')->select('FINACLE_FIELD','API_FIELD_SECTION')
   												 ->where('TYPE3','!=','KYC Updation')
   												 ->get()
   												 ->toArray();

   		$amendMasterData = DB::table('AMEND_MASTER')->select('ADDITIONAL_DATA','APPROVAL','ID','CUSTOMER_ID','CACHE_DATA')
   													->where('CRF_NUMBER',$crf_number)
   													->get()
   													->toArray();
   		$amendMasterData = (array) current($amendMasterData);
		//cache data decrypted to and use for kyc rference number and schemecode logic 
		
   		// doc settled 
   			 $customerID = isset($amendMasterData['customer_id']) && $amendMasterData['customer_id'] != ''?$amendMasterData['customer_id']:'';
            $crf_id = isset($amendMasterData['id']) && $amendMasterData['id'] !=''?$amendMasterData['id']:'';

            $refernceNumber = '';
            $proofId = '';
            $expDate = '';
            $addData = array();
            $amendaddData = array();
			// echo "<pre>";print_r($amendMasterData);exit;
            if(strtolower($amendMasterData['approval']) == 'offline' || strtolower($amendMasterData['approval']) == 'online'){
                $addData = json_decode($amendMasterData['additional_data'],true);
				$ekycNumber = isset($addData['ekyc_rrn']) && $addData['ekyc_rrn'] != ''? $addData['ekyc_rrn']:'';

				if($ekycNumber != ''){
					$vaultNo = $ekycNumber;
					$proofId = '9';
					$expDate = '';
					$amendIdDataArray = self::addExistingIdOfData($proofId,$customerID,$crf_id,$vaultNo,$expDate = '');
					array_push($amendaddData,$amendIdDataArray);
	
					$aadhaarRef = json_decode(base64_decode($amendMasterData['cache_data']),true);
					$amendIdDataArray1 = self::addExistingIdOfData('1',$customerID,$crf_id,$aadhaarRef['aadharRefNumber'],$expDate = '');
					array_push($amendaddData,$amendIdDataArray1);
				}
				
                $refernceNumber = (isset($addData['proofIdData']['id_code']) && $addData['proofIdData']['id_code'] != '') ? $addData['proofIdData']['id_code'] : '';
               
               if($refernceNumber != ''){

	                $addData['proofIdData']['id_date'] = (isset($addData['proofIdData']['id_date']) && $addData['proofIdData']['id_date'] != '') ? $addData['proofIdData']['id_date'] : '';
	                $proofId = $addData['proofIdData']['proof_id'] = (isset($addData['proofIdData']['proof_id']) && $addData['proofIdData']['proof_id'] != '') ? $addData['proofIdData']['proof_id'] : '';

	                if($proofId == 1){

	                    $refernceNumber = self::amendgetVaultRefNumber($refernceNumber,$crf_number);
	                    if($refernceNumber['status'] == 'success'){
	                        $addData['proofIdData']['id_code'] = $refernceNumber['data']['refernceNumber'];
	                    }else{
	                        return json_encode(['status'=>$refernceNumber['status'],'msg' => $refernceNumber['msg'],'data'=>[]]);
	                    }
	                }
               }

                $customerID = isset($amendMasterData['customer_id']) && $amendMasterData['customer_id'] != ''?$amendMasterData['customer_id']:'';
                $crf_id = isset($amendMasterData['id']) && $amendMasterData['id'] !=''?$amendMasterData['id']:'';
                
                if(isset($addData['proofIdData']['id_code']) && $addData['proofIdData']['id_code'] != ''){
                    $proofId = $addData['proofIdData']['proof_id'];
                    $vaultNo = $addData['proofIdData']['id_code'];
                    $expDate = $addData['proofIdData']['id_date'];
                    $issusDate = $addData['proofIdData']['issues_id_date'];
                    $amendIdDataArray = self::addExistingIdOfData($proofId,$customerID,$crf_id,$vaultNo,$expDate,$issusDate);
                    array_push($amendaddData,$amendIdDataArray);
                } 
				//prakash sir ref other if choice ekyc give number and without number not passing in finacle for amendemd
                if((isset($addData['comuproofAddData']['addproof_id']) && $addData['comuproofAddData']['addproof_id'] != '29') && (isset($addData['comuproofAddData']['addproof_no']) && $addData['comuproofAddData']['addproof_no'] != '')){	
			
                    $proofId = $addData['comuproofAddData']['addproof_id'];
                    $vaultNo = $addData['comuproofAddData']['addproof_no'];
                    $expDate = '';
					$issusDate = '';
                    $amendIdDataArray = self::addExistingIdOfData($proofId,$customerID,$crf_id,$vaultNo,$expDate = '',$issusDate='');
                    array_push($amendaddData,$amendIdDataArray);
                }

            }else{
                $refernceNumber = json_decode($amendMasterData['additional_data'],true);
                $vaultNo = $refernceNumber['ekyc_rrn'];
                $proofId = '9';
                $expDate = '';
				$issusDate = '';
				if($vaultNo != ''){
					$amendIdDataArray = self::addExistingIdOfData($proofId,$customerID,$crf_id,$vaultNo,$expDate = '',$issusDate='');
                array_push($amendaddData,$amendIdDataArray);
				$aadhaarRef = json_decode(base64_decode($amendMasterData['cache_data']),true);
					$amendIdDataArray1 = self::addExistingIdOfData('1',$customerID,$crf_id,$aadhaarRef['aadharRefNumber'],$expDate = '',$issusDate='');
                array_push($amendaddData,$amendIdDataArray1);
				}

            }
			
   		//end doc settled
            for($seqDoc=0;count($amendaddData)>$seqDoc;$seqDoc++){
			   
			   if(isset($amendaddData[$seqDoc]['amend_field']) && $amendaddData[$seqDoc]['amend_field'] != ''){
				   
            	$amendaddData[$seqDoc]['amend_field'] = $amendaddData[$seqDoc]['amend_field'].'|'.$seqDoc;
            }
            }
			
   		$addCount = -1;
	   	$useAddCounter = false;
		$stateDesc = '';
		$cityDesc = '';
   		for($i=0;count($amendData)>$i;$i++){
			
			//09_03_2023 pincode data to call state and city
			if($amendData[$i]->field_name == 'CUST_PERM_PIN_CODE'){
				$getDetails = Self::getPincodeDetails($amendData[$i]->new_value);
				$stateDesc = $getDetails['statedesc'];
				$cityDesc = $getDetails['citydesc'];
				$countryDesc =  $getDetails['countrydesc'];
			}

			if($amendData[$i]->field_name == 'CUST_PERM_STATE_CODE'){
				$amendData[$i]->new_value =  $stateDesc;
			}
			
			if($amendData[$i]->field_name == 'CUST_PERM_CITY_CODE'){
				$amendData[$i]->new_value =  $cityDesc;
			}

			if($amendData[$i]->field_name == 'CUST_PERM_CNTRY_CODE'){
				$amendData[$i]->new_value =  $countryDesc;
			}

			//25_05_2023 pincode data to call state and city
			if($amendData[$i]->field_name == 'CUST_COMU_PIN_CODE'){
				$getDetails = Self::getPincodeDetails($amendData[$i]->new_value);
				$stateDesc = $getDetails['statedesc'];
				$cityDesc = $getDetails['citydesc'];
				$countryDesc =  $getDetails['countrydesc'];
			}

			if($amendData[$i]->field_name == 'CUST_COMU_STATE_CODE'){
				$amendData[$i]->new_value =  $stateDesc;
			}
			
			if($amendData[$i]->field_name == 'CUST_COMU_CITY_CODE'){
				$amendData[$i]->new_value =  $cityDesc;
			}

			if($amendData[$i]->field_name == 'CUST_COMU_CNTRY_CODE'){
				$amendData[$i]->new_value =  $countryDesc;
			}

	   	$counter = 0;
		   		//-------------------------Special case for field-------------------------\\

			for($j=0;count($amendItemMatser)>$j;$j++){

				if(in_array($amendData[$i]->field_name,explode('|',$amendItemMatser[$j]->finacle_field))){

		   			$amendDataCall[$i]['field_name'] = $amendData[$i]->field_name;
		   			$amendDataCall[$i]['new_value'] = $amendData[$i]->new_value;

		   			if(in_array($amendData[$i]->field_name,explode('|','CUST_PERM_ADDR1|CUST_PERM_ADDR2|CUST_PERM_ADDR3|CUST_PERM_PIN_CODE|CUST_PERM_CITY_CODE|CUST_PERM_STATE_CODE|CUST_PERM_CNTRY_CODE'))){
	   					
					if($amendData[$i]->field_name == 'CUST_PERM_ADDR1'){
	   					$addCount++;
	   					$useAddCounter = true;
	   				}
	   				if($useAddCounter){
	   					$counter = $addCount;
	   				}
		   			$amendDataCall[$i]['amend_field'] = $amendItemMatser[$j]->api_field_section.$counter.'|'.$amendData[$i]->amend_field;

		   				$amendDataCall[$i]['amend_field'] = $amendItemMatser[$j]->api_field_section.$addCount.'|'.$amendData[$i]->amend_field;

		   			}elseif(in_array($amendData[$i]->field_name,explode('|','CUST_COMU_ADDR1|CUST_COMU_ADDR2|CUST_COMU_ADDR3|CUST_COMU_PIN_CODE|CUST_COMU_CITY_CODE|CUST_COMU_STATE_CODE|CUST_COMU_CNTRY_CODE'))){
		   				
		   			$counter++;
					if($amendData[$i]->field_name == 'CUST_COMU_ADDR1'){
						
	   				$addCount++;
		   			$useAddCounter = true;
		   					
	   				}
	   				if($useAddCounter){
	   					$counter = $addCount;
	   				}
		   				$amendDataCall[$i]['amend_field'] = $amendItemMatser[$j]->api_field_section.$counter.'|'.$amendData[$i]->amend_field;

		   			}elseif($amendData[$i]->field_name == 'EMAIL_ID'){
		   				$amendDataCall[$i]['amend_field'] = $amendItemMatser[$j]->api_field_section.$counter.'|'.$amendData[$i]->amend_field;
		   				$emailPresent = true;

		   			}elseif($amendData[$i]->field_name == 'CUST_PAGER_NO'){
		   				if(isset($emailPresent) && $emailPresent){
		   					$counter++;
		   				}
		   				$amendDataCall[$i]['amend_field'] = $amendItemMatser[$j]->api_field_section.$counter.'|'.$amendData[$i]->amend_field;

		   			}else{
		   				$amendDataCall[$i]['amend_field'] = $amendItemMatser[$j]->api_field_section.''.$amendData[$i]->amend_field;
		   			}
				}
			}			
   		}

   			//------------------cust required data added---------------\\
   
   		$amendDataCall = Self::addDataAmendApi($amendDataCall,$customerID,$crf_id,$amendaddData);
   		if($amendDataCall == 'fail'){
   			return 'fail';
   		}
   		//------------------end -------------------\\
   
   		$apiArray = array();
   		
	   		for($seqData=0;count($amendDataCall)>$seqData;$seqData++){
			
	   			$amendNewVal = Self::getCallData($amendDataCall[$seqData]['new_value'],$amendDataCall[$seqData]['field_name']);
	   			$amendField = explode('|', $amendDataCall[$seqData]['amend_field']);
				$apiArray = Self::addToApiStruct($apiArray,$amendField,$amendNewVal);
	   		}
	   	return $apiArray;
	
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','apiStructure',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}		
	   }


	public static function apiAcmStructure($accountNo,$crf_number){
		try{
		$amendDataCall = array();
   		$amendItemMatser = DB::table('AMENDITEMS')->select('FINACLE_FIELD','API_FIELD_SECTION')
   												  ->get()
   												  ->toArray();

   		$apiArray = array();
		$amendDataCall = self::amendAcmData($accountNo,$crf_number);

		$amendDataArray = array();

			for($amdAccSeq=0;count($amendDataCall)>$amdAccSeq;$amdAccSeq++){

				for($amdItem=0;count($amendItemMatser)>$amdItem;$amdItem++){

					if(in_array($amendDataCall[$amdAccSeq]->field_name,explode('|',$amendItemMatser[$amdItem]->finacle_field))){

			   			$amendDataArray[$amdAccSeq]['field_name'] = $amendDataCall[$amdAccSeq]->field_name;
			   			$amendDataArray[$amdAccSeq]['new_value'] = self::getCallData($amendDataCall[$amdAccSeq]->new_value,$amendDataCall[$amdAccSeq]->field_name);

						$amendacmPath =  self::amendAcmPathCall($amendItemMatser[$amdItem]->api_field_section,$amendDataCall[$amdAccSeq]->field_name);

			   			$amendDataArray[$amdAccSeq]['amend_field'] = $amendacmPath.''.$amendDataCall[$amdAccSeq]->amend_field;
			   			$amendDataArray[$amdAccSeq]['account_no'] = $amendDataCall[$amdAccSeq]->account_no;

					}
				}
			}

			$amendDataArray = self::addAcmAddData($amendDataArray,$crf_number);
			//-------------------Additional data add in acm level---------------------\\

			//-------------------Additional data add in acm level---------------------\\
			
			for($amdC=0;count($amendDataArray)>$amdC;$amdC++){

				if($amendDataArray[$amdC]['new_value'] != 'Initiated'){

					$amendNewVal = $amendDataArray[$amdC]['new_value'];
					$amendField = explode('|', $amendDataArray[$amdC]['amend_field']);	
					$apiArray = Self::addToApiStruct($apiArray,$amendField,$amendNewVal);
				}
			}
				
			return $apiArray;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','apiAcmStructure',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}	
	}	

	public static function addToApiStruct($main_array, $keys, $value){ 

	    $tmp_array = &$main_array;

		    while( count($keys) > 0 ){   

		        $k = array_shift($keys); 

		        if(!is_array($tmp_array)){

		            $tmp_array = array();
		        }

		        $tmp_array = &$tmp_array[$k];
		    }	
	       
	    	$tmp_array = $value;
	  
	    return $main_array;
	}

	public static function getCallData($getAmendData,$getFinacleField){
		try{
		$amendValue = '';
		switch($getFinacleField){

			case 'CUST_NAME':

					$getName = CommonFunctions::getFML_Name($getAmendData);
					$amendValue = $getName['first_name'].' '.$getName['middle_name'].' '.$getName['last_name'];
			break;

			case 'DATE_OF_BIRTH':

					$amendValue = Carbon::parse($getAmendData)->format('Y-m-d\TH:i:s.v');
			break;

			case 'CUST_COMU_CITY_CODE':

					$amendValue = Self::getCityCode($getAmendData);
			break;

			case 'CUST_COMU_STATE_CODE':
					
					$amendValue = Self::getStateCode($getAmendData);
			break;

			case 'CUST_COMU_CNTRY_CODE':

					$amendValue = Self::getCountryCode($getAmendData);
			break;

			case 'CUST_PERM_CITY_CODE':

					$amendValue = Self::getCityCode($getAmendData);
			break;

			case 'CUST_PERM_STATE_CODE':
					
					$amendValue = Self::getStateCode($getAmendData);
			break;

			case 'CUST_PERM_CNTRY_CODE':

					$amendValue = Self::getCountryCode($getAmendData);
			break;

			case 'MODE_OF_OPERATION':

					$mop = DB::table('MODE_OF_OPERATIONS')->select('CODE')->where('ID',$getAmendData)		
	                                                      ->get()->toArray();
	                $mop = current($mop);           
	        		$amendValue = $mop->code;	
			break;

			case 'CUST_TITLE_CODE':
					$title = DB::table('TITLE')->whereId($getAmendData)->get()->toArray();
        			$title = (array) current($title);
        			$amendValue = $title['title'];
			break;

			case 'OCCUPATION':
					$occupationgetId =  DB::table('OCCUPATION')->select('CODE')->where('ID',$getAmendData)->whereIn('ETB_NTB_BOTH',['BOTH','ETB'])->get()->toArray();
        			$occupationgetId = current($occupationgetId);
        			$amendValue = $occupationgetId->code;
			break;

			case '_DOB':
				$amendValue = Carbon::parse($getAmendData)->format('Y-m-d\TH:i:s.v');
			break;

			case 'DateOfNotification':
				$amendValue = Carbon::parse($getAmendData)->format('Y-m-d\TH:i:s.v');
				// $amendValue = '2023-04-02T00:00:00.000';
			break;

			case 'DateOfDeath':
				$amendValue = Carbon::parse($getAmendData)->format('Y-m-d\TH:i:s.v');
				// $amendValue = '2023-04-01T00:00:00.000';

			break;

			case 'nomineeBirthDt':
				$amendValue = Carbon::parse($getAmendData)->format('Y-m-d\TH:i:s.v');
			break;

			case 'g_city':
				$amendValue = Self::getCityCode($getAmendData);
			break;

			case 'g_stateProv':
				$amendValue = Self::getStateCode($getAmendData);
			break;

			case 'g_country':
				$amendValue = Self::getCountryCode($getAmendData);
			break;

			case 'city':
				$amendValue = Self::getCityCode($getAmendData);
			break;

			case 'stateProv':
				$amendValue = Self::getStateCode($getAmendData);
			break;

			case 'country':
				$amendValue = Self::getCountryCode($getAmendData);
			break;

			case 'FATCA_NATIONALITY':
				$amendValue = Self::getFatcaCountryCode($getAmendData);
			break;

			case 'FATCA_CNTRY_OF_RESIDENCE':
				$amendValue = Self::getFatcaCountryCode($getAmendData);
			break;

			case 'FATCA_BIRTHCOUNTRY':	
				$amendValue = Self::getFatcaCountryCode($getAmendData);
			break;

			case 'relType':
				$amendValue = Self::getNomineeRelationshipCode($getAmendData);
			break;

			default:
				$amendValue = $getAmendData;
			break;
		}

		return $amendValue;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getCallData',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function addDataAmendApi($amendDataCall,$customerID,$crf_id,$amendaddData){
		try{
   		$formId = $crf_id;
		$existingIDs = AmendApi::retcustinq($customerID, $formId);
		$url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');
		$getCustomerDetails = Api::customerdetails($url,'customerID',$customerID);
		$getExistingrisk = '';
		if(isset($getCustomerDetails['data']['customerDetails']['FREE_TEXT_10']) && $getCustomerDetails['data']['customerDetails']['FREE_TEXT_10']){
			$getExistingrisk = $getCustomerDetails['data']['customerDetails']['FREE_TEXT_10'];
		}

		$timestamp = Carbon::now()->timestamp;
        $current_timestamp = Carbon::parse($timestamp)->format('Y-m-d\TH:i:s.v');
		$riskCal = array();

		//-------------check for Permanent address or current address--------------\\
		$emailPhoneCount = 0;
		$docIdCount = 0;
		$addCount = 0;
		for($seqDoc=0;count($amendaddData)>$seqDoc;$seqDoc++){
			// echo "<pre>";print_r($amendaddData);exit;
			if(isset($amendaddData[$seqDoc]) && count($amendaddData[$seqDoc])>0){
				
			array_push($amendDataCall,$amendaddData[$seqDoc]);
		}
		}
		
		$deathNameChange = 'N';

		// echo "<pre>";print_r($amendDataCall);exit;
		for($dataSeq=0;count($amendDataCall)>$dataSeq;$dataSeq++){

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'DateOfDeath'){
				$deathNameChange = 'Y';
			}
			//------------------added short name / first name / middle name / prefName /last name-------------\\

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_NAME'){

				$fieldName = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|';
				$getName = CommonFunctions::getFML_Name($amendDataCall[$dataSeq]['new_value']);	
				$shortName = substr($amendDataCall[$dataSeq]['new_value'],0,10);

				$shName['field_name'] = 'ShortName';
		   		$shName['new_value'] = $shortName;
		   		$shName['amend_field'] = $fieldName.'ShortName';
		   		array_push($amendDataCall,$shName);


				$prefName['field_name'] = 'PrefName';
		   		$prefName['new_value'] = $getName['first_name'].' '.$getName['last_name'];
		   		$prefName['amend_field'] = $fieldName.'PrefName';
		   		array_push($amendDataCall,$prefName);

				$firstName['field_name'] = 'FirstName';
		   		$firstName['new_value'] = $getName['first_name'] != '' ? $getName['first_name']:'null';
		   		$firstName['amend_field'] = $fieldName.'FirstName';
		   		array_push($amendDataCall,$firstName);

		   		$middleName['field_name'] = 'MiddleName';
		   		$middleName['new_value'] = $getName['middle_name'] != '' ? $getName['middle_name']:'null';;
		   		$middleName['amend_field'] = $fieldName.'MiddleName';
		   		array_push($amendDataCall,$middleName);

		   		$lastName['field_name'] = 'LastName';

				if($deathNameChange == 'Y'){
					$amendDataCall[$dataSeq]['new_value'] = $amendDataCall[$dataSeq]['new_value'].' DECEASED';
					$lastName['new_value'] = $getName['last_name'] != '' ? $getName['last_name'].' DECEASED':'null';
				}else{
					$lastName['new_value'] = $getName['last_name'] != '' ? $getName['last_name']:'null';
				}
		   		$lastName['amend_field'] = $fieldName.'LastName';
		   		array_push($amendDataCall,$lastName);
			}
			
			 //-----------Permanent check------------------\\

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_PERM_ADDR1'){

				$fieldName = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|RetAddrModDtls|'.$addCount.'|';
				$addCount++;

		   		$currdate = Carbon::now();
		   		$currentDate = Carbon::parse($currdate)->format('Y-m-d\TH:i:s.v');
		   		$startDate['field_name'] = 'StartDt';
		   		$startDate['new_value'] = $currentDate;
		   		$startDate['amend_field'] = $fieldName.'StartDt';
		   		array_push($amendDataCall,$startDate);

		   		$permPostalData['field_name'] = 'addrCategory';
			   		$permPostalData['new_value'] = 'Home';
			   		$permPostalData['amend_field'] = $fieldName.'addrCategory';
			   		array_push($amendDataCall,$permPostalData);

				if(isset($existingIDs['HOME']) &&  $existingIDs['HOME'] != ''){


					$permExiAddData['field_name'] = 'addressID';
			   		$permExiAddData['new_value'] =  $existingIDs['HOME'];
			   		$permExiAddData['amend_field'] = $fieldName.'addressID';
			   		array_push($amendDataCall,$permExiAddData);
				}
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_PERM_ADDR2'){
				
				$amendDataCall[$dataSeq]['new_value'] = $amendDataCall[$dataSeq]['new_value'] != '' ? $amendDataCall[$dataSeq]['new_value']:'null';
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_PERM_ADDR3'){
				
				$amendDataCall[$dataSeq]['new_value'] = $amendDataCall[$dataSeq]['new_value'] != '' ? $amendDataCall[$dataSeq]['new_value']:'null';
			}

			//----------Communication check---------------\\

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_COMU_ADDR1'){

				$fieldName = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|RetAddrModDtls|'.$addCount.'|';
				$addCount++;

				$currdate = Carbon::now();
		   		$currentDate = Carbon::parse($currdate)->format('Y-m-d\TH:i:s.v');
		   		$startDate['field_name'] = 'StartDt';
		   		$startDate['new_value'] = $currentDate;
		   		$startDate['amend_field'] = $fieldName.'StartDt';
		   		array_push($amendDataCall,$startDate);

				$comPostalData['field_name'] = 'addrCategory';
			   		$comPostalData['new_value'] = 'Mailing';
			   		$comPostalData['amend_field'] = $fieldName.'addrCategory';
			   		array_push($amendDataCall,$comPostalData);
		   		
				if(isset($existingIDs['MAILING']) &&  $existingIDs['MAILING'] != ''){
					$comExiAddData['field_name'] = 'addressID';
			   		$comExiAddData['new_value'] =  $existingIDs['MAILING'];
			   		$comExiAddData['amend_field'] = $fieldName.'addressID';
			   		array_push($amendDataCall,$comExiAddData);
				}
			}
			
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_COMU_ADDR2'){
				
				$amendDataCall[$dataSeq]['new_value'] = $amendDataCall[$dataSeq]['new_value'] != ''?$amendDataCall[$dataSeq]['new_value']:'null';
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_COMU_ADDR3'){
				
				$amendDataCall[$dataSeq]['new_value'] = $amendDataCall[$dataSeq]['new_value'] != ''?$amendDataCall[$dataSeq]['new_value']:'null';
			}

			//----------------- email update--------------------\\

			if(isset($amendDataCall[$dataSeq]['field_name']) &&  $amendDataCall[$dataSeq]['field_name'] == 'EMAIL_ID'){

				$fieldName = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|PhoneEmailModData|'.$emailPhoneCount.'|';
				$emailData['field_name'] = 'PhoneOrEmail';
				$emailData['new_value'] = 'EMAIL';
		   		$emailData['amend_field'] = $fieldName.'PhoneOrEmail';
		   		array_push($amendDataCall,$emailData);
				   
				$emailData1['field_name'] = 'PrefFlag';
		   		$emailData1['new_value'] =  'Y';
		   		$emailData1['amend_field'] = $fieldName.'PrefFlag';
		   		array_push($amendDataCall,$emailData1);

		   		$emailData2['field_name'] = 'PhoneEmailtType';
		   		$emailData2['new_value'] =  'COMMEML';
		   		$emailData2['amend_field'] = $fieldName.'PhoneEmailtType';
		   		array_push($amendDataCall,$emailData2);
		   		
		   		if($existingIDs['COMMEML'] != ''){
					   $emailData3['field_name'] = 'PhoneEmailID';
			   		$emailData3['new_value'] =  $existingIDs['COMMEML'];
			   		$emailData3['amend_field'] = $fieldName.'PhoneEmailID';
			   		array_push($amendDataCall,$emailData3);
				}	
				$amendDataCall[$dataSeq]['amend_field'] = $fieldName.'Email';
				$emailPhoneCount++;
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) &&  $amendDataCall[$dataSeq]['field_name'] == 'CUST_COMU_PHONE_NUM_1'){
				$newValue =  $amendDataCall[$dataSeq]['new_value'];
				$fieldName = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|PhoneEmailModData|'.$emailPhoneCount.'|';
				//$fieldNamePer = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|RetAddrModDtls|'.$emailPhoneCount.'|';
				$emailPhoneCount++;
				
				// $amendDataCall[$dataSeq]['amend_field'] = $fieldNamePer.'CellNum';
				$phoneData1['field_name'] = 'PhoneNum';
				$phoneData1['new_value'] = $newValue;
				$phoneData1['amend_field'] = $fieldName.'PhoneNum';
				// array_push($amendDataCall,$phoneData1);

				$phoneData['field_name'] = 'PhoneNumCountryCode';
				$phoneData['new_value'] = '91';
				$phoneData['amend_field'] = $fieldName.'PhoneNumCountryCode';
				array_push($amendDataCall,$phoneData);
				
				$phoneType['field_name'] = 'PhoneOrEmail';
				$phoneType['new_value'] = 'PHONE';
				$phoneType['amend_field'] = $fieldName.'PhoneOrEmail';													
				array_push($amendDataCall,$phoneType);
		   		
				$phoneorEmail['field_name'] = 'PhoneEmailtType';
				$phoneorEmail['new_value'] = 'WORKPH1';
				$phoneorEmail['amend_field'] = $fieldName.'PhoneEmailtType';	
				array_push($amendDataCall,$phoneorEmail);			
			
				$amendDataCall[$dataSeq]['amend_field'] = 'PhoneNumLocalCode';	
				$amendDataCall[$dataSeq]['new_value'] = $newValue;
				$amendDataCall[$dataSeq]['amend_field'] = $fieldName.'PhoneNumLocalCode';

				if($existingIDs['WORKPH1'] != ''){												

					$phoneExt['field_name'] = 'PhoneEmailID';
					$phoneExt['new_value'] = $existingIDs['WORKPH1'];
					$phoneExt['amend_field'] = $fieldName.'PhoneEmailID';					
					array_push($amendDataCall,$phoneExt);					
				}
			}
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'CUST_PAGER_NO'){

				$newValue =  $amendDataCall[$dataSeq]['new_value'];
				$fieldName = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|PhoneEmailModData|'.$emailPhoneCount.'|';
				$emailPhoneCount++;

				$phoneData['field_name'] = 'PhoneNumCountryCode';
				$phoneData['new_value'] = '91';
				$phoneData['amend_field'] = $fieldName.'PhoneNumCountryCode';
				array_push($amendDataCall,$phoneData);
				
				$phoneType['field_name'] = 'PhoneOrEmail';
				$phoneType['new_value'] = 'PHONE';
				$phoneType['amend_field'] = $fieldName.'PhoneOrEmail';													
				array_push($amendDataCall,$phoneType);
				
				$phoneorEmail['field_name'] = 'PhoneEmailtType';
				$phoneorEmail['new_value'] = 'CELLPH';
				$phoneorEmail['amend_field'] = $fieldName.'PhoneEmailtType';	
				array_push($amendDataCall,$phoneorEmail);			

				$phoneCode['field_name'] = 'PhoneNumLocalCode';
				$phoneCode['new_value'] = $newValue;
				$phoneCode['amend_field'] = $fieldName.'PhoneNumLocalCode';
				array_push($amendDataCall,$phoneCode);					
					
				if($existingIDs['CELLPH'] != ''){												

					$phoneExt['field_name'] = 'PhoneEmailID';
					$phoneExt['new_value'] = $existingIDs['CELLPH'];
					$phoneExt['amend_field'] = $fieldName.'PhoneEmailID';					
					array_push($amendDataCall,$phoneExt);
				}else{
					$phoneExt['field_name'] = 'PrefFlag';
					$phoneExt['new_value'] = 'Y';
					$phoneExt['amend_field'] = $fieldName.'PrefFlag';					
					array_push($amendDataCall,$phoneExt);
				}
			}
			if(isset($amendDataCall[$dataSeq]['field_name']) &&  $amendDataCall[$dataSeq]['field_name'] == 'OCCUPATION'){
				
				$occId = $amendDataCall[$dataSeq]['new_value'];
				$checkNewRisk = DB::table('OCCUPATION')->select('RISK_CATEGORY')
													   ->where('ID',$occId)
													   ->get()
													   ->toArray();
													   $getNewRisk = (array)current($checkNewRisk);
													   $getValueRisk = self::getRiskScore($getNewRisk['risk_category']);
													   array_push($riskCal,$getValueRisk);
													}
													
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'FATCA_PLACEOFBIRTH'){
				$amendDataCall[$dataSeq]['amend_field'] = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|PlaceOfBirth';
			}

			// CG - 240725 - for fatch remarks path into custmoddata - others are ok!
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'FATCA_REMARKS'){
				$amendDataCall[$dataSeq]['amend_field'] = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|FatcaRemarks';
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'FATCA_BIRTHCOUNTRY'){
				$amendDataCall[$dataSeq]['amend_field'] = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|CountryOfBirth';

				$getFlag = self::getCountryRisk($amendDataCall[$dataSeq]['new_value']);
				$getValueRisk = self::getRiskScore($getFlag['risk_category']);
				array_push($riskCal,$getValueRisk);
			}
			
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'FATCA_CNTRY_OF_RESIDENCE'){

				$getFlag = self::getCountryRisk($amendDataCall[$dataSeq]['new_value']);
				$getValueRisk = self::getRiskScore($getFlag['risk_category']);
				array_push($riskCal,$getValueRisk);
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'FATCA_NATIONALITY'){

				$getFlag = self::getCountryRisk($amendDataCall[$dataSeq]['new_value']);
				$getValueRisk = self::getRiskScore($getFlag['risk_category']);
				array_push($riskCal,$getValueRisk);
			}

			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == '_AADHAR_NUMBER'){
				//----------------------saving flow hardcoded customerid---------------\\

				$aadhaarPre = true;
				$custId = '001189092';
				$aadharNumber = $amendDataCall[$dataSeq]['new_value'];
				$aadharReferenceDetails = Api::aadharValutSvc($custId,$aadharNumber,$crf_id);
				
				$aadhaarCode =  isset($aadharReferenceDetails['code']) && $aadharReferenceDetails['code'] != '' ? $aadharReferenceDetails['code'] : '!';
        		$aadhaarMsg =  isset($aadharReferenceDetails['message']) && $aadharReferenceDetails['message'] != '' ? $aadharReferenceDetails['message'] : '';

        		if(isset($aadharReferenceDetails['status']) && $aadharReferenceDetails['status'] == "Success"){
	            	$referenceKey = isset($aadharReferenceDetails['data']['response']['referenceKey']) && $aadharReferenceDetails['data']['response']['referenceKey'] != ''? $aadharReferenceDetails['data']['response']['referenceKey'] : '';
	            	if($referenceKey != ''){
	            		$amendDataCall[$dataSeq]['new_value'] = $referenceKey;
	            	}
				}else{
					return 'fail';
				}
				
			//---------------confirm the remove code--------------------\\
				if(isset($panPre) && $panPre){
					$docIdCount++;
				}

				$idArray = AmendApi::genEntityDocArray(1,$amendDataCall[$dataSeq]['new_value'],'');
				if(isset($existingIDs['ADHAR']) && $existingIDs['ADHAR'] != ''){

					$idArray["EntityDocumentID"] = $existingIDs['ADHAR'];
					$entityProofIdData['field_name'] = '"'.$docIdCount.'"';
				}
		   		$entityProofIdData['new_value'] = $idArray;
		   		$entityProofIdData['field_name'] = '"'.$docIdCount.'"';
		   		$entityProofIdData['amend_field'] = 'RetCustModRequest|RetCustModRq|RetailCustModRelatedData|EntityDocModData|'.$docIdCount;
		   		array_push($amendDataCall,$entityProofIdData);
			}

			//-------------------confirm and remove the code------------------\\
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'PAN_GIR_NUM'){

				$panPre =  true;
				if(isset($aadhaarPre) && $aadhaarPre){
					$docIdCount++;
				}

				if(count($amendaddData)>0){
					$docIdCount =  count($amendaddData);
				}
   				$idArray = AmendApi::genEntityDocArray(7,$amendDataCall[$dataSeq]['new_value'],'');
				if(isset($existingIDs['RPAN']) &&  $existingIDs['RPAN'] != ''){
					$idArray["EntityDocumentID"] = $existingIDs['RPAN'];
				}

				if(isset($existingIDs['INPAN']) &&  $existingIDs['INPAN'] != ''){
					$idArray["EntityDocumentID"] = $existingIDs['INPAN'];
				}
				
				$entityProofIdData['field_name'] = '"'.$docIdCount.'"';
				$entityProofIdData['new_value'] = $idArray;
				$entityProofIdData['amend_field'] = 'RetCustModRequest|RetCustModRq|RetailCustModRelatedData|EntityDocModData|'.$docIdCount;
				array_push($amendDataCall,$entityProofIdData);
			}

			//decessing making 
			if(isset($amendDataCall[$dataSeq]['field_name']) && $amendDataCall[$dataSeq]['field_name'] == 'DateOfNotification'){
				
				$deathStatus['field_name'] = 'CustStatus';
				$deathStatus['new_value'] = 'DCSED';
				$deathStatus['amend_field'] = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|CustStatus';
				array_push($amendDataCall,$deathStatus);
		}
		}
		//-----------------Risk Calculation Data-----------------\\

		if(count($riskCal) > 0){
			
			$getNewRisk = max($riskCal);
			$getriskData = self::addRiskApi($getExistingrisk,$getNewRisk);
			if(count($getriskData) > 0){
				array_push($amendDataCall,$getriskData);
			}
		}
		//------------------End Data----------------//

		//---------------------always add in custid----------------\\	
		
	   		$amendCustData['field_name'] = 'CustId';
	   		$amendCustData['new_value'] = $customerID;
	   		$amendCustData['amend_field'] = 'RetCustModRequest|RetCustModRq|RetCustModMainData|CustModData|CustId';
		   	array_unshift($amendDataCall,$amendCustData);
	   
   		//--------------------end CustID------------------------------//
	   	
   		return $amendDataCall;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','addDataAmendApi',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getCityCode($cityDesc){
		try{

		$cityCode = DB::table('FIN_PCS_DESC')->select('CITYCODE')
															->where(DB::raw('LOWER(CITYDESC)'),strtolower($cityDesc))
															->get()
															->toArray();
		$cityCode = (array) current($cityCode);
		return $cityCode['citycode'];

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getCityCode',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getStateCode($stateDesc){
		try{
		$stateCode = DB::table('FIN_PCS_DESC')->select('STATECODE')
															->where(DB::raw('LOWER(STATEDESC)'),strtolower($stateDesc))
															->get()
															->toArray();
		$stateCode = (array) current($stateCode);
			$statedescCode =  $stateCode['statecode'];
			return $statedescCode;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getStateCode',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getCountryCode($countryDesc){
		try{
		$countryCode = DB::table('FIN_PCS_DESC')->select('COUNTRYCODE')
															->where(DB::raw('LOWER(COUNTRYDESC)'),strtolower($countryDesc))
															->get()
															->toArray();
		$countryCode = (array) current($countryCode);
			$countrydescCode = $countryCode['countrycode'];
			return $countrydescCode;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getCountryCode',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getFatcaCountryCode($countryId){
		try{
		
		$countryCode = DB::table('COUNTRIES')->select('COUNTRY_CODE')
											->where('ID',$countryId)
											->get()
											->toArray();	
		$countryCode = (array) current($countryCode);
		return $countryCode['country_code'];

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getFatcaCountryCode',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function amendAcmData($accountNo,$crf_number){
		try{

		$amdAcmData = array();

		//-----------Per Selected acccount number of data--------------\\

		$amendData = DB::table('AMEND_QUEUE')->select('FIELD_NAME','ACCOUNT_NO','NEW_VALUE','AMEND_FIELD')
											  ->where('CRF',$crf_number)
											  ->where('ACCOUNT_NO',$accountNo)
											  ->where('SOFT_DEL','N')
											  ->get()
											  ->toArray();

		return $amendData;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','amendAcmData',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getNomineeRelationship($oldValue =''){
		try{

		if($oldValue == ''){

		 $relationship = DB::table('RELATIONSHIP')->pluck('display_description','id')->toArray();
		}else{
			$relationship = DB::table('RELATIONSHIP')->where('CODE',$oldValue)->pluck('display_description','id')->toArray();
		}
         return $relationship;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getNomineeRelationship',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getModeOperationNominee(){
		try{

		$modeOfOperations = DB::table('MODE_OF_OPERATIONS')
                                                        ->where('FILTER','I')
                                                        ->pluck('operation_type','code')->toArray();
        return $modeOfOperations;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getModeOperationNominee',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function amendAcmPathCall($amendField,$finacleField){
		try{
		$pathReturn = '';
		switch($finacleField){

			case 'nomineeName':
				$pathReturn = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|';
			break;

			case 'modeOfOper':
				$pathReturn = 'sbAcctModRequest|sbAcctModCustomData|nominee|0|';
			
			break;

			case 'recDelFlg': 
				$pathReturn = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|';
			break;

			case 'relType': 
				$pathReturn = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|';
			break;

			case 'regNum': 
				$pathReturn = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|';
			break;
			
			case 'nomineeBirthDt':
				$pathReturn = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|';
			break;

			case 'DEL_FLAG':
				$pathReturn = 'sbAcctModRequest|sbAcctModCustomData|joint|0|';
			break;

			case 'JOINTHOLDERS_CUSTID':
				$pathReturn = 'sbAcctModRequest|sbAcctModCustomData|joint|0|';
			break;

			default:
				$pathReturn = $amendField;
			break;
		}
		return $pathReturn;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','amendAcmPathCall',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getRiskScore($riskFlag){

		$returnrisk = '';
		switch($riskFlag){
			case 'L': 
				$returnrisk = 1;
			break;

			case 'M': 
				$returnrisk = 2;
			break;

			default:
				$returnrisk = 3;
			break;
		}

		return $returnrisk;
	}

	public static function getCountryRisk($countryID){
		try{
		$getCountyFlag =  DB::table('COUNTRIES')->select('RISK_CATEGORY')
												->where('ID',$countryID)
												->get()
												->toArray();

		$getCountyFlag = (array)current($getCountyFlag);

		return $getCountyFlag;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getCountryRisk',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	//----------------------Any risk and upadate the api---------------\\

	public static function addRiskApi($getExistingrisk,$getValueRisk){

		$risk = array();

		if($getExistingrisk < $getValueRisk){

			$risk['field_name'] = 'FreeText10';
			$risk['new_value'] = $getValueRisk;
			$risk['amend_field'] = 'RetCustModRequest|RetCustModRq|RetailCustModRelatedData|coreInterfaceInfo|FreeText10';
			return $risk;
		}

		return $risk;
	}

	public static function addAcmAddData($amendData,$crf_number){
		try{
		$accountNo = $amendData[0]['account_no'];
		$nomineeMinorFlag = '';
		
		for($amdList=0;count($amendData)>$amdList;$amdList++){

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'ASTATUS'){
				$amendData[$amdList]['new_value'] = 'A';
			}

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'addr1'){

				$amdAcmAddData['field_name'] = 'addrType';
				$amdAcmAddData['account_no'] = $accountNo;
		   		$amdAcmAddData['new_value'] = 'Mailing';
		   		$amdAcmAddData['amend_field'] = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|nomineeContactInfo|postAddr|addrType';

	   			array_push($amendData,$amdAcmAddData);
			}

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'nomineeBirthDt'){
				
				$nomineeDob = \Carbon\Carbon::parse($amendData[$amdList]['new_value'])->age;

				if($nomineeDob < 18){
					$nomineeMinorFlag = "Y";
				}else{
					$nomineeMinorFlag = "N";
				}

				$amdMinorData['field_name'] = 'nomineeMinorFlg';
				$amdMinorData['account_no'] = $accountNo;
		   		$amdMinorData['new_value'] = $nomineeMinorFlag;
		   		$amdMinorData['amend_field'] = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|nomineeMinorFlg';
	   			array_push($amendData,$amdMinorData);
			}

			if($nomineeMinorFlag == 'Y'){

				if(in_array($amendData[$amdList]['field_name'],['guardianName','guardianCode'])){

		   		 	$gurdPath = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|guardianInfo';
					$gurdField = $amendData[$amdList]['field_name'];
		   		 	$amendData[$amdList]['amend_field'] = $gurdPath.'|'.$gurdField;
				}

				if($amendData[$amdList]['field_name'] == 'guardianCode'){
		   		 	$amendData[$amdList]['new_value'] = 'O';
				}

				if($amendData[$amdList]['field_name'] == 'g_addr1'){
					$amdAcmAddData['field_name'] = 'addrType';
					$amdAcmAddData['account_no'] = $accountNo;
			   		$amdAcmAddData['new_value'] = 'Mailing';
			   		$amdAcmAddData['amend_field'] = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|guardianInfo|guardianContactInfo|postAddr|addrType';
		   			array_push($amendData,$amdAcmAddData);
				}

				if(in_array($amendData[$amdList]['field_name'],['g_addr1','g_addr2','g_postalCode','g_city','g_stateProv','g_country'])){

		   		 	$gurdPath = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|guardianInfo|guardianContactInfo|postAddr';
		   		 	$gurdField = str_replace('g_','',$amendData[$amdList]['field_name']);
		   		 	$amendData[$amdList]['amend_field'] = $gurdPath.'|'.$gurdField;
				}
			}

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'recDelFlg'){
				$amdDEL['field_name'] = 'delFlag';
				$amdDEL['account_no'] = $accountNo;
		   		$amdDEL['new_value'] = 'Y';
		   		$amdDEL['amend_field'] = 'sbAcctModRequest|sbAcctModCustomData|nominee|0|delFlag';
	   			array_push($amendData,$amdDEL);

			}

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'nomineeName'){

	   			$amdMul['field_name'] = 'isMultiRec';
				$amdMul['account_no'] = $accountNo;
		   		$amdMul['new_value'] = 'N';
		   		$amdMul['amend_field'] = 'sbAcctModRequest|sbAcctModCustomData|nominee|0|isMultiRec';
	   			array_push($amendData,$amdMul);

				
	   			$addNomineePer['field_name'] = 'nomineePercent';
				$addNomineePer['account_no'] = $accountNo;
		   		$addNomineePer['new_value'] = '100.0';
		   		$addNomineePer['amend_field'] = 'sbAcctModRequest|sbAcctModRq|nomineeInfoRec|0|nomineePercent|value';
	   			array_push($amendData,$addNomineePer);

	   		}
			$seqHolder = 0;

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'JOINTHOLDERS_CUSTID'){
				$path = 'sbAcctModRequest|sbAcctModCustomData|joint';
				$custId = $amendData[$amdList]['new_value'];
				$amendData[$amdList]['amend_field'] = 'sbAcctModRequest|sbAcctModCustomData|joint';
				
		   		$datajointDel['field_name'] = '';
	   			$datajointDel['new_value'] = [
	   				Array(
		   				"cifId"=>$custId,
		   				"srlNum" => $seqHolder,
			    		"isMultiRec" => "Y",
	                    "delFlag" => "Y",
	   				)
				];

		   		$datajointDel['amend_field'] = $path;
				$datajointDel['account_no'] = $accountNo;

		   		array_push($amendData,$datajointDel);

		   		$arrayRel = self::relationPartyAddorDel($custId,'Y');	
		   		$path1 = 'sbAcctModRequest|sbAcctModRq|relPartyRec|relPartyType';
				$partyData['field_name'] = 'relPartyType';
				$partyData['new_value'] = 'J';
				$partyData['amend_field'] = $path1;
				array_push($amendData,$partyData);


				$path2 = 'sbAcctModRequest|sbAcctModRq|relPartyRec|custId|custId';
				$partyData1['field_name'] = 'custId';
				$partyData1['new_value'] = $custId;
				$partyData1['amend_field'] = $path2;
				array_push($amendData,$partyData1);

		   	}

			if(isset($amendData[$amdList]['field_name']) && $amendData[$amdList]['field_name'] == 'JOIN_HOLDER_1'){
				$path = 'sbAcctModRequest|sbAcctModCustomData|joint';
				$custId = $amendData[$amdList]['new_value'];
	   			$amendData[$amdList]['new_value'] = [
	   				Array(
		   				"cifId"=>$custId,
	   				"srlNum" => $seqHolder,
                    "delFlag" => "N",
		    		"isMultiRec" => "Y",
					"passSheetFlag"=>"N",
                    "standingInstruction" => "N",
                    "depositNoticeFlag" => "N",
                    "loanOverDueNoticeFlag" => "N",
                    "xcludeForCombStmtFlag" => "N",
	   				)
					];
		   		$amendData[$amdList]['amend_field'] = $path;
		   		$amendData[$amdList]['field_name'] = $seqHolder;


		   		$path = 'sbAcctModRequest|sbAcctModRq|relPartyRec|'.$seqHolder;

		   		$arrayRel = self::addrelationPartyAddorDel($custId,'N');
		   		$path1 = 'sbAcctModRequest|sbAcctModRq|relPartyRec';
				$partyData['field_name'] = '';
				$partyData['new_value'] = $arrayRel;
				$partyData['amend_field'] = $path1;
				array_push($amendData,$partyData);
			}
		}

		$accountData['field_name'] = 'acctId';
		$accountData['account_no'] = $accountNo;
   		$accountData['new_value'] = $accountNo;
   		$accountData['amend_field'] = 'sbAcctModRequest|sbAcctModRq|sbAcctId|acctId';

	   	array_push($amendData,$accountData);

	   	return $amendData;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','addAcmAddData',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function isCustidSuspended($custId){
		$customerDetails =  AmendApi::customerRetCustInq($custId);
		
		if(isset($customerDetails->gatewayResponse->status->isSuccess) && $customerDetails->gatewayResponse->status->isSuccess == '1'){
			return 'success';
		}else{
			return 'fail';
	}
	}

	public static function getDynamicvalue($crf_number,$field_name,$columnName){
		try{

		$getcolumn = strtolower($columnName);

		$amendData = DB::table('AMEND_QUEUE')->select('FIELD_NAME',$columnName)
   											 ->where('CRF',$crf_number)
   											 ->where('AMEND_ITEM',$field_name)
   											 ->orderBy($getcolumn)
   											 ->pluck($getcolumn,'field_name');
 		return $amendData;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getDynamicvalue',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function sbSchemeCodeSelection($schmType){
		
		try{
			$schemeCode = array();

			switch($schmType){
				case 'SB':
						$schemeCode = DB::table('SCHEME_CODES')->select('SCHEME_CODE',DB::raw("SCHEME_CODE|| '-' ||SCHEME_DESC AS SCHEME_DESC"))
													      ->where('ACCOUNT_TYPE','1')
													      ->pluck('scheme_desc','scheme_code');
				break;

				case 'CA':
						$schemeCode = DB::table('CA_SCHEME_CODES')->select('SCHEME_CODE','SCHEME_DESC')
													      ->where('ACCOUNT_TYPE','2')
													      ->pluck('scheme_desc','scheme_code');
				break;

				case 'TD':
						$schemeCode = DB::table('TD_SCHEME_CODES')->select('SCHEME_CODE','SCHEME_DESC')
													      ->where('ACCOUNT_TYPE','3')
													      ->pluck('scheme_desc','scheme_code');
				break;
			}
			return $schemeCode;
			
		}catch(\Throwable $e){
	    	if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','sbSchemeCodeSelection',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
	}

	public static function getAmendMaritalStatus()
    {
		try{
        $maritalStatus = array();
        $maritalStatus = DB::table('marital_status')->pluck('marital_status','code')->toArray();
        return $maritalStatus;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getAmendMaritalStatus',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getTitleGenderBase($gender=''){
		try{
       
        $titles = array();
		if($gender == ''){

			$titles = DB::table('TITLE')->where('is_active',1)
	                                    ->pluck('description','id')->toArray();
		}else{
	        $titles = DB::table('TITLE')->where('is_active',1)
	                                    ->whereIn('GENDER',['MFTO','MF',$gender])
	                                    ->pluck('description','id')->toArray();
		}
        return $titles;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getTitleGenderBase',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getAlltitles(){
		try{
       
        $titles = array();
        $titles = DB::table('TITLE')->where('is_active',1)
                                    // ->whereIn('GENDER',['MFTO','MF',null,$gender])
                                    ->pluck('gender','id')->toArray();
        return $titles;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getAlltitles',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

      public static function getModeOfOperationsAmend($accountType='',$jointType){
		try{
        $modeOfOperations = array();
  
            switch($accountType){
                // case '2':
                // $modeOfOperations = DB::table('MODE_OF_OPERATIONS')
                //                                         ->where('FILTER','P')
                //                                         ->pluck('operation_type','id')->toArray();
                // break;

                default:
                	$modeOfOperations = DB::table('MODE_OF_OPERATIONS')->whereIn('ID',$jointType)
                                                        			   ->pluck('operation_type','id')
                                                        			   ->toArray();
                break;

            }
        return $modeOfOperations;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getModeOfOperationsAmend',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getFatacaCountryDesc($countryCode){
		try{

    	$getDesc = DB::table('COUNTRIES')->select('NAME')
    									 ->where('COUNTRY_CODE',$countryCode)
    									 ->get()
    									 ->toArray();
    	$getDesc = (array) current($getDesc);
    	
    	return $getDesc['name'];

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getFatacaCountryDesc',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function getEvidenceList($crf_number,$evid){

		try{
    	$getEvidenceList = DB::table('AMEND_PROOF_DOCUMENT')->select('AMEND_PROOF_IMAGE')
    														->where('CRF_NUMBER',$crf_number)
    														->where('EVIDENCE_ID',$evid)
    														->get()
    														->toArray();

    	$getEvidenceList = (array) current($getEvidenceList);

    	$imagePath = isset($getEvidenceList['amend_proof_image']) && $getEvidenceList['amend_proof_image'] != ''?$getEvidenceList['amend_proof_image'] : '';

    	return $imagePath;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getEvidenceList',$eMessage,$crf_number,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function entityArrayForAPIPush($entityArrayForAPI){
		
    	if(count($entityArrayForAPI)>0){
			$entityProofIdData['field_name'] = '';
	   		$entityProofIdData['new_value'] = $entityArrayForAPI;
	   		$entityProofIdData['amend_field'] = 'RetCustModRequest|RetCustModRq|RetailCustModRelatedData|EntityDocModData';
		}

		return $entityProofIdData;
    }

    public static function relationPartyAddorDel($custId,$delFlag){

    	$arrayRelationship = [
    						Array(
				    "relPartyType" => "J",
				)
    		];

    	return $arrayRelationship;
			}
			public static function addrelationPartyAddorDel($custId,$delFlag){
		try{
				$arrayRelationship = [
									Array(
							"relPartyType" => "J",
				    "relPartyTypeDesc" => "",
					    "relPartyCode" => "",
				    "relPartyCodeDesc" => "",
					    "recDelFlg" => $delFlag,
				    "custId" => Array(
					        "custId" => $custId,
				        "personName" => Array(
				            "lastName" => "",
				            "firstName" => "",
				            "middleName" => "",
				            "name" => "",
				            "titlePrefix" => "",
				            "custName" => ""
				        )
				    ),
				    "relPartyContactInfo" => Array(
				        "phoneNum" => Array(
				            "telephoneNum" => "",
				            "faxNum" => "",
				            "telexNum" => ""
				        ),
				        "emailAddr" => "",
				        "postAddr" => Array(
				            "addr1" => "",
				            "addr2" => "",
				            "addr3" => "",
				            "city" => "",
				            "stateProv" => "",
				            "postalCode" => "",
				            "country" => "",
				            "addrType" => ""
				        )
				    ),
					)
    		];
		
    	return $arrayRelationship;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','addrelationPartyAddorDel',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
			}
	}
		

	public static function getIdDescription($proof_Id){
		try{
		$idDescription = DB::table('OVD_TYPES')->select('OVD')
											->where('ID',$proof_Id)
											->get()
											->toArray();
		$idDescription = (array) current($idDescription);

		return $idDescription;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getIdDescription',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}


	public static function addExistingIdOfData($proofId,$customerID,$crf_id,$vaultNo = '',$expDate = '',$issuesDate = ''){	
		try{

		$existingIDs = AmendApi::retcustinq($customerID, $crf_id);
		$amendDataCall = array();
		$idArray = array();
		$entityArrayForAPI = array();
		$getData = array();

		if($proofId != ''){
   			// $idPresent = true;
   			$idArray = AmendApi::genEntityDocArray($proofId,$vaultNo,$expDate,$issuesDate);
   		
			switch($proofId){
				case 1:
					if(isset($existingIDs['ADHAR']) && $existingIDs['ADHAR'] != ''){
				    	$idArray["EntityDocumentID"] = $existingIDs['ADHAR'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;

				case 2:
					if(isset($existingIDs['PASPR']) && $existingIDs['PASPR'] != ''){
				    	$idArray["EntityDocumentID"] = $existingIDs['PASPR'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
						
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}

				break;

				case 3:
					if(isset($existingIDs['DRVLC']) && $existingIDs['DRVLC'] != ''){
				    	$idArray["EntityDocumentID"] = $existingIDs['DRVLC'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;

				case 4:
					if(isset($existingIDs['NREGA']) &&  $existingIDs['NREGA'] != ''){
					   	$idArray["EntityDocumentID"] = $existingIDs['NREGA'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;

				case 5:
					if(isset($existingIDs['NPR']) && $existingIDs['NPR'] != ''){
					   	$idArray["EntityDocumentID"] = $existingIDs['NPR'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;

				case 6:
					if(isset($existingIDs['VOTID']) && $existingIDs['VOTID'] != ''){
					   	$idArray["EntityDocumentID"] = $existingIDs['VOTID'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;

				case 9:
					if(isset($existingIDs['EKYC']) &&  $existingIDs['EKYC'] != ''){
					   	$idArray["EntityDocumentID"] = $existingIDs['EKYC'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;
				default:
					if(isset($existingIDs['UTLBL']) &&  $existingIDs['UTLBL'] != ''){
					    $idArray["EntityDocumentID"] = $existingIDs['UTLBL'];
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}else{
						$entityArrayForAPI = $idArray;
						$getData = self::entityArrayForAPIPush($entityArrayForAPI);
					}
				break;
			}
			
		}
			return $getData;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','addExistingIdOfData',$eMessage,'',$crf_id);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function amendgetVaultRefNumber($refernceNumber,$crfNumber){
		try{
        $custId = '001189092';
        $aadhaarNumber =  str_replace('-','',$refernceNumber);
        $aadharReferenceDetails = Api::aadharValutSvc($custId,$aadhaarNumber,$crfNumber);
        $aadhaarCode =  isset($aadharReferenceDetails['code']) && $aadharReferenceDetails['code'] != '' ? $aadharReferenceDetails['code'] : '!';
        $aadhaarMsg =  isset($aadharReferenceDetails['message']) && $aadharReferenceDetails['message'] != '' ? $aadharReferenceDetails['message'] : '!';
			
        if(isset($aadharReferenceDetails['status']) && $aadharReferenceDetails['status'] == "Success"){
            $referenceKey = isset($aadharReferenceDetails['data']['response']['referenceKey']) && $aadharReferenceDetails['data']['response']['referenceKey'] != ''? $aadharReferenceDetails['data']['response']['referenceKey'] : '';
            if($referenceKey != ''){
                $refernceNumber = $referenceKey;

            return ['status'=>'success','msg'=>'Successfully get Reference Number','data'=>['refernceNumber'=>$refernceNumber]];
            }
        }else{
            return ['status'=>'fail','msg'=>'Vault Api failed.','data'=>[]];
        }

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','amendgetVaultRefNumber',$eMessage,$crfNumber,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

	public static function getNomineeRelationshipCode($amendValue){
		try{
		$relationCode =  DB::table('RELATIONSHIP')->select('CODE')
												  ->where('ID',$amendValue)
												  ->get()
												  ->toArray();
		$relationCode =  (array) current($relationCode);
		return $relationCode['code'];

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getNomineeRelationshipCode',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getPincodeDetails($pincode){
		try{
		
		$getPinDetails =  DB::table('FIN_PCS_DESC')->select('CITYDESC','STATEDESC','COUNTRYDESC')
													->where('PINCODE',$pincode)
													->get()
													->toArray();
		$getPinDetails =  (array)current($getPinDetails);

		return $getPinDetails;
			
		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendCommonFunctions','getPincodeDetails',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getpincodeData($pincode){
		$checkValidPin = DB::table('FIN_PCS_DESC')->select('CITYCODE','STATECODE','COUNTRYCODE')
																  ->where('PINCODE',$pincode)
																  ->get()
																  ->toArray();
		$checkValidPin = (array) current($checkValidPin);
		return $checkValidPin;
	}
}

?>