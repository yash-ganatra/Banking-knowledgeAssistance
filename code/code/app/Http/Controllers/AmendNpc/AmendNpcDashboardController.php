<?php

namespace App\Http\Controllers\AmendNpc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cookie;
use DB;
use Crypt,Cache,Session;
use App\Helpers\CommonFunctions;
use SoulDoit\DataTable\SSP;
use Carbon\Carbon;


class AmendNpcDashboardController extends Controller
{    /**
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

            if(!in_array($this->roleId,[19,20,21,22,23])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('AmendNpc/AmendNpcDashboardController','application','Unauthorized attempt detected by '.$this->userId,'','','1');

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
   
    public function dashboard(){
        return view('amendnpc.dashboard');
    }

    public function amendapplicant(Request $request){

        try{
            $filterColumns = ['CREATED_AT','CRF_NUMBER','CUSTOMER_ID','ACCOUNT_NO','CREATED_BY','CRF_STATUS','ACTION','AMEND_NPC_REVIEW_TIME','AMEND_L1_REVIEW','AMEND_L2_REVIEW','AMEND_QC_REVIEW','AMEND_AUDIT_REVIEW'];
            $requestData = $request->get('data');
            $select_arr = [];

            $crfNumber = isset($requestData['crfNumber']) && $requestData['crfNumber'] != ''?$requestData['crfNumber']:'';
            $customerId =  isset($requestData['customerId']) && $requestData['customerId'] != ''?$requestData['customerId']:'';

            $i = 0;
            $role = Session::get('role');
            // $dt = array();

            foreach ($filterColumns as $column) {
                if($column == 'CRF_STATUS'){
                    array_push($select_arr, strtolower('CRF_STATUS'));
                     $dt[$i] = array('db'=>strtolower('CRF_STATUS'),'dt'=>$i,
                    'formatter'=>function($d,$row){
                        $html = '';
                        if($row->crf_status != ''){
                            $crfStatus = config('amend_status.CRF_STATUS');
                            $html = $crfStatus[$row->crf_status];
                        }
                        return $html;
                    });
                }elseif($column == 'CREATED_BY'){
                    array_push($select_arr, strtolower('CREATED_BY'));
                    $dt[$i] = array('db'=>strtolower('CREATED_BY'),'dt'=>$i,
                        'formatter'=>function($d,$row){
                            $userData =  DB::table('USERS')->select(DB::raw('emp_first_name || emp_middle_name || emp_last_name AS empname'))->where('ID',$row->created_by)->get()->toArray();
                            $userName = (array)current($userData);

                            $html = isset($userName['empname']) && $userName['empname'] != ''?$userName['empname'] : '';
                            return $html;
                        });

                }else if($column == 'ACTION'){
                    array_push($select_arr, DB::raw('CRF_NUMBER AS action'));
                    $dt[$i] = array('db'=>DB::raw('CRF_NUMBER AS action'),'dt'=>$i,
                    'formatter'=>function($d,$row){
                        $html = '';
                        $reviewTime = $row->amend_npc_review_time;
                            $timeCheck = 'OK';
                            if($reviewTime == '' || $reviewTime == NULL){
                                $timeCheck = 'OK';
                            }else{
                                $timeDiffInMin = Carbon::now()->diffInMinutes($reviewTime);
                                if($timeDiffInMin >= 15){
                                    $timeCheck = 'OK';
                                }else{
                                    $timeCheck = 'NOTOK';
                                }
                            }

                        if($row->crf_number != '' && !in_array($row->crf_status, [32,42,38,48])){
                            $getUserId = DB::table('AMEND_MASTER')->leftjoin('USERS','USERS.ID','AMEND_MASTER.AMEND_NPC_REVIEW_BY')->where('AMEND_MASTER.CRF_NUMBER',$row->crf_number)->pluck('users.emp_first_name')->toArray();
                            if(($row->amend_l1_review == 1 && $timeCheck == 'NOTOK') && (Session::get('role') == 19)){
                                $html.= '<a href="javascript:void(0)" id="'.$row->crf_number.'" class="amendNpcReview inReview" title="Form is in review by '.strtoupper($getUserId[0]).'">In-Review</a>'; }
                                elseif(($row->amend_l2_review == 1  && $timeCheck == 'NOTOK') && (Session::get('role') == 20)){

                                    $html.= '<a href="javascript:void(0)" id="'.$row->crf_number.'" class="amendNpcReview inReview" title="Form is in review by '.strtoupper($getUserId[0]).'">In-Review</a>';
                                }
                                elseif(($row->amend_qc_review == 1  && $timeCheck == 'NOTOK') && (Session::get('role') == 21)){

                                    $html.= '<a href="javascript:void(0)" id="'.$row->crf_number.'" class="amendNpcReview inReview" title="Form is in review by '.strtoupper($getUserId[0]).'">In-Review</a>';
                                }
                                elseif(($row->amend_audit_review == 1  && $timeCheck == 'NOTOK') && (Session::get('role') == 23)){

                                    $html.= '<a href="javascript:void(0)" id="'.$row->crf_number.'" class="amendNpcReview inReview" title="Form is in review by '.strtoupper($getUserId[0]).'">In-Review</a>';
                                }
                                else{
                                   if(!in_array($row->crf_status, [32,42,38,48])){
                                     $html .='<a href="javascript:amendL1Review('.$row->crf_number.')" id="'.$row->crf_number.'" class="amendamendNpcReview">Review</a>';
                                    }
                                 }
                            }


                        return $html;
                    });
                }else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }

            // $dt_obj = (new SSP('AMEND_MASTER',$dt));
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            if(in_array($role,[21,23])){
                $dt_obj = DB::table('AMEND_MASTER')->select($select_arr)->take(100);
            }else{
                $dt_obj = DB::table('AMEND_MASTER')->select($select_arr);
            }

            $dt_obj = $dt_obj->where('CRF_NUMBER','LIKE','%'.$crfNumber.'%');
            $dt_obj = $dt_obj->where('CUSTOMER_ID','LIKE','%'.$customerId.'%');
            $dt_obj = $dt_obj->where('CRF_STATUS','<>',48);
            $dt_obj = $dt_obj->where('CRF_STATUS','<>',38);
            $dt_obj = $dt_obj->where('CRF_NEXT_ROLE',$role);
            // $dt_obj = $dt_obj->where('L1','P');

            $dt_obj = $dt_obj->orderBy('ID', 'DESC');
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);

            //return response()->json($dt_obj->getDtArr());

        }catch(Illuminate\Exception\QueryException $e){
            if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }  

    public function alreadyamendreview(Request $request){
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $roleId = Session::get('role');
                $crfNumber = $requestData['crf_number'];
                $currentTime = Carbon::now()->format('Y-m-d H:i:s');
                $currentUser = Session::get('userId');
                switch ($roleId) {
                    case 19:
                        $field_name = 'AMEND_L1_REVIEW';
                        break;

                    case 20:
                        $field_name = 'AMEND_L2_REVIEW';
                        break;

                    case 21:
                        $field_name = 'AMEND_QC_REVIEW';
                        break;

                    case 23:
                        $field_name = 'AMEND_AUDIT_REVIEW';
                        break;
                    
                    default:
                        break;
                }
                $reviewStatus = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->get()->toArray();
                if(count($reviewStatus) >= 1){
                    $reviewStatus = (array) current($reviewStatus);
                    if($roleId!=$reviewStatus['crf_next_role']){
                        return json_encode(['status'=>'fail','msg'=>'Record already processed','data'=>[]]);
                    }
                    $fieldLower = strtolower($field_name);
                    $timeDiffInMin = 0;
                    if ($reviewStatus[$fieldLower] == '1') {

                        $reviewTime = $reviewStatus['amend_npc_review_time'];
                        $reviewBy=$reviewStatus['amend_npc_review_by'];

                        $okToProceed = false;


                        $timeCheck = 'OK';
                        
                        if($reviewTime == '' || $reviewTime == NULL ){
                            $timeCheck = 'OK';
                            $okToProceed = true;
                        }else{
                            $timeDiffInMin = Carbon::now()->diffInMinutes($reviewTime);
                            if($timeDiffInMin >= 15){
                                $timeCheck = 'OK';
                                $okToProceed = true;
                            }else{
                                $timeCheck = 'NOTOK';
                                $okToProceed = false;
                            }
                        }
                }else{
                        $okToProceed = true;
                    }   
                }else{
                    $okToProceed = true;
                    $timeDiffInMin = 0;
                }   

                if($okToProceed){
                    $reviewStatusupdate = DB::table("AMEND_MASTER")->where('CRF_NUMBER',$crfNumber)
                                                 ->update([$field_name=>1, 'AMEND_NPC_REVIEW_TIME'=>$currentTime, 'AMEND_NPC_REVIEW_BY'=>$currentUser]);
                            DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Ok to Review','data'=>[$timeDiffInMin, $currentUser]]);
                }else{
                    $userName = DB::table("USERS")->where("ID",$reviewBy)->pluck('emp_first_name')->toArray();
                    return json_encode(['status'=>'fail','msg'=>'Record Already in Review','data'=>[$timeDiffInMin, $userName,$crfNumber]]);

                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        }
    }  

}
