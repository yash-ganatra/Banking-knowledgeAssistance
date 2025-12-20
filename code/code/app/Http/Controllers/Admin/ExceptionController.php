<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\ApiCommonFunctions;
use App\Helpers\Api;
use App\Helpers\EncryptDecrypt;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Session;
use Cookie;
use Crypt;
use DB;
use Carbon\Carbon;


class ExceptionController extends Controller
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
            $claims = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($claims),true);
            //get auditeeId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];
             
            if($this->roleId != 1){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/ExceptionController','exception','Unauthorized attempt detected by '.$this->userId,'','','1');

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
     * This function is fetch TD Scheme Codes
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
     public function exception(Request $request)
    {
        try {

            //returns tempalte
            $users = CommonFunctions::getusers();
            $log_refresh_timers = config('constants.LOG_REFRESH_TIMER');

           
            return view('admin.exception')->with('users',$users)
                                        ->with('log_refresh_timers',$log_refresh_timers);
                                                
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getexceptionlist(Request $request)
    {
        try {
            $maxLength = 50;
            if($request['length'] == -1)
            {
                $maxLength = $request['length'];
            }

            $array_column = [];

            $requestData = $request->get('data');
            $filteredColumns = ['EXCEPTION_LOG.ID','EXCEPTION_LOG.MODULE','EXCEPTION_LOG.FUNCTION_NAME','USERS.EMP_FIRST_NAME','EXCEPTION_LOG.AOF_NUMBER','EXCEPTION_LOG.FORM_ID','EXCEPTION_LOG.MESSAGE','EXCEPTION_LOG.CREATED_AT'];
            $i=0;
            foreach ($filteredColumns as $column) {
                if($column == "EXCEPTION_LOG.MESSAGE"){
                        array_push($array_column, strtolower('EXCEPTION_LOG.MESSAGE'));
                        $dt[$i] = array( 'db' => strtolower('EXCEPTION_LOG.MESSAGE'),'dt' => $i,
                            'formatter' => function( $d, $row ) use($maxLength) {
                                $html = '';
                                if(strlen($row->message) > $maxLength){
                                    // $service_request = json_encode(htmlentities($row->service_request));
                                    $message = htmlentities($row->message);
                                    $html .= "<span class='mytooltip'>";
                                        $html .= "<span type='button' class='tooltipp service_modal' data-title='<pre>$message</pre>' data-type='Message' data-toggle='modal' data-target='#service_modal'>";
                                            $html .= trim(substr($row->message,0,$maxLength))."...";
                                        $html .= "</span>";
                                    $html .= "</span>";
                                }else{
                                    $html .= $row->message;
                                }
                                return $html;
                            }
                        );
                    }
                else{
                    array_push($array_column,$column);

                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;    
                }                
                $i++;              
            }
            // $dt_obj = (new SSP('EXCEPTION_LOG', $dt));
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj =  DB::table('EXCEPTION_LOG')->select($array_column);

            $dt_obj = $dt_obj->leftJoin('USERS','USERS.ID','EXCEPTION_LOG.USER_ID');
            //$dt_obj = $dt_obj->leftJoin('USER_ROLES','USER_ROLES.ID','USERS.ROLE');
            //checks user name is empty or not
            if($requestData['users'] != '')
            {
                $dt_obj = $dt_obj->where('EXCEPTION_LOG.USER_ID',$requestData['users']);
            }
            $dt_obj = $dt_obj->orderBy('ID', 'DESC');
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData(); 
            $dd['items'] = array_map(fn($items) => array_values($items),$dd['items']);
            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }        
    }

    public function getFullExLog() 
    {
    try{

                $logDate = Carbon::now()->format('Y-m-d');                                          
                $blacklist = "ldap|credentials";
                $fileName = storage_path("logs/laravel-".$logDate.".log");
                echo '<pre>';
                foreach(file($fileName) as $line) {
                    if(preg_match("/($blacklist)/", $line)) {
                            //print_r($line);
                    }else{                          
                            print_r($line);
                    }
                }
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
	
	public function getExLog() 
    {
    try{

			$logDate = Carbon::now()->format('Y-m-d');											
			$blacklist = "ldap|credentials";
			$fileName = storage_path("logs/laravel-".$logDate.".log");
			echo '<pre>';
			foreach(file($fileName) as $line) {
				if(preg_match("/($blacklist)/", $line)) {
						//print_r($line);
				}else{						
                    if(preg_match("/(^\[|\#0 |\#1 |\#2 |\#3 |\#4 |\#5 )/", $line)) {
                        if(preg_match("/(^\[)/", $line) && !preg_match("/(^\[stac)/", $line)) {
                            print_r('<br>');
                        }
						print_r($line);
                    }

				}
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function debugInfo($aof) 
    {
        try {
                 $acctDetails = DB::table('ACCOUNT_DETAILS')->where('AOF_NUMBER',$aof)
                                                            ->get()->toArray();

               
                $formId = $acctDetails[0]->id;
                $delightId = $acctDetails[0]->delight_kit_id;
                // echo '<pre>';print_r($delightkit);
                $delightdetail = DB::table('DELIGHT_KIT')->whereId($delightId)->get()->toArray();
                $delighthistory = DB::table('DKIT_STATUS_HISTORY')->where('DKIT_ID',$delightId)->get()->toArray();
                $ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $risk = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $declaration = DB::table('CUSTOMER_DECLARATIONS')->where('FORM_ID',$formId)->get()->toArray();
                $fincon = DB::table('FINCON')->where('FORM_ID',$formId)->get()->toArray();
				$review = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->get()->toArray();
				$nominee = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $status = DB::table('STATUS_LOG')->where('FORM_ID',$formId)->get()->toArray();
                $npc_review = DB::table('NPC_REVIEW_LOG')->where('FORM_ID',$formId)->get()->toArray();
				$l1_edit_log = DB::table('L1_EDIT_LOG')->where('FORM_ID',$formId)->get()->toArray();
                $entity = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $risk_class_details = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $aadhar_mask = DB::table('AADHAAR_MASKED_LOGS')->where('FORM_ID',$formId)->get()->toArray();
                $pan_details = DB::table('PAN_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $clearance = DB::table('CLEARANCE')->where('FORM_ID',$formId)->get()->toArray();
                $etb_cust_details = DB::table('ETB_CUST_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $sub_declaration_fields = DB::table('SUBMISSION_DECLARATION_FIELDS')->where('FORM_ID',$formId)->get()->toArray();
                $encrypt_api_service_log = DB::table('ENCRYPTED_API_SERVICE_LOG')->select('ID','SERVICE_NAME','SERVICE_URL','CREATED_BY','CREATED_AT','FORM_ID','API_RESPONSE_TIME','BRANCH_ID')
                                                                                -> where('FORM_ID',$formId)->get()->toArray();

				
                $response = array(
                    'account' => $acctDetails,
                    'ovd' => $ovd,  
                    'risk' => $risk,
                    'declaration' => $declaration,
                    'fincon' => $fincon,
					'review' => $review,
					'nominee' => $nominee,
					'status' => $status,
                    'npc_review' => $npc_review,
					'l1_edit_log' => $l1_edit_log,
                    'entity' => $entity,
                    'risk_class_details' => $risk_class_details,
                    'aadhar_mask' => $aadhar_mask,
                    'pan_details' => $pan_details,
                    'clearance' => $clearance,
                    'etb_cust_details' => $etb_cust_details,
                    'sub_declaration_fields' => $sub_declaration_fields,
                    'encrypt_api_service_log' => $encrypt_api_service_log,
                    'delightdetail' => $delightdetail,
                    'delighthistory'=>$delighthistory, 
                );
                
                return \Response::json($response, 200, array(), JSON_PRETTY_PRINT);
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function debugInfoAmend($crf) 
    {
        try {

                $master = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crf)->get()->toArray();
                $formId = $master[0]->id;
                $queue = DB::table('AMEND_QUEUE')->where('CRF',$crf)->get()->toArray();
            $proofs = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crf)->get()->toArray();
            $review= DB::table('AMEND_REVIEW_TABLE')->where('CRF_NUMBER',$crf)->get()->toArray(); 
            $amendapi= DB::table('AMEND_API_QUEUE')->where('CRF_NUMBER',$crf)->get()->toArray(); 
            $amendstatus= DB::table('AMEND_STATUS_LOG')->where('CRF_NUMBER',$crf)->get()->toArray();                              
				
                $response = array(
                    'master' => $master,
                    'queue' => $queue,  
                'proofs' => $proofs,
                'review'=> $review,
                'amendapi'=> $amendapi, 
                'amendstatus'=> $amendstatus,                  
                );
                
                return \Response::json($response, 200, array(), JSON_PRETTY_PRINT);
            
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function debugInfoTrace($aof) {
        $response = Self::debugInfo($aof);
        $response = json_decode(json_encode($response),true); 
        $account = $response['original']['account'];
        $ovd = $response['original']['ovd'];
        $risk = $response['original']['risk'];
        $declaration = $response['original']['declaration'];
        $fincon = $response['original']['fincon'];
        $review = $response['original']['review'];        
        $nominee = $response['original']['nominee'];        
        $status = $response['original']['status'];
        $npc_review = $response['original']['npc_review'];

        $entries = Array();        

        
        $aofStatus = DB::table('AOF_STATUS')->select('ID','STATUS')->where('IS_ACTIVE',1)->get()->toArray();
        $statusArray=Array();
        foreach($aofStatus as $stat){
            $statusArray[$stat->id] = $stat->status;
        }

        $roles = DB::table('USER_ROLES')->select('ID','ROLE')->get()->toArray();
        $roleArray=Array();
        foreach($roles as $role){
            $roleArray[$role->id] = $role->role;
        }

        //echo '<pre>'; print_r($statusArray); print_r($statusArray[3]); exit; 

        // Date | Role | User | Activity | Additional Info
        // Account
        echo '<pre>';
        array_push($entries, [ $account[0]['created_at'], '', $account[0]['created_by'], 'FORM_CREATED', '']);
        array_push($entries, [ $account[0]['updated_at'], '', $account[0]['updated_by'], 'FORM_UPDATED', '']);
        // OVD
        for($appl=0; $appl<count($ovd); $appl++){
            array_push($entries, [ $ovd[$appl]['created_at'], '', $ovd[$appl]['created_by'], 'OVD_CREATED', 'APPLICANT_'.$ovd[$appl]['applicant_sequence']]);
            array_push($entries, [ $ovd[$appl]['updated_at'], '', $ovd[$appl]['updated_by'], 'OVD_UPDATED', 'APPLICANT_'.$ovd[$appl]['applicant_sequence']]);
        }
        //Declaration
        for($appl=0; $appl<count($declaration); $appl++){
            array_push($entries, [ $declaration[$appl]['created_at'], '', $declaration[$appl]['created_by'], 'DECLARATION_CREATED', $declaration[$appl]['declaration_type']]);
            if($declaration[$appl]['updated_at'] != $declaration[$appl]['created_at']){
                array_push($entries, [ $declaration[$appl]['updated_at'], '', $declaration[$appl]['updated_by'], 'DECLARATION_UPDATED', $declaration[$appl]['declaration_type']]);
            }
        }
        //Fincon
        array_push($entries, [ $fincon[0]['created_date'], '', $fincon[0]['created_by'], 'FINCON_CREATED', 'FundingDate: '.$fincon[0]['funding_date'].' Status: '.$fincon[0]['funding_status']]);
        //Review
        for($rec=0; $rec<count($review); $rec++){
            array_push($entries, [ $review[$rec]['created_at'], $roleArray[$review[$rec]['role_id']], $review[$rec]['created_by'], 'DESC_MARKED', $review[$rec]['column_name']]);
            if($review[$rec]['updated_at'] != $review[$rec]['created_at']){
                array_push($entries, [ $review[$rec]['updated_at'], $roleArray[$review[$rec]['role_id']], $review[$rec]['updated_by'], 'DESC_UPDATED', $review[$rec]['column_name']]);
            }
        }
        //Status
        for($rec=0; $rec<count($status); $rec++){
            array_push($entries, [ $status[$rec]['created_at'], $roleArray[$status[$rec]['role']], $status[$rec]['created_by'], 'FORM_STATUS', $statusArray[$status[$rec]['status']]]);
            if($status[$rec]['updated_at'] != $status[$rec]['created_at']){
                array_push($entries, [ $status[$rec]['updated_at'], $roleArray[$status[$rec]['role']], '', 'FORM_STATUS', $status[$rec]['status']]);
            }
        }
        //NPC_Review
        for($rec=0; $rec<count($npc_review); $rec++){
            array_push($entries, [ $npc_review[$rec]['created_at'], $roleArray[$npc_review[$rec]['role_id']], $npc_review[$rec]['created_by'], 'NPC_PROCESS', $npc_review[$rec]['status']]);            
        }

        //echo '<pre>'; print_r(count($entries));

        $entries2 = Array();
        $userIds = Array(); // To capture UserIDs 
        for($rep=0; $rep<count($entries); $rep++){

            $record = $entries[$rep];            
            $entries2[$rep]['TS'] = $record[0];
            $data=Array();
            for($itm=1; $itm<count($record); $itm++){
                if($itm==2 && $record[$itm] != '') array_push($userIds,$record[$itm]);
                array_push($data,$record[$itm]);
            }
            $entries2[$rep]['DATA']=$data;
            
        }
        $columns = array_column($entries2, 'TS');
        array_multisort($columns, SORT_ASC, $entries2);
                
        
        $userIds = array_unique($userIds); 
        $users = DB::table('USERS')->select('ID','EMP_FIRST_NAME')->whereIn('ID',$userIds)->get()->toArray();            

        $userArray=Array();
        foreach($users as $user){
            $userArray[$user->id] = $user->emp_first_name; 
        }
                
        echo '<html><table border=0 width="100%">';
            echo '<tr><td width="10%">AOF</td><td>'.$account[0]['aof_number'].' ('.$account[0]['id'].')</td></tr>';
            echo '<tr><td width="10%">L1 Review</td><td>'.(empty($account[0]['l1_review'])?'N':'Y').'</td></tr>';
            echo '<tr><td width="10%">L2 Review</td><td>'.(empty($account[0]['l2_review'])?'N':'Y').'</td></tr>';
            echo '<tr><td width="10%">QC Review</td><td>'.(empty($account[0]['qc_review'])?'N':'Y').'</td></tr>';
            echo '<tr><td width="10%">AU Review</td><td>'.(empty($account[0]['audit_review'])?'N':'Y').'</td></tr>';
            echo '<tr><td width="10%">Next_Role</td><td>'.$roleArray[$account[0]['next_role']].'</td></tr>';
            echo '<tr><td width="10%">Signature Flag</td><td>'.$account[0]['signature_flag'].'</td></tr>';
            for($ovdRec=0; $ovdRec<count($ovd); $ovdRec++){                
                echo '<tr><td width="10%">ISAMAINT ('.($ovdRec+1).')</td><td>'.$ovd[$ovdRec]['kyc_update'].'</td></tr>';
            }
        echo '</table><br>';

        echo '<table border=0 width="100%">';
        for($rep=0; $rep<count($entries2); $rep++){
            if($entries2[$rep]['DATA'][2]=='NPC_PROCESS'){
                $rowColor = 'lightgrey';
            }else{
                $rowColor = 'white';
            }
            echo '<tr style="background-color:'.$rowColor.'"><td>'.$entries2[$rep]['TS'].'</td>';
            for($itm=0; $itm<count($entries2[$rep]['DATA']); $itm++){
                if(isset($userArray[$entries2[$rep]['DATA'][$itm]])){
                    $strToShow = ($itm==1 && $entries2[$rep]['DATA'][$itm] != '') ? $userArray[$entries2[$rep]['DATA'][$itm]] : $entries2[$rep]['DATA'][$itm];
                }else{
                    $strToShow = $entries2[$rep]['DATA'][$itm];
                }
                echo '<td>'.$strToShow.'</td>';
            }
            echo '</tr>';
        }
        echo '</table></html>';
    }

    // public function amenddebugTest()
    // {
    //     try{
    //        if(env('APP_SETUP') == 'PRODUCTION'){
    //             return '';
    //         }
    //         $amendDebugInfoJson = '';
    //         $amendData = json_decode($amendDebugInfoJson,true);
    //         $master = $amendData['master'];
    //         $amendqueue = $amendData['queue'];
    //         $proof = $amendData['proofs'];

    //         $masteramend = current($master);
    //         echo "<pre>";print_r($master);
            
    //         unset($masteramend['id']);
    //         unset($masteramend['cache_data']);

    //         $crfId = DB::table('AMEND_MASTER')->insertGetId($masteramend);
    //             if($crfId){
    //                 $masteramendLob = current($master);
    //                 $updateBlob = DB::table('AMEND_MASTER')->whereId($crfId)->updateLob(
    //                                     ['UPDATED_AT'=> Carbon::now()],                    
    //                                 ['CACHE_DATA' => $masteramendLob['cache_data']]);
    //             }
    //         DB::commit();
    //         echo "<pre>";print_r($crfId);

    //         for($i=0; $i<count($amendqueue); $i++){
    //             $queue = $amendqueue[$i];
    //             unset($queue['crf_id']);
    //             $queue['crf_id'] = $crfId;
    //             $queueInsert = DB::table('AMEND_QUEUE')->insertGetId($queue);

    //             DB::commit();
    //         }
    //         echo "<pre>";print_r('Records Inserted');

            

    //     }catch(\Illuminate\Database\QueryException $e) {
    //         return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    //     }
    // }

    // public function debugTest() 
    // {
    //     try {			
	// 			if(env('APP_SETUP') == 'PRODUCTION'){
	// 					return '';
	// 			}	
						
	// 		$debugInfoJson = '';
			
	// 		$data = json_decode($debugInfoJson, true);

    //         echo '<pre>';print_r($data);exit;
	// 		$acct = $data['account'];
	// 		$ovd = $data['ovd'];
	// 		$risk = $data['risk'];
	// 		$declaration = $data['declaration'];
	// 		$fincon = $data['fincon'];
	// 		$review = $data['review'];
	// 		$status = $data['status'];
    //         $npcReview = $data['npc_review'];
    //         $entity = $data['entity'];
    //         $risk_class_details = $data['risk_class_details'];
	// 		$aadhar_mask = $data['aadhar_mask'];
    //         $pan_details = $data['pan_details'];
    //         $clearance = $data['clearance'];
    //         $etb_cust_details = $data['etb_cust_details'];
    //         $sub_declaration_fields = $data['sub_declaration_fields'];
    //         $encrypt_api_service_log = $data['encrypt_api_service_log'];

	// 		$acct = current($acct);
			
	// 		unset($acct['id']);
	// 		$formId = DB::table('ACCOUNT_DETAILS')->insertGetId($acct);
	// 		DB::commit();
			
	// 		echo '<pre>'; print_r($formId); 
			
	// 		for($i=0; $i<count($ovd); $i++){
	// 			$ov = $ovd[$i];
	// 			unset($ov['id']);
	// 			$ov['form_id'] = $formId;
	// 			$acctID = DB::table('CUSTOMER_OVD_DETAILS')->insertGetId($ov);
	// 			DB::commit();
			
	// 			$ri = $risk[$i];
	// 			unset($ri['id']);
	// 			$ri['form_id'] = $formId;
	// 			$ri['account_id'] = $acctID;
	// 			DB::table('RISK_CLASSIFICATION_DETAILS')->insert($ri);
	// 			DB::commit();				
	// 		}
				
	// 		for($i=0; $i<count($declaration); $i++){
	// 			$decl = $declaration[$i];
	// 			unset($decl['id']);
	// 			$decl['form_id'] = $formId;
	// 			DB::table('CUSTOMER_DECLARATIONS')->insert($decl);
	// 			DB::commit();										
	// 		}

	// 		$fincon = current($fincon);
			
	// 		unset($fincon['id']);
	// 		$fincon['form_id'] = $formId;
	// 		DB::table('FINCON')->insert($fincon);
	// 		DB::commit();			
			
	// 		for($i=0; $i<count($review); $i++){
	// 			$rvw = $review[$i];
	// 			unset($rvw['id']);
	// 			$rvw['form_id'] = $formId;
	// 			DB::table('REVIEW_TABLE')->insert($rvw);
	// 			DB::commit();										
	// 		}

    //         for($i=0; $i<count($npcReview); $i++){
    //             $npcReviews = $npcReview[$i];
    //             unset($npcReviews['id']);
    //             $npcReviews['form_id'] = $formId;
    //             DB::table('NPC_REVIEW_LOG')->insert($npcReviews);
    //             DB::commit();                                       
    //         }

	// 		for($i=0; $i<count($status); $i++){
	// 			$st = $status[$i];
	// 			unset($st['id']);
	// 			$st['form_id'] = $formId;
	// 			DB::table('STATUS_LOG')->insert($st);
	// 			DB::commit();										
	// 		}

    //         for($i=0; $i<count($data['l1_edit_log']); $i++){
    //             $l1_log = $data['l1_edit_log'][$i];
    //             unset($l1_log['id']);
    //             $l1_log['form_id'] = $formId;
    //             DB::table('L1_EDIT_LOG')->insert($l1_log);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['entity']); $i++){
    //             $ety = $data['entity'][$i];
    //             unset($ety['id']);
    //             $ety['form_id'] = $formId;
    //             DB::table('ENTITY_DETAILS')->insert($ety);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['risk_class_details']); $i++){
    //             $rcd = $data['risk_class_details'][$i];
    //             unset($rcd['id']);
    //             $rcd['form_id'] = $formId;
    //             DB::table('RISK_CLASSIFICATION_DETAILS')->insert($rcd);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['aadhar_mask']); $i++){
    //             $ad_mask = $data['aadhar_mask'][$i];
    //             unset($ad_mask['id']);
    //             $ad_mask['form_id'] = $formId;
    //             DB::table('AADHAAR_MASKED_LOGS')->insert($ad_mask);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['pan_details']); $i++){
    //             $pan_info = $data['pan_details'][$i];
    //             unset($pan_info['id']);
    //             $pan_info['form_id'] = $formId;
    //             DB::table('AADHAAR_MASKED_LOGS')->insert($pan_info);
    //             DB::commit();                                       
    //         }


    //         for($i=0; $i<count($data['clearance']); $i++){
    //             $clear = $data['clearance'][$i];
    //             unset($clear['id']);
    //             $clear['form_id'] = $formId;
    //             DB::table('AADHAAR_MASKED_LOGS')->insert($clear);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['etb_cust_details']); $i++){
    //             $etb = $data['etb_cust_details'][$i];
    //             unset($etb['id']);
    //             $etb['form_id'] = $formId;
    //             DB::table('AADHAAR_MASKED_LOGS')->insert($etb);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['sub_declaration_fields']); $i++){
    //             $sub_field = $data['sub_declaration_fields'][$i];
    //             unset($sub_field['id']);
    //             $sub_field['form_id'] = $formId;
    //             DB::table('AADHAAR_MASKED_LOGS')->insert($sub_field);
    //             DB::commit();                                       
    //         }

    //         for($i=0; $i<count($data['encrypt_api_service_log']); $i++){
    //             $api_log = $data['encrypt_api_service_log'][$i];
    //             unset($api_log['id']);
    //             $api_log['form_id'] = $formId;
    //             DB::table('AADHAAR_MASKED_LOGS')->insert($api_log);
    //             DB::commit();                                       
    //         }
	// 		echo '<pre>'; print_r($acct); print_r($ovd); exit;
            
    //     }
    //     catch(\Illuminate\Database\QueryException $e) {
    //         return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    //     }
    // }

	public function debugDBstruct($code, $schema) 
    {
        try {
				$computedCode = Carbon::now()->format('dmYHi');
				$computedCode = substr(hash('sha256',$computedCode),0,5);
				
				if($code != $computedCode || $schema=='') return;								

                $tabcols = DB::table('ALL_TAB_COLS')->where('OWNER',$schema)
					->select('TABLE_NAME', 'COLUMN_NAME', 'DATA_TYPE', 'DATA_LENGTH')
					->orderBy('TABLE_NAME', 'ASC')->orderBy('COLUMN_NAME', 'ASC')
					->get()->toArray();
                
				//echo '<pre>'; print_r($tabcols); 
                return \Response::json($tabcols, 200, array(), JSON_PRETTY_PRINT);
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

	public function debugMasterData($code, $table, $records) 
    {
        try {

				$computedCode = Carbon::now()->format('dmYHi');
				$computedCode = substr(hash('sha256',$computedCode),0,5);
				
				if($code != $computedCode || $table=='' || $records == '' || $records > 200) return;								

                $tabdata = DB::table($table)->take($records)
					->get()->toArray();
                
				//echo '<pre>'; print_r($tabcols); 
                return \Response::json($tabdata, 200, array(), JSON_PRETTY_PRINT);
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


public static function imagesdirect($aof, $type='all',$role='') 
    {   

        $view='YES';
        if($aof=='TODAYS'){
            return self::checkForTodaysIamge();
        }
        $allFiles = array();	
        $formId = DB::table('ACCOUNT_DETAILS')->select('ID')->where('AOF_NUMBER', $aof)->get()->toArray();
		$formId = current($formId); $formId = $formId->id;
		
		$attach = $signed = $marked = $deleted = $l3 = array();
		if($type == 'attach' || $type == 'all'){
			$attach = Self::listFolderFiles(storage_path('uploads/attachments/'.$formId), 'ATTACH',$view,$role);
            // $allFiles['ATTACH']=$attach;          
          
		}
		if($type == 'signed' || $type == 'all'){
			$signed = Self::listFolderFiles(storage_path('uploads/attachments/'.$formId.'/signed'), 'SIGNED',$view,$role);
            // $allFiles['SIGNED']=$signed;         
           
		}
		if($type == 'marked' || $type == 'all'){
			$marked = Self::listFolderFiles(storage_path('uploads/markedattachments/'.$formId), 'MARKED',$view,$role);	
            $allFiles['MARKED']=$marked;
		}
		if($type == 'deleted' || $type == 'all'){
			$deleted = Self::listFolderFiles(storage_path('uploads/markedattachments/'.$formId.'/deleted'), 'DELETED',$view,$role);
            $allFiles['DELETED']=$deleted;	
		}
		if($type == 'l3' || $type == 'all'){
			$l3 = Self::listFolderFiles(storage_path('uploads/markedattachments/'.$formId.'/L3'), 'L3',$view,$role);
            $allFiles['L3']=$l3;		
		}
		
        // echo '<pre>'; print_r('$allFiles');exit;
        if($role >= 4)
        {           
            return $allFiles;
        }else
        {
		exit;
        }
		
    }
	
    function checkForTodaysIamge(){

        $todayDate =  Carbon::now()->subDays(2);
        //$tomDate =  Carbon::now()->addDays(1);
        $tomDate =  Carbon::now();
        //$formIds = DB::table('ACCOUNT_DETAILS')->select('ID', 'AOF_NUMBER')
        $formIds = DB::table('STATUS_LOG')->select('FORM_ID', 'STATUS', 'CREATED_AT')
                    ->whereIn('STATUS', [2,10,21])
                    ->where('CREATED_AT', '>=', $todayDate)
                    ->where('CREATED_AT', '<=', $tomDate)                    
                    ->orderBy('CREATED_AT')
                    ->get()->toArray();
        echo '<pre>';
        for($d=0; $d < count($formIds); $d++){
            $formId = $formIds[$d]->form_id;            
            echo PHP_EOL.'----------------------------------------'.PHP_EOL;
            echo ' TIMESTAMP: '.$formIds[$d]->created_at.' FormID: '.$formId.' Status:'.$formIds[$d]->status.PHP_EOL;				
            echo '----------------------------------------'.PHP_EOL;
            Self::echoDirFiles(storage_path('uploads/attachments/'.$formId), 'ATTACH');	
            Self::echoDirFiles(storage_path('uploads/attachments/'.$formId.'/signed'), 'SIGNED');	
            Self::echoDirFiles(storage_path('uploads/markedattachments/'.$formId), 'MARKED');	
            //Self::echoDirFiles(storage_path('uploads/markedattachments/'.$formId.'/deleted'), 'DELETED');	
            //Self::echoDirFiles(storage_path('uploads/markedattachments/'.$formId.'/L3'), 'L3');	            
        }                    

    }

    function echoDirFiles($dir, $type, $view='YES'){

        //echo '  TYPE: '.$type.PHP_EOL;
        
        if(!file_exists($dir)) { //echo '<ol>Not found!'.'</ol>'; 
            echo '  TYPE: '.$type.'  Directory not found: '.$dir.PHP_EOL;
            return;
        }

        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1)
            return;

        foreach($ffs as $ff){            
            if(is_dir($dir.'/'.$ff)) {
                // Self::listFolderFiles($dir.'/'.$ff);
            }else{
                $size =  filesize($dir.'/'.$ff);                
                echo '  TYPE: '.$type.' > FILE: '.$ff.' SIZE: '.$size.' '.PHP_EOL;				
            }
        }
    }
	
	public static function listFolderFiles($dir, $type, $view='YES',$role){
		
		if(!file_exists($dir)) { //echo '<ol>Not found!'.'</ol>'; 
			return; 
		}
		$ffs = scandir($dir);

		unset($ffs[array_search('.', $ffs, true)]);
		unset($ffs[array_search('..', $ffs, true)]);

		// prevent empty ordered elements
		if (count($ffs) < 1)
			return;

		$fileArray = [];
        $count=0;

		foreach($ffs as $ff){           
			
			if(is_dir($dir.'/'.$ff)) 
            {
				// Self::listFolderFiles($dir.'/'.$ff);
			}
            else
            {
                if($role >= 4)
                {                

                    $fileArray['IMAGE'][$count]=$ff; 
                    $fileArray['PATH'] = $dir;
                       $count++;                           
                }
                else
                {
                   
				array_push($fileArray, $ff);
				echo '<pre><b> '.$type.' > '.$ff.' </b></pre>'.PHP_EOL;				
				$imageData =  file_get_contents($dir.'/'.$ff);
                $dataUri = 'data:image/png;base64,' . base64_encode($imageData);
				echo '<img src="'.$dataUri.'"/>';
                    echo '<hr>';
			}
				 
		}
	}
        if($role >= 4)
        {          
            return $fileArray;
        }
		
	}

	public static function genPdf($aof='')
    {
	
		if($aof=='') return '';		
		
        $command = 'command:GeneratePdf';
		$artisan = \Artisan::call($command,['--aof'=>$aof]);
		$output = \Artisan::output();
		return $output;
			
	}

    public static function encodeDebugUser($uid, $givenHash=''){
        $computedCode = Carbon::now()->format('dmYHi');
		$computedCode = substr(hash('sha256',$uid.'AIS'.$computedCode),0,6);
		if($givenHash=='' && env('APP_SETUP')=='DEV'){
            echo '<pre>'.$computedCode;		
        }else{
            return ($givenHash == $computedCode) ? true : false ;
        }
    }

    public function checkGamTable($customerId){
       CommonFunctions::getAccountDetailsBasedOnCustID($customerId);        
    }

    public function showoldforms($days=30)    
    {
        $sqlQuery = DB::table('ACCOUNT_DETAILS')
                            ->select('ACCOUNT_DETAILS.ID', 'ACCOUNT_DETAILS.AOF_NUMBER', 'ACCOUNT_DETAILS.CREATED_AT', 
                                            'ACCOUNT_DETAILS.UPDATED_AT', 'ACCOUNT_DETAILS.BRANCH_ID', 
                                            'ACCOUNT_DETAILS.CREATED_BY', 'ACCOUNT_DETAILS.AOF_NUMBER', 'ACCOUNT_DETAILS.APPLICATION_STATUS',
                                            'CUSTOMER_OVD_DETAILS.CUSTOMER_ID','ACCOUNT_DETAILS.ACCOUNT_NO', 'ACCOUNT_DETAILS.TD_ACCOUNT_NO'
                                    )
            ->leftjoin('CUSTOMER_OVD_DETAILS', 'CUSTOMER_OVD_DETAILS.FORM_ID', 'ACCOUNT_DETAILS.ID')
            //->whereNull('CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
            //->whereNull('ACCOUNT_DETAILS.ACCOUNT_NO')
            ->where('ACCOUNT_DETAILS.NEXT_ROLE', 2)
            //->where('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE',1)
            ->where('ACCOUNT_DETAILS.CREATED_AT', '<=', Carbon::now()->subDays($days))
            ->where('ACCOUNT_DETAILS.UPDATED_AT', '<=', Carbon::now()->subDays($days))
            ->whereNotIn('APPLICATION_STATUS', [4, 5, 11, 12, 16, 26, 45])
            ->get()->toArray();
        $formcount = 0;
        $formarraysize = count($sqlQuery);
        if ($formarraysize == 0) {
            echo "No forms found!";
        } else {

            $statusArray = config('constants.APPLICATION_STATUS');            
            $csvdata = "FORM ID, AOF, CREATED AT,UPDATED AT,BRANCH ID,AOF NUMBER,APPLICATION STATUS, CUST_ID, ACCOUNT_NO, TD_ACCOUNT_NO, CAN_BE_DELETED";
            for ($i = 0; $i < $formarraysize; $i++) {
                $can_be_deleted = strlen(trim($sqlQuery[$i]->customer_id . $sqlQuery[$i]->account_no . $sqlQuery[$i]->td_account_no)) > 0 ? 'N' : 'Y';
                $data = "\n" . $sqlQuery[$i]->id . ',' . $sqlQuery[$i]->aof_number . ',' . date('d-m-Y H:i', strtotime($sqlQuery[$i]->created_at)) . ',' 
                                    . strval($sqlQuery[$i]->updated_at) . ',' . $sqlQuery[$i]->branch_id . ',' . strval($sqlQuery[$i]->aof_number) . ',' 
                                    . $statusArray[$sqlQuery[$i]->application_status] . ','.  $sqlQuery[$i]->customer_id .','
                                    . $sqlQuery[$i]->account_no . ',' . $sqlQuery[$i]->td_account_no. ','. $can_be_deleted;

                $csvdata = $csvdata . $data;
                $formcount = $formcount + 1;
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="Old_Forms.csv"');

            echo $csvdata;
        }
    }

    public function rejectoldforms($days)
    {
        $sqlQuery = DB::table('ACCOUNT_DETAILS')
                                ->select('ACCOUNT_DETAILS.ID', 'ACCOUNT_DETAILS.CREATED_AT', 
                                                'ACCOUNT_DETAILS.UPDATED_AT', 'ACCOUNT_DETAILS.BRANCH_ID', 
                                                'ACCOUNT_DETAILS.CREATED_BY', 'ACCOUNT_DETAILS.AOF_NUMBER', 'ACCOUNT_DETAILS.APPLICATION_STATUS',
                                                'CUSTOMER_OVD_DETAILS.CUSTOMER_ID','ACCOUNT_DETAILS.ACCOUNT_NO', 'ACCOUNT_DETAILS.TD_ACCOUNT_NO'
                                        )
                        ->leftjoin('CUSTOMER_OVD_DETAILS', 'CUSTOMER_OVD_DETAILS.FORM_ID', 'ACCOUNT_DETAILS.ID')
                        //->whereNull('CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
                        //->whereNull('ACCOUNT_DETAILS.ACCOUNT_NO')
                        ->where('ACCOUNT_DETAILS.NEXT_ROLE', 2)
                        //->where('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE',1)
                        ->where('ACCOUNT_DETAILS.CREATED_AT', '<=', Carbon::now()->subDays($days))
                        ->where('ACCOUNT_DETAILS.UPDATED_AT', '<=', Carbon::now()->subDays($days))
                        ->whereNotIn('APPLICATION_STATUS', [4, 5, 11, 12, 16, 26, 45])
                        ->get()->toArray();

        if (count($sqlQuery) == 0) {
            echo "No forms to delete!";
            return true;
        } else {
            $formcount = 0;
            for ($i = 0; $i < count($sqlQuery); $i++) {
                $can_be_deleted = strlen(trim($sqlQuery[$i]->customer_id . $sqlQuery[$i]->account_no . $sqlQuery[$i]->td_account_no)) > 0 ? 'N' : 'Y';
                if($can_be_deleted == 'Y'){
                    DB::table('ACCOUNT_DETAILS')->whereId($sqlQuery[$i]->id)->update(['APPLICATION_STATUS' => 45 , "IS_ACTIVE"=>'1']);
                    DB::table('STATUS_LOG')->insert(['FORM_ID'=>$sqlQuery[$i]->id, 'ROLE'=>1, 'STATUS' => 45, 'COMMENTS'=>'Auto Rejected' ]);
                    $formcount = $formcount + 1;
                }

            }

            echo strval($formcount). " form(s) rejected successfully";
            return true;
        }
    }

    public function rejectoldETBforms($days)
    {
        $sqlQuery = DB::table('ACCOUNT_DETAILS')
                                ->select('ACCOUNT_DETAILS.ID', 'ACCOUNT_DETAILS.CREATED_AT', 
                                                'ACCOUNT_DETAILS.UPDATED_AT', 'ACCOUNT_DETAILS.BRANCH_ID', 
                                                'ACCOUNT_DETAILS.CREATED_BY', 'ACCOUNT_DETAILS.AOF_NUMBER', 'ACCOUNT_DETAILS.APPLICATION_STATUS', 'CUSTOMER_OVD_DETAILS.IS_NEW_CUSTOMER','ACCOUNT_DETAILS.DELIGHT_SCHEME',
                                                'CUSTOMER_OVD_DETAILS.CUSTOMER_ID','ACCOUNT_DETAILS.ACCOUNT_NO', 'ACCOUNT_DETAILS.TD_ACCOUNT_NO'
                                        )
                        ->leftjoin('CUSTOMER_OVD_DETAILS', 'CUSTOMER_OVD_DETAILS.FORM_ID', 'ACCOUNT_DETAILS.ID')
                        //->whereNull('CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
                        //->whereNull('ACCOUNT_DETAILS.ACCOUNT_NO')
                        ->where('ACCOUNT_DETAILS.NEXT_ROLE', 2)
                        //->where('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE',1)
                        ->where('ACCOUNT_DETAILS.CREATED_AT', '<=', Carbon::now()->subDays($days))
                        ->where('ACCOUNT_DETAILS.UPDATED_AT', '<=', Carbon::now()->subDays($days))
                        ->where('CUSTOMER_OVD_DETAILS.IS_NEW_CUSTOMER', 0)
                        ->whereNotIn('APPLICATION_STATUS', [4, 5, 11, 12, 16, 26, 45])
                        ->get()->toArray();

        if (count($sqlQuery) == 0) {
            echo "No forms to delete!";
            return true;
        } else {
            $formcount = 0;
            for ($i = 0; $i < count($sqlQuery); $i++) {

                if($sqlQuery[$i]->delight_scheme != ''){              
                    DB::table('ACCOUNT_DETAILS')->whereId($sqlQuery[$i]->id)->update(['APPLICATION_STATUS' => 45,'IS_ACTIVE'=>1]);
                    DB::table('STATUS_LOG')->insert(['FORM_ID'=>$sqlQuery[$i]->id, 'ROLE'=>1, 'STATUS' => 45, 'COMMENTS'=>'Auto Rejected' ]);
                    $formcount = $formcount + 1;
                }
                else{               
                    $can_be_deleted = strlen(trim($sqlQuery[$i]->account_no . $sqlQuery[$i]->td_account_no)) > 0 ? 'N' : 'Y';

                    if($can_be_deleted == 'Y' ){
                        DB::table('ACCOUNT_DETAILS')->whereId($sqlQuery[$i]->id)->update(['APPLICATION_STATUS' => 45,'IS_ACTIVE'=>1]);
                        DB::table('STATUS_LOG')->insert(['FORM_ID'=>$sqlQuery[$i]->id, 'ROLE'=>1, 'STATUS' => 45, 'COMMENTS'=>'Auto Rejected' ]);
                        $formcount = $formcount + 1;
                    }

                }              
            }
            echo strval($formcount). " form(s) rejected successfully";
            return true;
        }
    }

    public function rejectNeverSentForms($days=30)
    {
        
        if($days<30){ // Force min 30 days required
            echo "Forms created in last 30 days can not be rejected!"; exit;
        }

        $sqlQuery = DB::table('ACCOUNT_DETAILS')
                                ->select('ACCOUNT_DETAILS.ID', 'ACCOUNT_DETAILS.CREATED_AT', 
                                                'ACCOUNT_DETAILS.UPDATED_AT', 'ACCOUNT_DETAILS.BRANCH_ID', 
                                                'ACCOUNT_DETAILS.CREATED_BY', 'ACCOUNT_DETAILS.AOF_NUMBER', 'ACCOUNT_DETAILS.APPLICATION_STATUS',
                                                'ACCOUNT_DETAILS.ACCOUNT_NO', 'ACCOUNT_DETAILS.TD_ACCOUNT_NO'
                                        )
                        ->where('ACCOUNT_DETAILS.NEXT_ROLE', null)
                        ->where('ACCOUNT_DETAILS.CREATED_AT', '<=', Carbon::now()->subDays($days))
                        ->where('ACCOUNT_DETAILS.UPDATED_AT', '<=', Carbon::now()->subDays($days))
                        ->where('ACCOUNT_DETAILS.L1_COUNTER', 0)                        
                        ->where('ACCOUNT_DETAILS.L2_COUNTER', 0)                        
                        ->where('APPLICATION_STATUS', 1)
                        ->get()->toArray();

        
        if (count($sqlQuery) == 0) {
            echo "No forms to delete!";
            return ;
        } else {
            $formcount = 0;
            echo '<pre>';
            for ($i = 0; $i < count($sqlQuery); $i++) {
                $can_be_deleted = strlen(trim($sqlQuery[$i]->account_no . $sqlQuery[$i]->td_account_no)) > 0 ? 'N' : 'Y';
                if($can_be_deleted == 'Y'){
                    DB::table('ACCOUNT_DETAILS')->whereId($sqlQuery[$i]->id)->update(['APPLICATION_STATUS' => 45]);
                    DB::table('STATUS_LOG')->insert(['FORM_ID'=>$sqlQuery[$i]->id, 'ROLE'=>1, 'STATUS' => 45, 'COMMENTS'=>'Auto Rejected' ]);
                    $formcount = $formcount + 1;
                    echo strval($formcount).". FORM_ID: ".$sqlQuery[$i]->id." AOF: ". $sqlQuery[$i]->aof_number. " rejected  <br>";
                }

            }

            echo "TOTAL ".strval($formcount). " form(s) rejected successfully";
            return ;
        }
    }


    // repayment api insert into api queue one time call

    public function repaymentApiinsertQueue($formId){

        try{
            
            $getAccountdetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                            ->where('ACCOUNT_TYPE',3)
                                                            ->where('TD_ACCOUNT_NO','!=',NULL)
                                                            ->count();
            
            if($getAccountdetails > 0){

                $apiQueueCheck = DB::table('API_QUEUE')->where('FORM_ID',$formId)
                                                    ->where('API','=','repaymentTDAccountWrapper')
                                                    ->count();
                if($apiQueueCheck > 0){
                    echo "<html>
                            <body>
                                <label>Already insert into api queue.</label>
                            </body>
                        </html>";
                }else{
                    $getUserDetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_DETAILS.TD_ACCOUNT_NO','CUSTOMER_OVD_DETAILS.ACCOUNT_NUMBER','CUSTOMER_OVD_DETAILS.MATURITY_IFSC_CODE')
                                                                ->leftJoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
                                                                ->where('ACCOUNT_DETAILS.ID',$formId)
                                                                ->get()
                                                                ->toArray();
                    $customerDetails = (array) current($getUserDetails);

                    
                    $ifscCode = $customerDetails['maturity_ifsc_code'];
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','repaymentTDAccountWrapper','Term Deposit',null,null,Array($customerDetails['td_account_no'],$customerDetails['account_number'],$ifscCode,$formId),Carbon::now()->addMinutes(2));
                    echo "<html>
                            <body>
                                <label>Successfully Inserted into api queue.</label>
                            </body>
                        </html>";
                }
            }else{
                echo "<html>
                                <body>
                                    <label>No record in process.Please try another FormId.</label>
                                </body>
                            </html>";
            }
        }catch(\Illuminate\Database\QueryException $e){
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



    public function uploadDebugInfo($formId = '',$aof_number ='',$acct ='',$ovd='') 
    {
        try {

            if(env('APP_SETUP') == 'PRODUCTION' || env('APP_SETUP')== 'UAT'){
                return '';
            }else{
                // echo $formId;exit;
                return view('admin.uploaddebuginfo')->with('formId',$formId)
                                                    ->with('aof_number',$aof_number)
                                                    ->with('acct_details',$acct)
                                                    ->with('ovd_details',$ovd);

            }	
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
    

    public function debugTest(Request $request) 
    {
        try {			
            if(env('APP_SETUP') == 'PRODUCTION' || env('APP_SETUP') == 'UAT'){
                return '';
            }else{
                
                $requestData = $request->get('data');
                // echo '<pre>';print_r($requestData);exit;

                if($requestData == ''){
                    return json_encode(['status'=>'fail','msg'=>'Please upload text file','data'=>[]]);
                }else{

                    $debugInfoJson = $requestData['debugInfo'];
                    
                    $data = json_decode($debugInfoJson, true);
                    $acct =(isset($data['account']) && $data['account'] != '') ? $data['account'] : [];
                    $ovd = (isset($data['ovd']) && $data['ovd'] != '') ? $data['ovd'] : [];
                    $risk = (isset($data['risk']) && $data['risk'] != '') ? $data['risk'] : [];
                    $declaration = (isset($data['declaration']) && $data['declaration'] != '') ? $data['declaration'] : [];
                    $fincon = (isset($data['fincon']) && $data['fincon'] != '') ? $data['fincon'] : [];
                    $review = (isset($data['review']) && $data['review'] != '') ? $data['review'] : [];
                    $status = (isset($data['status']) && $data['status'] != '') ? $data['status'] : [];
                    $npcReview =(isset($data['npc_review']) && $data['npc_review'] != '') ? $data['npc_review'] : [];
                    $entity = (isset($data['entity']) && $data['entity'] != '') ? $data['entity'] : [];
                    $risk_class_details = isset($data['risk_class_details']) && $data['risk_class_details'] != '' ? $data['risk_class_details'] : [];
                    $aadhar_mask = isset($data['aadhar_mask']) && $data['aadhar_mask'] != '' ? $data['aadhar_mask'] : [];
                    $pan_details = isset($data['pan_details']) && $data['pan_details'] != '' ? $data['pan_details'] : [];
                    $clearance = isset($data['clearance']) && $data['clearance'] != '' ? $data['clearance'] : [];
                    $etb_cust_details = isset($data['etb_cust_details']) && $data['etb_cust_details'] != '' ? $data['etb_cust_details'] : [];
                    $sub_declaration_fields = isset($data['sub_declaration_fields']) && $data['sub_declaration_fields'] != '' ? $data['sub_declaration_fields'] : [];
                    $encrypt_api_service_log =isset($data['encrypt_api_service_log']) && $data['encrypt_api_service_log'] != '' ? $data['encrypt_api_service_log'] : [];
                    $delightdetail = (isset($data['delightdetail']) && $data['delightdetail'] != '') ? $data['delightdetail'] : [];
                    $delighthistory = (isset($data['delighthistory']) && $data['delighthistory'] !='')? $data['delighthistory'] : [];
                    
                    
                    $acct = (array)current($acct);
                    
                    unset($acct['id']);
                    $formId = DB::table('ACCOUNT_DETAILS')->insertGetId($acct);
                    DB::commit();

                    $acctID = DB::table('DELIGHT_KIT')->insert($delightdetail);
                    DB::commit();

                    // $delighthistory = current($delighthistory);
                    
                    for($i=0; $i<count($ovd); $i++){
                        $ov = $ovd[$i];
                        unset($ov['id']);
                        $ov['form_id'] = $formId;
                        $acctID = DB::table('CUSTOMER_OVD_DETAILS')->insertGetId($ov);
                        DB::commit();
                        
                        $ri = $risk[$i];
                        unset($ri['id']);
                        $ri['form_id'] = $formId;
                        $ri['account_id'] = $acctID;
                        DB::table('RISK_CLASSIFICATION_DETAILS')->insert($ri);
                        DB::commit();				
                    }
                    
                    for($i=0; $i<count($declaration); $i++){
                        $decl = $declaration[$i];
                        unset($decl['id']);
                        $decl['form_id'] = $formId;
                        DB::table('CUSTOMER_DECLARATIONS')->insert($decl);
                        DB::commit();										
                    }
        
                    $fincon = current($fincon);
                    
                    unset($fincon['id']);
                    $fincon['form_id'] = $formId;
                    DB::table('FINCON')->insert($fincon);
                    DB::commit();			
                    
                    for($i=0; $i<count($review); $i++){
                        $rvw = $review[$i];
                        unset($rvw['id']);
                        $rvw['form_id'] = $formId;
                        DB::table('REVIEW_TABLE')->insert($rvw);
                        DB::commit();										
                    }
        
                    for($i=0; $i<count($npcReview); $i++){
                        $npcReviews = $npcReview[$i];
                        unset($npcReviews['id']);
                        $npcReviews['form_id'] = $formId;
                        DB::table('NPC_REVIEW_LOG')->insert($npcReviews);
                        DB::commit();                                       
                    }
        
                    for($i=0; $i<count($status); $i++){
                        $st = $status[$i];
                        unset($st['id']);
                        $st['form_id'] = $formId;
                        DB::table('STATUS_LOG')->insert($st);
                        DB::commit();										
                    }
        
                    for($i=0; $i<count($data['l1_edit_log']); $i++){
                        $l1_log = $data['l1_edit_log'][$i];
                        unset($l1_log['id']);
                        $l1_log['form_id'] = $formId;
                        DB::table('L1_EDIT_LOG')->insert($l1_log);
                        DB::commit();                                       
                    }
        

                    for($i=0; $i<count($entity); $i++){
                        $ety = $entity[$i];
                        unset($ety['id']);
                        $ety['form_id'] = $formId;
                        DB::table('ENTITY_DETAILS')->insert($ety);
                        DB::commit();                                       
                    }

        
                    for($i=0; $i<count($risk_class_details); $i++){
                        $rcd = $risk_class_details[$i];
                        unset($rcd['id']);
                        $rcd['form_id'] = $formId;
                        DB::table('RISK_CLASSIFICATION_DETAILS')->insert($rcd);
                        DB::commit();                                       
                    }
        
                    for($i=0; $i<count($aadhar_mask); $i++){
                        $ad_mask = $aadhar_mask[$i];
                        unset($ad_mask['id']);
                        $ad_mask['form_id'] = $formId;
                        DB::table('AADHAAR_MASKED_LOGS')->insert($ad_mask);
                        DB::commit();                                       
                    }
        
                    for($i=0; $i<count($pan_details); $i++){
                        $pan_info = $pan_details[$i];
                        unset($pan_info['id']);
                        $pan_info['form_id'] = $formId;
                        DB::table('PAN_DETAILS')->insert($pan_info);
                        DB::commit();                                       
                    }

        
                    for($i=0; $i<count($clearance); $i++){
                        $clear = $clearance[$i];
                        unset($clear['id']);
                        $clear['form_id'] = $formId;
                        DB::table('CLEARANCE')->insert($clear);
                        DB::commit();                                       
                    }

        
                    for($i=0; $i<count($etb_cust_details); $i++){
                        $etb = $etb_cust_details[$i];
                        unset($etb['id']);
                        $etb['form_id'] = $formId;
                        DB::table('ETB_CUST_DETAILS')->insert($etb);
                        DB::commit();                                       
                    }
                    
        
                    for($i=0; $i<count($sub_declaration_fields); $i++){
                        $sub_field = $sub_declaration_fields[$i];
                        unset($sub_field['id']);
                        $sub_field['form_id'] = $formId;
                        DB::table('SUBMISSION_DECLARATION_FIELDS')->insert($sub_field);
                        DB::commit();                                       
                    }
        
                    for($i=0; $i<count($encrypt_api_service_log); $i++){
                        $api_log = $encrypt_api_service_log[$i];
                        unset($api_log['id']);
                        $api_log['form_id'] = $formId;
                        DB::table('ENCRYPTED_API_SERVICE_LOG')->insert($api_log);
                        DB::commit();                                       
                    }


                    // echo '<pre>';print_r($formId);
                    // echo '<pre>'; print_r($acct['aof_number']); print_r($ovd); exit;

                    if($formId != ''){
                        return json_encode(['status'=>'success','msg'=>'Success','data'=>['formId'=>$formId, 'aof_number'=>$acct['aof_number'], 'acct_details'=> $acct, 'ovd_details' => $ovd]]);
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
                    }

                }
               
            }	
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    
    public function amenddebugTest(Request $request)
    {
        try{
           if(env('APP_SETUP') == 'PRODUCTION' || env('APP_SETUP') == 'UAT'){
                return '';
            }else{

                $requestData = $request->get('data');
                // echo '<pre>';print_r($requestData);exit;

                if($requestData == ''){
                    return json_encode(['status'=>'fail','msg'=>'Please upload text file','data'=>[]]);
                }else{

                    $amendDebugInfoJson = $requestData['amendDebugInfo'];
                    $amendData = json_decode($amendDebugInfoJson,true);
                    // echo '<pre>';print_r($amendData);exit;

                    $master = $amendData['master'];
                    $amendqueue = $amendData['queue'];
                    $proof = $amendData['proofs'];
                    $review = $amendData['review'];
                    $amendapi = $amendData['amendapi'];
                    $amendstatus = $amendData['amendstatus'];
    
                    $masteramend = current($master);
    
                    unset($masteramend['id']);
                    unset($masteramend['cache_data']);
        
                    $crfId = DB::table('AMEND_MASTER')->insertGetId($masteramend);
                   
                    if($crfId){
                        $masteramendLob = current($master);
                        $updateBlob = DB::table('AMEND_MASTER')->whereId($crfId)->updateLob(
                                            ['UPDATED_AT'=> Carbon::now()],                    
                                        ['CACHE_DATA' => $masteramendLob['cache_data']]);
                    }
                    DB::commit();
        
                    for($i=0; $i<count($amendqueue); $i++){
                        $queue = $amendqueue[$i];
                        unset($queue['crf_id']);
                        $queue['crf_id'] = $crfId;
                        $queueInsert = DB::table('AMEND_QUEUE')->insertGetId($queue);
        
                        DB::commit();
                    }

                    for($i=0; $i<count($proof); $i++){
                        $pro = $proof[$i];
                        unset($pro['crf_id']);
                        $pro['crf_id'] = $crfId;
                        $proofInsert = DB::table('AMEND_PROOF_DOCUMENT')->insertGetId($pro);
        
                        DB::commit();
                    }
                    
                    for($i=0; $i<count($review); $i++){
                        $rev = $review[$i];
                        unset($rev['crf_id']);
                        $rev['crf_id'] = $crfId;
                        $proofInsert = DB::table('AMEND_REVIEW_TABLE')->insertGetId($rev);
        
                        DB::commit();
                    }

                    for($i=0; $i<count($amendapi); $i++){
                        $ameapi = $amendapi[$i];
                        unset($ameapi['crf_id']);
                        $ameapi['crf_id'] = $crfId;
                        $proofInsert = DB::table('AMEND_API_QUEUE')->insertGetId($ameapi);
        
                        DB::commit();
                    }

                    for($i=0; $i<count($amendstatus); $i++){
                        $amestatus = $amendstatus[$i];
                        unset($amestatus['id']);
                        $amestatus['id'] = $crfId;
                        $proofInsert = DB::table('AMEND_STATUS_LOG')->insertGetId($amestatus);
        
                        DB::commit();
                    }

                    // echo '<pre>';print_r($masteramend);exit;

                    if($crfId != ''){
                        return json_encode(['status'=>'success','msg'=>'Success','data'=>['crfId'=>$crfId, 'crf_number'=> $masteramend['crf_number'],'amend_master'=>$masteramend,'amend_queue'=>$amendqueue]]);
                    }else{
                        return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
                    }

                    

                }

            }

            

        }catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }




}
?>
