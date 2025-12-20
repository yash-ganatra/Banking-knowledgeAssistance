<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Illuminate\Support\Arr;
use File;
use Session;
use Cookie;
use Crypt;
use Carbon\Carbon;

class SavingsController extends Controller
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

            if(!in_array($this->roleId,[2,8,3,4,11,22])){ // Allowing for Branch, NPC_L1 , NPC_L2, L3

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Bank/SavingsController','SavingsController','Unauthorized attempt detected by '.$this->userId,'','','1');

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
    
    /*  Method Name: fileupload
    *  Created By : Sharanya T
    *  Created At : 17-02-2020
    *
    *  Description:
    *  Method to upload files
    *
    *  Params:
    *  @$image,@$imageName
    *
    *  Output:
    *  Returns Json.
    */
    public function fileupload(Request $request)
    {
        try {
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $is_uploaded = true;
                //fetch image which is in base64 encoded format
                $image = $requestData['image'];
                
                
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
					$image = str_replace(' ', '+', $image);					
					$imageName = Str::random(10).'.'.'png';
				}
				if($magicStr == 'jpeg'){
					$image = str_replace('data:image/jpeg;base64,', '', $image);
					$image = str_replace(' ', '+', $image);					
					$imageName = Str::random(10).'.'.'jpg';
				}else{
					return json_encode(['status'=>'fail','msg'=>'Error detecting file format. Please retry.','data'=>[]]);
				}
				
                //$folder = public_path('\temp');
                $folder = storage_path('uploads/temp');
                /*if (!File::exists($folder)){
                    echo "test1234";//exit;
                }else{
                    echo "test";//exit;
                }*/
                // echo $folder;exit;
                if (!File::exists($folder)) {
                    File::makeDirectory($folder, 0775, true, true);
                    /*$location = storage_path('/app/posts/' . Auth::id() . '/' . $post->id . '/' . $filename);
                    Image::make($image)->resize(800,400)->save($location); //resizing and saving the image*/
                }
                //upload image to directory
                //if(\File::put('public/uploads/temp/' . $imageName, base64_decode($image))){
                if(\File::put($folder.'/'.$imageName, base64_decode($image))){
                    
                }else{
                    //make is_uploaded is false if file not uploaded
                    $is_uploaded = false;
                }

                if(substr($requestData['image_type'], -4) == '_img')
                {
                    $clearance_details = DB::table('CLEARANCE_MASTER')->where('BLADE_ID', substr($requestData['image_type'], 0, -4))
                                                                      ->get()->toArray();
                    $clearance_details = (array) current($clearance_details);
                   $update_clearance =  DB::table('CLEARANCE')
                                          ->where('FORM_ID',$requestData['form_id'])
                                          ->where('CLEARANCE_ID', $clearance_details['id'])
                                          ->get()->toArray();

                    if(count($update_clearance) == 1){
                         $update_clearance =  DB::table('CLEARANCE')
                                          ->where('FORM_ID',$requestData['form_id'])
                                          ->where('CLEARANCE_ID', $clearance_details['id'])
                                          ->update(['CLEARANCE_IMG' => $imageName,
                                      				"UPDATED_BY"=> Session::get('userId'),
                          							"UPDATED_AT"=> Carbon::now()]);

                        $folder = storage_path('/uploads/markedattachments/'.$requestData['form_id']);
                        /*if (!File::exists($folder)){
                            echo "test1234";//exit;
                        }else{
                            echo "test";//exit;
                        }*/
                        // echo $folder;exit;
                        if (!File::exists($folder)) {
                            File::makeDirectory($folder, 0775, true, true);
                            /*$location = storage_path('/app/posts/' . Auth::id() . '/' . $post->id . '/' . $filename);
                            Image::make($image)->resize(800,400)->save($location); //resizing and saving the image*/
                        }
                        //upload image to directory
                        //if(\File::put('public/uploads/temp/' . $imageName, base64_decode($image))){
                        if(\File::put($folder.'/'.$imageName, base64_decode($image))){
                            
                        }else{
                            //make is_uploaded is false if file not uploaded
                            $is_uploaded = false;
                        }
                    }
                }

            
                $imageCall  =  CommonFunctions::getapplicationSettingsDetails('IMAGE_ANALYSIS');
             
                $response = array();
                if($imageCall == 'WARNING' && $imageCall == 'STRICT'){
                    $imageHost = storage_path(config('constants.APPLICATION_SETTINGS.IMAGE_HOST'));
                    try{                            

                            $filepath=$folder.'/'.$imageName;
                            // $client = new \GuzzleHttp\Client();
                            // $guzzleClient = $client->request('POST','http://165.232.188.115:8811'.'/pathimageinfo',['json'=>['filepath'=>$filepath]]);
                            // $response = json_decode($guzzleClient->getBody(),true);
                            $url  = 'http://165.232.188.115:8811/imageinfo';
                            /*$client = new \GuzzleHttp\Client();
                            $requestTime = Carbon::now();
                            $guzzleClient = $client->request('POST',$url,
                                                                [ 
                                                                    'headers'  => [
                                                                        'Content-Type' => 'image/jpeg'
                                                                    ],
                                                                    'multipart' => [
                                                                        [
                                                                            'name'     => 'file', // API parameter name for the file
                                                                            'contents' => fopen($filepath, 'r'), // Open the file for reading
                                                                            'filename' => $imageName, // Original file name
                                                                        ],
                                                                    ],
                                            
                                                                ]);
                                    
                            $responseTime = Carbon::now()->diffInSeconds($requestTime); 
                            $response = json_encode(json_decode($guzzleClient->getBody()));
                            */
                            // echo "<pre>";print_r($response);exit;
                             $ch = curl_init($imageHost.'/imageinfo');

                             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                             curl_setopt($ch, CURLOPT_AUTOREFERER, true);

                             $cfile = new \CURLFile($filepath, mime_content_type($filepath), basename($filepath));
                             $post_data = array('file' => $cfile);
                             curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                             $response = curl_exec($ch);

                             if (curl_errno($ch)) {
                                $response = curl_error($ch); 
                             }
                            
                            // Close cURL session
                             curl_close($ch);
                            
                            $response = json_decode($response);
                      
                        }catch(\Throwable $e){
                            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
                        }catch(\Guzzle\Http\Exception\ConnectException $e){
                            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
                        }
                }    
                //checks file uploaded successfully or not
                if($is_uploaded){
                    return json_encode(['status'=>'success','msg'=>'Image Uploaded Successfully','imageName'=>$imageName, 'info'=>$response,'imageCall'=>$imageCall]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','imageName'=>$imageName,'data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
    }

    public function level3update(Request $request)
    { 
        try {
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $is_uploaded = false;				
				$formId = $requestData['formId'];
				$updateType = $requestData['updateType'];
				$note = $requestData['note'];				
                $imageName = $requestData['image'];
				$declarationType = 'l3_update_'.$requestData['serial'];
                
				if($updateType == 'image' && $imageName != ''){  
					$tempFile = storage_path('/uploads/temp').'/'.$imageName;
					if (!File::exists($tempFile)){
						return json_encode(['status'=>'fail','msg'=>'Error while accessing L3 update image','data'=>[]]);						
					}else{
						$level3Folder = storage_path('/uploads/markedattachments').'/'.$formId.'/L3';
						if(!File::exists($level3Folder)){												
							File::makeDirectory($level3Folder, 0775, true, true);														
						}						
						File::move($tempFile, $level3Folder.'/'.$imageName);																		
						$is_uploaded = true; 
					}																
				}else{
                    $is_uploaded = true;
                }

                if($is_uploaded){					
					$status = CommonFunctions::saveLevel3Updates($formId, $declarationType, $note, $imageName);
                    return json_encode(['status'=>'success','msg'=>'L3 Comments/Image updated successfully','note'=>$note, 'imageName'=>$imageName]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
    }


   public function linkupload(Request $request)
   {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
				$srcFilename = $requestData['srcfile'];
				$src = storage_path('uploads/temp').'/'.$srcFilename;
				$destFilename = Str::random(10).'.png';				
				$dest = storage_path('uploads/temp').'/'.$destFilename;
                
                if (File::exists($src)){
					\File::copy($src, $dest);
					$is_uploaded = true;
                }else{
					$is_uploaded = false;
				}
                if($is_uploaded){
                    return json_encode(['status'=>'success','msg'=>'Image Uploaded Successfully','imageName'=>$destFilename]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error copying link file!','data'=>[$srcFilename, $destFilename]]); 
                }
            }else{
                    return json_encode(['status'=>'fail','msg'=>'Error copying link file. Invalid request!','data'=>[]]);
            }
        }  
        catch(\Illuminate\Database\QueryException $e) {
            //if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
			dd($e->getMessage());  
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
    }
	
    /*
    *  Method Name: deletefile
    *  Created By : Sharanya T
    *  Created At : 17-02-2020
    *
    *  Description:
    *  Method to delete file in folder
    *
    *  Params:
    *  @$filename
    *
    *  Output:
    *  Returns Json.
    */
    public function deletefile(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                // echo "<pre>";print_r($requestData);exit;
                $filename = public_path().'/uploads/idM9mbepQl.png';
                \File::delete($filename);
            }

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
    }

     //---------------------Amend required documt upload pdf------------------\\

    public function uplodpdfdocument(Request $request){

        if($request->ajax()){
            if ($request->hasFile('pdf_file')) {
                $crfNumber = $request->get('crfdata');
                $moduleData = $request->get('module');
                $documentId = $request->get('documentId');
                $setnewPdfName = $moduleData.'_'.$documentId.'_'.'CRF_'.$crfNumber.'_'.time().'.pdf';
                if($moduleData == 'cube'){
                    $random = CommonFunctions::getRandomValue(9);

                    $setnewPdfName = $random.'.pdf';
                }
                $destinationPath = storage_path('uploads/temp');            
                $request->file('pdf_file')->move($destinationPath, $setnewPdfName);
    
                return json_encode(['status'=>'success','msg'=>'Pdf Uploaded Successfully','data'=>$setnewPdfName,'docId'=>$documentId,'module'=>$moduleData]);
    
            }else{
                // echo $request->file('crffile');exit;
                return json_encode(['status'=>'fail','msg'=>'Pdf Not Uploaded','data'=>[]]);
            }


        }
    }

}
?>