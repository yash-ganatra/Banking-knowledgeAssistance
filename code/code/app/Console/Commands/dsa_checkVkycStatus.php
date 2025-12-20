<?php

namespace App\Console\Commands;
use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\OaoCommonFunctions;
use App\Helpers\freezeUnfreezeApi;
use App\Helpers\EncryptDecrypt;

class dsa_checkVkycStatus extends Command
{ 

    protected $signature = 'command:dsa_checkVkycStatus';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
     
		//sleep(15);
		
		$_MAX_RECORDS_TO_PROCESS = 5;

        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

		$dsaInstance = DB::connection('dsa');
        $cubeInstance = DB::connection('oracle');
		
		$vkycPending = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
								->select('ID', 'OAO_ID', 'FORM_ID', 'VKYC_PROFILE_ID', 'ACCOUNT_NUMBER')
								->where('CUSTOMER_ID', 'Y')
                                ->where('ACCOUNT_ID', 'Y')
                                ->whereNotNull('VKYC_PROFILE_ID')
                                ->whereNull('VKYC_STATUS')
								->orderBy('ID','DESC')
								->take($_MAX_RECORDS_TO_PROCESS)->get()->toArray(); 
		
		if (count($vkycPending) == 0) { // Nothing here!
			return '';
		}	
		
		echo "\xA"; echo "Records: ".count($vkycPending);		
				
		foreach ($vkycPending as $record) {
			
			echo "\xA"; print_r('Processing: '.$record->oao_id.' form '.$record->form_id.' profile '.$record->vkyc_profile_id); //exit;
			
            $response = OaoCommonFunctions::fetchprofile($record->oao_id, $record->form_id, $record->vkyc_profile_id);

            if(isset($response['gatewayResponse']['status']['isSuccess']) && $response['gatewayResponse']['status']['isSuccess']){

                if(isset($response['gatewayResponse']['response']['status']) && $response['gatewayResponse']['response']['status']=='completed'){         
    
                    $updateVideoEkycDetailsDSA = $dsaInstance->table($dsaSchema.'.ACCOUNT_LOG')
                                    ->where('USER_ID', $record->oao_id)->update(['VKYC_VERIFICATION'=>'completed']);
                    
                    $updateVideoEkycDetailsCUBE = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
                                    ->where('OAO_ID', $record->oao_id)->update(['VKYC_STATUS'=>'completed']);
    
                    //return json_encode(['status'=>'success','msg'=>'Video E-KYC Completed.','data'=>[]]);                
                    

                    echo "\xA"; echo 'VKYC Completed';
                }else{
                    //return json_encode(['status'=>'fail','msg'=>'Video E-KYC status not yet approved.','data'=>[]]);
                    echo "\xA"; echo 'VKYC NOT Completed for profile: '.$record->vkyc_profile_id.' status: '.$response['gatewayResponse']['response']['status'];
                }
                     
            }else{
                echo 'VKYC Error!';  echo "\xA"; print_r($response);
                //$msg = $response['gatewayResponse']['status']['message'];
                //$errorCode = $response['gatewayResponse']['status']['statusCode'];
                //return json_encode(['status'=>'Error - '.$errorCode,'msg'=> 'Unable to get Vkyc status! Please retry..','data'=>[]]);
            }
          
		
			
		}

         $unfreezeKycPending = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
                                            ->select('CUSTOMER_OVD_DETAILS.KYC_UPDATE','OAO_STATUS.FREEZE_3','OAO_STATUS.FORM_ID','OAO_STATUS.CUSTOMER_NUMBER','OAO_STATUS.ACCOUNT_NUMBER')
                                            ->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','OAO_STATUS.FORM_ID')
                                            ->where('OAO_STATUS.VKYC_STATUS','completed')
                                            ->where(function ($query)   {
                                                    $query->where('CUSTOMER_OVD_DETAILS.KYC_UPDATE',NULL)
                                                          ->orWhere('OAO_STATUS.FREEZE_3',NULL);
                                                    })
                                            ->orderBy('OAO_STATUS.ID','DESC')
                                            ->take(5)->get()->toArray();


        foreach($unfreezeKycPending as  $value) {
            $kycUpdate = $value->kyc_update;
            $unfreeze = $value->freeze_3;
            $kycProcced = false;

            if($unfreeze == '' || $unfreeze == null){
                $unfreezeResponse = freezeUnfreezeApi::unfreezeApi($value->account_number, $value->form_id);
                if($unfreezeResponse){
                    $cubeInstance->table($cubeSchema.'.OAO_STATUS')->where('FORM_ID',$value->form_id)
                                                                   ->update(['FREEZE_3' => 'Y']);
                    $kycProcced = true;
                }
            }else{
                $kycProcced = true;
            }
            

            if($kycProcced == true && $kycUpdate == null || $kycUpdate != ''){
                $kycUpdateResponse = Api::kycUpdate($value->form_id,$value->customer_number,'VIDEO');
                if($kycUpdateResponse == 'Success'){
                    $cubeInstance->table($cubeSchema.'.CUSTOMER_OVD_DETAILS')->where('FORM_ID',$value->form_id)
                                                                             ->update(['KYC_UPDATE' => 'Y']);
                }
            }
        }


	} // End of function
		


}

