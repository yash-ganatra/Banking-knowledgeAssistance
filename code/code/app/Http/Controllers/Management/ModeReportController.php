<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
Use Crypt;
use Cache;

class ModeReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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

    public function modereport()
    {
        try{

             
          // fetch user names from applcations
            $customerNames = CommonFunctions::getCustomerDetails(3);
            //returns tempalte
            return view('manco.modereport')->with('customerNames',$customerNames);
               
     }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Management/ModeReportController','modereport',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getmodereport(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
              $select_arr=[];

            $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
            //echo "<pre>";print_r($userDetails);exit;
            //build columns array
            // $filteredColumns = ['AOF_NUMBER','USER_NAME','CUSTOMER_TYPE','ACCOUNT_TYPE','CREATED_AT','APPLICATION_STATUS',
            //                         'REVIEWER_NAME','ACTION','L1_COUNTER','L2_COUNTER','CREATED_BY','IS_ACTIVE','IS_ACTIVE','DELIGHT_SCHEME'];
            $filteredColumns = ['BRANCH_ID','AOF_NUMBER','ACCOUNT_TYPE','SCHEME_CODE','CUSTOMER_TYPE','EKYC','CREATED_AT','ACTION','DELIGHT_SCHEME','FLOW_TYPE'];
            $i=0;
            //build dt array
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
                else if($column == "CUSTOMER_TYPE"){
                     array_push($select_arr,"ACCOUNT_DETAILS.IS_NEW_CUSTOMER");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.IS_NEW_CUSTOMER'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) ($row);
                            if($row['flow_type'] == ''){

                            if ($row['delight_scheme'] != '' && $row['account_types.account_type'] == 'Savings') {
                                $html = 'DELIGHT';
                            }else if($row['is_new_customer'] == 1){
                                 $html = 'NTB';
                            }else{
                                $html = 'ETB';
                            }
                            }else{
                                $html = $row['flow_type'];
                            }
                                                    
                            return $html;
                        }
                    );
                }
                else if($column == "ACCOUNT_TYPE"){
                     array_push($select_arr,"ACCOUNT_TYPES.ACCOUNT_TYPE");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_TYPES.ACCOUNT_TYPE'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $row = (array) $row;
                                                 
                            $html = $row['account_type'];
                            return $html;
                        }
                    );
                }
                else if($column == "CREATED_AT"){
                     array_push($select_arr,"ACCOUNT_DETAILS.CREATED_AT");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            
                            $submissionDate = CommonFunctions::getBranchSubmissionDate($row->id);

                            //echo "<pre>";print_r($submissionDate);exit;

                            $html = \Carbon\Carbon::parse($submissionDate->created_at)->format('d-m-Y');
                            return $html;
                        }
                    );
                }
                
                else if($column == "SCHEME_CODE"){
                    array_push($select_arr,"ACCOUNT_DETAILS.SCHEME_CODE");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.SCHEME_CODE'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            
                            $schemeCode = $row['scheme_code'];
                            $accountType = $row['account_type'];

                            if($row['account_type'] == 'Savings'){

                             $getSchemeDesc = CommonFunctions::getSchemeCodesDesc($accountType,$schemeCode);
                             $html = $getSchemeDesc->scheme_code;

                            }
                            elseif($row['account_type'] == 'Term Deposit'){

                                $getTDSchemeDesc = CommonFunctions::getTDSchemeCodesDesc($accountType,$schemeCode);
                                $html = $getTDSchemeDesc->scheme_code;
                            }
                            else{

                                $getComboSchemeDesc = CommonFunctions::getSchemeCodesDesc($accountType,$schemeCode);
                                $html = $getComboSchemeDesc->scheme_code;
                            }
    
                            return $html;
                        }
                    );
                }
                else if($column == "EKYC"){
                   $id_proof = "COD.PROOF_OF_IDENTITY AS id_proof";
                    array_push($select_arr,$id_proof);
                    $dt[$i] = array( 'db' => DB::raw($id_proof),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            if($row->id_proof == 9){

                              $html = 'EKYC';
                            }else{

                              $html = 'NON-EKYC';
                            }


                            return $html;
                        }
                    );
                }
                else if($column == "ACTION"){
                 array_push($select_arr,"ACCOUNT_DETAILS.ID");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.ID'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $url = route('aoftracking').'/'.$row->aof_number;                              
                            $html = '<a href="'.$url.'" id="'.$row->id.'" target="_blank">View</a>';
                            return $html;
                        }
                    );
                }
                else if($column == "REVIEWER_NAME"){
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
                    $dt[$i]['db'] = strtolower("ACCOUNT_DETAILS.".$column);
                    $dt[$i]['dt'] = $i;
                }                
                $i++;              
            }
            // $dt_obj = new SSP('ACCOUNT_DETAILS', $dt);
             $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD','COD.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY');
            $dt_obj = $dt_obj->leftjoin('STATUS_LOG AS STL','STL.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE');
                 $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subDays(7));
            
            //checks customer name is empty or not
            if($requestData['aofnumber'] != '')
            {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER','like','%'.$requestData['aofnumber'].'%');
            }
            //checks applciation status is empty or not
            // if($requestData['status'] != '')
            // {
            //     $dt_obj = $dt_obj->where('APPLICATION_STATUS',$requestData['status']);
            // }
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
            }else{
                 //$dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(1));
            }

            $startDate = Carbon::now()->subDays(180)->format('d-m-Y');
            $endDate = Carbon::now()->format('d-m-Y');
            
            $dt_obj = $dt_obj->where('STL.STATUS',2);
            // $dt_obj = $dt_obj->where('STL.ROLE',2);
            $dt_obj = $dt_obj->where('STL.ROLE',2);
            $dt_obj = $dt_obj->whereRaw("STL.CREATED_AT >= to_date('".$startDate."','DD-MM-YYYY')");
            $dt_obj = $dt_obj->whereRaw("STL.CREATED_AT <= to_date('".$endDate."','DD-MM-YYYY')");
            $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1);
            //echo "<pre>";print_r($dt_obj->getDtArr());exit;
            // return response()->json($dt_obj->getDtArr());
            $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->orderBy("ACCOUNT_DETAILS.ID","DESC");
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);

            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Pldease try again','data'=>[]]);        }
    }

    public function getdescreportData($startDate, $endDate){

            // $startDate = Carbon::now()->subDays($reportDays)->format('Y-m-d');   
            // $endDate =  Carbon::now()->subDays(46)->format('Y-m-d');
            
            $formsToCheck = DB::table('ACCOUNT_DETAILS')->select('ID', 'AOF_NUMBER', 'BRANCH_ID', 'ACCOUNT_TYPE', 'NO_OF_ACCOUNT_HOLDERS', 'SCHEME_CODE', 'TD_SCHEME_CODE', 'IS_NEW_CUSTOMER', 'CUSTOMER_ID', 'ACCOUNT_NO', 'TD_ACCOUNT_NO', 'CREATED_AT', 'CREATED_BY', 'IS_NEW_CUSTOMER', 'DELIGHT_SCHEME' )
                            ->whereDate('CREATED_AT', '>=', $startDate)
                            ->whereDate('CREATED_AT', '<=', $endDate)
                            ->orderBy('ID')
                            ->get()->toArray();
            // echo "<pre>";print_r(count($formsToCheck));exit;

            $arrayToReport = array();
            
            for($fid = 0; $fid < count($formsToCheck); $fid++){
                
                $currForm = $formsToCheck[$fid]->id;
                            
                $ovd = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ID', 'FIRST_NAME', 'LAST_NAME','PROOF_OF_IDENTITY','CUSTOMER_ID')
                            ->where('FORM_ID', $currForm)
                            ->orderBy('ID')
                            ->get()->toArray();
                            
                $formsToCheck[$fid]->ovd = $ovd;                                                                

                $submissions = DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
                            ->select('ID', 'SUBMISSION_DATE', 'BRANCH_SUBMISSION', 'L1', 'L2', 'QC', 'AUDITING', 'L3', 'CREATED_BY')
                            ->where('FORM_ID', $currForm)                           
                            ->whereNull('ACCOUNT_OPENED')->whereNull('DISPATCH')->whereNull('COURIER')
                            ->whereNull('INWARD')->whereNull('ARCHIVAL')
                            ->orderBy('ID')
                            ->get()->toArray();
                            
                $npc_log = DB::table('NPC_REVIEW_LOG')
                            ->select('ID', 'ROLE_ID', 'STATUS', 'DESC_COUNT', 'CREATED_BY', 'CREATED_AT', 'TIME_TAKEN')
                            ->where('FORM_ID', $currForm)
                            ->orderBy('ID')
                            ->get()->toArray();
                                                        
                $desc_log = DB::table('REVIEW_TABLE')
                            ->select('ID', DB::raw("COLUMN_NAME || ': ' || COMMENTS AS COLUMN_NAME"), 'ROLE_ID', 'ITERATION', 'CREATED_AT')
                            ->where('FORM_ID', $currForm)
                            ->where('COMMENTS', 'not like', 'Auto added:%')
                            ->where('COMMENTS', 'not like', 'Automarked as part%')
                            ->orderBy('ID')
                            ->get()->toArray();

                $cust_acct_log = DB::table('STATUS_LOG')
                                ->select('ID','STATUS','CREATED_AT','CREATED_BY')
                                ->where('FORM_ID',$currForm)
                                ->whereIn('STATUS',[22,24])
                                ->orderBy('ID')
                                ->get()->toArray();
                            
                $formsToCheck[$fid]->cust_acct_log = $cust_acct_log;

                  // echo "<pre>";print_r($status_details);exit;   
                            
                if(count($npc_log)>0){
                    $formsToCheck[$fid]->npc_review = $npc_log;     
                    
                    if(count($desc_log)>0) {
                        $formsToCheck[$fid]->descrepancies = $desc_log;         
                    }else{
                        $formsToCheck[$fid]->descrepancies = array();           
                    }

                    if(count($submissions)>0) {
                        $formsToCheck[$fid]->submissions = $submissions;            
                    }else{
                        $formsToCheck[$fid]->submissions = array();         
                    }
                    
                 // Add branch submission to npc entires and then sort on dates
                 $br_npc_log = $npc_log;
                 
                 $tarray = json_decode(json_encode($submissions), true);                        
                 $br_sub = array_filter($tarray, function ($arr) {
                        return $arr['branch_submission'] != null;
                 });
                                                 
                foreach($br_sub as $k => $v){
                     $entToPush = array( 'role_id' => 2, 
                                         'created_at' => $v['submission_date'], 
                                         'created_by' => ($v['created_by'] == null ? 1 : $v['created_by']), 
                                         'status' => 'submitted',
                                         'time_taken' => (($v['branch_submission'] > 0 ? $v['branch_submission'] : 1) * 60)
                                        );
                     array_push($br_npc_log, $entToPush);
                 }
                                
                array_multisort(array_column($br_npc_log, 'created_at'), SORT_ASC, $br_npc_log);
                
                $formsToCheck[$fid]->br_npc_log = $br_npc_log;                          
                
                
                array_push($arrayToReport, $formsToCheck[$fid]);                                                                                        
                }
                                                                                        
     } // EndFor each FormID
     return $arrayToReport;
     
    } // Endof getdescreportData
            
    public function tatreport()
    {
        try{

            $disDateRange = CommonFunctions::getapplicationSettingsDetails('DIMENSION_DAYS');
            if($disDateRange == ''){
                $disDateRange = '15';
            }
            return view('manco.TATreport')->with('disDateRange',$disDateRange);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Management/ModeReportController','tatreport',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    // This is Dimension Report!
    public function tatreportdetails(Request $request)
    {
        $requestData = $request->get('data');

        ini_set('memory_limit', '2048M');   // 2GB
        set_time_limit(300);                // 5min

        $startDate = '';
        if (isset($requestData['startDate']) && $requestData['startDate'] != '') {

            $startDate = $requestData['startDate'];
            try {
                $startDate = Carbon::parse($startDate);
            } catch (\Exception $e) {
                return json_encode(['status'=>'fail','msg'=>'Please select valid date.','data'=>'']);
            }
            $endDate = $startDate->copy();
            $disDateRange = CommonFunctions::getapplicationSettingsDetails('DIMENSION_DAYS');

            //--------------not required data into the table--------------\\

            if($disDateRange == ''){
                $disDateRange = 14;
            }

            $endDate->addDays($disDateRange);
        }else{
            return json_encode(['status'=>'fail','msg'=>'Please select valid date.','data'=>'']);
        }            

        if(Cache::get('reviewDimReport') == 'ON'){
            return json_encode(['status'=>'fail','msg'=>'Another report in progress, please retry after few minutes.','data'=>'']);
        }
        $arrayToReport = Self::getdescreportData_v2($startDate,$endDate);          
        
        Cache::put('reviewDimReport','ON',now()->addMinutes(5));
        
        
        // $arrayToReport = Self::getdescreportData($startDate, $endDate);                  // Old function         
        // echo "<pre>";print_r($arrayToReport);exit;
        
        if (count($arrayToReport) == 0) {
            Cache::put('reviewDimReport','OFF');
            return json_encode(['status'=>'fail','msg'=>'Data not found for selected date range','data'=>'']);
        }

            
        $output = '';

        for($seq=0;count($arrayToReport)>$seq;$seq++){
            
            for($seqData=0;count($arrayToReport[$seq])>$seqData;$seqData++){

                $value = str_replace(',',' ',$arrayToReport[$seq][$seqData]);
                $output .= $value.",";
            }    

            $output .= "\n";  
        }

        // echo "<pre>";print_r($output);exit;        
        // $users = DB::table('USERS')->select('ID', 'HRMSNO', 'EMP_FIRST_NAME', 'EMP_LAST_NAME')->get()->toArray();        
        // $users = json_decode(json_encode($users));        
        // $generatedReport = Self::showDataDumpHtml($arrayToReport); 
        Cache::put('reviewDimReport','OFF');
        return json_encode(['status'=>'success','msg'=>'Dimension Report fetched','data'=>$output]);
        
        // return json_encode(['status'=>'success','msg'=>'Processing Data. Please Wait...','data'=>$arrayToReport]);
        // return $generatedReport; 
    }

    public function getAccounType($actType, $delightScheme){
        
        $response = $actType;
        switch($actType){
            case "1":
                $response = 'SA';
                break;
            case "2":
                $response = 'CA';
                break;
            case "3":
                $response = 'TD';
                break;
            case "4":
                $response = 'SATD';
                break;
        }
        
        if($actType == 1 && $delightScheme != ''){
            $response = 'DELIGHT';
        }
        
        return $response;       

    }
        
    private function showDescReportHtml($arrayToReport){
        
            echo '<pre>';           
            echo '<html><body>';
            echo '<style>
                    table { font-family: arial, sans-serif; border-collapse: collapse;width: 100%; font-size: 12px;}
                    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
                </style>';
            echo '<table border="1px;">';
            
            echo '<tr>';
                echo '<td>AOF Details</td>';                    
                echo '<td>DATE</td>';                   
                echo '<td>ROLE</td>';                   
                echo '<td width="25px;">REVIEW TIME (Sec)</td>';
                echo '<td>STATUS</td>';                 
                echo '<td width="15px;">DESCREPENCIES</td>';                    
                echo '<td width="45px;">FIELDS</td>';                   
            echo '</tr>';   


            // +2 to accomodate custID and acctID rows
            for($ar = 0; $ar < (count($arrayToReport)+2); $ar++){
                
                $currRec = $arrayToReport[$ar]->npc_review;
                
                // if($arrayToReport[$ar]->aof_number != '21182002051') continue;
                
                echo '<tr style="border-top-style:inset; border-top-width:2px;">';

                echo '<td rowspan="'.(count($currRec)+1).'" style="vertical-align: top;">';
                echo 'AOF: '.$arrayToReport[$ar]->aof_number.'';                    
                echo '<br><br>Date: '.$arrayToReport[$ar]->created_at.'';                   
                echo '<br>SOL: '.$arrayToReport[$ar]->branch_id.'';                 
                echo '<br>TYPE: '.$arrayToReport[$ar]->account_type.'';                 
                echo '<br>SCHEME: '.$arrayToReport[$ar]->scheme_code.'';                    
                echo '<br>CUSTID: '.$arrayToReport[$ar]->customer_id.'';                    
                echo '<br>ACCTNO.: '.$arrayToReport[$ar]->account_no.'</td>';                                       
                echo '<td colspan="6" style="display:none;">  </td>';
                
                echo '</tr>';
                
                    $counterL1 = $counterL2 = $counterQA = $counterAU = 0;
                    
                    for($rvw=0; $rvw<count($currRec); $rvw++){                          
                            
                            $role = $currRec[$rvw]->role_id;
                            switch($role){
                                case "3":
                                    $role = 'L1';
                                    break;
                                case "4":
                                    $role = 'L2';
                                    break;
                                case "5":
                                    $role = 'QA';
                                    break;
                                case "6":
                                    $role = 'AU';
                                    break;
                                
                            }
                            
                            echo '<tr>';
                                //echo '<td>'.$currRec[$rvw]->id.'</td>';                   
                                echo '<td>'.$currRec[$rvw]->created_at.'</td>';                 
                                echo '<td>'.$role.'</td>';                  
                                echo '<td>'.$currRec[$rvw]->time_taken.'</td>';                 
                                echo '<td>'.$currRec[$rvw]->status.'</td>';                 
                                echo '<td>'.($currRec[$rvw]->desc_count != 0 ? $currRec[$rvw]->desc_count : '').'</td>';
                                                                
                                $descFields = '';
                                if($currRec[$rvw]->desc_count != 0 && in_array($role, ['L1', 'L2', 'QA', 'AU'])){                                   
                                    if(isset($arrayToReport[$ar]->descrepancies) && count($arrayToReport[$ar]->descrepancies)>0){
                                        ${'counter'.$role}++;                                       
                                        foreach ($arrayToReport[$ar]->descrepancies as $value) {
                                            if($value->role_id == $currRec[$rvw]->role_id && $value->iteration == ${'counter'.$role}){
                                                $descFields .= $value->column_name.', ';
                                            }   
                                        }
                                    }                                                                       
                                }                               
                                echo '<td>'.$descFields.'</td>';                                                                
                            
                            echo '</tr>';               
                    }
                                
            }           
            echo '</table></body></html>';

            //print_r($arrayToReport); exit;

    }


    private function showNonFTRReportHtml($arrayToReport){
                
            $getBoth = false;

            echo '<pre>';           
            echo '<html><body>';
            echo '<style>
                    table { font-family: arial, sans-serif; border-collapse: collapse;width: 100%; font-size: 12px;}
                    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
                </style>';
            echo '<table border="1px;">';
            
            echo '<tr>';
                echo '<td>SOL</td>';                    
                echo '<td>AOF</td>';                    
                echo '<td>Name</td>';                   
                echo '<td>Type</td>';
                echo '<td>Account Type</td>';                   
                echo '<td>Submitted On</td>';                                   
                echo '<td style="width:120px;">Branch<br>Fields (Iterations)</td>';
                echo '<td>L1</td>';                 
                echo '<td>L2</td>';                 
                echo '<td>Remarks</td>';                                    
            echo '</tr>';   


            for($ar = 0; $ar < count($arrayToReport); $ar++){
                
                $currRec = $arrayToReport[$ar]->npc_review;
                $ovdRec = $arrayToReport[$ar]->ovd;
                
                // if($arrayToReport[$ar]->aof_number != '21182002051') continue;
                $acct_type = Self::getAccounType($arrayToReport[$ar]->account_type, $arrayToReport[$ar]->delight_scheme);
                                
                
                    $counterBR = $counterL1 = $counterL2 = 0;
                    $itrBR = $itrL1 = $itrL2 = 0;
                    $descBR = $descL1 = $descL2 = array();
                    
                    for($rvw=0; $rvw<count($currRec); $rvw++){ // NPC Review Records
                        
                        if($currRec[$rvw]->desc_count != 0){                                    
                            if(isset($arrayToReport[$ar]->descrepancies) && count($arrayToReport[$ar]->descrepancies)>0){
                                foreach ($arrayToReport[$ar]->descrepancies as $value) {                                    
                                    switch($value->role_id){
                                        case "3":
                                            $counterBR += $currRec[$rvw]->desc_count;
                                            $itrBR++;
                                            array_push($descBR,$value->column_name);
                                            break;
                                        case "4":
                                            $counterL1 += $currRec[$rvw]->desc_count;
                                            $itrL1++;
                                            array_push($descL1,$value->column_name);                                            
                                            break;
                                        case "5":
                                            $counterL2 += $currRec[$rvw]->desc_count;
                                            $itrL2++;
                                            array_push($descL2,$value->column_name);
                                            break;
                                    }   // Switch                                                                   
                                }       // Foreach
                            }           // If isset                                                                     
                        }               // If currRec != 0
                                                    
                    }                   // for NPC Review Records
                    
                
                if( $getBoth  || (count($descBR) > 0 || count($descL1) > 0 || count($descL2) > 0) ){

                    echo '<tr style="border-top-style:inset; border-top-width:2px;">';
                        
                        echo '<td>'.$arrayToReport[$ar]->branch_id.'</td>';                 
                        echo '<td>'.$arrayToReport[$ar]->aof_number.'</td>';                    
                        echo '<td>'.$ovdRec[0]->first_name.' '.$ovdRec[0]->last_name.'</td>';                   
                        echo '<td>'.($arrayToReport[$ar]->is_new_customer == 0 ? 'ETB' : 'NTB').'</td>';                    
                        echo '<td>'.$acct_type.'</td>';                 
                        echo '<td>'.$arrayToReport[$ar]->created_at.'</td>';                    

                        echo '<td>'.($counterBR != 0 ? $counterBR.' ('.$itrBR.')' : '').'</td>';                    
                        echo '<td>'.($counterL1 != 0 ? $counterL1.' ('.$itrL1.')' : '').'</td>';                    
                        echo '<td>'.($counterL2 != 0 ? $counterL2.' ('.$itrL2.')' : '').'</td>';                    
                        echo '<td>';
                            if(count($descBR)>0){
                                echo 'Branch: '.implode(',', $descBR).'<br>';
                            }   
                            if(count($descL1)>0){
                                echo 'L1: '.implode(',', $descL1).'<br>';
                            }   
                            if(count($descL2)>0){
                                echo 'L2: '.implode(',', $descL2).'<br>';
                            }   
                        echo '</td>';                                   

                    echo '</tr>';
                }   
                                                    
            }           
        echo '</table></body></html>';
    }

    public function getdescreportData_v2($startDate, $endDate){

        DB::table('DIMENSION_REPORT')->truncate();

        $schema = env('APP_SETUP') == 'DEV' ? 'NPC.' : 'CUBE.';
        // $today = Carbon::now()->format('Y-m-d');
        // $from = Carbon::now()->subDays(120)->format('Y-m-d');
        $from = $startDate->format('Y-m-d');
        $today = $endDate->format('Y-m-d');
        // ACCOUNT_DETAILS  
        $insertQuery =  "insert into ".$schema."DIMENSION_REPORT (
                                FORM_ID, AOF_NUMBER, SOL_ID, ACCOUNT_TYPE, NO_OF_ACCOUNT_HOLDERS, SCHEME_CODE, TD_SCHEME_CODE, CUSTOMER_TYPE, 
                                        ACCOUNT_NO, TD_ACCOUNT_NO, CREATED_AT, UPDATED_USER_ID, DELIGHT_SCHEME,
                                             CUSTOMER_NAME, PROOF_OF_IDENTITY, CUSTOMER_ID, STATUS, ROLE,IS_EKYC,TAG
                                            ) 
            select a.ID, a.AOF_NUMBER, a.BRANCH_ID, a.ACCOUNT_TYPE, a.NO_OF_ACCOUNT_HOLDERS, a.SCHEME_CODE, a.TD_SCHEME_CODE, 
                                CASE WHEN a.IS_NEW_CUSTOMER = '1' THEN 'NTB'
                                     WHEN a.IS_NEW_CUSTOMER = '0' THEN 'ETB'
                                     ELSE ''
                                END AS CUSTOMER_TYPE, 
                a.ACCOUNT_NO, a.TD_ACCOUNT_NO, a.CREATED_AT, a.CREATED_BY, 

                CASE WHEN a.DELIGHT_SCHEME is NOT NULL THEN 'Y'
                                     ELSE 'N'
                                END AS DELIGHT_SCHEME, 
                    o.FIRST_NAME || ' ' || o.LAST_NAME, o.PROOF_OF_IDENTITY, o.CUSTOMER_ID, 'Created' AS STATUS, '2' AS ROLE,
                     CASE WHEN o.PROOF_OF_IDENTITY = '9' THEN 'Y'
                                     ELSE 'N'
                                END AS IS_EKYC, 
                     'ACCOUNT' AS TAG
               from ".$schema."ACCOUNT_DETAILS a, ".$schema."CUSTOMER_OVD_DETAILS o 
                    where a.ID = o.FORM_ID and 
                                (a.ACCOUNT_NO is not null or a.TD_ACCOUNT_NO is not null or a.APPLICATION_STATUS=1 or a.APPLICATION_STATUS=10) and 
                                    trunc(a.CREATED_AT) BETWEEN to_date('".$from."','YYYY-MM-DD')  AND to_date('".$today."', 'YYYY-MM-DD') ";

        $act = DB::insert($insertQuery);

        // NPC_REVIEW_LOG
        $insertQuery =  "insert into ".$schema."DIMENSION_REPORT (
                   FORM_ID, ROLE, STATUS, TIME_COUNTER, CREATED_AT, UPDATED_USER_ID, TAG) 
                select FORM_ID, ROLE_ID, STATUS, to_char(TIME_TAKEN), CREATED_AT, CREATED_BY, 'REVIEW' AS TAG
                    from ".$schema."NPC_REVIEW_LOG 
                        where trunc(CREATED_AT) BETWEEN to_date('".$from."','YYYY-MM-DD')  AND to_date('".$today."', 'YYYY-MM-DD') ";
        
        $review = DB::insert($insertQuery);

        // DESC // REVIEW_TABLE
        $insertQuery =  "insert into ".$schema."DIMENSION_REPORT (
            FORM_ID, ROLE, STATUS, DISCREPANCY_COUNT, DISCREPANCY_FIELDS,  CREATED_AT, UPDATED_USER_ID, TAG) 
       select FORM_ID, ROLE_ID, 'Reviewed', COUNT(form_id),
                LISTAGG(COLUMN_NAME||': '||COMMENTS , '| ' on overflow truncate) WITHIN GROUP (ORDER BY COLUMN_NAME) AS DISCREPANCY_FIELDS,
                    max(CREATED_AT) as CREATED_AT, max(CREATED_BY) as CREATED_BY, 'DESC' AS TAG
           from ".$schema."REVIEW_TABLE".
               " where trunc(CREATED_AT) BETWEEN to_date('".$from."','YYYY-MM-DD')  AND to_date('".$today."', 'YYYY-MM-DD') 
                group by form_id, role_id, ITERATION   ";

                // COMMENTS not like 'Auto%' 

        $desc = DB::insert($insertQuery);

        // STATUS_LOG
        $insertQuery =  "insert into ".$schema."DIMENSION_REPORT (
                            FORM_ID, ROLE, STATUS, CREATED_AT, UPDATED_USER_ID, TAG) 
                        select FORM_ID, ROLE, 
                                    CASE WHEN STATUS = '2' THEN 'Submitted'
                                    WHEN STATUS = '22' THEN 'Cust ID'
                                    WHEN STATUS = '24' THEN 'Account No'
                                    ELSE ''
                                END AS STATUS,
                                CREATED_AT, CREATED_BY, 'STATUS' AS TAG
                            from ".$schema."STATUS_LOG".
                                " where STATUS in (2, 22, 24)
                                    and trunc(CREATED_AT) BETWEEN to_date('".$from."','YYYY-MM-DD')  AND to_date('".$today."', 'YYYY-MM-DD') ";
        $status = DB::insert($insertQuery);
        // Remove unwanted records where there is no AOF!
        $delQuery = "delete from ".$schema."DIMENSION_REPORT WHERE FORM_ID  NOT IN (SELECT DISTINCT form_id FROM ".$schema."DIMENSION_REPORT WHERE tag = 'ACCOUNT')";
        $delQry =  DB::update($delQuery);

        $updt1Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.SCHEME_CODE_DESC = (SELECT sc.SCHEME_CODE FROM ".$schema."SCHEME_CODES sc WHERE (sc.ID = dim.scheme_code) AND (dim.account_type = 1 OR dim.account_type = 4 OR dim.scheme_code = 14))";
        $up1 = DB::update($updt1Query);

        $updt2Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.TD_SCHEME_CODE_DESC = (SELECT sc.SCHEME_CODE FROM ".$schema."TD_SCHEME_CODES sc WHERE sc.ID = dim.scheme_code AND (dim.account_type = 3 OR dim.account_type = 4))";
        $up2 = DB::update($updt2Query);
        
        // //current account   
        $updt9Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.CA_SCHEME_CODE_DESC = (SELECT sc.SCHEME_CODE FROM ".$schema."CA_SCHEME_CODES sc WHERE (sc.ID = dim.scheme_code AND sc.ACCOUNT_TYPE = dim.account_type) OR (dim.scheme_code_desc = 'SB129' AND sc.id = 1))";
        $up9 = DB::update($updt9Query);

        $updt3Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.UPDATED_USER_NAME = (SELECT sc.empldapuserid FROM ".$schema."USERS sc WHERE sc.ID = dim.updated_user_id)";
        $up3 = DB::update($updt3Query);
        
        // $updt4Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.IS_EKYC = 'Y' where dim.PROOF_OF_IDENTITY = 9 ";
        // $up4 = DB::update($updt4Query);        

        $updt5Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.TIME_COUNTER =         
                                                TO_CHAR(TRUNC(to_number(TIME_COUNTER)/3600),'FM9900') || ':' ||
                                                TO_CHAR(TRUNC(MOD(to_number(TIME_COUNTER),3600)/60),'FM00') || ':' ||
                                                TO_CHAR(MOD(to_number(TIME_COUNTER),60),'FM00')
                                            WHERE TIME_COUNTER is not null";        
        $up5 = DB::update($updt5Query);
        
        $updt6Query = "UPDATE ".$schema."DIMENSION_REPORT SET 
                L1_TIME = case ROLE WHEN 3 THEN TIME_COUNTER ELSE '' END,
                L2_TIME = case ROLE WHEN 4 THEN TIME_COUNTER ELSE '' END, 
                QC_TIME = case ROLE WHEN 5 THEN TIME_COUNTER ELSE '' END, 
                AUDIT_TIME = case ROLE WHEN 6 THEN TIME_COUNTER ELSE '' END, 
                L3_TIME = case ROLE WHEN 8 THEN TIME_COUNTER ELSE '' END 
                ";
        $up6 = DB::update($updt6Query);

        $updt7Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.STATUS = INITCAP(STATUS), dim.TIME_COUNTER = ''";
        $up7 = DB::update($updt7Query);

        $updt8Query = "UPDATE ".$schema."DIMENSION_REPORT dim SET dim.DISCREPANCY_FIELDS = 
                    'C '|| dim.customer_id || ' A ' || dim.account_no || ' ' || dim.td_account_no where dim.customer_id is not null";
        $up8 = DB::update($updt8Query);


        /*
        L1_TIME, L2_TIME, QC_TIME, AUDIT_TIME, L3_TIME, 
        , */

        //----------------get data between date table-----------------\\

        $dimensionData = DB::table('DIMENSION_REPORT')->select('AOF_NUMBER','SOL_ID','BRANCH_NAME','CUSTOMER_NAME','ACCOUNT_TYPE','SCHEME_CODE_DESC',
                                                                'NO_OF_ACCOUNT_HOLDERS','CUSTOMER_TYPE','DELIGHT_SCHEME','IS_EKYC','ROLE',
                                                                'STATUS','CREATED_AT','TIME_COUNTER','L1_TIME','L2_TIME','QC_TIME',
                                                                'AUDIT_TIME','L3_TIME','UPDATED_USER_NAME','DISCREPANCY_COUNT',
                                                                'DISCREPANCY_FIELDS','FORM_ID','TD_SCHEME_CODE_DESC','CA_SCHEME_CODE_DESC')
                                                      ->orderBy('FORM_ID','asc')
                                                      ->orderBy('CREATED_AT','asc')
                                                      ->get()
                                                      ->toArray();
    

        $form_id = 0;


        $getRole = ['2'=>'Branch','3'=>'L1','4'=>'L2','5'=>'QC','6'=>'AU','8' =>'L3'];
            
        $getBranchName = DB::table('BRANCH')->select('BRANCH_ID','BRANCH_NAME')
                                            ->pluck('branch_name','branch_id');
        
        $accountTypeDesc = ['1' => 'Savings',
                            '2' => 'Current',
                            '3' => 'Term Deposit',
                            '4' => 'Savings & TD',
                            '5' => 'Delight Savings'];

        $fieldColumns = ['AOF','SOL','Branch','Name','AccountType','Scheme','Applicants','ETB/NTB','Delight','E-KYC','Role','Activity','SubmissionDate','TimeCounter','L1(M:S)','L2(M:S)','QC(M:S)','Audit(M:S)','L3(M:S)','User','DiscrepancyCount','DiscrepantComments'];

        $returnArray = array();

        //------------check data in selected date range--------------\\
            if(count($dimensionData) == 0){
                return $returnArray;
            }
        //--------------end--------------------\\
            
        array_push($returnArray,$fieldColumns);

        foreach($dimensionData as $key => $value){
            
            //$startTime = Carbon::parse($dimensionData['created_at']);
            if($value->form_id != $form_id){
                $form_id = $value->form_id;
                $aof = $value->aof_number;
                $startTime = Carbon::parse($value->created_at);
            }

            $currTime = Carbon::parse($value->created_at);                                       
            $timeCounter = $startTime->diff($currTime)->format('%D:%H:%I:%S');
            $timeCounter_m = $startTime->diff($currTime)->format('%M:%D:%H:%I:%S');

            unset($value->form_id);

            $saSchemeDesc = isset($dimensionData[$key]->scheme_code_desc) && $dimensionData[$key]->scheme_code_desc != ''?$dimensionData[$key]->scheme_code_desc:'';
            $tdSchemeDesc = isset($dimensionData[$key]->td_scheme_code_desc) && $dimensionData[$key]->td_scheme_code_desc != ''?$dimensionData[$key]->td_scheme_code_desc:''; 
            $caSchemeDesc = isset($dimensionData[$key]->ca_scheme_code_desc) && $dimensionData[$key]->ca_scheme_code_desc != ''?$dimensionData[$key]->ca_scheme_code_desc:'';

            if(substr($timeCounter_m, 0, 2)!='00'){ // If there is over a month diff then show month!
                $timeCounter = $timeCounter_m;  
            }  

            $dimensionData[$key]->scheme_code_desc = $saSchemeDesc." ".$tdSchemeDesc." ".$caSchemeDesc;
                            
            $dimensionData[$key]->account_type = isset($accountTypeDesc[$dimensionData[$key]->account_type]) && $accountTypeDesc[$dimensionData[$key]->account_type] != ''?$accountTypeDesc[$dimensionData[$key]->account_type]:'';

            $dimensionData[$key]->time_counter = $timeCounter;
            $dimensionData[$key]->aof_number = $aof;
            //$dimensionData[$key]->delight_scheme = $value->role != '' ? ($value->delight_scheme != '' ? 'Y' : 'N'):'';
            $dimensionData[$key]->role = isset($getRole[$value->role]) && $getRole[$value->role] != ''? $getRole[$value->role] : '';
            $dimensionData[$key]->branch_name = isset($getBranchName[$value->sol_id]) && $getBranchName[$value->sol_id] != ''? $getBranchName[$value->sol_id] : '';
            $dimensionData[$key]->discrepancy_fields = $dimensionData[$key]->discrepancy_fields == '' ? trim($dimensionData[$key]->discrepancy_fields) : $dimensionData[$key]->discrepancy_fields;

            

            array_push($returnArray, array_values((array) $dimensionData[$key]));
            
        } 
        return $returnArray;
    }

}
