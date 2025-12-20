<?php

namespace App\Http\Controllers\ETBNTB;
use App\Http\Controllers\Controller;
use DB;
use Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use Crypt,Cache,Session;
use App\Helpers\Api;
use App\Helpers\Rules;


class checkIDOVDController extends Controller
{
   public static function etbntb_check_via_idovd(Request $request){
   try
   {       
        $requestData = Arr::except($request->get('data'),'functionName');      
          
        $applicantId=isset($requestData['applicantID']) && $requestData['applicantID']!=''?$requestData['applicantID']:''; 
        $tab=isset($requestData['tab'])&& $requestData['tab']!=''?$requestData['tab']:'';      
           
        if(isset($requestData['proofOfIdentity'])&& $requestData['proofOfIdentity'] !='' ){           
           
        $proofOfIdentity = DB::table('OVD_TYPES')
                            ->where('ID',$requestData['proofOfIdentity'])
                            ->get()->toArray();        
        $proofOfIdentity=(array)current($proofOfIdentity);       

        if(isset($requestData['id_proof_aadhaar_ref_number'])){  // check for e-kyc
            if($requestData['proofOfIdentity'] == 9 && $requestData['id_proof_aadhaar_ref_number'] !=''){
                $referenceNo=$requestData['id_proof_aadhaar_ref_number'];
                $proofOfIdentity['id_proof_code']= 'ADHAR';
            }
            else{
                $referenceNo='';
            }       
        }else{           
            if(isset($requestData['proofcardNumber']) && $requestData['proofcardNumber'] !=''){
                $requestData['proofcardNumber'] = CommonFunctions::decryptRS($requestData['proofcardNumber']);
                if($requestData['proofcardNumber'] == '' || substr($requestData['proofcardNumber'],0,2) == '-1'){
                    return json_encode(['status'=>'warning','msg'=>'Decryption failed for applicant-'.$applicantId,'data'=>[$requestData['proofcardNumber']]]);  
                } 
            } 
                        
            $validationFailedField = Rules::specialCharValidations($requestData['proofcardNumber']);           
            if (isset($validationFailedField) && $validationFailedField != '') {
                return json_encode(['status'=>'warning','msg'=>$validationFailedField['msg'],'data'=>[]]);
            }           
            if($proofOfIdentity['id_proof_code'] == 'ADHAR'){
                $custId='001189092';                         
                $aadharNumber=str_replace('-','',$requestData['proofcardNumber']); 
                // echo"<pre>"; print_r($aadharNumber); exit; 
                $formId = Session::get('formId');      
                $AdharRefNumber=Api::aadharValutSvc($custId,$aadharNumber,$formId);  

                if($AdharRefNumber['status'] == 'Success'){
                    $referenceNo=$AdharRefNumber['data']['response']['referenceKey'];                    
                                
                }else{
                    return json_encode(['status'=>'warning','msg'=>'Failed to check Reference Number for applicant-'.$applicantId,'data'=>[$requestData['proofcardNumber']]]);                   
                }               

            }
            else{
                $referenceNo=$requestData['proofcardNumber'];
            } 
        }
       
        if(env('APP_SETUP') == 'DEV'){
            $schema = DB::table('CRM_ENTITYDOCUMENT');
        }else{
            $schema = DB::connection('oracle2')->table('CRMUSER.ENTITYDOCUMENT');
        }

        $checkcustExist=$schema->where('DOCTYPECODE',$proofOfIdentity['doc_type'])
                                    ->where('DOCCODE',$proofOfIdentity['id_proof_code'])
                                    ->where('REFERENCENUMBER',$referenceNo)
                                    ->take(20)
                                    ->pluck('orgkey');

        if(count($checkcustExist) > 0)
        {
            $customerId = $checkcustExist->implode(',');
            Session::put('isCustExists_ID-'.$applicantId,1);
            return json_encode(['status'=>'warning','msg'=>'Customer already exists with customer ID-'.$customerId,'data'=>$customerId]);

        }else{
            
            $checkaddress=Self::etbntb_check($requestData);
            if(isset($checkaddress)){            
            $checkaddress=json_decode($checkaddress);
                if($checkaddress->status == 'success'){
                    Session::put('isCustExists_ID-'.$applicantId,0);
                    return json_encode(['status'=>'success','msg'=>'FINACLE : Customer does not exists','data'=>['tab'=>$checkaddress->data->tab]]);
                }
                else{
                    Session::put('isCustExists_ID-'.$applicantId,1);
                    return json_encode(['status'=>'warning','msg'=>'Customer already exists with customer ID-'.$checkaddress->data,'data'=>$checkaddress->data]);
                }
            }
            else{
                Session::put('isCustExists_Address-'.$applicantId,0);
                return json_encode(['status'=>'success','msg'=>'FINACLE : Customer does not exists','data'=>['tab'=>$tab]]);
            }
           
        }    
        }

        if(isset($requestData['proofOfAddress']) && $requestData['proofOfAddress'] !='' ){           
            $checkaddress=Self::etbntb_check($requestData);
            $checkaddress=json_decode($checkaddress);
            if($checkaddress->status == 'success'){
                Session::put('isCustExists_ID-'.$applicantId,0);
                return json_encode(['status'=>'success','msg'=>'FINACLE : Customer does not exists','data'=>['tab'=>$checkaddress->data->tab]]);
            }
            else{
                Session::put('isCustExists_ID-'.$applicantId,1);
                return json_encode(['status'=>'warning','msg'=>'Customer already exists with customer ID-'.$checkaddress->data,'data'=>$checkaddress->data]);
            }
        }       
        return json_encode(['status'=>'warning','msg'=>'Error! Failed to check customer exist','data'=>[]]);

    }catch(\Illuminate\Database\QueryException $e) {
        if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        $eMessage = $e->getMessage();
        CommonFunctions::addExceptionLog($eMessage, $request);
        return json_encode(['status'=>'warning','msg'=>'Error! Failed to check customer exist','data'=>[]]);
    }
    }   

