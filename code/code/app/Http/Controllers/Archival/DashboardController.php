<?php

namespace App\Http\Controllers\Archival;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
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

            if($this->roleId != 9){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Archival/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

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
        //Array to hold accounts count
        $accountsCount = array();
        //fetch savings and td accounts count
        $savingsTDaccountsCount = CommonFunctions::getAccountsCountByTypeAndStatus('savingsTD',[20]);
        //fetch savings accounts count
        $accountsCount['savings'] = CommonFunctions::getAccountsCountByTypeAndStatus('savingsAccount',[20])
                                        + $savingsTDaccountsCount;
        //fetch current accounts count
        $accountsCount['current'] = CommonFunctions::getAccountsCountByTypeAndStatus('currentAccount',[20]);
        //fetch fixed deposite accounts count
        $accountsCount['termDeposit'] = CommonFunctions::getAccountsCountByTypeAndStatus('termDeposit',[20])
                                        + $savingsTDaccountsCount;
        //fetch applciation status
        $applicationStatus = config('constants.APPLICATION_STATUS');
        //fetch user names from applcations
        $customerNames = CommonFunctions::getCustomerDetails();
        //returns tempalte
        return view('archival.dashboard')->with('accountsCount',$accountsCount)->with('customerNames',$customerNames)
                                    ->with('applicationStatus',$applicationStatus);
    }

    /*
    *  Method Name: userapplications
    *  Created By : Sharanya T
    *  Created At : 12-02-2020
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
    public function userapplications(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
            //build columns array
            $select_arr = [];
            $filteredColumns = ['ACCOUNT_DETAILS.AOF_NUMBER','ACCOUNT_DETAILS.CUSTOMER_ID','ACCOUNT_DETAILS.ACCOUNT_NO','USER_NAME','CUSTOMER_TYPE','ACCOUNT_TYPES.ACCOUNT_TYPE','ACCOUNT_DETAILS.CREATED_AT','ACCOUNT_DETAILS.APPLICATION_STATUS','REVIEWER_NAME','ACCOUNT_DETAILS.L1_COUNTER','ACCOUNT_DETAILS.L2_COUNTER','ACCOUNT_DETAILS.CREATED_BY','ACCOUNT_DETAILS.TD_ACCOUNT_NO','ACCOUNT_DETAILS.DELIGHT_SCHEME','ACCOUNT_DETAILS.IS_NEW_CUSTOMER', 'ACCOUNT_DETAILS.ID','ACCOUNT_DETAILS.FLOW_TYPE'];
            // echo "<pre>";print_r($filteredColumns);exit;
            // $archivalRecords = DB::table('ARCHIVAL_RECORDS')->pluck('aof_number')->toArray();
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "USER_NAME")
                {
                    $user_name = "COD.FIRST_NAME || ' ' || COD.MIDDLE_NAME || ' ' || COD.LAST_NAME AS user_name";
                    array_push($select_arr, DB::raw($user_name));
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->user_name;
                            return $html;
                        }
                    );
                }
                else if($column == 'ACCOUNT_DETAILS.AOF_NUMBER'){
                    array_push($select_arr, strtolower('ACCOUNT_DETAILS.AOF_NUMBER'));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.AOF_NUMBER'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = '<a href="javascript:void(0)" data-screen="dashboard" id="'.$row['id'].'" class="archival" data-aof_number=' . $row['aof_number'].'>' . $row['aof_number'].'</a>';
                            return $html;
                        }
                    );
                }
                else if($column == "ACCOUNT_DETAILS.CUSTOMER_ID"){
                    array_push($select_arr, strtolower('ACCOUNT_DETAILS.CUSTOMER_ID'));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.CUSTOMER_ID'),'dt' => $i,
                        'formatter' => function( $d, $row)  {
                            // echo "<pre>";print_r($row);exit;
                            $custAccnt = DB::table('CUSTOMER_OVD_DETAILS')
                                    ->where('APPLICANT_SEQUENCE', 1)
                                    ->where('FORM_ID', $row->id)
                                    ->get()->toArray();
                            $custAccnt = (array) current($custAccnt);
                            // echo "<pre>";print_r($custAccnt);exit;
                            $is_new_customer = $row->is_new_customer;
                            if ($is_new_customer == 0) { //ETB
                               $html = $row->customer_id;
                            }else { //NTB
                                $html = $custAccnt['customer_id'];
                            }

                            return $html;
                        }
                    );
                }
                else if($column == "ACCOUNT_DETAILS.ACCOUNT_NO"){
                    array_push($select_arr, strtolower('ACCOUNT_DETAILS.ACCOUNT_NO'));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.ACCOUNT_NO'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                 
                            $row = (array) $row;
                            // $account_type = ucfirst(config('constants.ACCOUNT_TYPES.'.$row['account_type']));
                            $account_type = $row['account_type'];
                            if ( $account_type == 'Savings') {
                                $html = $row['account_no'];
                            }elseif($account_type == 'Current'){
                                $html = $row['account_no'];
                            }else{
                                $html = $row['td_account_no'];
                            }
                            return $html;
                        }
                    );
                }else if($column == "CUSTOMER_TYPE"){
                    // echo "test";exit;
                    array_push($select_arr,DB::raw('ACCOUNT_DETAILS.FLOW_TYPE as customer_type'));
                    $dt[$i] = array( 'db' => DB::raw('ACCOUNT_DETAILS.FLOW_TYPE as customer_type'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) ($row);
                            $html = $row['flow_type'];
                            return $html;
                            // echo"<pre>"; print_r($html); exit;
                        }
                    );
                }else if($column == "ACCOUNT_TYPES.ACCOUNT_TYPE"){
                    array_push($select_arr, strtolower('ACCOUNT_TYPES.ACCOUNT_TYPE'));
                   
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_TYPES.ACCOUNT_TYPE'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = $row['account_type'];
                            return $html;
                        }
                    );
                }else if($column == "ACCOUNT_DETAILS.CREATED_AT"){
                    array_push($select_arr, strtolower('ACCOUNT_DETAILS.CREATED_AT'));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "ACCOUNT_DETAILS.APPLICATION_STATUS"){
                    array_push($select_arr, strtolower('ACCOUNT_DETAILS.APPLICATION_STATUS'));
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.APPLICATION_STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            if($row->application_status == 2)
                            {
                                $html = config('constants.APPLICATION_STATUS.'.$row->application_status).'-'.$row->l1_counter;
                            }elseif($row->application_status == 9){
                                $html = config('constants.APPLICATION_STATUS.'.$row->application_status).'-'.$row->l2_counter;
                            }else{
                                $html = config('constants.APPLICATION_STATUS.'.$row->application_status);
                            }
                            return $html;
                        }
                    );
                }
                else if($column == "REVIEWER_NAME")
                {
                    $emp_name = "USERS.EMP_FIRST_NAME || ' ' || USERS.EMP_MIDDLE_NAME || ' ' || USERS.EMP_LAST_NAME AS emp_name";
                    array_push($select_arr, DB::raw($emp_name));
                    $dt[$i] = array( 'db' => DB::raw($emp_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->emp_name;
                            return $html;
                        }
                    );
                }
                else
                {
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('ARCHIVAL_RECORDS','ARCHIVAL_RECORDS.AOF_NUMBER','ACCOUNT_DETAILS.AOF_NUMBER');
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD','COD.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY');
            $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.UPDATED_BY');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE');
            $dt_obj = $dt_obj->whereRaw('ACCOUNT_DETAILS.AOF_NUMBER NOT IN (SELECT ARCHIVAL_RECORDS.AOF_NUMBER FROM ARCHIVAL_RECORDS)');

            // $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_BY',Session::get('userId'));
            //checks customer name is empty or not
            if($requestData['customer'] != '')
            {
                $dt_obj = $dt_obj->where('COD.ID',$requestData['customer']);
            }
            //checks applciation status is empty or not
            if($requestData['status'] != '')
            {
                $dt_obj = $dt_obj->where('APPLICATION_STATUS',$requestData['status']);
            }
            //checks customer type is empty or not
            if(isset($requestData['customer_type']))
            {
                if($requestData['customer_type'] == "0")
                {
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.IS_NEW_CUSTOMER',0);

                }else if($requestData['customer_type'] == "1")
                {
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.IS_NEW_CUSTOMER',1);
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.DELIGHT_SCHEME', null);

                }else if ($requestData['customer_type'] == "2") {
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.DELIGHT_SCHEME', '!=', null);
                }
            }

            //checks sent date is empty or not
            if($requestData['startDate'] != '')
            {
                $dt_obj = $dt_obj
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.APPLICATION_STATUS','=',20);
            $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->orderBy('ACCOUNT_DETAILS.ID','DESC');

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);

            //return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }
    }

}
?>
