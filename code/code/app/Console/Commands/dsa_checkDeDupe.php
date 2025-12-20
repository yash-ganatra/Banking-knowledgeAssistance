<?php

namespace App\Console\Commands;
use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;

class dsa_checkDeDupe extends Command
{

    protected $signature = 'command:dsa_checkDeDupe';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
     
		sleep(15);
		
		$_MAX_RECORDS_TO_PROCESS = 10;

        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

		$dsaInstance = DB::connection('dsa');
        //$cubeQueryInstance = DB::connection('oracle');
		
		$oaoUserDetails = $dsaInstance->table($dsaSchema.'.USER_DETAILS')
								->select('USER_DETAILS.ID', 'USER_DETAILS.QID')
								->where('OAO_APPLICATION_STATUS', '>=', 33)
								->where('QID', '!=', null)
								->where(function ($query) {
										$query->where('QID_STATUS', null)
											  ->orWhere('QID_STATUS', 'Pending');
									})
								->orderBy('USER_DETAILS.ID','DESC')
								->take($_MAX_RECORDS_TO_PROCESS)->get()->toArray(); 
		
		if (count($oaoUserDetails) == 0) { // Nothing here!
			return '';
		}	
		
		echo "\xA"; echo "Records: ".count($oaoUserDetails);		
				
		foreach ($oaoUserDetails as $record) {
			
			echo "\xA"; print_r('Processing: '.$record->id); //exit;
			
			$response = Self::checkDeDupe($record->qid, 'DSA_'.$record->id);
			
			echo "\xA"; print_r('Response: '.$response); //exit;
			
			if($response != 'FAILED'){
				echo "\xA"; print_r('Inside'); //exit;
								
				$dsaInstance->table($dsaSchema.'.USER_DETAILS')
									->where('ID', $record->id)
									->update(['QID_STATUS' => $response]);								
			
				//$dsaInstance->commit();
				
				echo "\xA"; print_r('After'); //exit;										
				print_r(' Updated '.$response); echo "\xA"; 
				//DB::commit($dsaInstance);
				//print_r($customerOvdData['formId']); print_r($response['data']);
			}
			
		}

	} // End of function
		
    public static function checkDeDupe($queryId,$formId)
    {
        $url = config('constants.APPLICATION_SETTINGS.LIVY_CHECK_STATUS_URL');
        $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
        $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
        $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
        $encrypt_key = config('constants.APPLICATION_SETTINGS.DEDUPE_ENCRYPT_KEY');

        $RequestUUID = CommonFunctions::getRandomValue(4);

        $current_timestamp = Carbon::now()->timestamp;

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
                                //'requestUUID' => '895547861',
                                'requestUUID' => strval($queryId.'_'.'CHECKLIVY'),
                                "sVersion" => "20",
                                "serReqId" => "LIVYCheckStatus",
                                'sessionId' => '5932216656835406787',
                                'timeStamp' => strval($current_timestamp),
                            ),
                        'request' => Array
                            (
                                "queryId" => $queryId
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
        
        if (!isset(json_decode($guzzleClient->getBody(),true)['gatewayResponse'])){
            return  []; 
        }

        $response = json_decode($guzzleClient->getBody(),true)['gatewayResponse'];
        if($response['status']['isSuccess'])
        {
            $encryptedResponse = json_encode($response['response']);
            $LIVYStatusResponse = json_decode(EncryptDecrypt::AES256Decryption($encryptedResponse,$encrypt_key),true);
			
			//echo "\xA"; print_r($LIVYStatusResponse); 
			
			$returnResponse = 'FAILED';
			
			if(isset($LIVYStatusResponse['data'][0]['decisionFlag'])){
					switch(strtoupper($LIVYStatusResponse['data'][0]['decisionFlag'])){
					case 'NO MATCH':
						$returnResponse = 'No Match';
						break;
					case 'PENDING':
						$returnResponse = 'Pending';
						break;
					case 'MATCH':
						$returnResponse = 'Match';
						break;
					case 'DEDUPE API HAS FAILED':
						$returnResponse = 'Dedupe Api has Failed';
						break;
					default:
						$returnResponse = 'FAILED';
				}
			}
			            
        }else{
			$returnResponse = 'FAILED'; 
		}

        CommonFunctions::saveApiRequest('DEDUPE_STATUS',$url,$payload,$response,                                                                                json_encode($data),$response,1, $responseTime);
        return $returnResponse;
    }


}
