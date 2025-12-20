<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Helpers\CommonFunctions;
use App\Helpers\DelightFunctions;
use App\Helpers\Rules;
use App\Helpers\Api;
use App\Helpers\CurrentApi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use File;
use DB;

class EntityAccountController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Bank/AddAccountController','AddAccountController','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }

        }
        ini_set('max_execution_time', '130');
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

    public static function saveEntityAccount($requestData,$formId){    	
		$entityDetails = $requestData['Entity'];
        if($entityDetails['proof_of_entity_address'] == 5){
            if(!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{2}[0-9A-Z]{1}/', $entityDetails['entity_add_proof_card_number'])){
                return json_encode(['status'=>'fail','msg'=>'Please enter a valid Entity Proof Card Number','data'=>[]]); 
            }
        }

        foreach ($entityDetails as $key => $value) {  
            if($key == 'entity_landmark'){
                unset($entityDetails[$key]);
                continue;
            }
            if($value == ''){
                $keystr = strtoupper(str_replace('_',' ',$key));
                return json_encode(['status'=>'fail','msg'=>'Entity data not complete! Please recheck '.$keystr,'data'=>[]]); 
            }

        }


        //Email validation
        $get_domainName = explode('@',$requestData['Entity']['entity_email_id']);
      
        if($get_domainName[0] != '' && $get_domainName[1] != ''){


                $email_check = DB::table('RESTRICTED_DOMAIN_ID')->select('DOMAIN_ID')
                    ->whereRaw('LOWER(DOMAIN_ID) = ?',[strtolower(trim($get_domainName[1]))])->get()->toArray();
                if(count($email_check)>0){
                // echo "<pre>";print_r(count($email_check));
                    return json_encode(['status'=>'fail','msg'=>'Email domain not permitted. Please enter valid domain account','data'=>[]]);
                }
        }else{
                return json_encode(['status'=>'fail','msg'=>'Incorrect email ID','data'=>[]]);     
        }
		
        //entity_name  special case validation 
        $entity_match = preg_match('/[^a-zA-Z0-9!@#\$\&\)\(‘.-]+$/',$entityDetails['entity_name']);
        if($entity_match == '1'){
            return json_encode(['status'=>'fail','msg'=>'Invalid special character detected! Please enter valid name','data'=>[]]);
        }

    	// echo "<pre>";print_r($formId);exit;
        if (strlen($entityDetails['entity_pincode']) < 6){
            return json_encode(['status'=>'fail','msg'=>'Pincode validation failed for Entity Details','data'=>[]]);
        }

        // if(strLen($entityDetails['entity_landmark']) > 20)
        // {
        //     return json_encode(['status'=>'fail','msg'=>'Landmark Only 20 Characters allow','data'=>[]]);
        // }


        $entityfieldtoValidate = ['entity_name','entity_address_line1','entity_address_line2'];
        $entityvalidationFailedField = CommonFunctions::apastropheValidations($entityDetails,$entityfieldtoValidate);

        if (isset($entityvalidationFailedField) && $entityvalidationFailedField != ''){
            return json_encode(['status'=>'fail','msg'=>'field validation failed ( '.$entityvalidationFailedField.' ).','data'=>[]]);
        }

        if(strlen($entityDetails['entity_address_line1']) > 45 || strlen($entityDetails['entity_address_line2']) > 45){
            return json_encode(['status'=>'fail','msg'=>'Address is more than 45 characters','data'=>[]]);
        }
                        // echo "<pre>";print_r($entityDetails)
        $image = $entityDetails['entity_add_proof_image'];
        if(isset($entityDetails['entity_add_proof_back_image']) && $entityDetails['entity_add_proof_back_image'] != ''){
            $backimage = $entityDetails['entity_add_proof_back_image'];
        }else{
            $backimage = '';
        }

        // $backimage = $entityDetails['entity_add_proof_back_image'];
        $files = array($image,$backimage);
        foreach ($files as $key => $file) {
            if($file == ''){
                continue;
            }
	    $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$file);
	    //$folder = public_path('/uploads/attachments/'.$formId);
	    // $folder = 'storage/uploads/attachments/'.$formId; commented during version upgrade
        $folder = storage_path('/uploads/attachments/'.$formId);
	    //define new file path
	     $filePath = $folder.'/'.$file;
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
	}
		$updateEntity = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->update($entityDetails);
	}
}
