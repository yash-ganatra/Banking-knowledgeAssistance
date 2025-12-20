<?php

namespace App\Http\Controllers\Maker;

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

            if($this->roleId != 15){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Maker/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
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

    /*
     * Method Name: dashboard
     * Created By : Sharanya T
     * Created At : 12-02-2020

     * Description:
     * This function is fetch savings , current and fixed deposite accounts Count and customer names
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function dashboard()
    {
        try{
            $branchId = Session::get('branchId');
            if ($branchId == '') {
                 return json_encode(['status'=>'fail','msg'=>'Branch Id not found','data'=>[]]);
            }
            $delightKitsDetails = array();
            $allocatedToSales = config('constants.DELIGHT_KIT_STATUS.ALLOCATED_TO_SALES');
            $unallocated = config('constants.DELIGHT_KIT_STATUS.UNALLOCATED');
            $utilized = config('constants.DELIGHT_KIT_STATUS.UTILIZED');
            $availableSalesCount = DelightFunctions::delightKitCountsByStatus($branchId, $allocatedToSales);
            $availableBranchCount = DelightFunctions::delightKitCountsByStatus($branchId, $unallocated);
            $utilizedCount = DelightFunctions::delightKitCountsByStatus($branchId, $utilized);

            $delightSchemeCodes = DB::table('SCHEME_CODES')->where(['ACCOUNT_TYPE'=>1,'DELIGHT_SCHEME'=>'Y','IS_ACTIVE'=>1])
                                                    ->pluck('scheme_desc','scheme_code')->toArray();

            if(count($delightSchemeCodes) > 0)
            {
                $i = 1;
                foreach ($delightSchemeCodes as $schemeCode => $schemeDescription)
                {
                    $delightKitsDetails[$schemeCode]['id'] = $i;
                    $delightKitsDetails[$schemeCode]['schemeCode'] = $schemeCode;
                    $delightKitsDetails[$schemeCode]['description'] = $schemeDescription;
                    $delightKitsDetails[$schemeCode]['availableSalesCount'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, $allocatedToSales);
                    $delightKitsDetails[$schemeCode]['availableBranchCount'] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, $unallocated);
                    $delightKitsDetails[$schemeCode]['totalAvailable'] = $delightKitsDetails[$schemeCode]['availableSalesCount'] + $delightKitsDetails[$schemeCode]['availableBranchCount'];

                    $requestedKits = DB::table('DELIGHT_REQUEST')->where('sol_id',$branchId)
                                                                ->where('scheme_code',$schemeCode)
                                                                ->where('dr_status','<>',5)          // Till the time its not recd by Branch treat as request
                                                                ->orderBy('id', 'DESC')
                                                                ->get()->toArray();

                    $dispatchedKits = DB::table('DELIGHT_KIT')->where('sol_id',$branchId)
                                                                ->where('scheme_code',$schemeCode)
                                                                ->where('status', 3) //kits dispatched from delight admin
                                                                ->get()->toArray();


                    if (count($requestedKits) > 0) {
                        $requestedKits = current($requestedKits);
                        $receivedDKits = DB::table('DELIGHT_KIT')->where('dr_id', $requestedKits->id)
                                                                ->where('status', '>=', 3) //kits dispatched from delight admin
                                                                ->count();

                        if ($receivedDKits == $requestedKits->request_count) {
                            $delightKitsDetails[$schemeCode]['requestedKits'] = 0;
                        }else{
                            $delightKitsDetails[$schemeCode]['requestedKits'] = $requestedKits->request_count;
                        }
                    }else{
                        $delightKitsDetails[$schemeCode]['requestedKits'] = 0;
                    }

                    if (count($dispatchedKits) > 0) {
                        $delightKitsDetails[$schemeCode]['dispatchedKits'] = count($dispatchedKits);
                    }
                    $i++;
                }
            }

            $configKitRequestThreshold = config('constants.DELIGHT_KIT_REQUEST_THRESHOLD');
            //echo "<pre>";print_r($delightKitsDetails);exit;
            return view('maker.dashboard')
                                        ->with('availableSalesCount',$availableSalesCount)
                                        ->with('availableBranchCount',$availableBranchCount)
                                        ->with('utilizedCount',$utilizedCount)
                                        ->with('delightKitsDetails',$delightKitsDetails)
                                        ->with('configKitRequestThreshold', $configKitRequestThreshold);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','dashboard',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function saveindent(Request $request)
    {
        try{
            if($request->ajax()){
                $branchId = Session::get('branchId');
                $requestData = $request->get('data');

                $getStatus = CommonFunctions::checkbranchActive($branchId);
                if(isset($getStatus['status']) && $getStatus['status'] == 'fail'){
                    return json_encode(['status'=>$getStatus['status'],'msg'=>$getStatus['message'],'data'=>[]]);
                }

                $specialScheme = config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.SPECIAL_SCHEME');
                $specialMin = config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.SPECIAL_MIN');
                $specialMax = config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.SPECIAL_MAX');

                $defaultMin = config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.DEFAULT_MIN');
                $defaultMax = config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.DEFAULT_MAX');

                if (in_array($requestData['SCHEME_CODE'], $specialScheme)) {
                    foreach ($specialScheme as $scheme) {
                        if (($requestData['SCHEME_CODE'] == $scheme) && (($requestData['REQUEST_COUNT'] < $specialMin) || ($requestData['REQUEST_COUNT'] > $specialMax)) ) {
                            return json_encode(['status'=>'fail','msg'=>'Permitted kits '.$specialMin.'-'.$specialMax.'','data'=>[]]);
                        }
                    }
                }else if (($defaultMin > $requestData['REQUEST_COUNT']) || ($defaultMax < $requestData['REQUEST_COUNT']) ) {
                    return json_encode(['status'=>'fail','msg'=>'Permitted kits '.$defaultMin.'-'.$defaultMax.'','data'=>[]]);
                }

                if (($requestData['REQUEST_COUNT'] % 5 != 0) || ($requestData['REQUEST_COUNT'] == 0)) {
                     return json_encode(['status'=>'fail','msg'=>'Please add Kit request count as multiple of 5','data'=>[]]);
                }
                
                $insertData = Arr::except($requestData, ['functionName']);
                $insertData['SOL_ID'] = $branchId;
                $insertData['CREATED_BY'] = $this->userId;
                $saveKitRequestData = DB::table('DELIGHT_REQUEST')->insert($insertData);
                if($saveKitRequestData)
                {
                    return json_encode(['status'=>'success','msg'=>'Kit Requested Data Saved Successfully','data'=>[]]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitstatusbyscheme(Request $request)
    {
        try{
            $branchId = Session::get('branchId');
            $schemeCode = '';
            $delightKitStatusDetails = array();
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $schemeCode = base64_decode($decodedString);
            }

            $allocatedToSales = config('constants.DELIGHT_KIT_STATUS.ALLOCATED_TO_SALES');
            $unallocated = config('constants.DELIGHT_KIT_STATUS.UNALLOCATED');
            $utilized = config('constants.DELIGHT_KIT_STATUS.UTILIZED');
            $availableSalesCount = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, $allocatedToSales);
            $availableBranchCount = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, $unallocated);
            $utilizedCount = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, $utilized);
            $schemeDetails = DB::table('SCHEME_CODES')->where('SCHEME_CODE',$schemeCode)
                                                    ->pluck('scheme_desc','scheme_code')->toArray();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();
//            echo "<pre>";print_r($delightKitStatus);exit;
            if(count($delightKitStatus) > 0)
            {
                $statusToShow = array('DAMAGED','ALLOCATED TO SALES','UTILIZED','DESTROYED','MISSING','UNALLOCATED');
                $delightKitStatusDetails['totalkitCount'] = 0;
                foreach ($delightKitStatus as $status)
                {
                    if (in_array($status, $statusToShow)) {
                        $kitsStatus = str_replace(' ', '_', $status);
                        $delightKitStatusDetails['kitCount'][$status] = DelightFunctions::delightKitCountsByStatusAndScheme($branchId, $schemeCode, config('constants.DELIGHT_KIT_STATUS.'.$kitsStatus));
                        $delightKitStatusDetails['totalkitCount'] = $delightKitStatusDetails['totalkitCount'] + $delightKitStatusDetails['kitCount'][$status];
                    }
                }
            }

            /*echo $availableSalesCount;
            echo $availableBranchCount;
            echo $utilizedCount;
            echo "<pre>";print_r($schemeDetails);
            echo "<pre>";print_r($delightKitStatusDetails);
            exit;*/

            //returns tempalte
            return view('maker.kitstatusdetails')
                                        ->with('schemeCode',$schemeCode)
                                        ->with('availableSalesCount',$availableSalesCount)
                                        ->with('availableBranchCount',$availableBranchCount)
                                        ->with('utilizedCount',$utilizedCount)
                                        ->with('schemeDetails',$schemeDetails)
                                        ->with('delightKitStatusDetails',$delightKitStatusDetails)
                                        ;

        }
        catch(\Illuminate\Database\QueryException $e) {
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
                // echo "<pre>";print_r($data);exit;
                $schemeCode = $data[0];
                if(isset($data[1]) && ($data[1] != ''))
                {
                    $kitStatus = $data[1];
                }
            }

            $delightSchemeCodes = DelightFunctions::delightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();
            $schemeCodeId = array_search($schemeCode,$delightSchemeCodes);
            $kitStatusId = array_search($kitStatus,$delightKitStatus);

            $branchSaleUsers =  DB::table('USERS')
                                        ->select('ID',DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME|| ' (' ||HRMSNO|| ')' AS user_name"))
                                        ->where(['ROLE'=>2,'EMPSOL'=>$branchId, 'EMPSTATUS'=>'Y'])
                                        ->pluck('user_name','id')->toArray();
            /*echo "<pre>";print_r($schemeCode);
            echo "<pre>";print_r($schemeCodeId);
            echo "<pre>";print_r($kitStatus);
            echo "<pre>";print_r($kitStatusId);
            echo "<pre>";print_r($delightKitStatus);
            exit;*/

            //returns tempalte
            return view('maker.kitdetails')
                            ->with('delightSchemeCodes',$delightSchemeCodes)
                            ->with('schemeCodeId',$schemeCodeId)
                            ->with('delightKitStatus',$delightKitStatus)
                            ->with('kitStatusId',$kitStatusId)
                            ->with('branchSaleUsers',$branchSaleUsers)
                            ;

        }
        catch(\Illuminate\Database\QueryException $e) {
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
            $filteredColumns = ['DELIGHT_KIT.ID','DELIGHT_KIT.SOL_ID','DELIGHT_KIT.SCHEME_CODE','DELIGHT_KIT.KIT_NUMBER','DELIGHT_KIT.CUSTOMER_ID','DELIGHT_KIT.ACCOUNT_NUMBER','DELIGHT_KIT.CREATED_AT','DELIGHT_KIT.STATUS','ACTION', 'DELIGHT_KIT.SALES_USER_ID'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "DELIGHT_KIT.CREATED_AT"){
                    array_push($select_arr,strtolower("DELIGHT_KIT.CREATED_AT"));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = \Carbon\Carbon::parse($row['created_at'])->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "DELIGHT_KIT.STATUS"){
                    array_push($select_arr,strtolower("DELIGHT_KIT.STATUS"));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $branchId = Session::get('branchId');
                            $html = '';
                            if (isset($row->status) && $row->status != '') {
                                $status = DelightFunctions::getDelightKitStatusById($row->status);
                                if ($row->status == 7 ) {
                                    $branchSaleUsers =  DB::table('USERS')
                                        ->select(DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS user_name"))
                                        ->whereId($row->sales_user_id)->get()->toArray();
                                    $branchSaleUsers = current($branchSaleUsers);
                                   
                                    $html .= '<div class="tooltip_text_trunk" id="status_tooltip-'.$row->id.'">';
                                        $html .= '<span class="mytooltip">';
                                            $html .= '<span data-bs-toggle="tooltip" data-bs-placement="top" title="'.$branchSaleUsers->user_name.'">';
                                                    $html .= ucfirst(strtolower($status['kitstatus']));
                                            $html .= '</span>';
                                        $html .= '</span>';
                                    $html .= '</div>';

                                }else{
                                    $html = ucfirst(strtolower($status['kitstatus']));

                                }
                            }

                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr,strtolower("DELIGHT_KIT.ID AS ACTION"));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.ID AS ACTION'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if (in_array($row->status, [3, 4, 11, 7]))
                            {
                                $html =  '<input type="checkbox" style="opacity: 20!important" class="kit-checkbox" name="kitStatus" id="kitStatus-'.$row->id.'" value="'.$row->id.'">';
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
            //$dt_obj = new SSP('DELIGHT_KIT', $dt); Commented during version upgrade and added below two lines
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

//            $delightKitStatus = [3,4,5,6,7,8,9,10,11,12,13,14];
            $dt_obj = DB::table('DELIGHT_KIT')->select($select_arr);

            $dt_obj = $dt_obj->where('DELIGHT_KIT.SOL_ID',$branchId)
                            ->where('DELIGHT_KIT.CUSTOMER_ID', '!=', null)
                            ->where('DELIGHT_KIT.ACCOUNT_NUMBER', '!=', null)
                            /*->whereIn('DELIGHT_KIT.STATUS', $delightKitStatus)*/;


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

            if(isset($requestData['delightKitStatus'] ))
            {
                if($requestData['delightKitStatus'] == 3){
                    $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', [1,2,3]);
                }else if($requestData['delightKitStatus'] == 4){
                    $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', [4,11]);
                }else if($requestData['delightKitStatus'] == 6){
                    $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', [6,12]);
                }else if($requestData['delightKitStatus'] == 9){
                    $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', [9,13]);
                }else if($requestData['delightKitStatus'] == 10){
                    $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', [10,14]);
                }else{
                    $dt_obj = $dt_obj->where('DELIGHT_KIT.STATUS', $requestData['delightKitStatus']);
                }
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

        //    return response()->json($dt_obj->getDtArr());
        }
        catch(\Illuminate\Database\QueryException $e) {
           if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
           $eMessage = $e->getMessage();
           CommonFunctions::addExceptionLog($eMessage, $request);
           return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function statusupdateapproval(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $updateArray = array();
                $updateArray['CR_STATUS'] = $requestData['request-status'];
                $updateArray['REQUEST_COMMENT'] = $requestData['request_comment'];
                $updateStatus = DB::table('DELIGHT_KIT')->where('KIT_NUMBER',$requestData['kit_number'])
                                                        ->update($updateArray);
                if($updateStatus)
                {
                    return json_encode(['status'=>'success','msg'=>'Status Approval Updated Successfully','data'=>[]]);
                }else{
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

    public function updatekitstatus(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $updateArray = array();
                $updateArray['STATUS'] = $requestData['Status'];
                $updateArray['REQUEST_COMMENT'] = $requestData['request_comment'];

                if(!isset($requestData['Status']) && $requestData['Status'] == ''){
                    return json_encode(['status'=>'fail','msg'=>'Please select Status to update','data'=>[]]);
                }

                if (($requestData['Status'] == 7) && (!isset($requestData['userId'])) && ($requestData['userId'] == '')) {
                    return json_encode(['status'=>'fail','msg'=>'Please select User to allocate','data'=>[]]);
                }

                $status_PA = [12, 13, 14];
                if (in_array($requestData['Status'], $status_PA) && ($requestData['request_comment'] == '')) {
                    return json_encode(['status'=>'fail','msg'=>'Please add comment for approval','data'=>[]]);
                }
                //echo "<pre>";print_r();exit;
                if(isset($requestData['userId']))
                {
                    $updateArray['SALES_USER_ID'] = $requestData['userId'];
                }

                $kitIds = explode(',',$requestData['kitIds']);

                if (isset($requestData['Status'])) {
                    $delightKits = DB::table('DELIGHT_KIT')->whereIn('ID', $kitIds)
                                                    ->get()->toArray();
                    if (isset($requestData['userId'])) {
                        foreach ($delightKits as $i => $delightkit) {
                            $prevUserId = $delightkit->sales_user_id;
                            $newUserId = $requestData['userId'];
                            $saveuserlog = CommonFunctions::createUserLogDirect('Maker/DashboardController','updatekitstatus','kit allocated to other sales person', $prevUserId, $newUserId,Session::get('userId'));
                        }
                        if (!$saveuserlog) {
                            return json_encode(['status'=>'fail','msg'=>'Log Error! Please try again','data'=>[]]);
                        }
                    }

                    $updateStatus = DB::table('DELIGHT_KIT')->whereIn('ID',$kitIds)
                                                            ->update($updateArray);

                    $role = Session::get('userId');

                    $kitIds = explode(',',$requestData['kitIds']);

                    for ($i=0; count($kitIds) > $i ; $i++) { 
                        
                    $kit_num = DB::table('DELIGHT_KIT')->select('KIT_NUMBER')
                                                            ->where('ID',$kitIds[$i])
                                                        ->get()
                                                        ->toArray();
                    
                    $kit_num = (array) current($kit_num);
    
                        
                    if(count($kit_num)> 0){
                            $updatestatushistory = CommonFunctions::updateDKStatusHistory($kitIds[$i], $kit_num['kit_number'],$requestData['Status'],$role);
                            
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'No data found','data'=>[]]);
                    }
                    }

                    $delightKit = current($delightKits);
                    if ($delightKit->dr_id != '') {
                        $updateDRStatus = DB::table('DELIGHT_REQUEST')->where('ID',$delightKit->dr_id)
                                                ->update(['DR_STATUS' => 5]);
                    }
                }
                
                if($updateStatus)
                {
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Kit Status Updated Successfully','data'=>[]]);
                }else{
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function makerindent()
    {
        try{
            $kitSummary = DB::table('DELIGHT_KIT')
                                ->select('DELIGHT_KIT.SOL_ID','DELIGHT_KIT.SCHEME_CODE',
                                    'DKIT_STATUS_LIST.DKIT_STATUS',
                                    DB::raw('count(*) as count'))
                                ->leftjoin('DKIT_STATUS_LIST','DKIT_STATUS_LIST.ID','DELIGHT_KIT.STATUS')
                                ->where('DELIGHT_KIT.SOL_ID','104')
                                ->groupBy('delight_kit.sol_id', 'delight_kit.scheme_code', 'dkit_status_list.dkit_status')
                                ->get()->toArray();

            $kitCounts = array();
            $kitCounts = CommonFunctions::getDelightKitCounts($kitSummary);
            return view('maker.makerindent')->with('kitCounts',$kitCounts);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','makerindent',$eMessage);
    

            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function branchinventory()
    {
        try{
            $delightKitInventoryDetails = array();
            $branchId = Session::get('branchId');
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
            return view('maker.branchinventory')->with('delightKitInventoryDetails',$delightKitInventoryDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','branchinventory',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function kitinward()
    {
        try{
            $delightSchemas = DB::table('SCHEME_CODES')->where('DELIGHT_SCHEME','Y')
                                            ->pluck('scheme_code','id')->toArray();
            //echo "<pre>";print_r($delightSchemas);exit;

            return view('maker.kitinward')->with('delightSchemas',$delightSchemas);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            // CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','kitinward',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function damagemissingkit()
    {
        try{
            $delightSchemas = DB::table('SCHEME_CODES')->where('DELIGHT_SCHEME','Y')
                                            ->pluck('scheme_code','id')->toArray();
            return view('maker.damagemissingkit')->with('delightSchemas',$delightSchemas);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            // CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','damagemissingkit',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function delightkitschemadetails($sol_id){
        try{
            $delightKitDetails = DB::table('DELIGHT_KIT')
                                            ->where('SOL_ID',$sol_id)
                                            ->orderBy('SCHEME_CODE','ASC')
                                                ->get()->toArray();
            //echo "<pre>";print_r($delightKitDetails[0]->scheme_code);exit;
            return $delightKitDetails;
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            // CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','delightkitschemadetails',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkerkitdetailstable(Request $request)
    {
        try {

            //fetch data from request
            $requestData = $request->get('data');
            $solId = Session::get('branchId');
            $select_arr=[];
            // $solId = 010;
            // echo "<pre>";print_r($solId);exit;

            $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
            //build columns array
            $filteredColumns = ['ID','SOL_ID','SCHEME_CODE','KIT_NUMBER','CUSTOMER_ID','ACCOUNT_NUMBER','STATUS','CREATED_AT','REQUEST_COMMENT','APPROVAL_COMMENT','ACTION','CR_STATUS'];
            $i=0;
            
            foreach ($filteredColumns as $column) {
                if($column == "KIT_NUMBER"){
                    array_push($select_arr,strtolower("KIT_NUMBER"));
                    $dt[$i] = array( 'db' => strtolower('KIT_NUMBER'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $status_PA = [12,13,14];
                            $html = $row['kit_number'];

                            if (in_array($row['status'], $status_PA)) {
                                $html = '<a class="open-seek-approval-modal" data-bs-toggle="modal" data-bs-target="#seekApproval" id="kitNumber-'.$row['kit_number'].'" href="javascript:void(0)">'.$row['kit_number'].'</a>';

                            }
                            return $html;
                        }
                    );
                }else if($column == "STATUS"){
                    array_push($select_arr,strtolower("STATUS"));
                    $dt[$i] = array( 'db' => strtolower('STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = '';
                            if (isset($row['status']) && $row['status'] != '' ) {
                                $status = DelightFunctions::getDelightKitStatusById($row['status']);
                                $html = '<span id="status-'.$row['kit_number'].'">'.ucfirst(strtolower($status['kitstatus'])).'</span>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "CREATED_AT"){
                    array_push($select_arr,strtolower("CREATED_AT"));
                    $dt[$i] = array( 'db' => strtolower('CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = \Carbon\Carbon::parse($row['created_at'])->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "REQUEST_COMMENT"){
                    array_push($select_arr,strtolower("REQUEST_COMMENT"));
                    $dt[$i] = array( 'db' => strtolower('REQUEST_COMMENT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $request_comment =  $row['request_comment'];
                            $html = '';
                            if($request_comment != '')
                            {
                                $maxLength = 15;
                                $html .= '<div class="tooltip_text_trunk">';
                                if(strlen($request_comment) > $maxLength){
                                    $html .= '<span class="mytooltip">';
                                    $html .= '<span id="request_comment-'.$row['kit_number'].'" data-bs-toggle="tooltip" data-bs-placement="top" title="" title="'.$request_comment.'">';
                                    $html .= trim(substr($request_comment,0,$maxLength)).'...';
                                    $html .= '</span>';
                                    $html .= '</span>';
                                }else{
                                    $html .= '<span id="request_comment-'.$row['kit_number'].'">'.$request_comment.'</span>';
                                }
                                $html .= '</div>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "APPROVAL_COMMENT"){
                    array_push($select_arr,strtolower("APPROVAL_COMMENT"));
                    $dt[$i] = array( 'db' => strtolower('APPROVAL_COMMENT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $approval_comment =  $row['approval_comment'];
                            $html = '';
                            if($approval_comment != '')
                            {
                                $maxLength = 15;
                                $html .= '<div class="tooltip_text_trunk">';
                                if(strlen($approval_comment) > $maxLength){
                                    $html .= '<span class="mytooltip">';
                                        $html .= '<span id="approval_comment-'.$row['kit_number'].'" data-bs-toggle="tooltip" data-bs-placement="top" title="" title="'.$approval_comment.'">';
                                            $html .= trim(substr($approval_comment,0,$maxLength)).'...';
                                        $html .= '</span>';
                                    $html .= '</span>';
                                }else{
                                    $html .= '<span id="approval_comment-'.$row['kit_number'].'">'.$approval_comment.'</span>';
                                }
                                $html .= '</div>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr,strtolower("KIT_NUMBER AS ACTION"));
                    $dt[$i] = array( 'db' => strtolower('KIT_NUMBER AS ACTION'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $status_PA = [12,13,14];
                            $html = '';
                            if (in_array($row['status'], $status_PA)) {
                                $html =  '<input type="checkbox" style="opacity: 20!important" class="kit-checkbox" name="approval-checkbox" id="kitCheckbox-'.$row['kit_number'].'">';
                            }
                            return $html;
                        }
                    );
                }else{
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = $column;
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            //$dt_obj = new SSP('DELIGHT_KIT', $dt);

            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            $dt_obj = DB::table('DELIGHT_KIT')->select($select_arr);

            $status = [6,9,10,12,13,14];
            $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS',$status);
             // $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', $status)->orderColumn('DELIGHT_KIT.STATUS', 'DESC');

            if ($solId) {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.SOL_ID',$solId);
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

            if(isset($requestData['delightKitStatus'] ))
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
           // return response()->json($dt_obj->getDtArr());
            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);        }
    }

    public function seekapproval()
    {
        try{
            $delightSchemeCodes = DelightFunctions::getDelightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();

            $delightKitStatus = Arr::except($delightKitStatus, [11,7,8,3,4,5]);

            //returns tempalte
            return view('maker.seekapproval')
                                        ->with('delightSchemeCodes',$delightSchemeCodes)
                                        ->with('delightKitStatus',$delightKitStatus);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            // CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Maker/DashboardController','seekapproval',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
    public function requestApproval(Request $request)
    {
        try{
            $requestData = $request->get('data');
            //echo "<pre>";print_r($requestData['param']);exit;
            $updateRequestApproval = DB::table("DELIGHT_KIT")->where('KIT_NUMBER', $requestData['kit_number'])->update(['request_comment'=> $requestData['request_comment'],'CR_STATUS'=> $requestData['approval_status']]);

            if($updateRequestApproval) {
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Approval request submitted','data'=>[$requestData['kit_number'], $requestData['request_comment']]]);
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

    public function submitApproval(Request $request){
        try{
            $requestData = $request->get('data');
            $current_timestamp = Carbon::now()->timestamp;
			$timestamp = Carbon::parse($current_timestamp);

            if (isset($requestData['approval_kits'])) {
                $approvalKits = $requestData['approval_kits'];
                for ($i=0; $i < count($approvalKits); $i++){
                    $updateKitStatus = DB::table("DELIGHT_KIT")->where('KIT_NUMBER', $approvalKits[$i]['kitNumber'])
                        ->update(['STATUS'=>$approvalKits[$i]['status'], 'CR_APPROVED_ON'=> $timestamp]);      
                }

            }else{
                $updateKitStatus = DB::table("DELIGHT_KIT")->where('KIT_NUMBER', $requestData['kit_number'])
                    ->update(['approval_comment'=> $requestData['approval_comment'],
                        'STATUS'=> $requestData['status'],
                        'CR_APPROVED_ON'=> $timestamp]);
            }

            if($updateKitStatus) {
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Approved','data'=>[]]);
            }else{
                DB::rollback();
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

    
}
?>
