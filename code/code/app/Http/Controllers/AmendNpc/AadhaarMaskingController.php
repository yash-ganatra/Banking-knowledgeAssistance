<?php

namespace App\Http\Controllers\AmendNpc;

use App\Helpers\CommonFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use File;
use Cookie;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\ExceptionController;


class AadhaarMaskingController extends Controller
{
    // public function display(Request $request)
    // {
        
    //     if(count($request->all()) > 0){
    //         $tokenParams = explode('.',Cookie::get('token'));
    //         $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
    //         $requestData = explode('.',base64_decode($decodedString));
    //     }
    //     $aadhaarMaskingDetails = array();
    //     $imagePresent = false;
    //     $image_name = '';
    //     $imageField = '';
    //     $mask_number = '';
    //     $multiside = '';
    //     $side = '';
    //     $doc_id = '';
    //     $custName='';
    //     $documentType='';

    //     if(isset($requestData) && $requestData != "")
    //     {              
    //         $role = Session::get('role');
    //         $type="all";
    //         $aof_No=$requestData[0];

    //         $aadhaarMaskingDetails=DB::table('AMEND_PROOF_DOCUMENT')->where('CRF_NUMBER',$requestData[0])
    //                                                                 ->where('FLAG',null)
    //                                                                 ->where('amend_proof_image', 'not like', '%.pdf')
    //                                                                 ->orderBy('AMEND_PROOF_DOCUMENT.ID','ASC')
    //                                                                 ->take(1)
    //                                                                 ->get()
    //                                                                 ->toArray();
                                                                    
    //                                                                 // echo "<pre>";print_r($aadhaarMaskingDetails);exit;
                     
    //         if(count($aadhaarMaskingDetails)>0 && !empty($aadhaarMaskingDetails))
    //         {
    //             $aadhaarMaskingDetails=(array)current($aadhaarMaskingDetails);
    //             $documentType=DB::table('AMEND_EVIDENCE')->where('ID',$aadhaarMaskingDetails['evidence_id'])->get()->toArray();                
    //             $documentType=(array)current($documentType);
               

    //             $custName=DB::table('AMEND_MASTER')->where('CRF_NUMBER',$requestData[0])->get()->toArray();
    //             $custName=(array)current($custName);
              
               
    //             $imagePresent='';
    //             $imageField='';
                        
    //             $image_name=isset($aadhaarMaskingDetails['amend_proof_image'])&& $aadhaarMaskingDetails['amend_proof_image']!='' ?$aadhaarMaskingDetails['amend_proof_image']:'';              
               
    //             if(isset($image_name)){
    //                 $imagePresent=true;
    //             }else{
    //                 $imagePresent=false;
    //             }
    //         }
    //     }
    //     else{           

    //         $aadhaarMaskingDetails = DB::table('AMEND_PROOF_DOCUMENT')
    //                                         ->where('FLAG',null)
    //                                         ->where('amend_proof_image', 'not like', '%.pdf')
    //                                         ->orderBy('AMEND_PROOF_DOCUMENT.ID','ASC')
    //                                         ->take(1)
    //                                         ->get()
    //                                         ->toArray();  
                
                                                          
    //         if(count($aadhaarMaskingDetails)>0 && !empty($aadhaarMaskingDetails))
    //         {
              
    //             $aadhaarMaskingDetails=(array)current($aadhaarMaskingDetails);
    //             $documentType=DB::table('AMEND_EVIDENCE')->where('ID',$aadhaarMaskingDetails['evidence_id'])->get()->toArray();                
    //             $documentType=(array)current($documentType);               

    //             $custName=DB::table('AMEND_MASTER')->where('CRF_NUMBER',$aadhaarMaskingDetails['crf_number'])->get()->toArray();
    //             $custName=(array)current($custName);

    //             $imagePresent='';
    //             $imageField='';              
    //             $image_name=isset($aadhaarMaskingDetails['amend_proof_image'])&& $aadhaarMaskingDetails['amend_proof_image']!='' ?$aadhaarMaskingDetails['amend_proof_image']:'';                
               

    //             if(isset($image_name)){
    //                 $imagePresent=true;
    //             }else{
    //                 $imagePresent=false;
    //             }
    //         }

    //     }
       
