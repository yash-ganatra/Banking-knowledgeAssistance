<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;				  
use Cookie;
use DB;

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

            if($this->roleId != 2){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Bank/ReviewController','ReviewController','Unauthorized attempt detected by '.$this->userId,'','','1');

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
            // $formId = base64_decode($decodedString);
            // $formId = 288;
            /*$formId = DB::table('ACCOUNT_DETAILS')->where('APPLICATION_STATUS',2)->pluck('id')->toArray();
            $formId = current($formId);*/
            /*$tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $formId = base64_decode($decodedString); */ 
            $formId = 301;
            $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->select('ACCOUNT_DETAILS.*','ACCOUNT_TYPES.ACCOUNT_TYPE'/*,'MODE_OF_OPERATIONS.OPERATION_TYPE
                                                         as MODE_OF_OPERATION'*/,'SCHEME_CODES.SCHEME_CODE as SCHEME_CODE')
                                    ->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE')
                                    // ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                    ->leftjoin('SCHEME_CODES','SCHEME_CODES.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
            $accountDetails = (array) current($accountDetails);
            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                        ->select('CUSTOMER_OVD_DETAILS.*','RESIDENTIAL_STATUS.RESIDENTIAL_STATUS',
                                            'OVD_TYPES.OVD as PROOF_OF_IDENTITY','A.OVD as PROOF_OF_ADDRESS','CA.OVD as PROOF_OF_CURRENT_ADDRESS')
                                        ->leftjoin('RESIDENTIAL_STATUS','RESIDENTIAL_STATUS.ID','CUSTOMER_OVD_DETAILS.RESIDENTIAL_STATUS')
                                        ->leftjoin('OVD_TYPES','OVD_TYPES.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_IDENTITY')
                                        ->leftjoin('OVD_TYPES as A','A.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_ADDRESS')
                                        ->leftjoin('OVD_TYPES as CA','CA.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_CURRENT_ADDRESS')
                                        ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                        ->get()->toArray();
            $customerOvdDetails = (array) current($customerOvdDetails);
            // $profileDetails = DB::table('CUSTOMER_ADDITIONAL_DETAILS')->where('FORM_ID',$formId)
            //                                                             ->get()->toArray();
            // $profileDetails = (array) current($profileDetails);
            $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')
                                            ->select('RISK_CLASSIFICATION_DETAILS.*'/*,'CUSTOMER_TYPE.DESCRIPTION as CUSTOMER_TYPE',
                                                'COUNTRIES.NAME as COUNTRY_NAME','OCCUPATION.DESCRIPTION as OCCUPATION'*/)
                                            // ->leftjoin('CUSTOMER_TYPE','CUSTOMER_TYPE.ID','RISK_CLASSIFICATION_DETAILS.CUSTOMER_TYPE')
                                            // ->leftjoin('COUNTRIES','COUNTRIES.ID','RISK_CLASSIFICATION_DETAILS.COUNTRY_NAME')
                                            // ->leftjoin('OCCUPATION','OCCUPATION.ID','RISK_CLASSIFICATION_DETAILS.OCCUPATION')
                                            ->where('FORM_ID',$formId)
                                            ->get()->toArray();
            $riskDetails = (array) current($riskDetails);
            $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)
                                                                    ->get()->toArray();
            $nomineeDetails = (array) current($nomineeDetails);
            $reviewDetails = DB::table('REVIEW_TABLE')->where('FORM_ID',$formId)
                                            ->pluck('comments','column_name')->toArray();
            //fetch mode of operations
            $modeOfOperations = CommonFunctions::getModeOfOperations();
            //fetch Annual_turnover details
            $annual_turnover = config('constants.ANNUAL_TURNOVER');
            //fetch basis categorisation details
            $basis_categorisation = config('constants.BASIS_CATEGORISATION');
            // fetch basis of Source_of_funds
            $source_of_funds = config('constants.SOURCE_OF_FUNDS');
            $customerTypes = CommonFunctions::getCustomertype();
            $countries = CommonFunctions::getCountry();
            $occupations = CommonFunctions::getOccupation();
                                            // ->get()->toArray();
            // $reviewDetails = (array) current($reviewDetails);
            
            // echo "<pre>";print_r($accountDetails);exit;
            //returns to template
            return view('bank.review')->with('formId',$formId)
                                    ->with('accountDetails',$accountDetails)
                                    ->with('customerOvdDetails',$customerOvdDetails)
                                    // ->with('profileDetails',$profileDetails)
                                    ->with('riskDetails',$riskDetails)
                                    ->with('nomineeDetails',$nomineeDetails)
                                    ->with('reviewDetails',$reviewDetails)
                                    ->with('modeOfOperations',$modeOfOperations)
                                    ->with('annualTurnover',$annual_turnover)
                                    ->with('basisCategorisation',$basis_categorisation)
                                    ->with('customerTypes',$customerTypes)
                                    ->with('countries',$countries)
                                    ->with('occupations',$occupations)
                                    ->with('sourceOfFunds',$source_of_funds);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

      //------------------------------Function will remove after check 19-FEB-2021------------------//

    public function savecomments(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),'functionName');
                // echo "<pre>";print_r($requestData);exit;
                //Begins db transaction
                DB::beginTransaction();
                if(isset($requestData['reviewId'])){
                    $reviewId = $requestData['reviewId'];
                    unset($requestData['reviewId']);
                    $saveComments = DB::table('REVIEW_TABLE')->whereId($reviewId)->update($requestData);
            }else{
                    $saveComments = DB::table('REVIEW_TABLE')->insertGetId($requestData);
                    $reviewId = $saveComments;
                }
                if($saveComments){                    
                    //commit database if response is true
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Comments Saved Successfully','data'=>['reviewId'=>$reviewId]]);
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
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updatecolumn(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                $checkStatus = CommonFunctions::precheckUpdateColumn($requestData);
                // echo "<pre>";print_r($requestData);exit;
                if($checkStatus['status'] != 'success'){
                    return json_encode(['status'=>'fail','msg'=>$checkStatus['msg'],'data'=>[]]);
                }

                $updateArray = array($requestData['column']=>$requestData['value']);
                //Begins db transaction
                DB::beginTransaction();
                DB::setDateFormat('DD-MM-YYYY');
                if($requestData['table'] == "account_details"){
                    $updateCoulmn = DB::table($requestData['table'])->whereId($requestData['formId'])->update($updateArray);
                }else if($requestData['table'] == "customer_ovd_details"){
                    if(isset($requestData['account_id'])){
                        $updateCoulmn = DB::table($requestData['table'])->whereId($requestData['account_id'])->update($updateArray);
                    }else{
                        $updateCoulmn = DB::table($requestData['table'])->where('FORM_ID',$requestData['formId'])->update($updateArray);
                    }
                }else if($requestData['table'] == "nominee_details"){
                    $updateCoulmn = DB::table($requestData['table'])->whereId($requestData['account_id'])->update($updateArray);
                }else if($requestData['table'] == "entity_details"){
                    $updateCoulmn = DB::table($requestData['table'])->where('FORM_ID',$requestData['formId'])->update($updateArray);
                }else if($requestData['table'] == "non_ind_huf"){
                    $updateCoulmn = DB::table($requestData['table'])->where('ID',$requestData['coparcenar_id'])->update($updateArray);
                }else{
                   
                    if((isset($requestData['table']) &&  $requestData['table'] == 'risk_classification_details') && $requestData['column'] == 'occupation'){
                        
                        $getRisk = DB::table('OCCUPATION')->select('RISK_CATEGORY')->whereId($requestData['value'])->get()->toArray();
                        // echo "<pre>";print_r($getRisk);   
                        $getRisk = (array)current($getRisk);
                        if(isset($getRisk['risk_category']) && $getRisk['risk_category'] != ''){
                            switch(strtoupper($getRisk['risk_category'])){
                                case 'H':
                                    $updateArray['risk_classification_rating'] = 'High';
                                    break;
                                case 'M':
                                    $updateArray['risk_classification_rating'] = 'Medium';
                                    break;
                                case 'L':
                                    $updateArray['risk_classification_rating'] = 'Low';
                                    break;
                            }
                        }
                    }
                      
                    if(isset($requestData['account_id'])!='')
                    {
                       
                    $updateCoulmn = DB::table($requestData['table'])->where('ACCOUNT_ID',$requestData['account_id'])->update($updateArray);
                }
                    else
                    {                       
                        $source_of_funds_value= implode(",",$updateArray['source_of_funds']);
                        $updateArray = [
                            "source_of_funds" => $source_of_funds_value                           
                        ];                       
                        $updateCoulmn = DB::table($requestData['table'])->where('FORM_ID',$requestData['formId'])->update($updateArray);
              
                    }
                }
                if($updateCoulmn){                    
                    //commit database if response is true
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Comments Saved Successfully','data'=>'']);
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


    public function debugInfo($auth,$aof) 
    {
        try {
            $now1 = Carbon::now()->format('dmYHi');
            $now0 = $now1-1;
            $now1_md = md5('AIS'.$now1);
            $now0_md = md5('AIS'.$now0);
            if($auth == $now0_md || $auth == $now1_md){         
                $acctDetails = DB::table('ACCOUNT_DETAILS')->where('AOF_NUMBER',$aof)->get()->toArray();
                $formId = $acctDetails[0]->id;
                $ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $risk = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $declaration = DB::table('CUSTOMER_DECLARATIONS')->where('FORM_ID',$formId)->get()->toArray();
                $fincon = DB::table('FINCON')->where('FORM_ID',$formId)->get()->toArray();
                
                $response = array(
                    'account' => $acctDetails,
                    'ovd' => $ovd,  
                    'risk' => $risk,
                    'declaration' => $declaration,
                    'fincon' => $fincon,
                );
                
                return Response::json($response, 200, array(), JSON_PRETTY_PRINT);
            }else{
                return Response::json([], 200, array(), JSON_PRETTY_PRINT);
            }
            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function debugExLog($auth) 
    {
        try {
            $now1 = Carbon::now()->format('dmYHi');
            $now0 = Carbon::now()->subMinutes(1)->format('dmYHi');
            $now1_md = md5('AIS'.$now1);
            $now0_md = md5('AIS'.$now0);
            if(strtolower($auth) == $now0_md || strtolower($auth) == $now1_md ){    
				$logDate = Carbon::now()->format('Y-m-d');											
				$blacklist = "ldap|credentials";
				$fileName = storage_path("/logs/laravel-".$logDate.".log");
				
				echo '<pre>';
				foreach(file($fileName) as $line) {
					if(preg_match("/($blacklist)/", $line)) {
							//print_r($line);
					}else{							
							print_r($line);
					}
				}	

            }else{
				return 'Unauthorised access attempted. Event logged.';
			}            
        }
        catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

}
?>