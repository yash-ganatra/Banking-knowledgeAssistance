<?php
namespace App\Helpers;

use Illuminate\Support\Arr;
use Intervention\Image\Facades\Image;
use App\Helpers\Api;
use Sop\JWX\JWE\JWE;
use Carbon\Carbon;
use Session;
use Route;
use Cache;
use File;
use DB;
use PDF;

class OaoCommonFunctions {
	public static function createOaoCustomerId($formId, $oaoUserId ){

		$customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('form_id',$formId)->get()->toArray();
		$customerOvdDetails = (array) current($customerOvdDetails);
	
		$apiDetails = Api::createcustomerid($customerOvdDetails);
		// echo "<pre>";print_r($apiDetails);exit;

		if($apiDetails['status'] == 'Success'){
			echo "<pre>";print_r('cust id done');
			Self::updateOaoFields('OAO.USER_DETAILS',$oaoUserId,'CUSTOMER_ID',$apiDetails['data']);
			Self::updateOaoFields('OAO.ACCOUNT_LOG',$oaoUserId,'CUSTOMER_ID','Y');
			OaoCommonFunctions::UpdateOaoApplicationStatus($oaoUserId, 91);
		}else{

			echo "<pre>";print_r('cust id failed');
			Self::updateOaoFields('OAO.ACCOUNT_LOG',$oaoUserId,'CUSTOMER_ID','N');
			OaoCommonFunctions::UpdateOaoApplicationStatus($oaoUserId, 90);
		}

	}

	public static function UpdateOaoApplicationStatus($userId, $status ){

    	$dsaInstance = DB::connection('dsa');
       	$dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');

       	$statusChanged =  $dsaInstance->table($dsaSchema.'.USER_DETAILS')->where('id', $userId)->update(['OAO_APPLICATION_STATUS'=>$status]);
		
		// echo "<pre>";print_r($apiDetails);
		echo "<pre>";print_r('status change');
		echo "<pre>";print_r($statusChanged);

		$dsaInstance->disconnect();
	}

	public static function updateOaoFields($table,$oaoUserId,$field,$value)
	{
    	$dsaInstance = DB::connection('dsa');
		$dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
    	$id = ($table == $dsaSchema.'.USER_DETAILS' ? 'ID': 'USER_ID');
		$updateOaoFields = $dsaInstance->table($table)->where($id,$oaoUserId)->update([$field=>$value]);
		$dsaInstance->disconnect();
	}

	public static function checkOaoFundingStatus($formId, $oaoUserId){

    	//api for check funding
		//testing mark as Yes
		$dsaInstance = DB::connection('dsa');
        $cubeInstance = DB::connection('oracle');
				
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

		DB::table('FINCON')->where('form_id',$formId)->update(['FUNDING_STATUS'=>'Y']);
		Self::updateOaoFields($dsaSchema.'.ACCOUNT_LOG',$oaoUserId,'FUNDING_STATUS','Y');

		echo "<pre>";print_r('funding done');

	}

	public static function createOaoAccountId($formId, $oaoUserId){

		$dsaInstance = DB::connection('dsa');
        $cubeInstance = DB::connection('oracle');
				
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

		$customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('form_id',$formId)->get()->toArray();
		$customerOvdDetails = (array) current($customerOvdDetails);
		
		$apiDetails = Api::createaccountid($formId,$customerOvdDetails['customer_id']);
		if($apiDetails['status'] == 'Success'){
			echo "<pre>";print_r('accnt id done');
			Self::updateOaoFields($dsaSchema.'.USER_DETAILS',$oaoUserId,'ACCOUNT_NUMBER',$apiDetails['data']);
			Self::updateOaoFields($dsaSchema.'.ACCOUNT_LOG',$oaoUserId,'ACCOUNT_ID','Y');
			OaoCommonFunctions::UpdateOaoApplicationStatus($oaoUserId, 101);
		}else{
			Self::updateOaoFields($dsaSchema.'.ACCOUNT_LOG',$oaoUserId,'ACCOUNT_ID','N');
			echo "<pre>";print_r('accnt id failed');
			OaoCommonFunctions::UpdateOaoApplicationStatus($oaoUserId, 100);
		}
	}

