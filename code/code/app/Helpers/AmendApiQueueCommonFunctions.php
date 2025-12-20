<?php
namespace App\Helpers;

use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Helpers\CommonFunctions;
use App\Http\Controllers\NotificationController;
use Session;
use Route;
use App\Helpers\Api;
use App\Helpers\Rules;
use DB;


class AmendApiQueueCommonFunctions {

	public static function insertIntoAmendApiQueue($crf_number,$class,$function,$module,$sequence,$parameter,$next_run){
		try{
		$apiQueueData = DB::table('AMEND_API_QUEUE')->where('API',$function)->where('CRF_NUMBER',$crf_number);
		
		if($apiQueueData->count() == 0){
			$insertApiQueue = [];
			$insertApiQueue['crf_number'] = $crf_number;
			$insertApiQueue['class'] = $class;
			$insertApiQueue['api'] = $function;
			$insertApiQueue['module'] = $module;
			$insertApiQueue['sequence'] = $sequence;
			$insertApiQueue['parameter'] = json_encode($parameter);
			$insertApiQueue['created_at'] = Carbon::now();
			$insertApiQueue['created_by'] = Session::get('userId');
			$insertApiQueue['next_run'] = $next_run;
			$insertApiQueue['retry'] = 0;
			$apiQueue = DB::table('AMEND_API_QUEUE')->insert($insertApiQueue);
            DB::commit();
		}
		}catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
	}		
}

?>