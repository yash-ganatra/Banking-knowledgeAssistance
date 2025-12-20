<?php

namespace App\Http\Controllers\NPC;

use App\Http\Controllers\Controller;
use SoulDoit\DataTable\SSP;
use App\Helpers\Rules;
use App\Helpers\CommonFunctions;
use App\Helpers\freezeUnfreezeApi;
use App\Helpers\ApiCommonFunctions;
use App\Helpers\OaoCommonFunctions;
use App\Helpers\Api;
use App\Helpers\AmendApi;
use App\Helpers\CurrentApi;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Crypt,Cache,Session;
use Cookie;
use DB;
use Intervention\Image\Facades\Image;
use File;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\ExceptionController;
use Illuminate\Support\Facades\Schema;
use App\Helpers\EncryptDecrypt;

class ReviewController extends Controller
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
        //checks token exists or not
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
                $saveuserlog = CommonFunctions::createUserLogDirect('NPC/ReviewController','review','Unauthorized attempt detected by '.$this->userId,'','','1');

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
     * Method Name: review
     * Created By : Sharanya T
     * Created At : 24-03-2020

     * Description:
     * Method to show npc review template
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function review(Request $request)
    {
        try {
            $tokenParams = Cookie::get('token');
            $encrypt_key = substr($tokenParams, -5); 
            $is_review = 0;
            $role = Session::get('role');
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $formId = base64_decode($decodedString);
            $reviewDetails = array();
            $qcReviewDetails = array();
			$lastSavedComments = array();
            $AOFStatus = 3;
            $deDupeStatusButton = true;
            $dedupe_check = env('DEDUPE_CHECK');
    
            $accountType = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE')->where('ID',$formId)->get()->toArray();
            $accountType = (array) current($accountType);
            
            if(isset($accountType['account_type']) &&  $accountType['account_type'] != "" && $accountType['account_type'] == 2 ){
                
            $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->select('ACCOUNT_DETAILS.*','ACCOUNT_TYPES.ID as ACCOUNT_TYPE_ID','ACCOUNT_TYPES.ACCOUNT_TYPE',
                                                'ACCOUNT_LEVEL_TYPES.ACCOUNT_LEVEL_TYPE','MODE_OF_OPERATIONS.OPERATION_TYPE
                                                     as MODE_OF_OPERATION','CA_SCHEME_CODES.SCHEME_CODE as SCHEME_CODE',
                                                     'TD_SC.SCHEME_CODE as TD_SCHEME_CODE','TDSC.SCHEME_CODE as TDSCHEME_CODE')
                                        ->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE')
                                        ->leftjoin('ACCOUNT_LEVEL_TYPES','ACCOUNT_LEVEL_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_LEVEL_TYPE')
                                        ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                        ->leftjoin('CA_SCHEME_CODES','CA_SCHEME_CODES.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                        ->leftjoin('TD_SCHEME_CODES as TDSC','TDSC.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                        ->leftjoin('TD_SCHEME_CODES as TD_SC','TD_SC.ID','ACCOUNT_DETAILS.TD_SCHEME_CODE')
                                        ->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY')
                                        ->where('ACCOUNT_DETAILS.ID',$formId)
                                        ->get()->toArray();
            }else{
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                        ->select('ACCOUNT_DETAILS.*','ACCOUNT_TYPES.ID as ACCOUNT_TYPE_ID','ACCOUNT_TYPES.ACCOUNT_TYPE',
                                                    'ACCOUNT_LEVEL_TYPES.ACCOUNT_LEVEL_TYPE','MODE_OF_OPERATIONS.OPERATION_TYPE
                                                 as MODE_OF_OPERATION','SCHEME_CODES.SCHEME_CODE as SCHEME_CODE',
                                                 'TD_SC.SCHEME_CODE as TD_SCHEME_CODE','TDSC.SCHEME_CODE as TDSCHEME_CODE')
                                    ->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE')
                                    ->leftjoin('ACCOUNT_LEVEL_TYPES','ACCOUNT_LEVEL_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_LEVEL_TYPE')
                                    ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                    ->leftjoin('SCHEME_CODES','SCHEME_CODES.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                    ->leftjoin('TD_SCHEME_CODES as TDSC','TDSC.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                    ->leftjoin('TD_SCHEME_CODES as TD_SC','TD_SC.ID','ACCOUNT_DETAILS.TD_SCHEME_CODE')
                                    ->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
            }
            $accountDetails = (array) current($accountDetails);
            $accountDetails['other_proof'] = explode(',', $accountDetails['other_proof']);
						


            $declarationsAll = DB::table('CUSTOMER_DECLARATIONS')->select('CUSTOMER_DECLARATIONS.*','DECLARATIONS.*')
                                               ->leftjoin('DECLARATIONS','DECLARATIONS.ID','CUSTOMER_DECLARATIONS.DECLARATION_ID')
                                               ->where('FORM_ID',$formId)
                                               ->where('is_active',1)
                                               ->get()->toArray();
            $declarations = [];
			$level3notesimages = [];
			// Remove L3 Updates from regular declarations 
			for($itr=0; $itr < count($declarationsAll); $itr++){				
				if(substr($declarationsAll[$itr]->blade_id,0,10) != 'l3_update_'){
					array_push($declarations, $declarationsAll[$itr]);
				}else{
					array_push($level3notesimages, $declarationsAll[$itr]);					
				}
			}		
			

            if(in_array($role,[3,4])) // Extra Dedupe checks for L1
            {
                $deDupeStatusButton = CommonFunctions::checkDedupeStatus($formId);
                 
            }

           
            if(($accountDetails['source'] == 'CC') && ($accountDetails['is_new_customer'] == 0) && ($accountDetails['account_type_id'] == 3) && ($role == 4)) // Extra Dedupe checks for L2 Call center Process
            {
                 $deDupeStatusButton = CommonFunctions::checkDedupeStatus($formId);
                 
            }

            $bankTable = DB::table('BANK')->pluck('bank_name',"id")->toArray();
            $countryTable = DB::table('COUNTRIES')->pluck('name',"id")->toArray();
            $relationshipTable = DB::table('RELATIONSHIP')->pluck('description',"id")->toArray();
            // $residentalStatus = config('constants.RESIDENTIAL_STATUS');
            $residentalStatus = Array(  '1' => 'Resident',
                                        '2' => 'Non Resident Indian',
                                        '3' => 'Foreign National',
                                        '4' => 'Person of Indian Origin');

                /*
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                ->select('CUSTOMER_OVD_DETAILS.*','RESIDENTIAL_STATUS.RESIDENTIAL_STATUS','OVD_TYPES.ID as PROOF_OF_IDENTITY_ID',
                    'OVD_TYPES.OVD as PROOF_OF_IDENTITY','A.OVD as PROOF_OF_ADDRESS','CA.OVD as PROOF_OF_CURRENT_ADDRESS',
                    'BANK.BANK_NAME as BANK_NAME','TITLE.DESCRIPTION as TITLE',
                    'COUNTRIES.NAME as PER_COUNTRY','CC.NAME as CURRENT_COUNTRY', 'relationship')
                ->leftjoin('RESIDENTIAL_STATUS','RESIDENTIAL_STATUS.ID','CUSTOMER_OVD_DETAILS.RESIDENTIAL_STATUS')
                ->leftjoin('OVD_TYPES','OVD_TYPES.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_IDENTITY')
                ->leftjoin('OVD_TYPES as A','A.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_ADDRESS')
                ->leftjoin('OVD_TYPES as CA','CA.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_CURRENT_ADDRESS')
                ->leftjoin('BANK','CUSTOMER_OVD_DETAILS.BANK_NAME','BANK.ID')
                ->leftjoin('TITLE','CUSTOMER_OVD_DETAILS.TITLE','TITLE.ID')
                ->leftjoin('COUNTRIES','CUSTOMER_OVD_DETAILS.PER_COUNTRY','COUNTRIES.ID')
                ->leftjoin('COUNTRIES as CC','CUSTOMER_OVD_DETAILS.CURRENT_COUNTRY','CC.ID')
                ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','CUSTOMER_OVD_DETAILS.RELATIONSHIP')
                ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                ->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','ASC')
                ->get()->toArray();

                if (isset($customerOvdDetails[0]->relationship) && $customerOvdDetails[0]->relationship != '') {
                $relationship = DB::table('RELATIONSHIP')->where('IS_CHEQUE_RELATION', 1)
                                                        ->where('code', str_pad($customerOvdDetails[0]->relationship, 3, '0', STR_PAD_LEFT))
                                                        ->get()->toArray();

                $relationship = current($relationship);
                $customerOvdDetails[0]->relationship = $relationship->display_description;
                }
                */

                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                        ->select('CUSTOMER_OVD_DETAILS.*','OVD_TYPES.ID as PROOF_OF_IDENTITY_ID',
                        'OVD_TYPES.OVD as PROOF_OF_IDENTITY','A.OVD as PROOF_OF_ADDRESS','CA.OVD as PROOF_OF_CURRENT_ADDRESS',
                        'TITLE.DESCRIPTION as TITLE', 'relationship')
                        ->leftjoin('OVD_TYPES','OVD_TYPES.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_IDENTITY')
                        ->leftjoin('OVD_TYPES as A','A.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_ADDRESS')
                        ->leftjoin('OVD_TYPES as CA','CA.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_CURRENT_ADDRESS')
                        ->leftjoin('TITLE','CUSTOMER_OVD_DETAILS.TITLE','TITLE.ID')
                        //->leftjoin('RESIDENTIAL_STATUS','RESIDENTIAL_STATUS.ID','CUSTOMER_OVD_DETAILS.RESIDENTIAL_STATUS')
                        // ->leftjoin('BANK','CUSTOMER_OVD_DETAILS.BANK_NAME','BANK.ID')
                        // ->leftjoin('COUNTRIES','CUSTOMER_OVD_DETAILS.PER_COUNTRY','COUNTRIES.ID')
                        // ->leftjoin('COUNTRIES as CC','CUSTOMER_OVD_DETAILS.CURRENT_COUNTRY','CC.ID')
                        // ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','CUSTOMER_OVD_DETAILS.RELATIONSHIP')
                        ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                        ->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','ASC')
                        ->get()->toArray();

                foreach($customerOvdDetails as $key => $value){
                    $customerOvdDetails[$key]->bank_name = isset($bankTable[$customerOvdDetails[$key]->bank_name]) && $bankTable[$customerOvdDetails[$key]->bank_name] != '' ? $bankTable[$customerOvdDetails[$key]->bank_name] : '';
                    $customerOvdDetails[$key]->per_country = isset($countryTable[$customerOvdDetails[$key]->per_country]) && $countryTable[$customerOvdDetails[$key]->per_country] != '' ? $countryTable[$customerOvdDetails[$key]->per_country] : '';
                    $customerOvdDetails[$key]->current_country = isset($countryTable[$customerOvdDetails[$key]->current_country]) && $countryTable[$customerOvdDetails[$key]->current_country] != '' ? $countryTable[$customerOvdDetails[$key]->current_country] : '';
                    $customerOvdDetails[$key]->relationship = isset($relationshipTable[$customerOvdDetails[$key]->relationship]) && $relationshipTable[$customerOvdDetails[$key]->relationship] != '' ? $relationshipTable[$customerOvdDetails[$key]->relationship] : '';
                    $customerOvdDetails[$key]->residential_status = isset($residentalStatus[$customerOvdDetails[$key]->residential_status]) && $residentalStatus[$customerOvdDetails[$key]->residential_status] != '' ? $residentalStatus[$customerOvdDetails[$key]->residential_status] : '';
                }

                // if (isset($customerOvdDetails[0]->relationship) && $customerOvdDetails[0]->relationship != '') {
                //         $relationship = DB::table('RELATIONSHIP')->where('IS_CHEQUE_RELATION', 1)
                //                                                     ->where('code', str_pad($customerOvdDetails[0]->relationship, 3, '0', STR_PAD_LEFT))
                //                                                     ->get()->toArray();
                        
                //         $relationship = current($relationship);
                //         $customerOvdDetails[0]->relationship = $relationship->display_description;
                // }
            $entityDetails = DB::table('ENTITY_DETAILS')
                                        ->leftjoin('COUNTRIES','ENTITY_DETAILS.ENTITY_COUNTRY','COUNTRIES.ID')
                                        ->where('ENTITY_DETAILS.FORM_ID',$formId)
                                        ->orderBy('ENTITY_DETAILS.APPLICANT_SEQUENCE','ASC')
                                        ->get()->toArray();
            $entityDetails = (array) current($entityDetails);
            $enc_fields = ["entity_email_id","entity_mobile_number"];
            foreach($enc_fields AS $k => $v){
                if(isset($entityDetails[$v])){
                    $entityDetails[$v]= CommonFunctions::encrypt256($entityDetails[$v],$encrypt_key);
                } 
            }
            
            $clearanceDetails = '';
            if($accountDetails['account_type'] == 'Current'){
               
                $schemeCode = DB::table('ACCOUNT_DETAILS')->select('SCHEME_CODE')->whereId($formId)->get()->toArray(); 
                $schemeCode = (array) current($schemeCode);
                
                if($accountDetails['scheme_code'] == ''){
                    $getSchemeDesc = DB::table('CA_SCHEME_CODES')->select('SCHEME_CODE')->whereId($entityDetails['entity_scheme_code'])
                                                                                        ->get()
                                                                                        ->toArray();
                    $getSchemeDesc =  (array) current($getSchemeDesc);

                    $entityDetails['entity_scheme_code'] = $getSchemeDesc['scheme_code'];
                }

                if($schemeCode['scheme_code'] == 14){
                    $schemeCode['scheme_code'] = 1;
                }

    
                $currentSchemeCode = CommonFunctions::getSchemeCodesBySchemeId($accountDetails['account_type_id'],$schemeCode['scheme_code']);
                $currentSchemeCode = (array) current($currentSchemeCode);
                if(strtoupper($accountDetails['flow_tag_1']) == 'PROP'){
                
                $clearanceDetails = DB::table('CLEARANCE_MASTER')->where('SCHEME_CODE',$currentSchemeCode['scheme_code'])->get()->toArray();
                }else{
                    $clearanceDetails = DB::table('CLEARANCE_MASTER')->where('SCHEME_CODE',$currentSchemeCode['scheme_code'])
                                                                    ->where('FOR_IND','Y')
                                                                    ->get()->toArray();
            }

                $entityDetails['ovd'] = array();
                if($accountDetails['flow_tag_1'] != 'INDI'){

                $entityDetails['ovd'] = substr(config('entity')[$currentSchemeCode['scheme_code']][$entityDetails['proof_of_entity_address']],0,30);
                   
            }
                // if($accountDetails['flow_tag_1'] == 'INDI'){
                //     $caClearance = array();
                //     for($i=0;count($clearanceDetails)>$i;$i++){
                //         if(!in_array($clearanceDetails[$i]->id,[2,21,3,])){
                //             array_push($caClearance,$clearanceDetails[$i]);
                //         }
                //     }
                //     $clearanceDetails = $caClearance;
                // }
                        }
            // echo "<pre>";print_r($clearanceDetails);exit;

            $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')
                                            ->select('RISK_CLASSIFICATION_DETAILS.*','CUSTOMER_TYPE.DESCRIPTION as CUSTOMER_TYPE','COUNTRIES.NAME as COUNTRY_NAME', 'COB.NAME as COUNTRY_OF_BIRTH','CITIZENSHIP.NAME as CITIZENSHIP','OCCUPATION.DESCRIPTION as OCCUPATION','RESIDENCE.NAME as RESIDENCE')
                            ->leftjoin('CUSTOMER_TYPE','CUSTOMER_TYPE.ID','RISK_CLASSIFICATION_DETAILS.CUSTOMER_TYPE')
                            ->leftjoin('COUNTRIES','COUNTRIES.ID','RISK_CLASSIFICATION_DETAILS.COUNTRY_NAME')
                            ->leftjoin('COUNTRIES AS COB','COB.ID','RISK_CLASSIFICATION_DETAILS.COUNTRY_OF_BIRTH')
                            ->leftjoin('COUNTRIES AS CITIZENSHIP','CITIZENSHIP.ID','RISK_CLASSIFICATION_DETAILS.CITIZENSHIP')
                            ->leftjoin('COUNTRIES AS RESIDENCE','RESIDENCE.ID','RISK_CLASSIFICATION_DETAILS.RESIDENCE')
                            // ->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.ID','RISK_CLASSIFICATION_DETAILS.ACCOUNT_ID')
                            ->leftjoin('OCCUPATION','OCCUPATION.ID','RISK_CLASSIFICATION_DETAILS.OCCUPATION')
                            // ->leftjoin('BASIS_CATEGORISATION','BASIS_CATEGORISATION.ID','RISK_CLASSIFICATION_DETAILS.BASIS_CATEGORISATION')
                                            ->where('FORM_ID',$formId)
                                            ->orderBy('RISK_CLASSIFICATION_DETAILS.APPLICANT_SEQUENCE','ASC')
                                            ->get()->toArray();
                                            
            for($r=0;$r<count($riskDetails);$r++){
                $getsourceoffundId = explode(',',$riskDetails[$r]->source_of_funds);
                $descsourceoffund = DB::table('SOURCE_OF_FUNDS')->select('SOURCE_OF_FUND')->whereIn('ID',$getsourceoffundId)->get()->toArray();
                $getdescsourceoffund = array();
                if(count($descsourceoffund)>0){
                    for($sl=0;count($descsourceoffund)>$sl;$sl++){
                        $getdescsourceoffund[$sl] = $descsourceoffund[$sl]->source_of_fund;
                    }
                    $riskDetails[$r]->source_of_funds = implode(',',$getdescsourceoffund);
                }

                for($cl=0;count($customerOvdDetails)>$cl;$cl++){
                    if($customerOvdDetails[$cl]->id == $riskDetails[$r]->account_id){
                        $riskDetails[$r]->is_new_customer = $customerOvdDetails[$cl]->is_new_customer;
                        $riskDetails[$r]->pf_type = $customerOvdDetails[$cl]->pf_type;
                    }
                }
             $riskDetails[$r]->basis_categorisation = CommonFunctions::getBasisCategorisationString($riskDetails[$r]->basis_categorisation);    
            }
         
            $nomineeDetails = DB::table('NOMINEE_DETAILS')
                                        ->select('NOMINEE_DETAILS.*','RELATIONSHIP.DESCRIPTION as RELATINSHIP_APPLICANT','GR.DESCRIPTION as RELATINSHIP_APPLICANT_GUARDIAN',
                                                                                        'COUNTRIES.NAME as NOMINEE_COUNTRY','COUNTRIES.NAME as GUARDIAN_COUNTRY')
                                        ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                                        ->leftjoin('RELATIONSHIP as GR','GR.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                        ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                        ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();

            // L1 and L2 , QC and AU
			if($role == 3 || $role == 4 || $role == 5 || $role == 6){
							// switch(Session::get('role')){
								// case 3:
									// $param = "l1_counter";
									// break;
								// case 4:
									// $param = "l2_counter";
									// break;		
								// default:
									// $param = "";					
							// }
				
							//if($accountDetails[$param] > 0 || 1==1){   
							
								// $iteration = DB::table('REVIEW_TABLE')->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=> $formId])->pluck('iteration')->last();

								// $reviewDetails = DB::table('REVIEW_TABLE')->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=>$formId,'STATUS'=>'1', 'ITERATION'=> $iteration])
								//                                         ->pluck('comments','column_name')->toArray();
												//echo "<pre>";print_r($reviewDetails);exit;  

				$iterationRecords = DB::table('REVIEW_TABLE')
							->where(['ROLE_ID'=>$role,'FORM_ID'=> $formId])
							->get()->toArray();
				if(count($iterationRecords)==0){
					$iteration = 0;
				}else{                  
					$iterationRecords = DB::table('REVIEW_TABLE')->select(DB::raw('MAX(ITERATION) as iteration')  )
								->where(['ROLE_ID'=>$role,'FORM_ID'=> $formId])
								->get()->toArray();
					$iteration = $iterationRecords[0]->iteration; 
				}   
				$reviewDetails = DB::table('REVIEW_TABLE')
							->where(['ROLE_ID'=>$role,'FORM_ID'=>$formId,'STATUS'=>'1', 'ITERATION'=> $iteration])
							->pluck('comments','column_name')->toArray();

				if(count($reviewDetails) > 0)
				{
					$is_review = 1;
				}
				
				$lastSavedComments = DB::table('REVIEW_TABLE')->where('FORM_ID', $formId)
											->where('STATUS','0')
											->where('ITERATION','0')
											->where('ROLE_ID',$role)
											->get()->toArray();			 														
                //}
            }
			
			// QC and AU
			$L3_Responses = [];
			if($role == 5 || $role == 6){ 
				$iterationRecords = DB::table('REVIEW_TABLE')
							->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=> $formId])
							->get()->toArray();
				if(count($iterationRecords)==0){
					$iteration = 0;
				}else{                  
					$iterationRecords = DB::table('REVIEW_TABLE')->select(DB::raw('MAX(ITERATION) as iteration')  )
								->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=> $formId])
								->get()->toArray();
					$iteration = $iterationRecords[0]->iteration; 
				}   
				$reviewDetails = DB::table('REVIEW_TABLE')
							->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=>$formId,'STATUS'=>'1', 'ITERATION'=> $iteration])
							->pluck('comments','column_name')->toArray();
				// $L3_Responses = DB::table('REVIEW_TABLE')
							// ->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=>$formId,'STATUS'=>'1', 'ITERATION'=> $iteration])
							// ->get()->toArray();						
				// For QC / Audit show all responses (post facto)
				$L3_Responses = DB::table('REVIEW_TABLE')->where('FORM_ID', $formId)->where('STATUS','1')
											->where(function($query){
													$query->where('ROLE_ID', '5')
                                                    ->orWhere('ROLE_ID', '6');
													})
											->get()->toArray();									
                //echo "<pre>";print_r($L3_Responses);exit;
				if(count($reviewDetails) > 0)
				{
					$is_review = 1;
				}
				
			}
									
            $fromRole = '';
			$lastSender = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)->orderBy('ID','DESC')
														->first();														
			if(isset($lastSender->role_id)){

               $fromRole = $lastSender->role_id;
            }else{

               $fromRole = '';
            }   
			
			if($role == 8) { // L3 								
				
				$reviewDetails = $qcReviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
											->where('ROLE_ID',$lastSender->role_id)											
											->where('ITERATION',$lastSender->iteration)
											->pluck('comments','column_name')->toArray();														

				$lastSavedComments = DB::table('REVIEW_TABLE')->where('FORM_ID', $formId)
											->where('ROLE_ID',$lastSender->role_id)											
											->where('ITERATION',$lastSender->iteration)
											->whereNotNull('RESPONSE')
											->get()->toArray();			 														
				
				$L3_Responses = DB::table('REVIEW_TABLE')->where('FORM_ID', $formId)->where('STATUS','1')
											->where(function($query){
													$query->where('ROLE_ID', '5')
                                                    ->orWhere('ROLE_ID', '6');
													})
											->get()->toArray();									
								 																	    
				// Would typically will always be 1 for L3
				$is_review = 1;				
            }	

			/*
            if(($role == 5) || ($role == 6) || ($role == 8)) // QC, AU, L3
            {
                // $roleId = '';
                // if($accountDetails['application_status'] == 22 || $accountDetails['application_status'] == 24){ //L3
                    // $roleId = 5;
                    // $status = 0;
                // }else if($accountDetails['application_status'] == 23){
                    // $roleId = 6;
                // }else if(($accountDetails['application_status'] == 15) || ($accountDetails['application_status'] == 18)){ //QC & AU
                    // $roleId = 8;
                    // $status = 1;
                // }

				$reviewDetails = $qcReviewDetails = DB::table('REVIEW_TABLE')->where(['ROLE_ID'=>$roleId,'FORM_ID'=>$formId,'STATUS'=> $status])
													->pluck('comments','column_name')->toArray();
				if(count($reviewDetails) > 0){
					$is_review = 1;
				}               
            }
			*/
            $currentTime = Carbon::now()->format('Y-m-d H:i:s');
            $currentUser = Session::get('userId');

            if(Session::get('role') == 3) 
            {
               // L1
			    $AOFStatus = 3;
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                            ->update(['APPLICATION_STATUS'=>2, 'L1_REVIEW'=>1,'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
            }

            if((Session::get('role') == 4) && ($accountDetails['application_status'] != 12))
            {
               // L2
                $AOFStatus = 14;
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['APPLICATION_STATUS'=>9, 'L2_REVIEW'=>1,'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
            }
            if(Session::get('role') == 5)
            {
                $AOFStatus = 26;
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['APPLICATION_STATUS'=>15, 'QC_REVIEW'=>1,'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
            }
            if(Session::get('role') == 6)
            {
                $AOFStatus = 31;
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['APPLICATION_STATUS'=>18, 'AUDIT_REVIEW'=>1,'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
            }
            if(Session::get('role') == 8)
            {
                $AOFStatus = 28;
                //updated_at or npc_review_time date Mar 9 2022
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['APPLICATION_STATUS'=>24,'UPDATED_AT'=>$currentTime]);
            }

            $saveStatus = CommonFunctions::saveStatusDetails($formId,$AOFStatus);
            $ddu_refrence = rand();

            $hold_reject_comment = DB::table('STATUS_LOG')
                                             ->where('FORM_ID',$formId)
                                             ->where('ROLE',$role)
                                             ->where('comments', '!=', null)
                                             ->orderByDesc('id')
                                             ->get()->toArray();
            $hold_reject_comment = (array) current($hold_reject_comment);
            $aofStatus = DB::table('AOF_STATUS')->pluck('status', 'id')->toArray();
            
            // echo "<pre>";print_r();exit;
            if (isset($hold_reject_comment['status'])) {
                if (substr($aofStatus[$hold_reject_comment['status']], -4) == 'Hold') {
                    $aofStatus = 'HOLD'; 
                }else if(substr($aofStatus[$hold_reject_comment['status']], -8) == 'Rejected'){
                    $aofStatus = 'REJECTED'; 
                }
            }
            // echo "<pre>";print_r($hold_reject_comment);exit;

            $accountDetailsDelight = DB::table('ACCOUNT_DETAILS')
                                            ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
            $userDetails['AccountDetails'] = (array) current($accountDetailsDelight);

            if($userDetails['AccountDetails']['account_type'] == 1 && $userDetails['AccountDetails']['delight_scheme'] == 5){
                $delightKit = DB::table('DELIGHT_KIT')
                                ->where('id', $userDetails['AccountDetails']['delight_kit_id'])
                                ->get()->toArray();
                $delightKit = current($delightKit);

                $delightSavings = true;
            }else{
                $delightKit = '';
                $delightSavings = false;
            }

            $checkNEFT = DB::table('FINCON')
                                ->where('FORM_ID',$formId)
                                ->where('ABORT', null)
                                ->get()->toArray();

            if(isset($checkNEFT) && count($checkNEFT) > 0){
                $neftStatusButton = true;
            }else{
                $neftStatusButton = false;
            }

            $l1Rules = config('l1_Rules');
            $banksList = DB::table('BANK')->where('IS_ACTIVE',1)->pluck('bank_name','id')->toArray();
            $titleList = array();
            $getCustomerData = DB::table('CUSTOMER_OVD_DETAILS')->select('TITLE','DOB','GENDER','MARITAL_STATUS')->where('FORM_ID',$formId)->get()->toArray();
            foreach ($getCustomerData as $getCustomerDataValue) {
                $getCustomerDataValue = (array) $getCustomerDataValue;
                $customerAge = Carbon::parse($getCustomerDataValue['dob'])->age;
                $getTitle = DB::table('TITLE')->where('GENDER', 'like', '%'.$getCustomerDataValue['gender'].'%')
                                                ->where('MIN_AGE','<=',$customerAge)
                                                ->where('MAX_AGE','>=',$customerAge)
                                                ->pluck('description','id')->toArray();
                array_push($titleList, $getTitle);
            }

            
            $nomineeRelations = DB::table('RELATIONSHIP')->where(['IS_NOMINEE_RELATION'=>1,'IS_ACTIVE'=>1])
                                                        ->pluck('display_description','id')->toArray();
				// echo '<pre>'; print_r($nomineeRelations); exit;  
            $formReviewDetails = DB::table('REVIEW_TABLE')->where('ROLE_ID', Session::get('role'))
                                                        ->where('FORM_ID',$formId)
                                                        ->orderBy('ID', 'DESC')
                                                        ->get()->toArray();
            if (count($formReviewDetails) > 0 ) {
                $formReviewDetails = current($formReviewDetails);
                $reviewIteration = $formReviewDetails->iteration;
            }else{
                $reviewIteration = 0;
            }
            $reviewIteration++;
            $getCCDeclarationImages = [];
            $declarationAll = [];
            $tdSchemeCode = [];
            if($accountDetails['source'] == 'CC'){
                $tdSchemeCode = DB::table('TD_SCHEME_CODES')->where('SCHEME_CODE',$accountDetails['tdscheme_code'])->where('RI_NRI','NRI')->count();
                if($tdSchemeCode == 1){
                $getCCDeclarationImages = DB::table('CUSTOMER_DECLARATIONS')->where('FORM_ID',$accountDetails['id'])
                                                                                ->whereIn('DECLARATION_ID',[53,142])->get()->toArray();
                // $getCCDeclarationImages = (array) current($getCCDeclarationImages);
                   $declarationAll = DB::table('DECLARATIONS')->select('ID','DECLARATION')->pluck('declaration','id');                                                             
                }
            }
            // echo "<pre>";print_r(count($getCCDeclarationImages));exit;
            if(count($getCCDeclarationImages)>0){
                $showdoc = 'true';
            }else{
                $showdoc = 'false';
            }

            if(Session::get('role') == '3' || Session::get('role') == '4'){  
                $npcbankreviewDetails = DB::table('REVIEW_TABLE')->select('comments','column_name','role_id')
                ->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=>$formId,'STATUS'=>'1', 'ITERATION'=> $iteration])
                ->get()->toArray();
           
            }else{
                $npcbankreviewDetails = [];
            }
            //GROSS INCOME CHANGE LOGIC
            for($seq=0;count($riskDetails)>$seq;$seq++){

                $grossincomeId = $riskDetails[$seq]->gross_income;

                $getdescgrossIncome = DB::table('GROSS_INCOME')->select('GROSS_ANNUAL_INCOME')
                                                            ->whereId($grossincomeId)
                                                            ->get()->toArray();
                if(count($getdescgrossIncome)>0){
                    $getdescgrossIncome =  (array) current($getdescgrossIncome);
                    $riskDetails[$seq]->gross_income = $getdescgrossIncome['gross_annual_income'];
                }
            }            
//new logic ekyc image
            if(isset($customerOvdDetails) && !empty($customerOvdDetails)){
                for($i=0;count($customerOvdDetails)>$i;$i++){
                    if(strtoupper($customerOvdDetails[$i]->{'proof_of_identity'}) == 'E-KYC' || strtoupper($customerOvdDetails[$i]->{'proof_of_address'}) ){
                        $getekycPhoto =  DB::table('EKYC_DETAILS')->select('EKYC_PHOTO')->where('FORM_ID',$customerOvdDetails[$i]->{'form_id'})
                                                                  ->where('APPLICANT_SEQUENCE',$customerOvdDetails[$i]->{'applicant_sequence'})
                                                                  ->whereIn('EKYC_NO',[$customerOvdDetails[$i]->{'id_proof_card_number'},$customerOvdDetails[$i]->{'add_proof_card_number'}])
                                                                  ->orderBy('ID','DESC')
                                                                  ->take('1')
                                                                  ->get()->toArray();
                        if(count($getekycPhoto)>0){
                            $getekycPhoto =  (array) current($getekycPhoto);
                            $ekycPhoto = json_decode(base64_decode($getekycPhoto['ekyc_photo']),true)['ekyc_photo_details']['photo'];
                            $customerOvdDetails[$i]->{'ekyc_photo'} = $ekycPhoto;
                        }
                    }
                }
            }
            
            $tokenParams = Cookie::get('token');
            $encrypt_key = substr($tokenParams, -5);  
            
            if(isset($customerOvdDetails) && !empty($customerOvdDetails)){             
                $enc_fields = ['Aadhaar Photocopy','Passport','Voter ID','Driving Licence'];
                foreach ($customerOvdDetails as $i => $v) {
                    if(in_array($v->proof_of_identity ,$enc_fields)){
                        $customerOvdDetails[$i]->id_proof_card_number=CommonFunctions::encrypt256($v->id_proof_card_number,$encrypt_key);
                    }
                    if(in_array($v->proof_of_address ,$enc_fields)){
                        $customerOvdDetails[$i]->add_proof_card_number=CommonFunctions::encrypt256($v->add_proof_card_number,$encrypt_key);
                    }
                    if(isset($v->pf_type)=="pancard"){
                        $customerOvdDetails[$i]->pancard_no= CommonFunctions::encrypt256($v->pancard_no,$encrypt_key);
                    }
                    $customerOvdDetails[$i]->mobile_number= CommonFunctions::encrypt256($v->mobile_number,$encrypt_key);
                    $customerOvdDetails[$i]->email= CommonFunctions::encrypt256($v->email,$encrypt_key);
                }
             }

    
          
            // echo "<pre>";print_r($customerOvdDetails);exit;
            // $customerOvd['proof_of_identity'],['Aadhaar Photocopy','Passport','Voter ID','Driving Licence']

            // $custOvdData = EncryptDecrypt::AES256Encryption($hrmsNo,$encrypt_key);
        
                    // echo "<pre>";print_r($customerOvdDetails);exit;
         
            $checkvisibleciid = Rules::precheckshowingciddInformation($riskDetails,$reviewDetails,$formId);
            $huf_cop_row=[];
            if($accountDetails["constitution"]=="NON_IND_HUF"){
                $huf_cop_row = DB::table("NON_IND_HUF as HUF")->select("HUF.*","REL.DISPLAY_DESCRIPTION as relation")
                ->leftJoin("RELATIONSHIP as REL","REL.ID","=","HUF.HUF_RELATION")
                ->where("HUF.FORM_ID",$formId)->where("HUF.DELETE_FLG","N")
                ->get()->toArray();
                $huf_cop_row = (array) $huf_cop_row;
            }
			  $etb_cust= array_filter($customerOvdDetails,fn($i)=> $i->is_new_customer == 0);
            
            $etb_cust_crf = [];
            foreach ($etb_cust as $i => $v) {
                $cust_crf_row = Rules::custAmendRequestIsPending($v->customer_id);
                if($cust_crf_row["status"]==true){
                    array_push($etb_cust_crf,[$cust_crf_row["data"]["crf_number"],$v->customer_id]);
                }
            }
            
            return view('npc.review')->with('formId',$formId)          
                                    ->with('accountDetails',$accountDetails)
                                    ->with('nomineeRelations',$nomineeRelations)
                                    ->with('l1Rules',$l1Rules)
                                    ->with('titleList',$titleList)
                                    ->with('banksList',$banksList)
                                    ->with('npcbankreviewDetails',$npcbankreviewDetails)
                                    ->with('customerOvdDetails',$customerOvdDetails)
                                    ->with('riskDetails',$riskDetails)
                                    ->with('getCCDeclarationImages',$getCCDeclarationImages)
                                    ->with('hold_reject_comment',$hold_reject_comment)
                                    ->with('ddu_refrence',$ddu_refrence)
                                    ->with('deDupeStatusButton',$deDupeStatusButton)
                                    ->with('is_review',$is_review)
                                    ->with('nomineeDetails',$nomineeDetails)
                                    ->with('declarations',$declarations)
                                    ->with('reviewDetails',$reviewDetails)
                                    ->with('qcReviewDetails',$qcReviewDetails)
                                    ->with('fromRole', $fromRole)
									->with('L3_Responses', $L3_Responses)
									->with('lastSavedComments', $lastSavedComments)
									->with('level3notesimages', $level3notesimages)
                                    ->with('delightSavings', $delightSavings)
                                    ->with('userDetails', $userDetails)
                                    ->with('delightKit', $delightKit)
                                    ->with('neftStatusButton', $neftStatusButton)
                                    ->with('aofStatus',$aofStatus)
                                    ->with('reviewIteration', $reviewIteration)
                                    ->with('entityDetails',$entityDetails)
                                    ->with('clearanceDetails',$clearanceDetails)
                                    ->with('declarationAll',$declarationAll)
                                    ->with('showdoc',$showdoc)
                                    ->with('tdSchemeCode',$tdSchemeCode)
                                    ->with('checkvisibleciid',$checkvisibleciid)
                                    ->with('huf_cop_row',$huf_cop_row)
									->with("etb_cust_crf",$etb_cust_crf)
									;
									 
                                    
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function entityreview(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $role = Session::get('role');
                $requestData = Arr::except($request->get('data'),'functionName');
                if(isset($requestData['clearance_image'])){
                        //define old file path(temp folder)
                        $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$requestData['clearance_image']);
                        //$folder = public_path('/uploads/attachments/'.$formId);
                        $folder = storage_path('/uploads/attachments/'.$requestData['form_id']);
                        // if(Session::get('is_review') == 1)
                        // {
                        //     $folder = public_path('/uploads/markedattachments/'.$formId);
                        // }
                        $filePath = $folder.'/'.$requestData['clearance_image'];
                // echo "<pre>";print_r($filePath);exit;
                        if (!File::exists($folder)) {
                            File::makeDirectory($folder, 0775, true, true);
                        }
                        //checks file exists or not in temp folder
                        if(file_exists($oldFilePath)){
                            //move file from temp folder to upload folder
                            if (File::move($oldFilePath, $filePath)){

                            }else{
                                //make it false if any file didn't uploaded
                                $is_uploaded = false;
                            }
                        }
                    }else{
                    return json_encode(['status'=>'fail','msg'=>'Kindly Upload Img ','data'=>[]]);

                    }
                $insertInClearance = ['FORM_ID' => $requestData['form_id'],
                                      'CLEARANCE_ID' => $requestData['clearance_id'],
                                      'ACTIVE' => $requestData['is_active'],
                                      'ROLE' => $role,
                                    'CLEARANCE_IMG' => $requestData['clearance_image'],
                                    "UPDATED_BY"=> Session::get('userId'),
                                    "UPDATED_AT"=> Carbon::now()];

                $getClearanceDetails = DB::table('CLEARANCE')->where('form_id',$requestData['form_id'])
                                                              ->where('CLEARANCE_ID',$requestData['clearance_id'])->get()->toArray();


                if(count($getClearanceDetails) > 0){
                   $saveClearance =  DB::table('CLEARANCE')->where('CLEARANCE_ID',$requestData['clearance_id'])
                                                            ->where('form_id',$requestData['form_id'])
                                                            ->update($insertInClearance);
                }else{
                   $saveClearance =  DB::table('CLEARANCE')->insert($insertInClearance);
                }

// echo "<pre>";print_r($requestData);exit;
                if($saveClearance){
                    DB::commit();                   
                    return json_encode(['status'=>'success','msg'=>'Checked successfully','data'=>['is_active' => $requestData['is_active'],'blade_id' => $requestData['blade_id']]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
                // echo "<pre>";print_r(count($getClearanceDetails));exit;
            
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
}

    public function savecomments(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                
                $requestData = Arr::except($request->get('data'),'functionName');
                $form_id = $requestData['form_id'];
                $accountDetails = DB::table("ACCOUNT_DETAILS")->select("CONSTITUTION")->where("ID",$form_id)->get()->toArray();
                $accountDetails = (array) current($accountDetails);
                $requestData['role_id'] = Session::get('role');
                $appplicantId = 1;
                $columnDetails = explode('-', $requestData['column_name']);
                $column = $columnDetails[0];
                if(count($columnDetails) > 1)
                {
                    $appplicantId = $columnDetails[1];
                }

                if(isset($requestData['clearance_active'])){
                  $blade_id = substr($requestData['column_name'], 0,-10);
                  $getClearanceDetails = DB::table('CLEARANCE_MASTER')->where('BLADE_ID',$blade_id)->get()->toArray();
                  $getClearanceDetails = (array) current($getClearanceDetails);

                  $clearance  = DB::table('CLEARANCE')->where('CLEARANCE_ID',$getClearanceDetails['id'])
                                                            ->where('FORM_ID',$requestData['form_id'])
                                                            ->get()->toArray();
                  if(count($clearance) == 1){
                    $clearance_update = DB::table('CLEARANCE')->where('CLEARANCE_ID',$getClearanceDetails['id'])
                                                            ->where('FORM_ID',$requestData['form_id'])
                                                            ->update(['ACTIVE' => 0,
                                                                      'ROLE' => $requestData['role_id'],
                                                                      "UPDATED_BY"=> Session::get('userId'),
                                                                      "UPDATED_AT"=> Carbon::now()]);
                  }else{
                     $clearance_update = DB::table('CLEARANCE')->insert(['ACTIVE' => 0,
                                                                        'ROLE' => $requestData['role_id'],
                                                                        'CLEARANCE_ID' => $getClearanceDetails['id'],
                                                                        'FORM_ID' => $requestData['form_id']
                                                                        ]);
                  }
                  unset($requestData['clearance_active']);
                  unset($requestData['reviewId']);
                }


                if (isset($requestData['comments']) && preg_match('/[^a-zA-Z0-9 .,%?!\/\\()_\n\r]/',$requestData['comments'])) {
                    return json_encode(['status'=>'warning','msg'=>'Error! Invalid comment (Special characters are not allowed)','data'=>[]]);
                }

                //Begins db transaction
                DB::beginTransaction();
				
				
				if(Session::get('role') != 8){ // L1 / L2 / QC /AU

					// Check if already exist from previous partial save. Role8/L3 is anyways always an UPDATE
					if(!isset($requestData['reviewId'])){
						$reviewExist = DB::table('REVIEW_TABLE')
									->where(['ROLE_ID'=> $requestData['role_id'], 'COLUMN_NAME'=> $requestData['column_name'], 'FORM_ID'=> $requestData['form_id'], 'STATUS'=> '0', 'ITERATION'=>'0'])
									->get()->toArray();	
						if(count($reviewExist)>0){ 
							$requestData['reviewId'] = $reviewExist[0]->id;
						}					
					}

                    $column = Rules::precheckdiscrepentColumn($requestData['form_id'],$column,$appplicantId);

					if(($column == "proof_of_identity") || ($column == "proof_of_address") || ($column == "proof_of_current_address") ||  ($column == "id_proof_image") || ($column == "current_add_proof_image") || ($column == "amount") || ($column=="relationship") || ($column=="father_name") || ($column == "add_proof_image")||($column=='occupation') || ($column == 'ALL') || ($column == 'td_amount')|| ($column =='proof_of_entity_address') || ($column == 'entity_add_proof_image')){
							$updateColumns = $this->updateDiscrepentFileds($requestData,$column,$appplicantId);
					}	
                    if( $accountDetails["constitution"]=="NON_IND_HUF"){
                        
                        $column = Rules::precheckforautodiscrepentColumn($form_id,$requestData['column_name'],$appplicantId);
                        
                        $updateOtersColumns = $this->updatehufDiscrepentFileds($requestData,$appplicantId,$column);
                        $updateOtersColumns = $this->updateAutoDiscrepentFileds($requestData,$column,$appplicantId);
                    }

					if(isset($requestData['reviewId'])){
						$reviewId = $requestData['reviewId'];
						unset($requestData['reviewId']);
						$requestData['updated_by'] = Session::get('userId');
						$requestData['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
                        $requestData['BRANCH_ID'] = Session::get('branchId'); 
						$saveComments = DB::table('REVIEW_TABLE')->whereId($reviewId)->update($requestData);
					}else{
                        $requestData['created_by'] = Session::get('userId'); 
						$requestData['BRANCH_ID'] = Session::get('branchId'); 
						$saveComments = DB::table('REVIEW_TABLE')->insertGetId($requestData);
						$reviewId = $saveComments;
					}
				}else{
					// For L3 Update response column instead of comments. Prevent overwriting comments
					if(isset($requestData['reviewId'])){
						$reviewId = $requestData['reviewId'];
					}else{	
						$tablePointer = DB::table('REVIEW_TABLE')
							->where(['COLUMN_NAME'=> $requestData['column_name'], 'FORM_ID'=> $requestData['form_id'], 'STATUS'=> '0'])
							->first();						
						$reviewId = $tablePointer->id;
					}
					$updateQcComments = [ 'STATUS'=> '1', 'RESPONSE' => $requestData['comments'], 
										  'RESPONSE_BY' => Session::get('userId'), 
										  'RESPONSE_DATE' => Carbon::now()->format('Y-m-d H:i:s'),
                                          'BRANCH_ID' => Session::get('branchId') ];
					$saveComments = DB::table('REVIEW_TABLE')->where('ID', $reviewId)->update($updateQcComments);				
				}                    
															 
                if($saveComments){
                    //commit database if response is true
                    DB::commit();					
                    return json_encode(['status'=>'success','msg'=>'Comments saved successfully','data'=>['reviewId'=>$reviewId]]);
                }else{
                   
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
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
    public static function updateAutoDiscrepentFileds($requestData,$discrepentColumn,$appplicantId){
        $columns = $discrepentColumn;
        if (isset($requestData['reviewId'])) {
            unset($requestData['reviewId']);
        }
        $requestData['comments'] = "Automarked as part of ALL";
        foreach($columns as $acc_id_number => $acc)
        {
            foreach ($acc as $tk => $tabs) {
                foreach ($tabs as $ck => $column) {
                    
                    $new_col_name = "$column-$acc_id_number";
                    
                    $requestData['column_name'] = $new_col_name;
                    $sql_array=['FORM_ID'=>$requestData['form_id'],'COLUMN_NAME'=>$new_col_name,'ROLE_ID'=>$requestData['role_id'], 'STATUS'=>'0'];

                    

                    $checkComments = DB::table('REVIEW_TABLE')->where($sql_array)->get()->toArray();

                   
			        if(count($checkComments)>0){
                        if (substr($checkComments[0]->comments, 0, 10) == "")
    {
			        	    $updateColumns = DB::table('REVIEW_TABLE')->where($sql_array)->update($requestData);
                        
                        }else{
                            $updateColumns = '';
                        }
			        }else{
                       
			        	$updateColumns = DB::table('REVIEW_TABLE')->insert($requestData);	
                        
			        } 
                }
            }
        }
    }
    public function updateDiscrepentFileds($requestData,$discrepentColumn,$appplicantId)
    {   
       
        $columns = config('constants.DISCREPENT_COLUMNS.'.$discrepentColumn);
        foreach($columns as $column)
        {
            $accountType = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE')->whereId($requestData['form_id'])->get()->toArray();
            $accountType = (array) current($accountType);

            if(($discrepentColumn == 'amount') && ($accountType['account_type'] != 4)){
                return true;
            }

            if(in_array($discrepentColumn,['amount','td_amount']) && ($accountType['account_type'] == 4)){
			     $requestData['column_name'] = $column;
            }
            elseif($discrepentColumn == 'occupation')
            {
                $requestData['column_name'] = $column;
                
                if($column =='other_occupation'){
                    
                    $requestData['column_name'] = $column.'-'.$appplicantId;
                }
            } 
            elseif($discrepentColumn == 'source_of_funds')
            {
                $requestData['column_name'] = $column;
            }               
            else{
                 $requestData['column_name'] = $column.'-'.$appplicantId;
            }
            
            if(in_array($discrepentColumn,['relationship','td_amount'])){
                $requestData['column_name'] = $column;
            }
            
            if(($discrepentColumn =='proof_of_entity_address' || $discrepentColumn =='entity_add_proof_image') && $accountType['account_type'] == 2){
                  $requestData['column_name'] = $column;
            }

			$requestData['comments'] = "Automarked as part of ".$discrepentColumn;            
			
			$checkComments = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$requestData['form_id'],
                                                                    'COLUMN_NAME'=>$requestData['column_name'],
                                                                    'ROLE_ID'=>$requestData['role_id'], 'STATUS'=>'0'])
                                                            ->get()->toArray();
        
            if (isset($requestData['reviewId'])) {
                unset($requestData['reviewId']);
            }
			if(count($checkComments)>0){
                if (substr($checkComments[0]->comments, 0, 10) == "")
                {
				    $updateColumns = DB::table('REVIEW_TABLE')->where(['FORM_ID'=>$requestData['form_id'],
                                                                    'COLUMN_NAME'=>$requestData['column_name'],
                                                                    'ROLE_ID'=>$requestData['role_id'], 'STATUS'=>'0'])
																	->update($requestData);	
                   
                }else{
                    $updateColumns = '';
                }
			}else{
				$updateColumns = DB::table('REVIEW_TABLE')->insertGetId($requestData);	
			}            
        }
        return $updateColumns;
    }

    public function sendSmsOnDiscrepancy($form_id, $idOfPersonWhoCreatedForm)
    {

        $recievernameQuery = DB::table('USERS')->where('ID', $idOfPersonWhoCreatedForm)->get()->toArray();
        $templateQuery = DB::table('MESSAGES')->where('ACTIVITY_CODE', "DISCREPANT_SMS")->get()->toArray();
        $getaofNumber =  DB::table('ACCOUNT_DETAILS')->select('AOF_NUMBER')->whereId($form_id)->get()->toArray();
        $getaofNumber = (array) current($getaofNumber);
        $aofNumber = $getaofNumber['aof_number'];
        $template = ['id' => $templateQuery[0]->id, 'function_name' => $templateQuery[0]->function_name, 'activity_code' => $templateQuery[0]->activity_code, 'message_type' => $templateQuery[0]->message_type, 'message' => $templateQuery[0]->message, 'subject' => $templateQuery[0]->subject];
        $detailsForSending = ['email' => $recievernameQuery[0]->empemailid, 'mobile_number' => $recievernameQuery[0]->empmobileno, 'attachment' => null, 'refernceNo' => $aofNumber];
        NotificationController::processMessage($form_id, $template, $detailsForSending);
    }


    public function submittobank(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');
     
                $role = Session::get('role');
                $reviewstatus = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                            //->where($fieldName, '1')
                                                            ->get()->toArray();
				$reviewstatus = (array) current($reviewstatus);					
                $aof_No=$reviewstatus['aof_number'];
                $type='all';
                
            if($role == 4 || $role == 8)
            {
                    CommonFunctions::checkMaskImages($aof_No,$type,$role);                
            }
                      
				// If NPC user clicks form (in parallel or for first time) while it was already processed
				if($role!=$reviewstatus['next_role']){
					return json_encode(['status'=>'fail','msg'=>'Record already processed','data'=>[]]);
				}


                $customerOvdDetails = array();
                $createAccount = false;
                $fundingStatus = true;
                DB::beginTransaction();
                $status  = config('constants.UPDATE_STATUS_ON_ROLE.'.$requestData['status'].'.'.$role);
                $AOFStatus  = config('constants.UPDATE_AOF_STATUS_ON_ROLE.'.$requestData['status'].'.'.$role);

				// For Management Dashboard //
				$insertNRL['form_id'] = $requestData['form_id'];
				$insertNRL['role_id'] = $role;
				$insertNRL['status'] = $requestData['status'];
				$insertNRL['desc_count'] = $requestData['descCount'];
				$insertNRL['time_taken'] = $requestData['timeTaken'];
				$insertNRL['created_by'] = Session::get('userId');
                $insertNRL['BRANCH_ID'] = Session::get('branchId'); 
				$npcReviewLog = DB::table('NPC_REVIEW_LOG')->insert($insertNRL);
				DB::commit();							
				//----------------------------//

                $updateArray = [];
				
                if($requestData['status'] == 'hold'){

                     $scenario = 'hold';
                      $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],$AOFStatus,$requestData['holdcomment']);
                   

                }elseif($requestData['status'] == 'reject'){

                     $scenario = 'reject';
                      $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],$AOFStatus,$requestData['rejectcomment']);

                }elseif($requestData['status'] == 'discrepent'){

                     $scenario = 'discrepent';
                     $updateCounters = Self::updateL1L2_discrep_counters($role, $requestData['form_id']);
                     $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],$AOFStatus);
                    if ($role == '3' || $role == '4' || $role == '5' || $role == '6') {

                        $iteration = DB::table('REVIEW_TABLE')->where(['FORM_ID'=> $requestData['form_id'], 'ROLE_ID' => $role])
                                                            ->where('status', '!=', '0');
                        $iteration = $iteration->pluck('iteration')->last();
                        
                        if ($iteration == '') {
                            $incrementedIteration = '1';
                        }else{
                            $incrementedIteration = $iteration + 1;
                        }

                        $updateIteration = DB::table('REVIEW_TABLE')->where(['ROLE_ID'=>Session::get('role'),'FORM_ID'=>$requestData['form_id'], 'STATUS'=> '0'])
                                                                ->update(['ITERATION'=> $incrementedIteration]);
                                                              
                                                              
                    }

                    $updateArray['UPDATED_AT'] = Carbon::now(); 
                    Self::sendSmsOnDiscrepancy($requestData['form_id'],$reviewstatus['created_by']);
                    
                }else{

                     $scenario = 'clear';
                     Self::submittobankClear($role,$requestData);

                    if($role == 4){
                        $ekycPhotos =  CommonFunctions::horizontalImageView($requestData['form_id']);
                    }
                     $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],$AOFStatus);
                }
                
                $tag  = '';             
                switch($role){
                    case 3:
                        $tag = 'L1';
                        break;
                    case 4:
                        $tag = 'L2';
                        break;
                    case 8:
                        $tag = 'L3';
                        break;
                    case 5:
                        $tag = 'QC';
                        break;
                    case 6:
                        $tag = 'AU';
                        break;                                          
                }

                $tag = $tag.'_'.$scenario;
                if($requestData['fromRole']!=''){
                    $fromRole = $requestData['fromRole'];
                }else{
                    $fromRole = '';
                }

                if($requestData['status'] == 'discrepent')
                {


                $checkdata=DB::table('REVIEW_TABLE')->where('FORM_ID',$requestData['form_id'])->where('ROLE_ID',Session::get('role'))->where('ITERATION',$incrementedIteration)->count();
                
                if($checkdata > 0)
                {
                $updateNextRole = CommonFunctions::updateNextRole($requestData['form_id'], $tag, $fromRole);                
                }  
                
                }   
                else
                {
                $updateNextRole = CommonFunctions::updateNextRole($requestData['form_id'], $tag, $fromRole);  
                }         

                //$saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],$AOFStatus);

                $updateArray['APPLICATION_STATUS']= $status;
                $updateArray['UPDATED_BY']= Session::get('userId');


                if($requestData['status'] == "approved"){
                    $column = config('constants.UPDATED_STATUS_ON_ROLE.'.$role);
                    $updateApplicationStatus = CommonFunctions::updateApplicationStatus($column,$requestData['form_id']);
                    if($role == 5){
                        $updateArray['NEXT_ROLE'] = 6;
                    }
                }
                $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                ->update($updateArray);

				// Send notification only if clear!
                if($scenario == 'clear'){
					switch($role) {

						case '3':
							NotificationController::processNotification($requestData['form_id'],'L1_CLEARED');
							break;

						case '4':
							// NotificationController::processNotification($requestData['form_id'],'L2_CLEARED');
							break;
						default:
							# code...
							break;
					}
				}

                if($updateStatus){
                    //commit database if response is true
                    DB::commit();
                    if(($role == 4) && ($requestData['status'] == "approved")){
                        // if($reviewstatus['delight_scheme'] == ''){
                            // if($reviewstatus['account_type'] == 2 || $reviewstatus['account_type'] == 1){
                                Rules::preCustomerId($requestData['form_id']);
                                return json_encode(['status'=>'success','msg'=>'Form Sent to further process','data'=>'api']);

                            // }
                        // }
                        return json_encode(['status'=>'success','msg'=>'Comments Saved Successfully','data'=>'createcustomerid']);
                    }else{
                        return json_encode(['status'=>'success','msg'=>'Comments saved successfully','data'=>'']);
                    }
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

   public function updateL1L2_discrep_counters($role,$formId){
        if($role == 3){
           $counter_column = 'l1_counter';
        }else{
            if($role == 4) $counter_column = 'l2_counter';
            else return;
        }
        $counter = DB::table('ACCOUNT_DETAILS')->whereId($formId)->pluck($counter_column)->toArray();
        $counter = current($counter) + 1;
        $updateArray[strtoupper($counter_column)] = $counter;
        $updateL2Status = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update($updateArray);
        DB::commit();
    }


    public function submittobankClear($role,$requestData){
        try {

             $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$requestData['form_id'])->get()->toArray();
             $accountDetails = (array) current($accountDetails);

             $tdDetails = DB::table('TD_DETAILS')->where('FORM_ID',$requestData['form_id'])
                                                ->get()->toArray();
             //echo "<pre>";print_r($tdDetails);exit;

             if($accountDetails['source'] == 'CC'){
                if($requestData['value_date'] == "02"){
                    $requestData['value_date'] = 'R'; // R => Review Date / Current Date

                }elseif($requestData['value_date'] == "053"){

                    $requestData['value_date'] = 'E'; // E =>CUSTOMER EMAIL DATE
                    
                }else{
                    $requestData['value_date'] = 'C'; // C => Creation Date / Submission Date
                }

                if($requestData['review_date'] == ''){
                    $requestData['review_date'] = Carbon::now();
                }

                $td_details = [
                    'FORM_ID'=>$requestData['form_id'],
                    'CREATION_DATE'=>Carbon::parse($requestData['creation_date']),
                    'REVIEW_DATE'=>Carbon::parse($requestData['review_date']),
                    'SELECTED_VALUE_TYPE'=>$requestData['value_date'],
                    'CUSTOMER_DATE' => Carbon::parse($requestData['customer_date']),
                    'CREATED_BY'=>Session::get('userId')
                ];

                if(count($tdDetails) > 0){

                    $saveTDDetails = DB::table('TD_DETAILS')->update($td_details);
                    DB::commit();
                }else{
                    $saveTDDetails = DB::table('TD_DETAILS')->insert($td_details);
                    DB::commit();
                }
                
             }

    
// (
//     [0] => stdClass Object
//         (
//             [id_proof_image] => 9fBhiUMK6E.jpg,ryQlJitZuM.jpg
//             [add_proof_image] => gfbrTunSwB.png
//             [applicant_sequence] => 1
//         )

// )
             if($role == 5){
                $getImage = config('constants.APPLICATION_SETTINGS.IMAGE_MASKING');
                if($getImage == 'AUTO'){
                    CommonFunctions::checkandmaskaadhaar($requestData['form_id']);
                            }
                        }

             if(($role == 3) || ($role == 4))
                {
                   $counter_column = 'l1_counter';
                    if($role == 4)
                    {
                        $counter_column = 'l2_counter';
                        if($requestData['status'] == "approved")
                        {
                            //$updateArray['L2_CLEARED_STATUS'] = 1;

                            $updateL2Status = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                                ->update(['L2_CLEARED_STATUS'=>1]);
                        }
                    }
                    $counter = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                                ->pluck($counter_column)->toArray();
                    $counter = current($counter) + 1;
                    $updateArray[strtoupper($counter_column)] = $counter;
                }



                switch ($role) {
                    case 3:
                        DB::commit();
                        $updateAlreadyreview = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                                ->update(['L1_REVIEW'=>0, 'PHYSICAL_FORM_STATUS'=> 3]);
                        break;
                    case 4:
                        DB::commit();
                        $updateAlreadyreview = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                                ->update(['L2_REVIEW'=>0]);
                        break;
                    case 5:
                        DB::commit();
                        $updateAlreadyreview = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                                ->update(['QC_REVIEW'=>0]);
                        break;
                    case 6:
                        DB::commit();
                        $updateAlreadyreview = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                                ->update(['AUDIT_REVIEW'=>0]);
                        break;

                    default:
                    return json_encode(['status'=>'fail','msg'=>'Error! Invalid Role','data'=>[]]);
                }

                // commented code is for masking aadhar images
                // if($role == 5)
                // {

                //     if($requestData['status'] == "approved")
                //     {
                //         $updateReferenceArray = array();
                //         $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                //                                                 ->where('FORM_ID',$requestData['form_id'])
                //                                                 ->get()->toArray();
                //         if(count($customerOvdDetails) > 0)
                //         {
                //             foreach ($customerOvdDetails as $applicantId => $customerData)
                //             {
                //                 $applicantId++;

                //                 $customerData = (array) $customerData;
                //                 $id = $customerData['id'];
                                
                //                 if($customerData['proof_of_identity'] == 1)
                //                 {
                //                     $idProofImagesCount = substr_count($customerData['id_proof_image'], ',');
                //                     if($idProofImagesCount > 0){
                //                         $idProofImages = explode(',', $customerData['id_proof_image']);
                //                         $idImage = strlen($idProofImages[0]) > 0 ? $idProofImages[0] : $idProofImages[1];                                       
                //                     }else{
                //                         $idImage = $customerData['id_proof_image'];
                //                     }
                                                                        
                //                     $filePath = 'storage/uploads/markedattachments/'.$requestData['form_id'].'/'.$idImage;
                //                     if (File::exists($filePath)) {
                //                         $img = Image::make('storage/uploads/markedattachments/'.$requestData['form_id'].'/'.$idImage);
                //                         $imgWidth  = $img->width();
                //                         $imgHeight = $img->height();

                //                         $canvasHeight = ($imgHeight)/5;
                //                         $canvasWidth = $imgWidth / 2;

                //                         $rightMargin = intval($imgWidth/4);
                //                         $bottomMargin = intval(($imgHeight - 50 )/2.3);


                //                         $GreyCanvas = Image::canvas($canvasWidth, $canvasHeight);
                //                         $GreyCanvas->fill('#505050');
                //                         $img->insert($GreyCanvas , 'bottom-right', $rightMargin, $bottomMargin);
                //                         $img->save('storage/uploads/markedattachments/'.$requestData['form_id'].'/'.$idImage);
                //                     }else{
                //                         $saveuserlog = CommonFunctions::createUserLogDirect('Npc/ReviewController','submittobankClear',$requestData['form_id'].' POI image '.$filePath.' not found for applicant-'.$applicantId,'','',$role);

                //                     }
                //                 }   

                //                 if($customerData['proof_of_address'] == 1)
                //                 {
                //                     $idProofImagesCount = substr_count($customerData['add_proof_image'], ',');
                //                     if($idProofImagesCount > 0){
                //                         $idProofImages = explode(',', $customerData['add_proof_image']);
                //                         $idImage = strlen($idProofImages[0]) > 0 ? $idProofImages[0] : $idProofImages[1];                                       
                //                     }else{
                //                         $idImage = $customerData['add_proof_image'];
                //                     }
                                    
                //                     $filePath = 'storage/uploads/markedattachments/'.$requestData['form_id'].'/'.$idImage;
                //                     if (File::exists($filePath)) {                        
                //                         $img = Image::make('storage/uploads/markedattachments/'.$requestData['form_id'].'/'.$idImage);
                //                         $imgWidth  = $img->width();
                //                         $imgHeight = $img->height();

                //                         $canvasHeight = ($imgHeight)/5;
                //                         $canvasWidth = $imgWidth / 2;

                //                         $rightMargin = intval($imgWidth/4);
                //                         $bottomMargin = intval(($imgHeight - 50 )/2.3);


                //                         $GreyCanvas = Image::canvas($canvasWidth, $canvasHeight);
                //                         $GreyCanvas->fill('#505050');
                //                         $img->insert($GreyCanvas , 'bottom-right', $rightMargin, $bottomMargin);
                //                         $img->save('storage/uploads/markedattachments/'.$requestData['form_id'].'/'.$idImage);
                //                     }else{
                //                         $saveuserlog = CommonFunctions::createUserLogDirect('Npc/ReviewController','submittobankClear',$requestData['form_id'].' POA image '.$filePath.' not found for applicant-'.$applicantId,'','',$role);

                //                     }
                //                 }
                                                              
                //             }
                //         }
                //     }

                // }

                if($role == 3)
                {
                    if($requestData['status'] == "approved")
                    {
                        //$process = CommonFunctions::processCustomerNotification($requestData['form_id'],'CUSTID_EMAIL');
                    }
                }

                // $updateRecordDate = DB::table('ACCOUNT_DETAILS')
                //         ->whereId($requestData['form_id'])
                //         ->update(['UPDATED_BY'=>Session::get('userId'), 
                //                 'UPDATED_AT'=>Carbon::now()]);
                $updateRecordDate=DB::table('ACCOUNT_DETAILS')
                                            ->whereId($requestData['form_id'])
                                            ->update(['UPDATED_BY'=>session::get('user_id'),
                                                      'NPC_REVIEW_TIME' => '',  
                                                    'UPDATED_AT'=>Carbon::now()]);

                                                                


        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','submittobankClear',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function createcustomerid(Request $request){
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $role = Session::get('role');
                if($role == 4)
                {
                    DB::beginTransaction();

                    $isFormValidToContinue = CommonFunctions::isFormValidToContinue($requestData['form_id']);

                    if (!$isFormValidToContinue) {
                        return json_encode(['status'=>'fail','msg'=>'Invalid data for customer ID creation ','data'=>[]]);
                    
                    }

                    // $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],'22');
                    $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('FORM_ID',$requestData['form_id'])
                                            ->where('CUSTOMER_ID', null)
                                            ->get()->toArray();

                    $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$requestData['form_id'])->get()->toArray();
                    $accountDetails = (array)  current($accountDetails);

                    if(($accountDetails['delight_scheme'] == 5) && ($accountDetails['account_type'] == 1)){

                        $delightScheme = true;
                    }else{

                        $delightScheme = false;
                    }

                    $schemeDetails = DB::table('SCHEME_CODES')->where('ID',$accountDetails['scheme_code'])->get()->toArray();

                    $schemeDetails = (array)  current($schemeDetails);


                    $custIdsCreated = [];
                    $custIdsFailed = 0;
                    $errArray = [];

                    if(count($customerDetails) > 0)
                    {
                        foreach($customerDetails as $customerData)
                        {
                            $customerData = (array) $customerData;

                            if($customerData['customer_id'] == '')
                            {
                                // $customerID = $this->createcustomerid($requestData['form_id']);
                                if($delightScheme && $customerData['applicant_sequence'] == '1'){

									$amendDetails = array('', '');
                                    $amendDetails = AmendApi::AmendCustApi($customerData,$requestData['form_id']);
                                  
                                    if(isset($amendDetails[0]) && $amendDetails[0] == 'success'){
                                    //    DB::beginTransaction();
                                        // $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],'23');
                                        DB::commit();
                                        array_push($custIdsCreated, $amendDetails[1]);
                                        //return json_encode(['status'=>'success','msg'=>'Amend Data inserted successfully','data'=>[]]);
                                    }else{
                                        $details = isset($amendDetails[1]) && $amendDetails[1] !=''?$amendDetails[1]:'';
                                        return json_encode(['status'=>'fail','msg'=>'Amendment API failed. '.$details,'data'=>[]]);
                                    }

                                }else{
                                    
                                    if($accountDetails['l2_cleared_status'] == 0){
                                        return json_encode(['status'=>'fail','msg'=>'Please clear l2 first.','data'=>[]]);
                                    }

                                    $customerIdDetails = Api::createcustomerid($customerData);
                                    if($customerIdDetails['status'] == "Success")
                                    {
                                        $customerID = $customerIdDetails['data'];
                                        array_push($custIdsCreated, $customerID);

                                    }else{
                                        if(count($customerDetails) == 1){ // if Single record
                                            $msg = 'Customer ID creation API failed.'.$customerIdDetails['data'].': '.$customerIdDetails['message'];
                                            return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                                        }else{
                                            $custIdsFailed++;
                                            array_push($errArray, $customerIdDetails['data'].': '.$customerIdDetails['message']);
                                        }

                                    }
                              }
                            }
                            //else{$customerID = $customerData['customer_id'];}
                        }
                    }else{
                        
                        if ($accountDetails['is_new_customer'] == 1) { //NTB
                            $notif = NotificationController::processNotification($requestData['form_id'],'CUSTID_CREATED');
                            $customNotif = CommonFunctions::processCustomerNotification($requestData['form_id'],'CUSTID_EMAIL');
                        }

                        if ($accountDetails['is_new_customer'] == 0 && ( $accountDetails['source'] == '' || $accountDetails['source'] == null)) { //ETB
                            $notif = NotificationController::processNotification($requestData['form_id'],'CUSTID_CREATED');
                            $customNotif = CommonFunctions::processCustomerNotification($requestData['form_id'],'CUSTID_EMAIL');
                        }


                        return json_encode(['status'=>'success','msg'=>'No Customer id to be created','data'=>[]]);
                    }

                    if(count($custIdsCreated)>0){

                        try{
                        $saveStatus = CommonFunctions::saveStatusDetails($requestData['form_id'],'22');
                            $notif = NotificationController::processNotification($requestData['form_id'],'CUSTID_CREATED');
                            $customNotif = CommonFunctions::processCustomerNotification($requestData['form_id'],'CUSTID_EMAIL');

                            // foreach($custIdsCreated as $custidKey => $custidvalues){
                            //     ApiCommonFunction::insertIntoApiQueue($requestData['form_id'],'ApiCommonFunction','internetBankingWrapper','Common',null,$custidKey+1,Array($custidvalues),Carbon::now()->addMinutes(15));

                            //     if ($accountDetails['is_new_customer'] == 0 && $accountDetails['source'] == '' || $accountDetails['source'] == null){
                            //         ApiCommonFunction::insertIntoApiQueue($requestData['form_id'],'ApiCommonFunction','kycUpdateWrapper','Common',null,$custidKey+1,Array($requestData['form_id'],$custidvalues,'PHYSICAL'),Carbon::now()->addMinutes(15));
                            //     }

                            // }

                        }catch(\Illuminate\Database\QueryException $e) {
                            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
                        }
                        DB::commit();
                        // for JS consumption return first ID created. Also return full array.
                        return json_encode(['status'=>'success','msg'=>'Customer id is created successfully','data'=>[$custIdsCreated[0],'idCreated'=>$custIdsCreated, 'idsWithError'=>$errArray]]);

                    }else{
                        //rollback db transactions if any error occurs in query
                        DB::rollback();
                        return json_encode(['status'=>'fail','msg'=>'Customer id is not created','data'=>[]]);
                    }
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Unauthorized access','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getaddressdatabypincode(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $addressData = CommonFunctions::getAddressDataByPincode($requestData['pincode']);
                if(count($addressData) > 0){
                    $addressData = (array) current($addressData);
                    array_push($addressData, $requestData['id']);
                    return json_encode(['status'=>'success','msg'=>'AddressData Details','data'=>$addressData]);
                }else{
                    $msg = 'Error! Pincode not found in registered database. Please contact NPC admin';
                    return json_encode(['status'=>'fail','msg'=>$msg,'data'=>[]]);
                }
            }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getifsccode(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch scheme data
                $ifscCode = DB::table('BANK')->whereId($requestData['id'])
                                                        ->get()->toArray();
                $ifscCode = (array) current($ifscCode);
                if($ifscCode){
                    return json_encode(['status'=>'success','msg'=>'Scheme Data Found.','data'=>$ifscCode]);
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



    public static function checkClearingStatus($formId){

            $fundingStatus = '';

            $ovdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
            $ovdDetails = (array)  current($ovdDetails);
            $refNum = ltrim($ovdDetails['reference'],'0');      // Remove leading 0 (zeros) in Cheque number while comparing!

            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
            $accountDetails = (array)  current($accountDetails);

            $fundingStatusDetails = DB::table('CHEQUE_CLEARED')->where('TRAN_RMKS',$accountDetails['aof_number'])
                                                    ->where(DB::raw('TRIM(INSTRMNT_ID)'),$refNum)
                                                    ->where('TRAN_AMT',$ovdDetails['amount'])
                                                    ->get()->toArray();

            if(count($fundingStatusDetails) > 0 && isset($fundingStatusDetails[0]->tran_date)){
                echo '...Match in funding records...'.PHP_EOL;
                  $fundingStatus = $fundingStatusDetails[0]->tran_date;
            }

            return $fundingStatus;

    }



    public static function checkNeftRtgsStatus($formId){

            $fundingStatus = '';
            $ovdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                ->get()->toArray();
            $ovdDetails = (array)  current($ovdDetails);

            $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
            $accountDetails = (array)  current($accountDetails);



            $fundingStatusDetails = DB::table('NEFT_RTGS_FUNDS_CLEARED')
                                                    //->where('UTR',$ovdDetails['reference'])
													->where(function($query) use($ovdDetails){
														$query->where('UTR', $ovdDetails['reference'])
														->orWhere('TRN', $ovdDetails['reference']);
													})
                                                    ->where('SENDER_IFSC',$ovdDetails['ifsc_code'])
                                                    ->where('AMOUNT',$ovdDetails['amount'])
                                                    ->get()->toArray();
            if(count($fundingStatusDetails) > 0 && isset($fundingStatusDetails[0]->valuedate) )
                {
                    echo '...Match in funding records...'.PHP_EOL;
                  $fundingStatus = $fundingStatusDetails[0]->valuedate;
                }

            return $fundingStatus;

    }

    public function checkfundingstatus(Request $request)
    {
        try{
            $errorCondition = false;
            if($request->ajax()){
                $requestData = $request->get('data');
                if(isset($requestData['form_id'])){
                    $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$requestData['form_id'])->get()->toArray();
                    $customerDetails = (array) current($customerDetails);
                    if(count($customerDetails) > 0 && isset($customerDetails['initial_funding_type'])){
                        return Self::checkfundingstatus_func($requestData['form_id'], $customerDetails['initial_funding_type']);
                    }
                }
             }
             // By default if any of the above IF fails, send back error!
             return json_encode(['status'=>'fail','msg'=>'Error: Funding check failed!','data'=>[]]);

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function checkfundingstatus_func($formId, $fundingType)
    {
        try{

            $isFormValidToContinue = CommonFunctions::isFormValidToContinue($formId);

            if(!$isFormValidToContinue){
                echo '...form is NOT valid!'.PHP_EOL;
                return json_encode(['status'=>'fail','msg'=>'Invalid data for check funding','data'=>[]]);
            }
            echo '...form is valid!'.PHP_EOL;
			
			$checkIfFundingDone = DB::table('FINCON')->where('FORM_ID', $formId)
                                                ->where('FUNDING_STATUS', 'Y')->get()->toArray();
			if (count($checkIfFundingDone) > 0){
                echo '...funding already cleared!'.PHP_EOL;
                return json_encode(['status'=>'success','msg'=>'Funding already cleared','data'=>[]]);
			}
			
            $fundingCleared = '';
            switch($fundingType) {
                case '1':
                    echo '...checking Cheque..'.PHP_EOL;
                     $fundingCleared  = Self::checkClearingStatus($formId);
                     echo '...checking Cheque Done..'.PHP_EOL;
                    break;
                case '2':
                    echo '...checking NEFT..'.PHP_EOL;
                    $fundingCleared  = Self::checkNeftRtgsStatus($formId);
                    echo '...checking NEFT Done..'.PHP_EOL;
                    break;

                case '3':                           //Call Centre
                    $fundingCleared  = Carbon::now()->format('Y-m-d H:i:s');
                    break;

                case '5':
                    $fundingCleared  = Carbon::now()->format('Y-m-d H:i:s');
                    break;

                default:
                    return json_encode(['status'=>'fail','msg'=>'Error! No valid funding type found','data'=>[]]);
                    break;
            }

            // $fundingCleared would have false or funding cleared date/
            if($fundingCleared != ''){
                DB::beginTransaction();
                $saveStatus = CommonFunctions::saveStatusDetails($formId,'23');
                $updatefundingStatus = CommonFunctions::updateFundingStatusY($formId, $fundingCleared);
                DB::commit();
                // Rules::postFundingCreateAccount($formId);
                return json_encode(['status'=>'success','msg'=>'Funding is cleared','data'=>[]]);
            }else{
                return json_encode(['status'=>'fail','msg'=>'Funding is not cleared','data'=>[]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request, 'Error in Funding Check Job');
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','checkfundingstatus_func',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkfundingforall(){

        $formIDs = DB::table('FINCON')->select('FINCON.FORM_ID','FINCON.FUNDING_TYPE')->where('FUNDING_STATUS','N')
                                    ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','FINCON.FORM_ID')
                                    ->where('ACCOUNT_DETAILS.NEXT_ROLE',1)
                                    ->where('CREATED_DATE','>',DB::raw('ADD_MONTHS(SYSDATE,-3)')) 
                                    ->orderBy('CREATED_DATE','DESC')    
                                    ->take(1000)
                                    ->get()->toArray();
 
        for($f=0; $f < count($formIDs); $f++){
            echo $f.' Processing..'.$formIDs[$f]->form_id.' with funding type '.$formIDs[$f]->funding_type.PHP_EOL;
            if(isset($formIDs[$f]->form_id) && isset($formIDs[$f]->funding_type)){
                Self::checkfundingstatus_func($formIDs[$f]->form_id, $formIDs[$f]->funding_type);
            }
        }

    }


    public function createaccountnumber(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $createaccountnumberInternal = Self::createaccountnumberInternal($requestData['form_id'], $requestData['type']);

                return json_encode(['status'=>$createaccountnumberInternal['status'],'msg'=>$createaccountnumberInternal['msg'],'data'=>$createaccountnumberInternal['data']]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function createaccountnumberInternal($formId, $type = '')
    {
        try{
                $isFormValidToContinue = CommonFunctions::isFormValidToContinue($formId);
                    
                if (!$isFormValidToContinue) {
                    return (['status'=>'fail','msg'=>'Invalid data for Account ID creation','data'=>[]]);
                }

                $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();

                if(count($accountDetails)==0 || count($accountDetails) > 1){
                    return (['status'=>'fail', 'msg'=> 'Invalid or missing data for Account creation!', 'data'=>[]]);
                }

                $accountType = $accountDetails[0]->account_type;

                $SArequired = false;
                $TDrequired = false;
                $CArequired = false;

                // For Savings or ComboSaving - Check if account_no already created
                if(($accountType==1  || $accountType==4) && ($accountDetails[0]->account_no == '' || $accountDetails[0]->account_no == null)){
                    $SArequired = true;
                }

                  // $entityAccount = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                  // echo "<pre>";print_r($entityAccount);exit; 
                // Special case for account type 2 == CA 229 SA 229
                    $entityAccount = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                if($accountType == 2 && ($accountDetails[0]->account_no == '' || $accountDetails[0]->account_no == null)) {
                    $SArequired = true;                    
                }


                if($accountType == 2 && count($entityAccount)>0){
                    if($entityAccount[0]->entity_account_no == '' || $entityAccount[0]->entity_account_no == null){
                            $CArequired = true;
                    }                    
                }

                // For TD & Combo  - Check if td_account_no already created
                if(($accountType==3 || $accountType==4) && ($accountDetails[0]->td_account_no == '' || $accountDetails[0]->td_account_no == null)){
                    $TDrequired = true;
                }

                $accountCheck = CommonFunctions::accountCheck($formId);

                if(!$accountCheck){
                    return (['status'=>'fail','msg'=>'Error! Incomplete Data to Create Account','data'=>[]]);
                }
                
                $combined_msg = ''; 
                $combined_data = array('SA' => '', 'CA' => '', 'TD' => '');
                $status = 'success';

                if($SArequired == true){
                        $sa_response = ($this->createaccountnumberSA($formId, $type));
                          // echo "<pre>";print_r($sa_response);exit;
                        $combined_msg .= 'SA: '.$sa_response['msg'].' ';
                        $combined_data['SA'] = $sa_response['data'];
                        if($sa_response['status'] != 'success'){
                            $status = 'fail';
                        }
                }

                if($CArequired == true){
                        $ca_response = ($this->createaccountnumberCA($formId, $type));
                        if (isset($ca_response['status']) && $ca_response['status'] == 'fail') {
                            return (['status'=>$ca_response['status'],'msg'=>$ca_response['msg'],'data'=>[]]);
                        }
                        $combined_msg .= 'CA: '.$ca_response['msg'].' ';
                        $combined_data['CA'] = $ca_response['data'];
                        if($ca_response['status'] != 'success'){
                            $status = 'fail';
                        }
                }

                if($TDrequired == true){
                        $td_response = ($this->createaccountnumberTD($formId, $type));
                        $combined_msg .= 'TD: '.$td_response['msg'].' ';
                        $combined_data['TD'] = $td_response['data'];
                        if($td_response['status'] != 'success'){
                            $status = 'fail'; 
                        }
                }

                // if($status == 'success'){
                //     ApiCommonFunction::insertIntoApiQueue($formId,'ApiCommonFunction','signatureAccountWrapper','Common',null,null,Array($formId),Carbon::now()->addMinutes(15));
                // }

                $combined_data['ACCOUNT_TYPE'] = $accountType;

                return (['status'=>$status, 'msg'=> $combined_msg,'data'=>[$combined_data]]);

               
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','createaccountnumberInternal',$eMessage,'',$formId);
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function createaccountnumberSA($formId, $type = '')
    {
        try{
                DB::beginTransaction();
                $customerIdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
																	->where('APPLICANT_SEQUENCE',1)
                                                                    ->pluck('customer_id')->toArray();
                $customerID = current($customerIdDetails);

                $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
                    $accountDetails = (array)  current($accountDetails);

                    if(($accountDetails['delight_scheme'] == 5) && ($accountDetails['account_type'] == 1)){

                        $delightScheme = true;
                    }else{

                        $delightScheme = false;
                    }
                //elite flow 
                if($accountDetails['scheme_code'] == 11){
                    $sbelite = true;
                }else{
                    $sbelite = false;
                }
                if($sbelite){

                    $accountNumberDetails = Api::createeliteaccountid($formId);
                }
                //end elite flow
                if(!$sbelite){

                if($delightScheme){
                  $accountNumberDetails = AmendApi::AmendAccountApi($formId,$customerID);

                  if($accountNumberDetails['status'] == "Success"){
                         $accountNumberDetails = $accountNumberDetails;
                        //  echo "test";exit;
                        $dataArray = array();
                        array_push($dataArray,$accountNumberDetails['data']);
                        array_push($dataArray,$formId);
                        freezeUnfreezeApi::unfreezeApi(array($dataArray));
                  }else{
                        $amendMsg = $accountNumberDetails['msg'];
                        return (['status'=>'fail','msg'=>$amendMsg,'data'=>[]]);
                  }
                }else{
                   $accountNumberDetails = Api::createaccountid($formId,$customerID);
                }
                }

                if($accountNumberDetails['status'] == "Success")
                {
                    $accountNumber = $accountNumberDetails['data'];
                    $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                    ->pluck('account_type')->toArray();
                    $accountType = current($accountDetails);

                    if($accountNumber){
                        if($accountType == 1)
                        {

                            $saveStatus = CommonFunctions::saveStatusDetails($formId,'24','done by '.$type);

                            NotificationController::processNotification($formId,'ACCOUNTNO_CREATED');

                            // ApiCommonFunction::insertIntoApiQueue($formId,'ApiCommonFunction','signatureAccountWrapper','Common',null,null,Array($formId),Carbon::now()->addMinutes(15));

                            //$sendL2Notification = CommonFunctions::processCustomerNotification($requestData['form_id'],'L2_EMAIL_01');
                        }
                        // Self::executePostSA($formId);
                        DB::commit();
                        return (['status'=>'success','msg'=>'Account number is created successfully','data'=>[$accountNumber]]);
                    }else{
                        //rollback db transactions if any error occurs in query
                        DB::rollback();
                        return (['status'=>'fail','msg'=>'Savings Account not created successfully','data'=>[]]);
                    }
                }else{
                    //rollback db transactions if any error occurs in query
                    // DB::rollback();
                    $msg = 'Account number creation API failed.'.$accountNumberDetails['message'];
                    return (['status'=>'fail','msg'=>$msg,'data'=>[$accountNumberDetails]]);
                }
            
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','createaccountnumberSA',$eMessage,'',$formId);
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function createaccountnumberTD($formId, $type = '')
    {
        try{
                DB::beginTransaction();

                // $applicationSettings = DB::table('APPLICATION_SETTINGS')->where('FIELD_NAME','TD_ACCOUNT')
                //                                                           ->get()->toArray();
                // $applicationSettings = (array) current($applicationSettings);

                $applicationSettings = CommonFunctions::getapplicationSettingsDetails('TD_ACCOUNT');

                if($applicationSettings == 'API'){
                    $accountDetails = DB::table('ACCOUNT_DETAILS')->select('SCHEME_CODE','TD_SCHEME_CODE','ACCOUNT_TYPE')->whereId($formId)->get()->toArray();
                    $accountDetails = (array) current($accountDetails);
                    if($accountDetails['td_scheme_code'] != ''){
                        $accountDetails['scheme_code'] = $accountDetails['td_scheme_code'];
                    }
                    $checkRdTd = DB::table('TD_SCHEME_CODES')->whereId($accountDetails['scheme_code'])->value('td_rd');
                    if($checkRdTd == 'RD'){
                        $tdAccountApi = Api::createaccountnumber_rdapi($formId,$accountDetails['scheme_code']);
                    }else{
                        $tdAccountApi = Api::createaccountnumber_tdapi($formId);
                    }
                    
                    if($tdAccountApi['status'] == 'success'){
                        $updateAccountDetails = [
                                                 'TD_ACCOUNT_NO'=>$tdAccountApi['data'],
                                                 'APPLICATION_STATUS'=> '14'
                                                ];
               
                       $saveComments = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update($updateAccountDetails);                   
                       NotificationController::processNotification($formId,'ACCOUNT_NO_CREATED');
                        $saveStatus = CommonFunctions::saveStatusDetails($formId,'24','done by '.$type);
                        $saveStatus = CommonFunctions::saveStatusDetails($formId,'25','done by '.$type);

                        if($accountDetails['account_type'] == 3){
                             $udpateTDFTRStatusAccount = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                        ->update(['FUND_TRANSFER_STATUS'=>1]);
                       
                            $udpateTDFTRStatus =  CommonFunctions::updateTDFtrStatusY($formId,'TD API');
                            Self::markFormForQCInternal($formId);
                        }
                        DB::commit();
                        Rules::postAccountIdApiQueue($formId,3);
                        return (['status'=>'success','msg'=>'TD Account Created Successfully','data'=>['td_account_no' => $tdAccountApi['data']]]);
                    }else{
                        return (['status'=>'error','msg'=>'TD Account Not Created successfully.','data'=>[]]);
                    }
                }

                $saveTDDetails = CommonFunctions::saveTDAccountDetails($formId);
                if($saveTDDetails != '')
                {
                    $saveStatus = CommonFunctions::saveStatusDetails($formId,'24', 'done by '.$type);
                    $saveStatus = CommonFunctions::saveStatusDetails($formId,'25', 'done by '.$type);
                    
                    $saveTdEntryDone = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                                                                    ->update(['TD_ENTRY_DONE'=>'Y']);
                    DB::commit();
                    $msg = 'TD Request inserted in Finacle successfully.';
                                return (['status'=>'success','msg'=>$msg,'data'=>[]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    // DB::rollback();
                    $msg = 'TD Request insert in Finacle failed.';
                                    return (['status'=>'fail','msg'=>$msg,'data'=>[]]);
                }
            
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','createaccountnumberTD',$eMessage,'',$formId);
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function createaccountnumberCA($formId, $type = '')
    {
        try{
                DB::beginTransaction();
                // echo "<pre>";print_r($formId);exit;

                $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('form_Id',$formId)
                                                                    ->get()->toArray();

                $customerDetails = (array)current($customerDetails);                                                    
                $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                                                              ->get()->toArray();
                $accountDetails = (array)current($accountDetails);                                                    
                // echo "<pre>";print_r($accountDetails);exit;
                $currentApiResponse = CurrentApi::CurrentAccountApi($formId);
                if(isset($currentApiResponse['status']) && isset($currentApiResponse['message']) && $currentApiResponse['status']=='Error')
                {
                    return (['status'=>'fail','msg'=>$currentApiResponse['message'],'data'=>[]]);
                }else{
                    $currentApiId = $currentApiResponse['data'];
                    $entitydetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)
                                                                ->update(['ENTITY_ACCOUNT_NO' => $currentApiId]);
                    if($entitydetails != '')
                    {
                        NotificationController::processNotification($formId,'ACCOUNTNO_CREATED');
                        Rules::postAccountIdApiQueue($formId,$accountDetails['account_type'],$currentApiId);
                        $saveStatus = CommonFunctions::saveStatusDetails($formId,'24', 'done by '.$type);

                        // $saveTdEntryDone = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)
                        //                                                 ->update(['TD_ENTRY_DONE'=>'Y']);
                        DB::commit();
                        $msg = 'Current Account number is created successfully';
                                    return (['status'=>'success','msg'=>$msg,'data'=>[$currentApiId]]);
                    }else{
                        //rollback db transactions if any error occurs in query
                        // DB::rollback();
                        $msg = 'Current Account not created successfully';
                                        return (['status'=>'fail','msg'=>$msg,'data'=>[]]);
                    }

                }


            
                // $customerID = current($customerIdDetails);

                // $saveTDDetails = CommonFunctions::saveTDAccountDetails($formId);
                // $saveTDDetails = Api::createTdaccountid($requestData['form_id'],
                //     $customerID);
            
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','createaccountnumberCA',$eMessage,'',$formId);
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function fundtransfer(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $checkFundTransfer = Self::checkFundTransfer($requestData['form_id'], $requestData['type']);

                return json_encode(['status'=>$checkFundTransfer['status'],'msg'=>$checkFundTransfer['msg'],'data'=>$checkFundTransfer['data']]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateFieldValue(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $getConfigRules = config('l1_Rules.'.$requestData['config_id']);

 				$updateDBvalue = DB::table($getConfigRules['table'])->where('FORM_ID',$requestData['form_id']);
 				if($getConfigRules['multi_applicant'] == 'Yes'){
 					$updateDBvalue->where('APPLICANT_SEQUENCE',$requestData['applicant_seq']);
 				}
 				switch ($getConfigRules['validation_type']){
 					case 'pincode':
 					$addressData = CommonFunctions::getAddressDataByPincode($requestData['new_value']);
 					$addressData = (array) current($addressData);
 						$updateDBvalue->update([$getConfigRules['field_name'] => $requestData['new_value'],
                                                                    $getConfigRules['state_name'] => $addressData['statedesc'],
                                                                    $getConfigRules['city_name'] => $addressData['citydesc']]);
 						break;
                    case 'ifsc':
                    $getifsccode  = DB::table('BANK')->where('IFSC_CODE_PREFIX',substr($requestData['new_value_text'], 0,4))->count();
                    if($getifsccode == 0){
                        return (['status'=>'fail','msg'=>'Invalid IFSC Code ','data'=>[]]);
                    }
                    $updateDBvalue->update([$getConfigRules['field_name'] => $requestData['new_value']]);
                    break;
                    case 'email_domain':
                    $domainId = explode("@",$requestData['new_value']);
                    $getvaliddomain = DB::table('RESTRICTED_DOMAIN_ID')->select('DOMAIN_ID')->where('DOMAIN_ID',strtolower($domainId[1]))->get()->toArray();

                    if(count($getvaliddomain) > 0){
                        return json_encode(['status'=>'fail','msg'=>'Given Email Domian (Temporary) Name is not permitted for Applicant-'.$requestData['applicant_seq'],'data'=>[]]);
                    }else{
                        $updateDBvalue->update([$getConfigRules['field_name'] => $requestData['new_value']]);
                    }
                    break;
 				
                	default:

                    if($getConfigRules['validation_type'] == 'datefield'){
                        $requestData['new_value'] = date_format(date_create($requestData['new_value']), "Y-m-d H:i:s");
                    }

                    if($getConfigRules['field_name'] == 'relationship'){
                        $getConfigRules['field_name'] = 'RELATINSHIP_APPLICANT';
                    }
 						$updateDBvalue->update([$getConfigRules['field_name'] => $requestData['new_value']]);
 						break;
 				}

                if($updateDBvalue){
                    $insertLogs = ['FORM_ID' => $requestData['form_id'],
                                   'APPLICANT_SEQUENCE' => $requestData['applicant_seq'],
                                   'FIELD_NAME' => $getConfigRules['field_name'],
                                    'OLD_VALUE' => $requestData['old_value'],   
                                    'NEW_VALUE' => $requestData['new_value_text'],
                                    'CREATED_BY'=>Session::get('userId')];
                    $insertL1Reviewlog = DB::table('L1_EDIT_LOG')->insert($insertLogs);
                    if($insertL1Reviewlog){
                        return (['status'=>'success','msg'=>'Field Update Successfully','data'=>[]]);
                    }
                }

            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function checkFundTransfer($formId, $type = '')
    {
        try {
            // DB::beginTransaction();

            $isFormValidToContinue = CommonFunctions::isFormValidToContinue($formId);

            if (!$isFormValidToContinue) {
                return (['status'=>'fail','msg'=>'Invalid data for FTR ','data'=>[]]);
            }

            $saveFTRDetails = false;

            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->get()->toArray();

            if(count($accountDetails) == 0){
                return (['status'=>'fail','msg'=>'Error! Incomplete Data for fund transfer','data'=>[]]);
            }
            $accountDetails = (array) current($accountDetails);
            $aofNumber = $accountDetails['aof_number'];
            $accountNumber = $accountDetails['account_no'];
            if($accountDetails['account_type'] == '2'){
                $accountNumber = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->value('ENTITY_ACCOUNT_NO');
            }

            $fundingDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                            ->get()->toArray();
            $fundingDetails = (array) current($fundingDetails);
            //$uploadSignature = Api::uploadSignature($formId, $fundingDetails['customer_id']);

            $preFlightFTR = CommonFunctions::preFlightFTR($formId);
            
            if (!$preFlightFTR) {

                return (['status'=>'fail','msg'=>'Error! funding not cleared, Hence can not processed','data'=>$preFlightFTR]);
            }


            $preFlightAccountIds = CommonFunctions::preFlightAccountIds($formId);
            // echo "<pre> form";print_r($formId);

            if (!$preFlightAccountIds) {
                return (['status'=>'fail','msg'=>'Error! Account Id not created, Hence can not processed','data'=>$preFlightFTR]);
            }
                	// echo "<pre>";print_r($fundingDetails);exit;

            if(count($fundingDetails) > 0)
            {
                if(($fundingDetails['initial_funding_type'] == 1) || ($fundingDetails['initial_funding_type'] == 2))
                {
                    if($accountDetails['account_type'] == '3'){
                        $updateFINCON = CommonFunctions::updateFtrStatusY($formId,'Automated for td Account');
                    }else{

                    $fundTransfer = Api::fundtransfer($formId,$accountNumber,$fundingDetails,$aofNumber);
                    if(!isset($fundTransfer['Body']) || !isset($fundTransfer['Body']['transferResponse'])){
                        
                        return (['status'=>'fail','msg'=>'Auth error or invalid FTR response!','data'=>$fundTransfer]);
                    }
                    $FTRDetails = $fundTransfer['Body']['transferResponse'];

                    if(isset($FTRDetails['transactionStatus']) && $FTRDetails['transactionStatus'] == "COMPLETED")

                    {
                        $updateCustomerId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                        ->update(['FUND_TRANSFER_STATUS'=>1]);
                        $updateFINCON = CommonFunctions::updateFtrStatusY($formId,$FTRDetails['bankReferenceNo']);
                    }else{
                         return (['status'=>'fail','msg'=>'Fund transferred Error','data'=>$fundTransfer]);
                    }

                    $FTRArray = ['FORM_ID'=>$formId,
                                'REQUEST_REFERENCE_NO'=>$FTRDetails['requestReferenceNo'],
                                'TRANSACTION_STATUS'=>$FTRDetails['transactionStatus'],
                                'MESSAGE'=>$FTRDetails['message'],
                                'CREATED_BY'=>Session::get('userId')];
                    $saveFTRDetails = DB::table('FTR_RESPONSE')->insert($FTRArray);
                }
                }


                if($fundingDetails['initial_funding_type'] == 11 || $fundingDetails['initial_funding_type'] == 12)
                {
                    //testing fund transfer done for DSA
                    $oaoStatusDetails = DB::table('OAO_STATUS')->where('FUND_RECEIVED','Y')
                                                                ->where('FTR', null)
                                                                ->where('CUSTOMER_ID','Y')
                                                                ->where('ACCOUNT_ID','Y')
                                                                ->where('FORM_ID', $formId)
                                                                ->where('FREEZE_1', 'Y')
                                                                ->get()->toArray();

                    if(count($oaoStatusDetails) > 0 ){
                        $oaoStatusDetails = current($oaoStatusDetails);
                        $checkFTRDone = DB::table('FINCON')->where('FORM_ID',$oaoStatusDetails->form_id )
                                                ->where('FUNDING_STATUS', 'Y')
                                                ->whereIn('FUNDING_TYPE',['11','12'])
                                                ->where('FTR_STATUS','N')
                                                ->get()->toArray();

                        if (count($checkFTRDone) > 0) {
                            $checkFTRDone = current($checkFTRDone);

                            $checkUnfreeze = OaoCommonFunctions::freezeUnfreeze($oaoStatusDetails->oao_id, 'Unfreeze', 'T');
                                //for testing purpose updateFtrStatusY function is out of if loop
                            // echo "<pre>";print_r($checkUnfreeze);exit;
                            if($checkUnfreeze){

                                // echo "<pre> form id : ";print_r($oaoStatusDetails->form_id);
                                $updateFINCON = CommonFunctions::updateFtrStatusY($oaoStatusDetails->form_id, '12345');
                            }

                            $checkfreeze = OaoCommonFunctions::freezeUnfreeze($oaoStatusDetails->oao_id, 'Freeze', 'T');
                            if($checkfreeze){
                                $saveFTRDetails = DB::table('OAO_STATUS')->where('OAO_ID', $oaoStatusDetails->oao_id)->update(['FREEZE_2'=> 'Y']); 
                            }else{
                                $saveFTRDetails = DB::table('OAO_STATUS')->where('OAO_ID'   , $oaoStatusDetails->oao_id)->update(['FREEZE_2'=> 'N']); 
                            }
                        }

                    }
                    $saveFTRDetails = true;
                }
               

                if($fundingDetails['initial_funding_type'] == 5 && $fundingDetails['others_type'] == 'zero'){
                    $updateCustomerId = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                        ->update(['FUND_TRANSFER_STATUS'=>1]);
                    $updateFINCON = CommonFunctions::updateFtrStatusY($formId,'Auto Zero Balance Updated');
                    $saveFTRDetails = true;
                    return (['status'=>'success','msg'=>'Fund transfer check completed','data'=>['account_type'=>$accountDetails['account_type'],'form_Id'=>$formId]]);
                }

                if($saveFTRDetails){
                	// echo "<pre>";print_r('success');
                    $saveStatus = CommonFunctions::saveStatusDetails($formId,'25', 'done by '.$type);

                    // DB::commit();
                    return (['status'=>'success','msg'=>'Fund transferred completed','data'=>['account_type'=>$accountDetails['account_type'], 'form_Id' => $formId ]]);
                }else{
                	// echo "<pre>";print_r('fail');
                    //rollback db transactions if any error occurs in query
                    // DB::rollback();
                    return (['status'=>'fail','msg'=>'Fund transfer could not be completed','data'=>[]]);
                }
            }else{
                return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','checkFundTransfer',$eMessage,'',$formId);
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function generatequeryid(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $updateDeDupeStatus = 0;
                $msg = '';
                $responseArray = [];
                //$dedupe_check = env('DEDUPE_CHECK', 'Strict');
                DB::beginTransaction();
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->select('CUSTOMER_OVD_DETAILS.*','ACCOUNT_DETAILS.AOF_NUMBER','ACCOUNT_DETAILS.SOURCE')
                                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$requestData['formId'])
                                            //->where('CUSTOMER_OVD_DETAILS.IS_NEW_CUSTOMER',1)
                                            ->get()->toArray();
                //$customerOvdDetails = (array) current($customerOvdDetails);
                if(count($customerOvdDetails) > 0)
                {

                $status = 'success';
                $msg = 'Query is Generated Successfully';
                    // echo "test";
                foreach($customerOvdDetails as $customerOvdData){
                    if($customerOvdData->source != 'CC'){
                    $customerOvdData = (array) $customerOvdData;
                    $dedupeStatusDetails = Api::checkdedupe($customerOvdData,$customerOvdData['aof_number'],
                                                                                            $customerOvdData['form_id']);

                    if($dedupeStatusDetails['status'] == "Success")
                    {
                        $queryId = $dedupeStatusDetails['data'];
                        $updateDeDupeQID = DB::table("CUSTOMER_OVD_DETAILS")->whereId($customerOvdData['id'])
                                                                            ->update(['QUERY_ID'=>$queryId]);
                            if($updateDeDupeQID)
                            {
                                DB::commit();
                                $responseArray[] = $queryId;

                            }else{
                                DB::rollback();
                                $status = 'fail';
                                 $msg = 'Error! Please try again';

                            }
                    }else{
                            //rollback db transactions if any error occurs in query
                            DB::rollback();
                            $msg = 'DeDupe API failed.'.$dedupeStatusDetails['errorCode'].': '.$dedupeStatusDetails['message'];
                            $status = 'fail';
                    }
                }
                }
              return json_encode(['status'=>$status,'msg'=>$msg,'data'=>$responseArray]);
            }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkdedupestatus(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $updateDeDupeStatus = 0;
                DB::beginTransaction();
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.ID',$requestData['accountId'])
                                            ->get()->toArray();
                $customerOvdDetails = (array) current($customerOvdDetails);
                if(count($customerOvdDetails) > 0)
                {
                    $dedupeStatusDetails = Api::checklivystatus($customerOvdDetails['query_id'],$customerOvdDetails['form_id']);

                    if(count($dedupeStatusDetails)==0 || !isset($dedupeStatusDetails['response']['data'][0]['decisionFlag'])){
                        return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                    }
                    $deDupeStatus = $dedupeStatusDetails['response']['data'][0]['decisionFlag'];
                    $deDupeReference = substr($dedupeStatusDetails['response']['data'][0]['remarks'],0,100);

                    $deDupeReference = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeReference);
                    $deDupeStatus = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeStatus);

                    $updateDeDupeStatus = DB::table("CUSTOMER_OVD_DETAILS")->whereId($customerOvdDetails['id'])
                                                                        ->update(['DEDUPE_STATUS'=>$deDupeStatus,'DEDUPE_REFERENCE' => $deDupeReference]);
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Updated dedupe response.','data'=>[]]);
                }

                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);

            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function checkdedupestatusall(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');

                if(!isset($requestData['formId']) || $requestData['formId'] == ''){
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }

                $updateDeDupeStatus = 0;
                DB::beginTransaction();
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$requestData['formId'])
                                            // ->where('CUSTOMER_OVD_DETAILS.IS_NEW_CUSTOMER',1)
                                            ->get()->toArray();
                //$customerOvdDetails = (array) current($customerOvdDetails);
                //if(count($customerOvdDetails) > 0)

                $checkFail = false;
                for($rec=0; $rec < count($customerOvdDetails); $rec++)
                {
                    $dedupeStatusDetails = Api::checklivystatus($customerOvdDetails[$rec]->query_id,$customerOvdDetails[$rec]->form_id);
                    if(count($dedupeStatusDetails)==0 || !isset($dedupeStatusDetails['response']['data'][0]['decisionFlag'])){
                        $checkFail = true;
                        //return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                    }else{
                        $deDupeStatus = $dedupeStatusDetails['response']['data'][0]['decisionFlag'];
                        $deDupeReference = $dedupeStatusDetails['response']['data'][0]['remarks'];
                        $deDupeReference = substr($dedupeStatusDetails['response']['data'][0]['remarks'], 0, 100);
                        
                        $deDupeReference = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeReference);
                        $deDupeStatus = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeStatus);
                        
                        $updateDeDupeStatus = DB::table("CUSTOMER_OVD_DETAILS")->where('FORM_ID',$requestData['formId'])
                                                                               ->where('QUERY_ID',$customerOvdDetails[$rec]->query_id)
                                                                              ->update(['DEDUPE_STATUS'=>$deDupeStatus,'DEDUPE_REFERENCE'=>$deDupeReference]);
                        DB::commit();
                    }
                }

                if(!$checkFail)
                {
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Updated dedupe response.','data'=>[]]);
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



      public function alreadyreview(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch data from request
                //echo "<pre>";print_r($requestData);exit;
                $roleId = Session::get('role');
                // echo "<pre>";print_r($roleId);
                switch($roleId){
                        case 3:     // L1
                            $fieldName = 'L1_REVIEW';
                            break;
                        case 4:     // L2
                            $fieldName = 'L2_REVIEW';
                            break;
                        case 5:     // QC
                            $fieldName = 'QC_REVIEW';
                            break;
                        case 6:     // AU
                            $fieldName = 'AUDIT_REVIEW';
                            break;      
                         default: 
                            $fieldName = 'AUDIT_REVIEW';
                            break;   
                    }

                $reviewstatus = DB::table('ACCOUNT_DETAILS')->whereId($requestData['form_id'])
                                                            //->where($fieldName, '1')
                                                            ->get()->toArray();
                if (count($reviewstatus) > 0) {
					$reviewstatus = (array) current($reviewstatus);					
					
					// If NPC user clicks form (in parallel or for first time) while it was already processed
					if($roleId!=$reviewstatus['next_role']){
						return json_encode(['status'=>'fail','msg'=>'Record already processed','data'=>[]]);
					}
					
					//echo '<pre>'; print_r($reviewstatus); exit;
					$fieldLower = strtolower($fieldName);
					
					$timeDiffInMin = 0;
					if ($reviewstatus[$fieldLower] == '1') {
						
						$reviewTime = $reviewstatus['npc_review_time'];
						$reviewBy=$reviewstatus['npc_review_by'];

						$okToProceed = false;


						$timeCheck = 'OK';
						
						if($reviewTime == '' || $reviewTime == NULL ){
							$timeCheck = 'OK';
							$okToProceed = true;
						}else{
							$timeDiffInMin = Carbon::now()->diffInMinutes($reviewTime);
							if($timeDiffInMin >= 15){
								$timeCheck = 'OK';
								$okToProceed = true;
							}else{
								$timeCheck = 'NOTOK';
								$okToProceed = false;
							}
						}
					}else{
						$okToProceed = true;
					}	
				}else{
					$okToProceed = true;
                    $timeDiffInMin = 0;
				}	
					

																
                // echo "<pre>";print_r($reviewstatus);exit;
                

				$currentTime = Carbon::now()->format('Y-m-d H:i:s');
				$currentUser = Session::get('userId');

                // echo "<pre>";print_r($okToProceed);exit;
                if($okToProceed){

                    switch(Session::get('role')){
                        case 3:     // L1
                            $fieldName = 'L1_REVIEW';
                            break;
                        case 4:     // L2
                            $fieldName = 'L2_REVIEW';
                            break;
                        case 5:     // QC
                            $fieldName = 'QC_REVIEW';
                            break;
                        case 6:     // AU
                            $fieldName = 'AUDIT_REVIEW';
                            break;      
                         default: 
                            $fieldName = 'AUDIT_REVIEW';
                            break;   
                    }

                   $reviewstatusupdate = DB::table("ACCOUNT_DETAILS")->whereId($requestData['form_id'])
                                             ->update([$fieldName=>1, 'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
                        DB::commit();
                return json_encode(['status'=>'success','msg'=>'Ok to Review','data'=>[$timeDiffInMin, $currentUser]]);
                }else{
                    $userName = DB::table("USERS")->where("ID",$reviewBy)->pluck('emp_first_name')->toArray();
                    return json_encode(['status'=>'fail','msg'=>'Record Already in Review','data'=>[$timeDiffInMin, $userName]]);

                }

				/*if($currentUser!=''){
					$userName = DB::table("USERS")->where("ID",$currentUser)->pluck('emp_first_name')->toArray();
					if(count($userName) > 0){
						$userName = $userName[0];
					}else{
						$userName = '';
					}
				}else{
					$userName = '';
				}


                if(count($reviewstatus) > 0)
                {
                  if((Session::get('role') == 3) && (($reviewstatus['l1_review'] != 1) || ($timeCheck == 'OK'))){

                        $reviewstatusupdate = DB::table("ACCOUNT_DETAILS")->whereId($requestData['form_id'])
														->update(['L1_REVIEW'=>1, 'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
                        DB::commit();
                        return json_encode(['status'=>'success','msg'=>'Ok to Review','data'=>[$timeDiffInMin, $userName]]);
                    }
                    elseif((Session::get('role') == 4) && (($reviewstatus['l2_review'] != 1) || ($timeCheck == 'OK'))){

                        $reviewstatusupdate = DB::table("ACCOUNT_DETAILS")->whereId($requestData['form_id'])
                                                  ->update(['L2_REVIEW'=>1, 'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
                        DB::commit();
                        return json_encode(['status'=>'success','msg'=>'Ok to Review','data'=>[$timeDiffInMin, $userName]]);
                    }
                    elseif((Session::get('role') == 5) && (($reviewstatus['qc_review'] != 1) || ($timeCheck == 'OK'))){

                        $reviewstatusupdate = DB::table("ACCOUNT_DETAILS")->whereId($requestData['form_id'])
                                                     ->update(['QC_REVIEW'=>1, 'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
                        DB::commit();
                        return json_encode(['status'=>'success','msg'=>'Ok to Review','data'=>[$timeDiffInMin, $userName]]);
                    }
                    elseif((Session::get('role') == 6) && (($reviewstatus['audit_review'] != 1) || ($timeCheck == 'OK'))){

                        $reviewstatusupdate = DB::table("ACCOUNT_DETAILS")->whereId($requestData['form_id'])
                                                     ->update(['AUDIT_REVIEW'=>1, 'NPC_REVIEW_TIME'=>$currentTime, 'NPC_REVIEW_BY'=>$currentUser]);
                        DB::commit();
                        return json_encode(['status'=>'success','msg'=>'Ok to Review','data'=>[$timeDiffInMin, $userName]]);
                    }
                    else{

                       return json_encode(['status'=>'fail','msg'=>'Record Already in Review','data'=>[$timeDiffInMin, $userName]]);
                    }

                }else{

                       return json_encode(['status'=>'fail','msg'=>'Record Not Found','data'=>[]]);
                }
                */

               //echo "<pre>";print_r($saveCommentss);exit;

                // if($reviewstatusupdate){
                //     //commit database if response is true
                //     DB::commit();
                //     return json_encode(['status'=>'success','msg'=>'Comments saved successfully','data'=>[]]);
                // }else{
                //     //rollback db transactions if any error occurs in query
                //     DB::rollback();
                //     return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                // }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function markFormForQC(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $markFormForQCInternal = Self::markFormForQCInternal($requestData['formId']);
                return json_encode(['status'=>$markFormForQCInternal['status'],'msg'=>$markFormForQCInternal['msg'],'data'=>$markFormForQCInternal['data']]);
                
            }else{
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function markFormForQCInternal($formId)
    {
        try {
            if(!CommonFunctions::isFTRDone($formId)) {
                return (['status'=>'fail','msg'=>'FTR not yet done','data'=>[]]);
            }

            if(!CommonFunctions::isAccountCreated($formId)) {
                return (['status'=>'fail','msg'=>'Account ID not yet generated','data'=>[]]);
            }
            DB::beginTransaction();
            $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->get()->toArray();

            $accountDetails = (array) current($accountDetails);
            if(count($accountDetails) > 0 ) {
                $nextRole = $accountDetails['next_role'];
                if ($nextRole == 1) {
                    $updateNextRole = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                    ->update(['NEXT_ROLE'=>5]);
                }else{
                    $currRole = DB::table('USER_ROLES')->whereId($nextRole)
                                                        ->pluck('role')->toArray();

                    $message = 'Form currently with '.$currRole[0].' role.';
                    return (['status'=>'fail','msg'=>$message,'data'=>[]]);
                }
                
            }else{
                return (['status'=>'fail','msg'=>'Data Error! Please try again','data'=>[]]);
            }

            if($updateNextRole){
                DB::commit();
                return (['status'=>'success','msg'=>'Form marked for QC','data'=>[]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return (['status'=>'fail','msg'=>'From marked for QC failed','data'=>[]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('NPC/ReviewController','markFormForQCInternal',$eMessage,'',$formId);
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function oaoReview(Request $request)
    {
        if(!empty($request->all())){
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $params = base64_decode($decodedString);
            // echo "<pre>";print_r($params);exit;
            $oao_id = explode('_',$params)[0];
            // echo "<pre>";print_r($params);exit;
        }else{
            return (['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

        $oaoData = DB::table('OAO_STATUS')->where('OAO_ID', $oao_id)->get()->toArray();
        $oaoData = current($oaoData);
        // echo "<pre>";print_r($oaoData);exit;
        // echo "<pre>";print_r($oaoData);exit;
        $accountDetails = DB::table('CUSTOMER_OVD_DETAILS')->select('first_name','middle_name','last_name','short_name','per_address_line1','per_address_line2','per_state','per_city','per_pincode','per_landmark', 'COUNTRIES.NAME as PER_COUNTRY','current_state','current_city','current_pincode','CC.NAME as current_country')
        ->where('FORM_ID', $oaoData->form_id)
        ->leftjoin('COUNTRIES','CUSTOMER_OVD_DETAILS.PER_COUNTRY','COUNTRIES.ID')
        ->leftjoin('COUNTRIES as CC','CUSTOMER_OVD_DETAILS.CURRENT_COUNTRY','CC.ID')
        ->get()->toArray();

        $accountDetails = current($accountDetails);

        $validations = [
            'first_name'=>['MAX'=> 50],
            'middle_name'=>['MAX'=> 50],
            'last_name'=>['MAX'=> 50],
            'short_name'=>['MAX'=> 25],
            'per_address_line1'=>['MAX'=> 50],
            'per_address_line2'=>['MAX'=> 50],
            'per_state'=>['MAX'=> 50],
            'per_city'=>['MAX'=> 50],
            'per_pincode'=>['MAX'=> 6],
            'per_landmark'=>['MAX'=> 50],
            'per_country'=>['MAX'=> 50],
            'current_city'=>['MAX'=> 50],
            'current_state'=>['MAX'=> 50],
            'current_pincode'=>['MAX'=> 6],
            'current_country'=>['MAX'=> 50]
        ];

        // echo "<pre>";print_r($accountDetails);exit;
        foreach ($accountDetails as $key => $value) {
            if (isset($validations[$key])) {
                $validations[$key]['VALUE'] = $value;
            }
        }

        // echo "<pre>";print_r($validations);exit;
        // $ovdDetails = DB::table('ACCOUNT_DETAILS')->where('FORM_ID', $oaoData->form_id)->get()->toArray();
        return view('npc.oaodetails')->with('accountDetails', $validations)
                                    ->with('form_id', $oaoData->form_id)
                                    ->with('oao_id', $oao_id);
    }

    public function updateOaoDetails(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');

                $updateField = DB::table('CUSTOMER_OVD_DETAILS')->where('form_id', $requestData['form_id'])->update([$requestData['field']=> $requestData['value']]);


                // echo "<pre>";print_r($requestData);exit;
                if ($updateField) {
                    return json_encode(['status'=>'success','msg'=>'field is updated successfully','data'=>[]]);
                }else{
                    return json_encode(['status'=>'error','msg'=>'Error! field is not updated','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function oaodashboard(Request $request)
    {
        //Array to hold accounts count
        $accountsCount = array();
            //fetch savings and td accounts count
        $savingsTDaccountsCount = CommonFunctions::getAccountsCountByTypeAndStatus('savingsTD');
        //fetch savings accounts count
        $accountsCount['savings'] = CommonFunctions::getAccountsCountByTypeAndStatus('savingsAccount')
                                                                     + $savingsTDaccountsCount;
        //fetch current accounts count
        $accountsCount['current'] = CommonFunctions::getAccountsCountByTypeAndStatus('currentAccount');
        //fetch fixed deposite accounts count
        $accountsCount['termDeposit'] = CommonFunctions::getAccountsCountByTypeAndStatus('termDeposit')
                                                                         + $savingsTDaccountsCount;
        //fetch applciation status
        $applicationStatus = config('constants.APPLICATION_STATUS');
        $applicationStatus = Arr::except($applicationStatus,[15,16,17,18,19,20,21,22,23]);
        //echo "<pre>";print_r($applicationSta);exit;

        //fetch user names from applcations
        $customerNames = DB::table('CUSTOMER_OVD_DETAILS')
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->select('CUSTOMER_OVD_DETAILS.ID AS id',DB::raw("CUSTOMER_OVD_DETAILS.FIRST_NAME || ' ' || CUSTOMER_OVD_DETAILS.MIDDLE_NAME || ' ' || CUSTOMER_OVD_DETAILS.LAST_NAME AS user_name"))
                            ->where('ACCOUNT_DETAILS.SOURCE','DSA')
                            ->whereNotNull('CUSTOMER_OVD_DETAILS.LAST_NAME')
                            ->pluck('user_name','id')->toArray();
        //returns tempalte
        return view('npc.oaodashboard')->with('accountsCount',$accountsCount)->with('customerNames',$customerNames)
                                    ->with('applicationStatus',$applicationStatus);
    }


    public function oaoapplications(Request $request)
    {
        try {
            //fetch data from request

            // echo "<pre>";print_r($userDetails);exit;
            $requestData = $request->get('data');
            $select_arr=[];
            $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
            //build columns array
            // $filteredColumns = ['OAO_ID','USER_NAME','AOF_NUMBER','MOBILE_NUMBER','QUERY_ID','DEDUPE_STATUS','PAYMENT','FUND_RECEIVED','CUSTOMER_ID','ACCOUNT_ID','FREEZE_1','FREEZE_2','FREEZE_3','FTR','VKYC_STATUS','Update','ACTION', 'FORM_ID'];

             $filteredColumns = ['OAO_ID','USER_NAME','PA_MISMATCH','AOF_NUMBER','MOBILE_NUMBER','QUERY_ID','DEDUPE_STATUS','PAYMENT','FUND_RECEIVED','CUSTOMER_ID','ACCOUNT_ID','FTR','VKYC_LINK','VKYC_STATUS', 'ACTION', 'FORM_ID','PAN_NAME','UPI_NAME','UA_MISMATCH'];

            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "USER_NAME")
                {
                     array_push($select_arr,'CUSTOMER_ID');
                     $dt[$i] = array( 'db' => 'CUSTOMER_ID','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            $custAccnt = DB::table('CUSTOMER_OVD_DETAILS')->select(DB::raw("CUSTOMER_OVD_DETAILS.FIRST_NAME || ' ' || CUSTOMER_OVD_DETAILS.LAST_NAME AS user_name"),DB::raw("CUSTOMER_OVD_DETAILS.FIRST_NAME || ' ' || CUSTOMER_OVD_DETAILS.MIDDLE_NAME || ' ' || CUSTOMER_OVD_DETAILS.LAST_NAME AS full_name"))
                                    ->where('APPLICANT_SEQUENCE', 1)
                                    ->where('FORM_ID', $row->form_id)
                                    ->get()->toArray();
                            $custAccnt = (array) current($custAccnt);
                            if (isset($custAccnt['user_name'])) {
                                $html = '<p style="font-size:12px; text-align:left; padding-left:49px;">A: '.$custAccnt['full_name'].
                                		'</br> U: '.$row->upi_name.
                                		'</br> P: '.$row->pan_name.
                                		'</p>';
                               
                            }
                            return $html;
                        }
                    );
                }else if($column == 'PA_MISMATCH'){
                    array_push($select_arr,"OAO_STATUS.PA_MISMATCH");
                    $dt[$i] = array( 'db' => 'OAO_STATUS.PA_MISMATCH','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->ua_mismatch.'  |  '.$row->ua_mismatch;
                            return $html;
                        }
                    );
                }else if($column == 'AOF_NUMBER'){
                      array_push($select_arr,"OAO_STATUS.AOF_NUMBER");
                    $dt[$i] = array( 'db' => 'OAO_STATUS.AOF_NUMBER','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if($row->aof_number != ''){
                                $html = '<a href="javascript:showForm('.$row->aof_number.')" style="font-size:15px;" >'.$row->aof_number.'</a>';   
                            }
                            return $html;
                        }
                    );
                }else if($column == 'MOBILE_NUMBER'){
                      array_push($select_arr,"OAO_STATUS.ID");
                    $dt[$i] = array( 'db' => 'OAO_STATUS.ID','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                        $mobileNumber = DB::table('CUSTOMER_OVD_DETAILS')->select('MOBILE_NUMBER')->where('FORM_ID', $row->form_id)->get()->toArray();
                            $mobileNumber = (array) current($mobileNumber);
                        if(isset($mobileNumber['mobile_number'])){
                            $html = $mobileNumber['mobile_number'];
                        }
                        return $html;
                    }
                        );
                }else if($column == "VKYC_LINK"){
                    
                      array_push($select_arr,"OAO_STATUS.VKYC_LINK");
                    $dt[$i] = array( 'db' => 'OAO_STATUS.VKYC_LINK','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if($row->vkyc_link != ''){
                                $html = '<a href="javascript:void(0)" class="linktext badge badge-secondary" title="'.$row->vkyc_link.'">Y</a>';   
                            }
                            return $html;
                        }
                    );
                }else if($column == "VKYC_STATUS"){
                    
                      array_push($select_arr,"OAO_STATUS.VKYC_STATUS");
                    $dt[$i] = array( 'db' => 'OAO_STATUS.VKYC_STATUS','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                             if($row->vkyc_link != ''){
                                $html = '<a href="javascript:void(0)" class="badge badge-secondary">Mark Done</a>';   
                            }
                            return $html;
                        }
                    );
                // }else if($column == "Update"){
                //     $dt[$i] = array( 'db' => 'OAO_STATUS.ID','dt' => $i,
                //         'formatter' => function( $d, $row ) {
                //             $html = '';
                //             if ($row->customer_id != 'Y' || $row->account_id != 'Y') {
                //                 $html = '<a href="javascript:void(0)" id="'.$row->oao_id.'" class="oaoReview">Update</a>';
                //             }
                //             return $html;
                //         }
                //     );
                }else if($column == "ACTION"){
                      array_push($select_arr,"OAO_STATUS.ID");
                    $dt[$i] = array( 'db' => 'OAO_STATUS.ID','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';

                            if ($row->account_id == 'Y' && $row->payment == 'Y' && $row->fund_received != 'Y'){
                                $html = '<a href="javascript:void(0)" id="'.$row->oao_id.'" class="fundReceived">Fund Received</a>';
                            }

                            if ($row->account_id == 'Y' && $row->payment == 'Y' && $row->ftr == 'Y' && $row->fund_received == 'Y' && $row->vkyc_status == 'link generated') {
                                $html = '<a href="javascript:void(0)" id="'.$row->oao_id.'" class="vkycDone">Vkyc Done</a>';
                            }

                            return $html;
                        }
                    );
                }else{
                    array_push($select_arr,"OAO_STATUS.".$column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = "oao_status.".strtolower($column);
                    $dt[$i]['dt'] = $i;
                }                
                $i++;              
            }

            // $dt_obj = new SSP('OAO_STATUS', $dt);
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            $dt_obj = DB::table('OAO_STATUS')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','OAO_STATUS.FORM_ID'); //only payment done accounts
            $dt_obj = $dt_obj->where('OAO_STATUS.PAYMENT', 'Y'); //only payment done accounts

            if(isset($requestData['MOBILE_NUMBER']) && $requestData['MOBILE_NUMBER'] != '')
            {
                $dt_obj = $dt_obj->where('CUSTOMER_OVD_DETAILS.MOBILE_NUMBER', 'like', '%'.$requestData['MOBILE_NUMBER'].'%');
            }

            if(isset($requestData['AOF_NUMBER']) && $requestData['AOF_NUMBER'] != '')
            {
                $dt_obj = $dt_obj->where('OAO_STATUS.AOF_NUMBER', 'like', '%'.$requestData['AOF_NUMBER'].'%');
            }

            if(isset($requestData['customer']) && $requestData['customer'] != '')
            {
                $dt_obj = $dt_obj->where('CUSTOMER_OVD_DETAILS.ID',$requestData['customer']);
            }


            // $dt_obj = $dt_obj->where(function ($query) {
            //    $query->where('OAO_STATUS.PAYMENT', 'Y')
            // });
           
            // $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->order(0, 'desc');
            //echo "<pre>";print_r($dt_obj->getDtArr());exit;
            // return response()->json($dt_obj->getDtArr());
            $dt_ssp_obj->setQuery($dt_obj->orderBy("OAO_STATUS.UPDATED_AT","ASC"));
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);        }
    }

    public function fundReceived(Request $request)
    {
        if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');
                // echo "<pre>";print_r($requestData);exit;    
                if (isset($requestData['oao_id'])) {
                    $updateFundTransfer = DB::table('OAO_STATUS')->where('oao_id', $requestData['oao_id'])->update(['fund_received'=> 'Y']);
                    return json_encode(['status'=>'success','msg'=>'Fund recived saved successfully','data'=>[]]);        
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);    
                }
        }
    }

    public function vkycDone(Request $request)
    {
        if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');
                if (isset($requestData['oao_id'])) {

                    $updateFundTransfer = DB::table('OAO_STATUS')->where('oao_id', $requestData['oao_id'])->update(['vkyc_status'=> 'completed']);

                    $unfreezeAccount = OaoCommonFunctions::freezeUnfreeze($requestData['oao_id'], 'Unfreeze', 'T');
                    
                    if($unfreezeAccount){
                        $updateOaoStatus = DB::table('OAO_STATUS')->where('OAO_ID', $requestData['oao_id'])->update(['FREEZE_3'=> 'Y']); 
                    }else{
                        $updateOaoStatus = DB::table('OAO_STATUS')->where('OAO_ID', $requestData['oao_id'])->update(['FREEZE_3'=> 'N']); 
                    }
                    
                    return json_encode(['status'=>'success','msg'=>'Vkyc completed successfully','data'=>[]]);        
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);    
                }
        }
    }

    public function executePostSA($formId)
    {
        Self::submitCardDetails($formId);
        Self::ekycStatus($formId);
    }

    public function submitCardDetails($formId)
    {
        $accountDetails = DB::table('ACCOUNT_DETAILS')->where('NEXT_ROLE', 1)
                                                ->where('ID',$formId)
                                                ->whereIn('ACCOUNT_TYPE',  ['1','4','5'])
                                                ->where(function ($query) {
                                                $query->where('CARD_FLAG', '!=', 'Y')
                                                      ->orWhereNull('CARD_FLAG');
                                                })
                                                ->get()->toArray();


        if (count($accountDetails) > 0) {
            foreach ($accountDetails as $key => $value) {
                $formId = $value->id;
                $apiResponse = Api::submitCardDetails($formId);

                if ($apiResponse != 'Success') {
                    $updateAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['CARD_FLAG' => 'N']);
                }else{
                    $updateAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['CARD_FLAG' => 'Y']);
                }
                
            }
        }

    }

    public function ekycStatus($formId)
    {
        $checkData = DB::table('CUSTOMER_OVD_DETAILS')->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                                                    ->select('CUSTOMER_OVD_DETAILS.FORM_ID', 'CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
                                                    ->whereNull('CUSTOMER_OVD_DETAILS.KYC_UPDATE')
                                                    ->whereNotNull('ACCOUNT_DETAILS.ACCOUNT_NO')
                                                    ->get()->toArray();

        foreach ($checkData as $key => $value){
            $customerId = $value->customer_id;
            $apiResponse = Api::ekycFinancleScript($formId,$customerId);

            if($apiResponse == 'Success') {
                $updateAccountDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->update(['KYC_UPDATE' => 'Y']);
            }else{
                $updateAccountDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->update(['KYC_UPDATE' => NULL]);
            }

        }
    }

    public function checkimagemasking(Request $request){
        try{
            $requestData = $request->get('data');
            $formId =  $requestData['form_id'];
           
            $updateMaskFlag =  DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['ADHR_MASK'=>'N']);
            if($updateMaskFlag){
                return json_encode(['status'=>'fail','msg'=>'From marked for manual masking.','data'=>[]]);
            }
            
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);        
        }
    
    }


    public function decrypt(Request $request){

        $encryptedString=$request['encryptedString'];
        $key=$request['key'];
        $decryptedData=EncryptDecrypt::AES256Decryption($encryptedString,$key);
        return json_encode(['status'=>'success','data'=>['decryptedData'=>$decryptedData]]);

    }

    public function updatehufDiscrepentFileds($requestData,$appplicantId,$column)
    {   
        $columns_ = config('constants.HUF_DISCREPENT_COLUMNS');
        $data1=$requestData['column_name'];
        $col_exsits = false;
        foreach ($columns_['HUF_ALL'] as $key => $value) {
            if(str_contains($data1 , $value)){
                $col_exsits = true;
                $data = str_replace($value,"",$data1);
                break;
            }
        }
        if(!$col_exsits){
            return null;
        }

        if(!isset($data)){
            return null;
        }

        // $data=substr($data1,-3,1);

        foreach ($columns_['HUF_ALL'] as $value) {
            $fields=$value.$data;
            $requestData['comments'] = "Automarked as part of"; 
            $requestData['column_name'] = $fields;
            $sql_array=['FORM_ID'=>$requestData['form_id'],'COLUMN_NAME'=>$fields,'ROLE_ID'=>$requestData['role_id'], 'STATUS'=>'0'];
            $checkComments = DB::table('REVIEW_TABLE')->where($sql_array)->get()->toArray();
			if(count($checkComments)>0){
                if (substr($checkComments[0]->comments, 0, 10) == "")
                {
				    $updateColumns = DB::table('REVIEW_TABLE')->where($sql_array)->update($requestData);
                }else{
                    $updateColumns = '';
                }
			}else{
				$updateColumns = DB::table('REVIEW_TABLE')->insert($requestData);               
			} 
        }  
        return $updateColumns;
    }
}
?>