    //     return view('amendnpc.aadhaarmaskingdashboard')->with('aadhaarMaskingDetails', $aadhaarMaskingDetails)
    //                                                     ->with('imagePresent', $imagePresent)
    //                                                     ->with('image_name', $image_name)
    //                                                     ->with('mask_number', $mask_number)
    //                                                     ->with('imageName', $image_name)
    //                                                     ->with('imageField', $imageField)
    //                                                     ->with('side', $side)
    //                                                     ->with('multiside', $multiside)
    //                                                     ->with('doc_id',$doc_id)
    //                                                     ->with('custName',$custName)
    //                                                     ->with('documentType',$documentType);
                                                       
    // }

    

    public function all_amend_aadharmasking(Request $request){

        // $crfNumber = $request->input('crf_number');  
        $crfNo=isset($request['number'])&& $request['number']!=''?$request['number']:'' ;        
        if($crfNo !=''){
        $crfNumber = $crfNo;    
                }
        else{
            $crfNumber = $request->input('crf_number'); 
        }    
           
        $aadhaarDetails = DB::table('AMEND_PROOF_DOCUMENT as amnd')->select('amnd.ID','amnd.CRF_NUMBER','amnd.CRF_ID','amnd.AMEND_PROOF_IMAGE','amnd.FLAG','amnd.EVIDENCE_ID',
                                                               'ae.EVIDENCE as EVIDENCE_DESCRIPTION' )
                                                           ->leftJoin('AMEND_EVIDENCE as ae', 'ae.ID', '=', 'amnd.EVIDENCE_ID')
                                                           ->where('amnd.AMEND_PROOF_IMAGE', 'not like', '%.pdf')
                                                           ->where('amnd.CRF_NUMBER', $crfNumber)
                                                           ->orderBy('amnd.ID', 'ASC')
                                                           ->get()->toArray();
        $aadhaarMaster = DB::table('AMEND_MASTER')->select('ADDITIONAL_DATA')->where('CRF_NUMBER',$crfNumber)
                                                                             ->get()->toArray();    
                                                                             
        $getOvdTypes = DB::table('OVD_TYPES')->select('ID','OVD')->where('IS_ACTIVE',1)->pluck('ovd','id')->toArray();
        // echo "<pre>";print_r($getOvdTypes);exit;
        if(count($aadhaarMaster) >0){
            $aadhaarMaster = (array) current($aadhaarMaster);
            $getproofData = json_decode($aadhaarMaster['additional_data'],true);

            if(isset($getproofData['proofIdData']['proof_id']) && $getproofData['proofIdData']['proof_id'] !=''){
               $getovdDesc = isset($getOvdTypes[$getproofData['proofIdData']['proof_id']]) && $getOvdTypes[$getproofData['proofIdData']['proof_id']] != ''?$getOvdTypes[$getproofData['proofIdData']['proof_id']]:'';
                for($i=0;count($aadhaarDetails)>$i;$i++){
                    if(in_array($aadhaarDetails[$i]->{'evidence_id'},[2,3])){
                        $aadhaarDetails[$i]->{'evidence_description'} = $getovdDesc.' - '.$aadhaarDetails[$i]->{'evidence_description'};
            }
        }
            }
            // echo "<pre>";print_r($getproofData);exit;
            if(isset($getproofData['comuproofAddData']['addproof_id']) && $getproofData['comuproofAddData']['addproof_id'] !=''){
                $getovdDesc = isset($getOvdTypes[$getproofData['comuproofAddData']['addproof_id']]) && $getOvdTypes[$getproofData['comuproofAddData']['addproof_id']] != ''?$getOvdTypes[$getproofData['comuproofAddData']['addproof_id']]:'';
                 for($i=0;count($aadhaarDetails)>$i;$i++){
                     if(in_array($aadhaarDetails[$i]->{'evidence_id'},[36,37])){
                         $aadhaarDetails[$i]->{'evidence_description'} = $getovdDesc.' - '.$aadhaarDetails[$i]->{'evidence_description'};
                     }
                 }
             }
        }

        return view('amendnpc.all_amend_aadharmasking')->with('aadhaarDetails',$aadhaarDetails);
                                                   
    }

