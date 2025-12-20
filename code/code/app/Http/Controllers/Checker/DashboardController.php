<?php

namespace App\Http\Controllers\Checker;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\DelightFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use DB;

class DashboardController extends Controller
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

            if($this->roleId != 17){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Checker/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    /*
     * Method Name: dashboard
     * Created By : Sharanya T
     * Created At : 12-02-2020

     * Description:
     * This function is fetch savings , current and fixed deposite accounts Count and customer names
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function dashboard()
    {
        try{
            $delightSchemeCodes = DelightFunctions::getDelightSchemeCodes();
            $delightKitStatus = DelightFunctions::getDelightKitStatus();

            $delightKitStatus = Arr::except($delightKitStatus, [11,7,8,3,4,5]);

            //returns tempalte
            return view('checker.seekapproval')
                                        ->with('delightSchemeCodes',$delightSchemeCodes)
                                        ->with('delightKitStatus',$delightKitStatus);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Checker/DashboardController','dashboard',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function checkerkitdetailstable(Request $request)
    {
        try {
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr=[];
            // $solId = Session::get('branchId');
            // $solId = 010;
            // $solId = 181;
            // echo "<pre>";print_r($requestData);exit;

            //build columns array
            $filteredColumns = ['DELIGHT_KIT.ID','DELIGHT_KIT.SOL_ID','DELIGHT_KIT.SCHEME_CODE','DELIGHT_KIT.KIT_NUMBER','DELIGHT_KIT.CUSTOMER_ID','DELIGHT_KIT.ACCOUNT_NUMBER','DELIGHT_KIT.STATUS','DELIGHT_KIT.CREATED_AT','DELIGHT_KIT.REQUEST_COMMENT','DELIGHT_KIT.APPROVAL_COMMENT','ACTION','DELIGHT_KIT.CR_STATUS'];
            $i=0;

            foreach ($filteredColumns as $column) {
                if($column == "DELIGHT_KIT.KIT_NUMBER"){
                    array_push($select_arr,strtolower('DELIGHT_KIT.KIT_NUMBER'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.KIT_NUMBER'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            // echo "<pre>";print_r($row);exit;
                            $status_PA = [12,13,14];
                            $html = $row['kit_number'];

                            if (in_array($row['status'], $status_PA)) {
                                $html = '<a class="open-seek-approval-modal" data-bs-toggle="modal" data-bs-target="#seekApproval" id="kitNumber-'.$row['kit_number'].'" href="javascript:void(0)">'.$row['kit_number'].'</a>';
                            }
                            // echo"<pre>"; print_r($html); exit;
                            return $html;
                        }
                    );
                }else if($column == "DELIGHT_KIT.STATUS"){
                      array_push($select_arr,strtolower('DELIGHT_KIT.STATUS'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.STATUS'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = '';
                            if (isset($row['status']) && $row['status'] != '' ) {
                                $status = DelightFunctions::getDelightKitStatusById($row['status']);
                                $html = '<span id="status-'.$row['kit_number'].'">'.ucfirst(strtolower($status['kitstatus'])).'</span>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "DELIGHT_KIT.CREATED_AT"){
                    array_push($select_arr,strtolower('DELIGHT_KIT.CREATED_AT'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.CREATED_AT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = \Carbon\Carbon::parse($row['created_at'])->format('d-m-Y');
                            return $html;
                        }
                    );
                }else if($column == "DELIGHT_KIT.REQUEST_COMMENT"){
                    array_push($select_arr,strtolower('DELIGHT_KIT.REQUEST_COMMENT'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.REQUEST_COMMENT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $request_comment =  $row['request_comment'];
                            $html = '';
                            if($request_comment != '')
                            {
                                $maxLength = 15;
                                $html .= '<div class="tooltip_text_trunk">';
                                if(strlen($request_comment) > $maxLength){
                                    $html .= '<span class="mytooltip">';
                                    $html .= '<span id="request_comment-'.$row['kit_number'].'" data-toggle="tooltip" data-placement="top" title="" data-original-title="'.$request_comment.'">';
                                    $html .= trim(substr($request_comment,0,$maxLength)).'...';
                                    $html .= '</span>';
                                    $html .= '</span>';
                                }else{
                                    $html .= '<span id="request_comment-'.$row['kit_number'].'">'.$request_comment.'</span>';
                                }
                                $html .= '</div>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "DELIGHT_KIT.APPROVAL_COMMENT"){
                    array_push($select_arr,strtolower('DELIGHT_KIT.APPROVAL_COMMENT'));
                    $dt[$i] = array( 'db' => strtolower('DELIGHT_KIT.APPROVAL_COMMENT'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $approval_comment =  $row['approval_comment'];
                            $html = '';
                            if($approval_comment != '')
                            {
                                $maxLength = 15;
                                $html .= '<div class="tooltip_text_trunk">';
                                if(strlen($approval_comment) > $maxLength){
                                    $html .= '<span class="mytooltip">';
                                        $html .= '<span id="approval_comment-'.$row['kit_number'].'" data-toggle="tooltip" data-placement="top" title="" data-original-title="'.$approval_comment.'">';
                                            $html .= trim(substr($approval_comment,0,$maxLength)).'...';
                                        $html .= '</span>';
                                    $html .= '</span>';
                                }else{
                                    $html .= '<span id="approval_comment-'.$row['kit_number'].'">'.$approval_comment.'</span>';
                                }
                                $html .= '</div>';
                            }
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr,DB::raw('DELIGHT_KIT.KIT_NUMBER AS action'));
                    $dt[$i] = array( 'db' => DB::raw('DELIGHT_KIT.KIT_NUMBER AS action'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $status_PA = [12,13,14];
                            $html = '';
                            if (in_array($row['status'], $status_PA)) {
                                $html =  '<input type="checkbox" style="opacity: 20!important" class="kit-checkbox" name="approval-checkbox" id="kitCheckbox-'.$row['kit_number'].'">';
                            }
                            return $html;
                        }
                    );
                }else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            // $dt_obj = new SSP('DELIGHT_KIT', $dt);
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            $dt_obj = DB::table('DELIGHT_KIT')->select($select_arr);
            $status = [6,9,10,12,13,14];
            $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', $status);
             // $dt_obj = $dt_obj->whereIn('DELIGHT_KIT.STATUS', $status)->orderColumn('DELIGHT_KIT.STATUS', 'DESC');

            if(isset($requestData['delightSchemeCode']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.SCHEME_CODE',$requestData['delightSchemeCode']);
            }

            if(isset($requestData['kitNumber']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.KIT_NUMBER', 'like', '%'.$requestData['kitNumber'].'%');
            }

            if(isset($requestData['customerID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.CUSTOMER_ID', 'like', '%'.$requestData['customerID'].'%');
            }

            if(isset($requestData['accountID']))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.ACCOUNT_NUMBER', 'like', '%'.$requestData['accountID'].'%');
            }

            if(isset($requestData['delightKitStatus'] ))
            {
                $dt_obj = $dt_obj->where('DELIGHT_KIT.STATUS', strtoupper($requestData['delightKitStatus']));
            }

            if(isset($requestData['startDate']))
            {
                $dt_obj = $dt_obj->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }

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

    public function submitApproval(Request $request){
        try{
            $requestData = $request->get('data');
            $current_timestamp = Carbon::now()->timestamp;
			$timestamp = Carbon::parse($current_timestamp);

            if (!isset($requestData['approval_comment']) && $requestData['approval_comment'] == '') {
                return json_encode(['status'=>'fail','msg'=>'Please enter Comment to approve','data'=>[]]);
            }

            $dkstatusupdate = DB::table('DELIGHT_KIT')->select('ID','CREATED_BY')
                                    ->where('KIT_NUMBER',$requestData['kit_number'])->get()->toArray();

            
            $dkstatusupdate = (array) current($dkstatusupdate);
            // echo '<pre>';print_r(Session::get('userId'));exit;



            if (isset($requestData['approval_kits'])) {
                $approvalKits = $requestData['approval_kits'];
                for ($i=0; $i < count($approvalKits); $i++) {
                    $updateKitStatus = DB::table("DELIGHT_KIT")->where('KIT_NUMBER', $approvalKits[$i]['kitNumber'])
                        ->update(['STATUS'=>$approvalKits[$i]['status'], 'CR_APPROVED_ON'=> $timestamp]);

                CommonFunctions::updateDKStatusHistory($dkstatusupdate['id'],$requestData['kit_number'],$requestData['status'],Session::get('userId'));
                }

            }else{
                $updateKitStatus = DB::table("DELIGHT_KIT")->where('KIT_NUMBER', $requestData['kit_number'])
                    ->update(['approval_comment'=> $requestData['approval_comment'],
                        'STATUS'=> $requestData['status'],
                        'CR_APPROVED_ON'=> $timestamp]);

                CommonFunctions::updateDKStatusHistory($dkstatusupdate['id'],$requestData['kit_number'],$requestData['status'],Session::get('userId'));
            }

            if($updateKitStatus) {
                DB::commit();
                return json_encode(['status'=>'success','msg'=>'Approved','data'=>[]]);
            }else{
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
}
?>
