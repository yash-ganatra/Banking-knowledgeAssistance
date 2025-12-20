<?php

namespace App\Http\Controllers\Inward;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Carbon\Carbon;
use Session,Cookie;
use DB;
use Crypt;

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

            if($this->roleId != 7){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Inward/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

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
		$applicationStatus = config('constants.APPLICATION_STATUS');
        $courierList = DB::table('COURIER_LIST')->pluck('courier_company','courier_id')->toArray();
        return view('inward.dashboard')->with('courierList',$courierList);
    }

    public function batchapplications(Request $request)
    {
        try{
            $requestData = $request->get('data');
             $select_arr = [];
            $filteredColumns = ['BATCH.ID','BATCHNO','BATCH.AIRWAY_BILL_NO','COURIER_LIST.COURIER_COMPANY',
                                                'BATCH.CREATED_AT','BATCH.NO_OF_FORMS','ACTION','ACCOUNT_DETAILS.ID','BATCH.BATCH_ID'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "AIRWAY_BILL_NO")
                {
                     array_push($select_arr, 'AIRWAY_BILL_NO');
                    $dt[$i] = array( 'db' => 'AIRWAY_BILL_NO','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            $html .= '<div id="airwayBillnoDiv_'.$row->id.'">';
                                $html .= $row->airway_bill_no;
                            $html .= '</div>';                      
                            return $html;
                        }
                    );
                }else if($column == "CREATED_AT"){
                     array_push($select_arr, 'CREATED_AT');
                    $dt[$i] = array( 'db' => 'CREATED_AT','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "BATCHNO"){
                     array_push($select_arr, strtolower("BATCH.BATCH_ID AS bach"));
                    $dt[$i] = array( 'db' => strtolower("BATCH.BATCH_ID AS bach"),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $html = '';
                                $html .= $row->batch_id;                  
                            return $html;
                        }
                    );
                }
                else if($column == "ACTION"){
                       array_push($select_arr,strtolower("BATCH.BATCH_ID AS action"));
                    $dt[$i] = array( 'db' =>strtolower ('action'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="inward" >
                                        Inward
                                    </a>';                     
                            return $html;
                        }
                    );
                }
                else if($column == "NO_OF_FORMS"){
                     array_push($select_arr, 'NO_OF_FORMS');
                    $dt[$i] = array( 'db' => 'NO_OF_FORMS','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;                    
                            $html = $row['count(account_details.id)'];                     
                            return $html;
                        }
                    );
                }else if($column == "ACCOUNT_DETAILS.ID"){
                    $countRaw = DB::raw('COUNT(ACCOUNT_DETAILS.ID) as total_count');
                    array_push($select_arr, $countRaw);
                    $dt[$i] = array('db' => 'total_count', 'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="inward" >
                                        Inward
                                    </a>';                     
                            return $html;
                        }
                    );
                }
                else{
                   array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('BATCH')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('COURIER_LIST','COURIER_LIST.COURIER_ID','BATCH.COURIER_ID');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.BATCH_ID','BATCH.ID');
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.DISPATCH_STATUS',1);
            $dt_obj = $dt_obj->where('physical_form_status', 7);

            if(isset($requestData['batch_no']))
            {
                $dt_obj = $dt_obj->whereRaw("BATCH.BATCH_ID LIKE '%".$requestData['batch_no']."%'");    
            }

            if(isset($requestData['airway_bill_no']))
            {
                $dt_obj = $dt_obj->whereRaw("BATCH.AIRWAY_BILL_NO LIKE '%".$requestData['airway_bill_no']."%'");    
            }

            if(isset($requestData['courier']))
            {
                $dt_obj = $dt_obj->where("BATCH.COURIER_ID",'=',$requestData['courier']);
            }

            if($requestData['startDate'] != '')
            {
                $dt_obj = $dt_obj
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }

            $dt_obj = $dt_obj->groupBy(['BATCH.ID','BATCH.BATCH_ID','BATCH.AIRWAY_BILL_NO',
                                            'COURIER_LIST.COURIER_COMPANY','BATCH.CREATED_AT','BATCH.NO_OF_FORMS'])
                            ->orderBy('BATCH.ID', 'desc');

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);

            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateinward(Request $request)
    {
        try{
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $batchId = base64_decode($decodedString);
            return view('inward.inwardform')->with('batchId',$batchId);
            // echo "<pre>";print_r($params);exit;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function batchformapplications(Request $request)
    {
        try{
            $requestData = $request->get('data');
             $select_arr = [];
            $filteredColumns = ['BATCH.ID','BATCH.BATCH_ID','BATCH.AIRWAY_BILL_NO','BATCH.RECEIVED_AIRWAY_BILL_NO','COURIER_LIST.COURIER_COMPANY',
                                                'ACCOUNT_DETAILS.AOF_NUMBER','ACCOUNT_DETAILS.PHYSICAL_FORM_STATUS'];
            $i=0;
            foreach ($filteredColumns as $column) {
                
                if($column == "ACCOUNT_DETAILS.PHYSICAL_FORM_STATUS"){
                    array_push($select_arr,strtolower( 'ACCOUNT_DETAILS.PHYSICAL_FORM_STATUS'));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.PHYSICAL_FORM_STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            // echo"<pre>";print_r($row);exit;
                            if($row['physical_form_status'] == 7)
                            {
                                $html = "Pending";
                            }else{
                                $html = "Received";                                
                            }
                            // $html = config('constants.APPLICATION_STATUS.'.$row['account_details.application_status']);
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
            // $dt_obj = new SSP('BATCH', $dt);
             $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('BATCH')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.BATCH_ID','BATCH.ID');
            $dt_obj = $dt_obj->leftjoin('COURIER_LIST','COURIER_LIST.COURIER_ID','BATCH.COURIER_ID');
            $dt_obj = $dt_obj->where('BATCH.ID',$requestData['batchId']);
            
            $dt_obj = $dt_obj->orderBy('BATCH.ID', 'desc');
            // $dt_obj = $dt_obj->order(0, 'desc');

            // return response()->json($dt_obj->getDtArr());

              $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateinwardstatus(Request $request)
    {
        try{
            if ($request->ajax())
            {
                //fetch data from request
                $requestData = $request->get('data');
                // echo "<pre>";print_r($requestData);exit;
                //Begins db transaction
                DB::beginTransaction();
                $updateinwardstatus = DB::table('ACCOUNT_DETAILS')->where('AOF_NUMBER',$requestData['aof_number'])
                                                                ->update(['INWARD_REVIEW'=>1,'PHYSICAL_FORM_STATUS'=>8]);
                $formId = DB::table('ACCOUNT_DETAILS')->where('AOF_NUMBER',$requestData['aof_number'])
                                                                        ->pluck('id')->toArray();
                $formId = current($formId);
                $saveStatus = CommonFunctions::saveStatusDetails($formId,13);
                $updateApplicationStatus = CommonFunctions::updateApplicationStatus('COURIER',$formId);
                if($updateinwardstatus)
                {
                    //commit database if response is true
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Inward Status Updated.','data'=>[]]);
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

    public function saveairwaybillno(Request $request)
    {
        try{
            if ($request->ajax())
            {
                //fetch data from request
                $requestData = $request->get('data');
                $updateairwaybill = DB::table('BATCH')->whereId($requestData['batchId'])
                                                        ->update(['RECEIVED_AIRWAY_BILL_NO'=>$requestData['airway_bill_no']]);
                if($updateairwaybill)
                {
                    return json_encode(['status'=>'success','msg'=>'Recived Airway bill no saved.','data'=>[]]);
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

    public function batchapplications_backup(Request $request)
    {
        try{
            //fetch data from request
            $requestData = $request->get('data');
             $select_arr = [];
            //build columns array
            $filteredColumns = ['BATCH.ID','BATCH.BATCH_ID','BATCH.AIRWAY_BILL_NO','COURIER_LIST.COURIER_COMPANY',
                                                                        'BATCH.CREATED_AT','BATCH.NO_OF_FORMS','ACTION'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "AIRWAY_BILL_NO")
                {  array_push($select_arr, 'AIRWAY_BILL_NO');
                    $dt[$i] = array( 'db' => 'AIRWAY_BILL_NO','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            $html .= '<div id="airwayBillnoDiv_'.$row->id.'">';
                                $html .= $row->airway_bill_no;
                            $html .= '</div>';                      
                            return $html;
                        }
                    );
                }else if($column == "CREATED_AT"){
                      array_push($select_arr, 'CREATED_AT');
                    $dt[$i] = array( 'db' => 'CREATED_AT','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                       array_push($select_arr, strtolower('BATCH.BATCH_ID'));
                    $dt[$i] = array( 'db' => strtolower('BATCH.BATCH_ID'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="inward" >
                                        Inward
                                    </a>';                     
                            return $html;
                        }
                    );
                }
                else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            // $dt_obj = new SSP('BATCH', $dt);
             $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('BATCH')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('COURIER_LIST','COURIER_LIST.COURIER_ID','BATCH.COURIER_ID');
            
            $dt_obj = $dt_obj->orderBy('BATCH.ID', 'desc');

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);
            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

}
?>