    public function display_aadhar_amend(Request $request){
        
        $ID = $request['id'];     
            $aadhaarMaskingDetails = DB::table('AMEND_PROOF_DOCUMENT')
                                // ->where(function($query) {
                                //     $query->whereNull('FLAG')
                                //         ->orWhere('FLAG', 'N');
                                // })
                                ->where('ID', $ID)
                                            ->where('amend_proof_image', 'not like', '%.pdf')
                                ->orderBy('ID', 'ASC')
                                            ->get()
                                ->toArray();

        if(count($aadhaarMaskingDetails)>0){
        $aadhaarMaskingDetails=(array)current($aadhaarMaskingDetails);     
                
        $documentType=DB::table('AMEND_EVIDENCE')->select('EVIDENCE')->where('ID',$aadhaarMaskingDetails['evidence_id'])->get()->toArray();                
        $documentType=(array)current($documentType);
                                                          
        $custName=DB::table('AMEND_MASTER')->select('CUSTOMER_NAME','ADDITIONAL_DATA')->where('CRF_NUMBER',$aadhaarMaskingDetails['crf_number'])->get()->toArray();
                $custName=(array)current($custName);

        // echo "<pre>";print_r($custName);exit                                          
        if(count($aadhaarMaskingDetails)>0){            
               
            $imagePresent='';
            $image_name=isset($aadhaarMaskingDetails['amend_proof_image'])&& $aadhaarMaskingDetails['amend_proof_image']!='' ?$aadhaarMaskingDetails['amend_proof_image']:'';
            $mask_number='';
            $imageField='';
            $side='';
            $multiside='';
            $doc_id='';

                if(isset($image_name)){
                    $imagePresent=true;
                }else{
                    $imagePresent=false;
                }
            $aadhaarMaskingDetails=isset($aadhaarMaskingDetails)&&$aadhaarMaskingDetails!=''?$aadhaarMaskingDetails:'';
        }        
        // echo "<pre>";print_r($aadhaarMaskingDetails);exit;
        return view('amendnpc.aadhaarmaskingdashboard')->with('aadhaarMaskingDetails', $aadhaarMaskingDetails)
                                                        ->with('imagePresent', $imagePresent)
                                                        ->with('image_name', $image_name)
                                                        ->with('mask_number', $mask_number)
                                                        ->with('imageName', $image_name)
                                                        ->with('imageField', $imageField)
                                                        ->with('side', $side)
                                                        ->with('multiside', $multiside)
                                                        ->with('doc_id',$doc_id)
                                                        ->with('custName',$custName)
                                                        ->with('documentType',$documentType);
        }else{
        $aadhaarMaskingDetails = array();
        $imagePresent = false;
        $image_name = '';
        $imageField = '';
        $mask_number = '';
        $multiside = '';
        $side = '';
        $doc_id = '';
        $custName='';
        $documentType='';

        return view('amendnpc.aadhaarmaskingdashboard')->with('aadhaarMaskingDetails', $aadhaarMaskingDetails)
                                                        ->with('imagePresent', $imagePresent)
                                                        ->with('image_name', $image_name)
                                                        ->with('mask_number', $mask_number)
                                                        ->with('imageName', $image_name)
                                                        ->with('imageField', $imageField)
                                                        ->with('side', $side)
                                                        ->with('multiside', $multiside)
                                                        ->with('doc_id',$doc_id)
                                                        ->with('custName',$custName)
                                                        ->with('documentType',$documentType);

    }
}

public function saveAadhaarMask(Request $request)
    {
        $requestData = $request->get('data');        
        $image = $requestData['updatedCanvasImage'];

        if ($image != '') {
            $imageSize = (strlen($image) / 4) * 3;
            $imageMaxSize = CommonFunctions::getapplicationSettingsDetails('upload_max_size');
            $msgString = $imageMaxSize / 1024 / 1024;

            if ($imageSize > $imageMaxSize) {
                return json_encode(['status' => 'fail', 'msg' => 'File size larger than expected (' . $msgString . 'MB)', 'data' => []]);
            }

            // Decrypt image
            $magicStr = substr($image, 11, 4);
            if ($magicStr == 'png;') {
                $image = str_replace('data:image/png;base64,', '', $image);
            } elseif ($magicStr == 'jpeg') {
                $image = str_replace('data:image/jpeg;base64,', '', $image);
            } else {
                return json_encode(['status' => 'fail', 'msg' => 'Error detecting file format. Please retry.', 'data' => []]);
            }

            $imgData = (base64_decode($requestData['imageName']));
            $folder = storage_path('uploads/amend/' .$imgData);
                    
            if(file_exists($folder)){
                $imageName = $requestData['imageName'];
                $imageMasked = File::put($folder, base64_decode($image));
                $filePath = storage_path('/uploads/amend/'.$imgData); 
        }
            else{
                return json_encode(['status'=>'fail','msg'=>'Image File Not Found','data'=>[]]);
            }


            if($requestData['button'] == 'saveImage' ||$requestData['button'] == 'submitImage' ){

                $maskCount=DB::table('AMEND_PROOF_DOCUMENT')
                           ->select('MASK_COUNT')
                           ->where('ID',$requestData['id'])                         
                           ->get()
                           ->toArray();
                $maskCount=(array)current($maskCount);
    
                $count=$maskCount['mask_count']+1;                 
            
                $aflagUpdate = DB::table('AMEND_PROOF_DOCUMENT')
                                ->where('ID',$requestData['id'])  
                                ->update(['MASK_COUNT'=>$count]);                              

            }


        } else {
            $filePath = '';
        }
        if($requestData['button'] == 'saveImage'){
            return json_encode(['status'=>'success','msg'=>'Mask updated successfully. You may continue or submit!','data'=>[]]);
        }

        $image= DB::table('AMEND_PROOF_DOCUMENT')
        ->select('MASK_COUNT')
        ->where('ID',$requestData['id'])  
        ->get()
        ->toArray();
        $maskimageCount=(array)current($image); 
        
        if($maskimageCount['mask_count'] >0){             
        $image_flag='Y';              
        }
        else{             
            $image_flag='N';

        }        
        
         $Updateflag = DB::table('AMEND_PROOF_DOCUMENT')->where('ID',$requestData['id'])                                                  
                                                        ->update(['FLAG'=>$image_flag]);
       
        $getImageMaskData=DB::table('AMEND_MASTER')->where('CRF_NUMBER',$requestData['crf_number'])->get()->toArray();       
        
        $getamendqueueData=DB::table('AMEND_QUEUE')->where('CRF',$requestData['crf_number'])->get()->toArray();  
        
        if(count($getImageMaskData)>0 || count($getamendqueueData)>0 ){
        $getImageMaskData=(array)current($getImageMaskData);
        $getImageMaskData=isset($getImageMaskData['additional_data'])&& $getImageMaskData['additional_data']!=''?$getImageMaskData['additional_data']:'';
        $getImageMaskData=json_decode($getImageMaskData);       
            
        if(!empty($getImageMaskData->proofIdData)){
          
            if($getImageMaskData->proofIdData->proof_id == 1)
            {
            
                $ImageMaskData= 'XXXX-XXXX-'.substr($getImageMaskData->proofIdData->id_code,-4);

                $updateData=json_encode(['proofIdData'=>['proof_id'=>$getImageMaskData->proofIdData->proof_id,'id_code'=>$ImageMaskData,'id_date'=>$getImageMaskData->proofIdData->id_date],'comuproofAddData'=>$getImageMaskData->comuproofAddData]);
                
                $datatata=DB::table('AMEND_MASTER')->where('crf_number',$requestData['crf_number'])
                ->update(['ADDITIONAL_DATA'=>$updateData]);                
              
            } 
        }     
        $maskNumber=[];
        $count=0;
        foreach($getamendqueueData as $amendqueueDetails)
        {
            if(!empty($amendqueueDetails->addition_data_ekyc)){
               
                $getImageMaskData=json_decode($amendqueueDetails->addition_data_ekyc);

                if(isset($getImageMaskData->proof_id) && $getImageMaskData->proof_id == 1){
                   
                    $aadhaarMaskedNumber= 'XXXX-XXXX-'.substr($getImageMaskData->id_code,-4);
                    $maskNumber[$count]['ADDITION_DATA_EKYC']=json_encode(['proof_id'=>$getImageMaskData->proof_id,'id_code'=>$aadhaarMaskedNumber,'id_date'=>$getImageMaskData->id_date]);
                }                       
               
            } 
            if($amendqueueDetails->field_name == '_AADHAR_NUMBER'){
                $maskNumber[$count]['NEW_VALUE']='XXXX-XXXX-'.substr($amendqueueDetails->new_value,-4); 
                $maskNumber[$count]['NEW_VALUE_DISPLAY']='XXXX-XXXX-'.substr($amendqueueDetails->new_value,-4);  

            }     
                   
                $count++;
        }
            if(count($maskNumber)>0){
                for($i=0;count($maskNumber)>$i;$i++){  
                                    
                    DB::table('AMEND_QUEUE')->where('CRF',$getamendqueueData[$i]->crf)
                                                     ->update($maskNumber[$i]);
                }
            }
            
            return json_encode(['status'=>'success','msg'=>'Aadhaar Image Masked Successfully','data'=>[$requestData['crf_number']]]);
            // return redirect('amendnpc/all_amend_aadharmasking')->with('status', 'Aadhaar Image Masked Successfully');
        }
          
        return json_encode(['status'=>'success','msg'=>'Image process Successfully','data'=>[$requestData['crf_number']]]);
    }

}