    public static function etbntb_check($requestData){

        $applicantId=isset($requestData['applicantID']) && $requestData['applicantID']!=''?$requestData['applicantID']:''; 
        $tab=isset($requestData['tab'])&& $requestData['tab']!=''?$requestData['tab']:'';      

        if(isset($requestData['proofOfAddress']) && $requestData['proofOfAddress'] !='' ){           

            $proofOfIdentity = DB::table('OVD_TYPES')
                            ->where('ID',$requestData['proofOfAddress'])
                            ->get()->toArray();        
            $proofOfIdentity=(array)current($proofOfIdentity);    
    
            if(isset($requestData['add_proof_aadhaar_ref_number'])){ // check for e-kyc
                if($requestData['proofOfAddress'] == 9 && $requestData['add_proof_aadhaar_ref_number'] !=''){
                    $referenceNo=$requestData['add_proof_aadhaar_ref_number'];
                    $proofOfIdentity['id_proof_code']= 'ADHAR';
                }
                else{
                    $referenceNo='';
                   
                }                
            }else{              
               
                if(isset($requestData['addproofcardNumber']) && $requestData['addproofcardNumber'] !=''){              
                    $requestData['addproofcardNumber'] = CommonFunctions::decryptRS($requestData['addproofcardNumber']);
                    if($requestData['addproofcardNumber'] == '' || substr($requestData['addproofcardNumber'],0,2) == '-1'){
                        return json_encode(['status'=>'warning','msg'=>'Decryption failed for applicant-'.$applicantId,'data'=>[$requestData['addproofcardNumber']]]);  
                    } 
                } 
               
                $validationFailedField = Rules::specialCharValidations($requestData['addproofcardNumber']);           
                if (isset($validationFailedField) && $validationFailedField != '') {
                    return json_encode(['status'=>'warning','msg'=>$validationFailedField['msg'],'data'=>[]]);
                }           
                if($proofOfIdentity['id_proof_code'] == 'ADHAR'){
                    $custId='001189092';
                    $aadharNumber=str_replace('-','',$requestData['addproofcardNumber']);  
                    $formId = Session::get('formId');  
    
                    $AdharRefNumber=Api::aadharValutSvc($custId,$aadharNumber,$formId);
                                    
                    if($AdharRefNumber['status'] == 'Success'){
                        $referenceNo=$AdharRefNumber['data']['response']['referenceKey'];                    
                    }else{                    
                        return json_encode(['status'=>'warning','msg'=>'failed to check Reference Number for applicant-'.$applicantId,'data'=>[$requestData['addproofcardNumber']]]);                                      
                    } 
                        
                }           
                else{
                    $referenceNo=$requestData['addproofcardNumber'];
                }
            }

            if(env('APP_SETUP') == 'DEV'){
                $schema = DB::table('CRM_ENTITYDOCUMENT');
            }else{
                $schema = DB::connection('oracle2')->table('CRMUSER.ENTITYDOCUMENT');
            }
    
            $checkcustExist=$schema->where('DOCTYPECODE',$proofOfIdentity['doc_type'])
                            ->where('DOCCODE',$proofOfIdentity['id_proof_code'])
                            ->where('REFERENCENUMBER',$referenceNo)
                            ->take(20)
                            ->pluck('orgkey');
                        
            if(count($checkcustExist) > 0)
            {
                $customerId = $checkcustExist->implode(',');
                Session::put('isCustExists_Address-'.$applicantId,1);
                return json_encode(['status'=>'warning','msg'=>'Customer is already exists with customer ID-'.$customerId,'data'=>$customerId]);
    
            }else{
                Session::put('isCustExists_Address-'.$applicantId,0);
                return json_encode(['status'=>'success','msg'=>'FINACLE : Customer does not exists','data'=>['tab'=>$tab]]);
            }    
        }
    
    }
}
