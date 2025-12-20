<?php

namespace App\Http\Controllers\DelightAdmin;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\DelightFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //declare userId as global variable
    protected $userId;
    //declare roleId as global variable
    protected $roleId;

    public function __construct()
    {
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
        }
        ini_set('max_execution_time', '130');
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

    public function kitCountApproval()
    {
        try{
            $branchId = Session::get('branchId');
            $schemeCodeId = '';
            $kitStatusId = '';
            $drStatusId = '';

            $delightSchemeCodes = DelightFunctions::getDelightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();
            $drStatusList = DelightFunctions::getDRStatusList();

            //returns tempalte
            return view('delightadmin.kitcountapproval')
                                        ->with('delightSchemeCodes',$delightSchemeCodes)
                                        ->with('schemeCodeId',$schemeCodeId)
                                        ->with('delightKitStatus',$delightKitStatus)
                                        ->with('kitStatusId',$kitStatusId)
                                        ->with('drStatusList',$drStatusList)
                                        ->with('drStatusId',$drStatusId)
                                        ;
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('DelightAdmin/DashboardController','kitCountApproval',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitcountapprovaltable(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr=[];
           


            //build columns array
            $filteredColumns = ['DELIGHT_REQUEST.ID','DELIGHT_REQUEST.SOL_ID','DELIGHT_REQUEST.SCHEME_CODE','DELIGHT_REQUEST.REQUEST_COUNT','DELIGHT_REQUEST.CREATED_AT','DELIGHT_REQUEST.DR_STATUS','ACTION','DELIGHT_REQUEST.BATCH_NUMBER'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                // if($column == "ID"){
                //     $dt[$i] = array( 'db' => 'DELIGHT_REQUEST.ID','dt' => $i,
                //         'formatter' => function( $d, $row ) {
                //             $html = '';
                //             if($row->dr_status == 1 )
                //             {
                //                 $html =  '<input type="checkbox" class="approval_checkbox" id="request_checkbox-'.$row->id.'" name="request-checkbox" value="'.$row->id.'">';
                //             }
                //             return $html;
                //         }
                //     );
                // }else
                if($column == "DELIGHT_REQUEST.CREATED_AT"){
                    array_push($select_arr,strtolower("DELIGHT_REQUEST.CREATED_AT"));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_REQUEST.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $date = \Carbon\Carbon::parse($row->created_at)->format('d-M-Y');
                            return $date;
                        }
                    );
                }else if($column == "DELIGHT_REQUEST.DR_STATUS"){
                    array_push($select_arr,strtolower('DELIGHT_REQUEST.DR_STATUS'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_REQUEST.DR_STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            // echo "test<pre>";print_r($row);exit;
                            $row = (array) $row;

                             $statusMap = [
                                    1 => 'Requested',
                                    2 => 'Forwarded',
                                    3 => 'Generated',
                                    4 => 'Dispatched',
                                    5 => 'Recevied',
                                ];
                            $html =  '<span id="dr_status-'.$row['id'].'">'.$statusMap[$row['dr_status']];
                            // echo "<pre>";print_r($row);exit;

                            if($row['dr_status'] == 2)
                            {
                                $kitGeneratedCount = DB::table('DELIGHT_KIT')->where(['DR_ID'=>$row['id'],'STATUS'=> null])
                                                                            ->count();
                                $html .= ' ('.$kitGeneratedCount.')';
                            }
                            else if($row['dr_status'] == 3)
                            {
                                $kitGeneratedCount = DB::table('DELIGHT_KIT')->where('DR_ID',$row['id'])
                                                                                ->where('CUSTOMER_ID','!=' ,null)
                                                                                ->where('ACCOUNT_NUMBER','!=' ,null)
                                                                                ->count();
                                //echo "<pre>";print_r($kitGeneratedCount);exit;
                                $html .= ' ('.$kitGeneratedCount.')';
                            }else if($row['dr_status'] == 4)
                            {
                                $kitGeneratedCount = DB::table('DELIGHT_KIT')->where('DR_ID',$row['id'])
                                                                                ->where('STATUS', 3)
                                                                                ->count();
                                //echo "<pre>";print_r($kitGeneratedCount);exit;
                                $html .= ' ('.$kitGeneratedCount.')';
                            }else if($row['dr_status'] == 5)
                            {
                                $kitGeneratedCount = DB::table('DELIGHT_KIT')->where('DR_ID',$row['id'])
                                                                                ->where('STATUS', '>=', 4)
                                                                                ->where('STATUS', '!=', 5)
                                                                                ->count();
                                // echo "<pre>";print_r($kitGeneratedCount);exit;
                                $html .= ' ('.$kitGeneratedCount.')';
                            }

                            $html .= '</span>';
                            // echo "<pre>";print_r($kitGeneratedCount);exit;
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    // echo "test";exit;
                    array_push($select_arr,strtolower('DELIGHT_REQUEST.ID as ACTION'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_REQUEST.ID as ACTION'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            // echo "<pre>";print_r($row);exit;
                            //$generatedbyfinacle = DelightFunctions::kitsgeneratedbyfinacle($row['id'],$row['sol_id'], $row['scheme_code']);
                            $html = '';
                            if (($row['dr_status'] >= 3) && (Session::get('role') == 16)){
                                //Kit Generated
                                $kitDispatchedCount = DB::table('DELIGHT_KIT')->where('DR_ID',$row['id'])
                                                                                ->where('STATUS','<=',2)
                                                                                ->count();
                                if ($kitDispatchedCount == 0) {
                                    $html = '';
                                }else{
                                     $html = '<button type="button" class="btn btn-outline-success kit-count-approve" id="kit_dispatch-'.$row['id'].'">Dispatch ('.$kitDispatchedCount.')</button>';
                                }

                            }else if(($row['dr_status'] == 1)  && (Session::get('role') == 8)){
                                //Requested
                                $html = '<button type="button" class="btn btn-outline-success kit-count-approve" id="kit_count_approve-'.$row['id'].'">Approve</button>';
                            }
                            return $html;
                        }
                    );
                }else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            //$dt_obj = new SSP('DELIGHT_REQUEST', $dt); commented line during version upgrade
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('DELIGHT_REQUEST')->select($select_arr);
            // $dt_obj = $dt_obj->leftJoin('DR_STATUS_LIST','DR_STATUS_LIST.ID','DELIGHT_REQUEST.DR_STATUS');

            if(isset($requestData['SolId']))
            {
                $dt_obj = $dt_obj->whereRaw("DELIGHT_REQUEST.SOL_ID LIKE '%".$requestData['SolId']."%'");
            }

            if(isset($requestData['delightSchemeCode']))
            {
                $dt_obj = $dt_obj->where("DELIGHT_REQUEST.SCHEME_CODE",$requestData['delightSchemeCode']);
            }

            if(isset($requestData['drStatusId']))
            {
                $dt_obj = $dt_obj->where("DELIGHT_REQUEST.DR_STATUS",$requestData['drStatusId']);
            }

            if(isset($requestData['startDate']))
            {
                $dt_obj = $dt_obj->whereRaw("to_char(DELIGHT_REQUEST.CREATED_AT,'DD-MM-YYYY')>='".$requestData['startDate']."'")
                                ->whereRaw("to_char(DELIGHT_REQUEST.CREATED_AT,'DD-MM-YYYY')<='".$requestData['endDate']."'");
            }

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);

            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function approveKitRequest(Request $request)
    {
        try{
            if ($request->ajax()) {
                $requestData = $request->get('data');
                $data = array();
                $updateDelightKitFlag = 0;
                DB::beginTransaction();
                if(count($requestData['approverIds']) == 0)
                {
                     return json_encode(['status'=>'fail','msg'=>'No records to process','data'=>[]]);
                }

				//$userId = Session::get('userId');
				//$userData = DB::table('USERS')->where('ID',$userId)->get()->toArray();
				//$userData = (array) current($userData);

				//$branchData = DB::table('BRANCH')->where('BRANCH_ID',$userData['empsol'])->get()->toArray();
							
				$delightKitTable = env('FINACLE_TABLE_DELIGHT_KIT');
				$delightFinacleQueryInstance = DB::connection('oracle2')->table($delightKitTable);

				for($r= 0; $r < count($requestData['approverIds']); $r++)
                {

					$branchData = DB::table('BRANCH')->select('SEG_CODE')
									->where('BRANCH_ID', $requestData['solIds'][0])->get()->toArray();
					$branchData = (array) current($branchData);

					$segData = DB::table('SEGMENT')->where('SEGMENT_CODE',$branchData['seg_code'])->get()->toArray();
					$segData = (array) current($segData);

                    $data['solId'] = $requestData['solIds'][$r];
                    $data['schemeCode'] = $requestData['schemeCodes'][$r];
					$data['segment_code'] = $branchData['seg_code'];
					$data['sub_segment'] = $segData['sub_segment'];

                    $requestCount = $requestData['requestCounts'][$r];
					
					$saveUpdateDelightKitFlag = false;					
					
                    for($j = 1; $j <= $requestCount; $j++)
                    {
                        if ($requestData['drStatus'][$r] == 'Requested') {

                            $sequnceNumber = DB::select('select DELIGHT_SEQUNCE.nextval from dual');
                            $sequnceNumber = (array) current($sequnceNumber);
                            $batchNumber = $sequnceNumber['nextval'];

                            $data['batchNumber'] = $batchNumber;
                            
							$insertData = DelightFunctions::buildInsertData($data);
                            
                            $saveDelightKit = $delightFinacleQueryInstance->insert($insertData);
                            $updateDelightKit = DB::table('DELIGHT_KIT')
                                                                        ->insert(['KIT_NUMBER'=>$batchNumber,'SOL_ID'=>$data['solId'],'SCHEME_CODE'=>$data['schemeCode'],'STATUS'=>'','DR_ID'=>$requestData['approverIds'][$r]]);
                            if($saveDelightKit && $updateDelightKit)
                            {
                                $saveUpdateDelightKitFlag = true;
                                $updatedDelightRequest = ['DR_STATUS' => 2,'BATCH_NUMBER'=>$batchNumber];
                            }
							
                        }
                        
                    } // InnerFor

                    if(isset($saveUpdateDelightKitFlag) && ($saveUpdateDelightKitFlag == true))
                    {
                        $updateDelightKitStatus = DB::table('DELIGHT_REQUEST')->whereId($requestData['approverIds'][$r])
                                                                            ->update($updatedDelightRequest);
                        
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'Unable to update Kit. Please try again','data'=>[]]);
                    }
                    // echo "<pre>";print_r( $updateDelightKitStatus);exit;
                }  // OutFor

                if($updateDelightKitStatus)
                {
                    //commit database if response is true
                    DB::commit();
                    DB::disconnect('oracle2');
                    return json_encode(['status'=>'success','msg'=>'Kit Request Approved Successfully','data'=>['inputData' => $requestData]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    DB::disconnect('oracle2');
                    return json_encode(['status'=>'fail','msg'=>'Error! Unable to Insert in Finacle table. Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatedrstatus(Request $request)
    {
        try {
            if ($request->ajax()) {
                $requestData = $request->get('data');
                $updateStatus = 1;
                DB::beginTransaction();
                if($requestData['status'] == "Forwarded to Finacle")
                {
                    $updateStatus = DB::table('DELIGHT_REQUEST')->whereId($requestData['requestId'])
                        ->update(['DR_STATUS'=>config('constants.DR_STAUS.KIT_GENERATED')]);
                }else if($requestData['status'] == "Kit Generated")
                {
                    $updateStatus = DB::table('DELIGHT_REQUEST')->whereId($requestData['requestId'])
                        ->update(['DR_STATUS'=>config('constants.DR_STAUS.DISPATCH_TO_BRANCH')]);
                }

                if($updateStatus)
                {
                    //commit database if response is true
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Updated Kit Status Updated Successfully','data'=>[]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'Error! Unable Update kit status. Please try again','data'=>[]]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
            }
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
    }

    public function kitdispatch(Request $request)
    {
        try{
            $solId = '';
            $schemeCode = '';
            $dr_no = '';
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = base64_decode(CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]));
                $params = explode('_',$decodedString);
                $solId = $params[0];
                $schemeCodeId = $params[1];
                $dr_no = $params[2];
            }

            $delightSchemeCodes = DelightFunctions::getDelightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();


            //returns tempalte
            return view('delightadmin.kitdispatch')
                                        ->with('delightSchemeCodes',$delightSchemeCodes)
                                        ->with('delightKitStatus',$delightKitStatus)
                                        ->with('solId',$solId)
                                        ->with('schemeCodeId',$schemeCodeId)
                                        ->with('dr_no',$dr_no)
                                        ;
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitdispatchtable(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
              $select_arr=[];
            //build columns array
            $filteredColumns = ['DELIGHT_KIT.ID','DELIGHT_KIT.SOL_ID','DELIGHT_KIT.SCHEME_CODE','DELIGHT_KIT.KIT_NUMBER','DELIGHT_KIT.CUSTOMER_ID','DELIGHT_KIT.ACCOUNT_NUMBER','ACTION'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "ACTION"){
                     array_push($select_arr,strtolower('DELIGHT_KIT.ID as ACTION'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.ID as ACTION'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html =  '<input type="checkbox" style="opacity: 20!important" class="kit-checkbox" name="kitDispatch" id="kitDispatch-'.$row->id.'" value="'.$row->id.'">';
                            return $html;
                        }
                    );
                }else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            // $dt_obj = new SSP('DELIGHT_KIT', $dt);
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('DELIGHT_KIT')->select($select_arr);
            $dt_obj = $dt_obj->where('DELIGHT_KIT.STATUS',1)
                             ->where('DELIGHT_KIT.DR_ID',$requestData['dr_no']);

            if($requestData['kitNumber'] != '')
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.KIT_NUMBER', 'like', '%'.$requestData['kitNumber'].'%');
            }

            if($requestData['customerId'] != '')
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.CUSTOMER_ID', 'like', '%'.$requestData['customerId'].'%');
            }

            if($requestData['accountNumber'] != '')
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.ACCOUNT_NUMBER', 'like', '%'.$requestData['accountNumber'].'%');
            }
            
             $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);

            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);


            // return response()->json($dt_obj->getDtArr());
        }
        catch(\Illuminate\Database\QueryException $e) {
           if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
           $eMessage = $e->getMessage();
           CommonFunctions::addExceptionLog($eMessage, $request);
           return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatekitstatus(Request $request)
    {
        try{
            if ($request->ajax()) {
                DB::beginTransaction();
                $requestData = $request->get('data');
  		if (!isset($requestData['kitIds'])) {
              return json_encode(['status'=>'fail','msg'=>'Error! Please choose atleast one kit to Dispatch','data'=>[]]);
        }       
        for($i=0; $i<count($requestData['kitIds']); $i++){
                    if ($requestData['kitIds'][$i] == 'on') {
                        unset($requestData['kitIds'][$i]);
                    }
                }
                foreach($requestData['kitIds'] as $kitID){
                    $updateKitStatus = DB::table('DELIGHT_KIT')->where('ID',$kitID)
                                                ->update(['STATUS'=>3]);

                    if ($updateKitStatus) {
                        $delightKit = DB::table('DELIGHT_KIT')->where('ID', $kitID)
                                                    ->get()->toArray();
                        $delightKit = current($delightKit);

                        $updateDRStatus = DB::table('DELIGHT_REQUEST')->where('ID',$delightKit->dr_id)
                                                    ->update(['DR_STATUS' => 4]);
                    }
                }

                if($updateDRStatus && $updateKitStatus)
                {
                    //commit database if response is true
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Kit Dispatched Successfully','data'=>[]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function branchinventory()
    {
        try{
            $branches = DB::table('BRANCH')->select('BRANCH_ID',DB::raw("BRANCH_ID || ' - ' || BRANCH_NAME as branch_name"))
                                        ->pluck('branch_name','branch_id')->toArray();
            return view('delightadmin.branchinventory')->with('branches',$branches);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('DelightAdmin/DashboardController','branchinventory',$eMessage);

            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function inventorydetails(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $branchId = $requestData['branchId'];
                $delightKitInventoryDetails = array();
                $delightSchemas = DB::table('SCHEME_CODES')->where('DELIGHT_SCHEME','Y')
                                                            ->pluck('scheme_code','id')->toArray();
                if(count($delightSchemas) > 0)
                {
                    foreach ($delightSchemas as $schemeCode)
                    {
                        $batchNumber = DB::table('DELIGHT_KIT')->where(['SOL_ID' => $branchId,'SCHEME_CODE' => $schemeCode])->pluck('batch_number')->toArray();
                        $delightKitInventoryDetails[$schemeCode]['Batch'] = current($batchNumber);
                        $delightKitInventoryDetails[$schemeCode]['Indent'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [1,2,3]);
                        $delightKitInventoryDetails[$schemeCode]['Received'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [4,11,6,7,9,10,12,13,14]);
                        $delightKitInventoryDetails[$schemeCode]['branchAvailable'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [4,11]);
                        //$delightKitInventoryDetails[$schemeCode]['Received'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [4,11]);
                        $delightKitInventoryDetails[$schemeCode]['notReceived'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [5]);
                        $delightKitInventoryDetails[$schemeCode]['Allocated'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [7]);
                        $delightKitInventoryDetails[$schemeCode]['Destroyed'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [9,13]);
                        $delightKitInventoryDetails[$schemeCode]['Missing'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [10,14]);
                        $delightKitInventoryDetails[$schemeCode]['Damaged'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [6,12]);
                        $delightKitInventoryDetails[$schemeCode]['Utilized'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [8]);
                        $delightKitInventoryDetails[$schemeCode]['totalAvailable'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, [4,7,11]);
                    }
                }
                return view('delightadmin.inventorytable')->with('delightKitInventoryDetails',$delightKitInventoryDetails);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            dd($e->getMessage());exit;
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitdetails(Request $request)
    {
        try{
            $branchId = Session::get('branchId');
            $schemeCode = '';
            $kitStatus = '';

            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $requestData = base64_decode($decodedString);
                $data = explode('_',$requestData);

                $schemeCode = $data[0];
                if(isset($data[1]))
                {
                    $kitStatus = $data[1];
                }

                if (isset($data[2])) {
                    $branchId = $data[2];
                }
            }

            $delightSchemeCodes = DelightFunctions::delightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();
            $schemeCodeId = array_search($schemeCode,$delightSchemeCodes);
            $kitStatusId = array_search($kitStatus,$delightKitStatus);

            $branchSaleUsers =  DB::table('USERS')
                ->select('ID',DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME|| ' (' ||HRMSNO|| ')' AS user_name"))
                ->where(['ROLE'=>2,'EMPSOL'=>$branchId])
                ->pluck('user_name','id')->toArray();
            /*echo "<pre>";print_r($schemeCode);
            echo "<pre>";print_r($kitStatus);
            echo "<pre>";print_r($kitStatusId);
            echo "<pre>";print_r($delightKitStatus);
            exit;*/

            //returns tempalte
            return view('delightadmin.kitdetails')
                                        ->with('delightSchemeCodes',$delightSchemeCodes)
                                        ->with('schemeCodeId',$schemeCodeId)
                                        ->with('delightKitStatus',$delightKitStatus)
                                        ->with('kitStatusId',$kitStatusId)
                                        ->with('branchSaleUsers',$branchSaleUsers)
                                        ->with('branchId',$branchId)
                                        ;

        }
        catch(\Illuminate\Database\QueryException $e) {
            dd($e->getMessage());exit;
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitdetailstable(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr=[];
            $branchId = Session::get('branchId');

            //build columns array
            $filteredColumns = ['ID','SOL_ID','SCHEME_CODE','KIT_NUMBER','CUSTOMER_ID','ACCOUNT_NUMBER','CREATED_AT','STATUS'/*,'ACTION'*/];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "CREATED_AT"){
                     array_push($select_arr,"CREATED_AT");
                    $dt[$i] = array( 'db' => 'DELIGHT_KIT.CREATED_AT','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = \Carbon\Carbon::parse($row['created_at'])->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "STATUS"){

                    array_push($select_arr,strtolower('DELIGHT_KIT.STATUS'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.STATUS'),'dt' => $i,        
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if (isset($row->status) && $row->status != '') {
                                $status = DelightFunctions::getDelightKitStatusById($row->status);
                                $html = ucfirst(strtolower($status['kitstatus']));
                            }

                            return $html;
                        }
                    );
                }/*else if($column == "ACTION"){
                    $dt[$i] = array( 'db' => 'DELIGHT_KIT.ID','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if (in_array($row->status, [3, 4, 11, 7]))
                            {
                                $html =  '<input type="checkbox" style="opacity: 20!important" class="kit-checkbox" name="kitStatus" id="kitStatus-'.$row->id.'" value="'.$row->id.'">';
                            }
                            return $html;
                        }
                    );
                }*/else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            // $dt_obj = new SSP('DELIGHT_KIT', $dt);
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('DELIGHT_KIT')->select($select_arr);

//            $delightKitStatus = [3,4,5,6,7,8,9,10,11,12,13,14];

            $dt_obj = $dt_obj/*->where('DELIGHT_KIT.SOL_ID',$branchId)*/
                ->where('DELIGHT_KIT.CUSTOMER_ID', '!=', null)
                ->where('DELIGHT_KIT.ACCOUNT_NUMBER', '!=', null)/*
                ->whereIn('DELIGHT_KIT.STATUS', $delightKitStatus)*/;


            if(isset($requestData['branchID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.SOL_ID', 'like', '%'.$requestData['branchID'].'%');
            }

            if(isset($requestData['delightSchemeCode']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.SCHEME_CODE',$requestData['delightSchemeCode']);
            }

            if(isset($requestData['kitNumber']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.KIT_NUMBER', 'like', '%'.$requestData['kitNumber'].'%');
            }

            if(isset($requestData['customerID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.CUSTOMER_ID', 'like', '%'.$requestData['customerID'].'%');
            }

            if(isset($requestData['accountID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.ACCOUNT_NUMBER', 'like', '%'.$requestData['accountID'].'%');
            }

            if(isset($requestData['delightKitStatus']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.STATUS', strtoupper($requestData['delightKitStatus']));
            }

            if(isset($requestData['startDate']))
            {
                $dt_obj = $dt_obj->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                    ->whereRaw("ACCOUNT_DETAILS.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);

            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

            // return response()->json($dt_obj->getDtArr());

        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



    public function adminkitdetailshistory(Request $request)
    {
        try{
            $branchId = Session::get('branchId');
            $schemeCode = '';
            $kitStatus = '';

            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $requestData = base64_decode($decodedString);
                $data = explode('_',$requestData);
                
                $schemeCode = $data[0];
                // echo '<pre>';print_r($data[0]);exit;
                if(isset($data[1]))
                {
                    $kitStatus = $data[1];
                }

                if (isset($data[2])) {
                    $branchId = $data[2];
                }
            }

            $delightSchemeCodes = DelightFunctions::delightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();
            $schemeCodeId = array_search($schemeCode,$delightSchemeCodes);
            $kitStatusId = array_search($kitStatus,$delightKitStatus);

            $branchSaleUsers =  DB::table('USERS')
                ->select('ID',DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME|| ' (' ||HRMSNO|| ')' AS user_name"))
                ->where(['ROLE'=>2,'EMPSOL'=>$branchId])
                ->pluck('user_name','id')->toArray();
            /*echo "<pre>";print_r($schemeCode);
            echo "<pre>";print_r($kitStatus);
            echo "<pre>";print_r($kitStatusId);
            echo "<pre>";print_r($delightKitStatus);
            exit;*/

            //returns tempalte
            return view('delightadmin.adminkitdetailshistory')
                                        ->with('delightSchemeCodes',$delightSchemeCodes)
                                        ->with('schemeCodeId',$schemeCodeId)
                                        ->with('delightKitStatus',$delightKitStatus)
                                        ->with('kitStatusId',$kitStatusId)
                                        ->with('branchSaleUsers',$branchSaleUsers)
                                        ->with('branchId',$branchId);

        }
        catch(\Illuminate\Database\QueryException $e) {
            dd($e->getMessage());exit;
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitdetailshistorytable(Request $request){
        try {
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr=[];
            $branchId = Session::get('branchId');
            //build columns array
            $branchSaleUsers =  DB::table('USERS')->select('ID',DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME|| ' (' ||HRMSNO|| ')' AS user_name"))
                                                  ->pluck('user_name','id')
                                                  ->toArray();

                   
            // $filteredColumns = ['ID','DELIGHT_KIT.SOL_ID','DELIGHT_KIT.SCHEME_CODE','KIT_NUMBER','DELIGHT_KIT.CUSTOMER_ID','DELIGHT_KIT.ACCOUNT_NUMBER','CREATED_AT','DKIT_STATUS','CREATED_BY'];

            $filteredColumns = ['DELIGHT_KIT.ID','DELIGHT_KIT.SOL_ID','DELIGHT_KIT.SCHEME_CODE','DELIGHT_KIT.KIT_NUMBER','DELIGHT_KIT.CUSTOMER_ID','DELIGHT_KIT.ACCOUNT_NUMBER','DKIT_STATUS_HISTORY.CREATED_AT','DKIT_STATUS_HISTORY.DKIT_STATUS','DKIT_STATUS_HISTORY.CREATED_BY','DKIT_STATUS_HISTORY.COMMENTS'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "CREATED_AT"){
                      array_push($select_arr,strtolower('DKIT_STATUS_HISTORY.CREATED_AT'));
                    $dt[$i] = array( 'db' => strtolower('DKIT_STATUS_HISTORY.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            
                            $row = (array) $row;
                            $html = \Carbon\Carbon::parse($row['dkit_status_history.created_at'])->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "DKIT_STATUS"){
                    
                      array_push($select_arr,strtolower('DKIT_STATUS_HISTORY.DKIT_STATUS'));
                    $dt[$i] = array( 'db' => strtolower('DKIT_STATUS_HISTORY.DKIT_STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;

                            $html = '';
                            
                            if (isset($row['dkit_status_history.dkit_status']) && $row['dkit_status_history.dkit_status'] != '') {
                                $status = DelightFunctions::getDelightKitStatusById($row['dkit_status_history.dkit_status']);
                                $html = ucfirst(strtolower($status['kitstatus']));
                            }

                            return $html;
                        }
                    );
                }else if($column == "CREATED_BY"){

                      array_push($select_arr,strtolower('DKIT_STATUS_HISTORY.CREATED_BY'));
                    $dt[$i] = array( 'db' => strtolower('DKIT_STATUS_HISTORY.CREATED_BY'),'dt' => $i,
                        'formatter' => function( $d, $row) {
                        //    echo "<pre>";print_r($row);exit;
                            $row = (array) $row;
                            $branchSaleUsers =  DB::table('USERS')->select('ID',DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME|| ' ' AS user_name"))
                                                  ->pluck('user_name','id')
                                                  ->toArray();
                            $html = '';
                            $html = isset($branchSaleUsers[$row['dkit_status_history.created_by']]) && $branchSaleUsers[$row['dkit_status_history.created_by']] != ''?$branchSaleUsers[$row['dkit_status_history.created_by']]:'';
                            return $html;
                        }
                    );
                }else if($column == "COMMENTS"){
                      array_push($select_arr,strtolower('DKIT_STATUS_HISTORY.COMMENTS'));
                    $dt[$i] = array( 'db' => strtolower('DKIT_STATUS_HISTORY.COMMENTS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = $row['dkit_status_history.comments'];
                            return $html;
                        }
                    );
                }else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }

            // $dt_obj = new SSP('DKIT_STATUS_HISTORY', $dt);
            // $dt_obj = $dt_obj->leftjoin('DELIGHT_KIT','DELIGHT_KIT.ID','DKIT_STATUS_HISTORY.DKIT_ID');

            // $dt_obj = new SSP('DELIGHT_KIT', $dt);
              $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('DELIGHT_KIT')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('DKIT_STATUS_HISTORY','DKIT_STATUS_HISTORY.DKIT_ID','DELIGHT_KIT.ID');
            // $dt_obj = $dt_obj->where('DELIGHT_KIT.KIT_NUMBER','=','590967');

            if(isset($requestData['branchID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.SOL_ID', 'like', '%'.$requestData['branchID'].'%');
            }

            if(isset($requestData['delightSchemeCode']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.SCHEME_CODE',$requestData['delightSchemeCode']);
            }

            if(isset($requestData['kitNumber']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.KIT_NUMBER', 'like', '%'.$requestData['kitNumber'].'%');
            }

            if(isset($requestData['customerID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.CUSTOMER_ID', 'like', '%'.$requestData['customerID'].'%');
            }

            if(isset($requestData['accountID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.ACCOUNT_NUMBER', 'like', '%'.$requestData['accountID'].'%');
            }

            if(isset($requestData['delightKitStatus']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.STATUS', strtoupper($requestData['delightKitStatus']));
            }

            if(isset($requestData['startDate']))
            {
                $dt_obj = $dt_obj->whereRaw("DELIGHT_KIT.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                    ->whereRaw("DELIGHT_KIT.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }

    
            $dt_obj = $dt_obj->orderBy('ID', 'DESC');
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);

            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);


            // return response()->json($dt_obj->getDtArr());
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

   
}
?>
