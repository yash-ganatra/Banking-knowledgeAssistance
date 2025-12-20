<?php

namespace App\Http\Controllers\DelightAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use App\Helpers\CommonFunctions;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Carbon\Carbon;
use DB;
use Mail;
use DateTime;
use Cookie;
use Crypt;

class CheckKitGeneratedByFincale extends Controller
{

    public function checkkitGenereated(){

        try {
                $delightKitNotGenerated = DB::table("DELIGHT_KIT")->where('CUSTOMER_ID',null)
                                                                ->where('STATUS',null)
                                                                ->where('ACCOUNT_NUMBER',null)
                                                                ->where('KIT_NUMBER','!=', null)
                                                                ->get()->toArray();

                $generatedKits = array();          
                

                $delightKitTable = 'CUSTOM.DCB_TMP_CUST_DETAIL_TBL_HIST';

                foreach($delightKitNotGenerated as $kit => $kitDetail){
                    $delightFinacleQueryInstance = DB::connection('oracle2')->table($delightKitTable);
                    $generatedKit = $delightFinacleQueryInstance->where('BATCH_NUM', $kitDetail->kit_number)
                                                                    ->where('CUST_ID', '!=' , null)
                                                                    ->where('FORACID', '!=' , null)
																	->where('UPL_USER_ID', 'CUBE')
																	// ->where('UPLOAD_SOL', '900')
																	// ->where('TEXT', $kitDetail->kit_number)
                                                                    ->get()->toArray();
                    $generatedKit = current($generatedKit);

                    
                    if ($generatedKit) {
                        $delightRequestStatus = DB::table("DELIGHT_REQUEST")
                                                                ->where('ID', $kitDetail->dr_id)
                                                                ->update(['DR_STATUS'=>3]);
                    }
                    
                    array_push($generatedKits, $generatedKit);

                }
                DB::disconnect('oracle2');


                if (count($generatedKits) > 0) {
                $generatedKits = array_filter($generatedKits);
                    foreach($generatedKits as $val){
                        if($val->cust_id != '' && $val->foracid != '' ){
                        $kitDetails = array();
                        $kitDetails['CUSTOMER_ID'] = $val->cust_id;
                        $kitDetails['ACCOUNT_NUMBER'] = $val->foracid;
                        $kitDetails['STATUS'] = 1;
                        $generatedKitsAdded = DB::table("DELIGHT_KIT")
                                                                ->where('KIT_NUMBER', $val->batch_num)
                                                                ->update($kitDetails);
                    }
                        
                    }

                }

                if (isset($generatedKitsAdded)) {
                  return json_encode(['status'=>'success','msg'=>'Kits generated','data'=>[]]);  
                }else{
                     return json_encode(['status'=>'fail','msg'=>'Kits not generated','data'=>[]]);  
                }
        } catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('DelightAdmin/CheckKitGeneratedByFincale','checkkitGenereated',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);  
        }

    }
}
