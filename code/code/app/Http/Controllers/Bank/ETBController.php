<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Helpers\CommonFunctions;
use App\Helpers\DelightFunctions;
use App\Helpers\Rules;
use App\Helpers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use File;
use DB;

class ETBController extends Controller
{
	public static function checkETBvalidation($customerDetails,$schemeCodeDetails)
    {
    	$validation = true;
        $validationMsg = 'Validation failed for following scenarios: <br>';

    	$applicantDob = Carbon::parse($customerDetails['DATE_OF_BIRTH'])->age;

        if($customerDetails['CUST_CONST'] != '001'){
            $validation = false;
            $validationMsg .= 'Non Individual customer Id <br>';
            return $validationMsg;
        }
        switch ($schemeCodeDetails['account_type']) {
        	case '1':
        		$panMandatoryField = $schemeCodeDetails['pan_mandatory'];
        		break;
            case '2':
                $panMandatoryField = $schemeCodeDetails['pan_mandatory'];
                break;
        	case '3':
        		$panMandatoryField = $schemeCodeDetails['pan_mandatory'];
        		break;
        	
        	default:
        		$panMandatoryField = '';
        		break;
        }
        // echo "<pre>";print_r($customerDetails);exit;

        if($panMandatoryField == 'Y' && $customerDetails['PAN_GIR_NUM'] == '' && $customerDetails['PAN2_NUM'] == ''){
            $validation = false;
            $validationMsg .= 'PAN number is mandatory for selected scheme Code <br>';
            return $validationMsg;
        }

    	//checking on Saving Account Details
    	// if ($schemeCodeDetails['min_age'] > $applicantDob || $schemeCodeDetails['max_age'] < $applicantDob) {
    		// $validation = false;
        	// $validationMsg .= 'Customer age<br>';
    	// }

    	if ($applicantDob < 18 && $customerDetails['CUST_MINOR_FLG'] != 'Y' ) {
    		$validation = false;
        	$validationMsg .= 'Minor validation failed <br>';
    	}

    	if($customerDetails['ISA_STATUS'] != 'Y'){
    		$validation = false;
        	$validationMsg .= 'ISA status <br>';
    	}

        if ($customerDetails['FATCA_PLACEOFBIRTH'] == '') {
            $validation = false;
            $validationMsg .= 'Place of Birth validation failed <br>';
        }

    	$checkCountries = ['FATCA_NATIONALITY','FATCA_CNTRY_OF_RESIDENCE','FATCA_BIRTHCOUNTRY'];
    	$checkCountryMsg = ['Nationality','Country Of Residence','Birth Country'];

    	for ($i=0; $i < count($checkCountries); $i++) { 
    		if ($customerDetails[$checkCountries[$i]] != 'IN') {
    			$validation = false;
	        	$validationMsg .= $checkCountryMsg[$i].' : '.$customerDetails[$checkCountries[$i]].' <br>';
    		}
    	}

    	if($customerDetails['CUST_NRE_FLG'] != 'N'){
    		$validation = false;
        	$validationMsg .= 'NRE customer not allowed <br>';
    	}

    	if ($schemeCodeDetails['account_type'] == '3' && $schemeCodeDetails['staff_customer'] == 'Staff' && $customerDetails['STAFF_FLAG'] != 'Y') {
    		$validation = false;
        	$validationMsg .= 'Staff validation failed<br>';
    	}
	
		if ($schemeCodeDetails['account_type'] == '2' && $customerDetails['STAFF_FLAG'] != 'N') {
    		$validation = false;
        	$validationMsg .= 'Staff Customer not allow for current account<br>';
    	}
			#region required fields.
			$aadharNumber = $customerDetails['AADHAR_NUM']??'';
			$passportNumber = $customerDetails['PASSPORT_NUM']??'';
			$voterId = $customerDetails['VOTER_NUM']??'';
			$drivingLicence = $customerDetails['DRIVINGLIC_NUM']??'';
			//Customer ID - KYC debit freeze
			if($customerDetails['KYC_DEBIT_FREEZ'] == "Y"){
				$validation = false;
				$validationMsg .= 'Customer ID - KYC debit freeze<br>';
			}
			//KYC status not updated
			if($customerDetails['KYC_STATUS'] == "P"){
				$validation = false;
				$validationMsg .= 'KYC status not updated<br>';
			}
			if($aadharNumber == "" && $passportNumber == "" && $voterId == "" && $drivingLicence == ""){
				$validation = false;	
				$validationMsg .= 'As proof aadhar, passport, voter id, driving license any one requeired<br>';
			}
			#endregion
			
			if ($validation) {
				return '';
			}else{
				return $validationMsg;
			}
			
		} // End ETB validator

}