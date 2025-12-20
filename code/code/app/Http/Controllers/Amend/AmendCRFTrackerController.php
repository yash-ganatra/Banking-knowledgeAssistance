<?php

namespace App\Http\Controllers\Amend;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Session;
use DB;
use Illuminate\Support\Arr;
use File;
use Cookie;
use Crypt;


class AmendCRFTrackerController extends Controller
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

            // if($this->roleId == 1){

            //     $isAutherized = false;
            // }else{

               $isAutherized = true;
            // }
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Amend/AmendCRFTrackerController','AmendCRFTrackerController','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    public function amendcrftracking($crf='')
    {
        // echo "<pre>";print_r('trackingDetails');exit;
        $userSol = Session::get('branchId');
        if($userSol == '' || $userSol == null) return false;

        $userRole = Session::get('role');
        if($userRole == '' || $userRole == null) return false;

        // $customerNames = array();

        $customerNames = DB::table('AMEND_MASTER')->where('CREATED_AT','>=',Carbon::now()->subMonths(2))
                                                  ->orderBy('crf_number','DESC')
                                                  ->pluck(DB::raw('customer_name ||- crf_number AS customer_name'),'crf_number');

        return view('amend.amendcrftracking')->with('customerNames',$customerNames)
                                            ->with('crf',$crf);
    }

    public function amendtrackingdetails(Request $request){
        
        $requestData = $request->get('data');

        $crf_number = '';

        if(isset($requestData['crf_tracking_no']) && $requestData['crf_tracking_no'] != ''){

            $crf_number = $requestData['crf_tracking_no'];

        }else if($crf_number == ''){

            $crf_number = (isset($requestData['customerName']) && $requestData['customerName'] != '') ? $requestData['customerName'] : '';
        }
        // echo "<pre>";print_r($crf_number);exit;
        if(strlen($crf_number) == 9){
            $custId = $crf_number;
            $getCrfList =  DB::table('AMEND_MASTER')->select('ID','CRF_NUMBER','CRF_STATUS','CUSTOMER_NAME','CREATED_AT')
                                                    ->where('CUSTOMER_ID',$custId)
                                                    ->orderBy('ID','DESC')
                                                    ->get()
                                                    ->toArray();
            
            return view('amend.amendcrflist')->with('getCrfList',$getCrfList);
        }else{

            $getAmendTrackingDetails = DB::table('AMEND_STATUS_LOG')->select()
                                                                    ->where('crf_number',$crf_number)
                                                                    ->orderBy('ID')
                                                                    ->get()
                                                                    ->toArray();
            // echo "<pre>";print_r($getAmendTrackingDetails);exit;
            for($userId = 0;$userId<count($getAmendTrackingDetails);$userId++){
    
                $getName = DB::table('USERS')->select('EMP_FIRST_NAME')
                                            ->where('id',$getAmendTrackingDetails[$userId]->created_by)
                                            ->get()
                                            ->toArray();
                $getAmendTrackingDetails[$userId]->created_by = current($getName);
    
                $getStatus = config('amend_status.CRF_STATUS.'.$getAmendTrackingDetails[$userId]->status);
    
                $getAmendTrackingDetails[$userId]->status = $getStatus;
    
            }
            return view('amend.amendtrackingdetails')->with('getAmendTrackingDetails',$getAmendTrackingDetails);
        }

    }

    public function crfcustomerlist(Request $request){
        if($request->ajax()){
            $requestData = $request->get('data');
            $custId = $requestData['custId'];

            $customerNames = DB::table('AMEND_MASTER')->where('customer_id',$custId)
                                                      ->orderBy('ID','DESC')
                                                      ->pluck(DB::raw('customer_name ||- crf_number AS customer_name'),'crf_number');
            
            if(count($customerNames)>0){
                return json_encode(['status'=>'success','message'=>'Successfully fetch the customer list.','data'=>[$customerNames]]);
            }
        }
    }
}

?>