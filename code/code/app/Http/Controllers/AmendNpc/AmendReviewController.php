<?php

namespace App\Http\Controllers\AmendNpc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Helpers\CommonFunctions;
use App\Helpers\AmendCommonFunctions;
use Carbon\Carbon;
use App\Helpers\AmendApi;
use App\Helpers\AmendRules;
use App\Helpers\Api;
use App\Helpers\AmendApiQueueCommonFunctions;
use Session;
use DB;
use Cookie;
Use Crypt;
use File;
use URL;

class AmendReviewController extends Controller
{
    public function amendreview(Request $request){
        try{
        $tokenParams = explode('.', Cookie::get('token'));
        $decodestring = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
        $crfNumber = base64_decode($decodestring);

        $getCrfDetails = DB::table('AMEND_QUEUE')->where('CRF',$crfNumber)
                                                ->where('SOFT_DEL','N')
                                                ->get()->toArray();

        $getProofDocument = DB::table('AMEND_PROOF_DOCUMENT')->leftjoin('AMEND_EVIDENCE','AMEND_EVIDENCE.ID','AMEND_PROOF_DOCUMENT.EVIDENCE_ID')
                                                   ->whereNotIn('EVIDENCE_ID',[31,32,33,34,35])
                                                            ->where('AMEND_PROOF_DOCUMENT.CRF_NUMBER',$crfNumber)
                                                            ->orderBy('EVIDENCE_ID','ASC')
                                                             ->get()->toArray();

        $amendL1Rules = config('amend_l1_rules');
        $amendValidation  = config('amendvalidation');
        $role = Session::get('role');

        $reviewDetails = DB::table('AMEND_REVIEW_TABLE')
                            ->where(['ROLE_ID'=>$role,'STATUS'=>'1', 'CRF_NUMBER' => $crfNumber])
                            ->pluck('comments','column_name')->toArray();

        $hold_comment = DB::table('AMEND_STATUS_LOG')->where('CRF_NUMBER',$crfNumber)->where('ROLE',$role)
            ->whereIn('STATUS',[35,45,85,38,48])->orderBy('ID','DESC')->get()->toArray();

        // echo "<pre>";print_r($crfNumber);exit;

        $masterDetails = DB::table('AMEND_MASTER')->select('REFERENCE_NO','CUSTOMER_ID','ADDITIONAL_DATA','SOL_ID','CRF_STATUS','UPLOAD_CRF_FLAG')
                            ->where('CRF_NUMBER',$crfNumber)
                            ->get()->toArray();
        $masterDetails = (array) current($masterDetails);
        Session::put('branchId',$masterDetails['sol_id']);
        $additionalData = json_decode($masterDetails['additional_data'],true);
        // echo "<pre>";print_r($additionalData);exit;

        if(isset($additionalData['comuproofAddData']['addproof_id']) && $additionalData['comuproofAddData']['addproof_id'] != ''){
            $ovdSection = AmendCommonFunctions::getIdDescription($additionalData['comuproofAddData']['addproof_id']);
            $additionalData['comuproofAddData']['addproof_id'] = $ovdSection['ovd'];
                foreach ($additionalData['comuproofAddData'] as $key =>  $value) {
                    $ovdData = array();
                    switch ($key) {
                        case 'addproof_id':
                            $ovdData['id'] = strtolower(str_replace(' ', '_', $ovdSection['ovd']));
                            $ovdData['field_name'] = strtolower(str_replace(' ', '_', $ovdSection['ovd']));
                            $ovdData['display_name'] = 'Proof of Address';
                            $ovdData['value'] = $ovdSection['ovd'];
                            $ovdData['section'] = 'ovd';
                            break;

                        case 'addproof_no':
                            $ovdData['id'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_number';
                            $ovdData['field_name'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_number';
                            $ovdData['display_name'] = $ovdSection['ovd'].' NUMBER ';
                            $ovdData['section'] = 'ovd';
                            $ovdData['value'] = $value;
                            break;
                        break;
                        default:
                        break;
            }
                    if(count($ovdData) > 0){
                        $ovdData = (object) $ovdData;
                        array_push($getCrfDetails, $ovdData);
                    }
                }
        }       
        // echo "<pre>";print_r($getCrfDetails);exit;

        if(isset($additionalData['proofIdData']['proof_id']) && $additionalData['proofIdData']['proof_id'] != ''){
           

            if($additionalData['proofIdData']['proof_id'] == 1){
                $additionalData['proofIdData']['id_code'] = 'XXXX-XXXX-'.substr($additionalData['proofIdData']['id_code'],10);
            }

            $ovdSection = AmendCommonFunctions::getIdDescription($additionalData['proofIdData']['proof_id']);
            $additionalData['proofIdData']['proof_id'] = $ovdSection['ovd'];
            // echo "<pre>";print_r($additionalData);exit;

            foreach ($additionalData['proofIdData'] as $key =>  $value) {
                $ovdData = array();
                switch ($key) {
                    case 'proof_id':
                        $ovdData['id'] = strtolower(str_replace(' ', '_', $ovdSection['ovd']));
                        $ovdData['field_name'] = strtolower(str_replace(' ', '_', $ovdSection['ovd']));
                        $ovdData['display_name'] = 'Proof of Identity';
                        $ovdData['value'] = $ovdSection['ovd'];
                        $ovdData['section'] = 'ovd';
                        break;

                    case 'id_code':
                        $ovdData['id'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_number';
                        $ovdData['field_name'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_number';
                        $ovdData['display_name'] = $ovdSection['ovd'].' NUMBER ';
                        $ovdData['section'] = 'ovd';
                        $ovdData['value'] = $value;
                        break;

                     case 'id_date':
                        if($additionalData['proofIdData'][$key] != ''){
                            $ovdData['id'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_expiry_date';
                            $ovdData['field_name'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_expiry_date';
                            $ovdData['display_name'] = $ovdSection['ovd'].' EXPIRY DATE';
                            $ovdData['section'] = 'ovd';
                            $ovdData['value'] = $value;
                        }
                    break;

                    case 'issues_id_date':
                        if($additionalData['proofIdData'][$key] != ''){
                            $ovdData['id'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_issues_date';
                            $ovdData['field_name'] = strtolower(str_replace(' ', '_', $ovdSection['ovd'])).'_issues_date';
                            $ovdData['display_name'] = $ovdSection['ovd'].' ISSUES DATE';
                            $ovdData['section'] = 'ovd';
                            $ovdData['value'] = $value;
                        }
                    break;
                    default:
                        break;
                }

                if(count($ovdData) > 0){
                    $ovdData = (object) $ovdData;
                    array_push($getCrfDetails, $ovdData);
                }
            }
        }

        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        $currentUser = Session::get('userId');


        switch ($role) {
            case '19':
                DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['AMEND_L1_REVIEW'=>1,'AMEND_NPC_REVIEW_TIME'=>$currentTime, 'AMEND_NPC_REVIEW_BY'=>$currentUser]);
            break;
            
            case '20':
                DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['AMEND_L2_REVIEW'=>1,'AMEND_NPC_REVIEW_TIME'=>$currentTime, 'AMEND_NPC_REVIEW_BY'=>$currentUser]);               
            break;

            case '21':
                DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['AMEND_QC_REVIEW'=>1,'AMEND_NPC_REVIEW_TIME'=>$currentTime, 'AMEND_NPC_REVIEW_BY'=>$currentUser]);               
            break;

            case '23':
                DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['AMEND_AUDIT_REVIEW'=>1,'AMEND_NPC_REVIEW_TIME'=>$currentTime, 'AMEND_NPC_REVIEW_BY'=>$currentUser]);               
            break;

            default:
                DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['AMEND_AUDIT_REVIEW'=>1,'AMEND_NPC_REVIEW_TIME'=>$currentTime, 'AMEND_NPC_REVIEW_BY'=>$currentUser]);    
            break;

        }



       

        $lastSender = DB::table('AMEND_REVIEW_TABLE')->where('CRF_NUMBER',$crfNumber)->orderBy('ID','DESC')->first();   
        if($lastSender != ''){   
            $role_id = $lastSender->role_id;
                if($role == 23 && $lastSender->role_id != $role){
                    $role_id = $role;
                }
            $qcReviewDetails = DB::table('AMEND_REVIEW_TABLE')->where('CRF_NUMBER',$crfNumber)
                                            ->where('ROLE_ID',$role_id)                                         
                                            ->where('ITERATION',$lastSender->iteration)
                                            ->pluck('comments','column_name')->toArray();  
            $formReviewDetails = DB::table('AMEND_REVIEW_TABLE')->where('ROLE_ID', $lastSender->role_id)
                                                            ->where('CRF_NUMBER',$crfNumber)
                                                            ->orderBy('ID', 'DESC')
                                                            ->get()->toArray();
            if (count($formReviewDetails) > 0 ) {
                $formReviewDetails = current($formReviewDetails);
                $reviewIteration = $formReviewDetails->iteration;
            }else{
                $reviewIteration = 0;
            }
            $reviewIteration++; 
        }else{
            $qcReviewDetails = [];
            $reviewIteration = '';
        }



        $amendL3Images = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crfNumber)->whereIn('EVIDENCE_ID',[31,32,33,34,35])->orderBy('ID','ASC')->get()->toArray();

         $lastSavedComments = DB::table('AMEND_REVIEW_TABLE')->where('CRF_NUMBER', $crfNumber)
                                            ->where('STATUS','1')
                                            ->where('ROLE_ID',$role)
                                            ->get()->toArray();           


        return view('amendnpc.amendreview')->with('getCrfDetails',$getCrfDetails)
                                           ->with('getProofDocument',$getProofDocument)
                                           ->with('amendValidation',$amendValidation)
                                           ->with('reviewIteration',$reviewIteration)
                                           ->with('reviewDetails',$reviewDetails)
                                           ->with('role',$role)
                                           ->with('crfNumber',$crfNumber)
                                           ->with('qcReviewDetails',$qcReviewDetails)
                                           ->with('lastSavedComments',$lastSavedComments)
                                           ->with('amendL3Images',$amendL3Images)
                                           ->with('amendL1Rules',$amendL1Rules)
                                           ->with('hold_comment',$hold_comment)
                                           ->with('masterDetails', $masterDetails);

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
        

    }

    public function updateAmendFieldValue(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $getConfigRules = config('amend_l1_rules.'.$requestData['config_id']);
                $updateDBvalue = DB::table($getConfigRules['table'])->whereId($requestData['table_id']);
                $tableArray = $updateDBvalue->get()->toArray();
                $tableArray = (array) current($tableArray);
                switch ($getConfigRules['validation_type']){
                    case 'pincode':
                        $addressData = CommonFunctions::getAddressDataByPincode($requestData['new_value']);
                        $addressData = (array) current($addressData);
                            $updateDBvalue->update(['new_value' => $requestData['new_value']]);
                            $stateupdate = DB::table($getConfigRules['table'])->whereId($requestData['table_id'])->update(['NEW_VALUE' => $addressData['statedesc']]);
                            $cityUpdate = DB::table($getConfigRules['table'])->whereId($requestData['table_id'])->update(['NEW_VALUE' => $addressData['citydesc']]);
                    break;
                    default:

                    if($getConfigRules['validation_type'] == 'datefield'){
                        $requestData['new_value'] = date_format(date_create($requestData['new_value']), "d-m-Y");
                    }
                    $updateDBvalue->update(['new_value' => $requestData['new_value']]);
                    break;
                }
                if($updateDBvalue){
                    $tableArray = $updateDBvalue->get()->toArray();
                    $tableArray = (array) current($tableArray);
                    $insertData = ['AMEND_QUEUE_ID' => $tableArray['id'],
                                   'CRF_NUMBER' => $tableArray['crf'],
                                   'OLD_VALUE' => $requestData['old_value'],
                                   'NEW_VALUE' => $requestData['new_value_text'],
                                   'CREATED_BY' => Session::get('userId'),
                                   'FIELD_NAME' => $tableArray['field_name']];
                    $insertL1Reviewlog = DB::table('AMEND_L1_EDIT_LOG')->insert($insertData);
                    if($insertL1Reviewlog){
                        return (['status'=>'success','msg'=>'Field Updated Successfully','data'=>[]]);
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

    public function getamendaddressdatabypincode(Request $request){
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

    // public function saveamendcomment(Request $request){
    //     try{
    //         if($request->ajax()){
    //             $requestData = $request->get('data');
    //             if(isset($requestData['amend_queue_id']) &&  $requestData['amend_queue_id'] != ''){
    //                 $amendQueueData = DB::table('AMEND_QUEUE')->whereId($requestData['amend_queue_id'])->get()->toArray();
    //                 $amendQueueData = (array) current($amendQueueData);
    //                 $insertComment = ['AMEND_QUEUE_ID' => $requestData['amend_queue_id'],
    //                                   'CRF_NUMBER' => $amendQueueData['crf'],
    //                                    'COLUMN_NAME' => $requestData['reviewId'],
    //                                    'CRF_NUMBER' => $amendQueueData['crf'],
    //                                    'COMMENTS' => $requestData['comments'],
    //                                    'STATUS' => 1,
    //                                     'SECTION' => $requestData['section'],
    //                                     'ROLE_ID' => Session::get('role'),
    //                                     'BRANCH_ID' => Session::get('branchId')];
    //                 $checkRecordExist = DB::table('AMEND_REVIEW_TABLE')->where('AMEND_QUEUE_ID',$requestData['amend_queue_id']);
    //                 if($checkRecordExist->count() >= 1){
    //                     $insertComment['UPDATED_BY'] = Session::get('userId');
    //                     $insertComment['UPDATED_AT'] = Carbon::now()->format('Y-m-d H:i:s');
    //                     $updateAmendData = $checkRecordExist->update($insertComment);
    //                 }else{
    //                     $insertComment['CREATED_BY'] = Session::get('userId');
    //                     $insertComment['CREATED_AT'] = Carbon::now()->format('Y-m-d H:i:s');
    //                     $updateAmendData = DB::table('AMEND_REVIEW_TABLE')->insert($insertComment);
    //                 }
    //                 if($updateAmendData){
    //                     DB::commit();                   
    //                     return json_encode(['status'=>'success','msg'=>'Comments saved successfully','data'=>['reviewId'=>$requestData['reviewId'],'column_name'=>$requestData['column_name'],'comments'=>$requestData['comments']]]);
    //                 }else{
    //                     DB::rollback();
    //                     return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    //                 }
    //             }

    //         }
    //     }catch(\Illuminate\Database\QueryException $e) {
    //         if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
    //         $eMessage = $e->getMessage();
    //         CommonFunctions::addExceptionLog($eMessage, $request);
    //         return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    //     }
    // }

    public function amendnpcsubmit(Request $request){
        try{
            if($request->ajax()){
                $requestData = $request->get('data');
                $updateArray = [];
		$sendData = '';
                if(isset($requestData['method']) && $requestData['method'] != ''){
                    $crfNumber = $requestData['crf_number'];
                    $role = Session::get('role');
                    switch ($requestData['method']) {
                        case 'discrepent':
                            Self::amendDiscrepant($requestData['discrepent'],$role);
                            if($role == 19){
                                $role = 2;
                                $status = 'L1 Discrepant';
                            }elseif($role ==  21){
                                $status = 'QC Discrepant';
                                $role = 22;
                            }elseif($role == 23){
                                $status = 'Audit Discrepant';
                                $role = 22;
                            }else{
                                $role = 2;
                                $status = 'L2 Discrepant';
                            }

                            $updateArray = ['CRF_STATUS' => $status,'role' => $role];
                            $saveStatus = CommonFunctions::saveAmendStatusLog($crfNumber,$status,$comment='');
				$sendData = 'true';
                            break;
                        case 'approved':
                          $updateArray = Self::amendClear($crfNumber,$role);
                          $roleId = '1';
                          if($role == 19){
                            $clearStatus = 'Amend-L1 Clear';
                          }elseif($role == 20){
                            $clearStatus = 'Amend-L2 Clear';
                          }elseif($role == 21){
                            $clearStatus = 'Amend-QC Clear';
                            $roleId = '23';
                          }elseif($role == 22){
                            $clearStatus = 'L3 Clear';
                          }elseif($role == 23){
                            $clearStatus = 'Amend-Audit Clear';
                            $roleId = '24';
                          }

                          $saveStatus = CommonFunctions::saveAmendStatusLog($crfNumber,$clearStatus,$role,$comment='');
                          $statusId = CommonFunctions::getAmendStatusId($clearStatus);
                          $saveStatus = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['CRF_STATUS' => $statusId,
                                                                                                    'CRF_NEXT_ROLE' => $roleId ]);

                            if($saveStatus){
                                DB::commit();                   
                                return json_encode(['status'=>'success','msg'=>'Form updated successfully','data'=>[]]);
                            }else{
                                DB::rollback();
                                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                            }
                          return json_encode(['status'=>'success','msg'=>'Waiting for Approval','data'=>[]]);
                        break;
                        case 'hold':
                           if($requestData['role'] == 19){
                                $status = 'L1 Hold';
                            }elseif($requestData['role'] == 20){
                                $status = 'L2 Hold';
                            }elseif($requestData['role'] == 21){
                                $status = 'QC Hold';
                            }else{
                                $status = 'L3 Hold';
                            }
                            $saveStatus = CommonFunctions::saveAmendStatusLog($crfNumber,$status,$role,$requestData['hold_comment']);
                            $updateArray = ['CRF_STATUS' => $status,'role' => $role];
			$sendData = 'true';

                            break;
                        case 'reject':
                           if($requestData['role'] == 19){
                                $status = 'L1 Reject';
                            }else{
                                $status = 'L2 Reject';
                            }
                            $saveStatus = CommonFunctions::saveAmendStatusLog($crfNumber,$status,$role,$requestData['reject_comment']);
                            
                        	    $updateArray = ['CRF_STATUS' => $status,'role' => $role];
				$sendData = 'true';

                            break;

                        default:
                            # code...
                            break;

                    } 
                   
                //     if(isset($updateArray['CRF_STATUS']) && $updateArray['CRF_STATUS'] != ''){
                //         $statusId = CommonFunctions::getAmendStatusId($status);
                //         $saveStatus = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['CRF_STATUS' => $statusId,
                //                                                                                     'CRF_NEXT_ROLE' => $updateArray['role'] ]);
		if($sendData == 'true'){
                     if($saveStatus){
                         DB::commit();                   
                         return json_encode(['status'=>'success','msg'=>'Form updated successfully','data'=>[]]);
                     }else{
                         DB::rollback();
                         return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                     }
		}
                // }
                    }
                }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function amendDiscrepant($discrepantdata,$role){
        try{
        $amendReviewData = [];
        foreach ($discrepantdata as $key => $value) {
            $discrepantdata[$key]['CREATED_BY'] = Session::get('userId');
            $discrepantdata[$key]['CREATED_AT'] = Carbon::now()->format('Y-m-d H:i:s');
            $discrepantdata[$key]['ROLE_ID'] = $role;
            $discrepantdata[$key]['BRANCH_ID'] = Session::get('branchId');
            DB::table('AMEND_REVIEW_TABLE')->insert($discrepantdata[$key]);
        }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public static function amendClear($crfNumber,$role){
        try{
        $returnArray = [];
        if($role == 19){
            $returnArray = ['success'=>'Y','CRF_STATUS' => 'Moved To Amend-L2','role' => 20];
        }

        if($role == 20){
            $amendMasterData = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->get()->toArray();
            $amendMasterData = (array) current($amendMasterData);

            $customerID = isset($amendMasterData['customer_id']) && $amendMasterData['customer_id'] != ''?$amendMasterData['customer_id']:'';
            $crf_id = isset($amendMasterData['id']) && $amendMasterData['id'] !=''?$amendMasterData['id']:'';

               
            $amendQueue  = DB::table('AMEND_QUEUE')->select('TAG')
                                                   ->where('CRF',$crfNumber)
                                                   ->groupBy('TAG')
                                                   ->get()
                                                   ->toArray();

            $amendCallQueue  = DB::table('AMEND_QUEUE')->select('API_CALL')
                                                   ->where('CRF',$crfNumber)
                                                   ->groupBy('API_CALL')
                                                   ->get()
                                                   ->toArray();
            $amendTag =  array();

            for($tagSeq=0;count($amendQueue)>$tagSeq;$tagSeq++){
                array_push($amendTag,$amendQueue[$tagSeq]->tag);
            }

            $amendApiCall = array();

            for($callSeq=0;count($amendCallQueue)>$callSeq;$callSeq++){
                array_push($amendApiCall,$amendCallQueue[$callSeq]->api_call);
            }

            //-----------------First Check Customer Field----------------------\\
        
            $getAcmResponse = 'success';
            $getCrmResponse = 'success';

            //-------------------ACM CALL API------------------\\

            if(in_array('ACM', $amendTag)){
            
                $getAcmResponse = Self::callAcctAmendForAcmData($crfNumber);
            }
            //----------------CRM CALL API----------------------\\
       
            if(in_array('CRM', $amendTag)){
                // echo "<pre>";print_r($amendApiCall);exit;
                if(in_array('API_CUST_MOD',$amendApiCall)){
                        $sequence =  config('amend_rules.'.'API_CUST_MOD');
                        // echo "<pre>";print_r($sequence);exit;
                        $customerData = [$amendMasterData['id'],$amendMasterData['customer_id'],$crfNumber];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','amendforEkycData','API_CUST_MOD',$sequence,$customerData,Carbon::now()->addMinutes(2));
                    }

                    if(in_array('KYC_UPDATE',$amendApiCall)){
                        $sequence =  config('amend_rules.'.'KYC_UPDATE');
                        $getKycData = [$amendMasterData['id'],$amendMasterData['customer_id'],'amend'];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'Api','kycUpdate','KYC_UPDATE',$sequence,$getKycData,Carbon::now()->addMinutes(2));
                        // AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendReviewController','callkycApi','KYC_UPDATE','',$getKycData,Carbon::now()->addMinutes(2));
                }

                // if(in_array('API_AADHAR_LINK',$amendApiCall)){

                //     $getCrmResponse = AmendApi::aadharlinkUpdate($amendMasterData['customer_id']);
                // }

                 //---------------only for kycRefresh-----------------------\\
                //---------------Merging Cutomer Id -------------------\\

                if(in_array('API_MERGE_CUST',$amendApiCall)){
                    $sequence =  config('amend_rules.'.'API_MERGE_CUST');
                    // $getCrmResponse = AmendApi::mergeCustId($amendMasterData['customer_id'],$amendMasterData['account_no'],$crfNumber);
                    $getAccNo = DB::table('AMEND_QUEUE')->select('NEW_VALUE')->where('CRF',$crfNumber)->where('FIELD_NAME','_ACCT_NO')->get()->toArray();

                    $getAccNo =  (array) current($getAccNo);
                    $accountNo = '';
                    if($amendMasterData['account_no'] != ''){
                        $accountNo = $amendMasterData['account_no'];
                    }else{
                        $accountNo = $getAccNo['new_value'];
                }
                    $getMergeData = [$amendMasterData['customer_id'],$accountNo,$crfNumber];
                    AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','mergeCustId','API_MERGE_CUST',$sequence,$getMergeData,Carbon::now()->addMinutes(2));

            }

                if(in_array('API_AADHAR_LINK',$amendApiCall)){
                    $getDetails = DB::table('AMEND_QUEUE')->select('NEW_VALUE','FIELD_NAME')->where('CRF',$crfNumber)->pluck('new_value','field_name');
                    $accountNo = $getDetails['acctNum'];
                    
                    if(strlen($getDetails['NAT_ID_CARD_NUM'])>12){
                        $adhaarRef = $getDetails['NAT_ID_CARD_NUM'];
                    }else{
                        $adhaarRef = AmendCommonFunctions::amendgetVaultRefNumber($getDetails['NAT_ID_CARD_NUM'],$crfNumber);
                        $adhaarRef = $adhaarRef['data']['refernceNumber'];
                    }
                    // echo "<pre>";print_r($adhaarRef);exit;
                    $sequence =  config('amend_rules.'.'API_AADHAR_LINK');
                    $getMergeData = [$amendMasterData['customer_id'],$accountNo,$adhaarRef,$crfNumber];
                    AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','aadharLinkAdd','API_AADHAR_LINK',$sequence,$getMergeData,Carbon::now()->addMinutes(2));
                }

                
            }

            if($getAcmResponse == 'success' && $getCrmResponse == 'success'){
                $returnArray = ['success'=>'Y','CRF_STATUS'=> 'Moved To Amend-QC','role'=>21];            
            }else{
                $returnArray = ['success'=>'N','msg'=>'Amendment API failed!','role'=>20];
            }
        }

            $sequence = '999';
            $getKycData = [$crfNumber];
            AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendRules','amednmarkfromQc','MARK_QC',$sequence,$getKycData,Carbon::now()->addMinutes(2));


        if($role == 22){
            $lastSender = DB::table('AMEND_REVIEW_TABLE')->where('CRF_NUMBER',$crfNumber)->orderBy('ID','DESC')->first(); 
            if($lastSender->role_id == 23){
                $status = 'Moved To Amend-Audit';
            }else{
                $status = 'Moved To Amend-QC';
            }
            $returnArray = ['success'=>'Y','CRF_STATUS' => $status,'role' => $lastSender->role_id];
        }

        if($role == 21){
            $returnArray = ['success'=>'Y','CRF_STATUS' => 'Moved To Amend-Audit','role' => 23];
        }
        return $returnArray;

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
    }
    }

    public function viewekycphoto($ekyc_no){
        
        return CommonFunctions::getekycphoto($ekyc_no);
    }    

    public function amendL3update(Request $request){
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                $is_uploaded = false;               
                $crfNumber = $requestData['crf_number'];
                $updateType = $requestData['updateType'];
                $note = $requestData['note'];               
                $imageName = $requestData['image'];
                $evidenceType = 'Amend_L3_Update_'.$requestData['serial'];
                
                if($updateType == 'image' && $imageName != ''){  
                    $tempFile = storage_path('uploads/temp').'/'.$imageName;
                    if (!File::exists($tempFile)){
                        return json_encode(['status'=>'fail','msg'=>'Error while accessing L3 update image','data'=>[]]);                       
                    }else{
                        $amendMasterData = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->get()->toArray();
                        $amendMasterData = (array)current($amendMasterData);
                        $crfYear = Carbon::parse($amendMasterData['created_at'])->format('Y');
                        $crfId = substr($amendMasterData['id'], -1);
                        $level3Folder = storage_path('uploads/amend').'/'.$crfYear.'/'.$crfId;
                        if(!File::exists($level3Folder)){                                               
                            File::makeDirectory($level3Folder, 0775, true, true);                      
                        }                       
                        File::move($tempFile, $level3Folder.'/'.$imageName);
                        $imageName = $crfYear.'/'.$crfId.'/'.$imageName;                                                                        
                        $is_uploaded = true; 
                    }                                                               
                }else{
                    $is_uploaded = true;
                }
                if($is_uploaded){                   
                    $status = Self::saveamendL3Updates($crfNumber, $evidenceType, $note, $imageName);
                    return json_encode(['status'=>'success','msg'=>'L3 Comments/Image updated successfully','note'=>$note, 'imageName'=>$imageName]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
    }

    public function saveamendL3Updates($crfNumber, $evidenceType, $note, $imageName)
    {
        if($note == '' && $imageName == '') return false;
        $amendEvidence = DB::table('AMEND_EVIDENCE')->where('EVIDENCE', $evidenceType)
                                            ->pluck('id')->toArray();

        $amendEvidenceId = current($amendEvidence);

        $amendProofList = DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$crfNumber)->where('EVIDENCE_ID',$amendEvidenceId)->get()->toArray();

        if(count($amendProofList) > 0){
           $scenario = 'Update';
           if($note == ''){
               $note = $amendProofList[0]->comments;
           }
           if($imageName == ''){
               $imageName = $amendProofList[0]->amend_proof_image;
           }
           $upsertDetails = Array(
                                'CRF_NUMBER' => $crfNumber,
                                'AMEND_PROOF_IMAGE' => $imageName,
                                'COMMENTS' => $note,
                                'EVIDENCE_ID' => $amendEvidenceId,
                                'UPDATED_BY' => Session::get('userId'),
                                'UPDATED_AT' => Carbon::now(),
                            );
            $status = DB::table('AMEND_PROOF_DOCUMENT')
                                ->where(['CRF_NUMBER' => $crfNumber, 'EVIDENCE_ID' => $amendEvidenceId])
                                ->update($upsertDetails);

        }else{
           $scenario = 'Insert';
           $upsertDetails = Array(
                                'CRF_NUMBER' => $crfNumber,
                                'AMEND_PROOF_IMAGE' => $imageName,
                                'COMMENTS' => $note,
                                'EVIDENCE_ID' => $amendEvidenceId,
                                'CREATED_BY' => Session::get('userId'),
                                'CREATED_AT' => Carbon::now(),
                            );
           $status = DB::table('AMEND_PROOF_DOCUMENT')->insert($upsertDetails);
        }

        if($status){
            return true;
        }else{
            return false;
        }
    }

    //-------------------------call accounts numbers or ACM api----------------\\

    public static function callAcctAmendForAcmData($crfNumber){
        try{

            $amendData = DB::table('AMEND_QUEUE')->select('ACCOUNT_NO','API_CALL')
                                             ->where('CRF',$crfNumber)
                                             ->where('TAG','ACM')
                                            //  ->orderBy('ACCOUNT_NO')
                                            ->orderBy('API_CALL','ASC')
                                             ->get()
                                             ->toArray();    
                                             
            $amendSchCode = DB::table('AMEND_MASTER')->select('CACHE_DATA')
                                                     ->where('CRF_NUMBER',$crfNumber)
                                                     ->get()
                                                     ->toArray();
            $amendSchCode = (array) current($amendSchCode);

            $getDetails = json_decode(base64_decode($amendSchCode['cache_data'],true));
            $getSchemeCode = $getDetails->accountDetails->accountDetails->SCHM_CODE;
            $getSchemeCode = substr($getSchemeCode,0,2);
            // echo "<pre>";print_r($getSchemeCode);exit;

            $accountNo = '';
            $tempAccountNo  = $accountNo;
            $statusApi = true;
            $statusMessage = '';
          
            for($amdSeq=0;count($amendData)>$amdSeq;$amdSeq++){

                $accountNo = $amendData[$amdSeq]->account_no;

                if($accountNo != $tempAccountNo){

                    if($amendData[$amdSeq]->api_call == 'API_ACCOUNT_MOD'){
                        // $amendAcmResponse = AmendApi::amendforAcmData($accountNo,$crfNumber);
                        $sequence =  config('amend_rules.'.$amendData[$amdSeq]->api_call);
                        $accountData = [$accountNo,$crfNumber];
            
                        if($getSchemeCode == 'CA'){
                            AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','cadormantAccActiveation','API_ACCOUNT_MOD',$sequence,$accountData,Carbon::now()->addMinutes(2));

                        }else{

                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','amendforAcmData','API_ACCOUNT_MOD',$sequence,$accountData,Carbon::now()->addMinutes(2));
                    }
                    }

                    //------------------------Account Clouser-------------------------\\
                
                    if($amendData[$amdSeq]->api_call == 'API_ACC_CLOSURE'){
                        // $amendAcmResponse = AmendApi::accountClouser($accountNo);
                        $sequence =  config('amend_rules.'.$amendData[$amdSeq]->api_call);
                        $getData = [$accountNo];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','accountClouser','API_ACC_CLOSURE',$sequence,$getData,Carbon::now()->addMinutes(2));
                    }

                    //-------------------------Nominee Modification---------------------\\

                    if($amendData[$amdSeq]->api_call == 'API_ACC_MOD'){
                        // $amendAcmResponse = AmendApi::nomineeModification($accountNo,$crfNumber);
                        $sequence =  config('amend_rules.'.$amendData[$amdSeq]->api_call);
                        $getAccountData = [$accountNo,$crfNumber];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','nomineeModification','API_ACC_MOD',$sequence,$getAccountData,Carbon::now()->addMinutes(2));
                    }

                    if($amendData[$amdSeq]->api_call == 'API_GPA_CNL'){
                        // $amendAcmResponse = AmendApi::gpaCancelation($accountNo,$crfNumber);
                        $sequence =  config('amend_rules.'.$amendData[$amdSeq]->api_call);
                        $getAccountData = [$accountNo,$crfNumber];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','gpaCancelation','API_GPA_CNL',$sequence,$getAccountData,Carbon::now()->addMinutes(2));
                    }

                    if($amendData[$amdSeq]->api_call == 'API_SCHEME_CONV'){
                        // $amendAcmResponse =  AmendApi::schemeConversion($accountNo,$crfNumber);
                        $sequence =  config('amend_rules.'.$amendData[$amdSeq]->api_call);
                        $getAccountData = [$accountNo,$crfNumber];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','schemeConversion','API_SCHEME_CONV',$sequence,$getAccountData,Carbon::now()->addMinutes(2));
                    }

                    if($amendData[$amdSeq]->api_call == 'API_SIGN_UPLOAD'){
                        // $amendAcmResponse =  AmendApi::amenduploadSignature($accountNo,$crfNumber);
                        $sequence =  config('amend_rules.'.$amendData[$amdSeq]->api_call);
                        $getAccountData = [$accountNo,$crfNumber];
                        AmendApiQueueCommonFunctions::insertIntoAmendApiQueue($crfNumber,'AmendApi','amenduploadSignature','API_SIGN_UPLOAD',$sequence,$getAccountData,Carbon::now()->addMinutes(2));

                    }
                }
            }
            
            if($statusApi){
                $statusMessage = 'success';
            }else{
                $statusMessage = 'fail';
            }
            return $statusMessage;

        }catch(Illuminate\Database\QueryException $e){
            if(env('APP_CUBE_DEBUG')){$e->getMessage();}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
}
