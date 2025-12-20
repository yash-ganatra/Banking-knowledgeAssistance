<?php

namespace App\Http\Controllers\Callcenter;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
use Crypt;

class CallCenterDashboardController extends Controller
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

            if(!in_array($this->roleId,[2,11])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Callcenter/CallCenterDashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

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
        try{
            
            //fetch applciation status
            $applicationStatus = config('constants.APPLICATION_STATUS');
            $applicationStatus = Arr::except($applicationStatus,[15,16,17,18,19,20,21,22,23]);
            //echo "<pre>";print_r($applicationSta);exit;

            //fetch user names from applcations
            $customerNames = CommonFunctions::getCustomerDetailsforCallCenter();

            //returns tempalte
            return view('callcenter.dashboard')->with('customerNames',$customerNames)
                                               ->with('applicationStatus',$applicationStatus);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Callcenter/CallCenterDashboardController','dashboard',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
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
    public function callcenteruserapplications(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr = [];
            $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
            //echo "<pre>";print_r($userDetails);exit;
            //build columns array
            $filteredColumns = ['ACCOUNT_DETAILS.AOF_NUMBER','USER_NAME','CUSTOMER_TYPE','ACCOUNT_DETAILS.ACCOUNT_TYPE','ACCOUNT_DETAILS.CREATED_AT','ACCOUNT_DETAILS.APPLICATION_STATUS', 'REVIEWER_NAME','ACTION','ACCOUNT_DETAILS.L1_COUNTER','ACCOUNT_DETAILS.L2_COUNTER','ACCOUNT_DETAILS.CREATED_BY','ACCOUNT_DETAILS.IS_ACTIVE','ACCOUNT_DETAILS.DELIGHT_SCHEME'];
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
                }else if($column == "CUSTOMER_TYPE"){
                    array_push($select_arr, 'ACCOUNT_DETAILS.IS_NEW_CUSTOMER');
                    $dt[$i] = array( 'db' => 'ACCOUNT_DETAILS.IS_NEW_CUSTOMER','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) ($row);

                            if ($row['delight_scheme'] != '' && $row['account_types.account_type'] == 'Savings') {
                                $html = 'DELIGHT';
                            }else if($row['is_new_customer'] == 1){
                                 $html = 'NTB';
                            }else{
                                $html = 'ETB';
                            }
                                                    
                            return $html;
                        }
                    );
                }else if($column == "ACCOUNT_TYPE"){
                    array_push($select_arr, 'ACCOUNT_TYPES.ACCOUNT_TYPE');
                    $dt[$i] = array( 'db' => 'ACCOUNT_TYPES.ACCOUNT_TYPE','dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $row = (array) $row;
                                                 
                            $html = $row['account_types.account_type'];
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
                }else if($column == "APPLICATION_STATUS"){
                    array_push($select_arr, 'APPLICATION_STATUS');
                    $dt[$i] = array( 'db' => 'APPLICATION_STATUS','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            if($row->is_active == 0)
                            {
                                $html = "In-Progress";
                            }else{
                                if($row->application_status == 2)
                                {
                                    $html = config('constants.APPLICATION_STATUS.'.$row->application_status).'-'.$row->l1_counter;          
                                }elseif($row->application_status == 9){
                                    $html = config('constants.APPLICATION_STATUS.'.$row->application_status).'-'.$row->l2_counter;                     
                                }else{
                                    $html = config('constants.APPLICATION_STATUS.'.$row->application_status);
                                }
                            }                            
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr, 'ACCOUNT_DETAILS.ID');
                    $dt[$i] = array( 'db' => 'ACCOUNT_DETAILS.ID','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if($row->application_status == '3'){
                                $url = 'createbatch';
                                $html = '<a href="'.route($url).'" id="'.$row->id.'" >Dispatch</a>'; 
                            }else if(($row->application_status == '22') || ($row->application_status == '23') && (Session::get('role') == 8)){
                                $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="bankReview">Update</a>';                              
                            }else if(Session::get('role') == 13){
                                $url = route('aoftracking').'/'.$row->aof_number;                              
                                $html = '<a href="'.$url.'" id="'.$row->id.'" >View</a>';                               
                            }
                            else if((Session::get('role') == 2) || (Session::get('role') == 11)){
                                // echo $row->application_status;exit;
                                if($row->is_active == 0){
                                    //$html = '<a href="javascript:void(0)" status="In-Progress" id="'.$row->id.'" class="bankReview">Edit</a>';    
                                   $html = '';                          
                                }else if(($row->application_status == 1) || ($row->application_status == 10)){
                                    //$html = '<a href="javascript:void(0)" id="'.$row->id.'" class="bankReview">Update</a>';
                                   $html = '';                          

                                }
                                else{
                                    $html = '';
                          /*          $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="bankReview">View</a>';*/
                                }
                            }else{
                         /*       $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="bankReview">View</a>';*/
                            }
                            return $html;
                        }
                    );
                }else if($column == "REVIEWER_NAME"){
                    $emp_name = "USERS.EMP_FIRST_NAME || ' ' || USERS.EMP_MIDDLE_NAME || ' ' || USERS.EMP_LAST_NAME AS emp_name";
                    array_push($select_arr, DB::raw($emp_name));
                    $dt[$i] = array( 'db' => DB::raw($emp_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->emp_name;
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
            //$dt_obj = new SSP('ACCOUNT_DETAILS', $dt); Commented line during version upgrade
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD','COD.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE');
            // $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_BY',Session::get('userId'));
            if((Session::get('role') != 8) && (Session::get('role') != 13)){
                 if(Session::get('role') == 14){
                    
                    if(!isset($userDetails['filter_type'])){

                        $f_type = '';
                        $f_id = '';

                    }else{

                        $f_type = $userDetails['filter_type'];
                        $f_id = $userDetails['filter_ids'];
                    }

                    $dt_obj = $dt_obj->leftjoin('BRANCH','BRANCH.BRANCH_ID','ACCOUNT_DETAILS.BRANCH_ID');

                    switch ($f_type){

                        case '1':
                            $dt_obj = $dt_obj->where('BRANCH.REGION_ID',$f_id);
                            break;

                        case '2':
                            $dt_obj = $dt_obj->where('BRANCH.ZONE_ID',$f_id);
                            break;

                        case '3':
                            $dt_obj = $dt_obj->where('BRANCH.CLUSTER_ID',$f_id);
                            break;
                        
                        default:
                            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.BRANCH_ID',Session::get('branchId'));
                            break;
                    }
                    
                 }else{

                   $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.BRANCH_ID',Session::get('branchId'));
                 }
            }

            //checks customer name is empty or not
            if($requestData['customer'] != '')
            {
                $dt_obj = $dt_obj->where('COD.ID',$requestData['customer']);
            }

            if(isset($requestData['AOF_NUMBER']))
            {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER', 'like', '%'.$requestData['AOF_NUMBER'].'%');
            }
           
            $dt_obj = $dt_obj->whereIn('APPLICATION_STATUS',[1,2,3,4,5,6,7,8,9,10,11,12,13,14]);               
            //checks customer type is empty or not
            if(isset($requestData['customer_type']))
            {
                if($requestData['customer_type'] == "0")
                {
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.IS_NEW_CUSTOMER',0);

                }
            }			
			//validations for call centre dashboard 
            if(Session::get('role') == 11)
            {  
                $dt_obj = $dt_obj->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE',[3]);
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.IS_NEW_CUSTOMER',0);
            }


            //checks sent date is empty or not
            if($requestData['startDate'] != '')
            {
                $dt_obj = $dt_obj
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }

            $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->orderBy('ACCOUNT_DETAILS.ID','DESC');
            //echo "<pre>";print_r($dt_obj->getDtArr());exit;
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
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);        }
    }


}

?>