<?php

namespace App\Http\Controllers\Archival;

use App\Helpers\CommonFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use File;
use Cookie;
use Crypt,Cache,Session;
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

    //     if(isset($requestData) && $requestData != "")
    //     {            
           
    //         $role = Session::get('role');
    //         $type="all";
    //         $aof_No=$requestData[0];          
            
    //         $checkformid=DB::table('ACCOUNT_DETAILS')->select('ID',)->where('AOF_NUMBER',$requestData[0])->get()->toArray();
    //         $checkformid=(array)current($checkformid);
            
    //         $checkFormIdallattached=DB::table('ALL_ATTACHMENTS')->where('FORM_ID',$checkformid['id'])->get()->toArray();
    //         $checkformidMaskLogs=DB::table('AADHAAR_MASKED_LOGS')->where('FORM_ID',$checkformid['id'])->get()->toArray();
   
     
    //         if(empty($checkFormIdallattached ) && empty($checkformidMaskLogs))
    //         {      
               
    //             $imagess=CommonFunctions::checkMaskImages($aof_No,$type,$role);
                              
    //         }

    //         $aadhaarMaskingDetails=DB::table('ALL_ATTACHMENTS')
    //         ->where('ALL_ATTACHMENTS.AOF_NUMBER',$requestData[0])
    //         ->where('RESULT1', 'not like', '%.pdf')
    //         ->where('ALL_ATTACHMENTS.FLAG',null)
    //         ->orderBy('ALL_ATTACHMENTS.ID','ASC')
    //         ->take(1)
    //         ->get()
    //         ->toArray();
                     
    //         if(count($aadhaarMaskingDetails)>0 && !empty($aadhaarMaskingDetails))
    //         {
    //             $aadhaarMaskingDetails=(array)current($aadhaarMaskingDetails);
    //             $custName=DB::table('CUSTOMER_OVD_DETAILS')->select('FIRST_NAME','LAST_NAME')->where('FORM_ID',$aadhaarMaskingDetails['form_id'])->get()->toArray();
    //             $custName=(array)current($custName);                

    //             $imagePresent='';
    //             $image_name=isset($aadhaarMaskingDetails['result1'])&& $aadhaarMaskingDetails['result1']!='' ?$aadhaarMaskingDetails['result1']:'';
    //             $mask_number='';
    //             $imageField=isset($aadhaarMaskingDetails['image_type'])&& $aadhaarMaskingDetails['image_type']!=''?$aadhaarMaskingDetails['image_type']:'';
    //             $side=isset($aadhaarMaskingDetails['side'])&&$aadhaarMaskingDetails['side']!=''?$aadhaarMaskingDetails['side']:'';
    //             $multiside='';
    //             $doc_id=isset($aadhaarMaskingDetails['image_type'])&&$aadhaarMaskingDetails['image_type']!=''?$aadhaarMaskingDetails['image_type']:'';

    //             if(isset($image_name)){
    //                 $imagePresent=true;
    //             }else{
    //                 $imagePresent=false;
    //             }
    //         }
    //     }
    //     else{           

    //         $aadhaarMaskingDetails=DB::table('ALL_ATTACHMENTS')
    //         // ->leftjoin('CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ALL_ATTACHMENTS.FORM_ID')
    //         ->where('ALL_ATTACHMENTS.FLAG',null)
    //         ->where('RESULT1', 'not like', '%.pdf')
    //         ->orderBy('ALL_ATTACHMENTS.ID','ASC')
    //         ->take(1)
    //         ->get()
    //         ->toArray();
    //         $aadhaarMaskingDetails=(array)current($aadhaarMaskingDetails);

    //         $custName=DB::table('CUSTOMER_OVD_DETAILS')->select('FIRST_NAME','LAST_NAME')->where('FORM_ID',$aadhaarMaskingDetails['form_id'])->get()->toArray();
    //         $custName=(array)current($custName);
      
    //         if(count($aadhaarMaskingDetails)>0){            
      
    //             $imagePresent='';
    //             $image_name=isset($aadhaarMaskingDetails['result1'])&& $aadhaarMaskingDetails['result1']!='' ?$aadhaarMaskingDetails['result1']:'';
    //             $mask_number='';
    //             $imageField=isset($aadhaarMaskingDetails['image_type'])&& $aadhaarMaskingDetails['image_type']!=''?$aadhaarMaskingDetails['image_type']:'';
    //             $side=isset($aadhaarMaskingDetails['side'])&&$aadhaarMaskingDetails['side']!=''?$aadhaarMaskingDetails['side']:'';
    //             $multiside='';
    //             $doc_id=isset($aadhaarMaskingDetails['image_type'])&&$aadhaarMaskingDetails['image_type']!=''?$aadhaarMaskingDetails['image_type']:'';
    
    //             if(isset($image_name)){
    //                 $imagePresent=true;
    //             }else{
    //                 $imagePresent=false;
    //             }
    //             $aadhaarMaskingDetails=isset($aadhaarMaskingDetails)&&$aadhaarMaskingDetails!=''?$aadhaarMaskingDetails:'';
    //         }
    //     }
     
    //     return view('archival.aadhaarmaskingdashboard')->with('aadhaarMaskingDetails', $aadhaarMaskingDetails)
    //                                                     ->with('imagePresent', $imagePresent)
    //                                                     ->with('image_name', $image_name)
    //                                                     ->with('mask_number', $mask_number)
    //                                                     ->with('imageName', $image_name)
    //                                                     ->with('imageField', $imageField)
    //                                                     ->with('side', $side)
    //                                                     ->with('multiside', $multiside)
    //                                                     ->with('doc_id',$doc_id)
    //                                                     ->with('custName',$custName);
                                                       
    // }

    public function allimagemask(Request $request){
        try{

        $aofNo=isset($request['number'])&& $request['number']!=''?$request['number']:'' ;        
        if($aofNo !=''){
        $aofNumber = $aofNo;    
        }
        else{
            $aofNumber = $request->input('aof_number'); 
        }  
        
            $role = Session::get('role');
        $type="all";                    
            
        $checkformid=DB::table('ACCOUNT_DETAILS')->select('ID',)->where('AOF_NUMBER',$aofNumber)->get()->toArray();
            $checkformid=(array)current($checkformid);
            
        
            $checkFormIdallattached=DB::table('ALL_ATTACHMENTS')->where('FORM_ID',$checkformid['id'])->get()->toArray();
           
           
            // if(empty($checkFormIdallattached ))
            // {      
               
            $imagess=CommonFunctions::checkMaskImages($aofNumber,$type,$role);          
                              
            // }

        $aadhaarDetails = DB::table('ALL_ATTACHMENTS as A1')->select('A1.ID','A1.AOF_NUMBER', 'A1.APPLICANT_SEQUENCE', 'A1.FORM_ID', 'A1.IMAGE_TYPE', 'A1.SIDE', 'A1.FLAG', 'A1.RESULT1','A1.PATH','ACCOUNT_DETAILS.NEXT_ROLE')
                                                      ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','A1.FORM_ID')
                                                      ->where('A1.RESULT1', 'not like', '%.pdf')
                                                      ->where('A1.AOF_NUMBER', $aofNumber)
                                                      ->orderBy('A1.ID', 'ASC')
                                                      ->get();

        return view('archival.allimagemask')->with('aadhaarDetails',$aadhaarDetails);
        }catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }
        }

    public function displaymaskimage(Request $request){    
       try{
        $ID = $request['id'];     
       
        $aadhaarMaskingDetails = DB::table('ALL_ATTACHMENTS')
                                // ->where(function($query) {
                                // //     $query->whereNull('FLAG')
                                //         ->orWhere('FLAG', 'N');
                                // })
                                ->where('ID', $ID)
            ->where('RESULT1', 'not like', '%.pdf')
                                ->orderBy('ID', 'ASC')
            ->get()
            ->toArray();


        if(count($aadhaarMaskingDetails)>0){
        $aadhaarMaskingDetails=(array)current($aadhaarMaskingDetails);     
        
            $custName=DB::table('CUSTOMER_OVD_DETAILS')->select('FIRST_NAME','LAST_NAME')->where('FORM_ID',$aadhaarMaskingDetails['form_id'])->get()->toArray();
        $custName=(array)current($custName);                   
            
                $imagePresent='';
                $image_name=isset($aadhaarMaskingDetails['result1'])&& $aadhaarMaskingDetails['result1']!='' ?$aadhaarMaskingDetails['result1']:'';
                $mask_number='';
                $imageField=isset($aadhaarMaskingDetails['image_type'])&& $aadhaarMaskingDetails['image_type']!=''?$aadhaarMaskingDetails['image_type']:'';
                $side=isset($aadhaarMaskingDetails['side'])&&$aadhaarMaskingDetails['side']!=''?$aadhaarMaskingDetails['side']:'';
                $multiside='';
                $doc_id=isset($aadhaarMaskingDetails['image_type'])&&$aadhaarMaskingDetails['image_type']!=''?$aadhaarMaskingDetails['image_type']:'';

                if(isset($image_name)){
                    $imagePresent=true;
            }else{
                    $imagePresent=false;
            }
                $aadhaarMaskingDetails=isset($aadhaarMaskingDetails)&&$aadhaarMaskingDetails!=''?$aadhaarMaskingDetails:'';
            
            $image= DB::table('ALL_ATTACHMENTS')
            ->select('MASK_COUNT','IMAGE_TYPE')
            ->where('ID',$aadhaarMaskingDetails['id'])        
            ->get()
            ->toArray();
            $maskimageCount=(array)current($image);
        
        
        return view('archival.aadhaarmaskingdashboard')->with('aadhaarMaskingDetails', $aadhaarMaskingDetails)
                                                        ->with('imagePresent', $imagePresent)
                                                        ->with('image_name', $image_name)
                                                        ->with('mask_number', $mask_number)
                                                        ->with('imageName', $image_name)
                                                        ->with('imageField', $imageField)
                                                        ->with('side', $side)
                                                        ->with('multiside', $multiside)
                                                        ->with('doc_id',$doc_id)
                                                        ->with('maskimageCount',$maskimageCount)
                                                        ->with('custName',$custName);
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
        $maskimageCount='';

        return view('archival.aadhaarmaskingdashboard')->with('aadhaarMaskingDetails', $aadhaarMaskingDetails)
        ->with('imagePresent', $imagePresent)
        ->with('image_name', $image_name)
        ->with('mask_number', $mask_number)
        ->with('imageName', $image_name)
        ->with('imageField', $imageField)
        ->with('side', $side)
        ->with('multiside', $multiside)
        ->with('doc_id',$doc_id)
        ->with('custName',$custName);

            }
    }catch(\Illuminate\Database\QueryException $e) {
        if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
    }
    }    

            
    public function saveAadhaarMask(Request $request)
    {
        try{
        $requestData = $request->get('data');
        // echo "<pre>";print_r($requestData);exit;
        $image = $requestData['updatedCanvasImage'];

            if($image != ''){

                $imageSize = (strlen($image)/4)*3;
                $imageMaxSize  = CommonFunctions::getapplicationSettingsDetails('upload_max_size');
                $msgString = $imageMaxSize/1024/1024;
                
                if($imageSize > $imageMaxSize){
                    return json_encode(['status'=>'fail','msg'=>'File size larger than expected ('.$msgString.'MB)','data'=>[]]);
                }
                //decrypt image
                $magicStr = substr($image, 11, 4); 
                if($magicStr == 'png;'){
                    $image = str_replace('data:image/png;base64,', '', $image);
                }elseif($magicStr == 'jpeg'){
                    $image = str_replace('data:image/jpeg;base64,', '', $image);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error detecting file format. Please retry.','data'=>[]]);
            }          
            
            if(str_contains($requestData['imageField'],'l3_update')){
                $foldername = storage_path('/uploads/markedattachments/'.$requestData['form_id'].'/'.'L3/'.$requestData['originalImage']);
                $filePathname = storage_path('/uploads/markedattachments/'.$requestData['form_id'].'/'.'L3/'.$requestData['originalImage']); 

            }else{
                $foldername = storage_path('/uploads/markedattachments/'.$requestData['form_id'].'/'.$requestData['originalImage']);
                $filePathname = storage_path('/uploads/markedattachments/'.$requestData['form_id'].'/'.$requestData['originalImage']);   
            }
            $folder = $foldername;
            // echo "<pre>";print_r($folder);exit;
                if(file_exists($folder)){
                    $imageName = $requestData['originalImage'];
                    $imageMasked = File::put($folder,base64_decode($image));
                $filePath = $filePathname;          
                }
                else{
                    return json_encode(['status'=>'fail','msg'=>'Image File Not Found','data'=>[]]);
            }          
            if($requestData['button'] == 'saveImage' ||$requestData['button'] == 'submitImage' ){

                $maskCount=DB::table('ALL_ATTACHMENTS')
                           ->select('MASK_COUNT')
                           ->where('RESULT1',$requestData['imageName'])
                           ->where('APPLICANT_SEQUENCE',$requestData['applicant_sequence'])
                           ->where('SIDE',$requestData['side'])
                           ->where('FORM_ID',$requestData['form_id'])
                           ->get()
                           ->toArray();
                $maskCount=(array)current($maskCount);
            
                $count=$maskCount['mask_count']+1;                 
            
                $aflagUpdate = DB::table('ALL_ATTACHMENTS')
                                ->where('RESULT1',$requestData['imageName'])
                                ->where('APPLICANT_SEQUENCE',$requestData['applicant_sequence'])
                                ->where('SIDE',$requestData['side'])
                                ->where('FORM_ID',$requestData['form_id'])
                                ->update(['MASK_COUNT'=>$count]);                              

            }

        }
            else{
                $filePath = '';
            }
        if($requestData['button'] == 'saveImage'){
            
                return json_encode(['status'=>'success','msg'=>'Mask updated successfully. You may continue or submit!','data'=>[]]);
        }      
       
        $image= DB::table('ALL_ATTACHMENTS')
                    ->select('MASK_COUNT')
                    ->where('RESULT1',$requestData['imageName'])
                    ->where('APPLICANT_SEQUENCE',$requestData['applicant_sequence'])
                    ->where('SIDE',$requestData['side'])
                    ->where('FORM_ID',$requestData['form_id'])
                    ->get()
                    ->toArray();
        $maskimageCount=(array)current($image); 
     
        if($maskimageCount['mask_count'] >0){             
          $image_flag='Y';                
          
            }
        else{             
            $image_flag='N';
            
        }        
            
         $aflagUpdate = DB::table('ALL_ATTACHMENTS')->where('RESULT1',$requestData['imageName'])
                                                   ->where('APPLICANT_SEQUENCE',$requestData['applicant_sequence'])
                                                   ->where('SIDE',$requestData['side'])
                                                   ->where('FORM_ID',$requestData['form_id'])
                                                   ->update(['FLAG'=>$image_flag]);
            
       

               
            $custOvddetail=DB::table('CUSTOMER_OVD_DETAILS')->select('PROOF_OF_IDENTITY','PROOF_OF_ADDRESS','ID_PROOF_CARD_NUMBER','ADD_PROOF_CARD_NUMBER','CURRENT_ADD_PROOF_CARD_NUMBER','PROOF_OF_CURRENT_ADDRESS','APPLICANT_SEQUENCE')
                                                            ->where('FORM_ID',$requestData['form_id'])                                                           
                                                            ->where('APPLICANT_SEQUENCE',$requestData['applicant_sequence'])
                                                            ->get()
                                                            ->toArray();                                                                    
                                                                                                                      
                $maskNumber=[];
                $count=0;
                foreach($custOvddetail as $custOvdDetail)
                {
                    if($custOvdDetail->proof_of_identity == 1 && $requestData['imageField'] == 'id_proof_image')
                    {                        
                        $maskNumber[$count]['id_proof_card_number'] = 'XXXX-XXXX-'.substr($custOvdDetail->id_proof_card_number,-4);
            }
                    if($custOvdDetail->proof_of_address == 1 && $requestData['imageField'] == 'add_proof_image')
                    {
                        $maskNumber[$count]['add_proof_card_number'] = 'XXXX-XXXX-'.substr($custOvdDetail->add_proof_card_number,-4);
            }
                    if($custOvdDetail->proof_of_current_address == 1 && $requestData['imageField'] == 'current_add_proof_image')
                    {
                        $maskNumber[$count]['current_add_proof_card_number'] = 'XXXX-XXXX-'.substr($custOvdDetail->current_add_proof_card_number,-4);
            }
                        $count++;
                    }
            
                    if(count($maskNumber)>0){
                        for($i=0;count($maskNumber)>$i;$i++){
                            DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$requestData['form_id'])
                                                             ->where('APPLICANT_SEQUENCE',$requestData['applicant_sequence'])                                                           
                                                             ->update($maskNumber[$i]);
            }
            

                return json_encode(['status'=>'success','msg'=>'Aadhaar Image Masked Successfully','data'=>[$requestData['aof_number']]]);
            }
 
        return json_encode(['status'=>'success','msg'=>'Image process Successfully','data'=>[$requestData['aof_number']]]);
        }catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
    }
    }
        
    public function getpendingaadhaarmask(){
       
        $aadhaarMaskingDetails =   DB::table('CUSTOMER_OVD_DETAILS')->select('ACCOUNT_DETAILS.AOF_NUMBER','CUSTOMER_OVD_DETAILS.ID', 'CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','CUSTOMER_OVD_DETAILS.ID_PROOF_CARD_NUMBER', 'CUSTOMER_OVD_DETAILS.ADD_PROOF_CARD_NUMBER')               
                                                    ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                                                    ->where('ACCOUNT_DETAILS.NEXT_ROLE',9)
                                                    ->where(function ($query)   {
                                                        $query->where('CUSTOMER_OVD_DETAILS.PROOF_OF_IDENTITY','1')
                                                             ->where(\DB::raw("substr(CUSTOMER_OVD_DETAILS.ID_PROOF_CARD_NUMBER, 0, 5)"), '<>' , 'XXXX-')
                                                             ->orWhere('CUSTOMER_OVD_DETAILS.PROOF_OF_ADDRESS','1')
                                                             ->where(\DB::raw("substr(CUSTOMER_OVD_DETAILS.ADD_PROOF_CARD_NUMBER, 0, 5)"), '<>' , 'XXXX-');
                                                        })
                                                    ->orderBy('ID','DESC')     
                                                    ->get()->toArray();

        if(count($aadhaarMaskingDetails) != 0){
            $aadhaarMaskingDetails = (array) $aadhaarMaskingDetails;                
        }
        
        return view('archival.getpendingaadhaarmask')->with('aadhaarMaskingDetails',$aadhaarMaskingDetails);

    }
}
?>