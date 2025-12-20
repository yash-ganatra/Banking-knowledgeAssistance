<?php
namespace App\Helpers;

use App\Helpers\CommonFunctions;
use App\Helpers\AmendCommonFunctions;
use App\Helpers\AmendApiQueueCommonFunctions;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Arr;
use Session;
use Carbon\Carbon;
use DB;

class AmendRules {
	public static function checkMinorToMajor($minortomajor,$custDob){

		$checkDob = date('d-m-Y',strtotime(substr($custDob,0,10)));
		$diffDob = Carbon::parse($checkDob)->age;

			if(18 < $diffDob && $minortomajor == 'N'){
				$minor = true;
			}else{
				$minor = false;
			}

		return $minor;
	}

	public static function checkekycStatus($ekycStatus,$ekycDate,$ekycRisk){

		$ekycUpdateDate = date('d-m-Y',strtotime(substr($ekycDate,0,10)));
		$currDate = date('d-m-Y',strtotime(substr(Carbon::now(),0,10)));
		$diffekycStatusDate = Carbon::parse($currDate)->diffInYears($ekycUpdateDate);
		
		$forekycFail = false;
		$ekycActiveFlagStatus = '';
		if($ekycStatus == 'Y'){
			Switch($ekycRisk){
				case 'H':
						if($diffekycStatusDate >= 2){
							$forekycFail = true;
						}
					break;
				case 'M':
						if($diffekycStatusDate >= 8){
							$forekycFail = true;
						}
					break;
				case 'L':
						if($diffekycStatusDate >= 10){
							$forekycFail = true;
						}
					break;
				default:
						if($diffekycStatusDate >= 10){
							$forekycFail = true;
						}
					break;
			}
		}else{
			
			$forekycFail = true;
	
		}

		return $forekycFail;
	}


