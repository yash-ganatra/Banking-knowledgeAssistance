<?php

namespace App\Http\Controllers\ChannelId;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
Use Crypt;

class DashboardController extends Controller
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
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if(!in_array($this->roleId,[18])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('ChannelId/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

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
        return view('channelid.oaodashboard')->with('accountsCount',$accountsCount)->with('customerNames',$customerNames)
                                    ->with('applicationStatus',$applicationStatus);
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
        return view('channelid.oaodetails')->with('accountDetails', $validations)
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



    public function oaoapplications(Request $request)
    {
        try {
            //fetch data from request

            // echo "<pre>";print_r($userDetails);exit;
            $requestData = $request->get('data');

            $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
            //build columns array
            // $filteredColumns = ['OAO_ID','USER_NAME','AOF_NUMBER','MOBILE_NUMBER','QUERY_ID','DEDUPE_STATUS','PAYMENT','FUND_RECEIVED','CUSTOMER_ID','ACCOUNT_ID','FREEZE_1','FREEZE_2','FREEZE_3','FTR','VKYC_STATUS','Update','ACTION', 'FORM_ID'];

            $filteredColumns = ['OAO_STATUS.OAO_ID','USER_NAME','OAO_STATUS.PA_MISMATCH','OAO_STATUS.AOF_NUMBER','MOBILE_NUMBER','OAO_STATUS.QUERY_ID','OAO_STATUS.DEDUPE_STATUS','OAO_STATUS.PAYMENT','OAO_STATUS.FUND_RECEIVED','OAO_STATUS.CUSTOMER_ID','OAO_STATUS.ACCOUNT_ID','OAO_STATUS.FTR','OAO_STATUS.VKYC_LINK','OAO_STATUS.VKYC_STATUS','UPDATES', 'ACTION', 'OAO_STATUS.FORM_ID','OAO_STATUS.PAN_NAME','OAO_STATUS.UPI_NAME','OAO_STATUS.UA_MISMATCH'];

            $array_column = [];

            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {
                if($column == "USER_NAME")
                {
                    array_push($array_column,DB::raw('OAO_STATUS.OAO_ID AS user_name'));
                     $dt[$i] = array( 'db' => DB::raw('OAO_STATUS.OAO_ID AS user_name'),'dt' => $i,
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
                }else if($column == 'OAO_STATUS.PA_MISMATCH'){
                    array_push($array_column,strtolower('OAO_STATUS.PA_MISMATCH'));
                    $dt[$i] = array( 'db' => strtolower('OAO_STATUS.PA_MISMATCH'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->ua_mismatch.'  |  '.$row->ua_mismatch;
                            return $html;
                        }
                    );
                }else if($column == 'OAO_STATUS.AOF_NUMBER'){
                    array_push($array_column,strtolower('OAO_STATUS.AOF_NUMBER'));
                    $dt[$i] = array( 'db' => strtolower('OAO_STATUS.AOF_NUMBER'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if($row->aof_number != ''){
                                $html = '<a href="javascript:showForm('.$row->aof_number.')" style="font-size:15px;" >'.$row->aof_number.'</a>';   
                            }
                            return $html;
                        }
                    );
                }else if($column == 'MOBILE_NUMBER'){
                    array_push($array_column,strtolower('OAO_STATUS.ID'));
                    $dt[$i] = array('db' => strtolower('OAO_STATUS.ID'),'dt' => $i,
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
                }else if($column == "OAO_STATUS.VKYC_LINK"){
                    array_push($array_column,strtolower('OAO_STATUS.VKYC_LINK'));
                    $dt[$i] = array( 'db' => strtolower('OAO_STATUS.VKYC_LINK'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if($row->vkyc_link != ''){
                                $html = '<a href="javascript:void(0)" class="linktext badge badge-secondary" title="'.$row->vkyc_link.'">Y</a>';   
                            }
                            return $html;
                        }
                    );
                }else if($column == "OAO_STATUS.VKYC_STATUS"){
                    array_push($array_column,strtolower('OAO_STATUS.VKYC_STATUS'));
                    $dt[$i] = array( 'db' => strtolower('OAO_STATUS.VKYC_STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                             if($row->vkyc_link != ''){
                                $html = '<a href="javascript:void(0)" class="badge badge-secondary">Mark Done</a>';   
                            }
                            return $html;
                        }
                    );
                }else if($column == "UPDATES"){
                    array_push($array_column,DB::raw('OAO_STATUS.ID AS updates'));
                    $dt[$i] = array( 'db' =>DB::raw('OAO_STATUS.ID AS updates'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if ($row->customer_id != 'Y' || $row->account_id != 'Y') {
                                $html = '<a href="javascript:void(0)" id="'.$row->oao_id.'" class="oaoReview">Update</a>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($array_column,DB::raw('OAO_STATUS.ID AS action'));
                    $dt[$i] = array( 'db' => DB::raw('OAO_STATUS.ID AS action'),'dt' => $i,
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
                    array_push($array_column,$column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }                
                $i++;              
            }

            // $dt_obj = new SSP('OAO_STATUS', $dt);

            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('OAO_STATUS')->select($array_column); 

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

            $dt_obj = $dt_obj->orderBy('OAO_STATUS.OAO_ID','DESC');

            

            // $dt_obj = $dt_obj->where(function ($query) {
            //    $query->where('OAO_STATUS.PAYMENT', 'Y')
            // });
           
            // $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->order(0, 'desc');
            //echo "<pre>";print_r($dt_obj->getDtArr());exit;
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
            
            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
            // return response()->json($dt_obj->getDtArr());
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

}