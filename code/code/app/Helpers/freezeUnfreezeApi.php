<?php
namespace App\Helpers;

use App\Helpers\EncryptDecrypt;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\CommonFunctions;

class freezeUnfreezeApi extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //checks token exists or not
        /*if(Cookie::get('token') != ''){
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            //get claims from token
            $claims = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($claims),true);
            //get auditeeId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];
        }*/
    }

	public static function freezeApi($accountId, $customerId, $freezeCode, $formId){
		$client = new \GuzzleHttp\Client();        
        $url = config('constants.APPLICATION_SETTINGS.FREEZE');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.FREEZE_UNFREZZE_KEY');
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();

        $data = Array(
        	 "header"=> Array(
	            'isEnc'=> 'N',
	            'apiVersion'=> '1.0',
	            'cifId'=> '103349881',
	            'languageId'=> '1',
	            'channelId' => 'CUBE',
                'requestUUID' => strval($current_timestamp),
	            'sVersion'=> '20',
	            'serReqId'=> 'ESB_ACCOUNT_FREEZE_FIS',
	            'timeStamp'=> "13122021"
	        ),
	        'request' => Array(
	            'FIXML'=> Array(
	                'Body'=> Array(
	                    'AcctFreezeAddRequest'=> Array(
	                        'AcctFreezeAddRq'=> Array(
                                    'AcctId'=>  $accountId, 
	                                // 'AcctId'=> "18210200000676",
                                    'CustId'=> $customerId,		
									'FreezeCode'=> $freezeCode,
									'FreezeReasonCode'=> '012',
									'TemplateInfo' => Array(
										'TemplateId' => ''
									),
	                        ),
	                        'AcctFreezeAdd_CustomData' => Array(),
	                    )
	                )
    			)
            )
 		
	    );
        // Self::unfreezeApi($accountId, $customerId, 'N');
        // exit;
        // echo "<pre>";print_r($data);
        // $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $serviceName = 'ACCOUNT_FREEZE';
        $payload = json_encode(['gatewayRequest'=>$data]);

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

        $response = $guzzleClient->getBody();
        $response = json_decode($response,true);
        // echo "<pre>";print_r($response);
        // $freezeresponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
   		$responseTime = Carbon::now()->diffInSeconds($requestTime); 
        if(isset($response['gatewayResponse']) && $response['gatewayResponse'] != ''){
            $responsed = $response['gatewayResponse'];
            $encryptedResponse = json_encode($responsed['response']);
        }else{
            $responsed = json_encode($response);
                $encryptedResponse =json_encode($response);
        }
   		

   		$saveFreezeService = CommonFunctions::saveApiRequest($serviceName,$url,$payload,$encryptedResponse,json_encode($data),$responsed, $formId, $responseTime);

        if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess']){
            return $response['gatewayResponse']['status']['isSuccess'];
        }else{
            return false;
        }

     //    // $freezeresponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
     //    $response = $encryptedResponse;
     //    if (isset($response['gatewayResponse']['status']) && $response['gatewayResponse']['status'] = 'success') {
     //        return 'success';
     //    }
	}

	public static function unfreezeApi($param){
        try {

            $getData = (array) current($param);
            $accountId = $getData['0'];
            $formId = $getData['1'];

		$client = new \GuzzleHttp\Client();
        $url = config('constants.APPLICATION_SETTINGS.UNFREEZE');
        $client_id = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.NEW_CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.FREEZE_UNFREZZE_KEY');
        $current_timestamp = Carbon::now()->timestamp;
        $requestTime = Carbon::now();

	
		$data = Array(
        	 "header"=> Array(
	            'isEnc'=> 'N',
	            'apiVersion'=> '1.0',
	            'cifId'=> '100212262',
	            'languageId'=> '1',
	            'channelId'=> 'CUBE',
	            'requestUUID' => strval($current_timestamp),
	            'sVersion'=> '20',
	            'serReqId'=> 'ESB_ACCOUNT_UNFREEZE_FIS',
	            'timeStamp'=> "13122021"
	        ),
	        'request' => Array(
	            'FIXML'=> Array(
	                'Body'=> Array(
	                    'AcctUnFreezeAddRequest'=> Array(
	                        'AcctUnFreezeAddRq'=> Array(
	                                'AcctId'=>  $accountId,
									//'CustId'=> $customerId,
									'TemplateInfo' => Array(
										'TemplateId' => ''
									),
	                        ),
	                        'AcctUnFreezeAdd_CustomData' => Array(),
	                    )
	                )
    			)
            )
 		
	    );
        
        // echo "<pre>";print_r($data);
        // $data['request'] = EncryptDecrypt::AES256Encryption(json_encode($data['request']),$encrypt_key);
        $serviceName = 'ACCOUNT_UNFREEZE';
        $payload = json_encode(['gatewayRequest'=>$data]);

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
         $response = $guzzleClient->getBody();
        $response = json_decode($response,true);
        // echo "<pre>";print_r($response);

     
    	// echo "<pre>";print_r($response);
        // $freezeresponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);

            $responseTime = Carbon::now()->diffInSeconds($requestTime);
            
            if(isset($response['gatewayResponse']) && is_array($response['gatewayResponse'])){
        $responsed = $response['gatewayResponse'];
        $encryptedResponse = json_encode($responsed['response']);
            }else{
                $encryptedResponse = '';
                $responsed = '';
            }
            $saveFreezeService = CommonFunctions::saveApiRequest($serviceName, $url, $payload, $encryptedResponse, json_encode($data), $responsed, $formId, $responseTime);
            
            if (isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess']) {
                    // return $response['gatewayResponse']['status']['isSuccess'];
                return ['status'=>'success','message'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
            } else {
                return ['status'=>'Error','message'=>$response['gatewayResponse']['status']['message'],'data'=>[]];
        }

     //    $encryptedResponse = json_encode($responsed);
     //    // $unfreezeresponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
     //    $response = $encryptedResponse;
     //    echo "<pre>";print_r('response api');
   		// $responseTime = Carbon::now()->diffInSeconds($requestTime); 

     //    $saveUnfreezeService = CommonFunctions::saveApiRequest($serviceName,$url,$payload,$encryptedResponse,json_encode($data),$responsed,'11111', $responseTime);

 	}
        catch (Exception $e){
            echo $e;
        }
 	}
}

?>