<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Http\Controllers\Admin\ExceptionController;
use Carbon\Carbon;
use App\Helpers\ApiCommonFunctions;
use Crypt,Cache,Session;

class OneTimeTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OneTimeTask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
       // $cont = new ExceptionController(); 		
       // $cont->getExLog();  
		// Self::generateRSAkeyPair();
        //Self::maskimagedata();
        return 0;
    }

    public function generateRSAkeyPair(){

        // Generate an RSA key pair
        // CG - 23 May 23 - modeled for 4096, OAEP defa padding, and sha digest
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $res = openssl_pkey_new($config);        
        openssl_pkey_export($res, $privKey);        
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        // This point forward - please be careful -
        // Do not execute or edit if you do not understand this code.
        // CG - 29-May-23
        //file_put_contents("priva.te", $privKey);
        //file_put_contents("publ.ic", $pubKey);
        echo 'Public private .key files generated!';
        exit;        
    }


    //Temprory lastloginupdateuser

    public function lastLoginUpdateUser(){
        try{

            $getuserList = DB::table('USERS')->select('ID','EMPLDAPUSERID','LAST_LOGIN')
                                            ->where('EMPSTATUS','Y')
                                            ->get()->toArray();

            for($i=0;count($getuserList)>$i;$i++){

                if($getuserList[$i]->last_login == ''){

                    $getUserDatils = DB::table('USER_ACTIVITY_LOG')->where(['USER_ID'=>$getuserList[$i]->id,'ACTION'=>'login'])
                                                                    ->orderBy('CREATED_AT','DESC')
                                                                    ->limit(1)
                                                                    ->get()->toArray();
                    $getUserDatils =  (array) current($getUserDatils);
                    $lastLoginDate = isset($getUserDatils['created_at']) && $getUserDatils['created_at'] != ''?$getUserDatils['created_at']:'';

                    $updatelastloginDate = DB::table('USERS')->whereId($getuserList[$i]->id)
                                                             ->where('EMPLDAPUSERID',$getuserList[$i]->empldapuserid)
                                                             ->update(['LAST_LOGIN'=>$lastLoginDate]);
                }
            }


        }catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

   //========================For Testing purpose or bugs checking =============================
    
    // public function handle()
    // {
    //     $commonFunctions = new CommonFunctions(); 
    //     $forms = DB::table('ACCOUNT_DETAILS')->get()->toArray();
    //     foreach ($forms as $key => $form) {
    //         print_r($form->id);
 
    //             $response = $commonFunctions->getFormDetails($form->id);  
    //         print_r($response);
    //     }


    //     // $response = $commonFunctions->getFormDetails(7522);  
    //     // print_r($response);
    // }

    public function maskimagedata(){

        if(Cache::get('image_mask_count') == ''){
        
            $getCount = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE')->where('FIELD_NAME','IMAGE_MASK_ALLOW')->get()->toArray();
            $getCount = (array) current($getCount);

            Cache::put('image_mask_count',$getCount['field_value'],now()->addHours(12));
            $iterationCount = Cache::get('image_mask_count');
            $getOvdData = DB::table('CUSTOMER_OVD_DETAILS')->select()->where(function($query){
                                                                $query->where(DB::raw('substr(ID_PROOF_CARD_NUMBER,0,10)'),'!=','XXXX-XXXX-')
                                                                        ->orWhere(DB::raw('substr(ADD_PROOF_CARD_NUMBER,0,10)'),'!=','XXXX-XXXX-'); 
                                                                }
                                                            )->where(function ($query){
                                                                $query->where('PROOF_OF_IDENTITY','1')
                                                                    ->orWhere('PROOF_OF_ADDRESS','1');
                                                                }
                                                            )->take($iterationCount)->get()->toArray();
            for($i=0;count($getOvdData)>$i;$i++){
    
                $getApiqueuecheck = DB::table('API_QUEUE')->where('FORM_ID',$getOvdData[$i]->form_id)
                                                          ->where('APPLICANT_NO',$getOvdData[$i]->applicant_sequence)
                                                          ->where('API','ovdmaskCallWrapper')
                                                          ->count();
                                                          exit;
                if($getApiqueuecheck == 0){
    
                    if($getOvdData[$i]->proof_of_identity == '1'){
                        $getIdProof = explode(',',$getOvdData[$i]->id_proof_image);  
                        for($j=0;count($getIdProof)>$j;$j++){
                            $squence = $getOvdData[$i]->applicant_sequence.'1'.$j;
                            ApiCommonFunctions::insertIntoApiQueue($getOvdData[$i]->form_id,'ApiCommonFunctions','ovdmaskCallWrapper','MaskOVD',$squence,$getOvdData[$i]->applicant_sequence,Array($getOvdData[$i]->form_id,$getIdProof[$j],$getOvdData[$i]->applicant_sequence,'ID'),Carbon::now()->addMinutes(2));
                        }
                    }
        
                    if($getOvdData[$i]->proof_of_address == '1'){
                        $getAddProof = explode(',',$getOvdData[$i]->add_proof_image);
                        for($k=0;count($getAddProof)>$k;$k++){
                            $squence = $getOvdData[$i]->applicant_sequence.'2'.$k;
                            ApiCommonFunctions::insertIntoApiQueue($getOvdData[$i]->form_id,'ApiCommonFunctions','ovdmaskCallWrapper','MaskOVD',$squence,$getOvdData[$i]->applicant_sequence,Array($getOvdData[$i]->form_id,$getAddProof[$k],$getOvdData[$i]->applicant_sequence,'ADD'),Carbon::now()->addMinutes(2));
                        }
                    }
        
                    ApiCommonFunctions::insertIntoApiQueue($getOvdData[$i]->form_id,'ApiCommonFunctions','ovdNumbermaskCallWrapper','MaskOVDNo',null,$getOvdData[$i]->applicant_sequence,Array($getOvdData[$i]->form_id,$getOvdData[$i]->applicant_sequence),Carbon::now()->addMinutes(2));
                }
            }
            Cache::put('image_mask_count','');
        }
    }

}
