<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
use Crypt;

class CreateBatchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        if (Cookie::get('token') != '') {
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'), false);
            //get claims from token
            $encoded = explode('.', $this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded), true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if ($this->roleId != 2) {
                $isAutherized = false;
            } else {
                $isAutherized = true;
            }
            if (!$isAutherized) {
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Bank/CreateBatchController', 'CreateBatchController', 'Unauthorized attempt detected by ' . $this->userId, '', '', '1');

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
    public function createbatch()
    {
        //fetch user names from applcations
        $customerNames = CommonFunctions::getCustomerDetails();

        $courier = CommonFunctions::getcourier();
        //returns tempalte
        return view('bank.dispatch')->with('courier', $courier)->with('customerNames', $customerNames);
    }

    public function editairwaybillno()
    {
        $courier = CommonFunctions::getcourier();
        if (count($courier) > 0) {
            return json_encode(['status' => 'success', 'msg' => 'Airway Bill Number Updated Successfully', 'data' => $courier]);
        } else {
            return json_encode(['status' => 'fail', 'msg' => 'No records found.', 'data' => []]);
        }
    }

    /*
     *  Method Name: dispatchapplications
     *  Created By : Sharanya T
     *  Created At : 30-04-2020
     *
     *  Description:
     *  Method to fetch user applications based on customer , customer type , status and date
     *
     *  Params:
     *  @$customer,@$customerType,@$status,@startDate
     *
     *  Output:
     *  Returns Json.
     */
    public function dispatchapplications(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');

            $select_arr = [];

            //build columns array
            $filteredColumns = ['ID', 'ACCOUNT_DETAILS.AOF_NUMBER', 'USER_NAME', 'ACCOUNT_TYPE', 'CREATED_AT', 'APPLICATION_STATUS', 'CUSTOMER_TYPE', 'ACTION', 'ACCOUNT_DETAILS.CREATED_BY','CONSTITUTION'];
            $i = 0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if ($column == 'ID') {
                    array_push($select_arr, 'ACCOUNT_DETAILS.CREATED_AT AS checkbox_id'); 
                    $dt[$i] = array('db' => 'checkbox_id','dt' => $i,
                        'formatter' => function ($d, $row) {
                            $html = '';
                            $html .= '<label class="checkbox m-0">';
                            $html .= '<input type="checkbox" class="dispatchapplications" name="name" id="'.$row->{'action_id'}.'" value="'.$row->{'action_id'}.'">';
                            $html .= '<span class="lbl m-0"></span>';
                            $html .= '</label>';
                            return $html;
                        },
                    );
                }elseif ($column == 'USER_NAME') {
                    $user_name = "COD.FIRST_NAME || ' ' || COD.MIDDLE_NAME || ' ' || COD.LAST_NAME AS user_name";
                    array_push($select_arr, DB::raw($user_name));
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function ($d, $row) {
                            $html = $row->user_name;
                            return $html;
                        },
                    );
                }else if($column == "ACCOUNT_TYPE"){
                    array_push($select_arr,strtolower("ACCOUNT_TYPES.ACCOUNT_TYPE"));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_TYPES.ACCOUNT_TYPE'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $row = (array) $row;
                           $html = '';
                           if($row['constitution'] == 'NON_IND_HUF'){
                            $html .= 'HUF ';
                        }
                            $html .= $row['account_type'];
                            return $html;
                        }
                    );
                }elseif ($column == 'CREATED_AT') {
                     array_push($select_arr, 'ACCOUNT_DETAILS.CREATED_AT AS createdat'); 
                     $dt[$i] = array('db' => 'createdat', 'dt' => $i,
                        'formatter' => function ($d, $row) {
                            return \Carbon\Carbon::parse($d)->format('d-m-Y'); 
                        },
                     );
                }elseif ($column == 'CUSTOMER_TYPE') {
                    array_push($select_arr, strtolower('ACCOUNT_DETAILS.FLOW_TYPE'));
                    $dt[$i] = array('db' => strtolower('ACCOUNT_DETAILS.FLOW_TYPE'),'dt' => $i,
                        'formatter' => function ($d, $row) {
                            // if ($row->customer_id == '') {
                            //     $html = 'NTB';
                            // } else {
                            //     $html = 'ETB';
                            // }
                            $html = $row->flow_type;
                            return $html;
                        },
                    );
                } elseif ($column == 'APPLICATION_STATUS') {
                array_push($select_arr, strtolower("ACCOUNT_DETAILS.APPLICATION_STATUS"));
                    $dt[$i] = array('db' => strtolower("ACCOUNT_DETAILS.APPLICATION_STATUS"),'dt' => $i,
                        'formatter' => function ($d, $row) {
                            $html = config('constants.APPLICATION_STATUS.' . $row->application_status);
                            return $html;
                        },
                    );
                }elseif ($column == 'ACTION') {
                    array_push($select_arr, 'ACCOUNT_DETAILS.ID AS action_id'); 
                    $dt[$i] = array ('db' => 'action_id','dt' => $i,
                        'formatter' => function ($d, $row) {
                            $html = '<a href="javascript:void(0)" id="' . $d . '" class="bankReview">Edit</a>';
                            return $html;
                        },
                    );
                }else {
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            // dd($select_arr);
            // $dt_obj = new SSP('ACCOUNT_DETAILS', $dt); Code commented during laravel version upgrade
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD', 'COD.FORM_ID', 'ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE');
            $userDetail = DB::table('USERS')->select('empsol')->where('ID', Session::get('userId'))->get()->toArray();
            $userDetail = current($userDetail);

            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.BRANCH_ID', $userDetail->empsol);
            //checks customer name is empty or not
            if ($requestData['customer'] != '') {
                $dt_obj = $dt_obj->where('COD.ID', $requestData['customer']);
            }
            //checks customer type is empty or not
            if ($requestData['customer_type'] != '') {
                if ($requestData['customer_type'] == '1') {
                    $dt_obj = $dt_obj->whereNotNull('CUSTOMER_ID');
                } elseif ($requestData['customer_type'] == '2') {
                    $dt_obj = $dt_obj->whereNull('CUSTOMER_ID');
                }
            }
            //checks sent date is empty or not
            if ($requestData['startDate'] != '') {
                $dt_obj = $dt_obj->whereRaw("to_char(ACCOUNT_DETAILS.CREATED_AT,'DD-MM-YYYY')>='" . $requestData['startDate'] . "'")->whereRaw("to_char(ACCOUNT_DETAILS.CREATED_AT,'DD-MM-YYYY')<='" . $requestData['endDate'] . "'");
            } else {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_AT', '>=', Carbon::now()->subMonths(3));
            }
            $dt_obj = $dt_obj
                ->where('APPLICANT_SEQUENCE', 1)
                ->where('DISPATCH_STATUS', 0)
                ->where('PHYSICAL_FORM_STATUS', 3)
                // ->orWhere(function($query) {
                //     // $query->whereNull('query_id')
                //     $query->whereNotIn('APPLICATION_STATUS',[1,2,4,5,6,7,8]);
                //  })
                ->whereNull('BATCH_ID')
                ->orderBy('ACCOUNT_DETAILS.ID', 'DESC');
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);
            // return response()->json($dt_obj->getDtArr());
        } catch (\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
            }
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
    }

    public function createbatchid(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from request
                $requestData = $request->get('data');
                $sequnceNumber = DB::select('select BATCH_SEQUENCE.nextval from dual');
                $sequnceNumber = (array) current($sequnceNumber);
                $sequnceLength = strlen($sequnceNumber['nextval']);
                $year = substr(Carbon::now()->year, 2);
                $branchId = 001;
                if ($sequnceLength != 6) {
                    $batchId = str_pad($sequnceNumber['nextval'], 6, '0', STR_PAD_LEFT);
                } else {
                    $batchId = $sequnceNumber['nextval'];
                }
                $batchId = $year . $branchId . $batchId;
                if ($batchId) {
                    // return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>['batchId',$batchId]]);
                    return json_encode(['status' => 'success', 'msg' => 'Response Updated Successfully', 'data' => $batchId]);
                } else {
                    return json_encode(['status' => 'fail', 'msg' => 'No records found.', 'data' => []]);
                }
                exit();
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

    public function savebatch(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from request
                $requestData = $request->get('data');
                $formIds = explode(',', $requestData['accountIds']);
                //Begins db transaction
                DB::beginTransaction();
                $batch = ['BATCH_ID' => $requestData['batch_id'], 'AIRWAY_BILL_NO' => $requestData['airwaybill_number'], 'COURIER_ID' => $requestData['courier'], 'NO_OF_FORMS' => count($formIds)];

                $batchId = DB::table('BATCH')->insertGetId($batch);
                $updateBatch = DB::table('ACCOUNT_DETAILS')
                    ->whereIn('ID', $formIds)
                    ->update(['BATCH_ID' => $batchId, 'PHYSICAL_FORM_STATUS' => 6]);
                if (count($formIds) > 0) {
                    foreach ($formIds as $formId) {
                        $saveStatus = CommonFunctions::saveStatusDetails($formId, 11);
                        // $updateApplicationStatus = CommonFunctions::updateApplicationStatus('DISPATCH',$formId);
                    }
                }
                if ($updateBatch) {
                    //commit database if response is true
                    DB::commit();
                    // return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>['batchId',$batchId]]);
                    return json_encode(['status' => 'success', 'msg' => 'Response Updated Successfully', 'data' => $batchId]);
                } else {
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status' => 'fail', 'msg' => 'No records found.', 'data' => []]);
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

    public function updatedairwaybill(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from request
                $requestData = $request->get('data');
                // echo "<pre>";print_r($requestData);exit;
                DB::beginTransaction();
                $airwayBillDetails = ['AIRWAY_BILL_NO' => $requestData['airwaybill_no'], 'COURIER_ID' => $requestData['courierData']];

                $updatedairwaybill = DB::table('BATCH')->whereId($requestData['batch_id'])->update($airwayBillDetails);
                $updateStatus = DB::table('ACCOUNT_DETAILS')
                    ->where('BATCH_ID', $requestData['batch_id'])
                    ->update(['PHYSICAL_FORM_STATUS' => 7, 'DISPATCH_STATUS' => 1]);
                $formIds = DB::table('ACCOUNT_DETAILS')->where('BATCH_ID', $requestData['batch_id'])->pluck('id')->toArray();
                if (count($formIds) > 0) {
                    foreach ($formIds as $formId) {
                        $saveStatus = CommonFunctions::saveStatusDetails($formId, 12);
                        $updateApplicationStatus = CommonFunctions::updateApplicationStatus('DISPATCH', $formId);
                    }
                }
                if ($updateStatus) {
                    DB::commit();
                    return json_encode(['status' => 'success', 'msg' => 'Response Updated Successfully', 'data' => []]);
                } else {
                    DB::rollback();
                    return json_encode(['status' => 'fail', 'msg' => 'No records found.', 'data' => []]);
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

    public function addairwaybillno()
    {
        try {
            //returns tempalte
            return view('bank.addairwaybillno');
        } catch (\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
            }
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Bank/CreateBatchController', 'addairwaybillno', $eMessage);
        }
    }

   
      public function batchlist(Request $request)
    {
        try{
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr = [];
            $accountIds = DB::table('ACCOUNT_DETAILS')->select('ID','BATCH_ID')
                                                    ->where('BRANCH_ID',Session::get('branchId'))
                                                    ->where('CREATED_AT','>=',Carbon::now()->subMonths(3))
                                                        ->groupBy('id','batch_id')
                                                        ->pluck('id','batch_id')->toArray();
            //build columns array
            $filteredColumns = ['BATCH.ID','BATCH.BATCH_ID','BATCH.AIRWAY_BILL_NO','COURIER_LIST.COURIER_COMPANY','BATCH.CREATED_AT','PRINT','ACTION','COURIER_LIST.COURIER_ID','BATCH.CREATED_BY','ACCOUNT_DETAILS.DISPATCH_STATUS'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "BATCH.AIRWAY_BILL_NO")
                {  
                    array_push($select_arr, strtolower('BATCH.AIRWAY_BILL_NO'));
                    $dt[$i] = array( 'db' => strtolower('BATCH.AIRWAY_BILL_NO'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            $html .= '<div id="airwayBillnoDiv_'.$row->id.'">';
                            $html .= $row->airway_bill_no;
                            $html .= '</div>';                   
                            return $html;
                        }
                    );
                }else if($column == "BATCH.CREATED_AT"){
                    array_push($select_arr, strtolower('BATCH.CREATED_AT'));
                    $dt[$i] = array( 'db' => strtolower('BATCH.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if ($column == "PRINT") {
                    array_push($select_arr, 'BATCH.ID AS print_id');  
    
                    $dt[$i] = array('db' => 'print_id', 'dt' => $i,
                        'formatter' => function($d, $row) {
                            $html = '';
                            $html .= '<a href="javascript:void(0)" id="' . $d . '" class="printeairBatch" data-toggle="modal" data-target="#print-batch">
                            <i class="fa fa-print" aria-hidden="true"></i></a>'; 
                            return $html;
                        }
                    );
                }
               else if($column == "ACTION"){
                  array_push($select_arr, 'BATCH.BATCH_ID AS action_id');  
                    $dt[$i] = array( 'db' => 'action_id','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            if($row['dispatch_status'])
                            {
                                $html = '<a>Dispatch</a>';
                            }else{
                                $html = '<a href="javascript:void(0)" id="'.$row['id'].'" data-courier="'.$row['courier_id'].'" 
                                            data-toggle="modal" data-target="#editairwaybillno" class="editairwaybill" >Edit</a>';
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
            // $dt_obj = new SSP('BATCH', $dt);
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('BATCH')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('COURIER_LIST','COURIER_LIST.COURIER_ID','BATCH.COURIER_ID');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.BATCH_ID','BATCH.ID');
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(3));
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.BRANCH_ID',Session::get('branchId'));
            $dt_obj = $dt_obj->whereIn('ACCOUNT_DETAILS.ID',$accountIds);
            $dt_obj = $dt_obj->where('PHYSICAL_FORM_STATUS', 6);
            
            // $dt_obj = $dt_obj->orderBy(0, 'desc');
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
   
    public function printairbatchid(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from request
                $requestData = $request->get('data');

                $airwayBillnoDetails = DB::table('ACCOUNT_DETAILS')
                    ->select('ACCOUNT_DETAILS.AOF_NUMBER', DB::raw("COD.FIRST_NAME || ' ' || COD.MIDDLE_NAME || ' ' || COD.LAST_NAME AS user_name"))
                    ->leftjoin('CUSTOMER_OVD_DETAILS as COD', 'COD.FORM_ID', 'ACCOUNT_DETAILS.ID')
                    ->where(['BATCH_ID' => $requestData['batchId'], 'APPLICANT_SEQUENCE' => 1])
                    ->get()
                    ->toArray();
                //->pluck('aof_number','user_name'

                if (count($airwayBillnoDetails) > 0) {
                    //commit database if response is true
                    // DB::commit();
                    return json_encode(['status' => 'success', 'msg' => 'Airway Bill Number Updated Successfully', 'data' => $airwayBillnoDetails]);
                } else {
                    //rollback db transactions if any error occurs in query
                    // DB::rollback();
                    return json_encode(['status' => 'fail', 'msg' => 'No records found.', 'data' => []]);
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

    public function saveairwaybillno(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from request
                $requestData = $request->get('data');
                //Begins db transaction
                DB::beginTransaction();
                $saveAirwayBillno = DB::table('BATCH')
                    ->whereId($requestData['id'])
                    ->update(['AIRWAY_BILL_NO' => $requestData['airway_bill_no']]);
                if ($saveAirwayBillno) {
                    //commit database if response is true
                    DB::commit();
                    // return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>['batchId',$batchId]]);
                    return json_encode(['status' => 'success', 'msg' => 'Airway Bill Number Updated Successfully', 'data' => $requestData['airway_bill_no']]);
                } else {
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status' => 'fail', 'msg' => 'No records found.', 'data' => []]);
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
}
?>
