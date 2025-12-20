<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use File;
use DB;

class CreateBatchController extends Controller
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
     * Method Name: addaccount
     * Created By : Sharanya T
     * Created At : 20-02-2020
   
     * Description:
     * Method to show add account template
     *
     * Input Params:
     * @params 
     *
     * Output:
     * Returns template.
    */
    public function createbatch(Request $request)
    {
        try {
            echo "dsfgfdg";exit;
            $formId = '';
            $userDetails = array();
            $reviewDetails = array();
            //fetch account types
            $accountTypes = CommonFunctions::getAccountTypes();
            //fetch mode of operations
            $modeOfOperations = CommonFunctions::getModeOfOperations();
            //fetch residential status
            $residentialStatus = CommonFunctions::getResidentialStatus();
            //fetch scheme code
            $schemeCodes = CommonFunctions::getSchemeCodes();
            //fetch education details
            $educationList = config('constants.EDUCATION');
            //fetch gross income details
            $grossIncome = CommonFunctions::getgrossannualIncome();
            if(Session::get('is_review') == ''){
                Session::put('is_review',0);
            }
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $params = base64_decode($decodedString);
                $is_review = explode('_',$params)[0];
                $formId = explode('_',$params)[1];
                Session::put('is_review',$is_review);
                Session::put('reviewId',$formId);
            }
            if(Session::get('is_review') == 1){
                $formId = Session::get('reviewId');
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
                $userDetails['AccountDetails'] = (array) current($accountDetails);
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->get()->toArray();
                array_unshift($customerOvdDetails, "phoney");
                unset($customerOvdDetails[0]);
                $userDetails['customerOvdDetails'] = json_decode(json_encode($customerOvdDetails),true);
                // $userDetails['customerOvdDetails'] = (array) current($customerOvdDetails);
                // $profileDetails = DB::table('CUSTOMER_ADDITIONAL_DETAILS')->where('FORM_ID',$formId)
                //                                                             ->get()->toArray();
                // array_unshift($profileDetails, "phoney");
                // unset($profileDetails[0]);
                // $userDetails['ProfileDetails'] = json_decode(json_encode($profileDetails),true);
                // $userDetails['ProfileDetails'] = (array) current($profileDetails);
                $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                            ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['AccountDetails'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }
                }
            }
            $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
            /*$alphabet = array("a", "b", "c");
            array_unshift($alphabet, "phoney");
            unset($alphabet[0]);
            echo "<pre>";print_r($alphabet);exit;*/
            // echo "<pre>";print_r($userDetails);exit;
            //returns to template
            return view('bank.addaccount')->with('accountTypes',$accountTypes)
                                            ->with('modeOfOperations',$modeOfOperations)
                                            ->with('residentialStatus',$residentialStatus)
                                            ->with('schemeCodes',$schemeCodes)
                                            ->with('educationList',$educationList)
                                            ->with('grossIncome',$grossIncome)
                                            ->with('placeOfBirthList',$placeOfBirthList)
                                            ->with('citizenshipList',$citizenshipList)
                                            ->with('formId',$formId)
                                            ->with('userDetails',$userDetails)
                                            ->with('reviewDetails',$reviewDetails);
            }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addapplicant(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                //fetch residential status
                $residentialStatus = CommonFunctions::getResidentialStatus();
                //fetch education details
                $educationList = config('constants.EDUCATION');
                //fetch gross income details
                $grossIncome = CommonFunctions::getgrossannualIncome();
                $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
                //returns to template
                return view('bank.addaccountapplicant')->with('residentialStatus',$residentialStatus)
                                                ->with('educationList',$educationList)
                                                ->with('grossIncome',$grossIncome)
                                                ->with('placeOfBirthList',$placeOfBirthList)
                                                ->with('citizenshipList',$citizenshipList)
                                                ->with('customerOvdDetails',array())
                                                ->with('i',$requestData['no_of_applicants']);
                }
            }            
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: savepandetails
    *  Created By : Sharanya T
    *  Created At : 24-02-2020
    *
    *  Description:
    *  Method to save pan details in datatabse
    *
    *  Params:
    *  $AccountDetails,$PanDetails
    *
    *  Output:
    *  Returns SchemeCodes.
    */
    public function savepandetails(Request $request)
    {
        try {
            // echo "fdgdfgdfg";exit;
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),'functionName');
            // echo "<pre>";print_r($requestData);exit;
            $is_uploaded = true;
            //Begins db transaction
            DB::beginTransaction();
            //store account type in session
            Session::put('accountType', $requestData['AccountDetails']['account_type']);
            Session::put('no_of_account_holders', $requestData['AccountDetails']['no_of_account_holders']);

            // if(Session::get('formId') != ''){
            if(isset($requestData['is_update'])){
                // $formId = Session::get('formId');
                $formId = $requestData['formId'];
                // echo "dsfdsf".$formId;exit;
                // $formId = 497;
                //insert account details
                // DB::enableQueryLog();
                $update = DB::table("ACCOUNT_DETAILS")->whereId($formId)->update($requestData['AccountDetails']);
                // dd(DB::getQueryLog());
                // echo "sdfsdf".$update;exit;
            }else{
                $sequnceNumber = DB::table("ACCOUNT_DETAILS")->orderby('id','DESC')->limit(1)->pluck('aof_number')->toArray();
                $sequnceNumber = current($sequnceNumber);
                $year = substr(Carbon::now()->year, 2);
                $branchId = 100;
                if($sequnceNumber != ''){
                    $sequnceNumber = substr($sequnceNumber, 5,11);
                }else{
                    $sequnceNumber = "000000";
                }
                $requestData['AccountDetails']['AOF_NUMBER'] = $year.$branchId.$sequnceNumber + 1;
                //insert account details
                $formId = DB::table("ACCOUNT_DETAILS")->insertGetId($requestData['AccountDetails']);    
                //fetch account_form_id
                // $requestData['PanDetails']['FORM_ID'] = $formId;
                // $requestData['ProfileDetails']['FORM_ID'] = $formId;
            }
            // echo "sdfsdf";exit;
            //Array to hold user details
            $userArray = array();            
            if(isset(Session::get('UserDetails')[$formId]['AccountDetails'])){
                $userArray[$formId]['AccountDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountDetails'],$requestData['AccountDetails']);
                $userArray[$formId]['customerOvdDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['customerOvdDetails'],$requestData['PanDetails']);
                // $userArray[$formId]['ProfileDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['ProfileDetails'],$requestData['ProfileDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['AccountDetails'] = $requestData['AccountDetails'];
                $userArray[$formId]['customerOvdDetails'] = $requestData['PanDetails'];
                // $userArray[$formId]['ProfileDetails'] = $requestData['ProfileDetails'];
                $userDetails = $userArray;
            }
            //store user details into sesion based on formId
            Session::put('UserDetails',$userDetails);
            DB::setDateFormat('DD-MM-YYYY');
            if(count($requestData['PanDetails']) > 0){
                foreach ($requestData['PanDetails'] as $key => $panImage) {
                    if(isset($panImage['pf_type_image'])){
                        //define old file path(temp folder)
                        $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$panImage['pf_type_image']);
                        $folder = storage_path('/uploads/attachments/'.$formId);
                        //define new file path
                        $filePath = $folder.'/'.$panImage['pf_type_image'];
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
                }
            }
            // echo "sdfsdf";exit;
            if($is_uploaded){
                // if(Session::get('formId') != ''){
                if(isset($requestData['is_update'])){
                    $i = 1;
                    foreach ($requestData['PanDetails'] as $panDetails){
                        $applicantId = $panDetails['applicantId'];
                        $panDetails = Arr::except($panDetails,'applicantId');
                        // echo $applicantId;
                        // echo "<pre>";print_r($panDetails);
                        // DB::enableQueryLog();
                        //update pan details
                        $response = DB::table("CUSTOMER_OVD_DETAILS")->whereId($applicantId)->update($panDetails);
                        // dd(DB::getQueryLog());
                        // echo $response;
                        $accountIds[$i] = $applicantId;
                        $i++;
                    }
                    // exit;                    
                    // foreach ($requestData['ProfileDetails'] as $ProfileDetails){
                    //     $applicantId = $ProfileDetails['applicantId'];
                    //     $ProfileDetails = Arr::except($ProfileDetails,'applicantId');
                    //     //update profile info into customer additional details
                    //     $saveCustomerDetails = DB::table("CUSTOMER_ADDITIONAL_DETAILS")->where('ACCOUNT_ID',$applicantId)->update($ProfileDetails);
                        
                    // }
                }
                else{
                    $accountIds = array();
                    Session::put('formId', $formId);
                    $i = 1;
                    foreach ($requestData['PanDetails'] as $panDetails) {
                        $accountDetails = (array) $panDetails;
                        $accountDetails['FORM_ID'] = $formId;
                        if($i == 1)
                        {
                            $accountDetails['APPLICANT_SEQUENCE'] = 1;
                        }
                        $response = DB::table("CUSTOMER_OVD_DETAILS")->insertGetId($accountDetails);
                        $accountIds[$i] = $response;                        
                        $i++;
                    }
                    // $i = 1;
                    // foreach ($requestData['ProfileDetails'] as $ProfileDetails){
                    //     $ProfileDetails['FORM_ID'] = $formId;
                    //     $ProfileDetails['ACCOUNT_ID'] = $accountIds[$i];
                    //     // insert profile info into customer additional details
                    //     $saveCustomerDetails = DB::table("CUSTOMER_ADDITIONAL_DETAILS")->insert($ProfileDetails);
                    //     $i++;
                    // }
                    // $userArray[$formId]['ProfileDetails'] = $requestData['ProfileDetails'];
                    
                    // echo "<pre>";print_r(Session::get('UserDetails'));exit;
                    //insert pan details
                    // $response = DB::table("CUSTOMER_OVD_DETAILS")->insert($requestData['PanDetails']);
                    //insert profile info into customer additional details
                    // $saveCustomerDetails = DB::table("CUSTOMER_ADDITIONAL_DETAILS")->insert($requestData['ProfileDetails']);    
                }   /*echo $saveCustomerDetails;
                    echo "<pre>";print_r($requestData['PanDetails']);exit;*/
                $useArray['AccountIds'] = $accountIds;
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $useArray);
                // echo "<pre>";print_r($userDetails);exit;
                Session::put('UserDetails',$userDetails);    
                if($response){                    
                    //commit database if response is true
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Response Saved Successfully','data'=>['formId'=>$formId,'accountIds'=>$accountIds]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'File is not uploaded','data'=>[]]);
            }
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: addovddocuments
    *  Created By : Sharanya T
    *  Created At : 24-02-2020
    *
    *  Description:
    *  Method to view addovd documents template
    *
    *  Params:
    *
    *  Output:
    *  Returns template.
    */
    public function addovddocuments(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);            
                $formId = base64_decode($decodedString);    
            }

            if(Session::get('is_review') == 1){
                $formId = Session::get('reviewId');
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
                $userDetails['AccountDetails'] = (array) current($accountDetails);
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->get()->toArray();
                // $userDetails['customerOvdDetails'] = (array) current($customerOvdDetails);
                array_unshift($customerOvdDetails, "phoney");
                unset($customerOvdDetails[0]);
                $userDetails['customerOvdDetails'] = json_decode(json_encode($customerOvdDetails),true);
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                            ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
                $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
            }else{
                // echo "<pre>";print_r(Session::get('UserDetails'));exit;
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['customerOvdDetails'][1]['proof_of_identity'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }
                }
            }
            // echo "<pre>";print_r(Session::get('UserDetails'));exit;
            // echo "<pre>";print_r($userDetails);exit;
            $idProofOVDs = CommonFunctions::getOVDList('ID_PROOF');
            $addressProofOVDs = CommonFunctions::getOVDList('PER_ADDRESS_PROOF');
            $currentAddressProofOVDs = CommonFunctions::getOVDList('CUR_ADDRESS_PROOF');
            return view('bank.addovddocuments')->with('formId',$formId)
                                                ->with('idProofOVDs',$idProofOVDs)
                                                ->with('addressProofOVDs',$addressProofOVDs)
                                                ->with('currentAddressProofOVDs',$currentAddressProofOVDs)
                                                ->with('userDetails',$userDetails)
                                                ->with('reviewDetails',$reviewDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: getovdslistbytype
    *  Created By : Sharanya T
    *  Created At : 25-02-2020
    *
    *  Description:
    *  Method to get ovd's list by proof type(id,address)
    *
    *  Params:
    *  $type
    *
    *  Output:
    *  Returns Json.
    */
    public function getovdslistbytype(Request $request)
    {
        try {
            if ($request->ajax())
            {
                //fetch get details from request
                $requestData = $request->get('data');
                $idProofOVDs = CommonFunctions::getOVDList($requestData['type']);
                if(count($idProofOVDs) > 0){
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>$idProofOVDs]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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

    /*
    *  Method Name: getstates
    *  Created By : Sharanya T
    *  Created At : 25-02-2020
    *
    *  Description:
    *  Method to get states list
    *
    *  Params:
    *
    *  Output:
    *  Returns Json.
    */
    public function getstates(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                $states = CommonFunctions::getStates();
                if(count($states) > 0){
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>$states]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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


    public function getcustomertype(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                $states = CommonFunctions::getCustomertype();
                if(count($states) > 0){
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>$states]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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


    public function getcountry(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                $states = CommonFunctions::getCountry();
                if(count($states) > 0){
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>$states]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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



  public function getoccupation(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                $states = CommonFunctions::getOccupation();
                if(count($states) > 0){
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>$states]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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

    
    /*
    *  Method Name: getcitybystate
    *  Created By : Sharanya T
    *  Created At : 25-02-2020
    *
    *  Description:
    *  Method to get cities list by state
    *
    *  Params:
    *  $state
    *
    *  Output:
    *  Returns Json.
    */
    public function getcitybystate(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = $request->get('data');
                $cities = CommonFunctions::getCitiesByStateId($requestData['stateId']);
                if(count($cities) > 0){
                    return json_encode(['status'=>'success','msg'=>'Cities List','data'=>$cities]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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

    /*
    *  Method Name: saveovddetails
    *  Created By : Sharanya T
    *  Created At : 26-02-2020
    *
    *  Description:
    *  Method to save ovd details
    *
    *  Params:
    *  $requestData
    *
    *  Output:
    *  Returns Json.
    */
    public function saveovddetails(Request $request)
    {
        try {
            if ($request->ajax())
            {
                $requestData = Arr::except($request->get('data'),['functionName','formId']);
                // echo "<pre>";print_r(Session::get('UserDetails'));exit;
                // echo "<pre>";print_r($requestData);exit;
                $is_update = false;
                if(isset($requestData['is_update'])){
                    $is_update = true;
                    $requestData = Arr::except($requestData,'is_update');
                }
                $is_uploaded = true;
                $formId = $request->get('data')['formId'];  
                // echo $formId;exit;
                $i = 1;    
                if(isset($requestData['OVDS'])){                    
                    $customerPhoto = $requestData['OVDS']['customers_photograph'];
                    $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$customerPhoto);
                    $folder = storage_path('/uploads/attachments/'.$formId);
                    //define new file path
                    $filePath = $folder.'/'.$customerPhoto;
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
                    $updateCustomerPhoto = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                            ->update(['CUSTOMERS_PHOTOGRAPH'=>$customerPhoto]);
                    $customerSignatures = $requestData['OVDS'];
                    $requestData = Arr::except($requestData,'OVDS');
                }
                foreach($requestData as $ovdDetails)
                {
                    if(isset($ovdDetails['OVDS']))
                    {
                        foreach($ovdDetails['OVDS'] as $ovdType=>$ovd)
                        {
                            $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$ovd);
                            $folder = storage_path('/uploads/attachments/'.$formId);
                            //define new file path
                            $filePath = $folder.'/'.$ovd;
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
                    }
                    if($is_update){
                        $accountId = $ovdDetails['applicantId'];
                        $ovdDetails = (array) Arr:: except($ovdDetails,'applicantId');
                    }else{
                        $accountId = Session::get('UserDetails')[$formId]['AccountIds'][$i];
                    }
                    $ovdData = (array) Arr:: except($ovdDetails,'OVDS');

                    $userArray[$formId]['AccountDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['AccountDetails'],$customerSignatures);
                    $userArray[$formId]['customerOvdDetails'][$i] = array_replace_recursive(Session::get('UserDetails')[$formId]['customerOvdDetails'][$i],$ovdData);
                    $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);

                    $updateovd = DB::table("CUSTOMER_OVD_DETAILS")->whereId($accountId)->update($ovdData);
                    // $ovdData = Arr::except($ovdDetails,['OVDS']);
                    // $ovdData = (array) Arr::except($ovdDetails,['OVDS']);
                    // echo "<pre>";print_r($ovdData);
                    // $ovdData = Arr::except($ovdDetails,'OVDS');
                    $i++;
                }
                //store user financial details into sesion based on formId
                Session::put('UserDetails',$userDetails);
                // echo "<pre>";print_r(Session::get('UserDetails'));exit;
                if($updateovd){
                    return json_encode(['status'=>'success','msg'=>'OVD Details Updated Successfully','data'=>[]]);
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

    /* Method Name: addfinancialinfo
     * Created By : Ravali N
     * Created At : 25-02-2020
     * Modified By : Sharanya T
     * Modified At : 02-03-2020
     *
     * Modified: Fetch FormId and render to template
     *
     * Description:
     * Method to show addfinancialinfo template
     *
     * Input Params:
     * @params 
     *
     * Output:
     * Returns template.
    */
    public function addfinancialinfo(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);            
                $formId = base64_decode($decodedString);
            }
            if(Session::get('is_review') == 1){
                $formId = Session::get('reviewId');
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->select('initial_funding_type','initial_funding_date','amount',
                                                'reference','bank_name','ifsc_code','account_number',
                                                'account_name','relationship','cheque_image')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->get()->toArray();
                $userDetails['FinancialDetails'] = (array) current($customerOvdDetails);
                $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['FinancialDetails'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }
                }
            }
            //fetch relationship
            $relationship = config('constants.RELATIONSHIP');
            $banksList = DB::table('BANK')->pluck('bank_name','id')->toArray();
            // echo "<pre>";print_r($userDetails);exit;
            //returns to template
            return view('bank.addfinancialinfo')->with('formId',$formId)
                                                ->with('relationships',$relationship)
                                                ->with('banksList',$banksList)
                                                ->with('userDetails',$userDetails)
                                                ->with('reviewDetails',$reviewDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    /*
    *  Method Name: savefinancialinfo
    *  Created By : Ravali N
    *  Created At : 25-02-2020
    *  Modified By : Sharanya T
    *  Modified At : 02-03-2020
    *
    *  Modified: Fetched FormId and Update OVD Details Based on FormId
    *
    *  Description:
    *  Method to save financial info and profile details
    *
    *  Params:
    *  $profile details,$financial info
    *
    *  Output:
    *  saving in database.
    */
    public function savefinancialinfo(Request $request)
    {
        try {
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),['functionName','formId']);
            $formId = $request->get('data')['formId'];
            if(isset($requestData['financialDetails']['cheque_image']))
            {
                //define old file path(temp folder)
                $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$requestData['financialDetails']['cheque_image']);
                $folder = storage_path('/uploads/attachments/'.$formId);
                //define new file path
                $filePath = $folder.'/'.$requestData['financialDetails']['cheque_image'];
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
            //Begins db transaction
            DB::beginTransaction();
            if(isset(Session::get('UserDetails')[$formId]['FinancialDetails'])){
                $userArray[$formId]['FinancialDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['FinancialDetails'], $requestData['financialDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['FinancialDetails'] = $requestData['financialDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            //store user financial details into sesion based on formId
            Session::put('UserDetails',$userDetails);
            //insert financial  details into customer ovd details
            $updateFinancialDetails = DB::table("CUSTOMER_OVD_DETAILS")->where('FORM_ID',$formId)
                                                                ->update($requestData['financialDetails']);
            if($updateFinancialDetails){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Financial Details Updated Successfully','data'=>[]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addriskclassification(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            // echo Session::get('no_of_account_holders');exit;
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);            
                $formId = base64_decode($decodedString);
            }
            if(Session::get('is_review') == 1){
                $formId = Session::get('reviewId');
                $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                                   ->get()->toArray();
                // $userDetails['RiskDetails'] = (array) current($riskDetails);
                array_unshift($riskDetails, "phoney");
                unset($riskDetails[0]);
                $userDetails['RiskDetails'] = json_decode(json_encode($riskDetails),true);
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                            ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
                $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['RiskDetails'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }
                }
            }
            // echo "<pre>";print_r($userDetails);exit;
            //fetch Annual_turnover details
            $annual_turnover = config('constants.ANNUAL_TURNOVER');
            //fetch basis categorisation details
            $basis_categorisation = config('constants.BASIS_CATEGORISATION');
            // fetch basis of Source_of_funds
            $source_of_funds = config('constants.SOURCE_OF_FUNDS');
            $customerTypes = CommonFunctions::getCustomertype();
            $countries = CommonFunctions::getCountry();
            $occupation = CommonFunctions::getOccupation();
            $accountIds = Session::get('UserDetails')[$formId]['AccountIds'];
            $residenceList = CommonFunctions::getCountry();
             $citizenshipList = $placeOfBirthList = CommonFunctions::getCountry();
            $educationList = config('constants.EDUCATION');
            $grossIncome = CommonFunctions::getgrossannualIncome();
            // echo "<pre>";print_r($userDetails);exit;
            /*echo "<pre>";print_r($userDetails);
            exit;*/
            return view('bank.addriskclassification')->with('formId',$formId)
                                                    ->with('annualTurnover',$annual_turnover)
                                                    ->with('basisCategorisation',$basis_categorisation)
                                                    ->with('sourceOfFunds',$source_of_funds)
                                                    ->with('customerTypes',$customerTypes)
                                                    ->with('countries',$countries)
                                                     ->with('placeOfBirthList',$placeOfBirthList)
                                                    ->with('citizenshipList',$citizenshipList)
                                                    ->with('educationList',$educationList)
                                                    ->with('grossIncome',$grossIncome)
                                                    ->with('occupationList',$occupation)
                                                    ->with('residenceList',$residenceList)
                                                    // ->with('accountIds',$accountIds)
                                                    ->with('userDetails',$userDetails)
                                                    ->with('reviewDetails',$reviewDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function saveriskdetails(Request $request)
    {
        try{
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),'functionName');
            // echo "<pre>";print_r($requestData);exit;
            $formId = $request->get('data')['formId'];
            // $requestData['riskclassificationDetails']['FORM_ID'] = $formId;
            // echo "<pre>";print_r($requestData['riskclassificationDetails']);exit;
            //Begins db transaction
            DB::beginTransaction();
            // echo "<pre>";print_r(Session::get('UserDetails'));exit;
            //build user Risk and Nominee deatils array with formId
            // if(isset($requestData['is_update'])){
            if((Session::get('is_review') == 1) || (isset(Session::get('UserDetails')[$formId]['RiskDetails']))){
                //insert risk classification details into risk_classification_details table
                // $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->where('FORM_ID',$formId)->update($requestData['riskclassificationDetails']);
                if(count($requestData['riskclassificationDetails']) > 0)
                {
                    foreach ($requestData['riskclassificationDetails'] as $riskclassificationDetails)
                    {
                        $applicantId = $riskclassificationDetails['applicantId'];
                        $riskclassificationDetails = (array) Arr::except($riskclassificationDetails,'applicantId');
                        $riskclassificationDetails['FORM_ID'] = $formId;
                        //update risk classification details into risk_classification_details table
                        $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->where('ACCOUNT_ID',$applicantId)->update($riskclassificationDetails);
                    }
                }
                /*$userArray[$formId]['RiskDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['RiskDetails'], $requestData['riskclassificationDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);*/
            }else{
                if(count($requestData['riskclassificationDetails']) > 0)
                {
                    $i = 1;
                    foreach ($requestData['riskclassificationDetails'] as $riskclassificationDetails)
                    {
                        $riskclassificationDetails = (array) $riskclassificationDetails;
                        $riskclassificationDetails['FORM_ID'] = $formId;
                        $riskclassificationDetails['ACCOUNT_ID'] = Session::get('UserDetails')[$formId]['AccountIds'][$i];
                        // echo "<pre>";print_r($riskclassificationDetails);exit;
                        //insert risk classification details into risk_classification_details table
                        $saveRiskDetails = DB::table("RISK_CLASSIFICATION_DETAILS")->insert($riskclassificationDetails);
                        $i++;
                    }
                }
                /*$userArray[$formId]['RiskDetails'] = $requestData['riskclassificationDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);*/
            }
            if(isset(Session::get('UserDetails')[$formId]['RiskDetails'])){
                $userArray[$formId]['RiskDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['RiskDetails'], $requestData['riskclassificationDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                $userArray[$formId]['RiskDetails'] = $requestData['riskclassificationDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            // echo "<pre>";print_r($userDetails);exit;
            Session::put('UserDetails',$userDetails);
            if($saveRiskDetails){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Risk Classification Details Updated Successfully','data'=>[]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }            
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addnomineedetails(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);            
                $formId = base64_decode($decodedString);
            }
            if(Session::get('is_review') == 1){
                $formId = Session::get('reviewId');
                $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)
                                                                        ->get()->toArray();
                $userDetails['NomineeDetails'] = (array) current($nomineeDetails);
                $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['NomineeDetails'])){
                        $userDetails = Session::get('UserDetails')[$formId];

                    }
                }
            }
            return view('bank.addnomineedetails')->with('formId',$formId)
                                                    ->with('userDetails',$userDetails)
                                                    ->with('reviewDetails',$reviewDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function savenomineedetails(Request $request)
    {
        try{
            //fetch get details from request
            $requestData = Arr::except($request->get('data'),'functionName');
            $is_uploaded = true;
            $formId = $request->get('data')['formId'];
            $requestData['nomineeDetails']['FORM_ID'] = $formId;
            if(isset($requestData['witnessSignatures']))
            {
                foreach($requestData['witnessSignatures'] as $signature)
                {
                    //define old file path(temp folder)
                    $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$signature);
                    $folder = storage_path('/uploads/attachments/'.$formId);
                    //define new file path
                    $filePath = $folder.'/'.$signature;
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
            }
            //Begins db transaction
            DB::beginTransaction();
            //build user Risk and Nominee deatils array with formId
            if(isset(Session::get('UserDetails')[$formId]['NomineeDetails'])){
                //insert risk classification details into risk_classification_details table
                $saveRiskDetails = DB::table("NOMINEE_DETAILS")->where('FORM_ID',$formId)->update($requestData['nomineeDetails']);
                $userArray[$formId]['NomineeDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['NomineeDetails'], $requestData['nomineeDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }else{
                //insert risk classification details into risk_classification_details table
                $saveRiskDetails = DB::table("NOMINEE_DETAILS")->insert($requestData['nomineeDetails']);
                $userArray[$formId]['NomineeDetails'] = $requestData['nomineeDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            Session::put('UserDetails',$userDetails);   
            if($saveRiskDetails){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Nomination Details Updated Successfully','data'=>[]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }            
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    *  Method Name: declaration
    *  Created By : Ravali N
    *  Created At : 28-02-2020
    *
    *  Description:
    *  Method to show declararion template
    *
    *  Params:
    *  @params 
    *
    *  Output:
    *  Returns template.
    */
    public function declaration(Request $request)
    {
        try{
            $userDetails = array();
            $reviewDetails = array();
            if(!empty($request->all())){
                $tokenParams = explode('.',Cookie::get('token'));
                //decode string
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $formId = base64_decode($decodedString);    
            }
            if(Session::get('is_review') == 1){
                $formId = Session::get('reviewId');
                $declarationDetails = DB::table('ACCOUNT_DETAILS')
                                            ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
                $userDetails['Declarations'] = (array) current($declarationDetails);
                $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
            }else{
                if(Session::get('formId') != ''){
                    $formId = Session::get('formId');
                    if(!empty(Session::get('UserDetails')[$formId]['Declarations'])){
                        $userDetails = Session::get('UserDetails')[$formId];
                    }
                }
            }
            // echo "<pre>";print_r($userDetails);exit;
            $accountType = Session::get('accountType');
            $declarationsList = DB::table("DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)->get()->toArray();
            return view('bank.declaration')->with('formId',$formId)
                                            ->with('declarationsList',$declarationsList)
                                            ->with('userDetails',$userDetails)
                                            ->with('reviewDetails',$reviewDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function applydigisign(Request $request)
    {
        try{
            $requestData = $request->get('data');
            // echo "<pre>";print_r($request->all());
            // echo "<pre>";print_r($requestData);exit;
            $formId = $requestData['formId'];
            // $declarations = array_merge($requestData['Declarations'],$requestData['Proofs']);
            // echo "<pre>";print_r($declarations);exit;
            //Begins db transaction
            DB::beginTransaction();
            if(isset($requestData['Declarations'])){
                foreach($requestData['Declarations'] as $declaration=>$value)
                {
                    $updateDeclaration = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                        ->update([$declaration=>$value]);
                }
            }

            if(isset($requestData['Proofs'])){
                foreach($requestData['Proofs'] as $proofType=>$proof)
                {
                    $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$proof);
                    $folder = storage_path('/uploads/attachments/'.$formId);
                    //define new file path
                    $filePath = $folder.'/'.$proof;
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
                    $updateProof = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update([$proofType=>$proof]);
                }
            }            
            $folder = storage_path('/uploads/attachments/'.$formId);
            $filesInFolder = \File::files($folder);
            foreach($filesInFolder as $path) { 
                $file = pathinfo($path);
                $filename = $file['filename'].'.'.$file['extension'];
                $markImage = CommonFunctions::markImage($formId,$filename);
            }
            /*$declarations = implode(',',$requestData['Declarations']);
            
            $response = DB::table("ACCOUNT_DETAILS")->whereId($formId)
                                                    ->update(['DECLARATIONS'=>$declarations]);*/
            $declarations = array_merge($requestData['Declarations'],$requestData['Proofs']);
            // echo "<pre>";print_r(Session::get('UserDetails'));exit;
            if(isset(Session::get('UserDetails')[$formId]['Declarations'])){
                /*$userArray[$formId]['NomineeDetails'] = array_replace_recursive(Session::get('UserDetails')[$formId]['NomineeDetails'], $requestData['nomineeDetails']);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);*/
                $userArray[$formId]['Declarations'] = array_replace_recursive(Session::get('UserDetails')[$formId]['Declarations'], $declarations);
                $userDetails[$formId] = array_merge(Session::get('UserDetails')[$formId], $userArray[$formId]);
                // $userDetails[$formId] = $userArray[$formId];
            }else{
                /*$userArray[$formId]['NomineeDetails'] = $requestData['nomineeDetails'];
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);*/
                $userArray[$formId]['Declarations'] = $declarations;
                $userDetails[$formId] = array_merge_recursive(Session::get('UserDetails')[$formId], $userArray[$formId]);
            }
            //store user financial details into sesion based on formId
            // echo "<pre>";print_r($userDetails);exit;
            Session::put('UserDetails',$userDetails);
            if($updateProof){
                //commit database if response is true
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Account Details Updated Successfully','data'=>[]]);
            }else{
                //rollback db transactions if any error occurs in query
                DB::rollback();
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function submission(Request $request)
    {
        try{
             $userDetails = array();
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $formId = base64_decode($decodedString);
            $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->select('ACCOUNT_DETAILS.*','ACCOUNT_TYPES.ACCOUNT_TYPE','MODE_OF_OPERATIONS.OPERATION_TYPE
                                                         as MODE_OF_OPERATION','SCHEME_CODES.SCHEME as SCHEME_CODE')
                                    ->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE')
                                    ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                    ->leftjoin('SCHEME_CODES','SCHEME_CODES.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
            $accountDetails = (array) current($accountDetails);
            // echo "<pre>";print_r($accountDetails);exit;
            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->select('CUSTOMER_OVD_DETAILS.*','RESIDENTIAL_STATUS.RESIDENTIAL_STATUS',
                                            'OVD_TYPES.OVD as PROOF_OF_IDENTITY','A.OVD as PROOF_OF_ADDRESS','CA.OVD as PROOF_OF_CURRENT_ADDRESS',
                                            'BANK.BANK_NAME as BANK_NAME')
                                        ->leftjoin('RESIDENTIAL_STATUS','RESIDENTIAL_STATUS.ID','CUSTOMER_OVD_DETAILS.RESIDENTIAL_STATUS')
                                        ->leftjoin('OVD_TYPES','OVD_TYPES.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_IDENTITY')
                                        ->leftjoin('OVD_TYPES as A','A.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_ADDRESS')
                                        ->leftjoin('OVD_TYPES as CA','CA.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_CURRENT_ADDRESS')
                                        ->leftjoin('BANK','CUSTOMER_OVD_DETAILS.BANK_NAME','BANK.ID')
                                        ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                        ->get()->toArray();
            // echo "<pre>";print_r($customerOvdDetails);exit;
            // $customerOvdDetails = (array) current($customerOvdDetails);
            // $profileDetails = DB::table('CUSTOMER_ADDITIONAL_DETAILS')->where('FORM_ID',$formId)
            //                                                             ->get()->toArray();
            // $profileDetails = (array) current($profileDetails);
            // echo "<pre>";print_r($profileDetails);exit;
            $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')
                                            ->select('RISK_CLASSIFICATION_DETAILS.*','CUSTOMER_TYPE.DESCRIPTION as CUSTOMER_TYPE',
                                                'COUNTRIES.NAME as COUNTRY_NAME','OCCUPATION.DESCRIPTION as OCCUPATION')
                                            ->leftjoin('CUSTOMER_TYPE','CUSTOMER_TYPE.ID','RISK_CLASSIFICATION_DETAILS.CUSTOMER_TYPE')
                                            ->leftjoin('COUNTRIES','COUNTRIES.ID','RISK_CLASSIFICATION_DETAILS.COUNTRY_NAME')
                                            ->leftjoin('OCCUPATION','OCCUPATION.ID','RISK_CLASSIFICATION_DETAILS.OCCUPATION')
                                            ->where('FORM_ID',$formId)
                                            ->distinct()
                                            ->get()->toArray();
            // echo "<pre>";print_r($riskDetails);exit;
            // $riskDetails = (array) current($riskDetails);
            $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)
                                                                    ->get()->toArray();
            $nomineeDetails = (array) current($nomineeDetails);


             $accountType = Session::get('accountType');
             $declarationsList = DB::table("DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)->get()->toArray();
            // echo "<pre>";print_r($riskDetails);exit;
            return view('bank.submission')->with('formId',$formId)
                                            ->with('accountDetails',$accountDetails)
                                            ->with('customerOvdDetails',$customerOvdDetails)
                                            // ->with('profileDetails',$profileDetails)
                                            ->with('riskDetails',$riskDetails)
                                            ->with('userDetails',$userDetails)
                                            ->with('declarationsList',$declarationsList)
                                            ->with('nomineeDetails',$nomineeDetails);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    

    /*
    *  Method Name: deleteimage
    *  Created By : Sharanya T
    *  Created At : 12-03-2020
    *
    *  Description:
    *  Method to delete iamge file in folder
    *
    *  Params:
    *  @$filename
    *
    *  Output:
    *  Returns Json.
    */
    public function deleteimage(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                // echo "<pre>";print_r($requestData);exit;
                $filename = strage_path().'/uploads/temp/'.$requestData['imageName'];
                if(!file_exists($filename))
                {
                    $formId = Session::get('formId');
                    $filename = storage_path().'/uploads/attachments/'.$formId.'/'.$requestData['imageName'];
                    if(!file_exists($filename)){
                        $formId = Session::get('reviewId');
                        $filename = storage_path().'/uploads/markedattachments/'.$formId.'/'.$requestData['imageName'];
                    }
                }
                $delete = \File::delete($filename);
                if($delete){
                    return json_encode(['status'=>'success','msg'=>'Image Deleted Successfully','data'=>[]]);
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

    public function getaddressdatabypincode(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $addressData = CommonFunctions::getAddressDataByPincode($requestData['pincode']);
                if(count($addressData) > 0){
                    $addressData = (array) current($addressData);
                    return json_encode(['status'=>'success','msg'=>'AddressData Details','data'=>$addressData]);
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

    public function submittonpc(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $formId = $requestData['formId'];
                $declarations = implode(',',$requestData['Declarations']);
               $updateStatus = DB::table('ACCOUNT_DETAILS')->whereId($requestData['formId'])
                        ->update(['DECLARATIONS'=>$declarations,'APPLICATION_STATUS'=>2]);
                if($updateStatus){
                    Session::forget('UserDetails');
                    Session::forget('formId');
                    if(Session::get('is_review') == 1){
                        Session::put('is_review',0);
                    }
                    return json_encode(['status'=>'success','msg'=>'Form Submitted to NPC','data'=>[]]);
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

}
?>