	public static function getValidAmendItem($currCustomerDetails,$getAccDetails = ''){

		try{

			$getekycStatus = Session::get('getekycStatus');
	    	$getminorStatus = 	Session::get('getminorStatus');
	    	$getacctStatus =  Session::get('getacctStatus');
			$ekycNumber  = Session::get('referenceNumber');
			
			$rmnMobChk =  isset($currCustomerDetails['data']['customerDetails']['CUST_PAGER_NO']) && $currCustomerDetails['data']['customerDetails']['CUST_PAGER_NO'] !=''
								?$currCustomerDetails['data']['customerDetails']['CUST_PAGER_NO']:'';

			$emailIdchk =  isset($currCustomerDetails['data']['customerDetails']['EMAIL_ID']) && $currCustomerDetails['data']['customerDetails']['EMAIL_ID'] !=''
								?$currCustomerDetails['data']['customerDetails']['EMAIL_ID']:'';

		 	$getOrder = DB::table('AMENDITEMS')->select('TYPE3')
	    									  ->groupBy('TYPE3')
	    									  ->orderBy('TYPE3','ASC')
	    									  ->get()->toArray();
				
				for($i=0;$i<count($getOrder);$i++){
					
					$getData = DB::table('AMENDITEMS')->select('DESCRIPTION','TYPE3','SEQUENCE','ID','ACTIVE','TYPE2')
													  ->where('TYPE3',$getOrder[$i]->type3)
													  ->orderBy('SEQUENCE','ASC')
													  ->get()->toArray();

					for($item=0; $item<count($getData); $item++){

						switch($getData[$item]->id){
							case 18: // Kyc Refresh

								if($getekycStatus == 'Over Due'){
									$getData[$item]->selected = true;
								}
								break;

							// case 20: //check major to minor
							// 	if($getminorStatus == 'Not Applicable'){
							// 		$getData[$item]->selected = false; 
							// 	}
							// break;

							// case 31: //------------check aadhaar number check present  or not------------------\\						

							// 	$aadharCheck = isset($currCustomerDetails['data']['customerDetails']['NAT_ID_CARD_NUM']) && $currCustomerDetails['data']['customerDetails']['NAT_ID_CARD_NUM'] != '' ? $currCustomerDetails['data']['customerDetails']['NAT_ID_CARD_NUM'] : '';
							
							// 	// if($aadharCheck == ''){
							// 	// 	$getData[$item]->active = 'N'; 
							// 	// }

							// break;

							case 40: //--------------- check zero balance to close the account ---------------\\
								if(isset($getAccDetails['accountDetails']['TOT_OUTSTANDING_AMT']) &&  $getAccDetails['accountDetails']['TOT_OUTSTANDING_AMT'] > 0){

									$getData[$item]->active = 'N';
			  					}
							break;

							case 49:
								$schemeCode = isset($getAccDetails['accountDetails']['SCHM_CODE']) && $getAccDetails['accountDetails']['SCHM_CODE'] != '' ? $getAccDetails['accountDetails']['SCHM_CODE'] : '';
								if($schemeCode == 'SB106'){
									$getData[$item]->active = 'N'; 
								}

								if(isset($getAccDetails['accountDetails']['JOINTHOLDERS_CUSTID']) &&  $getAccDetails['accountDetails']['JOINTHOLDERS_CUSTID'] != '-'){
									// $getData[$item]->active = 'N';
								}
							break;

							case 50:
								if(isset($getAccDetails['accountDetails']['JOINTHOLDERS_CUSTID']) &&  $getAccDetails['accountDetails']['JOINTHOLDERS_CUSTID'] == '-'){
									$getData[$item]->active = 'N';
					  			}
							break;

							case 32:
								if(isset($currCustomerDetails['data']['customerDetails']['NAT_ID_CARD_NUM']) &&  $currCustomerDetails['data']['customerDetails']['NAT_ID_CARD_NUM'] != ''){
									$getData[$item]->active = 'N'; 
								}
								if($ekycNumber != ''){
									$getData[$item]->active = 'N'; 
								}
							break;

							case 12:
								if(isset($currCustomerDetails['data']['customerDetails']['PAN_GIR_NUM']) && $currCustomerDetails['data']['customerDetails']['PAN_GIR_NUM'] == ''){
									// $getData[$item]->active = 'Y'; 
									// $getData[$item]->selected = true; 
								}
							break;

							case 13:
								if(isset($currCustomerDetails['data']['customerDetails']['FREE_TEXT_10']) &&  $currCustomerDetails['data']['customerDetails']['FREE_TEXT_10'] == ''){
									$getData[$item]->active = 'N'; 
									$getData[$item]->selected = true; 
								}
							break;

							case 35:
								if($rmnMobChk == '' || $emailIdchk == ''){
									$getData[$item]->active = 'N'; 
								}
							break;
							case 37:
								if($rmnMobChk == '' || $emailIdchk == ''){
									$getData[$item]->active = 'N'; 
								}
							break;
							case 38:
								if($rmnMobChk == '' || $emailIdchk == ''){
									$getData[$item]->active = 'N'; 
								}
							break;
							case 39:
								if($rmnMobChk == '' || $emailIdchk == ''){
									$getData[$item]->active = 'N'; 
								}
							break;

							case 27:
								
								if(isset($getAccDetails['accountDetails']['CHQ_ALWD_FLG']) && $getAccDetails['accountDetails']['CHQ_ALWD_FLG'] != 'Y'){
									$getData[$item]->active = 'N'; 
								}
							break;

							

							default:
							break;						
						}

						// if(count($currCustomerDetails) <= 0){
						if(isset($currCustomerDetails['data']['status']) && $currCustomerDetails['data']['status'] == 'FAILED'){
							if($getData[$item]->id == 19){
								$getData[$item]->active = 'Y';
								$getData[$item]->selected = true; 
							}else{
								$getData[$item]->active = 'N';
							}
						}
						//-------------- account is not active to all acm filed not modified the data-------------\\

						if($getacctStatus == 'Not ok'){

							if(in_array($getData[$item]->id, [10,15,5,18,4])){
								$getData[$item]->selected = true;
							}
							if($getData[$item]->id == '23'){
								$getData[$item]->selected = true;
							}

							if($getData[$item]->type2 == 'ACM'){
								$getData[$item]->active = 'N';
							} 
						}

						if(count($getAccDetails) <= 0){
							if($getData[$item]->type2 == 'ACM'){
								$getData[$item]->active = 'N';
					}
						}

						if($getminorStatus == 'Applicable'){
							if(in_array($getData[$item]->id,[20,24,25])){
						 		$getData[$item]->selected = true; 
							}
						}

					}
				$getValue[$i] = $getData;
			}  
			return $getValue;
		}catch(\Throwable $e){
			if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','getValidAmendItem',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	//------------------------set dynamic dropdown data----------------------------------\\

	public static function setDropdownData($cleanArray,$currAccountDetails,$currCustomerDetails){
		try{
		$schmType = '';
		$dropdownData = array();
		$getGender = '';
		
		if(isset($currCustomerDetails['data']['customerDetails']['CUST_SEX']) && $currCustomerDetails['data']['customerDetails']['CUST_SEX']){
			$getGender = $currCustomerDetails['data']['customerDetails']['CUST_SEX'];
		}

		if(isset($currAccountDetails['accountDetails']['SCHM_CODE']) && $currAccountDetails['accountDetails']['SCHM_CODE'] !=''){
			$schmType = substr($currAccountDetails['accountDetails']['SCHM_CODE'],0,2);
		}

    	$custSex = '';
		$joinListAllow = 'N';
		foreach($cleanArray as $key => $value){
			$value = (object) $value;
    		$amendField = isset($value->fieldName) && $value->fieldName != ''?$value->fieldName:'';
			$oldvalue = isset($value->oldValue) && $value->oldValue != ''?$value->oldValue:'';
			$amendId = isset($value->id) && $value->id != ''?$value->id:'';

			if($amendId == 29){
				$oldvalue = '';
			}

    		if($amendField == 'CUST_SEX'){
    			$custSex = 'Y';
    		}

			if($amendField == 'JOIN_HOLDER_1'){
				$joinListAllow = 'Y';
			}

    		switch($amendField){
    			case '_MARITAL_STATUS':
    				$dropdownData[$amendField] = AmendCommonFunctions::getAmendMaritalStatus();
    			break;

    			case 'CUST_SEX':
    				$dropdownData[$amendField] = config('constants.GENDER');
    			break;

    			case 'OCCUPATION':
    				$dropdownData[$amendField] = CommonFunctions::getOccupation();
    			break;

    			case 'CUST_TITLE_CODE':
    				
    				if($custSex == 'Y'){
	    				$dropdownData[$amendField] = AmendCommonFunctions::getTitleGenderBase();
	    				$dropdownData[$amendField.'_EXTRA'] = AmendCommonFunctions::getAlltitles();
    				}else{
	    				$dropdownData[$amendField] = AmendCommonFunctions::getTitleGenderBase($getGender);
    				}

    			break;

    			case 'MODE_OF_OPERATION':
    				$checkMultiApp = $currAccountDetails['accountDetails']['JOINTHOLDERS_CUSTID'];
    				$accountType = '';

    				if($checkMultiApp == '-'){
    					$joinList = [1];
    					$dropdownData[$amendField] = AmendCommonFunctions::getModeOfOperationsAmend($accountType,$joinList);
    				}else{
    					$joinList = [1,2,3,4,6,21];
    					$dropdownData[$amendField] = AmendCommonFunctions::getModeOfOperationsAmend($accountType,$joinList);
    				}
					
					if($joinListAllow == 'Y'){
						$joinList = [1,2,3,4,6,21];
						$dropdownData[$amendField] = AmendCommonFunctions::getModeOfOperationsAmend($accountType,$joinList);
					}
    			break;

    			case 'modeOfOper':
    				$dropdownData[$amendField] = AmendCommonFunctions::getModeOperationNominee();
    			break;

    			case 'relType':
    				$dropdownData[$amendField] = AmendCommonFunctions::getNomineeRelationship($oldvalue);
					
    			break;

    			case 'SCHM_CODE':
    				$dropdownData[$amendField] = AmendCommonFunctions::sbSchemeCodeSelection($schmType);
    			break;

    			case 'SCHM_CODE_NEW':
    				$dropdownData[$amendField] = AmendCommonFunctions::sbSchemeCodeSelection($schmType);
    			break;

    			case (in_array($amendField,['FATCA_NATIONALITY','FATCA_CNTRY_OF_RESIDENCE','FATCA_BIRTHCOUNTRY'])):
    				$dropdownData[$amendField] =  CommonFunctions::getCountry();
    			break;

    			case 'guardianCode':
    				$dropdownData[$amendField] = AmendCommonFunctions::getNomineeRelationship();
    			break;
    		}
	    }
	    return $dropdownData;

		}catch(\Throwable $e){
			if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','setDropdownData',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function amendValidationItems($getselectedData){
		try{
		$count = 0;
		$status = "true";
		$msg = '';	
		$checkSign = false;
		$checkTitle = false;

		for($item=0;count($getselectedData)>$item;$item++){

			if(in_array($getselectedData[$item]->id,[4,5,10,15])){
				$count += 1;
			}

			if($getselectedData[$item]->id == 25){
				$checkSign = true;
			}

			if($getselectedData[$item]->id == 4){
				$checkTitle = true;
			}
		}

		for($ls=0;count($getselectedData)>$ls;$ls++){

			if($getselectedData[$ls]->id == 18){
				if($count == 4){
					$status = "true";
				}else{
					$status = "false";
					$msg = 'Please select required field for KYC.';
				}
			}

			if($getselectedData[$ls]->id == 24){
				if($checkSign){
					$status = "true";
				}else{
					$status = "false";
					$msg = 'Please select required field for Mode of Operation.';
				}
			}

			if($getselectedData[$ls]->id == 15){

				if($checkTitle){
					$status = "true";
				}else{
					$status = "false";
					$msg = 'Please select required field for Gender.';
				}
			}
		}


		return ['status'=>$status,'msg'=>$msg];
		}catch(\Throwable $e){
			if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','amendValidationItems',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function amednmarkfromQc($crfNumber){

		try{
			$checkData = DB::table('AMEND_API_QUEUE')->where('CRF_NUMBER',$crfNumber)
													 ->whereNull('STATUS')
													 ->where('SEQUENCE','<>',999)
													 ->count();
			
			if($checkData > 0){
				return json_encode(['status'=>'fail','msg'=>'Other API Pending.','data'=>[]]);
			}else{
				$saveStatus = CommonFunctions::saveAmendStatusLog($crfNumber,'Moved To Amend-QC',21,$comment='');
				$saveStatus = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['CRF_NEXT_ROLE' => 21]);
				NotificationController::processNotification($crfNumber,'CRF_COMPLETED','CRF_DONE_EMAIL','amend','');
                NotificationController::processNotification($crfNumber,'CRF_COMPLETED','CRF_DONE_SMS','amend','');
				return json_encode(['status'=>'success','msg'=>'Form successfully Mark in Qc','data'=>[]]);
			}
		}catch(\Throwable $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','amednmarkfromQc',$eMessage,$crfNumber,'');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
	}

	public static function amendCrfApprovalOnline($getCrfData){
		try{
			$amendItemData = DB::table('AMENDITEMS')->select('ID','FINACLE_FIELD')
													->where('E_CRF','N')
													->get()
													->toArray();
			$linkshare = 'true';
			for($seq=0;count($getCrfData)>$seq;$seq++){

				for($item=0;count($amendItemData)>$item;$item++){
					
					if($getCrfData[$seq]->field_name == $amendItemData[$item]->finacle_field){
						$linkshare = 'false';
					}
				}
			}
		
			return $linkshare;

		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','amendCrfApprovalOnline',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
		}
	}

	public static function setcodeName($cleanArray){
		try{

		for($cl=0;count($cleanArray)>$cl;$cl++){

			switch($cleanArray[$cl]['fieldName']){
				case 'relType':
					$cleanArray[$cl]['oldValue'] = Self::getRelationshipDesc($cleanArray[$cl]['oldValue']);
					$cleanArray[$cl]['newValue'] = Self::getRelationshipDescId($cleanArray[$cl]['oldValue']);

				break;
			}
		}
		return $cleanArray;
		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','setcodeName',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getRelationshipDesc($oldvalue){
		// echo "<pre>";print_r($oldvalue);exit;
		try{
		if($oldvalue != ''){

			$relationshipdesc = DB::table('RELATIONSHIP')->select('DISPLAY_DESCRIPTION')->where('CODE',$oldvalue)->orderBy('DISPLAY_DESCRIPTION','DESC')->get()->toArray();
			$relationshipdesc = (array) current($relationshipdesc);
			return $relationshipdesc['display_description'];
		}else{
			return $oldvalue;
		}
		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','getRelationshipDesc',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}
	public static function getRelationshipDescId($oldvalue){
		try{
		if($oldvalue != ''){

			$relationshipdesc = DB::table('RELATIONSHIP')->select('ID')->where('DISPLAY_DESCRIPTION',$oldvalue)->orderBy('DISPLAY_DESCRIPTION','DESC')->get()->toArray();
			$relationshipdesc = (array) current($relationshipdesc);
			return $relationshipdesc['id'];
		}else{
			return $oldvalue;
		}
		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','getRelationshipDescId',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function getPincodeDetails($pinCode){
		try{
		$checkValidPin = DB::table('FIN_PCS_DESC')->select('CITYDESC','STATEDESC','COUNTRYDESC')
																  ->where('PINCODE',$pinCode)
																  ->get()
																  ->toArray();
		$checkValidPin = (array) current($checkValidPin);

		return $checkValidPin;
		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','getPincodeDetails',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function isMOPokay($requestData){
		try{
		$rule = 'OK';
		// MOP Request // 24
		if(isset($requestData['inputFieldCheck'][24])){
			$rule = 'NOTOK';
			$existing_applicants = 1;
			if(isset(Session::get('currAccountDetails')['accountDetails']['JOINTHOLDERS_CUSTID'])){
				$existing_applicants = Session::get('currAccountDetails')['accountDetails']['JOINTHOLDERS_CUSTID'];
				$existing_applicants = substr_count($existing_applicants, '|') + 1;
				if(isset($requestData['inputFieldCheck'][50])){ // Del Applicant
					$existing_applicants = $existing_applicants - 1;
				}
				if(isset($requestData['inputFieldCheck'][49])){ // Add Applicant
					$existing_applicants = $existing_applicants + 1;
				}
			}
			$primary_minor = 'N';
			if(isset(Session::get('currCustomerDetails')['data']['customerDetails']['CUST_MINOR_FLG'])){
				$primary_minor = Session::get('currCustomerDetails')['data']['customerDetails']['CUST_MINOR_FLG'];
			}
			$mop = null;
			foreach($requestData['amendNewData'] as $key => $value){
				if (strpos($key, 'input_24_') === 0) {
					$mop = $value;
					break;
				}
			}

			if($existing_applicants == 1 && $primary_minor == 'N' && $mop == 1){ // Self
				$rule = 'OK'; 
			}
			if($existing_applicants == 2 && $primary_minor == 'N' && ($mop == 2 || $mop == 3)){ // Either or Survivor // Former or Survivor
				$rule = 'OK';
			}
			if($existing_applicants == 2 && $primary_minor == 'Y' && $mop == 7){ //Minor By Natural guardian
				$rule = 'OK';
			}
			if($existing_applicants >= 2 && $primary_minor == 'N' && $mop == 4){ // Jointly
				$rule = 'OK';
			}
			if($existing_applicants >= 3 && $primary_minor == 'N' && $mop == 6){ // By anyone or Survivor
				$rule = 'OK';
			}
			if($existing_applicants >= 3 && $primary_minor == 'Y' && $mop == 8){ // Minor By Either of Natural guardians
				$rule = 'OK';
			}

		}

		if($rule == 'OK'){
			return true;
		}else{
			return false;
		}
		}catch(\Throwable $e) {
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','isMOPokay',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}

	public static function checkFieldValidation($fieldArray,$amendCrfData,$inputFieldCheck,$tokenKey = ''){
		try{
		for($ls=0;count($amendCrfData)>$ls;$ls++)
		{
			if(isset($inputFieldCheck[$amendCrfData[$ls]['id']]) && $inputFieldCheck[$amendCrfData[$ls]['id']] == 'Y'){
				$searchKeyId = 'input_'.$amendCrfData[$ls]['id'].'_'.$amendCrfData[$ls]['counter'];
			}else{
				$searchKeyId = 'amend_toggle_'.$amendCrfData[$ls]['id'].'_'.$amendCrfData[$ls]['counter'];
			}
		
			if(isset($fieldArray[$searchKeyId]) && $fieldArray[$searchKeyId] != ''){
				$amendCrfData[$ls]['newValue'] = $fieldArray[$searchKeyId];
			}
		}
		
		for($lsa=0;count($amendCrfData)>$lsa;$lsa++){
			$getArraydata = config('amendvalidation.'.$amendCrfData[$lsa]['fieldName']);
				// echo '<pre>';print_r($amendCrfData);exit;
		
			if(isset($amendCrfData[$lsa]['fieldName']) && $amendCrfData[$lsa]['fieldName']== '_AADHAR_NUMBER'){
				$amendCrfData[$lsa]['newValue'] = CommonFunctions::decryptRS($amendCrfData[$lsa]['newValue'],$tokenKey);
			}

			$pattern = '';
			$message = '';
			$classCheck = isset($getArraydata['class']) && $getArraydata['class'] != ''?$getArraydata['class']:'';
			$maxLength = isset($getArraydata['maxlength']) && $getArraydata['maxlength'] != ''?$getArraydata['maxlength']:'';

			switch($classCheck){
				case 'numeric':
					$pattern = '/[0-9]/i';
					$message = 'Please Enter Valid Data.';
				break;
	
				case 'specialcase':
					$pattern = '/(^[a-zA-Z0-9\+_\-].+)(\.[a-zA-z0-9\+_\-])*@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/';
					$message = 'Please Enter Valid Data.';
				break;
	
				case 'string':
					$pattern = '/[a-zA-Z]/i';
					$message = 'Please Enter Valid Data.';
				break;
	
				case 'spacestring':
					$pattern = '/[ a-zA-Z]/i';
					$message = 'Please Enter Valid Data.';
				break;
				
				case 'alphanumeric':
					// $pattern = '/[a-z0-9 (/),@.#&-\\]/i';
					$pattern = '/[a-z0-9 (),@.#&-\/\\/]/i';
					$message = 'Please Enter Valid Data.';
				break;
	
				case 'aplhaword':
					$pattern = '/[a-zA-Z]/i';
					$message = 'Please Enter Valid Data.';
				break;	
				
				case 'strnumbercombo':
					$pattern = '/[a-zA-Z0-9]/i';
					$message = 'Please Enter Valid Data.';
				break;
	
				case 'pincode':
					$pattern = '/[0-9]/i';
					$message = 'Please Enter Valid Data.';
				break;

				case 'pan':
					$pattern = '/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/';
					$message = 'Please Enter Valid Data.';
				break;

				case 'word':
					$pattern = '/[a-zA-Z]/i';
					$message = 'Please Enter Valid Data.';
				break;
	
				// default :
				// 	$pattern = '/[ a-zA-Z0-9!@#‘.-]+$/i';
				// 	$message = 'Please Enter Valid Data.';
				// break;
			}
		
			if($pattern != ''){
				
			if(!preg_match($pattern,$amendCrfData[$lsa]['newValue'])){
				return ['status'=>'fail','message'=>$message];
			}
			}
			
			if($maxLength != ''){

				if($maxLength < strlen($amendCrfData[$lsa]['newValue'])){
					return ['status'=>'fail','message'=>$message];
				}
			}


			return ['status'=>'success','message'=>''];
		}
			
		}catch(\Throwable $e){
			if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			$eMessage = $e->getMessage();
			CommonFunctions::addLogicExceptionLog('Helpers/AmendRules','checkFieldValidation',$eMessage,'','');
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	}
	}
}