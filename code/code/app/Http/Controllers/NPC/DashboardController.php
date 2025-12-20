<?php

namespace App\Http\Controllers\NPC;

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

            if(!in_array($this->roleId,[3,4,5,6,8,14])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('NPC/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

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
    public function dashboard(Request $request)
    {
        $activeTab = $request->input('activeTab');
      
        //Array to hold accounts count
        $accountsCount = array();
        $role = Session::get('role');
        $status = config('constants.STATUS_ON_ROLE.'.$role);
        //fetch savings and td accounts count
        $savingsTDaccountsCount = CommonFunctions::getAccountsCountByTypeAndStatus('savingsTD',$status);
        //fetch savings accounts count
        $accountsCount['savings'] = CommonFunctions::getAccountsCountByTypeAndStatus('savingsAccount',$status)
                                                + $savingsTDaccountsCount;
        //fetch current accounts count
        $accountsCount['current'] = CommonFunctions::getAccountsCountByTypeAndStatus('currentAccount',$status);
        //fetch fixed deposite accounts count
        $accountsCount['termDeposit'] = CommonFunctions::getAccountsCountByTypeAndStatus('termDeposit',$status)
                                                    + $savingsTDaccountsCount;
        // echo "<pre>";print_r($accountsCount);exit;
        //fetch applciation status
        $applicationStatus = config('constants.APPLICATION_STATUS');
        //fetch user names from applcations
        // $customerNames = CommonFunctions::getCustomerDetails();
        $customerNames = CommonFunctions::getCustomerDetailsNpc($role);

        //returns tempalte
        return view('npc.dashboard')->with('accountsCount',$accountsCount)
                                    ->with('customerNames',$customerNames)
                                    ->with('applicationStatus',$applicationStatus)
                                    ->with('activeTab',$activeTab);
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
            $requestData = $request->get('data');
            $select_arr=[];
            $role = Session::get('role');
            //build columns array

            if ($role == 5 || $role == 6) { //for QC & AUDIT
                $filteredColumns = ['UPDATED_AT','AOF_NUMBER','CUSTOMER_ID','ACCOUNT_NO','USER_NAME','CUSTOMER_TYPE','ACCOUNT_TYPE','APPLICATION_STATUS','REVIEWER_NAME','DEDUPE_STATUS','TAT_INDICATOR','ACTION','ID','L1_COUNTER','L2_COUNTER','CREATED_BY','L1_REVIEW','L2_REVIEW','QC_REVIEW','AUDIT_REVIEW','TD_ACCOUNT_NO','DELIGHT_SCHEME','IS_NEW_CUSTOMER','NPC_REVIEW_TIME','NPC_REVIEW_BY','SOURCE','FLOW_TYPE','CONSTITUTION'];
            }else{
                $filteredColumns = ['UPDATED_AT','AOF_NUMBER','USER_NAME','CUSTOMER_TYPE','ACCOUNT_TYPE','APPLICATION_STATUS','REVIEWER_NAME','DEDUPE_STATUS', 'TAT_INDICATOR', 'ACTION','ID','L1_COUNTER','L2_COUNTER','CREATED_BY','L1_REVIEW','L2_REVIEW','QC_REVIEW','AUDIT_REVIEW','DELIGHT_SCHEME','IS_NEW_CUSTOMER','NPC_REVIEW_TIME','NPC_REVIEW_BY','SOURCE','FLOW_TYPE','CONSTITUTION'];
            }
           
            $i=0;
            foreach ($filteredColumns as $column) {
                if($column == "USER_NAME")
                {
                    $user_name = "COD.FIRST_NAME || ' ' || COD.MIDDLE_NAME || ' ' || COD.LAST_NAME AS user_name";
                    array_push($select_arr,DB::raw($user_name));
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->user_name;
                            return $html;
                        }
                    );
                }
                else if($column == "CUSTOMER_ID"){
                    array_push($select_arr,"ACCOUNT_DETAILS.CUSTOMER_ID");
                    $dt[$i] = array( 'db' => 'account_details.customer_id','dt' => $i,
                        'formatter' => function( $d, $row)  {
                            $custAccnt = DB::table('CUSTOMER_OVD_DETAILS')
                                    ->where('APPLICANT_SEQUENCE', 1)
                                    ->where('FORM_ID', $row->id)
                                    ->get()->toArray();
                            $custAccnt = (array) current($custAccnt);
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
                else if($column == "ACCOUNT_NO"){
                     array_push($select_arr, strtolower("ACCOUNT_DETAILS.ACCOUNT_NO"));
                    $dt[$i] = array( 'db' => strtolower('account_details.account_no'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $entityAcctNo = DB::table('ENTITY_DETAILS')
                                            ->where('FORM_ID',$row->id)
                                            ->get()->toArray();
                            $entityAcctNo = (array) current($entityAcctNo);
                            // $html = ucfirst(config('constants.ACCOUNT_TYPES.'.$row->account_type));                     
                            $row = (array) $row;   
                            $account_type = $row['account_type'];   
                            if ($account_type == 'Savings') {
                                $html = $row['account_no']; 
                            }elseif($account_type == 'Current') {
                                $html = $entityAcctNo['entity_account_no'] ?? "";                     
                            }else{
                                $html = $row['td_account_no']; 
                            }

                            return $html;
                        }
                    );
                }else if($column == "CUSTOMER_TYPE"){
                    array_push($select_arr,DB::raw("ACCOUNT_DETAILS.CUSTOMER_ID AS type"));
                    $dt[$i] = array( 'db' => 'type','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) ($row);
                            if($row['flow_type'] == ''){
                            if ($row['delight_scheme'] != '' && $row['account_type'] == 'Savings') {
                                $html = 'DELIGHT';
                            }else if($row['is_new_customer'] == 1){
                                 $html = 'NTB';
                            }else if($row['source'] == 'CC'){
                                $html = 'ETB/CC';
                            }else{
                                $html = 'ETB';
                            }
                            }else{
                                 $html = $row['flow_type'];
                            }
                            return $html;
                        }
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

                }else if($column == "UPDATED_AT"){
                    array_push($select_arr,"ACCOUNT_DETAILS.UPDATED_AT");
                    $dt[$i] = array( 'db' => 'account_details.updated_at','dt' => $i,
                        'formatter' => function( $d, $row ) {
                        
                            $html = '<span class="aof_update_dt">'.\Carbon\Carbon::parse($row->updated_at)->format('d-m-Y H:i:s').'</span>';
                            
                            return $html;
                        }
                    );

                }else if($column == "TAT_INDICATOR"){
                    array_push($select_arr,DB::raw("ACCOUNT_DETAILS.UPDATED_AT as tat_ind"));
                    $dt[$i] = array( 'db' => 'tat_ind','dt' => $i,
                        'formatter' => function( $d, $row ) {
                        
                            $html = '<span class="date_white tat_indicator"></span>';
                            
                            return $html;
                        }
                    );

                }else if($column == "APPLICATION_STATUS"){
                    array_push($select_arr,"ACCOUNT_DETAILS.APPLICATION_STATUS");
                    $dt[$i] = array( 'db' => 'account_details.application_status','dt' => $i,
                        'formatter' => function( $d, $row ) use($role) {
                            if($role == 3)
                            {
                                $html = config('constants.APPLICATION_STATUS.'.$row->application_status).'-'.$row->l1_counter;
                            }else if($role == 4){
       
                                $html = config('constants.APPLICATION_STATUS.'.$row->application_status).'-'.$row->l2_counter;
                            }else{
                                $html = config('constants.APPLICATION_STATUS.'.$row->application_status);
                            }
                            return $html;
                        }
                    );
                }else if($column == "DEDUPE_STATUS"){
                    array_push($select_arr,"COD.DEDUPE_STATUS");
                    $dt[$i] = array( 'db' => 'co.dedupe_status','dt' => $i,
                        'formatter' => function( $d, $row ) use($i) {
                            $checkDedupeStatus = DB::table('CUSTOMER_OVD_DETAILS')
                                                    ->where('FORM_ID',$row->id)
                                                    ->pluck('dedupe_status')->toArray();

                            $j = 0;
                            foreach ($checkDedupeStatus as $dedupeStatus) {
                                if(($dedupeStatus != 'Pending') && ($dedupeStatus != ''))
                                {
                                    $j++;
                                }
                            }

                            if($j != count($checkDedupeStatus))
                            {
                                $html = $j.'/'.count($checkDedupeStatus).' Pending';
                            }else{
                                $html = $j.'/'.count($checkDedupeStatus).' Done';
                            }
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr,DB::raw("ACCOUNT_DETAILS.ID AS action"));
                    $dt[$i] = array( 'db' => 'action','dt' => $i,
                        'formatter' => function( $d, $row ) {
							
							// echo '<pre>'; print_r($row); exit;
							$reviewTime = $row->npc_review_time;

							$timeCheck = 'OK';
							if($reviewTime == '' || $reviewTime == NULL){
								$timeCheck = 'OK';
							}else{
								$timeDiffInMin = Carbon::now()->diffInMinutes($reviewTime);
								if($timeDiffInMin >= 15){
									$timeCheck = 'OK';
								}else{
									$timeCheck = 'NOTOK';
								}
							}						
							
                            if(($row->l1_review == 1 && $timeCheck == 'NOTOK') && (Session::get('role') == 3)){

                                $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="npcReview inReview">In-Review</a>';
                            }
                            elseif(($row->l2_review == 1  && $timeCheck == 'NOTOK') && (Session::get('role') == 4)){

                                $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="npcReview inReview">In-Review</a>';
                            }
                            elseif(($row->qc_review == 1  && $timeCheck == 'NOTOK') && (Session::get('role') == 5)){

                                $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="npcReview inReview">In-Review</a>';
                            }
                            elseif(($row->audit_review == 1  && $timeCheck == 'NOTOK') && (Session::get('role') == 6)){

                                $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="npcReview inReview">In-Review</a>';
                            }
                            else{
                                $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="npcReview">Review</a>';
                             }
                            //$html = '<a href="javascript:void(0)" id="'.$row->id.'" class="npcReview">Review</a>';
                            return $html;
                        }
                    );
                }else if($column == "REVIEWER_NAME"){
                    $emp_name = "USERS.EMP_FIRST_NAME || ' ' || USERS.EMP_MIDDLE_NAME || ' ' || USERS.EMP_LAST_NAME AS emp_name";
                    array_push($select_arr,DB::raw($emp_name));
                    $dt[$i] = array( 'db' => DB::raw($emp_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->emp_name;
                            return $html;
                        }
                    );
                }else{
                    array_push($select_arr,"ACCOUNT_DETAILS.".$column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = "account_details.".strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            if($role == 6){
                $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr)->take(100);
            }else{
                $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);
            }
            
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD','COD.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY');
            // $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.UPDATED_BY');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE');

            if($requestData['tabType'] == 'default')
            {
            $dt_obj = $dt_obj->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE',[1,2,3,4,5]);
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

            $aof_number = isset($requestData['AOF_NUMBER']) && $requestData['AOF_NUMBER'] !=''?$requestData['AOF_NUMBER']:'';

            $account_type = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','ID')->where('AOF_NUMBER',$aof_number)->get()->toArray();
            $account_type = (array) current($account_type);
            if(Session::get('role') == 5)
            {
                //showing dashboard current account
                
                if(isset($account_type['account_type']) && $account_type['account_type'] == '2'){

                    $formId = isset($account_type['id']) && $account_type['id'] !=''?$account_type['id']:'';
                    $checkCurrentAccount = DB::table('ENTITY_DETAILS')->select('ENTITY_ACCOUNT_NO')->where('FORM_ID',$formId)
                                                                    ->get()
                                                                    ->toArray();
                    $checkCurrentAccount = (array) current($checkCurrentAccount);
                    
                    if($checkCurrentAccount['entity_account_no'] == ''){
                        $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.ACCOUNT_NO','!=',null);
                    }
           
                }else{

                $dt_obj = $dt_obj->where(function ($query) {
                                   $query->where('ACCOUNT_DETAILS.ACCOUNT_NO', '!=', null)
                                         ->orWhere('ACCOUNT_DETAILS.TD_ACCOUNT_NO', '!=', null);
                 });
                }

                $dt_obj = $dt_obj->where(function ($query) {
                    $query->where('ACCOUNT_DETAILS.NEXT_ROLE','!=',6)
                            ->orWhereNull('ACCOUNT_DETAILS.NEXT_ROLE');
                });
            }

            if(Session::get('role') == 6)
            {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.NEXT_ROLE',Session::get('role'));
            }

			$dt_obj = $dt_obj
            ->where('ACCOUNT_DETAILS.NEXT_ROLE',Session::get('role'))
            ->where('APPLICANT_SEQUENCE',1);

            $NotrejectedApplicationStatus = [];
            $applicationStatus  = config('constants.APPLICATION_STATUS');
            foreach ($applicationStatus as $id => $status) {
                if (substr($status, -6) != 'Reject') {
                    array_push($NotrejectedApplicationStatus, $id);
                }
            }

            $dt_obj = $dt_obj->whereIn('ACCOUNT_DETAILS.APPLICATION_STATUS', $NotrejectedApplicationStatus);

            if(Session::get('role') == 4)
            {

                $dt_obj = $dt_obj->leftJoin('STATUS_LOG','STATUS_LOG.FORM_ID','ACCOUNT_DETAILS.ID');
                $dt_obj = $dt_obj->whereIn('STATUS_LOG.ROLE',['3','11']);
                $dt_obj = $dt_obj->where('STATUS_LOG.STATUS','10');
                $dt_obj = $dt_obj->where('STATUS_LOG.CREATED_BY','!=',$this->userId);
            
                }   
                                           
            if(Session::get('role') == 3 || Session::get('role') == 4 )
            { 
                $Value=DB::table('APPLICATION_SETTINGS')
                    ->whereIn('FIELD_NAME',['SA_PRIORITY_VALUE','TDA_PRIORITY_VALUE','CA_PRIORITY_VALUE'])
                    ->orWhere('FIELD_NAME','PRIORITY_SCHEME')
                    ->orderby('FIELD_NAME','asc')
                    ->pluck('field_value','field_name')
                    ->toArray();

                    if($Value['SA_PRIORITY_VALUE'] == ''){
                        $Value['SA_PRIORITY_VALUE'] = 0;
                    }

                    if($Value['CA_PRIORITY_VALUE'] == ''){
                       $Value['CA_PRIORITY_VALUE'] = 0; 
                    }

                    if($Value['TDA_PRIORITY_VALUE'] == ''){
                        $Value['TDA_PRIORITY_VALUE'] = 0;
                    }
                    // dd($Value);
           
                if($requestData['tabType']=='NR')
                {
                    $dt_obj = $dt_obj->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE',[6,7,8]);
                }
								
                elseif($requestData['tabType']=='PR')
                {  
                
                    if(is_numeric($Value['SA_PRIORITY_VALUE'])||is_numeric($Value['CA_PRIORITY_VALUE'])||is_numeric($Value['TDA_PRIORITY_VALUE'])) 
                    {          
                        $dt_obj = self::prioritytabValidation($dt_obj,$requestData['tabType'],$Value); 
                    }
                    else{
                        return json_encode(['data'=>[]]);
                    }
                             
                }elseif($requestData['tabType'] == 'default' || $requestData['tabType'] == ''){
                 
                 
                    
                    if(is_numeric($Value['SA_PRIORITY_VALUE']) || is_numeric($Value['CA_PRIORITY_VALUE']) || is_numeric($Value['TDA_PRIORITY_VALUE'])) 
                    { 
                        $dt_obj = self::prioritytabValidation($dt_obj,$requestData['tabType'],$Value); 
                    }else{
                        return json_encode(['data'=>[]]);
                    }
                }                    

            } 
            if(isset($requestData['AOF_NUMBER']) && !in_array($requestData['aof_tabType'],['PR','NR']))
            {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER', 'like', '%'.$requestData['AOF_NUMBER'].'%');
            } 
            // return response()->json($dt_obj->getDtArr());
        
            $dt_ssp_obj->setQuery($dt_obj->orderBy("ACCOUNT_DETAILS.UPDATED_AT","ASC"));
            
            $dd = $dt_ssp_obj->getData();
            
            $dd["items"] = (array) $dd["items"];
            
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
            
            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function prioritytabValidation($dt_obj,$tabType,$Value){
        
        $priority_value = isset($Value[0]) ? $Value[0] : '';                                     
        $priority_scheme = isset($Value[1]) ? $Value[1] : '';  
        $explodeArray= explode(',',$Value['PRIORITY_SCHEME']);
        $Priority_scheme= array_map('trim', $explodeArray);  
        $priority_scheme_value = isset($Value['PRIORITY_SCHEME']) ? $Value['PRIORITY_SCHEME'] : '';
            
            if($Value['PRIORITY_SCHEME']!= null)
            {                              

                if(str_contains($Value['PRIORITY_SCHEME'],'SB')){
                    $sbcode = DB::table('SCHEME_CODES')->select('scheme_code','id')->whereIn('scheme_code',$Priority_scheme)->pluck('id','scheme_code')->toArray();
                }

                if(str_contains($Value['PRIORITY_SCHEME'],'TD')){                         
                    $tdcode = DB::table('TD_SCHEME_CODES')->select('scheme_code','id')->whereIn('scheme_code',$Priority_scheme)->pluck('id','scheme_code')->toArray();
                }

                if(str_contains($Value['PRIORITY_SCHEME'],'CA')){
                    $cacode = DB::table('CA_SCHEME_CODES')->select('scheme_code','id')->whereIn('scheme_code',$Priority_scheme)->pluck('id','scheme_code')->toArray();
                }

                $scheme_code=[];
                $sbcount=0;
                $tdcount=0;
                $cacount=0;
                foreach ($Priority_scheme as $key => $value) 
                {           
                    switch (substr($value,0,2)){

                        case 'SB':
                            $scheme_code['SB'][$sbcount] = isset($sbcode[$value]) && $sbcode[$value]?$sbcode[$value]:'';
                            $sbcount++;  
                            break;

                        case 'TD':
                            $scheme_code['TD'][$tdcount] = isset($tdcode[$value]) && $tdcode[$value]?$tdcode[$value]:'';
                            $tdcount++; 
                            break;

                        case 'CA':
                            $scheme_code['CA'][$cacount] = isset($cacode[$value]) && $cacode[$value]?$cacode[$value]:'';
                            $cacount++;
                            break;
                    }                     
                }
            }
            
            if((isset($requestData['AOF_NUMBER']) && $requestData['AOF_NUMBER'] != '') && $requestData['aof_tabType'] == 'PR')
            {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER', 'like', '%'.$requestData['AOF_NUMBER'].'%');
            }

                    $SBschemcode = isset($scheme_code['SB']) ? $scheme_code['SB']: [];                                     
                    $TDschemecode = isset( $scheme_code['TD']) ? $scheme_code['TD'] : []; 
                    $CAschemecode = isset( $scheme_code['CA']) ? $scheme_code['CA'] : []; 
           
                    $dt_obj = $dt_obj->where(function ($query)use($priority_value,$SBschemcode,$TDschemecode,$CAschemecode,$Value,$tabType) {
                                               
                    $query->where(function($q)use($SBschemcode,$Value,$tabType) {

                        if($tabType == 'PR'){
                            if(count($SBschemcode) > 0){
                                $q->whereIn('ACCOUNT_DETAILS.SCHEME_CODE',$SBschemcode)
                                ->orwhereRaw("LPAD(COD.AMOUNT, 10, '0') >= LPAD('" . $Value['SA_PRIORITY_VALUE'] . "', 10, '0')")
                                ->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 1);
                            }else{
                                    $q->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 1)
                                    ->whereRaw("LPAD(COD.AMOUNT, 10, '0') >= LPAD('" . $Value['SA_PRIORITY_VALUE'] . "', 10, '0')");
                            }
                        }else{
                            if(count($SBschemcode) > 0){
                                $q->whereNotIn('ACCOUNT_DETAILS.SCHEME_CODE',$SBschemcode);                          

                                if($Value['SA_PRIORITY_VALUE'] == 0){
                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<=', $Value['SA_PRIORITY_VALUE']);
                                }else{

                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<', $Value['SA_PRIORITY_VALUE']);
                                }
                            $q = $q->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 1);
                            }
                            else{
                                $q->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 1);
                                  if($Value['SA_PRIORITY_VALUE'] == 0){
                                      $q =$q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<=', $Value['SA_PRIORITY_VALUE']);                                
                                  }else{
                                      $q =$q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<', $Value['SA_PRIORITY_VALUE']);                                
                                  }
                            }
                        }
                        })->orWhere(function($q)use($CAschemecode,$Value,$tabType) {

                        if($tabType == 'PR'){
                                if(count($CAschemecode) > 0){
                                    $q->whereIn('ACCOUNT_DETAILS.SCHEME_CODE',$CAschemecode)
                                        ->orwhereRaw("LPAD(COD.AMOUNT, 10, '0') >= LPAD('" . $Value['CA_PRIORITY_VALUE'] . "', 10, '0')")                             
                                        ->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 2);
                                }else{
                                    $q->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 2)
                                    ->whereRaw("LPAD(COD.AMOUNT, 10, '0') >= LPAD('" . $Value['CA_PRIORITY_VALUE'] . "', 10, '0')");
                                                                
                                }
                        }else{
                            if(count($CAschemecode) > 0){
                                $q->whereNotIn('ACCOUNT_DETAILS.SCHEME_CODE',$CAschemecode);

                                if($Value['CA_PRIORITY_VALUE'] == 0){
                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<=', $Value['CA_PRIORITY_VALUE']);
                                }else{
                                    $q =$q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<', $Value['CA_PRIORITY_VALUE']);                                              
                                }

                                $q= $q->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 2);

                            }else{
                                $q->where('ACCOUNT_DETAILS.ACCOUNT_TYPE', 2);
                                if($Value['CA_PRIORITY_VALUE'] == 0){
                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<=', $Value['CA_PRIORITY_VALUE']);
                                }else{
                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<', $Value['CA_PRIORITY_VALUE']);
                                }
                            }
                        }
                        })  
                    ->orWhere(function($q)use($TDschemecode,$SBschemcode,$Value,$tabType) {
                        if($tabType == 'PR'){
                                if(count($TDschemecode)>0){
                                    $q->whereIn('ACCOUNT_DETAILS.TD_SCHEME_CODE',$TDschemecode)
                                        ->orWhereIn('ACCOUNT_DETAILS.SCHEME_CODE',$SBschemcode)
                                        ->orwhereRaw("LPAD(COD.AMOUNT, 10, '0') >= LPAD('" . $Value['TDA_PRIORITY_VALUE'] . "', 10, '0')") 
                                        ->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE', [3,4]);
                                }
                                else{
                                    $q->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE', [3,4])
                                    ->whereRaw("LPAD(COD.AMOUNT, 10, '0') >= LPAD('" . $Value['TDA_PRIORITY_VALUE'] . "', 10, '0')");
                                    
                                }
                        }else{
                            if(count($TDschemecode)>0){
                                $q->whereNotIn('ACCOUNT_DETAILS.TD_SCHEME_CODE',$TDschemecode)
                                ->orWhereNotIn('ACCOUNT_DETAILS.SCHEME_CODE',$SBschemcode);
                                if($Value['TDA_PRIORITY_VALUE'] == 0){
                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<=', $Value['TDA_PRIORITY_VALUE']);

                                }else{

                                    $q= $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<', $Value['TDA_PRIORITY_VALUE']);  
                                }

                            $q = $q->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE', [3,4]);

                            }else{
                                $q->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE', [3,4]);
                                if($Value['TDA_PRIORITY_VALUE'] == 0){
                                        $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<=', $Value['TDA_PRIORITY_VALUE']); 
                                }else{

                                    $q = $q->where(DB::raw('CASE WHEN COD.AMOUNT IS NULL THEN 0 ELSE TO_NUMBER(COD.AMOUNT DEFAULT 0 ON CONVERSION ERROR) END'), '<', $Value['TDA_PRIORITY_VALUE']);                                                                          
                                }
                            }
                        }
                    });                                        
                });
        return $dt_obj;    
    }                         
}
?>
