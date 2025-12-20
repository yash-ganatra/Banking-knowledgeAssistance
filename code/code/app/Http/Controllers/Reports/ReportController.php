<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use DB;
use Schema;
use Cookie;
use Crypt;

class ReportController extends Controller
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

            if(in_array($this->roleId,[1,8,13,20])){
               $isAutherized = true;
            }else{
                $isAutherized = false;
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

    public  function l3Report()
    {
        return view('reports.l3report');
    }

    public  function l3ReportDetails(Request $request)
    {
        $requestData = $request->get('data');

        // echo "<pre>";print_r($requestData);exit;


        if (isset($requestData['startDate']) && $requestData['startDate'] != '') {
            $startDate = Carbon::parse($requestData['startDate']);
        }else{
            return json_encode(['status'=>'fail','msg'=>'Data not found for selected date range','data'=>'']);
        }
            
        $arrayToReport = Self::getdescreportData($startDate);
        if (count($arrayToReport) == 0) {
            return json_encode(['status'=>'fail','msg'=>'Data not found for selected date range','data'=>'']);
        }
        
        $users = DB::table('USERS')->select('ID', 'HRMSNO', 'EMP_FIRST_NAME', 'EMP_LAST_NAME')->get()->toArray();
        
        $users = json_decode(json_encode($users));
        
        $generatedReport = Self::showDataDumpHtml($arrayToReport, $users); 
        return json_encode(['status'=>'success','msg'=>'L3 Report fetched','data'=>$generatedReport]);
    }

    public  function getdescreportData($startDate)
    {
        // echo "<pre>";print_r($startDate);
        // echo "<pre>";print_r($endDate);exit;
        // $startDate = Carbon::now()->subDays($reportDays)->format('Y-m-d');   
        // $endDate =  Carbon::now()->subDays(46)->format('Y-m-d');
        
        $formsToCheck = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_DETAILS.ID','ACCOUNT_DETAILS.SCHEME_CODE','ACCOUNT_DETAILS.AOF_NUMBER','ACCOUNT_DETAILS.CUSTOMER_ID', 'ACCOUNT_DETAILS.ACCOUNT_NO','ACCOUNT_DETAILS.ACCOUNT_TYPE','ACCOUNT_DETAILS.CARD_TYPE','ACCOUNT_DETAILS.NO_OF_ACCOUNT_HOLDERS','ACCOUNT_DETAILS.NEXT_ROLE','ACCOUNT_DETAILS.DELIGHT_SCHEME','ACCOUNT_DETAILS.CREATED_AT','ACCOUNT_DETAILS.UPDATED_AT','ACCOUNT_DETAILS.MODE_OF_OPERATION','ACCOUNT_DETAILS.TD_SCHEME_CODE','ACCOUNT_DETAILS.TD_ACCOUNT_NO','ACCOUNT_DETAILS.FLOW_TYPE')->leftjoin('NPC_REVIEW_LOG','NPC_REVIEW_LOG.FORM_ID','ACCOUNT_DETAILS.ID')
                        ->where('ACCOUNT_DETAILS.EXTERNAL_ID',null)
                        ->whereDate('NPC_REVIEW_LOG.CREATED_AT', $startDate)
                        ->where('NPC_REVIEW_LOG.STATUS','approved')
                        ->where('NPC_REVIEW_LOG.ROLE_ID','4')
                        ->orderBy('ACCOUNT_DETAILS.ID')
                        ->get()->toArray();

        $arrayToReport = array();
        
        for($fid = 0; $fid < count($formsToCheck); $fid++){
            
            $currForm = $formsToCheck[$fid]->id;
            $mops = $formsToCheck[$fid]->mode_of_operation;
            $acctType = $formsToCheck[$fid]->account_type;
            $tdschemeId = $formsToCheck[$fid]->td_scheme_code;


            $ovd = DB::table('CUSTOMER_OVD_DETAILS')
                        ->select('QUERY_ID','AMOUNT','MOBILE_NUMBER','EMAIL','INITIAL_FUNDING_TYPE','SHORT_NAME','REFERENCE','CUSTOMER_ID','DEDUPE_STATUS','IS_NEW_CUSTOMER')
                        ->where('FORM_ID', $currForm)
                        ->orderBy('ID')
                        ->get()->toArray();

            $formsToCheck[$fid]->ovd = $ovd;
            $table = 'SCHEME_CODES';
            if($acctType == 3){
                $table = 'TD_SCHEME_CODES';
            }

            if($acctType == 2){
                $table = 'CA_SCHEME_CODES';
            }

            $schemeCode = DB::table($table)->select('SCHEME_CODE')->whereId($formsToCheck[$fid]->scheme_code)->get()->toArray();
            $schemeCode = (array) current($schemeCode);
            $schemeCode = isset($schemeCode['scheme_code']) && $schemeCode['scheme_code'] != ''?$schemeCode['scheme_code']:'';


            if($acctType == 4){
                $tdschemeCode = DB::table('TD_SCHEME_CODES')->select('SCHEME_CODE')->whereId($tdschemeId)->get()->toArray();
                $tdschemeCode = (array) current($tdschemeCode);
                $schemeCode = $tdschemeCode['scheme_code'].' / '.$schemeCode; 
            }  
            if($acctType == 2){
                if($formsToCheck[$fid]->scheme_code == '14'){
                    $formsToCheck[$fid]->scheme_code = '1';
                }
                $schemeCode = DB::table($table)->select('SCHEME_CODE')->whereId($formsToCheck[$fid]->scheme_code)->get()->toArray();
                $schemeCode = (array) current($schemeCode);
                $schemeCode = isset($schemeCode['scheme_code']) && $schemeCode['scheme_code'] != ''?$schemeCode['scheme_code']:'';
    
            }

            $fincon = DB::table('FINCON')->where('FORM_ID',$currForm)->get()->toArray();
            $fincon = current($fincon);                                                         
            // echo "<pre>";print_r($ovd);exit;
            $turnOver = DB::table('RISK_CLASSIFICATION_DETAILS')->select('ANNUAL_TURNOVER')
                                                                ->where('FORM_ID',$currForm)
                                                                ->orderBy('ID')
                                                                ->get()->toArray();

            $submissions = DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
                        ->select('ID', 'SUBMISSION_DATE','BRANCH_SUBMISSION','created_by')
                        ->where('FORM_ID', $currForm)                           
                        ->whereNull('ACCOUNT_OPENED')->whereNull('DISPATCH')->whereNull('COURIER')
                        ->whereNull('INWARD')->whereNull('ARCHIVAL')
                        ->orderBy('ID')
                        ->get()->toArray();
           
            $npc_log = DB::table('NPC_REVIEW_LOG')
                            ->select('ID','CREATED_AT','ROLE_ID')
                            ->where('FORM_ID', $currForm)
                            ->where('STATUS','approved')
                            ->where('ROLE_ID','4')
                            ->orderBy('ID')
                            ->get()->toArray();   

            $mop = DB::table('MODE_OF_OPERATIONS')->select('OPERATION_TYPE')
                                                ->where('ID',$mops)
                                                ->get()->toArray();
            $mop = current($mop)->operation_type;


            // // echo "<pre>";print_r($npc_log);exit;

                        
            if(count($ovd)>0){
                
                $formsToCheck[$fid]->mode_of_operation = $mop;     
                $formsToCheck[$fid]->npc_review = $npc_log;     
                $formsToCheck[$fid]->scheme_code = $schemeCode;     
                $formsToCheck[$fid]->funding_status = $fincon->funding_status;     
                $formsToCheck[$fid]->ftr_status = $fincon->ftr_status;     
                $br_npc_log = $npc_log;

                if(count($submissions)>0) {
                    $formsToCheck[$fid]->submissions = $submissions;            
                }else{
                    $formsToCheck[$fid]->submissions = array();         
                }
                
                if(count($turnOver)>0) {
                    $formsToCheck[$fid]->annual_turnover = $turnOver;            
                }else{
                    $formsToCheck[$fid]->annual_turnover = array();         
                }
                
                 // Add branch submission to npc entires and then sort on dates
                 
                 $tarray = json_decode(json_encode($submissions), true); 

                 $br_sub = array_filter($tarray, function ($arr) {
                        return $arr['branch_submission'] != null;
                 });
                    // echo "<pre>";print_r($br_sub);exit;
                                             
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
                                                                                        
        }  
        return $arrayToReport;
    }

    private function showDataDumpHtml($arrayToReport, $users)
    {
            $getBoth = false;                                               
            global $npcCounters;

            $arrayToExport = array();

                $html = '';
                $html .= '<style>table { font-family: arial, sans-serif; border-collapse: collapse; font-size: 12px;}td,th { border: 1px solid #dddddd; text-align: left; padding: 8px; }</style>';
                $html .=  '<table border="1px;" class="table table-custom" id="l3ReportExcel">';
                $html .= '<thead>';
                $html .=  '<tr class="sticky-header">';
                $html .=  '<th>Sr No</th>';          
                $html .=  '<th>AOF Number</th>';          
                $html .=  '<th>Last Updated At</th>';
                $html .=  '<th>Scheme Code</th>';
                $html .=  '<th>Account Type</th>';   
                $html .=  '<th>NTB/ETB</th>';                
                $html .=  '<th>Number of holders</th>';   
                $html .=  '<th>Mode of Operation</th>';    
                $html .=  '<th>Dedupe status</th>';
                $html .=  '<th>Dedupe QID</th>';   
                $html .=  '<th>Next Role</th>';              
                $html .=  '<th>Npcl2 Cleared Date</th>';                  
                $html .=  '<th>Funding type</th>';                   
                $html .=  '<th>Funding Status</th>';                   
                $html .=  '<th>FTR Status</th>';                   
                $html .=  '<th>Cheque/UTR Number</th>';
                $html .=  '<th>Amount</th>';
                $html .=  '<th>Mobile number</th>';
                $html .=  '<th>Email id</th>';
                $html .=  '<th>Card Type</th>';
                $html .=  '<th>Name on card</th>';
                $html .=  '<th>Expected turn over</th>';
                $html .=  '<th>Customer id</th>';
                $html .=  '<th>Account id</th>';
              
                
            $html .=  '</tr>';  
            $html .=  '</thead>';   


           
            for($ar = 0; $ar < count($arrayToReport); $ar++){
                $ovdRec = $arrayToReport[$ar]->ovd;
                $subm = $arrayToReport[$ar]->br_npc_log;
                $turnOver = $arrayToReport[$ar]->annual_turnover;
                // echo "<pre>";print_r($turnOver);exit;
                $mops = $arrayToReport[$ar]->mode_of_operation;
                // echo "<pre>";print_r($mops);exit;
               

                $acct_type = Self::getAccounType($arrayToReport[$ar]->account_type, $arrayToReport[$ar]->delight_scheme);

                //get role id 

                $role_id = Self::getRoleUser($arrayToReport[$ar]->next_role);

                $funding_type= "";
                if(isset(config('constants.INITIAL_FUNDING_TYPE')[$ovdRec[0]->initial_funding_type])){

                    $funding_type = config('constants.INITIAL_FUNDING_TYPE')[$ovdRec[0]->initial_funding_type];
                }
               
                $card_details=DB::table('CARD_RULES')->select('FINACLE_CODE')
                                                    ->where('ID',$arrayToReport[$ar]->card_type)
                                                    ->get()->toArray();
                $card_type='';
                if(count($card_details)>0)
                {
                    $card_type=$card_details[0]->finacle_code;
        
                }
                // echo "<pre>";print_r($arrayToReport[$ar]);
                $turn_over='';
                if(isset(config('constants.ANNUAL_TURNOVER')[$turnOver[0]->annual_turnover])){

                    $turn_over = config('constants.ANNUAL_TURNOVER')[$turnOver[0]->annual_turnover];
                    
                }
                            
                if(count($subm)==0) { 
                    continue;
                }   

                $created_at_toshow = carbon::parse($arrayToReport[$ar]->updated_at)->format('d-m-Y h:i:s' );

                // $L2_Cleared='';
                $L2_ClearedAt='';

                for($cl=0;$cl<count($subm);$cl++){
                    if(isset($subm[$cl]->role_id) && $subm[$cl]->role_id=="4")
                    {
                        $L2_ClearedAt=$subm[$cl]->created_at;
                    }
                }
                //check etb or ntb
                // echo "<pre>";print_r($ovdRec);exit;
                if(isset($arrayToReport[$ar]->flow_type) && $arrayToReport[$ar]->flow_type != ''){
                    $etb_ntb = $arrayToReport[$ar]->flow_type;
                }else{
                $etb_ntb = $ovdRec[0]->is_new_customer == 0 ? "ETB" : "NTB";
                }

                $srNo = $ar + 1;
                $acct_no = $arrayToReport[$ar]->account_no;
                if($acct_type == 'TD'){
                    $acct_no = $arrayToReport[$ar]->td_account_no;
                }

                if($acct_type == 'SATD'){
                    $acct_no = $acct_no.'/'.$arrayToReport[$ar]->td_account_no;
                }


                
                $html .=  '<tr style="vertical-align: top; border-top-style:inset; border-top-width:2px;">';
                        
                        $html .=  '<td>'.$srNo.'</td>';   
                        $html .=  '<td>'.$arrayToReport[$ar]->aof_number.'</td>';   
                        $html .=  '<td>'.$created_at_toshow.'</td>';
                        $html .=  '<td>'.$arrayToReport[$ar]->scheme_code.'</td>';
                        $html .=  '<td>'.$acct_type.'</td>';    
                        $html .=  '<td>'.$etb_ntb.'</td>';                 
                        $html .=  '<td>'.$arrayToReport[$ar]->no_of_account_holders.'</td>';
                        $html .=  '<td>'.$mops.'</td>';
                        $html .=  '<td>'.$ovdRec[0]->dedupe_status.'</td>'; 
                        $html .=  '<td>'.$ovdRec[0]->query_id.'</td>';   
                        $html .=  '<td>'.$role_id.'</td>';
                        $html .=  '<td>'.$L2_ClearedAt.'</td>';                  
                        $html .=  '<td>'.$funding_type.'</td>';
                        $html .=  '<td>'.$arrayToReport[$ar]->funding_status.'</td>';
                        $html .=  '<td>'.$arrayToReport[$ar]->ftr_status.'</td>';
                        $html .=  '<td>'.$ovdRec[0]->reference.'</td>';
                        $html .=  '<td>'.$ovdRec[0]->amount.'</td>'; 
                        $html .=  '<td>'.$ovdRec[0]->mobile_number.'</td>';                  
                        $html .=  '<td>'.$ovdRec[0]->email.'</td>';
                        $html .=  '<td>'.$card_type.'</td>';
                        $html .=  '<td>'.$ovdRec[0]->short_name.'</td>'; 
                        $html .=  '<td>'.$turn_over.'</td>'; 
                        $html .=  '<td>'.$ovdRec[0]->customer_id.'</td>';
                        $html .=  '<td>'.$acct_no.'</td>'; 
                
                $html .=  '</tr>';

                $tmpColOne = [$arrayToReport[$ar]->aof_number,$created_at_toshow,$arrayToReport[$ar]->scheme_code,$acct_type,$etb_ntb,$arrayToReport[$ar]->no_of_account_holders,$mops,$ovdRec[0]->dedupe_status, $ovdRec[0]->query_id,$role_id,$L2_ClearedAt, $funding_type,$arrayToReport[$ar]->funding_status,$arrayToReport[$ar]->ftr_status,$ovdRec[0]->reference,$ovdRec[0]->amount, $ovdRec[0]->mobile_number, $ovdRec[0]->email,$card_type,$ovdRec[0]->short_name, $turn_over, $ovdRec[0]->customer_id,$acct_no];

                $startTime = Carbon::parse($arrayToReport[$ar]->created_at);
                
                array_multisort(array_map('strtotime',array_column($subm,'created_at')), SORT_ASC, $subm);              
                $subm = json_decode(json_encode($subm), true);                                      
                
                    $arrayToExport[] = $tmpColOne; 
                
             
                  
             }       

             
            $html .=  '</table>';


            $html .= "<br><p><div class='display-none'>
                            <table id='l3ReportDataTable'>
                                    <thead>
                                        <tr>
                                            <th>AOF Number</th>                   
                                            <th>Last Updated At</th>
                                            <th>Scheme Code</th>  
                                            <th>Account Type</th> 
                                            <th>NTB/ETB</th>                
                                            <th>Number of holders</th> 
                                            <th>Mode of Operation</th>
                                            <th>Dedupe status</th>
                                            <th>Dedupe QID</th>   
                                            <th>Next Role</th>              
                                            <th>Npcl2 Cleared Date</th>                  
                                            <th>Funding type</th>  
                                            <th>Funding Status</th>                  
                                            <th>FTR Status</th>                                   
                                            <th>Cheque/UTR Number</th>
                                            <th>Amount</th>
                                            <th>Mobile number</th>
                                            <th>Email id</th>
                                            <th>Card Type</th>
                                            <th>Name on card</th>
                                            <th>Expected turn over</th>
                                            <th>Customer id</th>
                                            <th>Account id</th>
                                        </tr>
                                    </thead>
                                </table></div>";
                                
            $html .= '<script>
                var dtArray = '.json_encode($arrayToExport).';'
                .'$("#l3ReportDataTable").dataTable({
                            data:dtArray,
                            buttons: [{
                                extend : "excel",
                                text : "Export to Excel",
                                exportOptions : {
                                    modifier : {
                                        // DataTables core,
                                        selected: true,
                                        //order : "index",  // "current", "applied", "index",  "original"
                                        page : "all",      // "all",     "current"
                                        search : "none"     // "none",    "applied", "removed"
                                    }
                                }
                            }],
                            });'
                .'</script>';                   

            // echo "<pre>";print_r($html);exit;
            return $html;
    }

    public  function getAccounType($actType, $delightScheme){
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

    public  function getRoleUser($role_id){
        $response = $role_id;

        switch ($role_id) {
            case '1':
                $response = "Admin";
                break;
            case '5':
                $response = "Quality Checker";
                break;
            case '6':
                $response = "Auditor";
                break;
            case '7':
                $response = "Inward";
                break;
            case '8':
                $response = "L3 Reviewer";
                break;
            case '9':
                $response = "Archival";
                break;
            default:
                $response='';
                break;
        }
        return $response;
    }

    public function servicerequestreport(){
        
       return view('reports.amendReport');
    }

    public function getservicerequestdata(Request $request){

        try{
            if($request->ajax()){
                $requestData = $request->get('data');
                $startDate = Carbon::parse($requestData['startDate']);    
                $serviceRequestData =  DB::table('AMEND_QUEUE')->select('AMEND_QUEUE.ID','AMEND_QUEUE.CRF','AMEND_QUEUE.AMEND_ITEM','AMEND_QUEUE.CREATED_BY','AMEND_QUEUE.ACCOUNT_NO','AMEND_QUEUE.CREATED_AT','a.CRF_STATUS as status')
                                                            ->leftjoin('AMEND_MASTER as a','a.CRF_NUMBER','=','AMEND_QUEUE.CRF')
                                                            ->where('AMEND_QUEUE.CREATED_AT','>=',$startDate)
                                                            ->where(function($query){
                                                                $query->where('AMEND_QUEUE.FIELD_NAME','CARD_PIN')
                                                                      ->orWhere('AMEND_QUEUE.FIELD_NAME','MOBILE_ISSUANCE');
                                                            })
                                                            ->orderBy('AMEND_QUEUE.CREATED_AT','DESC')
                                                            ->get()
                                                            ->toArray();
                // echo "<pre>";print_r($serviceRequestData);exit;
                $getuserData =  DB::table('USERS')->select('ID',DB::raw("EMP_FIRST_NAME || ' ' || EMP_MIDDLE_NAME || ' ' || EMP_LAST_NAME as username"))
                                              ->pluck('username','id');
               
                $html = '';
                $html .= '<style>table { font-family: arial, sans-serif; border-collapse: collapse; font-size: 12px;}td,th { border: 1px solid #dddddd; text-align: left; padding: 8px; }</style>';
                $html .= '<table width="100%;" id="exportTableAmend">';
                $html .= '<tr style="color:white;">';
                $html .= '<th>ID</th>';
                $html .= '<th>CREATED DATE</th>';
                $html .= '<th>CRF NUMBER</th>';
                $html .= '<th>DESCRIPTION</th>';
                $html .= '<th>ACCOUNT NUMBER</th>';
                $html .= '<th>CRF STATUS</th>';
                $html .= '<th>CREATEDBY</th>';
                $html .= '</tr>';

                if(count($serviceRequestData) <= 0){
                    return json_encode(['status'=>'fail','msg'=>'No data found in Selected date','data'=>[]]);
                }

                for($serls=0;count($serviceRequestData)>$serls;$serls++){
                    $userName = isset($getuserData[$serviceRequestData[$serls]->created_by]) && $getuserData[$serviceRequestData[$serls]->created_by] != ''?$getuserData[$serviceRequestData[$serls]->created_by] : "";
                    $createdDate = Carbon::parse($serviceRequestData[$serls]->created_at)->format('d-m-Y H:i:s');
                    $crf_status =  config('amend_status.CRF_STATUS.'.$serviceRequestData[$serls]->status);
                    $html .= '<tr>';
                    $html .= '<td>'.$serviceRequestData[$serls]->id.'</td>';
                    $html .= '<td>'.$createdDate.'</td>';
                    $html .= '<td>'.$serviceRequestData[$serls]->crf.'</td>';
                    $html .= '<td>'.$serviceRequestData[$serls]->amend_item.'</td>';
                    $html .= '<td>'.$serviceRequestData[$serls]->account_no.'</td>';
                    $html .= '<td>'.$crf_status.'</td>';
                    $html .= '<td>'.$userName.'</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                
                return json_encode(['status'=>'success','msg'=>'Successfully fetch data.','data'=>$html]);
            }
        }catch(\Illuminate\Database\QueryException $e){
	    	if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
	    }
    }

    public function apiqueuereport(){
        $currentDate = Carbon::now();
        $lastdate = $currentDate->subDays(7)->format('Y-m-d');
        $getApiqueuedetails = DB::table('API_QUEUE')->select('API_QUEUE.ID','ACCOUNT_DETAILS.AOF_NUMBER','API_QUEUE.FORM_ID','API_QUEUE.MODULE','API_QUEUE.API','API_QUEUE.SEQUENCE','API_QUEUE.PARAMETER','API_QUEUE.STATUS','API_QUEUE.COMMENTS','API_QUEUE.CREATED_BY','API_QUEUE.UPDATED_BY','API_QUEUE.CREATED_AT','API_QUEUE.UPDATED_AT','API_QUEUE.CLASS','API_QUEUE.APPLICANT_NO','API_QUEUE.API_RESPONSE','API_QUEUE.RETRY','API_QUEUE.NEXT_RUN')
                                                    ->where('API_QUEUE.RETRY','>',3)
                                                    ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','API_QUEUE.FORM_ID')
                                                    ->where('API_QUEUE.CREATED_AT','>=',$lastdate)
                                                    ->get()
                                                    ->toArray();

        $getUserdetails = DB::table('USERS')->select('ID','EMPLDAPUSERID')->where('EMPSTATUS','Y')->pluck('empldapuserid','id')->toArray();
        $getColumndata = ['ID','AOF_NUMBER','FORM_ID','MODULE','API','SEQUENCE','PARAMETER','STATUS','COMMENTS','CREATED_BY','UPDATED_BY','CREATED_AT','UPDATED_AT','CLASS','APPLICANT_NO','API_RESPONSE','RETRY','NEXT_RUN'];
        $dataArray = [];

        if(count($getApiqueuedetails)>0){
            $dataArray[] = $getColumndata;
            for($i=0;count($getApiqueuedetails)>$i;$i++){
                $count = 0;
                foreach($getApiqueuedetails[$i] as $key => $value){
                    if(in_array($key,['parameter','api_response'])){
                        $dataArray[$i+1][$count] = base64_encode($value);
                    }elseif(in_array($key,['created_by','updated_by'])){
                        $dataArray[$i+1][$count] = isset($getUserdetails[$value]) && $getUserdetails[$value] != ''?$getUserdetails[$value]:'';
                    }else{
                        $dataArray[$i+1][$count] = $value;
                    }
                    $count++;
                }
            }
        }
       
        return view('reports.apiqueuelogreport')->with('apiqueuedata',$dataArray);
    }
}
?>
