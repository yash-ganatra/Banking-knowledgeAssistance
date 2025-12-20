<?php

namespace App\Console\Commands;
use DB;
use Carbon\Carbon;
use App\Helpers\Api;
use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;

class dsa_generateDeDupe extends Command
{

    protected $signature = 'command:dsa_generateDeDupe';

    protected $description = 'Command description';

    

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
     
		sleep(5);
	 
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');
        
        $_MAX_RECORDS_TO_PROCESS = 5;

		$dsaInstance = DB::connection('dsa');
        $cubeInstance = DB::connection('oracle');
		
		// Generate QID
		 $oaoUserDetails = $dsaInstance->table($dsaSchema.'.USER_DETAILS')
							->leftjoin($dsaSchema.'.AADHAAR_RESPONSES','AADHAAR_RESPONSES.USER_ID','USER_DETAILS.ID')
								->select('USER_DETAILS.ID', 'USER_DETAILS.MOBILE_NUMBER', 'USER_DETAILS.PAN_NUMBER', 
											'USER_DETAILS.ADHAAR_NUMBER', 
										  'AADHAAR_RESPONSES.NAME', 'AADHAAR_RESPONSES.DOB', 'AADHAAR_RESPONSES.GENDER', 
										  'AADHAAR_RESPONSES.HOUSE', 'AADHAAR_RESPONSES.VTC', 'AADHAAR_RESPONSES.PC'
										)
								->where('OAO_APPLICATION_STATUS', '>=', 33)
								->where('CUBE_AOF', null)
								->where('QID', null)
								->orderBy('USER_DETAILS.ID','DESC')
								->take($_MAX_RECORDS_TO_PROCESS)->get()->toArray(); 
		
		echo "\xA"; echo "Records: ".count($oaoUserDetails);
		
		if (count($oaoUserDetails) == 0) { // Nothing here!
			return '';
		}	
				
		foreach ($oaoUserDetails as $record) {
			$customerOvdData[] = [];
			$customerOvdData['formId'] = 'DSA_FORM_'.$record->id;
			$customerOvdData['aof_number']= 'DSA_AOF_'.$record->id;
			$customerOvdData['pancard_no'] = $record->pan_number;
			$customerOvdData['mobile_number'] = $record->mobile_number;
			$customerOvdData['aadhar_number'] = $record->adhaar_number;			
			$customerOvdData['full_name'] = $record->name;
			$customerOvdData['gender'] = $record->gender;
			$customerOvdData['dob'] = $record->dob;			
			$customerOvdData['email'] = '';
			$customerOvdData['per_address_line1'] = $record->house;
			$customerOvdData['per_city'] = $record->vtc;
			$customerOvdData['per_pincode'] = $record->pc;
			
			echo "\xA"; echo "Processing: ".$record->id;
			
			$response = Self::triggerDeDupe($customerOvdData);
			if(isset($response['status']) && $response['status'] == 'Success'){
				$oaoUserDetails = $dsaInstance->table($dsaSchema.'.USER_DETAILS')
										->where('ID', $record->id)
										->update(['QID' => $response['data']]);								
										
				DB::commit($dsaInstance);
				
				$oaoStatus = $cubeInstance->table($cubeSchema.'.OAO_STATUS')						
						->insert(['OAO_ID' => $record->id, 'IS_COPIED_1' => 'Y', 'QUERY_ID' => 'Y']);
						
				DB::commit($cubeInstance);						
				
				//print_r($customerOvdData['formId']); print_r($response['data']);
			}
			
		}	

	} // End of function
	
	public static function triggerDeDupe($customerOvdData)
    {        
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
		
        $formId = explode('_', $customerOvdData['formId']);
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');

        $dsaInstance = DB::connection('dsa');
        $referenceNumber = Api::aadharValutSvc('001189092',$customerOvdData['aadhar_number'],$formId[2]);
        if(isset($referenceNumber['status']) && $referenceNumber['status'] == 'Success'){
            if(isset($referenceNumber['data']['response']['referenceKey']) && $referenceNumber['data']['response']['referenceKey'] != ''){
                $referenceNumber = $referenceNumber['data']['response']['referenceKey'];
                $dsaInstance->table($dsaSchema.'.AADHAAR_RESPONSES')->where('USER_ID',$formId[2])->update(['VAULT_NUMBER' => $referenceNumber]);
            }else{
                return false;
            }
        }else{
            return false;
        }  

        $current_timestamp = Carbon::now()->timestamp;

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
                                'requestUUID' => strval($customerOvdData['aof_number'].'_'.'CHKDEDUPE'),
                                'serReqId' => 'LIVYGenerateQID',
                                'sessionId' => '5932216656835406787',
                                //'timeStamp' => '1519731538269'
                                'timeStamp' => $current_timestamp,
                                
                            ),
                        'request' => Array
                            (
                                "custName" => $customerOvdData['full_name'],
                                "gender" => $customerOvdData['gender'],
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
                                "freeText1" => "",
                                "freeText2" => "",
                                "freeText3" => "",
                                "sourceType"=>"DSA",
                                "entity"=>"Individual",
                                "sourceAppNum"=>$customerOvdData['aof_number'],
                                "inquiryAgencyId"=> $inquiryAgencyId,
                            )
                    );
		
        
		//print_r($data); exit;
		
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
			//print_r($response); 
			if($response['status']['isSuccess'] == "true"){
				$encryptedResponse = json_encode($response['response']);
				$LIVYGenerateQIDResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
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
              json_encode($data),$response,1, $responseTime);
        //print_r(['status'=>$status,'data'=>$queryId,'errorCode'=>$errorCode,'message'=>$message]);
        return ['status'=>$status,'data'=>$queryId,'errorCode'=>$errorCode,'message'=>$message];
        }
        else{
            $saveService = CommonFunctions::saveApiRequest('DEDUPE_QID',$url,$payload,$response,
              json_encode($data),$response,1, $responseTime);
			//print_r(['status'=>'Error','data'=>'','errorCode'=>'','message'=>'API Error: '.http_build_query($response,'',', ')]);														
            return ['status'=>'Error','data'=>'','errorCode'=>'','message'=>'API Error: '.http_build_query($response,'',', ')];
        }
    }			 

}
