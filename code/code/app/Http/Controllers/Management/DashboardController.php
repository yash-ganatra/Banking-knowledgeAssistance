<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use DB;
use Crypt;
use Cookie;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        if(Cookie::get('token') != ''){
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            //get claims from token
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if($this->roleId != 13){

                $isAutherized = false;
            }else{

               $isAutherized = true;
            }
            if(!$isAutherized)
            {
                echo "<div class='container RefreshRestrictMsg' style='
                    width: 63%;
                    margin: 0 auto;
                    height: 4em;
                    padding: 2em;
                    text-align: center;
                    border-radius: 10px red;
                    border-radius: 6px;
                    margin-top: 12em;
                    background-color: #fff0d3;
                    font-family:Arial;
                    line-height: 35px;'>

                    <p style='margin-top: 0%;
                    font-size: 1.375rem;
                    font-weight: 500;'>Unauthorized attempt detected.<br>Event logged for Audit and Admin team.</p>
                 
                  </div>";
                $saveuserlog = CommonFunctions::createUserLogDirect('Management/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }

        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

 //    public function getallgorstatusapi(Request $request)
	// {
	// 	try {
 //            if ($request->ajax()){
 //                $requestData = $request->get('data');
 //                //echo "<pre>";print_r($requestData);exit;
 //                if (isset($requestData['startDate']) && $requestData['startDate'] != '') {
 //                	$startDate = $requestData['startDate'];
 //                	$endDate = $requestData['endDate'];

 //                	$dateFilter = Self::dashboard($startDate, $endDate);
 //                }


 //            }

 //            if ($dateFilter) {
 //            	return json_encode(['status'=>'success','msg'=>'Date Filter Successfully','data'=>[]]);
 //            }else{
 //                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
 //            }
 //        }
 //        catch(\Illuminate\Database\QueryException $e) {
 //            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
 //            $eMessage = $e->getMessage();
 //            CommonFunctions::addExceptionLog($eMessage, $request);
 //            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
 //        }
	// }


    public function dashboard()
    {
            
            //echo "<pre>";print_r($startDate);exit;
    		$startDate = Carbon::now()->subDays(7)->format('d-m-Y');
			$endDate = Carbon::now()->format('d-m-Y');
			//$endDate = Carbon::now()->addDays(1)->format('d-m-Y');
			if ($endDate != '') {
				$endDate = Carbon::parse($endDate)->addDays(1)->format('d-m-Y');
			}
			//echo("<pre>");print_r($endDate);exit;
			$branchIds = '';

	        $rtData = Self::getallgorstatus($startDate, $endDate, $branchIds);
			$rtApiData = Self::getallgorstatus_api($startDate, $endDate, $branchIds);
            //echo "<pre>";print_r($rtApiData);exit;
			
			$rtL1Data = Self::getDescData('L1',$startDate, $endDate, $branchIds);
			$rtL2Data = Self::getDescData('L2',$startDate, $endDate, $branchIds);
			
			$rtQCData = Self::getDescData('QC',$startDate, $endDate, $branchIds);
			$rtAUData = Self::getDescData('AU',$startDate, $endDate, $branchIds);
			
			$branchlists = CommonFunctions::getBranch();
	        $regionlists = CommonFunctions::getRegional();
	        $zonelists = CommonFunctions::getZone();
	        $clusterlists = CommonFunctions::getCluster();

	        $aofcountdetails = Self::getTatview();
						
			$rtFtrData = Self::getFtrData($startDate, $endDate, $branchIds);
			
			$rtAvgTat = Self::getallavgtat($startDate, $endDate, $branchIds);
			
			$rtErrDetection = Self::getErrDetection($startDate, $endDate, $branchIds);

			$groups = config('constants.BRANCH_REVIEWER_TYPE');

			//for updating branch id
			// $branchIdss = DB::table('ACCOUNT_DETAILS')
   //              				->pluck('branch_id','id')->toArray();
	  //         foreach ($branchIdss as $key => $value) {
	  //         	$branchIdsUpdated = DB::table('REVIEW_TABLE')
	  //           					->where('NPC_REVIEW_LOG.FORM_ID',$key)
	  //           					->update(['BRANCH_ID'=> $value]);
	  //         }
			// echo "<pre>";print_r($branchIdsUpdated);exit;
			// for ($i=1; $i <25 ; $i++) { 
			// 	$entry = array();
			// 	$entry['created_at'] = Carbon::createFromFormat('Y-m-d H', '2021-06-'.$i.' 22')->toDateTimeString();
			// 	$entry['form_id'] = 8481;
			// 	$entry['branch_id'] = 182;
			// 	DB::table("REVIEW_TABLE")->insertGetId($entry);
			// }
			// for ($i=1; $i <25 ; $i++) { 
			// 	$entry = array();
			// 	$entry['created_at'] = Carbon::createFromFormat('Y-m-d H', '2021-06-'.$i.' 22')->toDateTimeString();
			// 	$entry['form_id'] = 8481;
			// 	$entry['branch_id'] = 182;
			// 	$entry['status'] = 'approved';
			// 	$entry['desc_count'] = 0;

			// 	DB::table("NPC_REVIEW_LOG")->insertGetId($entry);
			// }
			//$allFieldsMetrics = [ 'BRANCH_SUBMISSION', 'L1', 'L2', 'ACCOUNT_OPENED', 'QC', 'AUDITING'];
			// for ($i=1; $i <25 ; $i++) { 
			// 	$entry = array();
			// 	$entry['created_at'] = Carbon::createFromFormat('Y-m-d H', '2021-06-'.$i.' 22')->toDateTimeString();
			// 	$entry['form_id'] = 8481;
			// 	$entry['branch_id'] = 182;
			// 	if ($i>=0 && $i<5) {
			// 		$entry[$allFieldsMetrics[0]] = $i;
			// 	}else if($i>6 && $i<15){
			// 		$entry[$allFieldsMetrics[1]] = $i;
			// 	}else if($i>15 && $i<20){
			// 		$entry[$allFieldsMetrics[2]] = $i;
			// 	}else if($i>20 && $i<25){
			// 		$entry[$allFieldsMetrics[3]] = $i;
			// 	}else{
			// 		$entry[$allFieldsMetrics[4]] = $i;
			// 	}
			// 	DB::table("ACCOUNT_STATUS_UPDATE_METRICS")->insertGetId($entry);
			// }
			// $allFields = [ 'NSDL_API', 'FINACLE_PAN_DETAILS', 'FINACLE_CUSTOMER_DETAILS', 'E_KYC_DETAILS', 'DEDUPE_QID', 'DEDUPE_STATUS', 'CUSTOMER_ID', 'ACCOUNT_ID', 'FTR'];
			// foreach ($allFields as $field ) {
			// 	for ($i=1; $i <25 ; $i++) { 
			// 	$entry = array();
			// 	$entry['created_at'] = Carbon::createFromFormat('Y-m-d H', '2021-06-'.$i.' 22')->toDateTimeString();
			// 	$entry['form_id'] = 8481;
			// 	$entry['branch_id'] = 182;
			// 	$entry['service_name'] = $field;
			// 	//$entry[$field] = $i;
			// 	DB::table("ENCRYPTED_API_SERVICE_LOG")->insertGetId($entry);
			// }
			//}
	        return view('manco.management')
				->with('rtData', $rtData)
				->with('rtApiData', $rtApiData)
				->with('rtL1Data', $rtL1Data)
				->with('rtL2Data', $rtL2Data)
				->with('rtQCData', $rtQCData)
				->with('rtAUData', $rtAUData)
				->with('rtFtrData', $rtFtrData)	 		
				->with('rtAvgTat', $rtAvgTat)
				->with('rtErrDetection', $rtErrDetection)
				->with('aofcountdetails',$aofcountdetails)
			    ->with('branchlists',$branchlists)
			    ->with('regionlists',$regionlists)
			    ->with('zonelists',$zonelists)
			    ->with('clusterlists',$clusterlists)
			    ->with('groups',$groups)
				;	
    }

    
 //    public function getchartdata(Request $request)
	// {
	// 	try {
 //            if ($request->ajax()){
 //                $requestData = $request->get('data');
 //                //echo "<pre>";print_r($requestData);exit;
 //                if (isset($requestData) && $requestData != '') {
 //                	//$filteredData = [];
 //                	if (!isset($requestData['startDate']) || $requestData['startDate'] == '') {
 //                		$startDate = Carbon::now()->subDays(7)->format('d-m-Y');
	// 					$endDate = Carbon::now()->format('d-m-Y');
 //                	}else{
	//                 	$startDate = $requestData['startDate'];
	//                 	$endDate = $requestData['endDate'];
 //                	}

 //                	$filteredArray = array();

 //                	$filteredRtData = Self::getallgorstatus($startDate, $endDate);
 //                	if ($filteredRtData) {
 //                		$filteredArray['filteredRtData'] = $filteredRtData;
 //                	}

 //                	$filteredRtApiData = Self::getallgorstatus_api($startDate, $endDate);
 //                	if ($filteredRtApiData) {
 //                		$filteredArray['filteredRtApiData'] = $filteredRtApiData;
 //                	}

 //                	$filteredRtAvgTat = Self::getallavgtat($startDate, $endDate);
 //                	if ($filteredRtAvgTat) {
 //                		$filteredArray['filteredRtAvgTat'] = $filteredRtAvgTat;
 //                	}

 //                	$filteredRtErrDetection = Self::getErrDetection($startDate, $endDate);
 //                	if ($filteredRtErrDetection) {
 //                		$filteredArray['filteredRtErrDetection'] = $filteredRtErrDetection;
 //                	}

 //                	$filteredRtFtrData = Self::getFtrData($startDate, $endDate);
 //                	if ($filteredRtFtrData) {
 //                		$filteredArray['filteredRtFtrData'] = $filteredRtFtrData;
 //                	}

 //                	$filteredRtL1Data = Self::getDescData('L1', $startDate, $endDate);
 //                	if ($filteredRtL1Data) {
 //                		$filteredArray['filteredRtL1Data'] = $filteredRtL1Data;
 //                	}

 //                	$filteredRtL2Data = Self::getDescData('L2', $startDate, $endDate);
	// 				if ($filteredRtL1Data) {
 //                		$filteredArray['filteredRtL1Data'] = $filteredRtL1Data;
 //                	}

	// 				$filteredRtQCData = Self::getDescData('QC', $startDate, $endDate);
	// 				if ($filteredRtQCData) {
 //                		$filteredArray['filteredRtQCData'] = $filteredRtQCData;
 //                	}

	// 				$filteredRtAUData = Self::getDescData('AU', $startDate, $endDate);
	// 				if ($filteredRtAUData) {
 //                		$filteredArray['filteredRtAUData'] = $filteredRtAUData;
 //                	}

 //                	//echo "<pre>";print_r(count($filteredArray));exit;
 //                	//$rtData = Self::getallgorstatus();
 //                }


 //            }

 //            if (count($filteredArray) == 8) {
 //            	return json_encode(['status'=>'success','msg'=>'Date Filter Successfully','data'=>['filteredArray' =>$filteredArray]]);
 //            }else{
 //                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
 //            }
 //        }
 //        catch(\Illuminate\Database\QueryException $e) {
 //            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
 //            $eMessage = $e->getMessage();
 //            CommonFunctions::addExceptionLog($eMessage, $request);
 //            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
 //        }
	// }

	public function getfilterchartdata(Request $request)
	{
		try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //echo "<pre>";print_r($requestData);exit;
                if (isset($requestData) && $requestData != '') {
                	//echo "<pre>";print_r($branchIds);exit;
                	if (!isset($requestData['startDate']) || $requestData['startDate'] == '') {
                		$startDate = Carbon::now()->subDays(7)->format('d-m-Y');
						$endDate = Carbon::now()->format('d-m-Y');
                	}else{
	                	$startDate = $requestData['startDate'];
	                	$endDate = $requestData['endDate'];
                	}

                	if (isset($requestData['selectedSubGroup']) && $requestData['selectedSubGroup'] != '') {
	                	$selectedGroup = $requestData['selectedGroup'];
	                	$selectedSubGroup = $requestData['selectedSubGroup'];
	                	switch ($selectedGroup) {
	                		case '1':
	                			$masterTable = 'REGION_ID';
	                			break;
	                		case '2':
	                			$masterTable = 'ZONE_ID';
	                			break;
	                		case '3':
	                			$masterTable = 'CLUSTER_ID';
	                			break;
	                		default:
	                			//$masterTable = 'CLUSTER_ID';
	                			break;
	                	}
	                	// echo "<pre>";print_r($masterTable);
	                	// echo "<pre>";print_r($selectedSubGroup);exit;
	                	$branchIds = DB::table('BRANCH')
	                				->where($masterTable, $selectedSubGroup)
	                				->pluck('branch_id')->toArray();
	                	//echo "<pre>";print_r($branchIds);exit;

	                	if (count($branchIds) <= 0) {//if branch ID mapping not found
	                		$branchIds = [''];
	                		//return json_encode(['status'=>'fail','msg'=>'Data not found (BRANCH_ID Mapping)','data'=>[]]);
	                	}
                	}else{
                		$branchIds = '';
                	}

                	if ($endDate != '') {
						$endDate = Carbon::parse($endDate)->addDays(1)->format('d-m-Y');
					}
                	
                	$filteredArray = array();

                	$filteredRtData = Self::getallgorstatus($startDate, $endDate, $branchIds);
                	if ($filteredRtData) {
                		$filteredArray['filteredRtData'] = $filteredRtData;
                	}else{
                		$filteredArray['filteredRtData'] = '';
                	}

                	$filteredRtApiData = Self::getallgorstatus_api($startDate, $endDate, $branchIds);
                	if ($filteredRtApiData) {
                		$filteredArray['filteredRtApiData'] = $filteredRtApiData;
                	}else{
                		$filteredArray['filteredRtApiData'] = '';
                	}

                	$filteredRtAvgTat = Self::getallavgtat($startDate, $endDate, $branchIds);
                	if ($filteredRtAvgTat) {
                		$filteredArray['filteredRtAvgTat'] = $filteredRtAvgTat;
                	}else{
                		$filteredArray['filteredRtAvgTat'] = '';
                	}

                	$filteredRtErrDetection = Self::getErrDetection($startDate, $endDate, $branchIds);
                	if ($filteredRtErrDetection) {
                		$filteredArray['filteredRtErrDetection'] = $filteredRtErrDetection;
                	}else{
                		$filteredArray['filteredRtErrDetection'] = '';
                	}

                	$filteredRtFtrData = Self::getFtrData($startDate, $endDate, $branchIds);
                	if ($filteredRtFtrData) {
                		$filteredArray['filteredRtFtrData'] = $filteredRtFtrData;
                	}else{
                		$filteredArray['filteredRtFtrData'] = '';
                	}

                	$filteredRtL1Data = Self::getDescData('L1', $startDate, $endDate, $branchIds);
                	if ($filteredRtL1Data) {
                		$filteredArray['filteredRtL1Data'] = $filteredRtL1Data;
                	}else{
                		$filteredArray['filteredRtL1Data'] = '';
                	}
                	
                	$filteredRtL2Data = Self::getDescData('L2', $startDate, $endDate, $branchIds);
                	//echo "<pre>";print_r($filteredRtL2Data);exit;	
					if ($filteredRtL1Data) {
                		$filteredArray['filteredRtL2Data'] = $filteredRtL2Data;
                	}else{
                		$filteredArray['filteredRtL2Data'] = '';
                	}
                	
					$filteredRtQCData = Self::getDescData('QC', $startDate, $endDate, $branchIds);
					if ($filteredRtQCData) {
                		$filteredArray['filteredRtQCData'] = $filteredRtQCData;
                	}else{
                		$filteredArray['filteredRtQCData'] = '';
                	}
                	
					$filteredRtAUData = Self::getDescData('AU', $startDate, $endDate, $branchIds);
					if ($filteredRtAUData) {
                		$filteredArray['filteredRtAUData'] = $filteredRtAUData;
                	}else{
                		$filteredArray['filteredRtAUData'] = '';
                	}
                }
            }
            //echo "<pre>";print_r(count($filteredArray));exit;	
            if (count($filteredArray) == 9) {
            	return json_encode(['status'=>'success','msg'=>'Date filter applied','data'=>['filteredArray' =>$filteredArray]]);
            }else{
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
	}

    public function processflow()
    {
        //returns tempalte
        return view('manco.processflow');
    }

    public function dashboardv2()
    {
        //returns tempalte
        return view('manco.management2');
    }
	
	public function getErrDetection_old($startDate = '', $endDate = '', $branchIds = ''){

		
		$allFields = [ '3', '4', '5', '6'];
		
		$allData = array();
		$totalRecords =  DB::table('REVIEW_TABLE')
							->whereRaw("REVIEW_TABLE.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("REVIEW_TABLE.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							->count();	
		if ($totalRecords == 0) {
			$totalRecords = 1;
		}		 
		foreach($allFields as $field){
			if ($branchIds != '') {
				$response =  DB::table('REVIEW_TABLE')
							->where('ROLE_ID', $field)
							->whereRaw("REVIEW_TABLE.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("REVIEW_TABLE.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							//->whereRaw("to_char(REVIEW_TABLE.CREATED_AT,'DD-MM-YYYY') <= '".$endDate."'") 
							->whereIn('BRANCH_ID', $branchIds) 
							->count();	
			}else{
				$response =  DB::table('REVIEW_TABLE')
							->where('ROLE_ID', $field)
							->whereRaw("REVIEW_TABLE.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("REVIEW_TABLE.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							->count();	
			}
			// echo "<pre>";print_r($response);		
			// echo "<pre>";print_r($response/$totalRecords);exit;		
			array_push($allData, round(($response/$totalRecords)*100));
		}
		
		//echo '<pre>'; print_r($allData); exit;
		return $allData;
		
	}
	
	public function getallavgtat($startDate = '', $endDate = '', $branchIds = ''){
		$allFields = [ 'BRANCH_SUBMISSION', 'L1', 'L2', 'ACCOUNT_OPENED', 'QC', 'AUDITING'];
		$allData = array();
		foreach($allFields as $field){
			array_push($allData, Self::getavgtat($field, $startDate, $endDate, $branchIds));
		}
		return $allData;
	}	
	
	public function getavgtat($field, $startDate = '', $endDate = '', $branchIds = ''){	

		if ($branchIds != '') {
			$response =  DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
							->whereNotNull($field) 
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							->whereIn('BRANCH_ID', $branchIds) 
							->avg($field); 
		}else{
			$response =  DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
							->whereNotNull($field) 
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							->avg($field);
		}
		
		//echo '<pre>'; print_r($response); exit;

		return round($response);
	}
	
	//-------------------------------------------------//
	
	public function getallgorstatus($startDate = '', $endDate = '', $branchIds = ''){
		//$allFields = ['AOF', ['COE','DeDupe'], 'L1', ['Chq.',' Funding'], 'L2', 'QC', 'Audit'],];
		$allFields = [ 'BRANCH_SUBMISSION', 'L1', 'L2', 'QC', 'AUDITING'];
		$allData = array();
		foreach($allFields as $field){
				$fieldAllgorStatus = Self::getgorstatus($field, $startDate, $endDate, $branchIds);
				//echo "<pre>";print_r($fieldAllgorStatus);exit;
				if (!count($fieldAllgorStatus) > 0) {
					return false;
				}
				array_push($allData, $fieldAllgorStatus);
		}		
        //echo "<pre>";print_r($allData);exit;
		
		$green = array(); $orange = array(); $red = array();

		foreach($allData as $key => $value){
			$currBlock = $value;
			array_push($green, $currBlock[0]->green);
			array_push($orange, $currBlock[0]->orange);
			array_push($red, $currBlock[0]->red);
			//echo '<pre>'; print_r($currBlock[0]->green); 
		}	
		return array($green, $orange, $red);
	}
	
	public function getgorstatus($field, $startDate = '', $endDate = '', $branchIds = ''){			

		//combo
        // echo "<pre>";print_r($branchIds);
        // echo "<pre>";print_r($startDate);
        // echo "<pre>";print_r($endDate);exit;
			
		//$startDate = '15-08-2021';	
		// $endDate = '22-08-2021';	

		if($branchIds != '') {
			$response =  DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
							->select(DB::raw("'".$field."'"),
								DB::raw("COUNT(CASE WHEN ".$field." <= 30 THEN 1 END) AS GREEN"),
								DB::raw("COUNT(CASE WHEN ".$field." <= 120 THEN 1 END) AS ORANGE"),
								DB::raw("COUNT(CASE WHEN ".$field." > 120 THEN 1 END) AS RED"))
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							
							->whereIn('BRANCH_ID', $branchIds) 
							->groupBy(DB::raw("'".$field."'"))->get()->toArray();

		}else{
			$response =  DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
							->select(DB::raw("'".$field."'"),
								DB::raw("COUNT(CASE WHEN ".$field." <= 30 THEN 1 END) AS GREEN"),
								DB::raw("COUNT(CASE WHEN ".$field." <= 120 THEN 1 END) AS ORANGE"),
								DB::raw("COUNT(CASE WHEN ".$field." > 120 THEN 1 END) AS RED"))
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							//->whereRaw("ACCOUNT_STATUS_UPDATE_METRICS.CREATED_AT <= to_date('".$endDate."','DD-MM-YYYY')")
							//->whereRaw("to_char(EXCEPTION_DATE,'DD-MM-YYYY') = '".$requestData['date']."'")
							->groupBy(DB::raw("'".$field."'"))->get()->toArray();
		}
	  	//echo "<pre>";print_r($response);exit;
		return $response;
	}

	public function getTatview(){

		//returns tempalte
        $aofcountdetails = DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
                             ->select(
                                      DB::raw("to_char(SUBMISSION_DATE,'DD-MM-YYYY') as submission_date"),
                                      DB::raw('COUNT(BRANCH_SUBMISSION) as branch_submission'),
                                      DB::raw('COUNT(L1) as l1'),
                                      DB::raw('COUNT(L2) as l2'),
                                      DB::raw('COUNT(ACCOUNT_OPENED) as account_opened'),
                                      DB::raw('COUNT(DISPATCH) as dispatch'),
                                      DB::raw('COUNT(COURIER) as courier'),
                                      DB::raw('COUNT(INWARD) as inward'),
                                      DB::raw('COUNT(QC) as qc'),
                                      DB::raw('COUNT(AUDITING) as auditing'))
                             ->where( 'SUBMISSION_DATE', '>', Carbon::now()->subDays(6))
                             ->groupBy(DB::raw("to_char(SUBMISSION_DATE,'DD-MM-YYYY')"))
                             ->orderBy('SUBMISSION_DATE','DESC')
                             ->get()->toArray();
         return $aofcountdetails;

	}

	public function getallgorstatus_api($startDate = '', $endDate = '', $branchIds = ''){		
		$allFields = [ 'NSDL_API', 'FINACLE_PAN_DETAILS', 'FINACLE_CUSTOMER_DETAILS', 'E_KYC_DETAILS', 'DEDUPE_QID', 'CUSTOMER_ID', 'ACCOUNT_ID', 'FTR'];
		$allData = array();
		foreach($allFields as $field){	
				$filedsGetAllgorStatus_api = current(Self::getgorstatus_api($field, $startDate, $endDate, $branchIds));
				if (!count($filedsGetAllgorStatus_api) > 0) {
					return false;
				}
				array_push($allData, $filedsGetAllgorStatus_api);			
		}		
		//	echo '<pre>'; print_r($allData); exit;

		$green = array(); $orange = array(); $red = array();
		foreach($allData as $key => $value){
			$currBlock = $value;
			
			array_push($green, $currBlock['green']);
			array_push($orange, $currBlock['orange']);
			array_push($red, $currBlock['red']);
		
		}	
		return array($green, $orange, $red);
	}

	public function getgorstatus_api($field, $startDate = '', $endDate = '', $branchIds = ''){				
		//$field = 'NSDL_API';
			if ($startDate == '') {
				$startDate = Carbon::now()->subDays(7)->format('d-m-Y');
				$endDate = Carbon::now()->format('d-m-Y');
			}
			// echo "<pre>";print_r($startDate);
			// echo "<pre>";print_r($endDate);exit;
			//$startDate = '04-07-2021';
			//$endDate = '10-07-2021';
		if ($branchIds != '') {
			$response =  DB::table('ENCRYPTED_API_SERVICE_LOG')
							->select(DB::raw("'".$field."'"), 							
								DB::raw("COUNT(CASE WHEN API_RESPONSE_TIME <= 2 THEN 1 END) AS GREEN"),
								DB::raw("COUNT(CASE WHEN API_RESPONSE_TIME <= 5 THEN 1 END) AS ORANGE"),
								DB::raw("COUNT(CASE WHEN API_RESPONSE_TIME > 5 THEN 1 END) AS RED"))
							->where('SERVICE_NAME', $field) 
							->whereIn('BRANCH_ID', $branchIds) 
							->whereRaw("ENCRYPTED_API_SERVICE_LOG.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("ENCRYPTED_API_SERVICE_LOG.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							//->where('CREATED_AT','>=','2021-07-15 11:58:55')
							->groupBy(DB::raw("'".$field."'"))
							->get()->toArray();	  
		}else{
			$response =  DB::table('ENCRYPTED_API_SERVICE_LOG')
							->select(DB::raw("'".$field."'"), 							
								DB::raw("COUNT(CASE WHEN API_RESPONSE_TIME <= 2 THEN 1 END) AS GREEN"),
								DB::raw("COUNT(CASE WHEN API_RESPONSE_TIME <= 5 THEN 1 END) AS ORANGE"),
								DB::raw("COUNT(CASE WHEN API_RESPONSE_TIME > 5 THEN 1 END) AS RED"))
							->where('SERVICE_NAME', $field) 
							->whereRaw("ENCRYPTED_API_SERVICE_LOG.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("ENCRYPTED_API_SERVICE_LOG.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							//->where('CREATED_AT','>=','2021-07-15 11:58:55')
							->groupBy(DB::raw("'".$field."'"))
							->get()->toArray();	  
		}
				

		if(count($response)>0){
			return json_decode(json_encode($response), true); 
		}else{
			return array(array(strtolower("'".$field."'") => $field, 'green' => 0, 'orange' => 0, 'red' => 0));
		}		 
	}

	public function getDescData($role, $startDate = '', $endDate = '', $branchIds = ''){			

		if ($startDate == '') {
				$startDate = Carbon::now()->subDays(7)->format('d-m-Y');
				//echo "<pre>";print_r($startDate);exit;
				$endDate = Carbon::now()->format('d-m-Y');
		}

		switch($role){
			case 'L1':
				$roleId = 3;
				break;
			case 'L2':
				$roleId = 4;
				break;
			case 'QC':
				$roleId = 5;
				break;
			case 'AU':
				$roleId = 6;
				break;
			default:
				return array();
		}
		if ($branchIds != '') {
			$response =  DB::table('REVIEW_TABLE')
							->select(('COLUMN_NAME'), 							
								DB::raw("COUNT(*) AS COUNT"))
							->where('ROLE_ID', $roleId) 
							->whereIn('BRANCH_ID', $branchIds) 
							->whereRaw("REVIEW_TABLE.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("REVIEW_TABLE.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							->groupBy('COLUMN_NAME')
							->get()->toArray();	
		}else{
			$response =  DB::table('REVIEW_TABLE')
							->select(('COLUMN_NAME'), 							
								DB::raw("COUNT(*) AS COUNT"))
							->where('ROLE_ID', $roleId) 
							->whereRaw("REVIEW_TABLE.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')")
							->whereRaw("REVIEW_TABLE.CREATED_AT < to_date('".$endDate."','DD-MM-YYYY')")
							->groupBy('COLUMN_NAME')
							->get()->toArray();	
		}
																
	  		
		$retArray = array();	
		foreach($response as $key => $value){
			$retArray[$value->column_name] = $value->count;
		}	

		arsort($retArray);
		return $retArray;
	}
	
	
	public function getFtrData($startDate = '', $endDate = '', $branchIds = ''){				
		
		$ftrArray = array();
		//echo "<pre>";print_r($endDate);exit;
		//echo "<pre>";print_r(Carbon::parse($startDate)->format('d-M'));
		if ($endDate != '') {
			$endDate = Carbon::parse($endDate)->addDays(-1)->format('d-m-Y');
		}
 
 		

		for($t=0; $t<=6; $t++){	
			if ($endDate == '') {
				$dateToCheck = Carbon::now()->subDays($t)->format('d-m-Y');
			}else{
				$dateToCheck = Carbon::parse($endDate)->subDays($t)->format('d-m-Y');
			}
			$ftrArray[strtoupper($dateToCheck)] = array();
		}	
		//echo "<pre>";print_r($ftrArray);exit;
		foreach( $ftrArray as $edate => $value){	

			//echo "<pre>";print_r($edate);exit;
			//if($edate != '16-JUL') continue;
		
			if ($branchIds != '') {
				$clearedByRoles = DB::table('NPC_REVIEW_LOG')
							->select(DB::raw("to_char(created_at, 'DD-MON') as edate"), 'ROLE_ID', DB::raw('count(*) as formcount'))	
							->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') = '".$edate."'")
							->where('DESC_COUNT',0)
							->where('STATUS', 'approved')
							->whereIn('BRANCH_ID', $branchIds) 
							->groupBy(DB::raw("to_char(created_at, 'DD-MON')"),'ROLE_ID')							
							->get()->toArray();		

			    $countByRoles = DB::table('NPC_REVIEW_LOG')
								->select(DB::raw("to_char(created_at, 'DD-MON') as edate"), 'ROLE_ID', DB::raw('count(*) as totalcount'))							
								->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') = '".$edate."'")
								->whereIn('BRANCH_ID', $branchIds) 
								->groupBy(DB::raw("to_char(created_at, 'DD-MON')"),'ROLE_ID')							
								->get()->toArray();		

				
				$formsProcessed = DB::table('NPC_REVIEW_LOG')
								->select(DB::raw("to_char(created_at, 'DD-MON') as edate"), DB::raw('count(*) as formcount'))							
								->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') = '".$edate."'")
								->whereIn('BRANCH_ID', $branchIds)  
								->groupBy(DB::raw("to_char(created_at, 'DD-MON')"))							
								->get()->toArray();
			}else{

				$clearedByRoles = DB::table('NPC_REVIEW_LOG')
							->select(DB::raw("to_char(created_at, 'DD-MON') as edate"), 'ROLE_ID', DB::raw('count(*) as formcount'))		
							->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') = '".$edate."'")
							->where('DESC_COUNT',0)
							->where('STATUS', 'approved')
							->groupBy(DB::raw("to_char(created_at, 'DD-MON')"),'ROLE_ID')							
							->get()->toArray();		
				//echo "<pre>";print_r($clearedByRoles);exit;

			    $countByRoles = DB::table('NPC_REVIEW_LOG')
								->select(DB::raw("to_char(created_at, 'DD-MON') as edate"), 'ROLE_ID', DB::raw('count(*) as totalcount'))	
								->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') = '".$edate."'")
								->groupBy(DB::raw("to_char(created_at, 'DD-MON')"),'ROLE_ID')							
								->get()->toArray();		

				
				$formsProcessed = DB::table('NPC_REVIEW_LOG')
								->select(DB::raw("to_char(created_at, 'DD-MON') as edate"), DB::raw('count(*) as formcount'))			
								->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') = '".$edate."'")
								->groupBy(DB::raw("to_char(created_at, 'DD-MON')"))							
								->get()->toArray();
			}

													
			
			$ftrArray[$edate]['count'] = 0;			
			$ftrArray[$edate]['branch'] = 0; 
			$ftrArray[$edate]['l1'] = 0; 
			$ftrArray[$edate]['l2'] = 0; 
			$ftrArray[$edate]['qc'] = 0; 
			$ftrArray[$edate]['branch_total'] = 0; 
			$ftrArray[$edate]['branch_fc'] = 0; 
			$ftrArray[$edate]['l1_total'] = 0; 
			$ftrArray[$edate]['l1_fc'] = 0; 
			$ftrArray[$edate]['l2_total'] = 0; 
			$ftrArray[$edate]['l2_fc'] = 0; 
			$ftrArray[$edate]['l3_total'] = 0; 
			$ftrArray[$edate]['l3_fc'] = 0; 

			if(count($formsProcessed)>0	&& isset($formsProcessed[0]->formcount)){
				$ftrArray[$edate]['count'] = $formsProcessed[0]->formcount;
			}		

			//echo '<pre> CLEARED:'; print_r($clearedByRoles); echo '<pre> COUNT:'; print_r($countByRoles);  

			if(count($clearedByRoles)>0){				
				foreach($clearedByRoles as $key => $value){
					$perValue = 0;
					$tc = 0;
					
					$countIndex = '-1';
					foreach($countByRoles as $countKey => $countValue){
						if($countByRoles[$countKey]->role_id == $value->role_id){
							$countIndex = $countKey;
							break;
						}
					}
					
					switch($value->role_id){
						case 3:
							$fc = $value->formcount; 
							if($fc > 0 && isset($countByRoles[$countIndex]) && isset($countByRoles[$countIndex]->totalcount)){
								$tc = $countByRoles[$countIndex]->totalcount;
								$perValue = $fc / $tc * 100;
							}
							$ftrArray[$edate]['branch'] = round($perValue);
							$ftrArray[$edate]['branch_total'] = $tc;
							$ftrArray[$edate]['branch_fc'] = $fc;
							break;
						case 4:
							$fc = $value->formcount; 
							if($fc > 0 && isset($countByRoles[$countIndex]) && isset($countByRoles[$countIndex]->totalcount)){
								$tc = $countByRoles[$countIndex]->totalcount;
								$perValue = $fc / $tc * 100;
							}
							$ftrArray[$edate]['l1'] = round($perValue);
							$ftrArray[$edate]['l1_total'] = $tc;
							$ftrArray[$edate]['l1_fc'] = $fc;
							break;
						case 5:
							$fc = $value->formcount; 
							if($fc > 0 && isset($countByRoles[$countIndex]) && isset($countByRoles[$countIndex]->totalcount)){
								$tc = $countByRoles[$countIndex]->totalcount;
								$perValue = $fc / $tc * 100;
							}
							$ftrArray[$edate]['l2'] = round($perValue);
							$ftrArray[$edate]['l2_total'] = $tc;
							$ftrArray[$edate]['l2_fc'] = $fc;
							break;
						case 6:
							$fc = $value->formcount; 
							if($fc > 0 && isset($countByRoles[$countIndex]) && isset($countByRoles[$countIndex]->totalcount)){
								$tc = $countByRoles[$countIndex]->totalcount;
								$perValue = $fc / $tc * 100;
							}
							$ftrArray[$edate]['qc'] = round($perValue);
							$ftrArray[$edate]['qc_total'] = $tc;
							$ftrArray[$edate]['qc_fc'] = $fc;
							break;
					}
				}
			}		
			
			
		} // ForEach Date

		//echo '<pre>'; print_r($ftrArray); exit; 
		return $ftrArray;
	}
	
	
	
	public function getErrDetection($startDate = '', $endDate = '', $branchIds = ''){				
		
		$errRate = array();
		$errRateResponse = array();		

		if ($endDate != '') {
			$endDate = Carbon::parse($endDate)->addDays(-1)->format('d-m-Y');
		}else{
			$endDate = Carbon::now()->format('d-m-Y');
		}
		if ($startDate != '') {
			$startDate = Carbon::parse($startDate)->format('d-m-Y');			
		}else{
			$startDate = Carbon::now()->subDays(7)->format('d-m-Y');
		}
 								
		if ($branchIds != '') {
			$formsCleared = DB::table('NPC_REVIEW_LOG')
						->select('ROLE_ID', DB::raw('count(*) as formcount'))	
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') >= '".$startDate."'")
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') <= '".$endDate."'")
						->where('DESC_COUNT',0)
						->where('STATUS', 'approved')
						->whereIn('BRANCH_ID', $branchIds) 							
						->groupBy('ROLE_ID')
						->get()->toArray();		
			$totalProcessed = DB::table('NPC_REVIEW_LOG')
						->select('ROLE_ID', DB::raw('count(*) as totalcount'))	
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') >= '".$startDate."'")
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') <= '".$endDate."'")
						->whereIn('BRANCH_ID', $branchIds) 							
						->groupBy('ROLE_ID')
						->get()->toArray();		
			
		}else{

			$formsCleared = DB::table('NPC_REVIEW_LOG')
						->select('ROLE_ID', DB::raw('count(*) as formcount'))	
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') >= '".$startDate."'")
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') <= '".$endDate."'")
						->where('DESC_COUNT',0)
						->where('STATUS', 'approved')			
						->groupBy('ROLE_ID')
						->get()->toArray();		
			$totalProcessed = DB::table('NPC_REVIEW_LOG')
						->select('ROLE_ID', DB::raw('count(*) as totalcount'))	
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') >= '".$startDate."'")
						->whereRaw("to_char(NPC_REVIEW_LOG.CREATED_AT,'DD-MM-YYYY') <= '".$endDate."'")
						->groupBy('ROLE_ID')
						->get()->toArray();		
		
		}

		$errRate['branch_total'] = 0; 
		$errRate['branch_fc'] = 0; 
		$errRate['branch_per'] = 0; 
		$errRate['l1_total'] = 0; 
		$errRate['l1_fc'] = 0; 
		$errRate['l1_per'] = 0; 
		$errRate['l2_total'] = 0; 
		$errRate['l2_fc'] = 0; 
		$errRate['l2_per'] = 0; 
		$errRate['l3_total'] = 0; 
		$errRate['l3_fc'] = 0; 
		$errRate['l3_per'] = 0; 
	
		foreach($formsCleared as $key => $value){
					
					switch($value->role_id){
						case 3:
						    $errRate['branch_fc'] = $value->formcount;
							foreach($totalProcessed as $tkey => $tvalue){
								if($totalProcessed[$tkey]->role_id == $value->role_id){
									$errRate['branch_total'] = $totalProcessed[$tkey]->totalcount;
								}																	
							}
							if($errRate['branch_fc'] > 0 && $errRate['branch_total'] > 0){
								$errRate['branch_per'] = 100 - (($errRate['branch_fc']/$errRate['branch_total']) * 100);
							}
							break;
						case 4:
						    $errRate['l1_fc'] = $value->formcount;
							foreach($totalProcessed as $tkey => $tvalue){
								if($totalProcessed[$tkey]->role_id == $value->role_id){
									$errRate['l1_total'] = $totalProcessed[$tkey]->totalcount;
								}																	
							}
							if($errRate['l1_fc'] > 0 && $errRate['l1_total'] > 0){
								$errRate['l1_per'] = 100 - (($errRate['l1_fc']/$errRate['l1_total']) * 100);
							}
							break;
						case 5:
						    $errRate['l2_fc'] = $value->formcount;
							foreach($totalProcessed as $tkey => $tvalue){
								if($totalProcessed[$tkey]->role_id == $value->role_id){
									$errRate['l2_total'] = $totalProcessed[$tkey]->totalcount;
								}																	
							}
							if($errRate['l2_fc'] > 0 && $errRate['l2_total'] > 0){
								$errRate['l2_per'] = 100 - (($errRate['l2_fc']/$errRate['l2_total']) * 100);
							}
							break;
						case 6:
						    $errRate['l3_fc'] = $value->formcount;
							foreach($totalProcessed as $tkey => $tvalue){
								if($totalProcessed[$tkey]->role_id == $value->role_id){
									$errRate['l3_total'] = $totalProcessed[$tkey]->totalcount;
								}																	
							}
							if($errRate['l3_fc'] > 0 && $errRate['l3_total'] > 0){
								$errRate['l3_per'] = 100 - (($errRate['l3_fc']/$errRate['l3_total']) * 100);
							}
							break;
						
					}
						
		}					
						
		//echo '<pre>'; print_r($formsCleared); print_r($totalProcessed); print_r($errRate); exit;										
		
		array_push($errRateResponse, round($errRate['branch_per']));
		array_push($errRateResponse, round($errRate['l1_per']));
		array_push($errRateResponse, round($errRate['l2_per']));
		array_push($errRateResponse, round($errRate['l3_per']));
		
		return $errRateResponse; 
	
	
	}

}
?>