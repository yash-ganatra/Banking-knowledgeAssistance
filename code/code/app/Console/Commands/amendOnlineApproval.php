<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Http\Controllers\NotificationController;
use App\Helpers\EncryptDecrypt;
use DB;


class amendOnlineApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:amendOnlineApproval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command AmendOnline';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        $id = '';
        $responseData = Self::returnApproveStatus($id);
        if(isset($responseData['status']) && $responseData['status'] != 'Success'){
            return json_encode(['status'=>'fail','msg'=>'Server error please try again later.',data=>[]]);
        }

        if(isset($responseData['data']) && count($responseData['data'])>0){

            $chkOnlineAppData = $responseData['data'];

            for($seqApp=0;count($chkOnlineAppData)>$seqApp;$seqApp++){

                if($chkOnlineAppData[$seqApp]['status'] == 'P'){
                    
                    $decstring =  substr($chkOnlineAppData[$seqApp]['url'],66);
                    $decstringArray  = EncryptDecrypt::AES256Decryption($decstring,'amend-4');
                    $decstringArray = explode('|',$decstringArray);
                    $crfNumber =  $decstringArray[0];
                    $createdDate = '';
                    if(isset($decstringArray[2]) && $decstringArray[2] != ''){
                        $createdDate = $decstringArray[2];
                    }

                    $updatedAmendOnlineCrf = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)
                                                                        ->update(['CRF_STATUS'=>'24',
                                                                                'CRF_NEXT_ROLE'=>'20',
                                                                                'UPLOAD_CRF_FLAG'=>'ONLINE',
                                                                                'APPROVAL'=>'online',
                                                                                'UPDATED_AT'=>$createdDate]);

                    $getStatus = Self::returnApproveStatus($chkOnlineAppData[$seqApp]['id']);
                          
                    if($getStatus == 'Success'){
                        // NotificationController::processNotification($crfNumber,'CRF_APPROVED','CRF_SEND_EMAIL','amend','');
                        // NotificationController::processNotification($crfNumber,'CRF_APPROVED','CRF_SEND_SMS','amend','');  
                        CommonFunctions::saveAmendStatusLog($crfNumber,'Amend-L1',19,'Auto Approval');
                        CommonFunctions::saveAmendStatusLog($crfNumber,'Amend-L2',20,'');  
                    }
                }
            }
        }
        

    }
    public function returnApproveStatus($id=''){
        
        $getUrl = config('constants.APPLICATION_SETTINGS.AMEND_EVERYFICATION_URL');
        $getToken = config('constants.APPLICATION_SETTINGS.AMEND_EVERYFICATION_TOKEN');

        $url = "";
        if($id == ''){
            $url = $getUrl."getAmendData/".$getToken;
        }else{
            $url = $getUrl."updateApproveData/".$getToken.'/'.$id;
        }
      
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        // curl_setopt($curl,CURLOPT_PROXY,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curl,CURLOPT_TIMEOUT,10);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $response = curl_exec($curl);
        $responseData = json_decode($response,true);

        if(isset($response) && $response == ''){
            return ['status'=>'fail','msg'=>'Please try later.','data'=>[]];
        }
        
        if(isset($responseData['status']) && $responseData['status'] != 'Success'){
            return ['status'=>'fail','msg'=>'Please try later.','data'=>[]];
        }
        return ['status'=>$responseData['status'],'msg'=>'Succesfully data process.','data'=>$responseData['data']];
        curl_close($curl);
    }
}