	/*
	public static function freezeUnfreeze($oaoUserId, $type, $freezeCode){
		
		$finacleQueryInstance = DB::connection('oracle2');

		$encryption = 'N';

		$oaoDetails = $finacleQueryInstance->table('OAO.USER_DETAILS')->where('ID', $oaoUserId)->get()->toArray();
        $oaoDetails = current($oaoDetails);
			// echo "<pre>";print_r($oaoDetails);exit;

		if ($type != 'Unfreeze') {
			$freeze = freezeUnfreezeApi::freezeApi($oaoDetails->account_number, $oaoDetails->customer_id, $freezeCode, $encryption,$oaoDetails->id);
			return $freeze;
		}else{
			// echo "<pre>";print_r($oaoDetails);exit;
			$unFreeze = freezeUnfreezeApi::unfreezeApi($oaoDetails->account_number, $oaoDetails->customer_id, $encryption, $oaoDetails->id);
			return $unFreeze;
		}

		$finacleQueryInstance->disconnect();
	}*/

	public static function updateDsaActivityLog($dsaId, $process, $comment, $cubeId='', $filter=''){
		
				
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

        $dsaInstance = DB::connection('dsa');
		
		$updateDSA1 = $dsaInstance->table($dsaSchema.'.DSA_ACTIVITY_LOG')
										->insert([
												'DSA_ID' => $dsaId, 'CUBE_ID' => $cubeId,
												'PROCESS' => $process, 'COMMENT' => $comment, 'FILTER' => $filter
												]);								
	
	}

	public static function getBranch()
    {

        $branch = DB::table('OAO.BRANCH')->select('branch_name','branch_id')->pluck('branch_name','branch_id')->toArray();

        foreach ($branch as $key => $value) {
               $branch[$key] = $value.' - '.$key;
        }

        return $branch;
    }

    public static function getRegional()
    {
        $regional = array();
        $regional = DB::table('OAO.REGION_MASTER')->orderBy('id')->pluck('region_name','id')->toArray();
        return $regional;
    }

    public static function getCluster()
    {
        $cluster = array();
        $cluster = DB::table('OAO.CLUSTER_MASTER')->orderBy('id')->pluck('cluster_name','id')->toArray();
        return $cluster;
    }

    public static function getZone()
    {
        $zone = array();
        $zone = DB::table('OAO.ZONE_MASTER')->orderBy('id')->pluck('zone_name','id')->toArray();
        return $zone;
    }

	public static function fetchprofile($userId, $formId, $profileId)
    {
		try{
        $client = new \GuzzleHttp\Client();        

		$url = config('constants.APPLICATION_SETTINGS.VKYC_FETCHPROFILE');
        $client_id = config('constants.APPLICATION_SETTINGS.DSA_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.DSA_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.DSA_AUTHORIZATION');

        $current_timestamp = Carbon::now()->timestamp;

        $h1 = hash('sha256', $current_timestamp);
        $h2 = hash('md5', $current_timestamp);
        $uuid = substr($h1,0,8).'-'.substr($h1,-4).'-'.substr($h1,10,4).'-'.substr($h2,0,4).'-'.substr($h2,-12);


        $data = [
                    'header' => [
                        //"cifId": "100212262",                        
                        //"channelId": "ESB",                        
                        'apiVersion' => '1.0',
                        'languageId' => '1',
                        'sVersion' => '20',
                        'isEnc' => 'N',                        
                        'cifId' => '100812421',
                        'channelId' => 'CUBE',
                        'requestUUID' => $uuid,
                        'serReqId' => 'ESB_PROFILE_FETCHPROFILE_IDFY',
                        'timeStamp' =>  $current_timestamp
                    ],
                    'request' => [
                        //"reference_id" =>  '12345678', 
                        'profileId' => $profileId
                     
                    ]
                ]; 

        //echo "<pre>";print_r($data);
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

        $response = json_decode($guzzleClient->getBody(),true);

		$saveService = CommonFunctions::saveApiRequest('VKYC_STATUS',$url,'','',json_encode($data),$response, $formId, $responseTime);
        //echo "<pre>Status:";print_r($profileId); print_r($response);exit;
		return $response;

		}catch(Exception $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            //$eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            //CommonFunctions::addLogicExceptionLog('Helpers/OaoCommonFunction','fetchprofile',$eMessage,'',1);
            //return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
        
    }


}